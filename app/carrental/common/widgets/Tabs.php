<?php

namespace common\widgets;

/**
 * Description of Tabs
 *
 * @author kevin
 */
class Tabs extends \yii\bootstrap\Tabs
{
    /**
     *
     * @var string the jquery selector
     */
    public $containerSelector = null;
    
    public $enableFulidLayout = false;
    
    public $tabsOptions = ['class'=>'nav-tabs-custom'];
    
    use \common\widgets\BootstrapIndependentOfViewWidgetTrait;
    /*{
        \yii\bootstrap\BootstrapWidgetTrait::registerPlugin insteadof registerPlugin;
        \yii\bootstrap\BootstrapWidgetTrait::registerClientEvents insteadof registerClientEvents;
        
    }*/
    
    public function init() {
        if (!isset($this->options['id'])) {
            $this->options['id'] = 'w'.(static::$counter++).'tabs'. time();
        }
        if ($this->containerSelector == null) {
            $this->containerSelector = '#'.\common\helpers\BootstrapHtml::MAIN_CONTENT_ID;
        }
        if ($this->enableFulidLayout) {
            //\yii\bootstrap\Html::addCssStyle($this->tabsOptions, ['overflow'=>'hidden', 'width'=>'100%', 'height'=>'100%']);
        }
        parent::init();
        
        foreach ($this->items as $k => $item) {
            if (isset($item['url'])) {
                $item['linkOptions'] = array_merge(\yii\helpers\ArrayHelper::getValue($item, 'linkOptions', []), ['data-href'=>$item['url']]);
                unset($item['url']);
            }
            $this->items[$k] = $item;
        }
    }
    
    public function run() {
        $htmls = [];
        $htmls[] = \yii\bootstrap\Html::beginTag('div', $this->tabsOptions);
        $htmls[] = parent::run();
        $htmls[] = \yii\bootstrap\Html::endTag('div');
        $this->_registerJs("$.custom.bootstrap.tabs.init('#{$this->options['id']}');");
        
        if ($this->enableFulidLayout) {
            $tabSelector = "'#".$this->options['id']."'";
            $scripts = [];
            $scripts[] = "$(window).resize(function(event) {";
            $scripts[] = "  var containerH = $({$tabSelector}).parent().parent().height();";
            $scripts[] = "  var contentTarget = $({$tabSelector}).next('.tab-content');";
            $scripts[] = "  var innerH = $({$tabSelector}).outerHeight(true);";
            $scripts[] = "  var innerH2 = innerH + (contentTarget.outerHeight(true)-contentTarget.height());";
            $scripts[] = "  contentTarget.children('.tab-pane').css({height:containerH - innerH2, 'max-height':containerH - innerH2, overflow:'auto'});";
            $scripts[] = "  contentTarget.css({height:containerH - innerH, 'max-height':containerH - innerH, 'min-height':containerH - innerH, overflow:'hidden'});";
            $scripts[] = "  $({$tabSelector}).parent().css({height:containerH, 'max-height':containerH, 'margin-bottom':0, overflow:'hidden'});";
            $scripts[] = "});";
            $scripts[] = "$(function () {";
            $scripts[] = "  $(window).resize();";
            $scripts[] = "});";
            $this->_registerJs(implode("\n", $scripts));
        }
        $htmls[] = $this->renderJsPartHtml();
        return implode("\n", $htmls);
    }
    
}

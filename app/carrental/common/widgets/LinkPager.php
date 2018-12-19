<?php

namespace common\widgets;

/**
 * Description of LinkPager
 *
 * @author kevin
 */
class LinkPager extends \yii\widgets\LinkPager
{
    public $filterParams = [];
    
    public $containerSelector = false;
    
    public function init() {
        parent::init();
        if ($this->pagination && !empty($this->filterParams)) {
            foreach ($this->filterParams as $k => $v) {
                $this->pagination->params[$k] = $v;
            }
        }
    }
    
    public function renderPageButton($label, $page, $class, $disabled, $active) {
        $options = ['class' => empty($class) ? $this->pageCssClass : $class];
        if ($active) {
            \yii\bootstrap\Html::addCssClass($options, $this->activePageCssClass);
        }
        if ($disabled) {
            \yii\bootstrap\Html::addCssClass($options, $this->disabledPageCssClass);
            $tag = \yii\helpers\ArrayHelper::remove($this->disabledListItemSubTagOptions, 'tag', 'span');
            
            return \yii\bootstrap\Html::tag('li', \yii\bootstrap\Html::tag($tag, $label, $this->disabledListItemSubTagOptions), $options);
        }
        $linkOptions = $this->linkOptions;
        $linkOptions['data-page'] = $page;
        
        $url = $this->pagination->createUrl($page);
        if ($this->containerSelector) {
            $linkOptions['onclick'] = "$.custom.bootstrap.loadElement('{$this->containerSelector}', '{$url}')";
            $linkOptions['href'] = 'javascript:void(0);';
        }
        else {
            $linkOptions['href'] = $url;
        }

        return \yii\bootstrap\Html::tag('li', \yii\bootstrap\Html::a($label, null, $linkOptions), $options);
    }
    
}

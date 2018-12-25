<?php

namespace common\widgets;

/**
 * NavBarAdminWidget renders a navbar HTML component.
 *
 * Any content enclosed between the [[begin()]] and [[end()]] calls of NavBar
 * is treated as the content of the navbar. You may use widgets such as [[Nav]]
 * or [[\yii\widgets\Menu]] to build up such content. For example,
 *
 * ```php
 * use yii\bootstrap\NavBar;
 * use yii\bootstrap\Nav;
 *
 * NavBar::begin(['brandLabel' => 'NavBar Test']);
 * echo Nav::widget([
 *     'items' => [
 *         ['label' => 'Home', 'url' => ['/site/index']],
 *         ['label' => 'About', 'url' => ['/site/about']],
 *     ],
 *     'options' => ['class' => 'navbar-nav'],
 * ]);
 * NavBar::end();
 * ```
 *
 * @see http://getbootstrap.com/components/#navbar
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class NavBarAdminWidget extends \yii\bootstrap\Widget
{
    /**
     * @var array the HTML attributes for the widget container tag. The following special options are recognized:
     *
     * - tag: string, defaults to "nav", the name of the container tag.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    /**
     * @var array the HTML attributes for the container tag. The following special options are recognized:
     *
     * - tag: string, defaults to "div", the name of the container tag.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $containerOptions = [];
    /**
     * @var string|boolean the text of the brand or false if it's not used. Note that this is not HTML-encoded.
     * @see http://getbootstrap.com/components/#navbar
     */
    public $brandLabel = false;
    /**
     * @var array|string|boolean $url the URL for the brand's hyperlink tag. This parameter will be processed by [[\yii\helpers\Url::to()]]
     * and will be used for the "href" attribute of the brand link. Default value is false that means
     * [[\yii\web\Application::homeUrl]] will be used.
     * You may set it to `null` if you want to have no link at all.
     */
    public $brandUrl = false;
    /**
     * @var array the HTML attributes of the brand link.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $brandOptions = [];
    /**
     * @var string text to show for screen readers for the button to toggle the navbar.
     */
    public $screenReaderToggleText = 'Toggle navigation';
    /**
     * @var boolean whether the navbar content should be included in an inner div container which by default
     * adds left and right padding. Set this to false for a 100% width navbar.
     */
    public $renderInnerContainer = true;
    /**
     * @var array the HTML attributes of the inner container.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $innerContainerOptions = [];
    
    public $enableToggleSidebar = true;

    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();
        $this->clientOptions = false;
        $htmlArray = [];
        if (empty($this->options['class'])) {
            \yii\bootstrap\Html::addCssClass($this->options, ['navbar', 'navbar-static-top']);
        } else {
            \yii\bootstrap\Html::addCssClass($this->options, ['widget' => 'navbar']);
        }
        if (empty($this->options['role']) && !$this->enableToggleSidebar) {
            $this->options['role'] = 'navigation';
        }
        $options = $this->options;
        $tag = \yii\helpers\ArrayHelper::remove($options, 'tag', 'nav');
        $htmlArray[] = \yii\bootstrap\Html::beginTag($tag, $options);
        if ($this->enableToggleSidebar) {
            $htmlArray[] = $this->renderToggleSidebarButton();
            \yii\bootstrap\Html::addCssClass($this->containerOptions, ['widget' => 'navbar-custom-menu']);
            $options = $this->containerOptions;
            $tag = \yii\helpers\ArrayHelper::remove($options, 'tag', 'div');
            $htmlArray[] = \yii\bootstrap\Html::beginTag($tag, $options);
        }
        else {
            if ($this->renderInnerContainer) {
                if (!isset($this->innerContainerOptions['class'])) {
                    \yii\bootstrap\Html::addCssClass($this->innerContainerOptions, 'container');
                }
                $htmlArray[] = \yii\bootstrap\Html::beginTag('div', $this->innerContainerOptions);
            }
            $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class' => 'navbar-header']);
            if (!isset($this->containerOptions['id'])) {
                $this->containerOptions['id'] = "{$this->options['id']}-collapse";
            }
            $htmlArray[] = $this->renderToggleButton();
            if ($this->brandLabel !== false) {
                \yii\bootstrap\Html::addCssClass($this->brandOptions, ['widget' => 'navbar-brand']);
                $htmlArray[] = \yii\bootstrap\Html::a($this->brandLabel, $this->brandUrl === false ? Yii::$app->homeUrl : $this->brandUrl, $this->brandOptions);
            }
            $htmlArray[] = \yii\bootstrap\Html::endTag('div');
            \yii\bootstrap\Html::addCssClass($this->containerOptions, ['collapse' => 'collapse', 'widget' => 'navbar-collapse']);
            $options = $this->containerOptions;
            $tag = \yii\helpers\ArrayHelper::remove($options, 'tag', 'div');
            $htmlArray[] = \yii\bootstrap\Html::beginTag($tag, $options);
        }
        
        echo implode("\n", $htmlArray);
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        $tag = \yii\helpers\ArrayHelper::remove($this->containerOptions, 'tag', 'div');
        $htmlArray = [];
        $htmlArray[] = \yii\bootstrap\Html::endTag($tag);
        if (!$this->enableToggleSidebar && $this->renderInnerContainer) {
            $htmlArray[] = \yii\bootstrap\Html::endTag('div');
        }
        $tag = \yii\helpers\ArrayHelper::remove($this->options, 'tag', 'nav');
        $htmlArray[] = \yii\bootstrap\Html::endTag($tag);
        \yii\bootstrap\BootstrapPluginAsset::register($this->getView());
        
        echo implode("\n", $htmlArray);
    }

    /**
     * Renders collapsible toggle button.
     * @return string the rendering toggle button.
     */
    protected function renderToggleButton()
    {
        $bar = \yii\bootstrap\Html::tag('span', '', ['class' => 'icon-bar']);
        $screenReader = "<span class=\"sr-only\">{$this->screenReaderToggleText}</span>";

        return \yii\bootstrap\Html::button("{$screenReader}\n{$bar}\n{$bar}\n{$bar}", [
            'class' => 'navbar-toggle',
            'data-toggle' => 'collapse',
            'data-target' => "#{$this->containerOptions['id']}",
        ]);
    }
    
    protected function renderToggleSidebarButton()
    {
        return \yii\bootstrap\Html::a(\yii\bootstrap\Html::tag('span', $this->screenReaderToggleText, ['class'=>'sr-only']), '#', [
            'class' => 'sidebar-toggle',
            'data-toggle' => 'offcanvas',
            'role' => 'button',
        ]);
    }
    
    
}

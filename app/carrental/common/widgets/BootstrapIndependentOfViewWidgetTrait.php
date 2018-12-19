<?php

namespace common\widgets;

trait BootstrapIndependentOfViewWidgetTrait
{
    /**
     * @var array the options for the underlying Bootstrap JS plugin.
     * Please refer to the corresponding Bootstrap plugin Web page for possible options.
     * For example, [this page](http://getbootstrap.com/javascript/#modals) shows
     * how to use the "Modal" plugin and the supported options (e.g. "remote").
     */
    //public $clientOptions = [];
    /**
     * @var array the event handlers for the underlying Bootstrap JS plugin.
     * Please refer to the corresponding Bootstrap plugin Web page for possible events.
     * For example, [this page](http://getbootstrap.com/javascript/#modals) shows
     * how to use the "Modal" plugin and the supported events (e.g. "shown").
     */
    //public $clientEvents = [];

    public $jsArray = [];

    /**
     * Initializes the widget.
     * This method will register the bootstrap asset bundle. If you override this method,
     * make sure you call the parent implementation first.
     */
    public function init()
    {
        parent::init();
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
    }
    
    /**
     * Registers a specific Bootstrap plugin and the related events
     * @param string $name the name of the Bootstrap plugin
     */
    protected function registerPlugin($name)
    {
        $id = $this->options['id'];

        if ($this->clientOptions !== false) {
            $options = empty($this->clientOptions) ? '' : Json::htmlEncode($this->clientOptions);
            $js = "jQuery('#$id').$name($options);";
            $this->_registerJs($js);
        }

        $this->registerClientEvents();
    }

    /**
     * Registers JS event handlers that are listed in [[clientEvents]].
     * @since 2.0.2
     */
    protected function registerClientEvents()
    {
        if (!empty($this->clientEvents)) {
            $id = $this->options['id'];
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
            $this->_registerJs(implode("\n", $js));
        }
    }
    
    protected function _registerJs($js, $position = \yii\web\View::POS_READY, $key = null)
    {
        $key = $key ?: md5($js);
        if (!isset($this->jsArray[$position])) {
            $this->jsArray[$position] = [];
        }
        $this->jsArray[$position][$key] = $js;
    }
    
    protected function renderJsPartHtml()
    {
        $scripts = [];
        ksort($this->jsArray);
        foreach ($this->jsArray as $position => $js) {
            if (empty($js)) {
                continue;
            }
            if ($position == \yii\web\View::POS_READY) {
                $js_ = "jQuery(document).ready(function () {\n" . implode("\n", $js) . "\n});";
            }
            elseif ($position == \yii\web\View::POS_LOAD) {
                $js_ = "jQuery(window).on('load', function () {\n" . implode("\n", $js) . "\n});";
            }
            else {
                $js_ = implode("\n", $js);
            }
            $scripts[] = \yii\bootstrap\Html::script($js_, ['type'=>'text/javascript']);
        }
        
        return implode("\n", $scripts);
    }
    
}


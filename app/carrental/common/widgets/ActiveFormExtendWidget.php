<?php

namespace common\widgets;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ActiveFormExtendWidget extends \yii\bootstrap\ActiveForm
{
    
    public $extraInitScripts = [];
    
    /**
     * Runs the widget.
     * This registers the necessary JavaScript code and renders the form close tag.
     * @throws InvalidCallException if `beginField()` and `endField()` calls are not matching.
     */
    public function run()
    {
        if (!empty($this->_fields)) {
            throw new InvalidCallException('Each beginField() should have a matching endField() call.');
        }
        
        $htmlArray = [];
        $content = ob_get_clean();
        $htmlArray[] = \yii\helpers\Html::beginForm($this->action, $this->method, $this->options);
        $htmlArray[] = $content;
        
        $htmlArray[] = \yii\helpers\Html::endForm();
        $htmlArray[] = $this->getScriptHtml();
        
        return implode("\n", $htmlArray);
    }
    
    public function getScriptHtml() {
        if ($this->enableClientScript) {
            $id = $this->options['id'];
            $options = \yii\helpers\Json::htmlEncode($this->getClientOptions());
            $attributes = \yii\helpers\Json::htmlEncode($this->attributes);
            //$view = new \yii\web\View();
            //\yii\widgets\ActiveFormAsset::register($view);
            //$view->registerJs("jQuery('#$id').yiiActiveForm($attributes, $options);");
            
            $arrScripts = [];
            $arrScripts[] = "$(function () {";
            foreach ($this->extraInitScripts as $js) {
                $arrScripts[] = $js . (substr($js, -1) == ';' ? '' : ';');
            }
            $arrScripts[] = "jQuery('#$id').yiiActiveForm($attributes, $options);";
            $arrScripts[] = "});";
            
            return \yii\helpers\Html::script(implode("\n", $arrScripts), ['type'=>'text/javascript']);
            /*
            $htmlArray = [];
            if (!empty($view->cssFiles)) {
                $htmlArray[] = implode("\n", $view->cssFiles);
            }
            if (!empty($view->css)) {
                $htmlArray[] = implode("\n", $view->css);
            }
            if (!empty($view->jsFiles)) {
                foreach ($view->jsFiles as $pos => $js) {
                    $htmlArray[] = implode("\n", $js);
                }
            }
            if (!empty($view->js)) {
                foreach ($view->js as $pos => $js) {
                    $htmlArray[] = \yii\helpers\Html::script(implode("\n", $js), ['type'=>'text/javascript']);
                }
            }
            return implode("\n", $htmlArray);
             * 
             */
        }
        
        return "";
    }
    
    /**
     * Ends a widget.
     * Note that the rendering result of the widget is directly echoed out.
     * @return static the widget instance that is ended.
     * @throws InvalidCallException if [[begin()]] and [[end()]] calls are not properly nested
     * @see begin()
     */
    public static function end()
    {
        if (!empty(static::$stack)) {
            $widget = array_pop(static::$stack);
            if (get_class($widget) === get_called_class()) {
                /* @var $widget Widget */
                if ($widget->beforeRun()) {
                    $result = $widget->run();
                    $result = $widget->afterRun($result);
                    return $result;
                }
            } else {
                throw new InvalidCallException('Expecting end() of ' . get_class($widget) . ', found ' . get_called_class());
            }
        } else {
            throw new InvalidCallException('Unexpected ' . get_called_class() . '::end() call. A matching begin() is not found.');
        }
        return "";
    }

}

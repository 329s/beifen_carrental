<?php

namespace common\components;

/**
 * Description of ViewExtend
 *
 * @author kevin
 */
class ViewExtend extends \yii\web\View
{
    
    public $prefix = '';

    public function render($view, $params = array(), $context = null) {
        if (!empty($this->prefix)) {
            $view = $this->prefix.(substr($this->prefix, -1)=='_'?'':'_').$view;
        }
        return parent::render($view, $params, $context);
    }
    
}

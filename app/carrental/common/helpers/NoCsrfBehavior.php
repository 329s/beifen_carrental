<?php

namespace common\helpers;

class NoCsrfBehavior extends \yii\base\Behavior
{
    public $actions = [];
    public $controller;
    
    public function events() {
        return [\yii\web\Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }
    
    public function beforeAction($event) {
        $action = $event->action->id;
        if(in_array($action, $this->actions)){
            $this->controller->enableCsrfValidation = false;
        }
    }
    
}

<?php

namespace common\helpers;

class JQueryValidationAsset extends \yii\web\AssetBundle
{
    public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $baseUrl = '';

    public $css = [
    ];
    public $js = [
    ];
    
    public function init()
    {
        $urlRoot = \common\helpers\Utils::getRootUrl();
        $this->baseUrl = $urlRoot . 'assets';
        
        $this->css = [
        ];
        $this->js = [
            'js/jquery-validation/dist/jquery.validate.js',
        ];
        parent::init();
    }
    
    public function publish($am)
    {
    }
    
}

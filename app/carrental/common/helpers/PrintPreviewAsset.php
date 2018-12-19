<?php

namespace common\helpers;

class PrintPreviewAsset extends \yii\web\AssetBundle
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
        
        $this->js = [
            'js/print-preview/jquery.print-preview.js',
        ];
        $this->css = [
            'js/print-preview/css/print-preview.css',
        ];
        parent::init();
    }
    
    public function publish($am)
    {
    }
}

<?php

namespace common\helpers;

class MapGaodeAsset extends \yii\web\AssetBundle
{
    public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $baseUrl = '';
    
    public $cssOptions = [
        'type' => 'text/css',
    ];
    public $jsOptions = [
        'type' => 'text/javascript',
    ];

    public $css = [
    ];
    public $js = [
    ];
    
    public function init()
    {
        $this->js = [
            "//webapi.amap.com/maps?v=1.3&key=".\Yii::$app->params['map.gaode.jsappkey'],
            "//webapi.amap.com/ui/1.0/main.js",
        ];
        parent::init();
    }
    
    public function publish($am)
    {
    }
}

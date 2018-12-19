<?php

namespace common\helpers;

class HighchartsAsset extends \yii\web\AssetBundle
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
            'js/highcharts/highcharts.js',
        ];
        parent::init();
    }
    
    public function publish($am)
    {
    }
}

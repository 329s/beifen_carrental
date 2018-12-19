<?php

namespace common\helpers;

class KindeditorAsset extends \yii\web\AssetBundle
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
        
        //$lanLocale = \Yii::$app->params['lan_locale'];
        $lanLocale = 'zh-CN';
        
        $this->css = [
            'js/kindeditor/themes/default/default.css',
            'js/kindeditor/plugins/code/prettify.css',
        ];
        $this->js = [
            'js/kindeditor/plugins/code/prettify.js',
            'js/kindeditor/kindeditor-all-min.js',
            "js/kindeditor/lang/{$lanLocale}.js",
        ];
        parent::init();
    }
    
    public function publish($am)
    {
    }
}

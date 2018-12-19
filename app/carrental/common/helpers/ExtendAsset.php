<?php

namespace common\helpers;

class ExtendAsset extends \yii\web\AssetBundle
{
    public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $baseUrl = '';
    
    public $cssOptions = [
        'type' => 'text/css',
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
        'type' => 'text/javascript',
    ];

    public $css = [
    ];
    public $js = [
    ];
    
    public function init()
    {
        $urlRoot = \common\helpers\Utils::getRootUrl();
        $this->baseUrl = $urlRoot . 'assets';
        
        $lanLocale = \Yii::$app->params['lan_locale'];
        $this->css = [
            'extensions/font-awesome-4.7.0/css/font-awesome.min.css',
            'extensions/ionicons-2.0.1/css/ionicons.min.css',
            'extensions/vakata-jstree/themes/default/style.min.css',
            'extensions/x-popup/x-popup.css',
            'css/icons.extension.css',
            'css/icons.large.carrental.css',
        ];
        $this->js = [
            'custom/js/common.custom.js',
            'custom/js/utils.custom.js',
            "custom/js/locale/custom.messages-{$lanLocale}.js",
            'custom/js/bootstrap.custom.js',
            'extensions/vakata-jstree/jstree.min.js',
            'extensions/bootstrap-typeahead/bootstrap-typeahead.js',
            'extensions/bootstrap-typeahead/underscore-min.js',
            'extensions/x-popup/x-popup.js',
        ];
        parent::init();
    }
    
    public function publish($am)
    {
    }
}

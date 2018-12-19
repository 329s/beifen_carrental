<?php

namespace common\helpers;

class EasyUIAsset extends \yii\web\AssetBundle
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
        
        $easyUIFolder = \common\helpers\CEasyUI::ASSETS_EASYUI_FOLDER;
        $uiTheme = \common\helpers\CEasyUI::getUiTheme();
        $lanLocale = \Yii::$app->params['lan_locale'];
        $this->css = [
            "{$easyUIFolder}/themes/{$uiTheme}/easyui.css",
            "{$easyUIFolder}/themes/icon.css",
            'css/easyui.custom.css',
            'css/easyui.bs.extend.css',
        ];
        $this->js = [
            "{$easyUIFolder}/jquery.min.js",
            "{$easyUIFolder}/jquery.easyui.min.js",
            "{$easyUIFolder}/locale/easyui-lang-{$lanLocale}.js",
            'custom/js/easyui.custom.js',
        ];
        if (false && \common\helpers\Utils::isMobile()) {
            $this->css[] = "{$easyUIFolder}/themes/mobile.css";
            $this->js[] = "{$easyUIFolder}/jquery.easyui.mobile.js";
        }
        parent::init();
    }
    
    public function publish($am)
    {
    }
}
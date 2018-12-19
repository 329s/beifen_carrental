<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\helpers;

/**
 * Description of BootstrapDatetimeAsset
 *
 * @author kevin
 */
class BootstrapDatetimeAsset extends \yii\web\AssetBundle {
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
        
        $lanLocale = str_replace('_', '-', \Yii::$app->params['lan_locale']);
        
        $this->css = [
            'extensions/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',
        ];
        $this->js = [
            'extensions/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js',
            "extensions/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.{$lanLocale}.js",
        ];
        parent::init();
    }
    
    public function publish($am)
    {
    }
}

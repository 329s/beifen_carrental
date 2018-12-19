<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\helpers;

/**
 * Description of BootstrapAdminAsset
 *
 * @author kevin
 */
class BootstrapAdminAsset extends \yii\web\AssetBundle {
    public $basePath = '@webroot';
    public $baseUrl = '';

    public $css = [
    ];
    public $js = [
    ];
    
    public function init()
    {
        $urlRoot = \common\helpers\Utils::getRootUrl();
        $this->baseUrl = $urlRoot . 'assets';
        
        //$lanLocale = str_replace('_', '-', \Yii::$app->params['lan_locale']);
        
        $this->css = [
            //'extensions/font-awesome-4.7.0/css/font-awesome.min.css',
            //'extensions/ionicons-2.0.1/css/ionicons.min.css',
            'extensions/bootstrap-treeview/css/bootstrap-treeview.css',
            'extensions/admin-lte/plugins/daterangepicker/daterangepicker.css',
            'extensions/admin-lte/plugins/datepicker/datepicker3.css',
            //'extensions/admin-lte/plugins/iCheck/all.css',
            'extensions/admin-lte/plugins/timepicker/bootstrap-timepicker.min.css',
            'extensions/admin-lte/css/AdminLTE.min.css',
            'extensions/admin-lte/css/skins/_all-skins.min.css',
            'extensions/admin-lte/plugins/pace/pace.min.css',
        ];
        $this->js = [
            'extensions/admin-lte/plugins/pace/pace.min.js',
            'extensions/admin-lte/plugins/select2/select2.full.min.js',
            'extensions/admin-lte/plugins/input-mask/jquery.inputmask.js',
            'extensions/admin-lte/plugins/input-mask/jquery.inputmask.date.extensions.js',
            'extensions/admin-lte/plugins/input-mask/jquery.inputmask.extensions.js',
            'extensions/moment/moment.min.js',
            'extensions/admin-lte/plugins/daterangepicker/daterangepicker.js',
            'extensions/admin-lte/plugins/datepicker/bootstrap-datepicker.js',
            'extensions/admin-lte/plugins/timepicker/bootstrap-timepicker.min.js',
            'extensions/admin-lte/plugins/slimScroll/jquery.slimscroll.min.js',
            'extensions/admin-lte/plugins/fastclick/fastclick.js',
            //'extensions/admin-lte/plugins/iCheck/icheck.min.js',
            'extensions/admin-lte/js/app.min.js',
            'extensions/admin-lte/plugins/slimScroll/jquery.slimscroll.min.js',
            //'extensions/admin-lte/js/demo.js',
            'extensions/bootstrap-treeview/js/bootstrap-treeview.js',
        ];
        parent::init();
    }
    
    public function publish($am)
    {
    }
}

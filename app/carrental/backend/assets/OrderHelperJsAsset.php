<?php

namespace backend\assets;

/**
 * 订单相关 javascript 脚本资源
 */
class OrderHelperJsAsset extends \yii\web\AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //'css/agreement.css',
    ];
    public $js = [
        '@web/js/orderhelper.js',
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
        'type' => 'text/javascript',
    ];
    public $depends = [
    ];
}


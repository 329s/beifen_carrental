<?php

namespace backend\assets;

/**
 * 订单预览样式
 */
class AgreementAsset extends \yii\web\AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/agreement.css',
    ];
    public $js = [
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
        'type' => 'text/javascript',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'common\helpers\BarcodeGeneratorAssets',
    ];
}


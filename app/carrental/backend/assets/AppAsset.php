<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
        'js/notification.js'
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
        'type' => 'text/javascript',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'common\helpers\KindeditorAsset',
        //'common\helpers\EasyUIAsset',
        'common\helpers\BootstrapAdminAsset',
        'common\helpers\ExtendAsset',
        'common\helpers\HighchartsAsset',
        'common\helpers\PrintPreviewAsset',
        'common\helpers\JQueryValidationAsset',
        'common\helpers\JQueryFormAsset',
        'common\helpers\BootstrapDatetimeAsset',
        'yii\validators\ValidationAsset',
        'yii\widgets\ActiveFormAsset',
        'common\helpers\MapGaodeAsset',
    ];
}

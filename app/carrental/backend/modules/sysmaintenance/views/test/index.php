<?php

// container
use common\helpers\CMyHtml;

\yii\grid\GridViewAsset::register($this);

$targetId = \common\helpers\BootstrapHtml::MAIN_CONTENT_ID;
$accordionArray = [
    [
        'name' => '测试一',
        'data' => [
            [
                'name' => '测试API',
                'icon' => 'icon-bug',
                'type' => 'navTab',
                'url' => \yii\helpers\Url::to(['test/outer_api']),
                'target' => $targetId,
            ],
            [
                'name' => '测试农历时间',
                'icon' => 'icon-bug',
                'type' => 'navTab',
                'url' => \yii\helpers\Url::to(['test/lunar_calendar']),
                'target' => $targetId,
            ],
            [
                'name' => '测试发送短信',
                'icon' => 'icon-bug',
                'type' => 'navTab',
                'url' => \yii\helpers\Url::to(['test/send_sms']),
                'target' => $targetId,
            ],
        ],
    ],
    [
        'name' => '测试充值回调',
        'data' => [
            [
                'name' => '支付回调测试',
                'icon' => 'icon-bug',
                'type' => 'navTab',
                'url' => \yii\helpers\Url::to(['test/paymentcallback']),
                'target' => $targetId,
            ],
        ],
    ],
    [
        'name' => '测试二',
        'data' => [
            [
                'name' => '测试获取车型列表',
                'icon' => 'icon-bug',
                'type' => 'navTab',
                'url' => \yii\helpers\Url::to(['test/api_car_list']),
                'target' => $targetId,
            ],
            /*[
                'name' => '测试下单预览',
                'icon' => 'icon-bug',
                'type' => 'navTab',
                'url' => \yii\helpers\Url::to(['test/api_order_preview']),
                'target' => $targetId,
            ],*/
        ],
    ],
    [
        'name' => '测试地图',
        'data' => [
            [
                'name' => '测试地图两点距离',
                'icon' => 'icon-bug',
                'type' => 'navTab',
                'url' => \yii\helpers\Url::to(['test/distance']),
                'target' => $targetId,
            ],
        ],
    ],
    [
        'name' => '测试新功能',
        'data' => [
            [
                'name' => '测试金点子系统',
                'icon' => 'icon-bug',
                'type' => 'navTab',
                'url' => \yii\helpers\Url::to(['/splendididea/index']),
                'target' => $targetId,
            ],
        ],
    ],
];

$accordionOptions = ['title' => Yii::t('locale', 'Main Menu')];
$leftPart = \common\helpers\BootstrapAdminHtml::accordionList($accordionArray, $accordionOptions);

$containerPart = CMyHtml::beginMainPageTabs(['id'=>$targetId, 'fit'=>'true', 'border'=>'false', 'style'=>"width:100%;height:100%", 
    'tabs'=>[]]);
$_contentArr = [];
$containerPart .= implode("\n", $_contentArr);
$containerPart .= "\n".CMyHtml::endMainPageTabs();

$homePageUrl = \yii\helpers\Url::to(['/site/homepanel']);

$htmlArray = [];
$htmlArray[] = \common\helpers\BootstrapAdminHtml::beginSidebar();

$userName = \backend\components\AdminModule::getCurUserName();
// user part
$rootUrl = \common\helpers\Utils::getRootUrl();
$htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'user-panel']);
$htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'pull-left image']);
$htmlArray[] = \yii\helpers\Html::img("{$rootUrl}assets/images/user/user2-160x160.jpg", ['class'=>'img-circle', 'alt'=>$userName]);
$htmlArray[] = \yii\helpers\Html::endTag('div');
$htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'pull-left info']);
$htmlArray[] = \yii\helpers\Html::tag('p', $userName);
$htmlArray[] = \yii\bootstrap\Html::tag('a', \yii\helpers\Html::tag('i', '', ['class'=>'fa fa-circle text-success']).' '.Yii::t('locale', 'Online'), ['href'=>'#']);
$htmlArray[] = \yii\helpers\Html::endTag('div');
$htmlArray[] = \yii\helpers\Html::endTag('div');

$htmlArray[] = $leftPart;
$htmlArray[] = \common\helpers\BootstrapAdminHtml::endSidebar();

$htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'content-wrapper']);
$contentHeader0 = <<<EOD
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
        <small>Version 2.0</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
EOD;
$contentHeader1 = "</section>";
//$htmlArray[] = $contentHeader0;
$htmlArray[] = \yii\helpers\Html::beginTag('div', [
    'class'=>'content', 'id'=>$targetId, 
    'style'=>"overflow:auto;"
    ]);
//$htmlArray[] = $containerPart;
//$htmlArray[] = $contentHeader1;
$htmlArray[] = \yii\helpers\Html::endTag('div');
$htmlArray[] = \yii\helpers\Html::endTag('div');

$scripts = [];
$scripts[] = \common\helpers\BootstrapAdminHtml::getRegisterFixFluidWidowLayoutJs($targetId);
$scripts[] = "$(function () {";
$scripts[] = "$.custom.bootstrap.loadElement('#{$targetId}', '$homePageUrl');";
$scripts[] = "});";
$this->registerJs(implode("\n", $scripts));

echo implode("\n", $htmlArray);

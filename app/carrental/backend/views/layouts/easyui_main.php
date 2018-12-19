<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

\backend\assets\AppWithEasyUIAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1, user-scalable=no">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode(Yii::$app->params['app.management.name']) ?></title>
    <link type="image/x-icon" href="<?= \common\helpers\Utils::getRootUrl().'favicon.ico' ?>" rel="shortcut icon">
    <?php $this->head() ?>
</head>
<body class="easyui-layout">
<?php $this->beginBody() ?>
<?php
$arrScripts = [""];
$arrScripts[] = "$(function(){ $.custom.uiframework = 'easyui';});";

if (isset(Yii::$app->params['tabs_in_iframe'])) {
    if (Yii::$app->params['tabs_in_iframe']) {
        $arrScripts[] = "if ($.custom.easyui.config) { $.custom.easyui.config.openTabInIframe = true; }";
    }
}

$arrScripts[] = <<<EOD
var loadingDelayTimer;
function navTabShowLoading(){
    if ($("#loading")) {
        $("#loading").fadeOut("normal", function(){
            $(this).remove();
            loadingDelayTimer = undefined;
        });
    }
}

$.parser.onComplete = function(){
    if($.custom.easyui.loadingDelayTimer) {
        clearTimeout(loadingDelayTimer);
    }
    loadingDelayTimer = setTimeout(navTabShowLoading,500);
}
EOD;

echo Html::script(implode("\n", $arrScripts));

$urlRoot = \common\helpers\Utils::getRootUrl();
$logoUrl = "{$urlRoot}assets/images/logo/yikazc.png";
$arrAdminInfo = \backend\components\AdminHtmlService::getAdminDisplayInfoArray();
?>
    <div data-options="region:'north',border:false" style="height:60px;">
<div class="wrap-main-header">
    <?php
    NavBar::begin([
        'brandLabel' => '', // Yii::$app->params['app.management.name'],
        //'brandUrl' => Yii::$app->getHomeUrl(),
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
            'style' => "width:100%;height:60px",
        ],
        'innerContainerOptions' => [
            'class' => '',
            'style' => 'margin-left:24px;margin-right:24px;',
        ],
        'brandOptions' => [
            'style' => "color:yellow;font-weight:bold;text-shadow:1px 1px 1px #eeeeee; padding-left:269px; background-image:url('{$logoUrl}') ;background-repeat:no-repeat; background-position-x: 0; background-position-y: 12px; background-size:auto 36px;",
        ]
    ]);
    
    $menuItems = [
        ['label' => Yii::t('locale', 'Home'), 'url' => ['/site/index']],
    ];
    //$noticeItems = \backend\components\AdminHtmlService::getNavTabNoticeItems($arrAdminInfo);
    //foreach ($noticeItems as $item) {
    //    $menuItems[] = $item;
    //}
    $menuItems[] = \backend\components\AdminHtmlService::getNavTabUserItem($arrAdminInfo);
    /*if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => Yii::t('locale', 'Login'), 'url' => ['/site/login']];
    } else {
        $menuItems[] = [
            'label' => Yii::t('locale', 'Logout') . ' (' . Yii::$app->user->identity->username . ')',
            'url' => ['/site/logout'],
            'linkOptions' => ['data-method' => 'post']
        ];
        
        $menuItems[] = [
            'label' => \backend\components\AdminModule::getCurAuthRoleDisplayName(),
        ];
    }*/
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>
</div>
    </div>
    <div data-options="region:'south'" style="height:30px;">
<footer class="footer" style="height:30px">
    <div class="container">
        <p class="pull-left">&copy; <?= Yii::$app->params['app.copyright.name'] ?> <?= date('Y') ?></p>

        <p class="pull-right"><?= 'Powered by '.Html::tag('span', Yii::$app->params['app.copyright.name'], ['style'=>"color: #FF9900"]). " Copyright &copy; 2016 , All Rights Reserved." ?></p>
    </div>
</footer>
    </div>
    <?= $content ?>


<?php $this->endBody() ?>
</body>
<script type="text/javascript">
$(function(){
    setTimeout(function(){ $.carrental.notifications.urlCheck = '<?= yii\helpers\Url::to(['/notification/check']) ?>'; }, 100);
});
</script>
</html>
<?php $this->endPage() ?>

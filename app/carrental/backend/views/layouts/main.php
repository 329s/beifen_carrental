<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

AppAsset::register($this);
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
<script>
window.paceOptions = {
    ajax:{
        trackMethods: ['GET', 'POST'],
        ignoreURLs: [/notification/]
    }
};
</script>
    <?php $this->head() ?>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed" style="overflow: hidden">
<?php $this->beginBody() ?>
<?php

$arrAdminInfo = \backend\components\AdminHtmlService::getAdminDisplayInfoArray();

?>
<div class="wrapper" style="width:100%;height:100%">

    <header class="main-header">
    <!-- Logo -->
    <a href="javascript:void(0);" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src="<?= $arrAdminInfo['logoMiniUrl'] ?>" style="width:auto;height:36px;" /></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img src="<?= $arrAdminInfo['logoUrl'] ?>" style="width:auto;height:36px;" /></span>
    </a>
        
    <?php
    \common\widgets\NavBarAdminWidget::begin([
        //'brandLabel' => '', // Yii::$app->params['app.management.name'],
        //'brandUrl' => Yii::$app->getHomeUrl(),
        'options' => [
            'class' => 'navbar navbar-static-top',
        ],
        'innerContainerOptions' => [
            'class' => 'navbar-custom-menu',
        ],
    ]);
    
    $menuItems = [];
    $noticeItems = \backend\components\AdminHtmlService::getNavTabNoticeItems($arrAdminInfo);
    foreach ($noticeItems as $item) {
        $menuItems[] = $item;
    }
    $menuItems[] = \backend\components\AdminHtmlService::getNavTabUserItem($arrAdminInfo);
    $menuItems[] = [
        'label' => \yii\bootstrap\Html::tag('i', '', ['class'=>'fa fa-gears']),
        'linkOptions' => ['data-toggle'=>'control-sidebar'],
        'encode'=>false,
    ];
    
    echo Nav::widget([
        'options' => ['class' => 'nav navbar-nav'],
        'items' => $menuItems,
        'dropDownCaret' => '',
    ]);
    \common\widgets\NavBarAdminWidget::end();
    ?>
    </header>
    <!-- <div class="content-wrapper" style="margin: 0px;"> -->
    <?= $content ?>
    <!-- </div> -->
<footer class="main-footer" style="height:30px;padding-top: 6px;">
    <div class="pull-right hidden-xs">
      &copy; <?= Yii::$app->params['app.copyright.name'] ?> <?= date('Y') ?>
    </div>
    <strong><?= 'Powered by '.Html::tag('span', Yii::$app->params['app.copyright.name'], ['style'=>"color: #FF9900"]). " Copyright &copy; 2017." ?></strong> All Rights Reserved.
</footer>

  <?= \backend\components\AdminHtmlService::renderControlSidebar() ?>
  
</div><!-- ./wrapper -->
<script type="text/javascript">
// To make Pace works on Ajax calls

//$(document).ajaxStart(function() {
//    Pace.restart();
//});
$(function(){
    setTimeout(function(){ $.carrental.notifications.urlCheck = '<?= yii\helpers\Url::to(['/notification/check']) ?>'; }, 100);
});
</script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

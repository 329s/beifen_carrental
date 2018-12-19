<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;

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
    <title><?= Html::encode(Yii::$app->params['app.management.name'].' - '.Yii::t('locale', 'System maintenance')) ?></title>
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

$urlRoot = \common\helpers\Utils::getRootUrl();
$logoMiniUrl = "{$urlRoot}assets/images/logo/logo_yika_48x48.png";
$logoUrl = "{$urlRoot}assets/images/logo/yikazc.png";

$adminPhotoUrl = "{$urlRoot}assets/images/user/user2-160x160.jpg";
$authOfficeName = \backend\components\AdminModule::getAdminAuthOfficeDisplayName();
if (Yii::$app->user->isGuest) {
    $adminName = Yii::t('locale', 'Not signed in');
    $adminRole = Yii::t('locale', 'Not signed in');
    $adminAvatarUrl = "{$urlRoot}assets/images/user/avatar04.png";
}
else {
    $adminName = \Yii::$app->user->identity->username;
    $adminRole = \backend\components\AdminModule::getCurAuthRoleDisplayName();
    $adminAvatarUrl = "{$urlRoot}assets/images/user/avatar5.png";
}
?>
<div class="wrapper" style="width:100%;height:100%">

    <header class="main-header">
    <!-- Logo -->
    <a href="javascript:void(0);" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src="<?= $logoMiniUrl ?>" style="width:auto;height:36px;" /></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img src="<?= $logoUrl ?>" style="width:auto;height:36px;" /></span>
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
    
    $menuItems = [
        //['label' => Yii::t('locale', 'Home'), 'url' => ['/site/index']],
    ];
    // Messages
    $newMessages = [
        ['title' => Yii::t('carrental', 'Universal notification'), 'time' => Yii::t('locale', '{num} mins', ['num'=>5]),
            'desc'=> Yii::t('carrental', 'This is a normal notification'),
            'image'=>['src'=>$adminPhotoUrl, 'options'=>['alt'=>$adminRole]],
            'linkOptions' => [],
        ],
    ];
    $newMessageHtmls = [];
    foreach ($newMessages as $msgInfo) {
        $newMessageHtmls[] = \common\helpers\BootstrapAdminHtml::renderNotificationMessageElement(['title'=>$msgInfo['title'], 'time'=>$msgInfo['time'], 'description'=>$msgInfo['desc'], 'image'=>$msgInfo['image'], 'linkOptions'=>$msgInfo['linkOptions']]);
    }
    $messageItem = [
        'label' => \yii\bootstrap\Html::tag('i', '', ['class'=>'fa fa-envelope-o']).\yii\bootstrap\Html::tag('span', empty($newMessages) ? '' :count($newMessages), ['class'=>'label label-success']),
        'options' => ['class'=>'dropdown messages-menu'],
        //'linkOptions' => ['class'=>'dropdown-toggle'],
        'dropDownOptions' => ['class'=>'dropdown-menu'],
        'items' => [
            \yii\helpers\Html::tag('li', \Yii::t('locale', 'You have {number} {names}', ['number'=>count($newMessages), 'names'=>\Yii::t('locale', 'messages')]), ['class'=>'header']),
            \yii\bootstrap\Html::tag('li', \yii\bootstrap\Html::tag('ul', implode("\n", $newMessageHtmls), ['class'=>'menu'])),
            ['label'=> Yii::t('locale', 'See all {names}', ['names'=>\Yii::t('locale', 'messages')]), 'options'=>['class'=>'footer']],
        ],
        'encode' => false,
    ];
    $menuItems[] = $messageItem;
    
    // Notifications
    $newNotifications = [
        ['message'=>'The notification center is waiting to be completed', 'icon'=>'fa-users text-aqua',
            'linkOptions' => [],]
    ];
    $newNotificationHtmls = [];
    foreach ($newNotifications as $msgInfo) {
        $newNotificationHtmls[] = \common\helpers\BootstrapAdminHtml::renderNotificationMessageElement(['message'=>$msgInfo['message'], 'icon'=>$msgInfo['icon'], 'linkOptions'=>$msgInfo['linkOptions']]);
    }
    $notificationItem = [
        'label' => \yii\bootstrap\Html::tag('i', '', ['class'=>'fa fa-bell-o']).\yii\bootstrap\Html::tag('span', empty($newNotifications) ? '' :count($newNotifications), ['class'=>'label label-warning']),
        'options' => ['class'=>'dropdown notifications-menu'],
        //'linkOptions' => ['class'=>'dropdown-toggle'],
        'dropDownOptions' => ['class'=>'dropdown-menu'],
        'items' => [
            \yii\helpers\Html::tag('li', \Yii::t('locale', 'You have {number} {names}', ['number'=>count($newNotifications), 'names'=>\Yii::t('locale', 'notifications')]), ['class'=>'header']),
            \yii\bootstrap\Html::tag('li', \yii\bootstrap\Html::tag('ul', implode("\n", $newNotificationHtmls), ['class'=>'menu'])),
            ['label'=> Yii::t('locale', 'See all {names}', ['names'=>\Yii::t('locale', 'notifications')]), 'options'=>['class'=>'footer']],
        ],
        'encode' => false,
    ];
    $menuItems[] = $notificationItem;
    
    // Tasks
    $newTasks = [
        ['title'=>'The task center is waiting to be completed', 'percent'=>20, 'color'=>'aqua']
    ];
    $newTaskHtmls = [];
    foreach ($newTasks as $msgInfo) {
        $newTaskHtmls[] = \yii\bootstrap\Html::tag('li', 
            \yii\bootstrap\Html::tag('a', 
            \yii\bootstrap\Html::tag('h3', $msgInfo['title'].\yii\bootstrap\Html::tag('small', "{$msgInfo['percent']}%", ['class'=>'pull-right'])).common\helpers\BootstrapAdminHtml::progressBar($msgInfo['percent'], isset($msgInfo['options']) ? $msgInfo['options']:[])
            , ['href'=>'#'])
        );
    }
    $taskItem = [
        'label' => \yii\bootstrap\Html::tag('i', '', ['class'=>'fa fa-flag-o']).\yii\bootstrap\Html::tag('span', empty($newTasks) ? '' :count($newTasks), ['class'=>'label label-danger']),
        'options' => ['class'=>'dropdown tasks-menu'],
        //'linkOptions' => ['class'=>'dropdown-toggle'],
        'dropDownOptions' => ['class'=>'dropdown-menu'],
        'items' => [
            \yii\helpers\Html::tag('li', \Yii::t('locale', 'You have {number} {names}', ['number'=>count($newTasks), 'names'=>\Yii::t('locale', 'tasks')]), ['class'=>'header']),
            \yii\bootstrap\Html::tag('li', \yii\bootstrap\Html::tag('ul', implode("\n", $newTaskHtmls), ['class'=>'menu'])),
            ['label'=> Yii::t('locale', 'See all {names}', ['names'=>\Yii::t('locale', 'tasks')]), 'options'=>['class'=>'footer']],
        ],
        'encode' => false,
    ];
    $menuItems[] = $taskItem;
    
    $userInfoRows = [];
    $userInfoRows[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::tag('a', Yii::t('locale', 'Office'), ['href'=>'#']), ['class'=>'col-xs-6 text-center']);
    $userInfoRows[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::tag('a', $authOfficeName, ['href'=>'#']), ['class'=>'col-xs-6 text-center']);
    $userFooters = [];
    if (Yii::$app->user->isGuest) {
        $userFooters[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::a(Yii::t('locale', 'Login'), yii\helpers\Url::to(['site/login']), ['class'=>'btn btn-default btn-flat']), ['class'=>'pull-right']);
    }
    else {
        $userFooters[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::a(Yii::t('locale', 'Profile'), '#', ['class'=>'btn btn-default btn-flat']), ['class'=>'pull-left']);
        $userFooters[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::a(Yii::t('locale', 'Logout'), yii\helpers\Url::to(['site/logout']), ['data-method' => 'post', 'class'=>'btn btn-default btn-flat']), ['class'=>'pull-right']);
    }
    
    $userItem = [
        'label' => \yii\bootstrap\Html::img($adminAvatarUrl, ['class'=>'user-image', 'alt'=>$adminName]).\yii\bootstrap\Html::tag('span', Yii::$app->user->isGuest?Yii::t('locale', 'Login'):$adminName, ['class'=>'hidden-xs']),
        'options' => ['class'=>'dropdown user user-menu'],
        'dropDownOptions' => ['class'=>'dropdown-menu'],
        'items' => [
            \yii\helpers\Html::tag('li', \yii\bootstrap\Html::img($adminPhotoUrl, ['class'=>'img-circle', 'alt'=>$adminName]).
                \yii\bootstrap\Html::tag('p', $adminName . \yii\bootstrap\Html::tag('small', (Yii::$app->user->isGuest?'':$adminRole))), 
                ['class'=>'user-header']),
            \yii\helpers\Html::tag('li', \yii\bootstrap\Html::tag('div', implode("\n", $userInfoRows), ['class'=>'row']), ['class'=>'user-body']),
            \yii\helpers\Html::tag('li', implode("\n", $userFooters), 
                ['class'=>'user-footer']),
        ],
        'encode' => false,
    ];
    $menuItems[] = $userItem;

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
</script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

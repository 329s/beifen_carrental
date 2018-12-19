<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => \Yii::$app->params['app.company.name'],
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        ['label' => \Yii::t('locale', 'Home'), 'url' => ['/site/index']],
        ['label' => \Yii::t('locale', 'About'), 'url' => ['/site/about']],
        ['label' => \Yii::t('locale', 'Contact us'), 'url' => ['/site/contact']],
    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => \Yii::t('locale', 'Signup'), 'url' => ['/site/signup']];
        $menuItems[] = ['label' => \Yii::t('locale', 'Login'), 'url' => ['/site/login']];
    } else {
        $menuItems[] = [
            'label' => 'Logout (' . Yii::$app->user->identity->account . ')',
            'url' => ['/site/logout'],
            'linkOptions' => ['data-method' => 'post']
        ];
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    
    $requestedAction = \Yii::$app->requestedAction;
    if ($requestedAction->controller->id == 'site' && $requestedAction->id == 'index') {
        echo \frontend\widgets\ActivitiesCarouselWidget::widget();
    }
    
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= \Yii::$app->params['app.company.name'] ?> <?= date('Y') ?></p>

        <p class="pull-right"><?= \Yii::t('yii', 'Powered by {yii}', [
            'yii' => '<a href="#" rel="external">' . \Yii::$app->params['app.copyright.name'] . '</a>'
        ]) ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

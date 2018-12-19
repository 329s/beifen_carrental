<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;

$urlRoot = \common\helpers\Utils::getRootUrl();

$urlCssFile = \yii\helpers\Url::to(['css/login/style.css']);

$myAssetsRoot = "{$urlRoot}app/carrental/backend/web/";

?>
<link rel="stylesheet" href="<?= "{$myAssetsRoot}css/login/style.css" ?>">
<?= Html::style("#loginweb { background:url({$myAssetsRoot}images/login/bg.png) no-repeat; }\n".
    ".btnbg { background:url({$myAssetsRoot}images/login/btnbg.png) no-repeat; }\n".
    "#login-banner-carousel1 .carousel-inner { height:218px; }\n".
    "#login-banner-carousel1 .carousel-inner img { height:218px; }\n".
    ".logdv .help-block { margin-top:-12px; }\n"
) ?>

<div id="loginweb">
<p style="height:180px;"></p>
<p align="center"><img src="<?= $myAssetsRoot ?>images/login/logzi.png" /></p>
<p style="height:40px;"></p>
<div class="login">
    <div class="banner">
        <?= \yii\helpers\Html::cssFile("{$urlRoot}assets/ui/bootstrap/extensions/carousel.css") ?>
        <?= \yii\bootstrap\Carousel::widget([
            'items' => [
                ['content'=>\yii\helpers\Html::img("{$myAssetsRoot}images/login/banner1.jpg", [])],
                ['content'=>\yii\helpers\Html::img("{$myAssetsRoot}images/login/banner2.jpg", [])],
                ['content'=>\yii\helpers\Html::img("{$myAssetsRoot}images/login/banner3.jpg", [])],
            ],
            'options' => ['style'=>'height:218px;width:368px;', 'id'=>"login-banner-carousel1"],
        ]) ?>
    </div>
    <div class="logmain">
        <h1 style="height:16px;line-height: 16px;">&nbsp;</h1>
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <div class="logdv" style="height:40px;">
            <?= $form->field($model, 'username', ['inputOptions'=>['class' => 'ipt']]) ?>
        </div>
        <div class="logdv" style="height:40px;">
            <?= $form->field($model, 'password', ['inputOptions'=>['class' => 'ipt']])->passwordInput() ?>
        </div>
        <div class="logdv">
            <p class="logzi">&nbsp;</p>
            <a href="#" class="more">忘记密码？</a>
            <?= $form->field($model, 'rememberMe')->checkbox() ?>
        </div>
        <div class="logdv" style="height:40px;">
            <p class="logzi">&nbsp;</p>    
            <input name="提交" type="submit" class="btnbg" value="点击登录" />
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<p style="height:100px;"></p>
<p align="center">技术支持：<?= \Yii::$app->params['app.copyright.name'] ?></p>
</div>

<?php

/* @var $this yii\web\View */
/* @var $user common\models\Pub_user */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
Hello <?= $user->account ?>,

Follow the link below to reset your password:

<?= $resetLink ?>

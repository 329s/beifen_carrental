<?php

/* @var $this yii\web\View */
$idPrefix = \common\helpers\CMyHtml::getIDPrefix();
$autoId = \common\helpers\CMyHtml::genID();

echo \common\widgets\Tabs::widget([
    'enableFulidLayout' => true,
    'items' => [
        [
            'label' => \Yii::t('locale', '{name} authority', ['name' => \Yii::t('locale', 'Menu')]),
            'url' => yii\helpers\Url::to(['/rbac/permissions/menu-management']),
        ],
        [
            'label' => \Yii::t('locale', '{name} authority', ['name' => \Yii::t('locale', 'System')]),
            'url' => yii\helpers\Url::to(['/rbac/permissions/system-management']),
        ],
    ],
]);


<?php
/* @var $this yii\web\View */
$idPrefix = \common\helpers\CMyHtml::getIDPrefix();
$autoId = \common\helpers\CMyHtml::genID();

$searcherModel = new \backend\models\searchers\Searcher_pro_splendid_idea();
$items = [];
if (Yii::$app->user->can('splendididea/mainpage')) {
    $items[] = [
        'label' => Yii::t('carrental', 'Splendid idea'),
        'url' => yii\helpers\Url::to(array_merge($searcherModel->genUrlParams(Yii::$app->request->getQueryParams()), ['/splendididea/mainpage'])),
    ];
}
if (Yii::$app->user->can('splendididea/publish')) {
    $items[] = [
        'label' => Yii::t('carrental', 'I wanna publish'),
        'url' => yii\helpers\Url::to(['/splendididea/publish']),
    ];
}

echo \common\widgets\Tabs::widget([
    'enableFulidLayout' => true,
    'items' => $items,
]);

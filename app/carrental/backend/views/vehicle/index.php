<?php

$notices = backend\components\NoticeService::currentlyStatus();

function _arrayValue($arr, $key) {
    if (!isset($arr[$key])) {
        return '';
    }
    return $arr[$key] ? $arr[$key] : '';
}

$items = [];
if (true || \Yii::$app->user->can('vehicle/all-index')) {
    $items[] = [
        'label' => \Yii::t('carrental', 'All vehicle list'),
        'url' => yii\helpers\Url::to(['/vehicle/all-index']),
    ];
}
if (true || \Yii::$app->user->can('vehicle/recentlyrenewal-index')) {
    $items[] = [
        'label' => \Yii::t('carrental', 'Recently renewal of insurance').
        \yii\bootstrap\Html::tag('span', _arrayValue($notices, 'vehicle-renewal-count'), 
                ['class'=>'badge','id'=>'tabtips-vehicle-renewal-count']),
        'url' => yii\helpers\Url::to(['/vehicle/recentlyrenewal-index']),
    ];
}
if (true || \Yii::$app->user->can('vehicle/recentlyannual-index')) {
    $items[] = [
        'label' => \Yii::t('carrental', 'Recently annual inspection').
        \yii\bootstrap\Html::tag('span', _arrayValue($notices, 'vehicle-annual-inspection-count'), 
                ['class'=>'badge','id'=>'tabtips-vehicle-annual-inspection-count']),
        'url' => yii\helpers\Url::to(['/vehicle/recentlyannual-index']),
    ];
}
if (true || \Yii::$app->user->can('vehicle/periodicmaintenance-index')) {
    $items[] = [
        'label' => Yii::t('carrental', 'Recently periodic maintenance').
        \yii\bootstrap\Html::tag('span', _arrayValue($notices, 'vehicle-upkeep-by-mileage-count'), 
                ['class'=>'badge','id'=>'tabtips-vehicle-upkeep-by-mileage-count']),
        'url' => yii\helpers\Url::to(['/vehicle/periodicmaintenance-index']),
    ];
}
// if (true || \Yii::$app->user->can('vehicle/stagemaintenance-index')) {
//     $items[] = [
//         'label' => \Yii::t('carrental', 'Recently stage maintenance').
//         \yii\bootstrap\Html::tag('span', _arrayValue($notices, 'vehicle-upkeep-by-time-count'), 
//                 ['class'=>'badge','id'=>'tabtips-vehicle-upkeep-by-time-count']),
//         'url' => yii\helpers\Url::to(['/vehicle/stagemaintenance-index']),
//     ];
// }
if (true || \Yii::$app->user->can('vehicle/undermaintenance-index')) {
    $items[] = [
        'label' => \Yii::t('carrental', 'Under maintenance').
        \yii\bootstrap\Html::tag('span', _arrayValue($notices, 'vehicle-maintenance-count'), 
                ['class'=>'badge','id'=>'tabtips-vehicle-maintenance-count']),
        'url' => yii\helpers\Url::to(['/vehicle/undermaintenance-index']),
    ];
}
if (true || \Yii::$app->user->can('vehicle/saled-index')) {
    $items[] = [
        'label' => \Yii::t('carrental', 'Saled vehicles').
        \yii\bootstrap\Html::tag('span', _arrayValue($notices, 'vehicle-saled-count'), 
                ['class'=>'badge','id'=>'tabtips-vehicle-saled-count']),
        'url' => yii\helpers\Url::to(['/vehicle/saled-index']),
    ];
}

echo \common\widgets\Tabs::widget([
    'enableFulidLayout' => true,
    'items' => $items,
    'encodeLabels' => false,
]);

<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$dataProvider = new \yii\data\ArrayDataProvider([
    'allModels' => $logs,
    'modelClass' => '\common\models\Pro_vehicle_order_price_detail',
    'pagination' => [
        'pageSize' => count($logs),
    ],
]);

$model = new \common\models\Pro_vehicle_order_price_detail();
$keys = $model->attributes();

$skipKeys = ['id'=>1/*, 'order_id'=>1*/];

$paySourceFormatter = function($model, $key, $index, $column) {
    if (!isset($model[$column->attribute])) {
        return '';
    }
    return \common\components\OrderModule::getOrderPayTypeText($model[$column->attribute]);
};

$isYesFormatter = function($model, $key, $index, $column) {
    if (isset($model[$column->attribute]) && $model[$column->attribute]) {
        return '是';
    }
    return '';
};

$columns = [
    [
        'attribute' => 'order',
        'label' => '订单',
    ],
    [
        'attribute' => 'is_new',
        'label' => '是否新增',
        'value' => $isYesFormatter,
    ]
];
foreach ($keys as $k) {
    if ($k == 'time' || $k == 'created_at' || $k == 'updated_at') {
        $columns[] = [
            'attribute' => $k,
            'format' => ['date', 'php:Y-m-d'],
        ];
    }
    elseif ($k == 'type') {
        $columns[] = [
            'attribute' => $k,
            'value' => function ($model, $key, $index, $column) {
                if (!isset($model[$column->attribute])) return '';
                if ($model[$column->attribute] == \common\models\Pro_vehicle_order_price_detail::TYPE_SHOULD_PAY) {
                    return '需付';
                }
                elseif ($model[$column->attribute] == \common\models\Pro_vehicle_order_price_detail::TYPE_PAID) {
                    return '已付';
                }
                return '';
            },
        ];
    }
    elseif ($k == 'relet_mark') {
        $columns[] = [
            'attribute' => $k,
            'value' => $isYesFormatter,
        ];
    }
    elseif ($k == 'pay_source' || $k == 'deposit_pay_source') {
        $columns[] = [
            'attribute' => $k,
            'value' => $paySourceFormatter,
        ];
    }
    elseif (!isset($skipKeys[$k])) {
        $columns[] = $k;
    }
}

echo yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => $columns,
]);

if (!$isSave) {
    $url = \yii\helpers\Url::to(['sysmaintenance/fixpaymentdetail', 'is_save'=>1]);
    echo yii\bootstrap\Html::tag('div', 
        yii\bootstrap\Html::tag('div', 
            yii\bootstrap\Html::button('保存数据', ['class'=>'btn btn-primary', 'onclick'=>"easyuiFuncNavTabReloadCurTab('{$url}')"]), 
        ['class'=>'panel-body']), 
    ['class'=>'panel panel-default']);
    //echo yii\helpers\Html::button('', $columns)
}

<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$dataProvider = new \yii\data\ArrayDataProvider([
    'allModels' => $logs,
    'modelClass' => '\common\models\Pro_vehicle_fee_plan',
    'pagination' => [
        'pageSize' => count($logs),
    ],
]);

$model = new \common\models\Pro_vehicle_fee_plan();
$keys = $model->attributes();

$skipKeys = ['id'=>1/*, 'order_id'=>1*/];

$paySourceFormatter = function($model, $key, $index, $column) {
    if (!isset($model[$column->attribute])) {
        return '';
    }
    $arr = \common\models\Pro_vehicle_fee_plan::getSourceTypesArray();
    return isset($arr[$model[$column->attribute]]) ? $arr[$model[$column->attribute]] : '';
};

$isYesFormatter = function($model, $key, $index, $column) {
    if (isset($model[$column->attribute]) && $model[$column->attribute]) {
        return '是';
    }
    return '';
};

$columns = [
];
foreach ($keys as $k) {
    if ($k == 'created_at' || $k == 'updated_at') {
        $columns[] = [
            'attribute' => $k,
            'format' => ['date', 'php:Y-m-d H:i:s'],
        ];
    }
    elseif ($k == 'source') {
        $columns[] = [
            'attribute' => $k,
            'value' => $paySourceFormatter,
        ];
    }
    elseif ($k == 'status') {
        $columns[] = [
            'attribute' => $k,
            'value' => function($model, $key, $index, $column) {
                if (!isset($model[$column->attribute])) {
                    return '';
                }
                $arr = \common\models\Pro_vehicle_fee_plan::getStatusArray();
                return isset($arr[$model[$column->attribute]]) ? $arr[$model[$column->attribute]] : '';
            },
        ];
    }
    elseif ($k == 'vehicle_model_id') {
        $columns[] = [
            'attribute' => $k,
            'value' => function($model, $key, $index, $column) {
                return $model['model_name'];
            },
        ];
    }
    elseif ($k == 'office_id') {
        $columns[] = [
            'attribute' => $k,
            'value' => function($model, $key, $index, $column) {
                return $model['office_name'];
            },
        ];
    }
    elseif (!isset($skipKeys[$k])) {
        $columns[] = $k;
    }
}

echo \common\widgets\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => $columns,
]);

if (!$isSave) {
    $url = \yii\helpers\Url::to(['default/upgradevehiclefeeplan', 'is_save'=>1]);
    echo yii\bootstrap\Html::tag('div', 
        yii\bootstrap\Html::tag('div', 
            yii\bootstrap\Html::button('保存数据', ['class'=>'btn btn-primary', 'onclick'=>"$.custom.bootstrap.loadElement('#".\common\helpers\BootstrapHtml::MAIN_CONTENT_ID."', '{$url}')"]), 
        ['class'=>'panel-body']), 
    ['class'=>'panel panel-default']);
    //echo yii\helpers\Html::button('', $columns)
}

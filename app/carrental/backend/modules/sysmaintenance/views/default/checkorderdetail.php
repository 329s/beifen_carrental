<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$dataProvider = new \yii\data\ArrayDataProvider([
    'allModels' => $logs,
    //'modelClass' => '\common\models\Pro_vehicle_order_price_detail',
    'pagination' => [
        'pageSize' => count($logs),
    ],
]);

$cellOptionsFormatter = function($model, $key, $index, $column) {
    $field = $column->attribute;
    $opts = [];
    if (!isset($model[$field])) {
        $opts['class'] = 'danger';
    }
    else {
        $prefix = substr($field, 0, -1);
        // method1
        $values = [];
        for($i = 0; $i < 3; $i++) {
            $k = $prefix.$i;
            if (isset($model[$k])) {
                $values[] = $model[$k];
            }
        }
        $cmp = $model[$field];
        foreach ($values as $v) {
            if ($v != $cmp) {
                $opts['class'] = 'danger';
                break;
            }
        }
        
        // method2
        /*
        $idx = intval(substr($field, -1));
        if ($idx > 0) {
            $k = $prefix.($idx-1);
            if (isset($model[$k]) && $model[$field] != $model[$k]) {
                $opts['class'] = 'danger';
            }
        }
        $k = $prefix.($idx+1);
        if (isset($model[$k]) && $model[$field] != $model[$k]) {
            $opts['class'] = 'danger';
        }
         * 
         */
    }
    return $opts;
};

echo yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'order:text:订单',
        'start_time:datetime:开始时间',
        'end_time:datetime:结束时间',
        'customer:text:客户',
        'office:text:门店',
        [
            'attribute'=>'total_amount0',
            'label'=>'订单总价',
            'contentOptions'=>$cellOptionsFormatter,
        ],
        [
            'attribute'=>'total_amount1',
            'label'=>'详单总价',
            'contentOptions'=>$cellOptionsFormatter,
        ],
        [
            'attribute'=>'paid_amount0',
            'label'=>'订单已付金额',
            'contentOptions'=>$cellOptionsFormatter,
        ],
        [
            'attribute'=>'paid_amount1',
            'label'=>'详单已付金额',
            'contentOptions'=>$cellOptionsFormatter,
        ],
        [
            'attribute'=>'paid_amount2',
            'label'=>'支付统计金额',
            'contentOptions'=>$cellOptionsFormatter,
        ],
        [
            'attribute'=>'deposit_amount0',
            'label'=>'订单押金',
            'contentOptions'=>$cellOptionsFormatter,
        ],
        [
            'attribute'=>'deposit_amount1',
            'label'=>'详单押金',
            'contentOptions'=>$cellOptionsFormatter,
        ],
        [
            'attribute'=>'paid_deposit0',
            'label'=>'订单已付押金',
            'contentOptions'=>$cellOptionsFormatter,
        ],
        [
            'attribute'=>'paid_deposit1',
            'label'=>'详单已付押金',
            'contentOptions'=>$cellOptionsFormatter,
        ],
        [
            'attribute'=>'paid_deposit2',
            'label'=>'支付统计押金',
            'contentOptions'=>$cellOptionsFormatter,
        ],
    ],
]);


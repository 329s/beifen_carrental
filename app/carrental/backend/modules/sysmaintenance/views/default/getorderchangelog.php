<?php

$htmlArray = [];
$arrScripts = [];

//$autoId = \common\helpers\CMyHtml::genID();
$reloadUrl = \yii\helpers\Url::to(array_merge($filterModel->genUrlParams(), ['default/getorderchangelog', '_'=>time()]));
 
$cellOptionsFormatter = function ($model, $key, $index, $column) {
    $field = $column->attribute;
    $grid = $column->grid;
    $dataProvider = $grid->dataProvider;
    $models = $dataProvider->getModels();
    $opts = ['style'=>"word-break:keep-all; word-wrap:keep-all; white-space:nowrap;"];
    if (!isset($model[$field])) {
        $opts['class'] = 'danger';
    }
    else {
        if ($index > 0) {
            if ($models[$index-1][$field] != $model[$field]) {
                $opts['class'] = 'danger';
            }
        }
        if (isset($models[$index+1])) {
            if ($models[$index+1][$field] != $model[$field]) {
                $opts['class'] = 'danger';
            }
        }
    }
    return $opts;
};

$timeFieldFormatter = function ($model, $key, $index, $column) {
    $field = $column->attribute;
    if (!isset($model[$field])) {
        return '';
    }
    return date('Y-m-d H:i:s', $model[$field]);
};

$columns = [];
$model = new \common\models\Pro_vehicle_order_change_log();
foreach ($model->attributes() as $attr) {
    $col = null;
    if ($attr == 'updated_at') {
        continue;
    }
    elseif ($attr == 'created_at') {
        $col = [
            'attribute'=>$attr,
            //'label'=>$model->getAttributeLabel($attr),
            'value'=>$timeFieldFormatter,
            'contentOptions'=>['style'=>"word-break:keep-all; word-wrap:keep-all; white-space:nowrap;"],
        ];
    }
    elseif ($attr == 'id') {
        $col = $attr;
    }
    else {
        $col = [
            'attribute'=>$attr,
            //'label'=>$model->getAttributeLabel($attr),
            'contentOptions'=>$cellOptionsFormatter,
        ];
        
        $valueFormatter = $dataProvider->getAttributeFormatter($attr);
        if ($valueFormatter) {
            $col['value'] = $valueFormatter;
        }
    }
    if ($col) {
        $columns[] = $col;
    }
}

$bodyHtml = \common\widgets\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'filterModel' => $filterModel,
    'filterUrl' => $reloadUrl,
    'enableFulidLayout' => true,
]);

//$htmlArray[] = \common\helpers\BootstrapHtml::beginPanel("订单修改记录", ['body' => $bodyHtml]);
//$htmlArray[] = \common\helpers\BootstrapHtml::endPanel();

//$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

$htmlArray[] = $bodyHtml;
$htmlArray[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::a('导出', yii\helpers\Url::to(array_merge($filterModel->genUrlParams(), ['default/orderchangelog-export', '_'=>time()])), ['class'=>'btn btn-default']), ['class'=>'row']);

echo implode("\n", $htmlArray);

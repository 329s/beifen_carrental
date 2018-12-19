<?php

use common\helpers\CMyHtml;

if (!isset($action)) {
    $action = 'update';
}
$formTitle = '';

$objData = isset($objOrder) ? $objOrder : null;
if (!$objData) {
    $action = 'insert';
}

$orderId = ($objData ? $objData->id : 0);
$orderOrigionEndTime = ($objData ? $objData->new_end_time : 0);

$urlInfo = ['vehicle/expenditure_list', 'type'=>$type, 'vehicle_id'=>$vehicleId];
$columnFields = null;
$objModel = null;
$objForm = null;

if ($type == \common\models\Pro_vehicle_cost::TYPE_RENEWAL) {
    $columnFields = ['id', 'time', 'insurance_company', 'insurance_type', 'insurance_no', 'price', 'insurance_amount', 'remark', 'edit_user_id', 'created_at', 'updated_at'];
    $objModel = new \common\models\Pro_vehicle_insurance();
    $objForm = new \backend\models\Form_pro_vehicle_insurance();
}
elseif ($type == \common\models\Pro_vehicle_cost::TYPE_DESIGNATING) {
    $columnFields = ['id', 'time', 'driver', 'driver_fee', 'road_fee', 'parking_fee', 'fuel_fee', 'remark', 'edit_user_id', 'created_at', 'updated_at'];
    $objModel = new \common\models\Pro_vehicle_designating_cost();
    $objForm = new \backend\models\Form_pro_vehicle_designating_cost();
}
elseif ($type == \common\models\Pro_vehicle_cost::TYPE_OIL) {
    $columnFields = ['id', 'time', 'oil_label', 'oil_volume', 'amount', 'pay_type', 'purpose', 'mileage', 'oil_tanker', 'edit_user_id', 'created_at', 'updated_at'];
    $objModel = new \common\models\Pro_vehicle_oil_cost();
    $objForm = new \backend\models\Form_pro_vehicle_oil_cost();
}
else {
    $columnFields = ['id', 'name', 'cost_time', 'cost_price', 'remark', 'edit_user_id', 'created_at', 'updated_at'];
    $objModel = new \common\models\Pro_vehicle_cost();
    $objForm = new \backend\models\Form_pro_vehicle_cost();
    
    if ($type == \common\models\Pro_vehicle_cost::TYPE_UPKEEP
        || $type == \common\models\Pro_vehicle_cost::TYPE_VIOLATION) {
        array_splice($columnFields, 2, 0, ['bind_id']);
    }
}

$objModel->vehicle_id = $vehicleId;
$objModel->type = $type;

$arrCostTypes = \common\components\VehicleModule::getVehicleExpenditureTypesArray();
$dgTitle = (isset($arrCostTypes[$type]) ? $arrCostTypes[$type] : '');

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
    'saveUrl' => \yii\helpers\Url::to(['vehicle/expenditure_add', 'type'=>$type]),
    'updateUrl' => \yii\helpers\Url::to(['vehicle/expenditure_edit', 'type'=>$type]),
    'deleteUrl' => \yii\helpers\Url::to(['vehicle/expenditure_delete', 'type'=>$type]),
];

$headerHeight = 40;
$totalHeight = 472;
$width = 962;

$arrData = [
    [
        [$objVehicle->getAttributeLabel('plate_number'), $objVehicle->plate_number],
        [$objVehicleModel->getAttributeLabel('vehicle_model'), $objVehicleModel->vehicle_model],
    ],
];

$canAdd = \Yii::$app->user->can('vehicle/expenditure_add');
$canEdit = \Yii::$app->user->can('vehicle/expenditure_edit');

$toolbarArray = [
    $canAdd ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', []) : null,
    $canEdit ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', []) : null,
    \Yii::$app->user->can('vehicle/expenditure_delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', []) : null,
    ($canAdd || $canEdit) ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_ACCEPT, '', '') : null,
    ($canAdd || $canEdit) ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REJECT, '', '') : null,
];

$funcId = CMyHtml::genID(); 
$dgId = CMyHtml::getIDPrefix().'dg_'.CMyHtml::genID();

$containerPart = CMyHtml::datagrid($dgTitle, // $title
    $objModel,    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>$dgId, 'data-options' => ['onLoadSuccess'=>"funOnLoadDataSuccess{$funcId}"]],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$htmlArray = [];

$htmlArray[] = CMyHtml::beginMainPageLayoutRegion("{$width}px", "{$headerHeight}", '', 'north');
$htmlArray[] = \common\helpers\CMyHtml::beginPanel('', ['height'=>"{$headerHeight}"]);
$htmlArray[] = \yii\helpers\Html::style(".dv-table td {border:0; } .dv-label {font-weight:bold; color:#15428B; padding:5px 5px 5px 25px; }", ['type'=>'text/css']);
$htmlArray[] = \common\helpers\CMyHtml::beginTag('table', ['class'=>'dv-table', 'border'=>'0', 'style'=>'']);
$htmlArray[] = \common\helpers\CMyHtml::beginTag('tbody');

foreach ($arrData as $row) {
    $htmlArray[] = \common\helpers\CMyHtml::beginTag('tr');
    foreach ($row as $ele) {
        $htmlArray[] = \common\helpers\CMyHtml::tag('td', $ele[0], ['class'=>'dv-label']);
        $htmlArray[] = \common\helpers\CMyHtml::tag('td', $ele[1]);
    }
    $htmlArray[] = \common\helpers\CMyHtml::endTag('tr');
}

$htmlArray[] = \common\helpers\CMyHtml::endTag('tbody');
$htmlArray[] = \common\helpers\CMyHtml::endTag('table');
$htmlArray[] = \common\helpers\CMyHtml::endPanel();

$htmlArray[] = CMyHtml::endMainPageLayoutRegion();
$htmlArray[] = CMyHtml::beginMainPageLayoutRegion("{$width}px", ($totalHeight-$headerHeight).'px', '', 'center');
$htmlArray[] = $containerPart;
$htmlArray[] = CMyHtml::endMainPageLayoutRegion();

$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$orderOrigionEndTimeStr = date('Y-m-d H:i:s', $orderOrigionEndTime);

$minReleteTime = 3600*6;

$arrScripts = [];
$arrScripts[] = <<<EOD
function funOnLoadDataSuccess{$funcId}(data) {
}

setTimeout(function() {
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'queryParams', {type:{$type}, {$yiiCsrfKey}:'{$yiiCsrfToken}'});
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'modelName', '{$objForm->formName()}');
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {type:{$type}, vehicle_id:{$vehicleId}, name:'{$typeName}'});
}, 50);
EOD;

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);

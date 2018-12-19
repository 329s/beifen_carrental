<?php

use common\helpers\CMyHtml;

$urlInfo = ['vehicle/expenditure_list', 'type'=>$type, 'getall'=>1];
$columnFields = null;
$objModel = null;

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'plate_number', Yii::t('locale', 'Plate number'), '', []),
    
];

if ($type == \common\models\Pro_vehicle_cost::TYPE_RENEWAL) {
    $columnFields = ['id', 'vehicle_id', 'time', 'insurance_company', 'insurance_type', 'insurance_no', 'price', 'insurance_amount', 'remark', 'edit_user_id', 'created_at', 'updated_at'];
    $objModel = new \common\models\Pro_vehicle_insurance();
    
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'insurance_no', $objModel->getAttributeLabel('insurance_no'), '', []);
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_DATEBOX, ['start_time', 'end_time'], $objModel->getAttributeLabel('time'), '', ['searchOnChange'=>true]);
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, ['price_min', 'price_max'], $objModel->getAttributeLabel('price'), '', ['type'=>'numberbox']);
}
elseif ($type == \common\models\Pro_vehicle_cost::TYPE_DESIGNATING) {
    $columnFields = ['id', 'vehicle_id', 'time', 'driver', 'driver_fee', 'road_fee', 'parking_fee', 'fuel_fee'];
    $objModel = new \common\models\Pro_vehicle_designating_cost();
    
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'driver', $objModel->getAttributeLabel('driver'), '', []);
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_DATEBOX, ['start_time', 'end_time'], $objModel->getAttributeLabel('time'), '', ['searchOnChange'=>true]);
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, ['price_min', 'price_max'], $objModel->getAttributeLabel('driver_fee'), '', ['type'=>'numberbox']);
}
elseif ($type == \common\models\Pro_vehicle_cost::TYPE_OIL) {
    $columnFields = ['id', 'vehicle_id', 'time', 'oil_label', 'oil_volume', 'amount', 'pay_type', 'purpose', 'mileage', 'oil_tanker'];
    $objModel = new \common\models\Pro_vehicle_oil_cost();
    
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'oil_tanker', $objModel->getAttributeLabel('oil_tanker'), '', []);
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_DATEBOX, ['start_time', 'end_time'], $objModel->getAttributeLabel('time'), '', ['searchOnChange'=>true]);
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, ['price_min', 'price_max'], $objModel->getAttributeLabel('amount'), '', ['type'=>'numberbox']);
}
else {
    $columnFields = ['id', 'vehicle_id', 'name', 'cost_time', 'cost_price'];
    $objModel = new \common\models\Pro_vehicle_cost();
    
    if ($type == \common\models\Pro_vehicle_cost::TYPE_UPKEEP
        || $type == \common\models\Pro_vehicle_cost::TYPE_VIOLATION) {
        array_splice($columnFields, 2, 0, ['bind_id']);
    }
    
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_DATEBOX, ['start_time', 'end_time'], $objModel->getAttributeLabel('cost_time'), '', ['searchOnChange'=>true]);
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, ['price_min', 'price_max'], $objModel->getAttributeLabel('cost_price'), '', ['type'=>'numberbox']);
}

$objModel->type = $type;

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
];

$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_BUTTON, Yii::t('locale', 'Retrieval'), '', '', []);

$containerPart = CMyHtml::datagrid('', // $title
    $objModel,    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$htmlArray = [];

$htmlArray[] = $containerPart;

echo implode("\n", $htmlArray);
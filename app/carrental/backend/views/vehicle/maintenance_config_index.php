<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle_maintenance_config_item();
$formTitle = '';

$urlInfo = ['vehicle/maintenance_config_item_list', 'belong_id'=>$belongId];

if (isset($status)) {
    $urlInfo['status'] = $status;
}
else {
    $status = 0; 
}

$columnFields = ['id', 'type', 'value', 'status', 'reference_price', 'edit_user_id', 'created_at', 'updated_at'];

$objMaintanceItemModel = new \common\models\Pro_vehicle_maintenance_config_item();
$objModel = new \common\models\Pro_vehicle_maintenance_config();

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
    'saveUrl' => \yii\helpers\Url::to(['vehicle/maintenance_edit']),
    'updateUrl' => \yii\helpers\Url::to(['vehicle/maintenance_edit']),
    'deleteUrl' => \yii\helpers\Url::to(['vehicle/maintenance_delete']),
];

$dgId = CMyHtml::getIDPrefix().'dg_'.CMyHtml::genID();

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, null, null),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, null, null),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, null, null),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_ACCEPT, null, null),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REJECT, null, null),
];

if ($hideTool) {
    $toolbarArray = [];
}

$dgWidth = '100%';
$dgHeight = '100%';

if ($hideInfo) {
    $dgWidth = '960px';
    $dgHeight = '470px';
}

$containerPart = CMyHtml::datagrid(Yii::t('carrental', 'Maintenance input'), // $title
    $objMaintanceItemModel,    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    $dgWidth, $dgHeight,     // $width, $height
    ['id'=>$dgId],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$funcId = CMyHtml::genID();
$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$itemDefaultType = common\models\Pro_vehicle_maintenance_config_item::CHECKPOINT_TYPE_MILEAGE;
$itemDefaultStatus = common\models\Pro_vehicle_maintenance_config_item::STATUS_ENABLED;
$vehicleId = ($objVehicle ? $objVehicle->id : 0);

$htmlArray = [];
$arrScripts = [];

if ($hideInfo) {
    $htmlArray[] = $containerPart;
}
else {
    $totalHeight = 472;
    $headerHeight = 40;

    $objConfigModel = new \common\models\Pro_vehicle_maintenance_config();
    $columnFields = ['id', 'name', 'belong_brand'];
    $modelCustomFieldOptions = $objConfigModel->attributeCustomTypes();
    $arrDatagridColumns = [];
    foreach ($columnFields as $field) {
        if (isset($modelCustomFieldOptions[$field])) {
            $fieldOptions = ['field' => $field, 'title' => $objConfigModel->getAttributeLabel($field)];
            foreach ($modelCustomFieldOptions[$field] as $k => $v) {
                $fieldOptions[$k] = $v;
            }
            $arrDatagridColumns[] = $fieldOptions;
        }
    }
    $configDatagridData = [
        'columns' => $arrDatagridColumns,
        'url' => \yii\helpers\Url::to(['vehicle/maintenance_config_title_list']),
        'detailUrl' => \yii\helpers\Url::to(['vehicle/maintenance_config_index', 'hide_info'=>'true', 'hide_tool'=>'true']),
        'method' => 'get',
        'panelWidth' => 800,
        'idField' => 'id',
        'textField' => 'name',
        'pagination' => 'true',
    ];
    
    $inputChangeConfgHtml = \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_COMBOGRID, 
        '', ($objMaintenanceConfig ? $objMaintenanceConfig->id : ''), $configDatagridData, 
        ['single'=>true, 'style'=>"width:200px", 'editable'=>false,
            'onChange' => "funcOnChangeMaintenanceConifgSelected{$funcId}",
        ], '');
    
    $arrData = [];
    
    if ($objVehicle) {
        $headerHeight += 30;
        $arrData[] = [
            [$objVehicle->getAttributeLabel('plate_number'), $objVehicle->plate_number],
            [$objVehicleModel->getAttributeLabel('vehicle_model'), $objVehicleModel->vehicle_model],
        ];
    }
    
    $arrData[] = [
        [$objModel->getAttributeLabel('name'), ($objMaintenanceConfig ? $objMaintenanceConfig->name : '')],
        [Yii::t('carrental', '{operation} maintenance config', ['operation'=>Yii::t('locale', 'Change')]), $inputChangeConfgHtml],
    ];

    $width = '962px';

    $htmlArray[] = CMyHtml::beginMainPageLayoutRegion($width, "{$headerHeight}px", '', 'north');
    $htmlArray[] = \common\helpers\CMyHtml::beginPanel('', ['height'=>"{$headerHeight}px"]);
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
    $htmlArray[] = CMyHtml::beginMainPageLayoutRegion($width, ($totalHeight - $headerHeight)."px", '', 'center');
    $htmlArray[] = $containerPart;
    $htmlArray[] = CMyHtml::endMainPageLayoutRegion();

    $urlChangeMaintenanceConfigUrl = \yii\helpers\Url::to(['vehicle/change_maintenance_config']);

    $arrScripts[] = <<<EOD
function funcOnChangeMaintenanceConifgSelected{$funcId}(newValue, oldValue) {
    var params = {
        {$yiiCsrfKey}:'{$yiiCsrfToken}',
        action:'update',
        vehicle_id:{$vehicleId},
        upkeep_config_id:newValue
    };
    easyuiFuncAjaxSendData('{$urlChangeMaintenanceConfigUrl}', 'post', params);
}
EOD;
}

$arrScripts[] = <<<EOD
setTimeout(function() {
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'queryParams', {belong_id:{$belongId}, {$yiiCsrfKey}:'{$yiiCsrfToken}'});
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'modelName', '{$objForm->formName()}');
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {belong_id:{$belongId}, type:{$itemDefaultType}, status:{$itemDefaultStatus}});
}, 110);
EOD;

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);

<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle_maintenance_config();
$formTitle = Yii::t('carrental', '{operation} maintenance config', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('carrental', 'Add new'))]);

$objData = isset($objMaintenanceConfig) ? $objMaintenanceConfig : null;
if (!$objData) {
    $objData = new \common\models\Pro_vehicle_maintenance_config();
    $objData->belong_brand = $brandId;
    $objData->status = \common\models\Pro_vehicle_maintenance_config::STATUS_ENABLED;
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('name'),
        'label' => $objData->getAttributeLabel('name'),
        'value' => $objData->name,
        'htmlOptions' => ['required' => true],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('belong_brand'),
        'label' => $objData->getAttributeLabel('belong_brand'),
        'value' => $objData->belong_brand,
        'data' => \common\components\VehicleModule::getVehicleBrandsArray(),
        'htmlOptions' => ['required' => true, 'size' => '32', 'editable'=>'true', 'readonly'=>true],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => \common\components\VehicleModule::getCommonStatusArray(),
        'htmlOptions' => ['required' => true],
        'columnindex' => 2,
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit')];

$hiddenFields = ['action' => $action, 'vehicle_id' => $objVehicle->id];
if ($action == 'update' && $objMaintenanceConfig) {
    $hiddenFields['id'] = $objMaintenanceConfig->id;
    $hiddenFields[$objForm->fieldName('id')] = $objMaintenanceConfig->id;
}

$arrInputVehicleBasicInfo = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => $objVehicle->getAttributeLabel('plate_number'),
        'value' => $objVehicle->plate_number,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => $objVehicleModel->getAttributeLabel('vehicle_model'),
        'value' => $objVehicleModel->vehicle_model,
        'htmlOptions' => ['editable'=>false, 'readonly'=>true],
        'columnindex' => 1,
    ],
];

$autoId = CMyHtml::genID();

$objConfigModel = new \common\models\Pro_vehicle_maintenance_config();
$columnFields = ['id', 'name', 'belong_brand', 'status', 'edit_user_id', 'created_at', 'updated_at', 'detailed_info'];
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

$arrInputChangeConfig = [
    ['type' => CMyHtml::INPUT_COMBOGRID, 'name' => 'upkeep_config_id',
        'label' => Yii::t('carrental', '{operation} maintenance config', ['operation'=>($objMaintenanceConfig ? Yii::t('locale', 'Change') : Yii::t('locale', 'Select'))]),
        'value' => ($objMaintenanceConfig ? $objMaintenanceConfig->id : ''),
        'data' => $configDatagridData,
        'htmlOptions' => ['single'=>true, 'style'=>"width:200px", 'onChange' => "funcOnChangeMaintenanceConifgSelected{$autoId}", 'editable'=>false],
        'columnindex' => 0,
    ],
];

$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();
$vehicleId = ($objVehicle ? $objVehicle->id : 0);
$urlChangeMaintenanceConfigUrl = \yii\helpers\Url::to(['vehicle/change_maintenance_config']);

$promptTextId = CMyHtml::getIDPrefix()."desc_{$autoId}";

$htmlArray = [];

$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Vehicle basic info'), ['style'=>"width:100%;height:120px", 'footer'=>$promptTextId]);
$htmlArray[] = CMyHtml::form('', \yii\helpers\Url::to(['vehicle/maintenance_config_edit']), 'post', ['fit'=>true], $arrInputVehicleBasicInfo, [], []);
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::tag('div', Yii::t('carrental', 'You can use the methods below to configure the vehicle maintenance list.'), ['id'=>$promptTextId]);

$htmlArray[] = CMyHtml::beginTag('div', ['class'=>'easyui-accordion', 'style'=>"width:100%;height:200px"]);

$htmlArray[] = CMyHtml::beginTag('div', ['title'=>Yii::t('carrental', '{operation} maintenance config', ['operation'=>($objMaintenanceConfig ? Yii::t('locale', 'Change') : Yii::t('locale', 'Select'))]), 
    'data-options'=>"selected:true,iconCls:'icon-edit'", 'encode'=>false]);
$htmlArray[] = CMyHtml::form('', $urlChangeMaintenanceConfigUrl, 'post', ['fit'=>false], $arrInputChangeConfig, [], []);
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::beginTag('div', ['title'=>$formTitle, 
    'data-options'=>"iconCls:'icon-add'", 'encode'=>false]);
$htmlArray[] = CMyHtml::form('', \yii\helpers\Url::to(['vehicle/maintenance_config_edit']), 'post', [], $inputs, $buttons, $hiddenFields);
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::endTag('div');

$arrScripts = [];
$arrScripts[] = <<<EOD
function funcOnChangeMaintenanceConifgSelected{$autoId}(newValue, oldValue) {
    var params = {
        {$yiiCsrfKey}:'{$yiiCsrfToken}',
        action:'update',
        vehicle_id:{$vehicleId},
        upkeep_config_id:newValue
    };
    easyuiFuncAjaxSendData('{$urlChangeMaintenanceConfigUrl}', 'post', params);
}
EOD;

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);
<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle_brand();
$formTitle = Yii::t('locale', '{operation} vehicle brand', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = isset($objVehicleBrand) ? $objVehicleBrand : null;
if (!$objData) {
    $objData = new \common\models\Pro_vehicle_brand();
    $objData->belong_brand = $belongBrand;
    $objData->flag = \common\models\Pro_vehicle_brand::FLAG_ENABLED;
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('name'),
        'label' => $objData->getAttributeLabel('name'),
        'value' => $objData->name,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('belong_brand'),
        'label' => $objData->getAttributeLabel('belong_brand'),
        'value' => $objData->belong_brand,
        'data' => \common\components\VehicleModule::getVehicleBrandsArray(),
        'htmlOptions' => ['required' => true, 'size' => '32'],
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => $objForm->fieldName('flag'),
        'label' => $objData->getAttributeLabel('flag'),
        'value' => $objData->flag,
        'data' => \common\components\VehicleModule::getVehicleBrandFlagsArray(),
        'htmlOptions' => ['required' => true],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($action == 'update' && $objVehicleBrand) {
    $hiddenFields['id'] = $objVehicleBrand->id;
    $hiddenFields[$objForm->fieldName('id')] = $objVehicleBrand->id;
}


echo CMyHtml::form($formTitle, $saveUrl, 'post', [], $inputs, $buttons, $hiddenFields);

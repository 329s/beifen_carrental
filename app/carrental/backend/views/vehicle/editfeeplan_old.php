<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle_fee_plan();
$formTitle = Yii::t('locale', '{operation} vehicle fee plan', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = isset($objVehicleFeePlan) ? $objVehicleFeePlan : null;

if (!$objData) {
    $objData = new \common\models\Pro_vehicle_fee_plan();
}

$inputs = [
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('locale', 'Basic fee info')],
    /*['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('name'),
        'label' => $objData->getAttributeLabel('name'),
        'value' => $objData->name,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],*/
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('source'),
        'label' => $objData->getAttributeLabel('source'),
        'value' => $objData->source,
        'data' => \common\models\Pro_vehicle_fee_plan::getSourceTypesArray(),
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('vehicle_model_id'),
        'label' => $objData->getAttributeLabel('vehicle_model_id'),
        'value' => $objData->vehicle_model_id,
        'data' => \common\components\VehicleModule::getVehicleModelNamesArray(),
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('office_id'),
        'label' => $objData->getAttributeLabel('office_id'),
        'value' => ($objData->office_id ? $objData->office_id : ''),
        'data' => \common\components\OfficeModule::getOfficeComboTreeData(['showUniversal'=>true]),
        'htmlOptions' => ['style'=>"width:200px", 'required'=>true],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => \common\models\Pro_vehicle_fee_plan::getStatusArray(),
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_default'),
        'label' => $objData->getAttributeLabel('price_default'),
        'value' => $objData->price_default,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_3days'),
        'label' => $objData->getAttributeLabel('price_3days'),
        'value' => $objData->price_3days,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_week'),
        'label' => $objData->getAttributeLabel('price_week'),
        'value' => $objData->price_week,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_15days'),
        'label' => $objData->getAttributeLabel('price_15days'),
        'value' => $objData->price_15days,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_month'),
        'label' => $objData->getAttributeLabel('price_month'),
        'value' => $objData->price_month,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('locale', 'Daily fee info')],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_sunday'),
        'label' => $objData->getAttributeLabel('price_sunday'),
        'value' => $objData->price_sunday,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_monday'),
        'label' => $objData->getAttributeLabel('price_monday'),
        'value' => $objData->price_monday,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_tuesday'),
        'label' => $objData->getAttributeLabel('price_tuesday'),
        'value' => $objData->price_tuesday,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_wednesday'),
        'label' => $objData->getAttributeLabel('price_wednesday'),
        'value' => $objData->price_wednesday,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_thirsday'),
        'label' => $objData->getAttributeLabel('price_thirsday'),
        'value' => $objData->price_thirsday,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_friday'),
        'label' => $objData->getAttributeLabel('price_friday'),
        'value' => $objData->price_friday,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('price_saturday'),
        'label' => $objData->getAttributeLabel('price_saturday'),
        'value' => $objData->price_saturday,
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => 0,
    ],
    
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => Yii::t('locale', 'Festival fee info')],
];

$objFeePlan = new common\models\Pro_vehicle_fee_plan();
$objFeePlan->setFestivalNames(\common\components\OptionsModule::getFestivalsArray());
$festivalFields = [];
foreach ($objFeePlan->festivalFieldsArray as $field => $festivalId) {
    $festivalFields[$festivalId] = $field;
}
ksort($festivalFields);
$festivalCount = 0;
// festival fields
foreach ($festivalFields as $festivalId => $field) {
    $inputs[] = [
        'type' => CMyHtml::INPUT_NUMBERBOX,
        'name' => $objForm->fieldName($field),
        'label' => $objFeePlan->getAttributeLabel($field),
        'value' => (isset($objData->festivalPricesArray[$festivalId]) ? $objData->festivalPricesArray[$festivalId] : ''),
        'htmlOptions' => ['style'=>"width:200px"],
        'columnindex' => ($festivalCount % 2),
    ];
    $festivalCount++;
}

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($action == 'update' && $objVehicleFeePlan) {
    $hiddenFields['id'] = $objVehicleFeePlan->id;
    $hiddenFields[$objForm->fieldName('id')] = $objVehicleFeePlan->id;
}

echo CMyHtml::form($formTitle, \yii\helpers\Url::to(['vehicle/editfeeplan']), 'post', ['style'=>'width:650px;height:430px'], $inputs, $buttons, $hiddenFields);

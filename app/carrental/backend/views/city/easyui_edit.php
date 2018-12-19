<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_city();

$gaodeCityFieldId = CMyHtml::getIdPrefix().'combo_'.CMyHtml::genID();
$urlGetGaodeCityId = \yii\helpers\Url::to(['api/gaode_get_cityid', 'city'=>'']);

$objData = null;
if (isset($city) && $city) {
    $objData = $city;
}
else {
    $action = 'insert';
    $objData = new \common\models\Pro_city();
    if (empty($belongId)) {
        $objData->type = \common\models\Pro_city::TYPE_CITY;
        $objData->belong_id = $belongId;
    }
    else {
        $objData->type = $cityType;
        $objData->belong_id = $belongId;
    }
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('name'),
        'label' => Yii::t('locale', 'City'),
        'value' => $objData->name,
        'htmlOptions' => ['required' => true, 'onChange'=>"function(newValue, oldValue) { $('#{$gaodeCityFieldId}').combobox('reload', '$urlGetGaodeCityId'+encodeURI(newValue)); }"],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('flag'),
        'label' => Yii::t('locale', 'Flag'),
        'value' => $objData->flag,
        'data' => \common\components\CityModule::getCityFlagsArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => $objForm->fieldName('status'),
        'label' => Yii::t('locale', 'Region type'),
        'value' => $objData->status,
        'data' => \common\components\CityModule::getCityStatusArray(),
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('city_code'),
        'label' => \Yii::t('carrental', 'City ID of gaode'),
        'value' => $objData->city_code,
        'data' => $urlGetGaodeCityId.urldecode($objData->name),
        'htmlOptions' => ['required' => true, 'id'=>$gaodeCityFieldId],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action, $objForm->fieldName('type')=>$objData->type];

if ($action == 'insert') {
    if (empty($objData->belong_id)) {
        $inputs[] = ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('belong_id'),
            'label' => Yii::t('locale', 'Belongs to {name}', ['name' => '']),
            'value' => '',
            'data' => \common\components\CityModule::getAllProvincesArray(),
            'htmlOptions' => ['required' => true, 'editable'=>false],
        ];
    }
    else {
        $hiddenFields[$objForm->fieldName('belong_id')] = $objData->belong_id;
    }
}
else {
    $hiddenFields['id'] = $objData->id;
    $hiddenFields[$objForm->fieldName('id')] = $objData->id;
    $hiddenFields[$objForm->fieldName('belong_id')] = $objData->belong_id;
}

echo CMyHtml::form(Yii::t('locale', 'Edit city'), $saveUrl, 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
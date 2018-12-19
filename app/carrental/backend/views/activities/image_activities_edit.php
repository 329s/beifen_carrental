<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_activity_image();
$formTitle = Yii::t('locale', '{operation} activity', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = isset($objActivityImage) ? $objActivityImage : null;
if (!$objData) {
    $objData = new \common\models\Pro_activity_image();
    $objData->status = \common\models\Pro_activity_image::STATUS_ENABLED;
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('name'),
        'label' => $objData->getAttributeLabel('name'),
        'value' => $objData->name,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('type'),
        'label' => $objData->getAttributeLabel('type'),
        'value' => $objData->type,
        'data' => \common\models\Pro_activity_image::getTypesArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('bind_param'),
        'label' => $objData->getAttributeLabel('bind_param'),
        'value' => $objData->bind_param,
        'htmlOptions' => ['required' => false],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => \common\components\OptionsModule::getActivityStatusArray(),
        'htmlOptions' => ['editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('image'),
        'label' => $objData->getAttributeLabel('image'),
        'value' => '',
        'htmlOptions' => ['required' => false,
            'style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'src'=>\common\helpers\Utils::toFileUri($objData->image),
            'fileSize'=>'800KB'],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('icon'),
        'label' => $objData->getAttributeLabel('icon'),
        'value' => '',
        'htmlOptions' => ['required' => false,
            'style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'src'=>\common\helpers\Utils::toFileUri($objData->icon),
            'fileSize'=>'400KB'],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('href'),
        'label' => $objData->getAttributeLabel('href'),
        'value' => $objData->href,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
    ],
    ['type' => CMyHtml::INPUT_TEXTAREA, 'name' => $objForm->fieldName('remark'),
        'label' => $objData->getAttributeLabel('remark'),
        'value' => $objData->remark,
        'htmlOptions' => ['required' => false],
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('city_id'),
        'label' => $objData->getAttributeLabel('city_id'),
        'value' => $objData->city_id,
        'data' => \common\components\CityModule::getCityComboTreeData(true, ['showUniversal'=>\Yii::t('locale', 'All')]),
        'htmlOptions' => ['editable'=>true],
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('office_id'),
        'label' => $objData->getAttributeLabel('office_id'),
        'value' => $objData->office_id,
        'data' => \common\components\OfficeModule::getOfficeComboTreeData(['showUniversal'=>\Yii::t('locale', 'All')]),
        'htmlOptions' => ['editable'=>true],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($action == 'update' && $objActivityImage) {
    $hiddenFields['id'] = $objActivityImage->id;
    $hiddenFields[$objForm->fieldName('id')] = $objActivityImage->id;
}

echo CMyHtml::form($formTitle, \yii\helpers\Url::to(['activities/image_activities_edit']), 'post', ['enctype' => 'multipart/form-data'], $inputs, $buttons, $hiddenFields);

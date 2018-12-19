<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_activity_info();
$formTitle = Yii::t('locale', '{operation} activity', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = isset($objActivityInfo) ? $objActivityInfo : null;
if (!$objData) {
    $objData = new \common\models\Pro_activity_info();
    $objData->status = \common\models\Pro_activity_info::STATUS_NORMAL;
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('title'),
        'label' => $objData->getAttributeLabel('title'),
        'value' => $objData->title,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_TEXTAREA, 'name' => $objForm->fieldName('content'),
        'label' => $objData->getAttributeLabel('content'),
        'value' => $objData->content,
        'htmlOptions' => ['required' => true],
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
    ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => $objForm->fieldName('start_time'),
        'label' => $objData->getAttributeLabel('start_time'),
        'value' => $objData->start_time,
        'htmlOptions' => ['required' => true, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => $objForm->fieldName('end_time'),
        'label' => $objData->getAttributeLabel('end_time'),
        'value' => $objData->end_time,
        'htmlOptions' => ['required' => true, 'editable'=>false],
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
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => \common\models\Pro_activity_info::getStatusArray(),
        'htmlOptions' => ['editable'=>false],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($action == 'update' && $objActivityInfo) {
    $hiddenFields['id'] = $objActivityInfo->id;
    $hiddenFields[$objForm->fieldName('id')] = $objActivityInfo->id;
}

echo CMyHtml::form($formTitle, \yii\helpers\Url::to(['activities/text_activities_edit']), 'post', ['enctype' => 'multipart/form-data'], $inputs, $buttons, $hiddenFields);

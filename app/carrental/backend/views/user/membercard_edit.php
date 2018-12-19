<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_member_card();
$formTitle = Yii::t('locale', '{operation} member card', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = $objMemberCard;
if (!$objData) {
    $objData = new \common\models\Pro_member_card();
    $objData->type = \common\models\Pub_user_info::VIP_LEVEL_NORMAL;
    $objData->status = \common\models\Pro_member_card::STATUS_LOCKED;
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('card_no'),
        'label' => $objData->getAttributeLabel('card_no'),
        'value' => $objData->card_no,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('card_name'),
        'label' => $objData->getAttributeLabel('card_name'),
        'value' => $objData->card_name,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('type'),
        'label' => $objData->getAttributeLabel('type'),
        'value' => $objData->type,
        'data' => \common\models\Pub_user_info::getVipLevelsArray(),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('card_code'),
        'label' => $objData->getAttributeLabel('card_code'),
        'value' => $objData->card_code,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('card_password'),
        'label' => $objData->getAttributeLabel('card_password'),
        'value' => $objData->card_password,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('amount'),
        'label' => $objData->getAttributeLabel('amount'),
        'value' => $objData->amount,
        'htmlOptions' => ['required' => false, 'precision'=>2, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('recharged_amount'),
        'label' => $objData->getAttributeLabel('recharged_amount'),
        'value' => $objData->recharged_amount,
        'htmlOptions' => ['required' => false, 'precision'=>2, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('activated_at'),
        'label' => $objData->getAttributeLabel('activated_at'),
        'value' =>(empty($objData->activated_at) ? '' : date('Y-m-d', $objData->activated_at)),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => \common\models\Pro_member_card::getStatusArray(),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($action == 'update' && $objMemberCard) {
    $hiddenFields['id'] = $objMemberCard->id;
    $hiddenFields[$objForm->fieldName('id')] = $objMemberCard->id;
}

echo CMyHtml::form($formTitle, \yii\helpers\Url::to(['user/membercard_edit']), 'post', [], $inputs, $buttons, $hiddenFields);


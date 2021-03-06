<?php

use common\helpers\CMyHtml;

$objForm = new \common\models\Form_pub_user_info();
$objFormMember = new \backend\models\Form_pro_member_card_useredit();
$formTitle = Yii::t('locale', '{operation} customer', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = $objUserInfo;
$objMemberData = $objMemberInfo;
if (!$objData) {
    $objData = new \common\models\Pub_user_info();
    $objData->user_type = \common\models\Pub_user_info::USER_TYPE_PERSONAL;
    $objData->identity_type = \common\components\Consts::ID_TYPE_IDENTITY;
    $objData->gender = \common\models\Pub_user_info::GENDER_MALE;
}
if (!$objMemberData) {
    $objMemberData = new \common\models\Pro_member_card();
    $objMemberData->type = \common\models\Pub_user_info::VIP_LEVEL_NORMAL;
    $objMemberData->status = \common\models\Pro_member_card::STATUS_LOCKED;
}

$inputs = [
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Basic')]),
        'columnindex' => 0],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => $objForm->fieldName('user_type'),
        'label' => $objData->getAttributeLabel('user_type'),
        'value' => $objData->user_type,
        'data' => \common\models\Pub_user_info::getUserTypesArray(),
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('name'),
        'label' => $objData->getAttributeLabel('name'),
        'value' => $objData->name,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $objForm->fieldName('telephone'),
        'label' => $objData->getAttributeLabel('telephone'),
        'value' => $objData->telephone,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $objForm->fieldName('fixedphone'),
        'label' => $objData->getAttributeLabel('fixedphone'),
        'value' => $objData->fixedphone,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('home_address'),
        'label' => $objData->getAttributeLabel('home_address'),
        'value' => $objData->home_address,
        'htmlOptions' => ['required' => false, 'style'=>"width:500px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('post_code'),
        'label' => $objData->getAttributeLabel('post_code'),
        'value' => $objData->post_code,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_EMAIL, 'name' => $objForm->fieldName('email'),
        'label' => $objData->getAttributeLabel('email'),
        'value' => $objData->email,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('qq'),
        'label' => $objData->getAttributeLabel('qq'),
        'value' => $objData->qq,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_EMAIL, 'name' => $objForm->fieldName('msn'),
        'label' => $objData->getAttributeLabel('msn'),
        'value' => $objData->msn,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('emergency_contact'),
        'label' => $objData->getAttributeLabel('emergency_contact'),
        'value' => $objData->emergency_contact,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $objForm->fieldName('emergency_telephone'),
        'label' => $objData->getAttributeLabel('emergency_telephone'),
        'value' => $objData->emergency_telephone,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TYPE_SUBGROUP, 'label'=>\Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Driver license')])],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('driver_license'),
        'label' => $objData->getAttributeLabel('driver_license'),
        'value' => $objData->driver_license,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('driver_license_type'),
        'label' => $objData->getAttributeLabel('driver_license_type'),
        'value' => $objData->driver_license_type,
        'data' => \common\models\Pub_user_info::getDriverLicenseTypesArray(),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('driver_license_time'),
        'label' => $objData->getAttributeLabel('driver_license_time'),
        'value' => (empty($objData->driver_license_time) ? '' : date('Y-m-d', $objData->driver_license_time)),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('driver_license_expire_time'),
        'label' => $objData->getAttributeLabel('driver_license_expire_time'),
        'value' => (empty($objData->driver_license_expire_time) ? '' : date('Y-m-d', $objData->driver_license_expire_time)),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('driver_license_issuing_unit'),
        'label' => $objData->getAttributeLabel('driver_license_issuing_unit'),
        'value' => $objData->driver_license_issuing_unit,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Customer identity')]),
        'htmlOptions' => [
            'style' => "padding:22px 16px 22px 16px",
            'encode' => false,
        ],
        'columnindex' => 1],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('identity_type'),
        'label' => $objData->getAttributeLabel('identity_type'),
        'value' => $objData->identity_type,
        'data' => \common\models\Pub_user_info::getIdentityTypesArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('identity_id'),
        'label' => $objData->getAttributeLabel('identity_id'),
        'value' => $objData->identity_id,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('gender'),
        'label' => $objData->getAttributeLabel('gender'),
        'value' => $objData->gender,
        'data' => \common\models\Pub_user_info::getGendersArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('nationality'),
        'label' => $objData->getAttributeLabel('nationality'),
        'value' => $objData->nationality,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('birthday'),
        'label' => $objData->getAttributeLabel('birthday'),
        'value' => (empty($objData->birthday) ? '' : date('Y-m-d', $objData->birthday)),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('identity_start_time'),
        'label' => $objData->getAttributeLabel('identity_start_time'),
        'value' => (empty($objData->identity_start_time) ? '' : date('Y-m-d', $objData->identity_start_time)),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('identity_end_time'),
        'label' => $objData->getAttributeLabel('identity_end_time'),
        'value' => (empty($objData->identity_end_time) ? '' : date('Y-m-d', $objData->identity_end_time)),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('residence_address'),
        'label' => $objData->getAttributeLabel('residence_address'),
        'value' => $objData->residence_address,
        'htmlOptions' => ['required' => false, 'style'=>"width:500px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('issuing_unit'),
        'label' => $objData->getAttributeLabel('issuing_unit'),
        'value' => $objData->issuing_unit,
        'htmlOptions' => ['required' => false, 'style'=>"width:500px"],
        'columnindex' => 0,
    ],
    
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('carrental', 'Upload driver license'),
        'htmlOptions' => [
            'data-options' => "collapsible:true,collapsed:true",
            'encode' => false,
        ],
        'columnindex' => 0],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('driver_license_image_file'),
        'label' => $objData->getAttributeLabel('driver_license_image'),
        'value' => '',
        'htmlOptions' => ['style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 
            'fileSize'=>"3MB",
            'src'=>\common\helpers\Utils::toFileUri($objData->driver_license_image)],
        'columnindex' => 0,
    ],
    
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('carrental', 'Company info'),
        'columnindex' => 0],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('company_name'),
        'label' => $objData->getAttributeLabel('company_name'),
        'value' => $objData->company_name,
        'htmlOptions' => ['required' => false, 'style'=>"width:500px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('company_address'),
        'label' => $objData->getAttributeLabel('company_address'),
        'value' => $objData->company_address,
        'htmlOptions' => ['required' => false, 'style'=>"width:500px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('company_license'),
        'label' => $objData->getAttributeLabel('company_license'),
        'value' => $objData->company_license,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('organization_code'),
        'label' => $objData->getAttributeLabel('organization_code'),
        'value' => $objData->organization_code,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $objForm->fieldName('company_telephone'),
        'label' => $objData->getAttributeLabel('company_telephone'),
        'value' => $objData->company_telephone,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('company_postcode'),
        'label' => $objData->getAttributeLabel('company_postcode'),
        'value' => $objData->company_postcode,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    
    ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Member')]),
        'columnindex' => 0],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objFormMember->fieldName('card_no'),
        'label' => $objMemberData->getAttributeLabel('card_no'),
        'value' => $objMemberData->card_no,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objFormMember->fieldName('card_name'),
        'label' => $objMemberData->getAttributeLabel('card_name'),
        'value' => $objMemberData->card_name,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('vip_level'),
        'label' => $objData->getAttributeLabel('vip_level'),
        'value' => $objData->vip_level,
        'data' => \common\models\Pub_user_info::getVipLevelsArray(),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objFormMember->fieldName('activated_at'),
        'label' => $objMemberData->getAttributeLabel('activated_at'),
        'value' =>(empty($objMemberData->activated_at) ? '' : date('Y-m-d', $objMemberData->activated_at)),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 3,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('credit_card_no'),
        'label' => $objData->getAttributeLabel('credit_card_no'),
        'value' => $objData->credit_card_no,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('credit_card_deposit'),
        'label' => $objData->getAttributeLabel('credit_card_deposit'),
        'value' => $objData->credit_card_deposit,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('credit_card_lines'),
        'label' => $objData->getAttributeLabel('credit_card_lines'),
        'value' => $objData->credit_card_lines,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('credit_card_type'),
        'label' => $objData->getAttributeLabel('credit_card_type'),
        'value' => $objData->credit_card_type,
        'data' => \common\models\Pub_user_info::getCreditCardTypesArray(),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 3,
    ],
    ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $objForm->fieldName('credit_card_expire_time'),
        'label' => $objData->getAttributeLabel('credit_card_expire_time'),
        'value' => (empty($objData->credit_card_expire_time) ? '' : date('Y-m-d', $objData->credit_card_expire_time)),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('bank_card_no'),
        'label' => $objData->getAttributeLabel('bank_card_no'),
        'value' => $objData->bank_card_no,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('bank_card_name'),
        'label' => $objData->getAttributeLabel('bank_card_name'),
        'value' => $objData->bank_card_name,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('bank_card_deposit'),
        'label' => $objData->getAttributeLabel('bank_card_deposit'),
        'value' => $objData->bank_card_deposit,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
        'columnindex' => 3,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('total_consumption'),
        'label' => $objData->getAttributeLabel('total_consumption'),
        'value' => $objData->total_consumption,
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('cur_integration'),
        'label' => $objData->getAttributeLabel('cur_integration'),
        'value' => $objData->cur_integration,
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('used_integration'),
        'label' => $objData->getAttributeLabel('used_integration'),
        'value' => $objData->used_integration,
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('credit_level'),
        'label' => $objData->getAttributeLabel('credit_level'),
        'value' => $objData->credit_level,
        'data' => \common\models\Pub_user_info::getCreditLevelsArray(),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 3,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objFormMember->fieldName('card_code'),
        'label' => $objMemberData->getAttributeLabel('card_code'),
        'value' => $objMemberData->card_code,
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objFormMember->fieldName('card_password'),
        'label' => $objMemberData->getAttributeLabel('card_password'),
        'value' => $objMemberData->card_password,
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('blacklist_reason'),
        'label' => $objData->getAttributeLabel('blacklist_reason'),
        'value' => $objData->blacklist_reason,
        'htmlOptions' => ['required' => false, 'readonly'=>false, 'style'=>"width:200px"],
        'columnindex' => 3,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objFormMember->fieldName('amount'),
        'label' => $objMemberData->getAttributeLabel('amount'),
        'value' => $objMemberData->amount,
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'precision'=>2, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objFormMember->fieldName('recharged_amount'),
        'label' => $objMemberData->getAttributeLabel('recharged_amount'),
        'value' => $objMemberData->recharged_amount,
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'precision'=>2, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('love_car_level'),
        'label' => $objData->getAttributeLabel('love_car_level'),
        'value' => $objData->love_car_level,
        'data' => \common\models\Pub_user_info::getLoveCarLevelsArray(),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => '',
        'label' => $objData->getAttributeLabel('violation_records'),
        'value' => $objData->getViolationRecordsText(),
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'style'=>"width:200px"],
        'columnindex' => 3,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('finger_no'),
        'label' => $objData->getAttributeLabel('finger_no'),
        'value' => $objData->finger_no,
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'style'=>"width:200px"],
        'columnindex' => 0,
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $objForm->fieldName('finger_info'),
        'label' => $objData->getAttributeLabel('finger_info'),
        'value' => (empty($objData->finger_info) ? Yii::t('locale', 'Not collected') : $objData->finger_info),
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'style'=>"width:200px"],
        'columnindex' => 1,
    ],
    ['type' => CMyHtml::INPUT_TYPE_HTML, 'name' => '',
        'label' => $objData->getAttributeLabel('accident_records'),
        'html' => $objData->getAccidentRecordsHtml(),
        'htmlOptions' => ['required' => false, 'readonly'=>true, 'style'=>"width:200px"],
        'columnindex' => 2,
    ],
    ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => $objForm->fieldName('unfreeze_at'),
        'label' => $objData->getAttributeLabel('unfreeze_at'),
        'value' => (empty($objData->unfreeze_at) ? '' : date('Y-m-d H:i:s', $objData->unfreeze_at)),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 3,
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action, $objForm->fieldName('driver_license_image') => $objData->driver_license_image];
if ($action == 'update' && $objUserInfo) {
    $hiddenFields['id'] = $objUserInfo->id;
    $hiddenFields[$objForm->fieldName('id')] = $objUserInfo->id;
}

$authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
if ($authOfficeId == \common\components\OfficeModule::HEAD_OFFICE_ID) {
    $inputs[] = ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('belong_office_id'),
        'label' => $objData->getAttributeLabel('belong_office_id'),
        'value' => $objData->belong_office_id,
        'data' => \common\components\OfficeModule::getOfficeComboTreeData(),
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
        'columnindex' => 0,
    ];
}
else {
    $hiddenFields[$objForm->fieldName('belong_office_id')] = (empty($objData->belong_office_id) ? $objData->belong_office_id : $authOfficeId);
}

echo CMyHtml::form($formTitle, $saveUrl, 'post', [], $inputs, $buttons, $hiddenFields);

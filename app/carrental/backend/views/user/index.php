<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['user/user_list']),
    'deleteUrl' => \yii\helpers\Url::to(['user/delete']),
    //'detailUrl' => \yii\helpers\Url::to(['user/getuserdetailview']),
];

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['tab'=>\yii\helpers\Url::to(['user/add'])]),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['tab'=>\yii\helpers\Url::to(['user/edit']), 'needSelect' => true]),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', ''),
    
    // blacklist_index
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Inner black names'), ['tab'=>\yii\helpers\Url::to(['user/blacklist_index'])], 'icon-user_gray'),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', 'Member card management'), ['tab'=>\yii\helpers\Url::to(['user/membercard_index'])], 'icon-creditcards'),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'User feedbacks'), ['tab'=>\yii\helpers\Url::to(['user/feedback_index'])], 'icon-feed'),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Long rent application'), ['tab'=>\yii\helpers\Url::to(['user/longrentapplying_index'])], 'icon-coolite'),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Signup (not authenticated real name) accounts'), ['tab'=>\yii\helpers\Url::to(['user/account_index', 'hide_real_name'=>1])], 'icon-people'),
    
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'name', Yii::t('locale', 'Customer name'), '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'telephone', Yii::t('locale', 'Mobilephone'), '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'office_id', Yii::t('locale', 'Office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>"width:160px"]),
];

echo CMyHtml::datagrid('   ', // $title
    new common\models\Pub_user_info(),    // $model
    ['id', 'name', 'telephone', 'member_id', 'vip_level', 'credit_level', 'member_card_amount', 'total_consumption', 'cur_integration', 'violation_records', 'accident_records', 'unfreeze_at', 'created_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
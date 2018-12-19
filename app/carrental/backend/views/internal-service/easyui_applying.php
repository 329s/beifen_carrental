<?php
/**
 * @var $filterModel \common\helpers\ActiveSearcherModel
 */

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['internal-service/applying_list']),
];

$toolbarArray = [
    Yii::$app->user->can('internal-service/applying_add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['internal-service/applying_add'])]) : null,
    Yii::$app->user->can('internal-service/applying_edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['internal-service/applying_edit']), 'needSelect' => true]) : null,
    Yii::$app->user->can('internal-service/applying_approval') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, \Yii::t('locale', 'Approval'), ['dialog'=>\yii\helpers\Url::to(['internal-service/applying_approval']), 'needSelect' => true], 'icon-ok') : null,
    Yii::$app->user->can('internal-service/applying_delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', ['ajax'=>\yii\helpers\Url::to(['internal-service/applying_delete']), 'needSelect' => true]) : null,
    
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, $filterModel->fieldName('plate_number'), $filterModel->getAttributeLabel('plate_number'), '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, $filterModel->fieldName('applyer'), $filterModel->getAttributeLabel('applyer'), '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, $filterModel->fieldName('type'), $filterModel->getAttributeLabel('type'), array_merge([0 => \Yii::t('locale', 'All')], backend\models\Pro_inner_applying::getTypeArray()), ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, $filterModel->fieldName('status'), $filterModel->getAttributeLabel('status'), array_merge([0 => \Yii::t('locale', 'All')], backend\models\Pro_inner_applying::getStatusArray()), ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, $filterModel->fieldName('office_id'), $filterModel->getAttributeLabel('office_id'), common\components\OfficeModule::getOfficeComboTreeData(['showUniversal'=> \Yii::t('locale', 'All')]), ['searchOnChange'=>true, 'style'=>'width:150px']),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_DATEBOX, $filterModel->fieldName('applyed_time'), $filterModel->getAttributeLabel('applyed_time'), '', ['searchOnChange'=>true]),
];

$objModel = new backend\models\Pro_inner_applying();
$objForm = new backend\models\Form_pro_inner_applying();

$funcId = CMyHtml::genID(); 
$dgId = CMyHtml::getIDPrefix().'dg_'.CMyHtml::genID();

$containerPart = CMyHtml::datagrid(Yii::t('carrental', 'Vehicle applying management'), // $title
    $objModel,    // $model
    ['id', 'office_id', 'plate_number', 'content', 'applyer', 'type', 'approval_content', 'status', 'created_at'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [
		'id'=>$dgId,
		'data-options'=>[
            'sortName' => 'created_at',
            'sortOrder' => 'desc',
        ],
	],// $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$htmlArray = [];

$htmlArray[] = $containerPart;

echo implode("\n", $htmlArray);

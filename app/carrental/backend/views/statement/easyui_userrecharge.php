<?php
use common\helpers\CMyHtml;

$unitTextRMB = \Yii::t('locale', 'RMB Yuan');
$unitTextDays = Yii::t('carrental', 'days');

$columnOptionsArray = [
    ['name'=>\Yii::t('locale', 'Customer name'), 'options'=>['field'=>'name', 'width'=>140]],
    ['name'=>\Yii::t('locale', 'Card number'), 'options'=>['field'=>'card_number', 'width'=>140]],
    ['name'=>\Yii::t('locale', 'Member type'), 'options'=>['field'=>'vip_level', 'width'=>140, '']],
    ['name'=>\Yii::t('locale', 'Recharge amount').'('.$unitTextRMB.')', 'options'=>['field'=>'recharge_amount', 'width'=>140]],
    ['name'=>\Yii::t('locale', 'Current balance').'('.$unitTextRMB.')', 'options'=>['field'=>'current_balance', 'width'=>140]],
    ['name'=>\Yii::t('locale', 'Historical consumption').'('.$unitTextRMB.')', 'options'=>['field'=>'historical_consumption', 'width'=>140]],
    ['name'=>\Yii::t('locale', 'Current integration'), 'options'=>['field'=>'integration', 'width'=>140]],
];

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'customer_name', Yii::t('locale', 'Customer name'), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'customer_id', Yii::t('locale', 'Identity card'), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'card_number', Yii::t('locale', 'Card number'), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vip_level', Yii::t('locale', 'Member type'), \common\components\UserModule::getVipLevelsArray(), ['searchOnChange'=>true, 'style'=>'width:120px']),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'office_id', Yii::t('locale', 'Belong office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>'width:120px']),
];

$url = yii\helpers\Url::to(['statement/userrechargedata_list']);

echo \common\helpers\CEasyUI::datagrid2(Yii::t('carrental', '{name} income statistics report', ['name'=>Yii::t('locale', 'Vehicle')]), 
    $columnOptionsArray,
    [], // data
    '100%', "100%",
    ['rowheight'=>24,'pagination'=>true,'url'=>$url,'method'=>'get','pageSize'=>$pageSize],  // datagrid options
    [], // htmlOptions
    $toolbarArray
);

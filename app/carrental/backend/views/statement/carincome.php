<?php
use common\helpers\CMyHtml;

$unitTextRMB = \Yii::t('locale', 'RMB Yuan');
$unitTextDays = Yii::t('carrental', 'days');

$columnOptionsArray = [
    ['name'=>\Yii::t('locale', 'Plate number'), 'options'=>['field'=>'name', 'width'=>140]],
    ['name'=>\Yii::t('locale', 'Vehicle model'), 'options'=>['field'=>'model', 'width'=>140]],
    ['name'=>\Yii::t('locale', 'Rent days').'('.$unitTextDays.')', 'options'=>['field'=>'rent_days', 'width'=>140]],
    ['name'=>\Yii::t('carrental', 'Statistic days').'('.$unitTextDays.')', 'options'=>['field'=>'statistic_days', 'width'=>140]],
    ['name'=>\Yii::t('carrental', 'Rent rate').'(%)', 'options'=>['field'=>'rent_rate', 'width'=>140]],
    ['name'=>\Yii::t('carrental', 'Vehicle non-settlemented income').'('.$unitTextRMB.')', 'options'=>['field'=>'income0', 'width'=>140]],
    ['name'=>\Yii::t('carrental', 'Vehicle settlemented income').'('.$unitTextRMB.')', 'options'=>['field'=>'income1', 'width'=>140]],
    ['name'=>\Yii::t('carrental', 'Vehicle outcome').'('.$unitTextRMB.')', 'options'=>['field'=>'outcome', 'width'=>140]],
    ['name'=>\Yii::t('locale', 'Total summary').'('.$unitTextRMB.')', 'options'=>['field'=>'summary', 'width'=>140]],
];

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'plate_number', Yii::t('locale', 'Plate number'), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_model_id', Yii::t('locale', 'Vehicle model'), \yii\helpers\Url::to(['vehicle/getmodelnames', 'enableadd'=>'0', 'enableall'=>'1']), ['searchOnChange'=>true, 'style'=>'width:120px']),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_DATEBOX, ['start_time', 'end_time'], Yii::t('locale', 'Statistic time'), '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'office_id', Yii::t('locale', 'Belong office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>'width:120px']),
];

$url = yii\helpers\Url::to(['statement/carincomedata_list']);

echo \common\helpers\CEasyUI::datagrid2(Yii::t('carrental', '{name} income statistics report', ['name'=>Yii::t('locale', 'Vehicle')]), 
    $columnOptionsArray,
    [], // data
    '100%', "100%",
    ['rowheight'=>24,'pagination'=>true,'url'=>$url,'method'=>'get','pageSize'=>$pageSize],  // datagrid options
    [], // htmlOptions
    $toolbarArray
);

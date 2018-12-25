<?php
use common\helpers\CMyHtml;

$unitTextRMB = \Yii::t('locale', 'RMB Yuan');
$unitTextDays = Yii::t('carrental', 'days');

$columnOptionsArray = [
    ['name'=>\Yii::t('locale', 'Plate number'), 'options'=>['field'=>'name', 'width'=>100]],
    ['name'=>\Yii::t('locale', 'Vehicle model'), 'options'=>['field'=>'model', 'width'=>140]],
    ['name'=>\Yii::t('locale', 'Rent days').'('.$unitTextDays.')', 'options'=>['field'=>'rent_days', 'width'=>100]],
    ['name'=>\Yii::t('carrental', 'Statistic days').'('.$unitTextDays.')', 'options'=>['field'=>'statistic_days', 'width'=>100]],
    ['name'=>\Yii::t('carrental', 'Rent rate').'(%)', 'options'=>['field'=>'rent_rate', 'width'=>100]],
    ['name'=>\Yii::t('carrental', 'Vehicle baught price').'('.$unitTextRMB.')', 'options'=>['field'=>'baught_price', 'width'=>100]],
    ['name'=>\Yii::t('locale', 'Purchase tax').'('.$unitTextRMB.')', 'options'=>['field'=>'baught_tax', 'width'=>100]],
    ['name'=>\Yii::t('carrental', 'Vehicle income').'('.$unitTextRMB.')', 'options'=>['field'=>'income', 'width'=>100]],
    ['name'=>\Yii::t('carrental', 'Vehicle outcome').'('.$unitTextRMB.')', 'options'=>['field'=>'outcome', 'width'=>100]],
    ['name'=>\Yii::t('carrental', 'Management allocation').'('.$unitTextRMB.')', 'options'=>['field'=>'management_allocation', 'width'=>100]],
    ['name'=>\Yii::t('locale', 'Total summary').'('.$unitTextRMB.')', 'options'=>['field'=>'summary', 'width'=>100]],
    ['name'=>\Yii::t('carrental', 'Earnings rate').'(%)', 'options'=>['field'=>'earnings_rate', 'width'=>100]],
];

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'plate_number', Yii::t('locale', 'Plate number'), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_model_id', Yii::t('locale', 'Vehicle model'), \yii\helpers\Url::to(['vehicle/getmodelnames', 'enableadd'=>'0', 'enableall'=>'1']), ['searchOnChange'=>true, 'style'=>'width:120px']),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_DATEBOX, 'baught_time', Yii::t('carrental', 'Baught time'), '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'office_id', Yii::t('locale', 'Belong office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>'width:120px']),
];

$url = yii\helpers\Url::to(['statement/carsummarydata_list']);

echo \common\helpers\CEasyUI::datagrid2(Yii::t('carrental', 'Vehicle income total report'), 
    $columnOptionsArray,
    [], // data
    '100%', "100%",
    ['rowheight'=>24,'pagination'=>true,'url'=>$url,'method'=>'get','pageSize'=>$pageSize],  // datagrid options
    [], // htmlOptions
    $toolbarArray
);

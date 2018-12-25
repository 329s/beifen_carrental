<?php
use common\helpers\CMyHtml;

$htmlArray = [];

$unitTextRMB = \Yii::t('locale', 'RMB Yuan');
$unitTextCars = Yii::t('carrental', 'cars');

$idPrefix = CMyHtml::getIDPrefix();
$autoId = CMyHtml::genID();

$reloadUrl = yii\helpers\Url::to(['statement/monthly', '_'=>time()]);

$htmlArray[] = CMyHtml::beginLayout(['fit'=>'true']);

$htmlArray[] = CMyHtml::beginLayoutRegion('100%', '38px', '', 'north', []);
$htmlArray[] = CMyHtml::beginPanel('', ['style'=>'text-align:right']);

$htmlArray[] = \yii\bootstrap\Html::label(Yii::t('locale', 'Year:')).\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_COMBOBOX, 'year', $year,
    ['2017'=>'2017', '2018'=>'2018', '2019'=>'2019', '2020'=>'2020'],
    [
    'id'=>"{$idPrefix}param_year{$autoId}",
    'style'=>"width:120px",
    'data-options' => <<<EOD
onChange: function(newValue,oldValue) {
    var month = $('#{$idPrefix}param_month{$autoId}').combobox('getValue');
    easyuiFuncNavTabReloadCurTab('{$reloadUrl}&year='+encodeURI(newValue)+'&month='+month);
}
EOD
], '');
$htmlArray[] = \yii\bootstrap\Html::label(Yii::t('locale', 'Month:')).\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_COMBOBOX, 'month', $month, 
    ['01'=>'1', '02'=>'2', '03'=>'3', '04'=>'4', '05'=>'5', '06'=>'6', '07'=>'7', '08'=>'8', '09'=>'9', '10'=>'10', '11'=>'11', '12'=>'12'], 
    [
    'id'=>"{$idPrefix}param_month{$autoId}",
    'style'=>"width:80px",
    'data-options' => <<<EOD
editable:false,
onChange: function(newValue,oldValue) {
    var year = $('#{$idPrefix}param_year{$autoId}').combobox('getValue');
    easyuiFuncNavTabReloadCurTab('{$reloadUrl}&year='+encodeURI(year)+'&month='+newValue);
}
EOD
], '');

$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endLayoutRegion();

$htmlArray[] = CMyHtml::beginLayoutRegion('100%', '', '', 'center', []);
// 月度账目统计
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', '{name} account statistics', ['name'=>Yii::t('carrental', 'Monthly')]), ['style'=>"width:100%;height:auto;padding:8px 16px 8px 16px"]);
// 按日期月度账目统计报表
$columnOptionsArray = [
    ['name'=>\Yii::t('locale', 'Date'), 'options'=>['field'=>'name', 'width'=>240]],
    ['name'=>\Yii::t('locale', 'Income').'('.$unitTextRMB.')', 'options'=>['field'=>'income', 'width'=>240]],
    ['name'=>\Yii::t('locale', 'Outcome').'('.$unitTextRMB.')', 'options'=>['field'=>'outcome', 'width'=>240]],
    ['name'=>\Yii::t('locale', 'Amount subtotal'), 'options'=>['field'=>'subtotal', 'width'=>240]],
];
$rowCount = count($arrMonthlyDateData['rows']) + count($arrMonthlyDateData['footer']);
$strDgData = \common\helpers\CEasyUI::convertDatagridDataWithFooterToString($arrMonthlyDateData);
//   toolbarH + titleH + rowsH + bottomLineH
$dgHeight = 3 + 25 + 25 * $rowCount + 3;
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', '{name} accounts statistics report by {item}', ['name'=>Yii::t('carrental', 'Monthly'), 'item'=>Yii::t('locale', 'date')]), ['style'=>"width:100%;height:auto"]);
$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, [], '100%', "{$dgHeight}px", ['rowheight'=>24,'pagination'=>false,'data'=>$strDgData], []);
$htmlArray[] = CMyHtml::endPanel();

// 按车辆月度账目统计报表
$columnOptionsArray = [
    ['name'=>\Yii::t('locale', 'Plate number'), 'options'=>['field'=>'name', 'width'=>240]],
    ['name'=>\Yii::t('locale', 'Income').'('.$unitTextRMB.')', 'options'=>['field'=>'income', 'width'=>240]],
    ['name'=>\Yii::t('locale', 'Outcome').'('.$unitTextRMB.')', 'options'=>['field'=>'outcome', 'width'=>240]],
    ['name'=>\Yii::t('locale', 'Amount subtotal'), 'options'=>['field'=>'subtotal', 'width'=>240]],
];
$rowCount = count($arrMonthlyVehicleData['rows']) + count($arrMonthlyVehicleData['footer']);
$strDgData = \common\helpers\CEasyUI::convertDatagridDataWithFooterToString($arrMonthlyVehicleData);
//   toolbarH + titleH + rowsH + bottomLineH
$dgHeight = 3 + 25 + 25 * $rowCount + 3;
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', '{name} accounts statistics report by {item}', ['name'=>Yii::t('carrental', 'Monthly'), 'item'=>Yii::t('locale', 'Vehicle')]), ['style'=>"width:100%;height:auto"]);
$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, [], '100%', "{$dgHeight}px", ['rowheight'=>24,'pagination'=>false,'data'=>$strDgData], []);
$htmlArray[] = CMyHtml::endPanel();

// 按车型月度账目统计报表
$columnOptionsArray = [
    ['name'=>\Yii::t('locale', 'Vehicle model'), 'options'=>['field'=>'name', 'width'=>240]],
    ['name'=>\Yii::t('locale', 'Income').'('.$unitTextRMB.')', 'options'=>['field'=>'income', 'width'=>240]],
    ['name'=>\Yii::t('locale', 'Outcome').'('.$unitTextRMB.')', 'options'=>['field'=>'outcome', 'width'=>240]],
    ['name'=>\Yii::t('locale', 'Amount subtotal'), 'options'=>['field'=>'subtotal', 'width'=>240]],
];
$rowCount = count($arrMonthlyVehicleModelData['rows']) + count($arrMonthlyVehicleModelData['footer']);
$strDgData = \common\helpers\CEasyUI::convertDatagridDataWithFooterToString($arrMonthlyVehicleModelData);
//   toolbarH + titleH + rowsH + bottomLineH
$dgHeight = 3 + 25 + 25 * $rowCount + 3;
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', '{name} accounts statistics report by {item}', ['name'=>Yii::t('carrental', 'Monthly'), 'item'=>Yii::t('carrental', 'Car model')]), ['style'=>"width:100%;height:auto"]);
$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, [], '100%', "{$dgHeight}px", ['rowheight'=>24,'pagination'=>false,'data'=>$strDgData], []);
$htmlArray[] = CMyHtml::endPanel();

// 月度账目收支统计 
$columnOptionsArray = [
    ['name'=>\Yii::t('locale', '{name} item', ['name'=>\Yii::t('locale', 'Income')]), 'options'=>['field'=>'incomename', 'width'=>140]],
    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Cash')]), 'options'=>['field'=>'cash', 'width'=>120]],
    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Cheque')]), 'options'=>['field'=>'cheque', 'width'=>120]],
    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Swipe card')]), 'options'=>['field'=>'swipe_card', 'width'=>120]],
    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Online banking')]), 'options'=>['field'=>'online_banking', 'width'=>120]],
    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Member')]), 'options'=>['field'=>'member', 'width'=>120]],
    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Alipay')]), 'options'=>['field'=>'alipay', 'width'=>120]],
    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Weixin')]), 'options'=>['field'=>'wxpay', 'width'=>120]],
    ['name'=>\Yii::t('locale', 'Amount subtotal'), 'options'=>['field'=>'income', 'width'=>120]],
    ['name'=>\Yii::t('locale', '{name} item', ['name'=>\Yii::t('locale', 'Outcome')]), 'options'=>['field'=>'outcomename', 'width'=>120]],
    ['name'=>\Yii::t('locale', '{name} amount', ['name'=>\Yii::t('locale', 'Outcome')]), 'options'=>['field'=>'outcome', 'width'=>120]],
];
$rowCount = count($arrMonthlySummaryData['rows']) + count($arrMonthlySummaryData['footer']);
$strDgData = \common\helpers\CEasyUI::convertDatagridDataWithFooterToString($arrMonthlySummaryData);
//   toolbarH + titleH + rowsH + bottomLineH
$dgHeight = 3 + 25 + 25 * $rowCount + 3;
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', '{name} summary of accounts', ['name'=>Yii::t('carrental', 'Monthly')]), ['style'=>"width:100%;height:auto"]);
$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, [], '100%', "{$dgHeight}px", ['rowheight'=>24,'pagination'=>false,'data'=>$strDgData], []);
$htmlArray[] = CMyHtml::endPanel();

// 月度其他支出统计 
$columnOptionsArray = [
    ['name'=>\Yii::t('locale', '{name} outcome', ['name'=>\Yii::t('carrental', 'Fuel')]), 'options'=>['field'=>'fuel', 'width'=>200]],
    ['name'=>\Yii::t('carrental', 'Maintenance'), 'options'=>['field'=>'maintenance', 'width'=>200]],
    ['name'=>\Yii::t('locale', '{name} outcome', ['name'=>\Yii::t('carrental', 'Insurance')]), 'options'=>['field'=>'insurance', 'width'=>200]],
    ['name'=>\Yii::t('locale', '{name} outcome', ['name'=>\Yii::t('locale', 'Designated driving')]), 'options'=>['field'=>'disignated_driving', 'width'=>200]],
    ['name'=>\Yii::t('locale', '{name} outcome', ['name'=>\Yii::t('locale', 'Other')]), 'options'=>['field'=>'other', 'width'=>200]],
];
$rowCount = count($arrMonthlyOtherOutcomeData['rows']) + count($arrMonthlyOtherOutcomeData['footer']);
$strDgData = \common\helpers\CEasyUI::convertDatagridDataWithFooterToString($arrMonthlyOtherOutcomeData);
//   toolbarH + titleH + rowsH + bottomLineH
$dgHeight = 3 + 25 + 25 * $rowCount + 3;
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', '{name} other outcome', ['name'=>Yii::t('carrental', 'Monthly')]), ['style'=>"width:100%;height:auto"]);
$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, [], '100%', "{$dgHeight}px", ['rowheight'=>24,'pagination'=>false,'data'=>$strDgData], []);
$htmlArray[] = CMyHtml::endPanel();

$htmlArray[] = CMyHtml::endPanel();

$htmlArray[] = CMyHtml::endLayoutRegion();

$htmlArray[] = CMyHtml::endLayout();

echo implode("\n", $htmlArray);

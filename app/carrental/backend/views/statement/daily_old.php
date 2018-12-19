<?php
use common\helpers\CMyHtml;

$htmlArray = [];
$arrScripts = [];

$unitTextRMB = \Yii::t('locale', 'RMB Yuan');
$unitTextCars = Yii::t('carrental', 'cars');

$autoId = CMyHtml::genID();
$isAdministrator = \backend\components\AdminModule::isAuthorizedHeadOffice();

$reloadUrl = \yii\helpers\Url::to(['statement/daily', '_'=>time()]);
$exportUrl = \yii\helpers\Url::to(['statement/daily', 'export'=>'excel', 'date'=>$date, 'office_id'=>$belongOfficeId]);

$htmlArray[] = CMyHtml::beginLayout(['fit'=>'true']);

$htmlArray[] = CMyHtml::beginLayoutRegion('100%', '38px', '', 'north', []);
$htmlArray[] = CMyHtml::beginPanel('', ['style'=>'text-align:right']);
// 今日账目统计
$arrScripts[] = "var dailyQueryParams{$autoId} = {office_id:'{$belongOfficeId}', date:'{$date}'};\n".
    "function funcDailyReloadPage{$autoId}() {\n".
    "    var url = '{$reloadUrl}';\n".
    "    for (var k in dailyQueryParams{$autoId}) {\n".
    "        url += '&'+k+'='+encodeURI(dailyQueryParams{$autoId}[k]);\n".
    "    }\n".
    "    easyuiFuncNavTabReloadCurTab(url);\n".
    "}";
if ($isAdministrator) {
    $htmlArray[] = \yii\bootstrap\Html::label(Yii::t('locale', 'Office').':').\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_COMBOTREE, 'office_id', $belongOfficeId, \common\components\OfficeModule::getOfficeComboTreeData(), [
        'data-options' => "editable:false,\n".
        "onChange: function(newValue,oldValue) {\n".
        "    dailyQueryParams{$autoId}.office_id = newValue;\n".
        "    funcDailyReloadPage{$autoId}();\n".
        "}"
    ], Yii::t('locale', 'Office'));
}
$htmlArray[] = \yii\bootstrap\Html::label(Yii::t('locale', 'Time').':').\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_DATEBOX, 'time', $date, [], [
    'data-options' =>"editable:false,\n".
        "onChange: function(newValue,oldValue) {\n".
        "    dailyQueryParams{$autoId}.date = newValue;\n".
        "    setTimeout(function() { funcDailyReloadPage{$autoId}(); }, 100);\n".
        "}"
], '');

// export button
$htmlArray[] = CMyHtml::tag('a', '导出', ['class'=>'btn btn-info', 'href'=>$exportUrl]);

$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endLayoutRegion();

$htmlArray[] = CMyHtml::beginLayoutRegion('100%', '', '', 'center', []);
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', '{name} account statistics', ['name'=>Yii::t('locale', 'Today')]), ['style'=>"width:100%;height:auto;padding:8px 16px 8px 16px", 'fit'=>'true']);
// 今日账目明细表
$columnOptionsArray = [
    ['name'=>\Yii::t('locale', 'Time'), 'options'=>['field'=>'time', 'width'=>140, 'formatter'=>"function(value,row){ return $.custom.utils.humanTime(value);}"]],
    ['name'=>\Yii::t('locale', 'Office'), 'options'=>['field'=>'office', 'width'=>100]],
    ['name'=>\Yii::t('locale', 'Handler name'), 'options'=>['field'=>'handler', 'width'=>120]],
    ['name'=>\Yii::t('locale', 'Plate number'), 'options'=>['field'=>'plate', 'width'=>100]],
    ['name'=>\Yii::t('locale', 'Name'), 'options'=>['field'=>'customer_name', 'width'=>120]],
    //['name'=>\Yii::t('carrental', 'Contract No.'), 'options'=>['field'=>'serial', 'width'=>120]],
    ['name'=>\Yii::t('locale', 'Days'), 'options'=>['field'=>'days', 'width'=>80]],
    ['name'=>\Yii::t('locale', 'Pre-licensing'), 'options'=>['field'=>'pre_licensing_amount', 'width'=>100]],
    ['name'=>\Yii::t('locale', 'Deposit'), 'options'=>['field'=>'deposit_amount', 'width'=>100]],
    ['name'=>\Yii::t('locale', 'Rent'), 'options'=>['field'=>'rent_amount', 'width'=>100]],
    ['name'=>\Yii::t('locale', 'Value-added services'), 'options'=>['field'=>'optional_amount', 'width'=>100]],
    ['name'=>'误工费', 'options'=>['field'=>'delay_cost', 'width'=>100]],
    ['name'=>\Yii::t('locale', '{type} price', ['type'=>\Yii::t('carrental', 'Car damage')]), 'options'=>['field'=>'car_damage_amount', 'width'=>100]],
    ['name'=>\Yii::t('carrental', 'Fuel cost'), 'options'=>['field'=>'oil_amount', 'width'=>100]],
    ['name'=>\Yii::t('carrental', 'Violation price'), 'options'=>['field'=>'violation_amount', 'width'=>100]],
    ['name'=>'代办费', 'options'=>['field'=>'poundage_amount', 'width'=>100]],
    ['name'=>'服务费', 'options'=>['field'=>'service_amount', 'width'=>100]],
    ['name'=>'配件费', 'options'=>['field'=>'accessories_amount', 'width'=>100]],
    ['name'=>\Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Other')]), 'options'=>['field'=>'other_amount', 'width'=>100]],
    ['name'=>'合计', 'options'=>['field'=>'total_amount', 'width'=>100]],
    ['name'=>'收款方式', 'options'=>['field'=>'pay_source', 'width'=>100, 'formatter'=>"function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(\common\components\OrderModule::getOrderPayTypeArray())." }"]],
    ['name'=>'业务类型', 'options'=>['field'=>'operation_type', 'width'=>100, 'formatter'=>"function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc([0=>''])." }"]],
    ['name'=>'携程订单', 'options'=>['field'=>'from_xtrip', 'width'=>100, 'formatter'=>"function(value,row){ if (value>0) { return '√'; } return ''; }"]],
    ['name'=>\Yii::t('locale', 'Remark'), 'options'=>['field'=>'remark', 'width'=>160]],
    //['name'=>\Yii::t('locale', 'Income').'('.$unitTextRMB.')', 'options'=>['field'=>'income', 'width'=>120]],
    //['name'=>\Yii::t('locale', 'Outcome').'('.$unitTextRMB.')', 'options'=>['field'=>'outcome', 'width'=>120]],
];
$rowCount = count($arrDailyData['rows']);
$strDgData = null;
if (isset($arrDailyData['footer'])) {
    $rowCount += count($arrDailyData['footer']);
}
$strDgData = \common\helpers\CEasyUI::convertDatagridDataWithFooterToString($arrDailyData);
//   toolbarH + titleH + rowsH + bottomLineH
$dgHeight = 3 + 25 + 25 * $rowCount + 3;
// because of the column count is too large, there would need spaces for the scroll bar
$dgHeight += 18;
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Today statement of accounts'), ['style'=>"width:100%;height:auto"]);
$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, [], '100%', "{$dgHeight}px", ['rowheight'=>24,'pagination'=>false,'data'=>$strDgData], []);
$htmlArray[] = CMyHtml::endPanel();

// 今日账目收支统计 
//$columnOptionsArray = [
//    ['name'=>\Yii::t('locale', '{name} item', ['name'=>\Yii::t('locale', 'Income')]), 'options'=>['field'=>'incomename', 'width'=>140]],
//    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Cash')]), 'options'=>['field'=>'cash', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Cheque')]), 'options'=>['field'=>'cheque', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Swipe card')]), 'options'=>['field'=>'swipe_card', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Online banking')]), 'options'=>['field'=>'online_banking', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Member')]), 'options'=>['field'=>'member', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Alipay')]), 'options'=>['field'=>'alipay', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{type} recharge', ['type'=>\Yii::t('locale', 'Weixin')]), 'options'=>['field'=>'wxpay', 'width'=>120]],
//    ['name'=>\Yii::t('locale', 'Amount subtotal'), 'options'=>['field'=>'income', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{name} item', ['name'=>\Yii::t('locale', 'Outcome')]), 'options'=>['field'=>'outcomename', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{name} amount', ['name'=>\Yii::t('locale', 'Outcome')]), 'options'=>['field'=>'outcome', 'width'=>120]],
//];
//$rowCount = count($arrDailySummaryData['rows']) + count($arrDailySummaryData['footer']);
//$strDgData = \common\helpers\CEasyUI::convertDatagridDataWithFooterToString($arrDailySummaryData);
////    toolbarH + titleH + rowsH + bottomLineH
//$dgHeight = 3 + 25 + 25 * $rowCount + 3;
//$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', '{name} summary of accounts', ['name'=>Yii::t('locale', 'Today')]), ['style'=>"width:100%;height:auto"]);
//$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, [], '100%', "{$dgHeight}px", ['rowheight'=>24,'pagination'=>false,'data'=>$strDgData], []);
//$htmlArray[] = CMyHtml::endPanel();
//
//// 今日其他支出统计 
//$columnOptionsArray = [
//    ['name'=>\Yii::t('locale', '{name} outcome', ['name'=>\Yii::t('carrental', 'Fuel')]), 'options'=>['field'=>'fuel', 'width'=>120]],
//    ['name'=>\Yii::t('carrental', 'Maintenance'), 'options'=>['field'=>'maintenance', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{name} outcome', ['name'=>\Yii::t('carrental', 'Insurance')]), 'options'=>['field'=>'insurance', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{name} outcome', ['name'=>\Yii::t('locale', 'Designated driving')]), 'options'=>['field'=>'disignated_driving', 'width'=>120]],
//    ['name'=>\Yii::t('locale', '{name} outcome', ['name'=>\Yii::t('locale', 'Other')]), 'options'=>['field'=>'other', 'width'=>120]],
//];
//$rowCount = count($arrDailyOtherOutcomeData['rows']) + count($arrDailyOtherOutcomeData['footer']);
//$strDgData = \common\helpers\CEasyUI::convertDatagridDataWithFooterToString($arrDailyOtherOutcomeData);
////    toolbarH + titleH + rowsH + bottomLineH
//$dgHeight = 3 + 25 + 25 * $rowCount + 3;
//$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', '{name} other outcome', ['name'=>Yii::t('locale', 'Today')]), ['style'=>"width:100%;height:auto"]);
//$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, [], '100%', "{$dgHeight}px", ['rowheight'=>24,'pagination'=>false,'data'=>$strDgData], []);
//$htmlArray[] = CMyHtml::endPanel();
//
//// 未退押金账目总计 
//$columnOptionsArray = [
//    ['name'=>'Item0', 'options'=>['field'=>'item_0', 'width'=>200]],
//    ['name'=>'Value0', 'options'=>['field'=>'value_0', 'width'=>200]],
//    ['name'=>'Item1', 'options'=>['field'=>'item_1', 'width'=>200]],
//    ['name'=>'Value1', 'options'=>['field'=>'value_1', 'width'=>200]],
//];
//$rowCount = count($arrDepositData);
////    toolbarH + titleH + rowsH + bottomLineH
//$dgHeight = 3 + 0 + 25 * $rowCount + 3;
//$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Today summary of no deposit'), ['style'=>"width:100%;height:auto"]);
//$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, $arrDepositData, '100%', "{$dgHeight}px", ['pagination'=>false,'showHeader'=>false], []);
//$htmlArray[] = CMyHtml::endPanel();
//
//$htmlArray[] = CMyHtml::endPanel();
//
//// 经营分析日报 
//$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Daily business analysis'), ['style'=>"width:100%;height:auto;padding:8px 16px 8px 16px"]);
//$columnOptionsArray = [
//    ['name'=>'Item', 'options'=>['field'=>'item', 'width'=>200]],
//    ['name'=>'Cars', 'options'=>['field'=>'cars', 'width'=>200, 'formatter'=>"function(value,row){ if (value == '') { if (row.num != '') { return row.num + ' 位'; } return '';} return value + ' {$unitTextCars}';}"]],
//    ['name'=>'Amount', 'options'=>['field'=>'amount', 'width'=>200, 'formatter'=>"function(value,row){ if (value == '') {return '';} return '￥' + value + ' {$unitTextRMB}';}"]],
//    ['name'=>'Num', 'options'=>['field'=>'num', 'width'=>0, 'hidden'=>true]],
//];
//// 今日经营预测 
//$rowCount = count($arrTodayBusinessForecastData);
////    toolbarH + titleH + rowsH + bottomLineH
//$dgHeight = 3 + 1 + 25 * $rowCount + 3;
//$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Today business forecast'), ['style'=>"width:100%;height:auto"]);
//$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, $arrTodayBusinessForecastData, '100%', "{$dgHeight}px", ['pagination'=>false,'showHeader'=>false], []);
//$htmlArray[] = CMyHtml::endPanel();
//// 今日经营情况
//$rowCount = count($arrTodayBusinessData);
////    toolbarH + titleH + rowsH + bottomLineH
//$dgHeight = 3 + 1 + 25 * $rowCount + 3;
//$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Today business situation'), ['style'=>"width:100%;height:auto"]);
//$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, $arrTodayBusinessData, '100%', "{$dgHeight}px", ['pagination'=>false,'showHeader'=>false], []);
//$htmlArray[] = CMyHtml::endPanel();
//// 明日经营预测
//$rowCount = count($arrTomorrowBusinessForecastData);
////    toolbarH + titleH + rowsH + bottomLineH
//$dgHeight = 3 + 1 + 25 * $rowCount + 3;
//$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Tomorrow business forecast'), ['style'=>"width:100%;height:auto"]);
//$htmlArray[] = \common\helpers\CEasyUI::datagrid2('', $columnOptionsArray, $arrTomorrowBusinessForecastData, '100%', "{$dgHeight}px", ['pagination'=>false,'showHeader'=>false], []);
//$htmlArray[] = CMyHtml::endPanel();

//$htmlArray[] = CMyHtml::endPanel();

//$htmlArray[] = CMyHtml::endTag('div');

//$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::endLayoutRegion();

$htmlArray[] = CMyHtml::endLayout();

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);

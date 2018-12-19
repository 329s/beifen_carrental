<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use common\helpers\CMyHtml;

\backend\assets\AgreementAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode(Yii::$app->params['app.company.name']) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?php

// 增值服务信息
$priceNonDeductible = 0;
$priceNonDeductibleOvertime = 0;
$priceAgencyFee = $objOrder->price_oil_agency + $objOrder->price_violation;
$priceOther = $objOrder->price_other + $objOrder->price_different_office + $objOrder->price_oil + $objOrder->price_car_damage + $objOrder->price_designated_driving + $objOrder->price_designated_driving_overtime + $objOrder->price_designated_driving_overmileage;
$arrServicePriceObjects = \common\components\OptionsModule::getOptionalServiceObjectsArray();
$arrSelectedOptionalPrices = $objOrder->getOptionalServicePriceArray();
foreach ($arrSelectedOptionalPrices as $id => $val) {
    $serObj = (isset($arrServicePriceObjects[$id]) ? $arrServicePriceObjects[$id] : null);
    if ($serObj && $serObj->type == \common\models\Pro_service_price::TYPE_NON_DEDUCTIBLE_INSURANCE) {
        $priceNonDeductible += $val['price']*$val['count'];
    }
    else {
        $priceOther += $val['price']*$val['count'];
    }
}
if($objOrder->pay_type == 6){
	$otherName1 = '送车上门服务费';
	$otherName2 = '上门取车服务费';
	$priceOther1 = $objOrder->price_take_car;
	$priceOther2 = $objOrder->price_return_car;
}else{
	$otherName1 = '代办费（加油、违章）';
	$otherName2 = '其他（油费、代驾费、送车上门费等）';
	$priceOther1 = $priceAgencyFee;
	$priceOther2 = $priceOther+$objOrder->price_take_car + $objOrder->price_return_car;
}
$arrData1 = [
    [
        ['name'=>'租车费用及服务费', 'colspan'=>4, 'bold'=>true, 'namealign'=>'center', 'height'=>'24px'],
    ],
    [
        ['name'=>'租车费用', 'colspan'=>4],
    ],
    [
        ['name'=>'费用项目', 'value'=>'小计', 'valuealign'=>'center', 'namespan'=>2],
        ['name'=>'合计', 'namealign'=>'center'],
    ],
    [
        ['name'=>'车辆租赁费用', 'value'=>floatval($objOrder->price_rent), 'valuealign'=>'center', 'namespan'=>2],
        ['name'=>floatval($objOrder->price_rent + $objOrder->price_basic_insurance + $objOrder->price_poundage +  $objOrder->price_address_km + $objOrder->price_overtime - $objOrder->price_preferential), 'rowspan'=>4, 'namealign'=>'center'],
    ],
    [
        ['name'=>'基本服务费费用', 'value'=>floatval($objOrder->price_basic_insurance), 'valuealign'=>'center', 'namespan'=>2],
    ],
    [
        ['name'=>'手续费', 'value'=>floatval($objOrder->price_poundage),'valuealign'=>'center','namespan'=>2],
    ],
    [
        ['name'=>'超时租金', 'value'=>floatval($objOrder->price_overtime), 'valuealign'=>'center', 'namespan'=>2],
    ],
	[
        ['name'=>'公里数油耗费用', 'value'=>floatval($objOrder->price_address_km), 'valuealign'=>'center', 'namespan'=>2],
    ],
    [
        ['name'=>'折扣优惠', 'value'=>floatval(-$objOrder->price_preferential), 'valuealign'=>'center', 'namespan'=>2],
    ],
    [
        ['name'=>'服务费用', 'colspan'=>4, 'bold'=>true, 'namealign'=>'center', 'height'=>'24px'],
    ],
    [
        ['name'=>'费用项目', 'value'=>'小计', 'valuealign'=>'center', 'namespan'=>2],
        ['name'=>'合计', 'namealign'=>'center'],
    ],
    [
        ['name'=>'不计免赔', 'value'=>floatval($priceNonDeductible), 'valuealign'=>'center', 'namespan'=>2],
        ['name'=>floatval($priceNonDeductible + $priceNonDeductibleOvertime + $priceAgencyFee + $priceOther+$objOrder->price_take_car + $objOrder->price_return_car), 'rowspan'=>4, 'namealign'=>'center'],
    ],
    [
        ['name'=>'超时不计免赔', 'value'=>floatval($priceNonDeductibleOvertime), 'valuealign'=>'center', 'namespan'=>2],
    ],
    [
        ['name'=>$otherName1, 'value'=>floatval($priceOther1), 'valuealign'=>'center', 'namespan'=>2],
    ],
    [
        ['name'=>$otherName2, 'value'=>floatval($priceOther2), 'valuealign'=>'center', 'namespan'=>2],
    ],
    [
        ['name'=>'费用合计：&nbsp;'.floatval($objOrder->total_amount), 'namealign'=>'right', 'namespan'=>4],
    ],
    [
        ['name'=>'结算信息', 'colspan'=>4, 'bold'=>true, 'namealign'=>'center', 'height'=>'24px'],
    ],
    [
        ['name'=>'共计应收费用', 'value'=>floatval($objOrder->total_amount), 'valuealign'=>'right', 'namespan'=>3],
    ],
    [
        ['name'=>'已交租金', 'value'=>floatval($objOrder->paid_amount), 'valuealign'=>'right', 'namespan'=>3],
    ],
    [
        ['name'=>'找补金额', 'value'=>floatval($objOrder->total_amount - $objOrder->paid_amount), 'valuealign'=>'right', 'namespan'=>3],
    ],
    [
        ['name'=>'清退押金', 'value'=>'', 'valuealign'=>'right', 'namespan'=>3],
    ],
    [
        ['name'=>'结算时间：&nbsp;'.($objOrder->settlemented_at ? date('Y-m-d H:i', $objOrder->settlemented_at) : ''), 'value'=>'结算人员：&nbsp;'.$objOrder->getSettlementUserName(), 'namespan'=>2, 'colspan'=>2],
    ],
    [
        ['name'=>'发票信息', 'colspan'=>4, 'bold'=>true, 'namealign'=>'center', 'height'=>'24px'],
    ],
    [
        ['name'=>Html::radioList('_inv_sel', null, [1=>'需要发票', 0=>'不需要发票'], ['separator'=>'<br />']), 
            'value'=>'可开票金额：<br /><br />车辆租赁费用 &nbsp;'.floatval($objOrder->price_rent).'&nbsp;&nbsp;&nbsp;&nbsp; 增值服务费用 &nbsp;'.floatval($objOrder->price_optional_service),
            'colspan'=>3, 'height'=>'64px'],
    ],
];

$arrData2 = [
    [
        ['name'=>'取车信息', 'bold'=>true, 'namealign'=>'center', 'colspan'=>2, 'height'=>'24px'],
    ],
    [
        ['name'=>'客户信息', 'bold'=>true, 'namealign'=>'center', 'colspan'=>2],
    ],
    [
        ['name'=>'客户姓名', 'value'=>$objOrder->customer_name],
    ],
    [
        ['name'=>'身份证号', 'value'=>$objOrder->customer_id],
    ],
    [
        ['name'=>'联系电话', 'value'=>$objOrder->customer_telephone],
    ],
    [
        ['name'=>'车辆信息', 'bold'=>true, 'namealign'=>'center', 'colspan'=>2, 'height'=>'24px'],
    ],
    [
        ['name'=>'车辆牌号', 'value'=>$objVehicle->plate_number],
    ],
    [
        ['name'=>'车辆型号', 'value'=>$objVehicleModel->vehicle_model],
    ],
    [
        ['name'=>'车辆颜色', 'value'=>$objVehicle->getColorText()],
    ],
    [
        ['name'=>'取车城市/门店', 'value'=>$objOrder->getTakeCarCityAndOfficeText()],
    ],
    [
        ['name'=>'还车城市/门店', 'value'=>$objOrder->getReturnCarCityAndOfficeText()],
    ],
    [
        ['name'=>'燃油型号', 'value'=>$objVehicleModel->oil_label],
    ],
    [
        ['name'=>'出库里程', 'value'=>($objOrder->vehicle_outbound_mileage ? Yii::t('carrental', '{number} kilometers', ['number'=>$objOrder->vehicle_outbound_mileage]) : '')],
    ],
    [
        ['name'=>'还车里程', 'value'=>($objOrder->vehicle_inbound_mileage ? Yii::t('carrental', '{number} kilometers', ['number'=>$objOrder->vehicle_inbound_mileage]) : '')],
    ],
    /*[
        ['name'=>'预定取车时间', 'value'=>($objOrder->start_time ? date('Y-m-d H:i', $objOrder->start_time) : '')],
    ],
    [
        ['name'=>'预定还车时间', 'value'=>($objOrder->new_end_time ? date('Y-m-d H:i', $objOrder->new_end_time) : '')],
    ],
    [
        ['name'=>'实际取车时间', 'value'=>($objOrder->car_dispatched_at ? date('Y-m-d H:i', $objOrder->car_dispatched_at) : '')],
    ],
    [
        ['name'=>'实际还车时间', 'value'=>($objOrder->car_returned_at ? date('Y-m-d H:i', $objOrder->car_returned_at) : '')],
    ],*/
    [
        ['name'=>'预定取车时间', 'value'=>($objOrder->yuyue_time ? date('Y-m-d H:i', $objOrder->yuyue_time) : '')],
    ],
    [
        ['name'=>'预定还车时间', 'value'=>($objOrder->yuyue_end_time ? date('Y-m-d H:i', $objOrder->yuyue_end_time) : '')],
    ],
    [
        ['name'=>'实际取车时间', 'value'=>($objOrder->start_time ? date('Y-m-d H:i', $objOrder->start_time) : '')],
    ],
    [
        ['name'=>'实际还车时间', 'value'=>($objOrder->car_returned_at ? date('Y-m-d H:i', $objOrder->car_returned_at) : '')],
    ],
    [
        ['name'=>'车辆加油', 'value'=>''],
    ],
    [
        ['name'=>'登记时间', 'value'=>date('Y-m-d H:i', $objOrder->created_at)],
    ],
    [
        ['name'=>'登记人员', 'value'=>$objOrder->getEditUserName()],
    ],
    [
        ['name'=>'备注说明：'.$objOrder->remark.'<br />'.$objOrder->settlement_remark, 'namespan'=>2, 'namealign'=>'left', 'nameverticalalign'=>'top', 'height'=>'142px', 'allowwrap'=>true],
    ],
];

$sheetWidget = new \backend\widgets\PrintingSheetWidget();

$htmlArray = [];

$htmlArray[] = \backend\widgets\OrderPrintingTitleWidget::widget([
    'title'=>'结算单',
    'titleOptions'=>['style'=>"font-size:14px"],
    'serial'=>$objOrder->serial,
]);

$unitTextDays = Yii::t('carrental', 'days');

$htmlArray[] = Html::tag('h3', '合同号：'.$objOrder->serial, ['class'=>'contract', 'style'=>"font-weight:bold"]);

$htmlArray[] = $sheetWidget->beginSheet(['border'=>0]);
$htmlArray[] = Html::beginTag('tr');

$htmlArray[] = Html::beginTag('td', ['style'=>"width:55%;height:auto;padding:0px;"]);
$htmlArray[] = $sheetWidget->beginSheet(['style'=>"width:100%;height:660px;table-layout:fixed;border-collapse:collapse;"]);

$htmlArray[] = $sheetWidget->genDataRowsHtml($arrData1);

$htmlArray[] = $sheetWidget->endSheet();
$htmlArray[] = Html::endTag('td');
$htmlArray[] = Html::beginTag('td', ['style'=>"width:45%;height:auto;padding:0px;"]);
$htmlArray[] = $sheetWidget->beginSheet(['style'=>"width:100%;height:660px;table-layout:fixed;border-collapse:collapse;"]);

$htmlArray[] = $sheetWidget->genDataRowsHtml($arrData2);

$htmlArray[] = $sheetWidget->endSheet();
$htmlArray[] = Html::endTag('td');

$htmlArray[] = Html::endTag('tr');

$optionSignment = ['style'=>"margin:8px 8px 16px 2px;display:block"];
$signmentTime = time();

$objConfig = \common\models\Pro_rent_contract_options::findOne(['type' => \common\models\Pro_rent_contract_options::TYPE_SETTLEMENT]);

if (false) {
    $orderTips = ($objConfig ? str_replace('[r]', '<br />', $objConfig->instruction) : '&nbsp;');
    $htmlArray[] = Html::beginTag('tr');
    $htmlArray[] = Html::tag('td', $orderTips, ['colspan'=>6, 'style'=>"word-break:normal;white-space:normal;work-wrap:break-word;", 'width'=>"100%"]);
    $htmlArray[] = Html::endTag('tr');
}

$htmlArray[] = Html::beginTag('tr');
$htmlArray[] = Html::beginTag('td', ['style'=>"width:100%;height:auto;padding:0px;", 'colspan'=>2]);

$htmlArray[] = $sheetWidget->beginSheet();

$htmlArray[] = Html::beginTag('tr');
$htmlArray[] = Html::beginTag('td', ['style'=>"width:50%;"]);
$htmlArray[] = Html::tag('div', '出租方（甲方）：'.Yii::$app->params['app.company.fullname'], $optionSignment);
$htmlArray[] = Html::tag('div', '经办人：', $optionSignment);
$htmlArray[] = Html::tag('div', '日期：'.date('Y-m-d', $signmentTime), $optionSignment);
$htmlArray[] = Html::endTag('td');
$htmlArray[] = Html::beginTag('td', ['style'=>"width:50%"]);
$htmlArray[] = Html::tag('div', '承租方（乙方）：', $optionSignment);
$htmlArray[] = Html::tag('div', '经办人：', $optionSignment);
$htmlArray[] = Html::tag('div', '日期：'.date('Y-m-d', $signmentTime), $optionSignment);
$htmlArray[] = Html::endTag('td');
$htmlArray[] = Html::endTag('tr');

$htmlArray[] = $sheetWidget->endSheet();

$htmlArray[] = Html::endTag('td');
$htmlArray[] = Html::endTag('tr');

$htmlArray[] = $sheetWidget->endSheet();

if ($objConfig && $objConfig->footer) {
    $htmlArray[] = Html::tag('p', $objConfig->footer, []);
}

echo implode("\n", $htmlArray);

?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

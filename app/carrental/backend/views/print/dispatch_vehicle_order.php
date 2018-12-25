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
$htmlArray = [];

$showEn = false;

$htmlArray[] = \backend\widgets\OrderPrintingTitleWidget::widget([
    'title'=>Yii::t('carrental', 'Vehicle dispatch agreement'),
    'titleOptions'=>['style'=>"letter-spacing:9px;width:100%"],
    //'subtitle'=>'CAR RENTAL AGREEMENT',
    'subTitleOptions'=>['style'=>"width:100%"],
    'serial'=>$objOrder->serial,
]);
if($objOrder->pay_type == 6){
	$rent_name = '预定小时';
	$total_name = '份数/小时';
}else{
	$rent_name = '预定天数';
	$total_name = '份数/天数';
}
// order data
$orderData = [
    [
        'group'=>\Yii::t('locale', '{name} info', ['name'=>Yii::t('locale', 'Customer')]),
        'data'=> [
            [
                ['name'=>'姓名', 'nameen'=>'Name', 'value'=>$objOrder->customer_name, 'namewidth'=>'80px', 'valuewidth'=>'80px'],
                ['name'=>'移动电话', 'nameen'=>'Mobile', 'value'=>$objOrder->customer_telephone, 'namewidth'=>'80px', 'valuewidth'=>'140px'],
                ['name'=>'会员等级', 'nameen'=>'Membership', 'value'=>$objUserInfo->getMemberTypeText(), 'namewidth'=>'80px'],
            ],
            [
                ['name'=>'证件类型', 'nameen'=>'Type of Certificate', 'value'=>$objOrder->getIdentityTypeText()],
                ['name'=>'证件号码', 'nameen'=>'Certificate No.', 'value'=>$objOrder->customer_id],
                ['name'=>'联系地址', 'nameen'=>'Contact Address', 'value'=>$objOrder->customer_address, 'allowwrap'=>true],
            ],
        ]
    ],
    [
        'group'=>'订单详情',
        'data'=> [
            [
                ['name'=>'车牌号码', 'nameen'=>'Plate No.', 'value'=>$objVehicle->plate_number, 'namewidth'=>'70px'],
                ['name'=>'车型信息', 'nameen'=>'Car Model', 'value'=>$objVehicleModel->vehicle_model, 'namewidth'=>'70px'],
                ['name'=>'订单来源', 'nameen'=>'Reservation Channel', 'value'=>$objOrder->getOrderSourceText(), 'namewidth'=>'70px'],
                ['name'=>($objOrder->deposit_pay_source== \common\models\Pro_vehicle_order::PAY_TYPE_PRE_LICENSING?'预授权':'车辆押金'), 'nameen'=>'', 'value'=>floatval($objOrder->paid_deposit), 'namewidth'=>'90px'],
            ],
            [
                // ['name'=>'取车时间', 'nameen'=>'Pickup Date/Time', 'value'=>date('Y/m/d H:i', $objOrder->start_time), 'valuefontsize'=>'9px'],
                ['name'=>'预定取车时间', 'nameen'=>'Pickup Date/Time', 'value'=>date('Y/m/d H:i', $objOrder->yuyue_time), 'valuefontsize'=>'9px'],
                ['name'=>'取车城市', 'nameen'=>'Pickup City', 'value'=>$objOrder->getTakeCarCityText()],
                ['name'=>'取车门店', 'nameen'=>'Pickup Office', 'value'=>$objOrder->getTakeCarOfficeText()],
                //['name'=>'实际取车时间', 'nameen'=>'Pickup Date/Time', 'value'=>(empty($objOrder->car_dispatched_at) ? '' : date('Y/m/d H:i', $objOrder->car_dispatched_at)), 'valuefontsize'=>'9px'],
                ['name'=>'实际取车时间', 'nameen'=>'Pickup Date/Time', 'value'=>(empty($objOrder->start_time) ? '' : date('Y/m/d H:i', $objOrder->start_time)), 'valuefontsize'=>'9px'],
            ],
            [
                // ['name'=>'还车时间', 'nameen'=>'Dropoff Date/Time', 'value'=>date('Y/m/d H:i', $objOrder->new_end_time), 'valuefontsize'=>'9px'],
                ['name'=>'预定还车时间', 'nameen'=>'Dropoff Date/Time', 'value'=>date('Y/m/d H:i', $objOrder->yuyue_end_time), 'valuefontsize'=>'9px'],
                ['name'=>'还车城市', 'nameen'=>'Dropoff City', 'value'=>$objOrder->getReturnCarCityText()],
                ['name'=>'还车门店', 'nameen'=>'Dropoff Office', 'value'=>$objOrder->getReturnCarOfficeText()],
                //['name'=>'实际还车时间', 'nameen'=>'Dropoff Date/Time', 'value'=>(empty($objOrder->car_returned_at) ? '' : date('Y/m/d H:i', $objOrder->car_returned_at)), 'valuefontsize'=>'9px'],
				['name'=>'燃油标志', 'nameen'=>'Dropoff Office', 'value'=>$objVehicleModel->oil_label, 'valuefontsize'=>'9px'],
            ],
            [
                ['name'=>$rent_name, 'nameen'=>'No. of Days', 'value'=>$objOrder->rent_days],
                ['name'=>'客户备注', 'nameen'=>'Customer Remark', 'value'=>$objOrder->remark, 'colspan'=>5, 'allowwrap'=>true],
            ],
        ]
    ],
    [
        'group' => '费用明细',
        'data' => [
            [
                ['name'=>'实际订单金额：&nbsp;￥'.floatval($objOrder->total_amount), 'namespan'=>4, 'namealign'=>'right'],
            ],
            [
                ['name'=>'费用名称'],
                ['name'=>$total_name],
                ['name'=>'单价（￥）'],
                ['name'=>'合计（￥）'],
            ],
            [
                ['value'=>'基本租车费'],
                ['value'=>$objOrder->rent_days],
                ['value'=>floatval($objOrder->rent_per_day)],
                ['value'=>floatval($objOrder->price_rent)],
            ],
            /*[
                ['value'=>'基本服务费费'],
                ['value'=>1],
                ['value'=>floatval($objOrder->price_basic_insurance)],
                ['value'=>floatval($objOrder->price_basic_insurance)],
            ],
            [
                ['value'=>'手续费'],
                ['value'=>1],
                ['value'=>floatval($objOrder->price_poundage)],
                ['value'=>floatval($objOrder->price_poundage)],
            ],
            [
                ['value'=>'优惠金额'],
                ['value'=>1],
                ['value'=>floatval(-$objOrder->price_preferential)],
                ['value'=>floatval(-$objOrder->price_preferential)],
            ],*/
        ],
    ],
    [
        'group' => '支付明细',
        'data' => [
            [
                ['name'=>'实际支付金额：&nbsp;￥'.floatval($objOrder->paid_amount), 'namespan'=>2, 'namealign'=>'right'],
            ],
            [
                ['value'=>'取车-已支付金额（网上支付）', 'valuealign'=>'left'],
                ['value'=>floatval($objOrder->paid_amount)],
            ],
            [
                ['value'=>'取车-仍需支付金额（门店支付）', 'valuealign'=>'left'],
                ['value'=>floatval($objOrder->total_amount - $objOrder->paid_amount)],
            ],
            [
                ['value'=>'还车-仍需支付金额', 'valuealign'=>'left'],
                ['value'=>'0'],
            ],
        ],
    ],
    [
        'group' => '发票信息',
        'data' => [
            [
                ['name'=>'发票抬头'],
                ['name'=>'发票税号'],
                ['name'=>'发票金额'],
            ],
            [
                ['value'=>$objOrder->inv_title],
                ['value'=>$objOrder->inv_tax_number],
                ['value'=>(floatval($objOrder->inv_amount)?floatval($objOrder->inv_amount):'')],
            ],
        ],
    ],
];

$arrOrderDetialsInfo = &$orderData[1]['data'];
$arrServicePriceInfo = &$orderData[2]['data'];

// check address data
if (!empty($objOrder->address_take_car)) {
    $arrOrderDetialsInfo[] = [
        ['name'=>'送车上门地址', 'value'=>$objOrder->address_take_car, 'namespan'=>2, 'colspan'=>6]
    ];
}
if (!empty($objOrder->address_return_car)) {
    $arrOrderDetialsInfo[] = [
        ['name'=>'上门取车地址', 'value'=>$objOrder->address_return_car, 'namespan'=>2, 'colspan'=>6]
    ];
}

$arrOtherPriceFields = [];
$arrPreferentialFields = $objOrder->getPreferentialPriceFields();
$arrSkipPriceFields = ['price_rent'=>1, 'price_optional_service'=>1, 'price_deposit'=>1, 'price_deposit_violation'=>1];
foreach ($objOrder->getPriceAttributeFields() as $priceAttr) {
    if (!isset($arrSkipPriceFields[$priceAttr]) && !isset($arrPreferentialFields[$priceAttr])) {
        $_pri = floatval($objOrder[$priceAttr]);

        if ($_pri) {
            $arrServicePriceInfo[] = [
                ['value' => $objOrder->getAttributeLabel($priceAttr)],
                ['value'=>($priceAttr=='price_basic_insurance')?$objOrder->rent_days:1],
                ['value'=>($priceAttr=='price_basic_insurance')?$_pri/$objOrder->rent_days:$_pri],
                // ['value'=>$_pri],
                ['value'=>$_pri],
            ];
        }
    }
}

// 增值服务信息
$arrServicePriceObjects = \common\components\OptionsModule::getOptionalServiceObjectsArray();
$arrSelectedOptionalPrices = $objOrder->getOptionalServicePriceArray();
foreach ($arrSelectedOptionalPrices as $id => $val) {
    $serObj = (isset($arrServicePriceObjects[$id]) ? $arrServicePriceObjects[$id] : null);
    $o = [
        ['value' => ($serObj ? (isset($arrServicePriceObjects[$id]) ? $arrServicePriceObjects[$id]->name : '') : $id)],
        ['value'=>$val['count']],
        ['value'=>$val['price']],
        ['value'=>$val['price']*$val['count']],
    ];
    
    $arrServicePriceInfo[] = $o;
}

foreach ($arrPreferentialFields as $priceAttr => $_) {
    $_pri = floatval($objOrder[$priceAttr]);
    if ($_pri) {
        $arrServicePriceInfo[] = [
            ['value' => $objOrder->getAttributeLabel($priceAttr)],
            ['value'=>1],
            ['value'=>-$_pri],
            ['value'=>-$_pri],
        ];
    }
}

foreach ($orderData as $groupData) {
    $htmlArray[] = Html::tag('h3', $groupData['group'], ['class'=>'contract', 'style'=>"font-weight:bold"]);
    $htmlArray[] = Html::beginTag('table', ['class'=>'contract', 'border'=>'1', 'cellspacing'=>0, 'cellpadding'=>"0", 'style'=>"table-layout:fixed;width:100%"]);
    $htmlArray[] = Html::beginTag('tbody');
    foreach ($groupData['data'] as $row) {
        $htmlArray[] = Html::beginTag('tr', ['height'=>'24px']);
        foreach ($row as $col) {
            $colName = Html::tag('span', (isset($col['name']) ? $col['name'] : ''), ['style'=>'font-size:12px;font-weight:bold;']);
            $colValue = (isset($col['value']) ? $col['value'] : '');
            $styleExtra = "";
            if (isset($col['allowwrap'])) {
                //$styleExtra .= "word-break:none;white-space:none;";
                //$styleExtra .= "word-wrap:break-word;";
                $styleExtra .= "word-break:break-all;word-wrap:break-word;white-space:normal;";
            }
            $nameAlign = 'center';
            $valueAlign = 'center';
            if (isset($col['namealign'])) {
                $nameAlign = $col['namealign'];
            }
            if (isset($col['valuealign'])) {
                $valueAlign = $col['valuealign'];
            }
            if (isset($col['valuefontsize'])) {
                $styleExtra .= "font-size:{$col['valuefontsize']};";
            }
            $nameOptions = ['style'=>"text-align:{$nameAlign};nowrap:nowrap;"];
            $valueOptions = ['style'=>"text-align:{$valueAlign};{$styleExtra}"];
            if ($showEn && isset($col['nameen']) && !empty($col['nameen'])) {
                $colName .= '<br />'.  Html::tag('span', $col['nameen'], ['style'=>'font-size:9px;nowrap:nowrap;font-weight:bold;']);
            }
            if (isset($col['colspan'])) {
                $valueOptions['colspan'] = $col['colspan'];
            }
            if (isset($col['namespan'])) {
                $nameOptions['colspan'] = $col['namespan'];
            }
            if (isset($col['namewidth'])) {
                $nameOptions['width'] = $col['namewidth'];
            }
            if (isset($col['valuewidth'])) {
                $valueOptions['width'] = $col['valuewidth'];
            }
            if (isset($col['name'])) {
                $htmlArray[] = Html::tag('td', $colName, $nameOptions);
            }
            if (isset($col['value'])) {
                $htmlArray[] = Html::tag('td', ($colValue === '' ? '&nbsp;' : $colValue), $valueOptions);
            }
        }
        $htmlArray[] = Html::endTag('tr');
    }
    $htmlArray[] = Html::endTag('tbody');
    $htmlArray[] = Html::endTag('table');
}

$htmlArray[] = Html::tag('p', '注：请核对您的发票寄送地址，如果发票寄送地址不够详细，可能导致发票不能及时寄送到达。', ['style'=>"width:100%;font-size:11px"]);

$htmlArray[] = Html::tag('h3', '现金退款账户信息', ['class'=>'contract', 'style'=>'font-weight:bold']);
$htmlArray[] = Html::beginTag('table', ['class'=>'contract', 'border'=>'1', 'cellspacing'=>0, 'cellpadding'=>0, 'style'=>"width:100%"]);
$htmlArray[] = Html::beginTag('tbody');
$arrRefundInfo = [
    ['银行账户号码', '户名', '开户银行', '备注'],
    [(empty($objOrder->refund_account_number) ? '&nbsp;' : $objOrder->refund_account_number), 
        (empty($objOrder->refund_account_name) ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : $objOrder->refund_account_name), 
        (empty($objOrder->refund_bank_name) ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : $objOrder->refund_bank_name), 
        (empty($objOrder->refund_remark) ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : $objOrder->refund_remark)],
];
foreach ($arrRefundInfo as $row) {
    $htmlArray[] = Html::beginTag('tr');
    foreach ($row as $col) {
        $htmlArray[] = Html::tag('td', $col);
    }
    $htmlArray[] = Html::endTag('tr');
}
$htmlArray[] = Html::endTag('tbody');
$htmlArray[] = Html::endTag('table');

$htmlArray[] = Html::tag('h3', '车辆保证金预授权转违章押金', ['class'=>'contract']);
$htmlArray[] = Html::tag('p', '①客户还车后，车辆保证金自动转为违章押金，若还车后10个工作日内无违章则给予退还。', ['style'=>"width:100%;font-size:11px"]);
$htmlArray[] = Html::tag('p','②押金退还后如后期车辆出现违章我们会在第一时间联系您，并希望您及时处理。',['style'=>"width:100%;font-size:11px"]);
//$htmlArray[] = Html::tag('p', '①客户同意并签名：本人同意从车辆保证金预授权________元中联机完成________元作为车辆违章押金。客户签名____________', ['style'=>"width:100%;font-size:11px"]);
$htmlArray[] = Html::beginTag('p', ['style'=>"width:100%;font-size:11px"]);
$htmlArray[] = Html::tag('span', '③其他（经办人备注）：_________________________________________________________', ['style'=>"font-size:11px"]);
$htmlArray[] = Html::tag('span', '经办人签名________________', ['style'=>"text-align:right;float:right;font-size:11px"]);
$htmlArray[] = Html::endTag('p');
$htmlArray[] = Html::tag('p', '&nbsp;');
$htmlArray[] = Html::tag('p', '客户签名________________', ['style'=>"width:100%;text-align:right;font-size:11px"]);

$objConfig = \common\models\Pro_rent_contract_options::findOne(['type' => \common\models\Pro_rent_contract_options::TYPE_DISPATCHING]);
$orderTips = ($objConfig ? str_replace('[r]', '<br />', $objConfig->instruction) : '');

if (!empty($orderTips)) {
    $htmlArray[] = Html::tag('h3', '备注', ['class'=>'contract']);
    $htmlArray[] = Html::beginTag('table', ['class'=>'contract', 'border'=>'1', 'cellspacing'=>0, 'cellpadding'=>0, 'style'=>"width:100%"]);
    $htmlArray[] = Html::beginTag('tbody');
    $htmlArray[] = Html::beginTag('tr');
    $htmlArray[] = Html::tag('td', $orderTips, ['style'=>"word-break:normal;white-space:normal;work-wrap:break-word;", 'width'=>"100%"]);
    $htmlArray[] = Html::endTag('tr');
    $htmlArray[] = Html::endTag('tbody');
    $htmlArray[] = Html::endTag('table');
}

if ($objConfig && $objConfig->footer) {
    $htmlArray[] = Html::tag('p', $objConfig->footer, []);
}

echo implode("\n", $htmlArray);

?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
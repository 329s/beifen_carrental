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

$htmlArray[] = \backend\widgets\OrderPrintingTitleWidget::widget([
    'title'=>Yii::$app->params['app.company.fullname'].'预定单',
    'titleOptions'=>['style'=>"font-size:14px"],
    'serial'=>$objOrder->serial,
]);

$unitTextDays = Yii::t('carrental', 'days');

$htmlArray[] = Html::tag('h3', '合同号：'.$objOrder->serial, ['class'=>'contract', 'style'=>"font-weight:bold"]);

$arrBookingData = [
    [
        ['name'=>'客户信息', 'colspan'=>6, 'bold'=>true],
    ],
    [
        ['name'=>'客户名称', 'value'=>$objOrder->customer_name],
        ['name'=>'身份证号', 'value'=>$objOrder->customer_id],
        ['name'=>'联系电话', 'value'=>$objOrder->customer_telephone],
    ],
    [
        ['name'=>'现在住所', 'value'=>$objOrder->customer_address, 'colspan'=>5],
    ],
    [
        ['name'=>'车辆信息', 'colspan'=>6, 'bold'=>true],
    ],
    [
        ['name'=>'车辆牌号', 'value'=>$objVehicle->plate_number],
        ['name'=>'车辆型号', 'value'=>$objVehicleModel->vehicle_model],
        ['name'=>'车辆颜色', 'value'=>$objVehicle->getColorText()],
    ],
    [
        ['name'=>'燃油型号', 'value'=>$objVehicleModel->oil_label],
        ['name'=>'出库里程', 'value'=>($objOrder->vehicle_outbound_mileage ? Yii::t('carrental', '{number} kilometers', ['number'=>$objOrder->vehicle_outbound_mileage]) : ''), 'colspan'=>3, 'valuealign'=>'right'],
    ],
    [
        ['name'=>'租赁信息', 'colspan'=>6, 'bold'=>true],
    ],
    [
        ['name'=>'承租时间', 'value'=>date('Y-m-d H:i', $objOrder->start_time)],
        ['name'=>'还车时间', 'value'=>date('Y-m-d H:i', $objOrder->new_end_time)],
        ['name'=>'签约租期', 'value'=>"{$objOrder->rent_days} {$unitTextDays}", 'valuealign'=>'right'],
    ],
    [
        ['name'=>'车辆押金', 'value'=> ($objOrder->paid_deposit ? '' : '未交押金 ').\Yii::t('carrental', '{number} RMB', ['number'=>($objOrder->paid_deposit?$objOrder->paid_deposit:$objOrder->getTotalDepositPrice())]), 'valuealign'=>'right', 'colspan'=>5],
    ],
    [
        ['name'=>'预交租金', 'value'=> ($objOrder->paid_amount ? '' : '未预交租金 ').\Yii::t('carrental', '{number} RMB', ['number'=>$objOrder->paid_amount]), 'valuealign'=>'right', 'colspan'=>5],
    ],
    [
        ['name'=>'租金标准', 'value'=> \Yii::t('carrental', '{number} RMB/day, total {count} days', ['number'=>$objOrder->rent_per_day, 'count'=>$objOrder->rent_days]), 'valuealign'=>'right', 'colspan'=>5],
    ],
    [
        ['name'=>'超时标准', 'value'=> Yii::t('carrental', '{number} RMB/hour', ['number'=>$objOrder->unit_price_overtime]), 'valuealign'=>'right', 'colspan'=>5],
    ],
    [
        ['name'=>'里程限制', 'value'=> '里程不限', 'valuealign'=>'right', 'colspan'=>5],
    ],
];

$arrServicePriceObjects = \common\components\OptionsModule::getOptionalServiceObjectsArray();
$arrSelectedOptionalPrices = $objOrder->getOptionalServicePriceArray();
foreach ($arrSelectedOptionalPrices as $id => $val) {
    $serObj = (isset($arrServicePriceObjects[$id]) ? $arrServicePriceObjects[$id] : null);
    $o = ['valuealign'=>'right', 'colspan'=>5];
    if ($serObj) {
        $o['name'] = (isset($arrServicePriceObjects[$id]) ? $arrServicePriceObjects[$id]->name : '');
        
        if ($serObj->unit_type == \common\models\Pro_service_price::UNIT_TYPE_DAILY) {
            $o['value'] = \Yii::t('carrental', '{number} RMB/day, total {count} days', ['number'=>$val['price'], 'count'=>$val['count']]);
        }
        elseif ($serObj->unit_type == \common\models\Pro_service_price::UNIT_TYPE_KM) {
            $o['value'] = \Yii::t('carrental', '{number} RMB/km, total {count} km', ['number'=>$val['price'], 'count'=>$val['count']]);
        }
        else {
            $o['value'] = \Yii::t('carrental', '{number} RMB/unit, total {count} units', ['number'=>$val['price'], 'count'=>$val['count']]);
        }
    }
    else {
        $o['name'] = $id;
        $o['value'] = $val['price'].'*'.$val['count'];
    }
    
    $arrBookingData[] = [$o];
}

$arrBookingData[] = [
    ['name'=>'登记时间', 'value'=>date('Y-m-d H:i', $objOrder->created_at)],
    ['name'=>'登记人员', 'value'=>$objOrder->getEditUserName(), 'colspan'=>3],
];
$arrBookingData[] = [
    ['name'=>'备注说明', 'value'=>$objOrder->remark, 'colspan'=>5],
];

$htmlArray[] = Html::beginTag('table', ['class'=>'contract', 'border'=>'1', 'cellspacing'=>0, 'cellpadding'=>0, 'style'=>"width:100%"]);
$htmlArray[] = Html::beginTag('tbody');

$htmlArray[] = Html::beginTag('table', ['class'=>'contract', 'border'=>'1', 'cellspacing'=>0, 'cellpadding'=>0, 'style'=>"width:100%"]);
$htmlArray[] = Html::beginTag('tbody');
foreach ($arrBookingData as $row) {
    $htmlArray[] = Html::beginTag('tr');
    foreach ($row as $col) {
        $colName = Html::tag('span', ($col['name'] ? $col['name'] : '&nbsp;'), ['style'=>'font-size:12px;']);
        $colValue = null;
        $nameAlign = 'left';
        $valueAlign = 'left';
        if (isset($col['value'])) {
            $colValue = ($col['value'] ? $col['value'] : '&nbsp;');
        }
        if (isset($col['namealign'])) {
            $nameAlign = $col['namealign'];
        }
        if (isset($col['valuealign'])) {
            $valueAlign = $col['valuealign'];
        }
        $nameExtraStyle = '';
        if (isset($col['bold'])) {
            $nameExtraStyle = "font-weight:bold";
        }
        $nameOptions = ['style'=>"text-align:{$nameAlign};nowrap:nowrap;width:100px;{$nameExtraStyle}"];
        $valueOptions = ['style'=>"text-align:{$valueAlign};width:100px"];
        if (isset($col['colspan'])) {
            if ($colValue) {
                $valueOptions['colspan'] = $col['colspan'];
            }
            else {
                $nameOptions['colspan'] = $col['colspan'];
            }
        }
        if (isset($col['namespan'])) {
            $nameOptions['colspan'] = $col['namespan'];
        }
        
        $htmlArray[] = Html::tag('td', $colName, $nameOptions);
        if ($colValue) {
            $htmlArray[] = Html::tag('td', Html::tag('span', $colValue, ['style'=>'font-size:12px;']), $valueOptions);
        }
    }
    $htmlArray[] = Html::endTag('tr');
}

$optionSignment = ['style'=>"margin:8px 8px 16px 2px;display:block"];
$signmentTime = time();

$objConfig = \common\models\Pro_rent_contract_options::findOne(['type' => \common\models\Pro_rent_contract_options::TYPE_BOOKING]);
$orderTips = ($objConfig ? str_replace('[r]', '<br />', $objConfig->instruction) : '&nbsp;');
$htmlArray[] = Html::beginTag('tr');
$htmlArray[] = Html::tag('td', $orderTips, ['colspan'=>6, 'style'=>"word-break:normal;white-space:normal;work-wrap:break-word;", 'width'=>"100%"]);
$htmlArray[] = Html::endTag('tr');

$htmlArray[] = Html::beginTag('tr');
$htmlArray[] = Html::beginTag('td', ['colspan'=>3]);
$htmlArray[] = Html::tag('div', '出租方（甲方）：'.Yii::$app->params['app.company.fullname'], $optionSignment);
$htmlArray[] = Html::tag('div', '经办人：', $optionSignment);
$htmlArray[] = Html::tag('div', '日期：'.date('Y-m-d', $signmentTime), $optionSignment);
$htmlArray[] = Html::endTag('td');
$htmlArray[] = Html::beginTag('td', ['colspan'=>3]);
$htmlArray[] = Html::tag('div', '承租方（乙方）：', $optionSignment);
$htmlArray[] = Html::tag('div', '经办人：', $optionSignment);
$htmlArray[] = Html::tag('div', '日期：'.date('Y-m-d', $signmentTime), $optionSignment);
$htmlArray[] = Html::endTag('td');
$htmlArray[] = Html::endTag('tr');

$htmlArray[] = Html::endTag('tbody');
$htmlArray[] = Html::endTag('table');

if ($objConfig && $objConfig->footer) {
    $htmlArray[] = Html::tag('p', $objConfig->footer, []);
}

echo implode("\n", $htmlArray);

?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
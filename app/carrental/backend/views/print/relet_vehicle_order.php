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
    'title'=>Yii::$app->params['app.company.fullname'].'续租单',
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
        ['name'=>'续租信息', 'colspan'=>6, 'bold'=>true],
    ],
    [
        ['name'=>'原还车时间', 'value'=>date('Y-m-d H:i', $objOrderRelet->origion_end_time)],
        ['name'=>'新还车时间', 'value'=>date('Y-m-d H:i', $objOrderRelet->new_end_time)],
        ['name'=>'续交租金', 'value'=>($objOrderRelet->paid_amount ? \Yii::t('carrental', '{number} RMB', ['number'=>$objOrderRelet->paid_amount]) : '未续交租金'), 'valuealign'=>'right'],
    ],
    [
        ['name'=>'续交额度', 'value'=>\Yii::t('carrental', '{number} RMB', ['number'=>$objOrderRelet->total_amount]), 'valuealign'=>'right'],
        ['name'=>'登记人员', 'value'=>$objOrderRelet->getEditUserName()],
        ['name'=>'登记时间', 'value'=>date('Y-m-d H:i', $objOrderRelet->created_at)],
    ],
    [
        ['name'=>'备注说明', 'value'=>$objOrderRelet->remark, 'colspan'=>5],
    ],
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

$objConfig = \common\models\Pro_rent_contract_options::findOne(['type' => \common\models\Pro_rent_contract_options::TYPE_BOOKING]);
if ($objConfig && $objConfig->footer) {
    $htmlArray[] = Html::tag('p', $objConfig->footer, []);
}

echo implode("\n", $htmlArray);

?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

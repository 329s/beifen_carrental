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

$urlRoot = \common\helpers\Utils::getRootUrl();
$modelImageUrl = "{$urlRoot}assets/images/carrental/validation/car_validation_model.jpg";

$htmlArray[] = \backend\widgets\OrderPrintingTitleWidget::widget([
    'title'=> Yii::$app->params['app.company.fullname'].'验车单',
    'titleOptions'=>['style'=>"font-size:14px"],
    'serial'=>$objOrder->serial,
]);

// order data
$orderData = [
    [
        ['name'=>'合同编号', 'value'=>$objOrder->serial, 'colspan'=>2],
        ['name'=>'客户名称', 'value'=>$objOrder->customer_name, 'colspan'=>2],
        ['name'=>'联系电话', 'value'=>$objOrder->customer_fixedphone, 'colspan'=>2],
    ],
    [
        ['name'=>'车辆牌号', 'value'=>$objVehicle->plate_number, 'colspan'=>2],
        ['name'=>'车辆型号', 'value'=>$objVehicleModel->vehicle_model, 'colspan'=>2],
        ['name'=>'出车门店', 'value'=>$objOrder->getTakeCarOfficeText(), 'colspan'=>2],
    ],
    [
        ['name'=>'验车项目', 'value1'=>'出车时', 'value2'=>'还车时'],
        ['name'=>'验车项目', 'value1'=>'出车时', 'value2'=>'还车时'],
        ['name'=>'验车项目', 'value1'=>'出车时', 'value2'=>'还车时'],
    ],
    [
        ['name'=>'车辆数据信息', 'colspan'=>3],
        ['name'=>'油量情况', 'value1'=>'', 'value2'=>''],
        ['name'=>'当前公里', 'value1'=>'', 'value2'=>''],
    ],
];

$arrAllValidationOptions = \common\components\VehicleModule::getVehicleValidationOptionsArray();
$arrImageGroupData = [];
foreach ($arrAllValidationOptions as $k => $group) {
    $groupColIndex = 0;
    $groupName = $group['name'];
    $groupType = $group['type'];
    $groupArr = [];
    if (isset($group['children'])) {
        $groupArr = &$group['children'];
    }
    if ($groupType == \common\models\Pro_vehicle_validation_config::TYPE_IMAGES) {
        // images
        $o = ['name'=>$groupName, 'images'=>[]];
        // 区分出车与还车照片
        if ($objPickupValidation) {
            $arrImages = $objPickupValidation->getValidationImagesByValidationOptionsId($k);
            foreach ($arrImages as $imgIdx => $imgInfo) {
                $o['images'][] = $imgInfo->getImageUrl();
            }
        }
        if ($objDropoffValidation) {
            $arrImages = $objDropoffValidation->getValidationImagesByValidationOptionsId($k);
            foreach ($arrImages as $imgIdx => $imgInfo) {
                $o['images'][] = $imgInfo->getImageUrl();
            }
        }
        $arrImageGroupData[] = $o;
    }
    else {
        $colspan = 1;
        $excount = 0;
        $count = count($groupArr);
        if ($count % 3 == 0) {
            $colspan = 9;
        }
        elseif ($count % 3 == 1) {
            $colspan = 6;
            $excount = 1;
        }
        else {
            $colspan = 3;
            $excount = 2;
        }
        
        $r = [['name'=>$groupName, 'colspan'=>$colspan]];
        $i = 0;
        $j = 0;
        foreach ($groupArr as $row) {
            if ($row->type == \common\models\Pro_vehicle_validation_config::TYPE_OPTIONS) {
                $col = ['name'=>$row->name, 'value1'=>'', 'value2'=>''];
                if ($i >= $excount) {
                    if ($j % 3 == 0) {
                        $orderData[] = $r;
                        $r = [];
                    }
                    $j++;
                }
                $r[] = $col;
                
                $i++;
            }
        }
        
        if (!empty($r)) {
            $orderData[] = $r;
        }
    }
}

$htmlArray[] = Html::beginTag('table', ['class'=>'contract', 'border'=>'1', 'cellspacing'=>0, 'cellpadding'=>0, 'style'=>"width:100%"]);
$htmlArray[] = Html::beginTag('tbody');
foreach ($orderData as $row) {
    $htmlArray[] = Html::beginTag('tr');
    foreach ($row as $col) {
        $colName = Html::tag('span', ($col['name'] ? $col['name'] : '&nbsp;'), ['style'=>'font-size:12px;']);
        $nameOptions = ['style'=>'text-align:left;nowrap:nowrap;width:100px'];
        $colValue0 = null;
        $colValue1 = null;
        if (isset($col['value'])) {
            $colValue0 = ($col['value'] ? $col['value'] : '&nbsp;');
        }
        elseif (isset($col['value1'])) {
            $colValue0 = ($col['value1'] ? $col['value1'] : '&nbsp;');
            $colValue1 = ($col['value2'] ? $col['value2'] : '&nbsp;');
        }
        $valueOptions = ['style'=>'text-align:center;width:100px'];
        if (isset($col['colspan'])) {
            if ($colValue0) {
                $valueOptions['colspan'] = $col['colspan'];
            }
            else {
                $nameOptions['colspan'] = $col['colspan'];
            }
        }
        
        $htmlArray[] = Html::tag('td', $colName, $nameOptions);
        if ($colValue0) {
            $htmlArray[] = Html::tag('td', Html::tag('span', $colValue0, ['style'=>'font-size:12px;']), $valueOptions);
        }
        if ($colValue1) {
            $htmlArray[] = Html::tag('td', Html::tag('span', $colValue1, ['style'=>'font-size:12px;']), $valueOptions);
        }
    }
    $htmlArray[] = Html::endTag('tr');
}

// images
$htmlArray[] = Html::beginTag('tr');
$htmlArray[] = Html::tag('td', Html::img($modelImageUrl), ['colspan'=>4]);
$htmlArray[] = Html::tag('td', Html::img($modelImageUrl), ['colspan'=>5]);
$htmlArray[] = Html::endTag('tr');

$htmlArray[] = Html::beginTag('tr');
$htmlArray[] = Html::tag('td', '说明：左图出车 右图还车 图例：√：正常完好齐全 N：缺少 —：划痕 ×：裂痕 ○：凹陷 ●：脱落 ✩：其他', ['style'=>"text-align:left;font-size:12px;", 'colspan'=>9]);
$htmlArray[] = Html::endTag('tr');

$htmlArray[] = Html::beginTag('tr');
$htmlArray[] = Html::tag('td', '出车说明：', ['colspan'=>4, 'style'=>"height:32px;text-align:left;vertical-align:top"]);
$htmlArray[] = Html::tag('td', '还车说明：', ['colspan'=>5, 'style'=>"height:32px;text-align:left;vertical-align:top"]);
$htmlArray[] = Html::endTag('tr');

$htmlArray[] = Html::beginTag('tr');
$htmlArray[] = Html::tag('td', '<span>双方对上述发车情况确认无疑。</span><br /><span style="padding-right:100px">出租方签字：</span><span>承租方签字：</span><br /><span>验车时间：</span>', 
    ['colspan'=>4, 'style'=>"height:32px;text-align:left;vertical-align:top"]);
$htmlArray[] = Html::tag('td', '<span>双方对上述收车情况确认无疑。</span><br /><span style="padding-right:100px">出租方签字：</span><span>承租方签字：</span><br /><span>验车时间：</span>', 
    ['colspan'=>5, 'style'=>"height:32px;text-align:left;vertical-align:top"]);
$htmlArray[] = Html::endTag('tr');

$htmlArray[] = Html::endTag('tbody');
$htmlArray[] = Html::endTag('table');

$htmlArray[] = Html::tag('div', '', ['style'=>"page-break-after: always;"]);

// page 2
$htmlArray[] = Html::beginTag('table', ['class'=>'contract', 'border'=>'1', 'cellspacing'=>0, 'cellpadding'=>0, 'style'=>"width:100%"]);
$htmlArray[] = Html::beginTag('tbody');
foreach ($arrImageGroupData as $imageGroup) {
    $htmlArray[] = Html::beginTag('tr');
    $htmlArray[] = Html::tag('td', $imageGroup['name'], ['style'=>"font-size:12px;text-align:left"]);
    $htmlArray[] = Html::endTag('tr');
    $htmlArray[] = Html::beginTag('tr');
    if (empty($imageGroup['images'])) {
        $htmlArray[] = Html::tag('td', '&nbsp', ['style'=>"font-size:12px;text-align:left"]);
    }
    else {
        $htmlArray[] = Html::beginTag('td');
        foreach ($imageGroup['images'] as $imgUrl) {
            $htmlArray[] = Html::img($imgUrl, ['style'=>"height:240px;padding:4px 4px 4px 4px;"]);
        }
        $htmlArray[] = Html::endTag('td');
    }
    $htmlArray[] = Html::endTag('tr');
}
$htmlArray[] = Html::endTag('tbody');
$htmlArray[] = Html::endTag('table');

echo implode("\n", $htmlArray);

?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle_violation();
$formTitle = '';

$urlInfo = ['vehicle/violation_list', 'vehicle_id'=>$vehicleId, 'order_id'=>$orderId];

if (isset($status)) {
    $urlInfo['status'] = $status;
}
else {
    $status = 0; 
}

$columnFields = ['id', 'violated_at', 'notified_at', 'score', 'penalty', 'status', 'description', 'edit_user_id', 'created_at', 'updated_at'];

$objViolationModel = new \common\models\Pro_vehicle_violation();
$objModel = new \common\models\Pro_vehicle_model();

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
    'saveUrl' => \yii\helpers\Url::to(['vehicle/violation_add']),
    'updateUrl' => \yii\helpers\Url::to(['vehicle/violation_edit']),
    'deleteUrl' => \yii\helpers\Url::to(['vehicle/violation_delete']),
];

$totalHeight = 472;
$headerHeight = 40;

$arrData = [
    [
        [\Yii::t('locale', 'Vehicle'), (isset($objVehicle) ? $objVehicle->plate_number : 'Unknown')],
        [$objModel->getAttributeLabel('vehicle_model'), $vehicleModelName],
    ]
];

if ($objOrder) {
    $arrData[] = [
        [$objOrder->getAttributeLabel('serial'), $objOrder->serial],
        [$objOrder->getAttributeLabel('start_time'), date('Y-m-d H:i:s', $objOrder->start_time)],
        [$objOrder->getAttributeLabel('end_time'), date('Y-m-d H:i:s', $objOrder->new_end_time)],
    ];
    $headerHeight += 20;
}

$canAdd = \Yii::$app->user->can('vehicle/violation_add');
$canEdit = \Yii::$app->user->can('vehicle/violation_edit');
$toolbarArray = [
    $canAdd ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', []) : null,
    $canEdit ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', []) : null,
    \Yii::$app->user->can('vehicle/violation_delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,
    ($canAdd || $canEdit) ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_ACCEPT, '', '') : null,
    ($canAdd || $canEdit) ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REJECT, '', '') : null,
];

$dgId = CMyHtml::getIDPrefix().'dg_'.CMyHtml::genID();

$containerPart = CMyHtml::datagrid(Yii::t('carrental', 'Violation input'), // $title
    $objViolationModel,    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>$dgId],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$width = '962px';

$htmlArray = [];

$htmlArray[] = CMyHtml::beginMainPageLayoutRegion($width, "{$headerHeight}px", '', 'north');
$htmlArray[] = \common\helpers\CMyHtml::beginPanel('', ['height'=>"{$headerHeight}px"]);
$htmlArray[] = \yii\helpers\Html::style(".dv-table td {border:0; } .dv-label {font-weight:bold; color:#15428B; padding:5px 5px 5px 25px; }", ['type'=>'text/css']);
$htmlArray[] = \common\helpers\CMyHtml::beginTag('table', ['class'=>'dv-table', 'border'=>'0', 'style'=>'']);
$htmlArray[] = \common\helpers\CMyHtml::beginTag('tbody');

foreach ($arrData as $row) {
    $htmlArray[] = \common\helpers\CMyHtml::beginTag('tr');
    foreach ($row as $ele) {
        $htmlArray[] = \common\helpers\CMyHtml::tag('td', $ele[0], ['class'=>'dv-label']);
        $htmlArray[] = \common\helpers\CMyHtml::tag('td', $ele[1]);
    }
    $htmlArray[] = \common\helpers\CMyHtml::endTag('tr');
}

$htmlArray[] = \common\helpers\CMyHtml::endTag('tbody');
$htmlArray[] = \common\helpers\CMyHtml::endTag('table');
$htmlArray[] = \common\helpers\CMyHtml::endPanel();

$htmlArray[] = CMyHtml::endMainPageLayoutRegion();
$htmlArray[] = CMyHtml::beginMainPageLayoutRegion($width, ($totalHeight - $headerHeight)."px", '', 'center');
$htmlArray[] = $containerPart;
$htmlArray[] = CMyHtml::endMainPageLayoutRegion();

$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$violationDefaultStatus = common\models\Pro_vehicle_violation::STATUS_UNPROCESSED;

$arrScripts = [];
$arrScripts[] = <<<EOD

setTimeout(function() {
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'queryParams', {vehicle_id:{$vehicleId}, order_id:{$orderId}, '{$yiiCsrfKey}':'{$yiiCsrfToken}'});
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'modelName', '{$objForm->formName()}');
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {vehicle_id:{$vehicleId}, order_id:{$orderId}, status:{$violationDefaultStatus}});
}, 110);
EOD;

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);

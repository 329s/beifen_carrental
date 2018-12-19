<?php

use common\helpers\CMyHtml;

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'plate_number', Yii::t('locale', 'Plate number'), '', []),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'serial', Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')]), '', []),
	CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'status', '违章状态', \common\models\Pro_violation_inquiry::getInquiryStatusArray(), ['searchOnChange'=>true, 'style'=>'width:120px']),
];
if($plate_number){
	$urlInfo = ['inquiry/inquiry_list'];
	$urlInfo['plate_number'] = $plate_number;
}else{
	$urlInfo = ['inquiry/inquiry_list'];
}

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
];
// $columnFields = ['id','plate_number','time','address','content','price','score','status','agency','add_time','Operation'];//'province','city','town',
$columnFields = ['id','hphm','date','area','act','money','fen','status','add_time','Operation'];//聚合

$toolbarArray[] = CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_GETCHANGES, \Yii::t('locale', 'According vehicle') , ['tab'=>\yii\helpers\Url::to(['inquiry/according_vehicle'])]);

$toolbarArray[] = \Yii::$app->user->can('inquiry/query_vehicle_violation') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_GETCHANGES, \Yii::t('locale', 'Query vehicle violation information') , ['dialog'=>\yii\helpers\Url::to(['inquiry/query_vehicle_violation'])]) : null;
$toolbarArray[] = \Yii::$app->user->can('inquiry/view_log') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_GETCHANGES, \Yii::t('locale', 'View Log') , ['tab'=>\yii\helpers\Url::to(['inquiry/view_log'])]) : null;



echo CMyHtml::datagrid('车辆违章列表', // $title
    new \common\models\Pro_violation_inquiry(),    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [
		'data-options'=>[
            'sortName' => 'id',
            'sortOrder' => 'desc',
        ],
	],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
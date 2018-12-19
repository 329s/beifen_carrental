<?php

use common\helpers\CMyHtml;
if($type == 3){
	$toolbarArray = [
	    Yii::$app->user->can('user/buy_car') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', 'Buy apply'), ['tab'=>\yii\helpers\Url::to(['user/related_apply?type=1'])], 'icon-house_blue') : null,
	    Yii::$app->user->can('user/invest_apply') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Join application'), ['tab'=>\yii\helpers\Url::to(['user/related_apply?type=2'])], 'icon-house_blue') : null,
	    Yii::$app->user->can('user/sign_up') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Sign up customer'), ['tab'=>\yii\helpers\Url::to(['user/related_apply?type=3'])], 'icon-house_blue') : null,
	    // Yii::$app->user->can('user/sign_up') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, '导出未处理信息', ['tab'=>\yii\helpers\Url::to(['user/print'])], 'icon-house_blue') : null,
	    Yii::$app->user->can('user/sign_up') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', 'Excel message', ['operation' => Yii::t('locale', 'Management')]), ['tab'=>\yii\helpers\Url::to(['user/print'])], 'icon-printer') : null,


	];
}else{
	$toolbarArray = [
	    Yii::$app->user->can('user/buy_car') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', 'Buy apply'), ['tab'=>\yii\helpers\Url::to(['user/related_apply?type=1'])], 'icon-house_blue') : null,
	    Yii::$app->user->can('user/invest_apply') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Join application'), ['tab'=>\yii\helpers\Url::to(['user/related_apply?type=2'])], 'icon-house_blue') : null,
	    Yii::$app->user->can('user/sign_up') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Sign up customer'), ['tab'=>\yii\helpers\Url::to(['user/related_apply?type=3'])], 'icon-house_blue') : null,
	    Yii::$app->user->can('user/coach_invitation') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Coach invitation customer'), ['tab'=>\yii\helpers\Url::to(['user/related_apply?type=5'])], 'icon-house_blue') : null,
	    /*Yii::$app->user->can('user/sign_up') ?*/ CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'instalment customer'), ['tab'=>\yii\helpers\Url::to(['user/related_apply?type=4'])], 'icon-application_home') /*: null*/,
	    // Yii::$app->user->can('user/sign_up') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', '导出未处理信息', ['operation' => Yii::t('locale', 'Management')]), ['tab'=>\yii\helpers\Url::to(['user/print'])], 'icon-money_yen') : null,
	];
}

if($type == 1){
	$urlsArray = [
		'url' => \yii\helpers\Url::to(['user/buy_car']),
	];
	echo CMyHtml::datagrid('   ', // $title
		new \common\models\Pro_buy_car(),    // $model
		['id', 'name', 'mobile', 'sex', 'car_models', 'buy_city', 'add_time','status'],            // $columns
		[],            // $dataArray
		'100%', '100%',     // $width, $height
		[],            // $htmlsOptions,
		$urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
		0, 0                // $frozenColumnIndex, $frozenRowIndex
	);

}else if($type == 2){
	$urlsArray = [
		'url' => \yii\helpers\Url::to(['user/joinapplying_list']),
	];
	echo CMyHtml::datagrid('   ', // $title
		new \common\models\Pro_join_applying(),    // $model
		['id', 'name', 'phone', 'address', 'mail', 'message', 'user_id', 'status', 'edit_user_id', 'created_at', 'operation'],            // $columns
		[],            // $dataArray
		'100%', '100%',     // $width, $height
		[],            // $htmlsOptions,
		$urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
		0, 0                // $frozenColumnIndex, $frozenRowIndex
	);

}else if ($type == 3) {
	$urlsArray  = [
		'url'   => \yii\helpers\Url::to(['user/sign_up']),
	];
	echo CMyHtml::datagrid(' ',
		new \common\models\Pro_sign_up(),
		['id','name','phone','status','city','sex','way','source','remark','created_at','operation'],
		[],
		'100%','100%',
		[],
		$urlsArray,$toolbarArray,
		0,0
	);
}else if ($type == 4) {
	$urlsArray  = [
		'url'   => \yii\helpers\Url::to(['user/instalment']),
	];
	echo CMyHtml::datagrid(' ',
		new \common\models\Pro_instalment(),
		['id','name','phone','status','product','numbers','type','remark','created_at','operation'],
		[],
		'100%','100%',
		[],
		$urlsArray,$toolbarArray,
		0,0
	);
}else if($type == 5){
	$urlsArray = ['url'=>\yii\helpers\Url::to(['user/coach_invitation'])];
	echo CMyHtml::datagrid(
		' ',
		new \common\models\Pro_invitation(),
		['id','name','phone','status','code','school_id','sex','created_at','operation'],
		[],
		'100%','100%',
		[],
		$urlsArray,$toolbarArray,
		0,0
	);
}





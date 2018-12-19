<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pub_user_sms();

$dgTitle = ($type == \common\models\Pub_user_sms::TYPE_SENT ? Yii::t('carrental', 'Customer sms records') : Yii::t('carrental', 'Recieved customer sms records'));

$urlInfo = ['user/sms_list', 'type'=>$type];

if (isset($status)) {
    $urlInfo['status'] = $status;
}
else {
    $status = 0; 
}

$columnFields = ['id', 'time', 'customer_name', 'customer_phone', 'content'];

$objModel = new common\models\Pub_user_sms();

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
    'saveUrl' => \yii\helpers\Url::to([$type == \common\models\Pub_user_sms::TYPE_SENT ? 'user/sms_add' : 'user/smsrecv_add']),
    'updateUrl' => \yii\helpers\Url::to([$type == \common\models\Pub_user_sms::TYPE_SENT ? 'user/sms_edit' : 'user/smsrecv_edit']),
    'deleteUrl' => \yii\helpers\Url::to([$type == \common\models\Pub_user_sms::TYPE_SENT ? 'user/sms_delete' : 'user/smsrecv_delete']),
];

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', []),
    //CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', []),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', []),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_ACCEPT, '', ''),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REJECT, '', ''),
];

$funcId = CMyHtml::genID();
$dgId = CMyHtml::getIDPrefix().'dg_'.CMyHtml::genID();

$htmlArray = [];

$htmlArray[] = CMyHtml::datagrid($dgTitle, // $title
    $objModel,    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>$dgId, 'data-options' => ['onLoadSuccess'=>"funOnLoadDataSuccess{$funcId}"]],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$defaultTimeStr = date('Y-m-d H:i:s');
$defaultType = $type;
$defaultStatus = \common\models\Pub_user_sms::STATUS_NORMAL;

$searchUserUrl = \yii\helpers\Url::to(['user/searchuserslike', 'name'=>'']);

$arrScripts = [];
$arrScripts[] = <<<EOD
function funOnLoadDataSuccess{$funcId}() {
    var curDate = new Date();
    var curTimeStr = $.custom.utils.humanTime(Math.ceil(curDate.getTime() / 1000));
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {type:{$defaultType},status:{$defaultStatus},time:curTimeStr});
}
function funOnDgRowCustomerNameChanged{$funcId}(newValue, oldValue) {
    var obj = $(this);
    obj.combobox('reload', '{$searchUserUrl}'+encodeURI(newValue));
}
function funcDgRowCustomerNameSelected{$funcId}(record) {
    var dgObj = $('#{$dgId}');
    var opts = dgObj.datagrid('options');
    var ed = dgObj.datagrid('getEditor', {index:opts.curEditingIndex,field:'customer_phone'});
    if (ed) {
        $(ed.target).textbox('setValue', record.telephone);
    }
    var curRow = dgObj.datagrid('getSelected');
    //easyuiFuncDebugThisValue(curRow, opts, record);
    if (curRow) {
        curRow.customer_id = record.user_id;
    }
}

setTimeout(function() {
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'queryParams', {{$yiiCsrfKey}:'{$yiiCsrfToken}'});
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'modelName', '{$objForm->formName()}');
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {type:{$defaultType},status:{$defaultStatus},time:'{$defaultTimeStr}'});
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'editorEvents', {customer_name:{onChange:funOnDgRowCustomerNameChanged{$funcId},onSelect:funcDgRowCustomerNameSelected{$funcId}}});
}, 50);
EOD;

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);

<?php

use common\helpers\CMyHtml;

$eleIdPrefix = CMyHtml::getIDPrefix();
$autoId = CMyHtml::genID();

$urlsArray = [
    'url' => \yii\helpers\Url::to(['user/user_list']),
    'deleteUrl' => \yii\helpers\Url::to(['user/delete']),
    //'detailUrl' => \yii\helpers\Url::to(['user/getuserdetailview']),
];

$toolbarArray = [
    Yii::$app->user->can('user/add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['tab'=>\yii\helpers\Url::to(['user/add'])]) : null,
    Yii::$app->user->can('user/edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['tab'=>\yii\helpers\Url::to(['user/edit']), 'needSelect' => true]) : null,
    Yii::$app->user->can('user/delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,
    
    // blacklist_index
    Yii::$app->user->can('user/blacklist_index') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Inner black names'), ['tab'=>\yii\helpers\Url::to(['user/blacklist_index'])], 'icon-user_gray') : null,
    Yii::$app->user->can('user/membercard_index') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', 'Member card management'), ['tab'=>\yii\helpers\Url::to(['user/membercard_index'])], 'icon-creditcards') : null,
    Yii::$app->user->can('user/feedback_index') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'User feedbacks'), ['tab'=>\yii\helpers\Url::to(['user/feedback_index'])], 'icon-feed') : null,
    Yii::$app->user->can('user/longrentapplying_index') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Long rent application'), ['tab'=>\yii\helpers\Url::to(['user/longrentapplying_index'])], 'icon-coolite') : null,
    Yii::$app->user->can('user/account_index') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('carrental', 'Signup (not authenticated real name) accounts'), ['tab'=>\yii\helpers\Url::to(['user/account_index', 'hide_real_name'=>1])], 'icon-people') : null,
    
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'name', Yii::t('locale', 'Customer name'), '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'telephone', Yii::t('locale', 'Mobilephone'), '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'office_id', Yii::t('locale', 'Office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>"width:160px"]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_DATEBOX, 'created_at', '开始时间', '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_DATEBOX, 'e_time', '结束时间', '', ['searchOnChange'=>true]),
];

if (\Yii::$app->user->can('user/user-export')) {
    $exportUrl = \yii\helpers\Url::to(['user/user-export', '_'=>time()]);
    $tabName = '客户信息列表';
    $toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_BUTTON, \Yii::t('locale', 'Export'), '', '', [ 
            'onclick'=>"funcOpenExportLink{$autoId}('{$tabName}', '{$exportUrl}')"]);
}

echo CMyHtml::datagrid('   ', // $title
    new common\models\Pub_user_info(),    // $model
    ['id', 'name', 'telephone', 'member_id', 'vip_level', 'credit_level', 'member_card_amount', 'total_consumption', 'cur_integration', 'violation_records', 'accident_records', 'unfreeze_at', 'created_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>"{$eleIdPrefix}user_list{$autoId}"],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
?>
<script type="text/javascript">
function funcOpenExportLink<?= $autoId ?>(tabName, url) {
    var params = $('#<?= "{$eleIdPrefix}user_list{$autoId}" ?>').datagrid('options').queryParams;
    var form = $('<form>');
    form.attr('style', 'display:none');
    form.attr('target', '');
    form.attr('method', 'get');
    form.attr('action', url);
    for (var i in params) {
        var input = $('<input>');
        input.attr('type', 'hidden');
        input.attr('name', i);
        input.attr('value', params[i]);
        form.append(input);
    }
    $('body').append(form);
    form.submit();
    setTimeout(function(){ form.remove(); }, 500);
}

</script>

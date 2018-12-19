<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_vehicle_order_relet();
if (!isset($action)) {
    $action = 'update';
}
$formTitle = '';

$objData = isset($objOrder) ? $objOrder : null;
if (!$objData) {
    $action = 'insert';
}

$orderId = ($objData ? $objData->id : 0);
$orderOrigionEndTime = ($objData ? $objData->new_end_time : 0);

$rootUrl = \common\helpers\Utils::getRootUrl();
$urlInfo = ['order/order_relet_list', 'order_id'=>$orderId];

$idPrefix = CMyHtml::getIDPrefix();
$autoId = CMyHtml::genID();
$dgId = "{$idPrefix}dg_{$autoId}";
$updateEndTimeUrl = \yii\helpers\Url::to(['order/get_order_endtime', 'id'=>$orderId]);
$getOrderPriceUrl = \yii\helpers\Url::to(['order/get_order_price', 'type'=>'order_relet', 'order_id'=>$orderId]);
$urlPaymentinput = \yii\helpers\Url::to(['order/paymentinput', 'is_relet'=>1, 'order_id'=>$orderId]);
$paymentModalId = "{$idPrefix}paymentinput{$autoId}";

if (isset($status)) {
    $urlInfo['status'] = $status;
}
else {
    $status = 0; 
}

$columnFields = ['id', 'serial', 'origion_end_time', 'new_end_time', 
    'total_amount', 'paid_amount', 'pay_source', 'remark', 'edit_user_id', 'created_at', 'updated_at','operation'];

$objReletModel = new \common\models\Pro_vehicle_order_relet();
$objModel = new \common\models\Pro_vehicle_order();

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
    'saveUrl' => \yii\helpers\Url::to(['order/order_relet_add']),
    'updateUrl' => \yii\helpers\Url::to(['order/order_relet_edit']),
    'deleteUrl' => \yii\helpers\Url::to(['order/order_relet_delete']),
];

$headerHeight = 40;
$totalHeight = 444;
$width = 870;

$arrData = [
    [
        [\Yii::t('locale', 'Main order'), ($objData ? $objData->serial : 'Unknown')],
        [$objModel->getAttributeLabel('customer_name'), ($objData ? $objData->customer_name : 'Unknown')],
    ],
];

$canAdd = \Yii::$app->user->can('order/order_relet_add');
$canEdit = \Yii::$app->user->can('order/order_relet_edit');
$toolbarArray = [
    $canAdd ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', []) : null,
    $canEdit ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', []) : null,
    \Yii::$app->user->can('order/order_relet_delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', []) : null,
    ($canAdd || $canEdit) ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_ACCEPT, '', '') : null,
    ($canAdd || $canEdit) ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REJECT, '', '') : null,
    
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', 'View'), ['dialog'=>\yii\helpers\Url::to(['order/order_relet_view', 'id'=>'']), 'needSelect'=>true], 'icon-view'),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_CUSTOM, Yii::t('locale', 'Payment'), ['dialog'=>$urlPaymentinput, 'needSelect'=>false, 'bootstrapmodal'=>$paymentModalId], 'icon-money'),
];

$containerPart = CMyHtml::datagrid(Yii::t('locale', 'Orders releted'), // $title
    $objReletModel,    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>$dgId, 'dialogWidth'=>"800px", 'dialogHeight'=>"420px", 'data-options' => [
            'onLoadSuccess'=>"funOnLoadDataSuccess{$autoId}",
            'onRowContextMenu'=>"onGridRowContextMenu{$autoId}",
        ]
    ],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$htmlArray = [];

//$htmlArray[] = \yii\helpers\Html::jsFile(yii\helpers\Url::to(['@web/js/orderhelper.js']));
$htmlArray[] = \yii\helpers\Html::jsFile("{$rootUrl}app/carrental/backend/web/js/orderhelper.js");
$htmlArray[] = CMyHtml::beginPanel('', ['fit'=>'true', 
    //'footer'=>"'#toolbar{$autoId}'"
    ]);
$htmlArray[] = CMyHtml::beginMainPageLayoutRegion("100%", "{$headerHeight}", '', 'north');
$htmlArray[] = \common\helpers\CMyHtml::beginPanel('', ['height'=>"{$headerHeight}"]);
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
$htmlArray[] = CMyHtml::beginMainPageLayoutRegion("100%", ($totalHeight-$headerHeight).'px', '', 'center');
$htmlArray[] = $containerPart;
$htmlArray[] = CMyHtml::endMainPageLayoutRegion();

$htmlArray[] = CMyHtml::endPanel();

//$htmlArray[] = CMyHtml::beginTag('div', ['id'=>"toolbar{$autoId}", 'style'=>"text-align:center;"]);
//$htmlArray[] = CMyHtml::tag('a', 'xxx', ['href'=>'javascript:void(0);', 'class'=>'easyui-tooltip icon-info', 'id'=>"last_relet_price_info_btn{$autoId}", 'title'=>"xxx", 'style'=>"width:16px;height:16px;float:right;zIndex:999;", 'data-options'=>"iconCls:'icon-info'"]);
//$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = \common\helpers\BootstrapHtml::dialog(['id'=>$paymentModalId]);

$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$orderOrigionEndTimeStr = date('Y-m-d H:i:s', $orderOrigionEndTime);

$minReleteTime = 3600*6;

$arrScripts = [];
$arrScripts[] = <<<EOD
function funOnLoadDataSuccess{$autoId}() {
    easyuiFuncAjaxSendDataWithoutAlert('{$updateEndTimeUrl}', 'get', undefined, function(data){
        var obj = eval('(' + data + ')');
        if (obj.code == 0 && obj.value) {
            easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {origion_end_time:obj.value});
        }
        else if (obj.msg) {
            $.custom.easyui.alert.show(obj.msg, $.custom.utils.lan.defaults.titleWarning, '', 'warning');
        }
    });
}

function funOnDgRowNewEndTimeChanged{$autoId}(newValue,oldValue) {
    var dgObj = $('#{$dgId}');
    var opts = dgObj.datagrid('options');
    var newEndTime = $.custom.utils.toTimestamp(newValue);
    var origionEndTime = $.custom.utils.toTimestamp(opts.customData.defaultValues.origion_end_time);
    if (newEndTime < origionEndTime + {$minReleteTime}) {
        var ed = dgObj.datagrid('getEditor', {index:opts.curEditingIndex,field:'new_end_time'});
        if (ed) {
            $(ed.target).datetimebox('setValue', $.custom.utils.humanTime(origionEndTime + 86400));
            return;
        }
    }

    var url = '{$getOrderPriceUrl}' + '&start_time='+origionEndTime+'&end_time='+newEndTime;
    easyuiFuncAjaxSendDataWithoutAlert(url, 'get', undefined, function(data){
        console.log(data);
        var obj = eval('(' + data + ')');
        if (obj.code == 0 && obj.value) {
            if (opts.customData == undefined || opts.customData == {}) {
                opts.customData = {};
            }
            opts.customData.lastPriceDetails = obj;
    
            var ed = dgObj.datagrid('getEditor', {index:opts.curEditingIndex,field:'total_amount'});
            if (ed) {
                $(ed.target).numberbox('setValue', obj.value);
                //$('ed.target').tooltip({content:formatOrderDetailedDailyPriceTips(obj)});
                //$('last_relet_price_info_btn{$autoId}').tooltip({content:formatOrderDetailedDailyPriceTips(obj)});
                return;
            }
        }
        else if (obj.msg) {
            $.custom.easyui.alert.show(obj.msg, $.custom.utils.lan.defaults.titleWarning, '', 'warning');
        }
    });
}

function onGridRowContextMenu{$autoId}(e, index, row) {
    e.preventDefault();
    var grid = $(this);/* grid本身 */
    var opts = grid.datagrid('options');
    if (opts.customData == undefined) {
        return;
    }
    if (opts.customData.lastPriceDetails == undefined) {
        return;
    }
    var html = formatOrderDetailedDailyPriceTips(opts.customData.lastPriceDetails, '新增续租订单详情');
    var rowContextMenu = opts.customData.rowContextMenu;
    if (!rowContextMenu) {
        var tmenu = $('<div style="width:200px;"></div>').appendTo('body');
        rowContextMenu = opts.customData.rowContextMenu = tmenu.menu({
        });
    }
    
    rowContextMenu.html(html);

    rowContextMenu.menu('show', {
        left : e.pageX,
        top : e.pageY
    });
}

setTimeout(function() {
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'queryParams', {order_id:{$orderId}, {$yiiCsrfKey}:'{$yiiCsrfToken}'});
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'modelName', '{$objForm->formName()}');
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {order_id:{$orderId}, origion_end_time:'{$orderOrigionEndTimeStr}'});
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'editorEvents', {new_end_time:{onChange:funOnDgRowNewEndTimeChanged{$autoId}}});
}, 50);
EOD;

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);
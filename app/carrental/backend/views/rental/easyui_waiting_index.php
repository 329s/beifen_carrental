<?php

use common\helpers\CMyHtml;

$urlInfo = ['vehicle/waiting_vehicle_list'];

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_model_id', Yii::t('locale', 'Vehicle model'), \common\components\VehicleModule::getVehicleModelNamesArray(), ['searchOnChange'=>true, 'style'=>"width:150px"]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'plate_number', Yii::t('locale', 'Plate number'), '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_DATETIMEBOX, ['start_time', 'end_time'], Yii::t('locale', 'Time'), '', ['searchOnChange'=>true]),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'stop_office_id', Yii::t('locale', 'Office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>"width:150px"]),
];

$urlLoadList = \yii\helpers\Url::to($urlInfo);

$urlsArray = [
    'url' => $urlLoadList,
];

$objModel = new \common\models\Pro_vehicle();
$objModel->setShowVehicleModelDetail(true);

$htmlArray = [];

$idPrefix = CMyHtml::getIDPrefix();
$autoId = CMyHtml::genID();
$contentId = "{$idPrefix}content{$autoId}";
$paginationId = "{$idPrefix}pagination{$autoId}";

$urlRoot = \common\helpers\Utils::getRootUrl();
$htmlArray[] = \yii\helpers\Html::cssFile("{$urlRoot}assets/netease/framework/css/reset.css");
$htmlArray[] = \yii\helpers\Html::cssFile("{$urlRoot}assets/netease/framework/css/function.css");
$cssImageBorderArray = [
    "/* 有边图片容器-默认定宽定高、图片拉伸 */",
    ".u-img2{display:block;position:relative;width:104px;height:104px;padding:2px;border:1px solid #ddd;}",
    ".u-img2 img{display:block;width:100%;height:100%;}",
    "/* 图片高适应 */",
    ".u-img2-ha,.u-img2-ha img{height:auto;}"
];
$cssImageNonBorderArray = [
    "/* 无边图片容器-默认定宽定高、图片拉伸 */",
    ".u-img{display:block;position:relative;width:110px;height:110px;}",
    ".u-img img{display:block;width:100%;height:100%;}",
    "/* 图片高适应 */",
    ".u-img-ha,.u-img-ha img{height:auto;}"
];
$cssStylesArray = [
    ".m-list4{padding:1px 0 0;zoom:1;}",
    ".m-list4 ul{margin:0 0 0 0; width:100%; padding:0px 0px 0px 0px;}",
    ".m-list4 li{display:inline;float:left;width:14.2%;padding:6px 6px 6px 6px; margin:0 0 0 0px;}",
    ".m-list4 li:hover{background-color:#87CEEB;}",
    ".m-list4 h3,.m-list4 p {width:100%;height:18px;overflow:hidden;margin:5px 0 0;line-height:18px; text-align:center;}",
    ".m-list4 h3 a,.m-list4 h3 a:hover{color:#33e;}",
    ".m-list4 .u-img,.m-list4 .u-img2{width:auto;height:90px;text-align:center;}",
    ".m-list4 h3 a{font-size:20px}",
    ".m-list4 .u-img img,.m-list4 .u-img2 img{width:auto;height:84px;}",
];
$htmlArray[] = \yii\helpers\Html::style(implode("\n", $cssImageBorderArray));
$htmlArray[] = \yii\helpers\Html::style(implode("\n", $cssImageNonBorderArray));
$htmlArray[] = \yii\helpers\Html::style(implode("\n", $cssStylesArray));

$cssLoopScrollStylesArray = [
    ".m-loop-scroll { width:100%; height:24px; overflow:hidden;}",
    ".m-loop-scroll ul{list-style:none;margin:0px;}",
    ".m-loop-scroll ul li{margin:0 5px; float:left; display:block; height:24px; border:#CCCCCC 1px solid; overflow:hidden;}",
];
$htmlArray[] = \yii\helpers\Html::style(implode("\n", $cssLoopScrollStylesArray));

$htmlArray[] = CMyHtml::beginLayout(['fit'=>'true']);

$htmlArray[] = CMyHtml::beginLayoutRegion('100%', '38px', '', 'north', []);
$htmlArray[] = CMyHtml::beginPanel('', ['style'=>'text-align:right']);
// 时间
$htmlArray[] = \yii\bootstrap\Html::label(Yii::t('locale', 'Time').':').\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_DATETIMEBOX, 'start_time', '', [], [
    'data-options' => <<<EOD
editable:false,
onChange: function(newValue,oldValue) {
    funcSetPaginationQueryField('start_time', newValue);
    funcLoadVehicleList{$autoId}();
}
EOD
], '').'—'.\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_DATETIMEBOX, 'end_time', '', [], [
    'data-options' => <<<EOD
editable:false,
onChange: function(newValue,oldValue) {
    funcSetPaginationQueryField('end_time', newValue);
    funcLoadVehicleList{$autoId}();
}
EOD
], '');

// 车型 
$htmlArray[] = \yii\bootstrap\Html::label(Yii::t('locale', 'Vehicle model').':').\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_COMBOBOX, 'vehicle_model_id', '', \common\components\VehicleModule::getVehicleModelNamesArray(['enableNone'=>true]), [
    'data-options' => <<<EOD
editable:false,
onChange: function(newValue,oldValue) {
    funcSetPaginationQueryField('vehicle_model_id', newValue);
    funcLoadVehicleList{$autoId}();
}
EOD
], Yii::t('locale', 'Vehicle model'));
// 车牌号 
$htmlArray[] = \yii\bootstrap\Html::label(Yii::t('locale', 'Plate number').':').\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_TEXTBOX, 'plate_number', '', [], [
    'data-options' => <<<EOD
onChange: function(newValue,oldValue) {
    funcSetPaginationQueryField('plate_number', newValue);
    funcLoadVehicleList{$autoId}();
}
EOD
], Yii::t('locale', 'Plate number'));
// 停靠门店 
$htmlArray[] = \yii\bootstrap\Html::label(Yii::t('locale', 'Office').':').\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_COMBOTREE, 'stop_office_id', '', \common\components\OfficeModule::getOfficeComboTreeData(), [
    'data-options' => <<<EOD
editable:false,
onChange: function(newValue,oldValue) {
    funcSetPaginationQueryField('stop_office_id', newValue);
    funcLoadVehicleList{$autoId}();
}
EOD
], Yii::t('locale', 'Office'));
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endLayoutRegion();

//$htmlArray[] = CMyHtml::beginLayoutRegion('100%', '37px', '', 'south', []);
//$htmlArray[] = CMyHtml::endLayoutRegion();

$htmlArray[] = CMyHtml::beginLayoutRegion('100%', '', '', 'center', []);
$htmlArray[] = CMyHtml::beginPanel('', ['id'=>$contentId, 'fit'=>'true', 'footer'=>$paginationId]);
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::tag('div', '', 
    ['class'=>'easyui-pagination', 'encode'=>'false', 'id'=>$paginationId,
        'style'=>"border:1px solid #ccc;width:100%;height:32px;margin:0px 0px 0px 0px;padding:0px 0px 0px 0px;",
        'data-options'=>"total:0, pageSize:28, pageList: [28], onSelectPage:funcLoadVehicleList{$autoId}"
    ]);
$htmlArray[] = CMyHtml::endLayoutRegion();

$htmlArray[] = CMyHtml::endLayout();

echo implode("\n", $htmlArray);

?>
<script type="text/javascript">
function funcSetPaginationQueryField(fieldName, fieldValue) {
    var objPagination = $('#<?= $paginationId ?>');
    var opts = objPagination.pagination('options');
    if (opts.queryParams == undefined) {
        opts.queryParams = {};
    }
    opts.queryParams[fieldName] = fieldValue;
}

function funcGenerateVehicleInfoCell(row) {
    var curTim = new Date().getTime() / 1000;
    var upkeepMileage = parseInt(row.next_upkeep_mileage) - parseInt(row.last_upkeep_mileage);
    var nextUpkeepTime = $.custom.utils.toTimestamp(row.next_upkeep_time);
    var upkeepTime = nextUpkeepTime - $.custom.utils.toTimestamp(row.last_upkeep_time);
    var renewalTimeTCI = $.custom.utils.toTimestamp(row.tci_renewal_time);
    var renewalTimeVCI = $.custom.utils.toTimestamp(row.vci_renewal_time);
    var annualInspectionTime = $.custom.utils.toTimestamp(row.annual_inspection_time);
    var leftMileage = parseInt(row.next_upkeep_mileage) - parseInt(row.cur_kilometers);
    var arrStatusInfo = new Array();
    if (row.status == <?= \common\models\Pro_vehicle::STATUS_BOOKED ?>) {
        arrStatusInfo.push('<font size="12" color="green">'+'<?= Yii::t('carrental', 'Booked: ') ?> '+$.custom.utils.formatTime('yyyy-MM-dd hh:mm', $.custom.utils.toTimestamp(row.rent_start_time))+'—'+$.custom.utils.formatTime('yyyy-MM-dd hh:mm', $.custom.utils.toTimestamp(row.rent_end_time))+'</font>');
    }
    else if (row.status == <?= \common\models\Pro_vehicle::STATUS_RENTED ?>) {
        arrStatusInfo.push('<font size="12" color="green">'+'<?= Yii::t('carrental', 'Rented: ') ?> '+$.custom.utils.formatTime('yyyy-MM-dd hh:mm', $.custom.utils.toTimestamp(row.rent_start_time))+'—'+$.custom.utils.formatTime('yyyy-MM-dd hh:mm', $.custom.utils.toTimestamp(row.rent_end_time))+'</font>');
    }
    if (upkeepMileage > 0 && leftMileage < 1000) {
        var txt = (leftMileage >= 0 ? $.custom.lan.defaults.vehicle.left : $.custom.lan.defaults.vehicle.overflow) + Math.abs(leftMileage) + $.custom.lan.defaults.vehicle.kilometer;
        var color = leftMileage >= 0 ? '#00FF00' : '#FF0000';
        arrStatusInfo.push('<font size="12" color="'+color+'">'+'<?= Yii::t('carrental', 'Upkeep: ') ?>'+txt+'</font>');
    }
    if (nextUpkeepTime > 0 && nextUpkeepTime < curTim + 30*86400 && upkeepTime > 1000) {
        var leftDays = Math.floor(($.custom.utils.toTimestamp(row.next_upkeep_time) - curTim) / 86400);
        var txt = (leftDays >= 0 ? $.custom.lan.defaults.vehicle.left : $.custom.lan.defaults.vehicle.overflow) + Math.abs(leftDays) + $.custom.lan.defaults.vehicle.days;
        var color = leftDays >= 0 ? '#00FF00' : '#FF0000';
        arrStatusInfo.push('<font size="12" color="'+color+'">'+'<?= Yii::t('carrental', 'Upkeep: ') ?> '+txt+'</font>');
    }
    if (renewalTimeTCI > 0 && renewalTimeTCI < curTim + 30*86400) {
        var leftDays = Math.floor((renewalTimeTCI - curTim) / 86400);
        var txt = (leftDays >= 0 ? $.custom.lan.defaults.vehicle.left : $.custom.lan.defaults.vehicle.overflow) + Math.abs(leftDays) + $.custom.lan.defaults.vehicle.days;
        var color = leftDays >= 0 ? '#00FF00' : '#FF0000';
        arrStatusInfo.push('<font size="12" color="'+color+'">'+'<?= Yii::t('carrental', 'Renewal: ') ?> '+txt+'</font>');
    }
    else if (renewalTimeVCI > 0 && renewalTimeVCI < curTim + 30*86400) {
        var leftDays = Math.floor((renewalTimeVCI - curTim) / 86400);
        var txt = (leftDays >= 0 ? $.custom.lan.defaults.vehicle.left : $.custom.lan.defaults.vehicle.overflow) + Math.abs(leftDays) + $.custom.lan.defaults.vehicle.days;
        var color = leftDays >= 0 ? '#00FF00' : '#FF0000';
        arrStatusInfo.push('<font size="12" color="'+color+'">'+'<?= Yii::t('carrental', 'Renewal: ') ?> '+txt+'</font>');
    }
    if (annualInspectionTime > 0 && annualInspectionTime < curTim + 30*86400) {
        var leftDays = Math.floor((annualInspectionTime - curTim) / 86400);
        var txt = (leftDays >= 0 ? $.custom.lan.defaults.vehicle.left : $.custom.lan.defaults.vehicle.overflow) + Math.abs(leftDays) + $.custom.lan.defaults.vehicle.days;
        var color = leftDays >= 0 ? '#00FF00' : '#FF0000';
        arrStatusInfo.push('<font size="12" color="'+color+'">'+'<?= Yii::t('carrental', 'Annual: ') ?> '+txt+'</font>');
    }
    if (row.stop_office_id != row.belong_office_id) {
        arrStatusInfo.push('<font size="12" color="red">'+'<?= Yii::t('carrental', 'Stop: ') ?> '+row.stop_office_id+'</font>');
    }
    
    var arrCellHtmls = new Array();
    arrCellHtmls.push('<div style="border:1px solid #87CEEB;">');
    if (arrStatusInfo.length > 0) {
        arrCellHtmls.push('<marquee style="width:100%;height:24px;font-weight:bold;padding:6px" scrollamount="2" direction="left" behaviour="scroll">');
        //arrCellHtmls.push('<p style="font-weight:bold;width:auto;z-index:1000;">'+arrStatusInfo.join('<span>&nbsp;&nbsp;</span>')+'</p>');
        arrCellHtmls.push(arrStatusInfo.join('<span>&nbsp;&nbsp;</span>'));
        arrCellHtmls.push('</marquee>');
        //arrCellHtmls.push('<div></div>');
    }
    else {
        arrCellHtmls.push('<p class="f-toe" style="font-weight:bold"><font size="12" color="green"><?= Yii::t('carrental', 'Vehicle status is normal') ?></font></p>');
    }
    arrCellHtmls.push('<p class="f-toe"><font color="gray">'+row.model_id+'</font></p>');
    arrCellHtmls.push('<div class="u-img2"><span style="text-align:center;"><img src="'+row.vehicle_image+'" alt="'+row.model_id+'" style="clear:both; display:block; margin:auto;" /></span></div>');
    arrCellHtmls.push('<h3><a href="javascript:void(0)">'+row.plate_number+'</a></h3>');
    arrCellHtmls.push('<p class="f-toe"><font color="orange" style="font-weight:bold">'+row.belong_office_id+'</font></p>');
    arrCellHtmls.push('</div>');
    return arrCellHtmls.join("\n");
}

function funcLoadVehicleList<?= $autoId ?>(pageNumber, pageSize) {
    var params = {};
    var objPagination = $('#<?= $paginationId ?>');
    var opts = objPagination.pagination('options');
    if (opts.queryParams != undefined) {
        for (var k in opts.queryParams) {
            var v = opts.queryParams[k];
            if (v) {
                params[k] = v;
            }
        }
    }
    if (pageNumber !== undefined) {
        params.page = pageNumber;
    }
    else {
        params.page = opts.pageNumber;
    }
    if (pageSize !== undefined) {
        params.rows = pageSize;
    }
    else {
        params.rows = opts.pageSize;
    }
    $.ajax({
        type:'get',
        url:'<?php echo $urlLoadList; ?>',
        data:params,
        beforeSend:easyuiFuncAjaxLoading,
        success: function (data) {
            easyuiFuncAjaxEndLoading();
            funcOnVehicleListLoaded<?= $autoId ?>(data);
        },
        error: function (e) {
            easyuiFuncAjaxEndLoading();
            easyuiFuncOnProcessErrorEvents(e);
        }
    });
}

function funcOnVehicleListLoaded<?= $autoId ?>(data) {
    if (data == '') return;
    var obj = undefined;
    if ($.type(data) == 'string') {
        try {
            obj = eval('(' + data + ')');
        }
        catch (e) {
            obj = undefined;
        }
    }
    
    var objPanel = $('#<?= $contentId ?>');
    var objPagination = $('#<?= $paginationId ?>');
    
    if (objPagination && objPagination.length == 1) {
        objPagination.pagination('refresh', {total: obj.total});
    }
    
    if (!objPanel || objPanel.length == 0) {
        return;
    }
    
    objPanel.panel('clear');
    
    //easyuiFuncDebugThisValue(objPanel, obj);
    var arrHtmls = new Array();
    var curCol = 0;
    var colSize = 7;
    for (var i in obj.rows) {
        var row = obj.rows[i];
        if (curCol % colSize == 0) {
            arrHtmls.push('<div class="m-list4">');
            arrHtmls.push('<ul class="f-cb">');
        }
        arrHtmls.push('<li onclick="easyuiFuncNavTabAddDoNotKnownId(\'<?= Yii::t('carrental', 'Book vehicle') ?>\', \'<?= \yii\helpers\Url::to(['rental/waiting_book', 'vehicle_id'=>'']) ?>'+row.id+'\', undefined, false);">');
        var htmlText = funcGenerateVehicleInfoCell(row);
        arrHtmls.push(htmlText);
        arrHtmls.push('</li>');
        
        curCol += 1;
        if (curCol % colSize == 0) {
            arrHtmls.push('</ul>');
            arrHtmls.push('</div>');
        }
    }
    
    var oBody = objPanel.panel('body');
    oBody.html(arrHtmls.join("\n"));
}

setTimeout(function() {
    funcLoadVehicleList<?= $autoId ?>(1, 28);
}, 100);

</script>

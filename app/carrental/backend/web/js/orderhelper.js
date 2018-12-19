
function formatOrderDetailedDailyPriceTips(param, title, skipDrawPanel)
{
    var arrHtmls = new Array();
    if (skipDrawPanel) {
        skipDrawPanel = true;
    }
    if (!skipDrawPanel) {
        arrHtmls.push('<div class="panel panel-info">');
    }
    if (title !== undefined) {
        arrHtmls.push('<div class="panel-heading">');
        arrHtmls.push('<h3 class="panel-title">'+title+'</h3>');
        arrHtmls.push('</div>');
    }
    arrHtmls.push('<div class="panel-body" style="max-height:500px;overflow:auto;overflow-x:hidden">');
    var startTime = 0;
    var endTime = 0;
    if (param.start_time !== undefined) {
        startTime = param.start_time;
    }
    if (param.end_time !== undefined) {
        endTime = param.end_time;
    }
    arrHtmls.push('<table class="table table-striped table-condsensed" style="margin:0px">');
    arrHtmls.push('<tbody>');
    for (var i in param.details) {
        var t2 = startTime + 86400;
        if (endTime && t2 > endTime) {
            t2 = endTime;
        }
        var d1 = new Date(startTime*1000);
        var d2 = new Date(t2*1000);
        var timeTxt = '<span class="label label-default">'+(d1.getMonth()+1)+'/'+d1.getDate()+' '+(d1.getHours()>=10 ? d1.getHours() : '0'+d1.getHours())+':'+(d1.getMinutes()>=10 ? d1.getMinutes() : '0'+d1.getMinutes()) +
                '</span>~<span class="label label-default">'+
                (d2.getMonth()+1)+'/'+d2.getDate()+' '+(d2.getHours()>=10 ? d2.getHours() : '0'+d2.getHours())+':'+(d2.getMinutes()>=10 ? d2.getMinutes() : '0'+d2.getMinutes()) +
                '</span>';
        
        arrHtmls.push('<tr style="white-space:nowrap;">');
        arrHtmls.push('<td>'+timeTxt+'</td><td>￥'+param.details[i]+'</td>');
        arrHtmls.push('</tr>');
        
        startTime = t2;
    }
    if (param.optional_service !== undefined && param.optional_service != 0) {
        arrHtmls.push('<tr>');
        arrHtmls.push('<td>增值服务费</td><td>￥'+param.optional_service+'</td>');
        arrHtmls.push('</tr>');
    }
    if (param.now_overtime_price !== undefined && param.now_overtime_price != 0) {
        arrHtmls.push('<tr>');
        arrHtmls.push('<td>超时费</td><td>￥'+param.now_overtime_price+'</td>');
        arrHtmls.push('</tr>');
    }
    /*if (param.origin_overtime_price !== undefined && param.origin_overtime_price != 0) {
        arrHtmls.push('<tr>');
        arrHtmls.push('<td>原超时费</td><td>￥'+param.origin_overtime_price+'</td>');
        arrHtmls.push('</tr>');
    }*/
    if (param.other_price !== undefined && param.other_price != 0) {
        arrHtmls.push('<tr>');
        arrHtmls.push('<td>其它费用</td><td>￥'+param.other_price+'</td>');
        arrHtmls.push('</tr>');
    }
    if (param.free_price !== undefined && param.free_price != 0 && param.free_price != null) {
        arrHtmls.push('<tr>');
        arrHtmls.push('<td>优惠金额</td><td>￥-'+param.free_price+'</td>');
        arrHtmls.push('</tr>');
    }
    //明细添加
    if (param.price_poundage !== undefined && param.price_poundage != 0 && param.price_poundage != null) {
        arrHtmls.push('<tr>');
        arrHtmls.push('<td>手续费</td><td>￥'+param.price_poundage+'</td>');
        arrHtmls.push('</tr>');
    }

    if (param.unit_price_basic_insurance !== undefined && param.unit_price_basic_insurance != 0 && param.unit_price_basic_insurance != null) {
        arrHtmls.push('<tr>');
        arrHtmls.push('<td>基本服务费</td><td>￥'+param.unit_price_basic_insurance*param.details.length+'</td>');
        arrHtmls.push('</tr>');
    }
    //price_different_office
    if (param.price_different_office !== undefined && param.price_different_office != 0 && param.price_different_office != null) {
        arrHtmls.push('<tr>');
        arrHtmls.push('<td>异店还车费</td><td>￥'+param.price_different_office+'</td>');
        arrHtmls.push('</tr>');
    }
    if (param.price_take_car !== undefined && param.price_take_car != 0 && param.price_take_car != null) {
        arrHtmls.push('<tr>');
        arrHtmls.push('<td>送车上门服务费</td><td>￥'+param.price_take_car+'</td>');
        arrHtmls.push('</tr>');
    }
    if (param.price_return_car !== undefined && param.price_return_car != 0 && param.price_return_car != null) {
        arrHtmls.push('<tr>');
        arrHtmls.push('<td>上门取车服务费</td><td>￥'+param.price_return_car+'</td>');
        arrHtmls.push('</tr>');
    }
    arrHtmls.push('</tbody>');
    arrHtmls.push('</table>');
    
    arrHtmls.push('</div>');
    if (!skipDrawPanel) {
        arrHtmls.push('</div>');
    }
    
    return arrHtmls.join("\n");
}

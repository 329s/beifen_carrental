/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if ($.custom.lan) {
    $.custom.lan.defaults.sys.prompt = '提示';
    $.custom.lan.defaults.sys.ok = '好的';
    $.custom.lan.defaults.sys.cancel = '取消';
    $.custom.lan.defaults.sys.sessionTimeoutOrSigninByOtherPleaseResignin = '您的会话已超时或该账号在其他地点登录，请重新登录。';
    
    $.custom.lan.defaults.role.enabled = '已启用';
    $.custom.lan.defaults.role.disabled = '停用';
    
    $.custom.lan.defaults.bills.timeRange = '时间区间';
    $.custom.lan.defaults.bills.totalAmount = '总金额';
    $.custom.lan.defaults.bills.totalOrders = '总订单数';
    $.custom.lan.defaults.bills.succeedAmount = '成功金额';
    $.custom.lan.defaults.bills.succeedOrders = '成功订单数';

    $.custom.lan.defaults.vehicle.newDriver = '新手';
    $.custom.lan.defaults.vehicle.driverLisence = '驾照';
    $.custom.lan.defaults.vehicle.newDriverLimited = '限制新手';
    $.custom.lan.defaults.vehicle.driverLisenceLimited = '限制驾照';
    $.custom.lan.defaults.vehicle.newDriverAllowed = '不限新手';
    $.custom.lan.defaults.vehicle.driverLisenceNotLimited = '不限驾照';
    $.custom.lan.defaults.vehicle.naturallyAspirated = '自然吸气';
    $.custom.lan.defaults.vehicle.turboCharged = '涡轮增压';
    $.custom.lan.defaults.vehicle.engineFront = '前置';
    $.custom.lan.defaults.vehicle.engineMiddle = '中置';
    $.custom.lan.defaults.vehicle.engineRear = '后置';
    $.custom.lan.defaults.vehicle.driverWheelFront = '前驱';
    $.custom.lan.defaults.vehicle.driverWheelRear = '后驱';
    $.custom.lan.defaults.vehicle.driverWheelFull = '四驱';
    $.custom.lan.defaults.vehicle.addVehicleBrand = '添加车型品牌';
    $.custom.lan.defaults.vehicle.addVehicleSeries = '添加车系';
    $.custom.lan.defaults.vehicle.vehicleBrand = '车型品牌';
    $.custom.lan.defaults.vehicle.vehicleSeries = '车系';
    $.custom.lan.defaults.vehicle.kilometer = '公里';
    $.custom.lan.defaults.vehicle.days = '天';
    $.custom.lan.defaults.vehicle.overflow = '已超';
    $.custom.lan.defaults.vehicle.left = '剩余';
    $.custom.lan.defaults.vehicle.drivingLisenceWouldExpired = '驾照即将过期，请确认是否出租';
    $.custom.lan.defaults.vehicle.youConfirmedToRentPleaseSubmitAgain = '您选择了确认出租，请再次提交订单';
    
    $.custom.lan.defaults.office.nearAirPort = '近机场';
    $.custom.lan.defaults.office.nearTrainStation = '近火车站';
    $.custom.lan.defaults.office.nearBusStation = '近汽车站';
    $.custom.lan.defaults.office.nearSubway = '近地铁';
    $.custom.lan.defaults.office.normal = '正常';
    $.custom.lan.defaults.office.closed = '关闭';
}

if ($.custom.utils.lan) {
    $.custom.utils.lan.defaults.days = '天';
    $.custom.utils.lan.defaults.hours = '小时';
    $.custom.utils.lan.defaults.minutes = '分';
    $.custom.utils.lan.defaults.seconds = '秒';
    
    $.custom.utils.lan.defaults.titleError = '错误';
    $.custom.utils.lan.defaults.titleWarning = '警告';
    $.custom.utils.lan.defaults.titlePrompt = '提示';
    $.custom.utils.lan.defaults.titleInfo = '信息';
    $.custom.utils.lan.defaults.msgCannotParseJson = '无法解析JSON数据:{0}';
    $.custom.utils.lan.defaults.msgGotResponseFailedError = '获取响应失败！ 错误信息：';
    $.custom.utils.lan.defaults.msgAreYouSureToDeleteSelectedItems = '你确定要删除所选项目吗？';
    $.custom.utils.lan.defaults.msgPleaseConfirmAgainToDeleteItems = '请再次确认以删除所选项目。';
    $.custom.utils.lan.defaults.msgPleaseFinishEditingCellFirst = '请先结束编辑的项目！';
    $.custom.utils.lan.defaults.msgAreYouSureToSaveChangedItems = '你确定要保存所更改的项目吗？';
    $.custom.utils.lan.defaults.msgAreYouSureToDoThisOperation = '你确定要执行此项操作吗？';
    $.custom.utils.lan.defaults.msgNumRowsAreChanged = '修改了 {0} 行。';
    $.custom.utils.lan.defaults.msgUpdateUrlNotConfiguredSoSkip = '\'updateUrl\' 未配置，所以更改的项目将不会被保存。';
    $.custom.utils.lan.defaults.msgSaveUrlNotConfiguredSoSkip = '\'saveUrl\' 未配置，所以新增的项目将不会被保存。';
    $.custom.utils.lan.defaults.msgDeleteUrlNotConfiguredSoSkip = '\'deleteUrl\' 未配置，所以所选项目将不会被删除。';
    $.custom.utils.lan.defaults.msgSavingItemByIndex = '正在保存第 {0} 条项目。';
    $.custom.utils.lan.defaults.msgYouShouldSelectARow = '请先选择一行数据！';
    $.custom.utils.lan.defaults.msgRequestGotEmptyData = '您的请求收到的响应数据为空！';
}

if ($.custom.easyui) {
}

if ($.custom.dwz) {
    $.custom.dwz.defaults.paginationTextDisplay = '显示';
    $.custom.dwz.defaults.paginationTextDisplayItems = '条，共{0}条';
}

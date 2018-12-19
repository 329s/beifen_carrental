<?php

namespace common\components;

class Consts
{
    const PERMISSION_TYPE_MENU = 1001;
    const PERMISSION_TYPE_ACTION = 1002;
    
    const KEY_OVERTIME_FREE_MINUTES = 21101;      // 超时忽略计费时间范围
    const KEY_OVERTIME_1HOUR_MINUTES = 21102;    // 超时1小时时间认定范围 
    const KEY_OVERTIME_1DAY_HOURS = 21103;          // 租车，代驾超时 达到1天的时间计算规则
    const KEY_OVERTIME_HALF_DAY_HOURS = 21104;  // 超时保险、GPS、座椅半天时间计算规则
    const KEY_OVERMILEAGE_BY_OVER_HOURS = 21105;  // 超时超里程按日、里程计算规则
    
    const KEY_INVICE_TAX = 21201;                            // 发票税率
    const KEY_SWIPE_CARD_TAX = 21202;                    // 刷卡税率及封顶金额
    
    const KEY_INSTALLMENT_SETTLEMENT_PERIOD = 21231; // 分期结算周期
    const KEY_INSTALLMENT_SETTLEMENT_REMIND = 21232;  // 分期结算提醒天数
    
    const KEY_CAR_REMIND_RED_HOURS = 21241;        // 取车、还车红色提醒小时数
    const KEY_CAR_REMIND_YELLOW_HOURS = 21242;  // 取车、还车黄色提醒小时数
    const KEY_CAR_REMIND_BLUE_DAYS = 21243;        // 取车、还车蓝色提醒天数
    
    const KEY_CHECKOUT_MONEY_REMIND_YELLOW = 21251;
    const KEY_CHECKOUT_MONEY_REMIND_PINK = 21252;
    const KEY_CHECKOUT_MONEY_REMIND_RED = 21253;
    const KEY_CHECKOUT_MONEY_REMIND_GREEN = 21254;
    
    const KEY_POUNDAGE_VALID_PERIOD = 21301;          // 押金（预授权）有效期
    const KEY_POUNDAGE_REMIND_DAYS = 21302;            // 押金（预授权）提醒天数
    const KEY_VIOLATION_POUNDAGE_VALID_PERIOD = 21303;  // 违章押金期限
    const KEY_VIOLATION_POUNDAGE_REMIND_DAYS = 21304;  // 违章押金提醒天数
    const KEY_VIOLATION_POUNDAGE_AMOUNT = 21305;  // 违章押金数额
    
    const KEY_VEHICLE_MAINTENANCE_REMIND_DAYS = 21401;          // 车辆保养提前提醒天数
    const KEY_VEHICLE_MAINTENANCE_REMIND_MILEAGE = 21402;    // 车辆保养提前提醒公里数
    
    const KEY_VEHICLE_YEAR_CHECK_REMIND_DAYS = 21411;    // 车辆年检提前提醒天数
    const KEY_VEHICLE_RENEWAL_REMIND_DAYS = 21412;          // 车辆续保提前提醒天数
    
    const KEY_CONSUME_MONEY_TO_INTEGRATION = 21501;        // 消费人民币兑一积分数额
    const KEY_INTEGRATION_TO_MONEY = 21502;                        // 兑换1人民币使用积分数额
    
    const KEY_SMS_USER_SIGNUP = 23101;          // 会员注册短信
    const KEY_SMS_USER_BIRTHDAY = 23102;        // 会员生日祝福短信
    const KEY_SMS_USER_REGISTER_MEMBER = 23103; // 会员办理会员卡短信
    const KEY_SMS_USER_PURCHASE_MEMBER = 23104; // 会员充值短信
    const KEY_SMS_USER_CONSUME = 23105;         // 会员消费短信

    const KEY_SMS_ORDER_CONFIRMED = 23202;      // 订单确认短信
    const KEY_SMS_ORDER_CANCELED = 23203;       // 订单取消短信
    const KEY_SMS_TAKE_CAR_REMIND = 23204;      // 已确认订单取车提前提醒短信
    const KEY_SMS_TAKE_CAR_REMIND_AGAIN = 23205;    // 已确认订单取车再次提醒短信
    const KEY_SMS_ORDER_BOOKED_BY_OFFICE = 23206;   // 门店下单成功短信
    const KEY_SMS_ORDER_BOOKED_BY_APP = 23207;  // 在线预定未支付短信
    const KEY_SMS_ORDER_BOOKED_PAID = 23208;    // 在线预定支付成功短信
    const KEY_SMS_ORDER_CHANGED = 23209;        // 修改订单成功短信

    const KEY_SMS_USER_TAKEN_CAR0 = 23220;       // 客户提车后短信[1]
    const KEY_SMS_USER_TAKEN_CAR1 = 23221;       // 客户提车后短信[2]
    const KEY_SMS_USER_CREDIT_CARD_REMIND = 23222;  // 客户信用卡二次授权提前提醒短信
    const KEY_SMS_USER_RETURN_CAR_REMIND = 23223;   // 客户还车提醒短信
    const KEY_SMS_USER_RETURN_CAR_REMIND_AGAIN = 23224; // 客户还车再次提醒短信
    const KEY_SMS_USER_RELET = 23225;           // 客户续租短信
    const KEY_SMS_USER_RENT_OVERDUE_REMIND = 23226;    // 客户预交租金欠费提醒短信
    const KEY_SMS_USER_LONG_RENT_INSTALLMENT_REMIND = 23227; // 客户长租分期结算提醒

    const KEY_SMS_ORDER_SETTLEMENTED = 23211;   // 结算完成短信
    const KEY_SMS_USER_VIOLATION = 23212;       // 违章短信
    const KEY_SMS_USER_VIOLATION_SETTLEMENT_REMIND = 23213; // 客户违章结算提前提醒短信
    const KEY_SMS_USER_VIOLATION_SETTLEMENTED = 23214;  // 客户违章结算完成短信
	
    const KEY_SMS_USER_RETRIEVE = 23215;  // 找回密码
    const KEY_SMS_ORDER_STORE = 23216;  // 下单通知门店
    const KEY_SMS_ORDER_CAR = 23217;  // 出车短信
    const KEY_SMS_ORDER_RENEWAL = 23218;  // 续租短信
    const KEY_SMS_ORDER_REFUND = 23219;  // 退款信息

    const ID_TYPE_IDENTITY = 1;
    const ID_TYPE_PASSPORT = 2;
    const ID_TYPE_HK_MACAO = 3;
    const ID_TYPE_TAIWAN = 4;
    
    const STATUS_DISABLED = 10;
    const STATUS_ENABLED = 0;
    
    const OVERTIME_FREE_HOURS = 1;
    const OVERTIME_MAX_HOURS = 4;
    const ONEDAY_MIN_HOURS = 5;             // 超过该小时时长按一天算 
    const HOUR_AS_NEXT_DAY = 18;            // 该小时以后按第二天计算 
    const AHEAD_RETURN_CAR_ALLOW_DELTA_SECONDS = 60;
    
    const DEFAULT_MILEAGE_WARNING = 1000;   // 默认公里数提醒
    const DEFAULT_DAYS_WARNING = 30;
    
    const VEHICLE_TRADE_NO_PREFIX = '';
    const RELET_TRADE_NO_PREFIX = 'R';
    const PURCHANSE_TRADE_NO_PREFIX = 'PAY';
    
    const OPTIONAL_SERVICE_OVERTIME_AS_ONE_DAY = false;
    
    const PROCESS_TYPE_FIRST_RENTAL_GIFT_ONE_DAY = 101;
    
    const MAX_VALID_RENT_PERIOD_DAYS = 60; // 申请租车的最久可用天数 
    
    const AUTO_MONTH_PRICE_DAYS = 30;
    
    const DIFFERENT_OFFICE_DISTANCE_PRICE_MIN = 50;         // 异店还车最低服务费
    const DIFFERENT_OFFICE_DISTANCE_PRICE_COEFFICENT = 1.5; // 异店还车每公里收费价格
    const DELIVERRY_CAR_BY_OFFICE_FREE_MIN_MILEAGE = 6;     // 送车上门，上门取车免费公里数 
    const DELEVERRY_CAR_BY_OFFICE_PRICE_COEFFICENT = 1;     // 送车上门，上门取车每公里收费价格
    
    const CAR_DAILY_RENT_PRICE_DEFAULT_COUNT = 16;          // 默认车辆每日租金价格走势的天数

    // sjj
    const ONEHOURS_MIN_MINUTE  =    10;//单程租车超时时间规定小于10分钟不计费
    const ONEHOURS_MAX_MINUTE  =    30;//单程租车超时时间规定大于30分钟算一小时
    const ID_TYPE_MEMU = 'menu';
    const ID_TYPE_ACTION = 'action';
    const ID_TYPE_NODE = 'node';
}


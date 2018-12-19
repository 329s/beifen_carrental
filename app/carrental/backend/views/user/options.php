<?php

use common\helpers\CMyHtml;
use yii\helpers\Html;

$autoId = CMyHtml::genID();

$unitTextRMB = Yii::t('locale', 'RMB Yuan');
$unitTextDays = Yii::t('carrental', 'days');

$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$htmlArray = [];
$arrScripts = [];

$scopeConfig = 'ConfigRent';
$scopeConfigSms = 'ConfigSMS';
$urlSaveConfig = yii\helpers\Url::to(['options/config_rent_edit']);
$urlSaveSmsConfig = yii\helpers\Url::to(['options/config_sms_edit']);

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'display:block;width:100%']);

// 消费积分设置
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'width:50%;float:left']);
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Consumption integration settings'), ['width'=>'100%']);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row;width:100%"]);
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_CONSUME_MONEY_TO_INTEGRATION, ['float_value'=>1]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::tag('div', '', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 6px;"]);
    $htmlArray[] = \Yii::t('locale', 'Consume').
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->float_value, [], 
            [
                'style'=>"width:60px", 'precision'=>2,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'float_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).'元累加1分';
    $htmlArray[] = CMyHtml::endTag('div');
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'width:50%;float:left']);

// 积分兑换设置
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Integration exchange settings'), ['style'=>"width:100%"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row;width:100%"]);
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_INTEGRATION_TO_MONEY, ['int_value'=>1]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::tag('div', '', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = \Yii::t('locale', 'Use').
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:60px", 'precision'=>2,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).'积分兑换1元';
    $htmlArray[] = CMyHtml::endTag('div');
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::endTag('div');

$arrSmsConfigs = [
    [
        'group' => '会员相关短信自动发送设置(<font style="color:blue">说明：{代码}为程序使用，其前后可以加减文字，但不要修改它；内容设置为0后刷新页面可以恢复到默认</font>)',
        'rows' => [
            [
                'type' => \common\components\Consts::KEY_SMS_USER_SIGNUP, 'label' => '网站会员注册是否发送短信', 'flags' => true, 'prompt' => '',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '感谢您注册成为我公司会员，我们将竭诚为您提供优质的用车服务，祝您生活愉快，工作顺利，谢谢！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_USER_BIRTHDAY, 'label' => '会员生日是否自动发送祝福短信', 'flags' => true, 'prompt' => '（每天早上8:00发送，黑名单除外）',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '在这美好的日子里，愿朋友的祝福汇成你快乐的源泉愿祝福萦绕着你，在你永远与春天接壤的梦幻里。祝你：心想事成、幸福快乐！生日快乐！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_USER_REGISTER_MEMBER, 'label' => '会员卡办理是否发送短信', 'flags' => true, 'prompt' => '',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '非常感谢您成为我公司尊贵的{MEMBERTYPE}会员，你的会员卡号为{MEMBERCARD}在接下来的日子我们将竭诚为您提供优质、优惠的用车服务，祝您生活愉快，工作顺利，谢谢！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_USER_PURCHASE_MEMBER, 'label' => '会员卡充值短信发送格式', 'flags' => false, 'prompt' => '（充值时手动发送）',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '您的卡号为{MEMBERCARD}的{MEMBERTYPE}卡，于{RECHARGETIME}充值金额{RECHARGEAMOUNT}元，目前您的会员卡余额为{CARDLEFT}元，祝您用车愉快，谢谢！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_USER_CONSUME, 'label' => '会员卡消费后是否发送短信', 'flags' => true, 'prompt' => '（充值时手动发送）',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '您的卡号为{MEMBERCARD}的{MEMBERTYPE}卡，于{CHARGETIME}消费金额{CHARGEAMOUNT}元，目前您的会员卡余额为{CARDLEFT}元，祝您用车愉快，谢谢！',
            ],
        ],
    ],
    [
        'group' => '车辆预定自动发送短信设置(<font style="color:blue">说明：{代码}为程序使用，其前后可以加减文字，但不要修改它；内容设置为0后刷新页面可以恢复到默认</font>)',
        'rows' => [
            [
                'type' => \common\components\Consts::KEY_SMS_ORDER_BOOKED_BY_OFFICE, 'label' => '门店订单下单成功立即发送短信', 'flags' => true, 'prompt' => '',
                'title' => '',
                'content' => '【易卡租车】您已成功预定了一辆{AUTOMODEL}，取车时间{USETIME}，取车地址：{SHOPADDRESS}，电话：{SHOPTELEPHONE}，订单号{ORDERID}。温馨提示：请您携带本人有效驾照正副本、会员注册时所用的证件（身份证，港澳居民来往内地通行证、台胞证、海外护照）、信用卡（机场店高铁店除信用卡外，还需提供本人当天登机牌或高铁票）',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_ORDER_BOOKED_BY_APP, 'label' => '在线预定未支付短信', 'flags' => true, 'prompt' => '',
                'title' => '',
                'content' => '【易卡租车】感谢您选择易卡租车，您的订单{ORDERID}预付款{PRICESTANDARD}元，请6小时内支付，逾期订单可能会被自动取消！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_ORDER_BOOKED_PAID, 'label' => '在线预定支付成功短信', 'flags' => true, 'prompt' => '',
                'title' => '',
                'content' => '【易卡租车】您已成功预定了{AUTOMODEL}已支付完成，取车时间：{USETIME}，取车地址：{SHOPADDRESS}，电话：{SHOPTELEPHONE}，订单号{ORDERID}。温馨提示：请您携带本人有效驾照正副本、会员注册时所用的证件（身份证，港澳居民来往内地通行证、台胞证、海外护照）、信用卡（机场店高铁店除信用卡外，还需提供本人当天登机牌或高铁票）',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_ORDER_CHANGED, 'label' => '修改订单成功发送短信', 'flags' => true, 'prompt' => '',
                'title' => '【易卡租车】您好！',
                'content' => '订单{ORDERID}修改成功！取车时间变更为：{USETIME} {AUTOMODEL}，详询400-876-0101',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_ORDER_CONFIRMED, 'label' => '确认订单后立即发送短信', 'flags' => true, 'prompt' => '',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '感谢您预定我公司{AUTOMODEL}型车辆，您的用车时间为{USETIME}，价格为{PRICESTANDARD}，我们已确认您的订单，请按时到店取车，如有变化请24小时之前通知我们，否则定金不退，谢谢！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_ORDER_CANCELED, 'label' => '取消订单后立即发送短信', 'flags' => true, 'prompt' => '',
                'title' => '【易卡租车】您好！',
                'content' => '您的订单{ORDERID}的订单已成功取消，如有疑问请拨打客服电话：400-876-0101',
            ],
            /*[
                'type' => \common\components\Consts::KEY_SMS_TAKE_CAR_REMIND, 'label' => '已确认订单取车前', 'flags' => false, 'prompt' => '（设为0为不自动发送）', 'interval'=>0, 'intervaltext' => '天发送短信',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '您预定的车型是{AUTOMODEL}，用车时间为{USETIME}，请带齐证件按时到{SHOPNAME}取车，门店详细地址：{SHOPADDRESS}，谢谢！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_TAKE_CAR_REMIND_AGAIN, 'label' => '已确认订单取车前', 'flags' => false, 'prompt' => '（设为0为不自动发送）', 'interval'=>0, 'intervaltext' => '小时再次发送短信',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '您预定的车型是{AUTOMODEL}，用车时间为{USETIME}，目前离您提车仅有{BEHOURS}小时，请带齐证件按时到{SHOPNAME}取车，门店详细地址：{SHOPADDRESS}，谢谢！',
            ],*/
        ],
    ],
    [
        'group' => '车辆出车自动发送短信设置(<font style="color:blue">说明：{代码}为程序使用，其前后可以加减文字，但不要修改它；内容设置为0后刷新页面可以恢复到默认</font>)',
        'rows' => [
            [
                'type' => \common\components\Consts::KEY_SMS_USER_TAKEN_CAR0, 'label' => '出车成功发送短信(1)', 'flags' => true, 'prompt' => '',
                'title' => '【易卡租车】尊敬的{CNAME}您好！',
                'content' => '您本次租赁车型为{AUTOMODEL}，已于{USETIME}取车，租期{DAYS}天，应于{BACKTIME}在{CITY}{SHOPNAME}还车，预计租金及其他费用合计{PRICESTANDARD}元，祝您用车愉快，如有疑问请咨询：400-876-0101。',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_USER_TAKEN_CAR1, 'label' => '出车成功发送短信(2)', 'flags' => true, 'prompt' => '',
                'title' => '【易卡租车】温馨提醒：',
                'content' => '您租用的车辆将于{BACKTIME}到期，还车门店{SHOPNAME}，地址：{SHOPADDRESS}，电话：{SHOPTELEPHONE}，续租或修改还车地点，请在预定还车时间2个工作小时前致电400-876-0101。',
            ],
            /*[
                'type' => \common\components\Consts::KEY_SMS_USER_CREDIT_CARD_REMIND, 'label' => '信用卡预授权二次授权前', 'flags' => [0x10=>'每天一次', 0x20=>'仅发一次'], 'prompt' => '（设为0为不自动发送）', 'interval'=>0, 'intervaltext' => '天发送短信',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '您租用我公司车牌为{AUTOBRAND}的{AUTOMODEL}型车辆，需于{AUTHORIZETIME}前来进行信用卡二次预授权，如若未按时来我公司授权，我们将会直接托收您的预授权，谢谢！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_USER_RETURN_CAR_REMIND, 'label' => '还车前', 'flags' => false, 'prompt' => '（设为0为不自动发送）', 'interval'=>0, 'intervaltext' => '天发送短信',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '您租用我公司车牌为{AUTOBRAND}的{AUTOMODEL}型车辆，将于{BACKTIME}还车，请按时到{SHOPNAME}还车，谢谢！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_USER_RETURN_CAR_REMIND_AGAIN, 'label' => '还车前', 'flags' => false, 'prompt' => '（设为0为不自动发送）', 'interval'=>0, 'intervaltext' => '小时再次发送短信',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '您租用我公司车牌为{AUTOBRAND}的{AUTOMODEL}型车辆，目前离您还车仅有{BEHOURS}小时，请按时到{SHOPNAME}还车，谢谢！',
            ],*/
            [
                'type' => \common\components\Consts::KEY_SMS_USER_RELET, 'label' => '客户续租后立即发送短信', 'flags' => true, 'prompt' => '',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '感谢您租用我公司车牌为{AUTOBRAND}的{AUTOMODEL}型车辆，您的用车时间已续租到{BACKTIME}，请您注意驾驶安全，遵守交通规则，祝您用车愉快，谢谢！',
            ],
            /*[
                'type' => \common\components\Consts::KEY_SMS_USER_RENT_OVERDUE_REMIND, 'label' => '预交租金欠费后发送短信', 'flags' => [0x01=>'是', 0x02=>'否', 0x10=>'每天一次', 0x20=>'仅发一次'], 'prompt' => '',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '感谢您租用我公司车牌为{AUTOBRAND}的{AUTOMODEL}型车辆，您目前已欠费{ARREARAGE}元，请您尽早来我公司续费，谢谢！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_USER_LONG_RENT_INSTALLMENT_REMIND, 'label' => '长租分期结算前', 'flags' => [0x10=>'每天一次', 0x20=>'仅发一次'], 'prompt' => '（设为0为不自动发送）', 'interval'=>0, 'intervaltext' => '天发送短信',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '您租用我公司车牌为{AUTOBRAND}的{AUTOMODEL}型车辆，需于{BALANCETIME}前来进行分期结算，谢谢！',
            ],*/
        ],
    ],
    [
        'group' => '车辆结算及违章发送短信设置(<font style="color:blue">说明：{代码}为程序使用，其前后可以加减文字，但不要修改它；内容设置为0后刷新页面可以恢复到默认</font>)',
        'rows' => [
            [
                'type' => \common\components\Consts::KEY_SMS_ORDER_SETTLEMENTED, 'label' => '结算完成时立即发送短信', 'flags' => true, 'prompt' => '',
                'title' => '【易卡租车】尊敬的{CNAME}，',
                'content' => '您本单消费金额为{PRICESTANDARD}元，感谢您选择易卡租车，请您对本次租车服务进行点评，服务监督热线：0579-82760101。您的反馈是我们改善的动力，我们将努力做得更好。',
            ],
            /*[
                'type' => \common\components\Consts::KEY_SMS_USER_VIOLATION, 'label' => '违章短信发送格式', 'flags' => false, 'prompt' => '（违章录入时手动发送）',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '感谢您租用我公司车牌为{AUTOBRAND}的{AUTOMODEL}型车辆，租赁期间如有违章我们会及时通知您处理，欢迎下次惠顾，祝您工作及生活愉快，谢谢！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_USER_VIOLATION_SETTLEMENT_REMIND, 'label' => '违章结算前', 'flags' => [0x10=>'每天一次', 0x20=>'仅发一次'], 'prompt' => '（设为0为不自动发送）', 'interval'=>0, 'intervaltext' => '天发送短信',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '您租用我公司车牌为{AUTOBRAND}的{AUTOMODEL}型车辆，需于{PECCANCYTIME}前来进行违章结算，请尽快来我公司进行处理，谢谢！',
            ],
            [
                'type' => \common\components\Consts::KEY_SMS_USER_VIOLATION_SETTLEMENTED, 'label' => '违章结算完成时立即发送短信', 'flags' => true, 'prompt' => '',
                'title' => '尊敬的{CNAME}您好！',
                'content' => '感谢您租用我公司车牌为{AUTOBRAND}的{AUTOMODEL}型车辆，您在{USETIME}期间{PECCANCY}，结算完成，欢迎下次惠顾，谢谢！',
            ],*/
        ],
    ],
];

foreach ($arrSmsConfigs as $arrGroup) {
    $htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:block;width:100%"]);
    $htmlArray[] = CMyHtml::beginPanel($arrGroup['group'], ['width'=>"100%"]);

    foreach ($arrGroup['rows'] as $cfg) {
        $objSmsConfig = \common\models\Pro_config_sms::instanceByType($cfg['type'], ['title'=>$cfg['title'], 'content'=>$cfg['content']]);
        if ($objSmsConfig) {
            $htmlArray[] = CMyHtml::beginTag('div', ['style'=>'display:block;width:100%;height:auto']);

            $labelArray = [$cfg['label']];
            if (isset($cfg['interval'])) {
                $labelArray[] = \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, '', $objSmsConfig->send_interval, [],
                    [
                        'style'=>"width:60px", 'precision'=>0,
                        'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveSmsConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfigSms}':{'id':{$objSmsConfig->id}, 'send_interval':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
                    ]);
            }
            if (isset($cfg['intervaltext'])) {
                $labelArray[] = $cfg['intervaltext'];
            }
            if ($cfg['flags']) {
                $flagsArray = [0x01=>\Yii::t('locale', 'Yes'), 0x02=>\Yii::t('locale', 'No')];
                if (is_array($cfg['flags'])) {
                    $flagsArray2 = $cfg['flags'];
                }
                else {
                    $labelArray[] = \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_RATIOBUTTONLIST, "send_flag_{$objSmsConfig->id}", 
                        $objSmsConfig->send_flag, $flagsArray, 
                        [
                            'style'=>"display:inline;padding-left:8px;padding-right:8px",
                            'onchange'=>"onSelectFlags{$autoId}($(this), {$objSmsConfig->id}, 'send_flag')",
                        ]);
                }
            }
            if (!empty($cfg['prompt'])) {
                $labelArray[] = $cfg['prompt'];
            }

            $htmlArray[] = CMyHtml::tag('div', implode("", $labelArray), ['style'=>"display:block;width:100%;margin:3px 6px 3px 6px"]);
            $htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table;width:100%;margin:3px 6px 3px 6px"]);
            $htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row;width:100%;margin:3px 6px 3px 6px"]);
            $htmlArray[] = CMyHtml::tag('div', \Yii::t('carrental', 'Title content'), ['style'=>"display:table-cell;padding::3px 6px 3px 6px"]);
            $htmlArray[] = CMyHtml::tag('div', 
                \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_TEXTBOX, '', $objSmsConfig->title, [],
                    [
                        'style'=>"width:100%", 'precision'=>0,
                        'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveSmsConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfigSms}':{'id':{$objSmsConfig->id}, 'title':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
                    ]), 
                ['style'=>"width:auto;display:table-cell;padding::3px 6px 3px 6px;"]);
            $htmlArray[] = CMyHtml::endTag('div');
            
            $htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row;width:100%;margin:3px 6px 3px 6px;"]);
            $htmlArray[] = CMyHtml::tag('div', \Yii::t('carrental', 'Text content'), ['style'=>"display:table-cell;padding::3px 6px 3px 6px;"]);
            $htmlArray[] = CMyHtml::tag('div', 
                \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_TEXTBOX, '', $objSmsConfig->content, [],
                    [
                        'style'=>"width:100%", 'precision'=>0,
                        'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveSmsConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfigSms}':{'id':{$objSmsConfig->id}, 'content':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
                    ]), 
                ['style'=>"width:auto;display:table-cell;padding::3px 6px 3px 6px;"]);
            $htmlArray[] = CMyHtml::endTag('div');
            
            $htmlArray[] = CMyHtml::endTag('div');

            $htmlArray[] = CMyHtml::endTag('div');
        }
    }

    $htmlArray[] = CMyHtml::endPanel();
    $htmlArray[] = CMyHtml::endTag('div');
}

$scriptsContent = <<<EOD
function onSelectFlags{$autoId}(obj, id, field) {
    easyuiFuncDebugThisValue(obj);
}
EOD;

$htmlArray[] = Html::script($scriptsContent);

echo implode("\n", $htmlArray);

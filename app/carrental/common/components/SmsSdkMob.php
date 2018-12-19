<?php

namespace common\components;
/* 
 * SMS sdk
 */
class SmsSdkMob extends \yii\base\Component
{
    public $appKey = '';
    public $url = 'https://webapi.sms.mob.com/custom/msg';
    
    /**
     * @inheritdoc
     * @return \common\components\SmsSdkMob the newly created [[\common\components\SmsSdkMob]] instance.
     */
    public static function create($config)
    {
        try {
            $config['class'] = get_called_class();
            
            $object = \Yii::createObject($config);
            $object->init();
            
        } catch (\Exception $e) {
            throw $e;
        }
        
        return $object;
    }
    
    public function init()
    {
        if (empty($this->appKey) && isset(\Yii::$app->params['mob.sms.appkey'])) {
            $this->appKey = \Yii::$app->params['mob.sms.appkey'];
        }
        if (isset(\Yii::$app->params['mob.sms.sendurl']) && !empty(\Yii::$app->params['mob.sms.sendurl'])) {
            $this->url = \Yii::$app->params['mob.sms.sendurl'];
        }
    }
    
    public function sendSms($phone, $msgId, $params = [])
    {
        // convert the template code
        $templateCode = static::convertMsgIdToTemplateCode($msgId);
        
        if ($templateCode == 0) {
            return [false, \Yii::t('locale', 'The MOB sms template code were not configured by sms config ID {code}, please contact the administrator or developer.', ['code'=>$msgId])];
        }
        
        return $this->_sendSMS($phone, $templateCode, $params);
    }
    
    protected function _sendSMS($phone, $templateCode, $params = [])
    {
        $arrData = [
            'appKey' => $this->appKey,
            'phone' => $phone,
            'zone' => '86',
            'templateCode' => $templateCode,
        ];
        
        foreach ($params as $k => $v) {
            // $arrData[$k] = $v;
			//Sumin 接口参数转换小写
            $arrData[$k] = $v;
        }
        // print_r($arrData);exit;
        $arrRet = [true, 'Success'];
        $arrResult = \common\helpers\Utils::queryUrlPost($this->url, $arrData);
		
        if ($arrResult[0] == 200) {
            $arrResponse = json_decode($arrResult[1], true);
            if (!$arrResponse || !is_array($arrResponse) || !isset($arrResponse['status'])) {
                $arrRet[0] = false;
                $arrRet[1] = "Can not parse the response data:{$arrResult[1]}";
            }
            else {
                if ($arrResponse['status'] != 200) {
                    $arrRet[0] = false;
                }
                $arrRet[1] = static::convertErrorCode($arrResponse['status']);
            }
        }
        else {
            $errText = $arrResult[1];
            $arrRet[0] = false;
            $arrRet[1] = $errText;
        }
        
        if (!$arrRet[0]) {
            \Yii::error("send sms msg by mob plugin failed with code:{$arrResult[0]} and err_msg:{$arrRet[1]}", 'sdk');
        }
       
        return $arrRet;
    }
    
    public static function convertMsgIdToTemplateCode($msgId)
    {
        static $convert = [
			\common\components\Consts::KEY_SMS_USER_SIGNUP => 13530737,          // 会员注册短信
            //\common\components\Consts::KEY_SMS_USER_BIRTHDAY => 0,        // 会员生日祝福短信
            //\common\components\Consts::KEY_SMS_USER_REGISTER_MEMBER => 0, // 会员办理会员卡短信
            //\common\components\Consts::KEY_SMS_USER_PURCHASE_MEMBER => 0, // 会员充值短信
            //\common\components\Consts::KEY_SMS_USER_CONSUME => 0,         // 会员消费短信

            //\common\components\Consts::KEY_SMS_ORDER_CONFIRMED => 0,      // 订单确认短信
            // \common\components\Consts::KEY_SMS_ORDER_CANCELED => 14363957,       // 订单取消短信
            \common\components\Consts::KEY_SMS_ORDER_CANCELED => 10165961,       // 订单取消短信
            //\common\components\Consts::KEY_SMS_TAKE_CAR_REMIND => 0,      // 已确认订单取车提前提醒短信
            //\common\components\Consts::KEY_SMS_TAKE_CAR_REMIND_AGAIN => 0,    // 已确认订单取车再次提醒短信
            \common\components\Consts::KEY_SMS_ORDER_BOOKED_BY_OFFICE => 13530745,       // 门店下单成功短信
            \common\components\Consts::KEY_SMS_ORDER_BOOKED_BY_APP => 14363972,       // 在线预定未支付短信
            // \common\components\Consts::KEY_SMS_ORDER_BOOKED_PAID => 13530745,       // 在线预定支付成功短信
            \common\components\Consts::KEY_SMS_ORDER_CHANGED => 14363971,       // 修改订单成功短信

            \common\components\Consts::KEY_SMS_USER_TAKEN_CAR0 => 0,       // 客户提车后短信
            \common\components\Consts::KEY_SMS_USER_TAKEN_CAR1 => 0,       // 客户提车后短信
            //\common\components\Consts::KEY_SMS_USER_CREDIT_CARD_REMIND => 0,  // 客户信用卡二次授权提前提醒短信
            //\common\components\Consts::KEY_SMS_USER_RETURN_CAR_REMIND => 0,   // 客户还车提醒短信
            //\common\components\Consts::KEY_SMS_USER_RETURN_CAR_REMIND_AGAIN => 0, // 客户还车再次提醒短信
            //\common\components\Consts::KEY_SMS_USER_RELET => 0,           // 客户续租短信
            //\common\components\Consts::KEY_SMS_USER_RENT_OVERDUE_REMIND => 0,    // 客户预交租金欠费提醒短信
            //\common\components\Consts::KEY_SMS_USER_LONG_RENT_INSTALLMENT_REMIND => 0, // 客户长租分期结算提醒

            \common\components\Consts::KEY_SMS_ORDER_SETTLEMENTED => 11048075,   // 结算完成短信
            //\common\components\Consts::KEY_SMS_USER_VIOLATION => 0,       // 违章短信
            //\common\components\Consts::KEY_SMS_USER_VIOLATION_SETTLEMENT_REMIND => 0, // 客户违章结算提前提醒短信
            //\common\components\Consts::KEY_SMS_USER_VIOLATION_SETTLEMENTED => 0,  // 客户违章结算完成短信
			
			//新注册短信模板id
          /*   \common\components\Consts::KEY_SMS_USER_SIGNUP => 5853174,          // 会员注册短信
            \common\components\Consts::KEY_SMS_USER_RETRIEVE => 1871584,          // 会员找回密码短信
            \common\components\Consts::KEY_SMS_ORDER_BOOKED_BY_OFFICE => 5853213,          // 客户预定车辆
            \common\components\Consts::KEY_SMS_ORDER_BOOKED_PAID => 5853213,          // 客户预定车辆
            \common\components\Consts::KEY_SMS_ORDER_STORE => 14967914,         // 客户预定车辆通知门店
            \common\components\Consts::KEY_SMS_ORDER_CAR => 14967881,         // 预定出车短信
            \common\components\Consts::KEY_SMS_ORDER_RENEWAL => 14967882,         // 续租短信
            \common\components\Consts::KEY_SMS_ORDER_REFUND => 5853181,         // 退款提醒
            \common\components\Consts::KEY_SMS_ORDER_SETTLEMENTED => 5853181,         // 结算短信
            \common\components\Consts::KEY_SMS_ORDER_CANCELED => 1871586,          // 取消订单
            \common\components\Consts::KEY_SMS_ORDER_CHANGED => 14967885,         // 修改订单 */
        ];
        if (isset($convert[$msgId])) {
            return $convert[$msgId];
        }
        // test
        elseif ($msgId == 8888888) {
            return 8888888;
        }
        return 0;
    }

    public static function convertErrorCode($code) {
        static $arr = [
            200 => '提交成功',
            201 => 'appKey为空',
            202 => '模板号为空',
            203 => 'appKey非法',
            204 => '国家代码或者手机号码为空',
            205 => '手机号码格式错误',
            206 => '模板编号非法',
            207 => '模板格式错误',
            208 => '请求参数与模板参数不相符',
            209 => '发送的短信中存在敏感词',
            210 => '手机号码在黑名单中',
            211 => '账户异常',
            212 => '没有打开发送web短信的开关',
            213 => '账户余额不足',
            214 => '请求IP非法',
            215 => '请求参数长度超过16个字符',
            219 => '同一App下同一手机号每分钟发送短信的条数超过5条',
            220 => '同一个手机号12小时内发送自定义短信的条数为5条',
            221 => '自定义短信不支持中国大陆以外的其他地区',
            222 => '短信内容超过60个字符',
            218 => '服务器繁忙',
        ];
        
        $co = intval($code);
        if (isset($arr[$co])) {
            return $arr[$co];
        }
        return "未知错误码：{$code}";
    }
    
}

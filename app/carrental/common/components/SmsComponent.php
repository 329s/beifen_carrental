<?php

namespace common\components;

/**
 * Sms component 
 */
class SmsComponent extends \yii\base\Component
{
    
    /**
     * @inheritdoc
     * @param type $config
     * @return \common\components\SmsComponent the newly created [[\common\components\SmsComponent]] instance.
     * @throws \Exception
     */
    public static function create($config = [])
    {
        try {
            $config['class'] = isset(\Yii::$app->params['component.sms.class']) ? \Yii::$app->params['component.sms.class'] : '\common\components\SmsSdkMob';
            
            $object = \Yii::createObject($config);
            $object->init();
            
        } catch (\Exception $e) {
            throw $e;
        }
        
        return $object;
    }
    
    /**
     * 
     * @param string $phone
     * @param integer $msgId
     * @param array $params
     * @return array [boolean, message]
     */
    public static function send($phone, $msgId, $params = [])
    {
		
        $obj = static::create(isset($params['config']) ? $params['config'] : []);
        $ret = $obj->sendSms($phone, $msgId, $params);
        if (is_array($ret) && isset($ret[0]) && isset($ret[1])) {
            if (!$ret[0]) {
                \Yii::error("send sms msg by msg_id:{$msgId} failed with err_msg:{$ret[1]}", 'sdk');
            }
        }
        return $ret;
    }
    
    public function init()
    {
    }
    
    /**
     * 
     * @param string $phone
     * @param string $msgId
     * @param array $params
     * @return array [boolean, message]
     * @throws \Exception
     */
    public function sendSms($phone, $msgId, $params = [])
    {
        throw new \Exception('send sms function should not be called by base sms component class object');
    }
    
    /**
     * 
     * @param integer $msgId
     * @param array $params
     * @return string sms content
     */
    public static function formatSmsContent($msgId, $params = [])
    {
        $objSmsConfig = \common\models\Pro_config_sms::getByType($msgId);
        if (!$objSmsConfig || !$objSmsConfig->isEnabled()) {
            return false;
        }
        
        $p = [];
        foreach ((array) $params as $name => $value) {
            $p['{' . $name . '}'] = $value;
        }
        
        $smsText = ($p === [] ? $objSmsConfig->title.$objSmsConfig->content : strtr($objSmsConfig->title, $p).strtr($objSmsConfig->content, $p));
        
        return $smsText;
    }
    
    /**
     * @desc 发送PC短信验证码
     * @param api $api
     * @param array $params
     * @return string sms content
     */
    public function postRequest($api, array $params = array(), $timeout = 30 ) {
        $ch = curl_init(); 
        // 以返回的形式接收信息 
        curl_setopt( $ch, CURLOPT_URL, $api ); 
        // 设置为POST方式 
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
        curl_setopt( $ch, CURLOPT_POST, 1 ); 
        // 不验证https证书 
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) ); 
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 ); 
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 ); 
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout ); 
        // 发送数据 
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/x-www-form-urlencoded;charset=UTF-8', 'Accept: application/json', ) ); 
        // 不要忘记释放资源 
        $response = curl_exec( $ch ); 
        curl_close( $ch ); return $response;
    }




}


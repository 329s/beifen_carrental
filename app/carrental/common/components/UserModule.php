<?php

namespace common\components;

class UserModule {
    
    public static function getVipLevelsArray() {
        return [
            \common\models\Pub_user_info::VIP_LEVEL_NORMAL => \Yii::t('locale', 'Normal member'),
            \common\models\Pub_user_info::VIP_LEVEL_SILVER => \Yii::t('locale', 'Silver member'),
            \common\models\Pub_user_info::VIP_LEVEL_GOLDEN => \Yii::t('locale', 'Golden member'),
            \common\models\Pub_user_info::VIP_LEVEL_DIAMOND => \Yii::t('locale', 'Diamond member'),
        ];
    }
    
    public static function getUserObjectsArray($userIdArray) {
        $arrData = [];
        $cdb2 = \common\models\Pub_user::find();
        if (is_array($userIdArray) && !empty($userIdArray)) {
            $cdb2->where(['id' => $userIdArray]);
        }
        else {
            $cdb2->where(['id' => intval($userIdArray)]);
        }
        $arrRows = $cdb2->all();
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row;
        }
        return $arrData;
    }
    
    public static function getUserInfoObjectsByUserIdArray($userIdArray) {
        $arrData = [];
        $cdb2 = \common\models\Pub_user_info::find();
        if (is_array($userIdArray) && !empty($userIdArray)) {
            $cdb2->where(['id' => $userIdArray]);
        }
        else {
            $cdb2->where(['id' => intval($userIdArray)]);
        }
        $arrRows = $cdb2->all();
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row;
        }
        
        return $arrData;
    }
    
    public static function getMemberCardInfosArray($cardIdArray) {
        $arrData = [];
        $cdb2 = \common\models\Pro_member_card::find();
        if (is_array($cardIdArray) && !empty($cardIdArray)) {
            $cdb2->where(['id' => $cardIdArray]);
        }
        else {
            $cdb2->where(['id' => intval($cardIdArray)]);
        }
        $arrRows = $cdb2->asArray()->all();
        foreach ($arrRows as $row) {
            $arrData[$row['id']] = $row;
        }
        
        return $arrData;
    }
    
    public static function verifyUserPhoneSmsCode($phone, $code, $zone = '86') {
        if (isset(\Yii::$app->params['mob.sms.enabled']) && !\Yii::$app->params['mob.sms.enabled']) {
            return array(true, \Yii::t('locale', 'Success'));
        }
        $url = 'https://webapi.sms.mob.com/sms/verify';
        $appKey = \Yii::$app->params['mob.sms.appkey'];
        
        $params = array(
            'appkey' => $appKey,
            'phone' => $phone,
            'zone' => $zone,
            'code' => $code,
        );
        
        $result = \common\helpers\Utils::queryUrlPost($url, $params);
        
        $errMsg = \Yii::t('locale', 'Unknown error');
        
        if ($result[0] == 200) {
            $response = $result[1];
            $oResult = json_decode($response);
            if (isset($oResult->status) && $oResult->status == 200) {
                $errMsg = \Yii::t('locale', 'Success');
                return [true, $errMsg];
            }
            else {
                $errCodes = [
                    405 => 'AppKey为空',
                    406 => 'AppKey无效',
                    456 => '国家代码或手机号码为空',
                    457 => '手机号码格式错误',
                    466 => '请求校验的验证码为空',
                    467 => '请求校验验证码频繁',
                    468 => '验证码错误',
                    474 => '没有打开服务端验证开关',
                ];
                $errMsg = (isset($errCodes[$oResult->status]) ? $errCodes[$oResult->status] : '未知错误');
                
                \Yii::warning("verify user phone sms code by phone:{$phone} zone:{$zone} and code:{$code} failed with http error:{$oResult->status} errmsg:{$errMsg}.", 'user');
            }
        }
        else {
            $errMsg = $result[1];
            \Yii::error("verify user phone sms code by phone:{$phone} zone:{$zone} and code:{$code} failed with http error:{$result[0]} errmsg:{$errMsg}.", 'user');
        }
        
        return [false, $errMsg];
    }
    
    public static function verifyUserIdentityCardNo($identityCardNo) {
        if (!preg_match('/^(^\d{18}$|^\d{17}(\d|X|x))$/', $identityCardNo)) {
            return array(-1, \Yii::t('locale', 'Identity card number not valid'));
        }
        
        if (isset(\Yii::$app->params['mob.identify.enabled']) && !\Yii::$app->params['mob.identify.enabled']) {
            return array(0, \Yii::t('locale', 'Success'));
        }
        $url = 'http://apicloud.mob.com/idcard/query';
        $appKey = \Yii::$app->params['mob.identity.appkey'];
        
        $params = array(
            'key' => $appKey,
            'cardno' => $identityCardNo,
        );
        
        $result = \common\helpers\Utils::queryUrlGet($url, $params);
        if ($result[0] == 200) {
            $response = $result[1];
            $oResult = json_decode($response);
            if (isset($oResult->retCode) && $oResult->retCode == 200) {
                return array(0, \Yii::t('locale', 'Success'));
            }
            else if (isset($oResult->msg)) {
                \Yii::warning("verify user identity card no:{$identityCardNo} failed with http error:{$oResult->retCode} errmsg:{$oResult->msg}.", 'user');
                return array(1, $oResult->msg);
            }
            // test
            else {
                \Yii::error("verify user identity card no:{$identityCardNo} failed with unknown response:[{$response}]", 'user');
                return array(-1, '验证身份证信息无法解析响应内容。');
            }
        }
        else {
            \Yii::error("verify user identity card no:{$identityCardNo} failed with http error:{$result[0]} errmsg:{$result[1]}.", 'user');
            return array(1, $result[1]);
        }
        
        return array(-1, \Yii::t('locale', 'Unknown error'));
    }
    
    public static function verifyPassportNo($passportNo) {
        if (!preg_match('/^(G)\d{8}$/', $passportNo)) {
            return array(-1, \Yii::t('locale', '{name} number not valid', ['name'=> \Yii::t('locale', 'Passport')]));
        }
        return array(0, \Yii::t('locale', 'Success'));
    }
    
    public static function verifyHKandMacaoPassNo($passportNo) {
        if (!preg_match('/^[A-Z]\d{10}$/', $passportNo)) {
            return array(-1, \Yii::t('locale', '{name} number not valid', ['name'=> \Yii::t('locale', 'Hong Kong and Macao Residents Traveling to Mainland Pass')]));
        }
        return array(0, \Yii::t('locale', 'Success'));
    }
    // 台湾台胞证验证
    public static function verifyTWandMacaoPassNo($passportNo){
        if (!preg_match('/^\d{8}$|^\d{9}$|^\d{10}$/', $passportNo)) {
            return array(-1, \Yii::t('locale', '{name} number not valid', ['name'=> \Yii::t('locale', 'Taiwan Residents Traveling to Mainland Pass')]));
        }
        return array(0, \Yii::t('locale', 'Success'));
    }
    
    public static function verifyTaiwanPassNo($passportNo) {
        if (!preg_match('/^(^\d{18}$|^\d{17}(\d|X|x))$/', $passportNo)) {
            return array(-1, \Yii::t('locale', '{name} number not valid', ['name'=> \Yii::t('locale', 'Taiwan Residents Traveling to Mainland Pass')]));
        }
        return array(0, \Yii::t('locale', 'Success'));
    }
    
    public static function validateIdentity($identityType, $identityId) {
        if ($identityType == \common\components\Consts::ID_TYPE_IDENTITY) {
            return \common\components\UserModule::verifyUserIdentityCardNo($identityId);
        }
        elseif ($identityType == Consts::ID_TYPE_PASSPORT) {
            return \common\components\UserModule::verifyPassportNo($identityId);
        }
        elseif ($identityType == Consts::ID_TYPE_HK_MACAO) {
            return \common\components\UserModule::verifyHKandMacaoPassNo($identityId);
        }
        elseif ($identityType == Consts::ID_TYPE_TAIWAN) {
            return \common\components\UserModule::verifyTWandMacaoPassNo($identityId);
        }
        else {
            return array(-1, \Yii::t('locale', 'Identity card type invalid'));
        }
        return array(0, \Yii::t('locale', 'Success'));
    }
    
    public static function onInvitedUser($inviteCode) {
        if (empty($inviteCode)) {
            return null;
        }
        $awardUserInfo = \common\models\Pub_user_info::findOne(['invite_code'=>$inviteCode]);
        if ($awardUserInfo) {
            $curTime = time();
            $startTime = strtotime(date('Y-m-d 00:00:00', $curTime));
            $curDayCount = \common\models\Pub_user_integration::find()
                    ->where(['and', ['user_id'=>$awardUserInfo->id], ['>=', 'created_at', $startTime]])
                    ->count();
            if ($curDayCount < 5) {
                $log = \common\models\Pub_user_integration::create($awardUserInfo->id, 5, \common\models\Pub_user_integration::TYPE_INVITE_REGISTER);
                $log->save();
                $awardUserInfo->cur_integration += $log->integral;
                $awardUserInfo->save();
                return $awardUserInfo;
            }
        }
        return null;
    }
    
    public static function onInvitedUserRentcar($renterUserInfo, $orderId) {
        if (empty($renterUserInfo->invited_code)) {
            return null;
        }
        $awardUserInfo = \common\models\Pub_user_info::findOne(['invite_code'=>$renterUserInfo->invited_code]);
        if ($awardUserInfo) {
            $checkQuery = \common\models\Pro_vehicle_order::find();
            $checkQuery->where(['<>', 'id', $orderId]);
            $checkQuery->andWhere(['and', ['>=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING], ['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]]);
            if ($checkQuery->count()) {
                return null;
            }
            $log = \common\models\Pub_user_integration::create($awardUserInfo->id, 10, \common\models\Pub_user_integration::TYPE_INVITE_RENTCAR);
            $log->save();
            $awardUserInfo->cur_integration += $log->integral;
            $awardUserInfo->save();
            return $awardUserInfo;
        }
        return null;
    }
    
    /**
     * 
     * @param \common\models\Pub_user_info $objUserInfo
     * @param number $rentPrice
     * @return type
     */
    public static function onUserConsumeByRent($objUserInfo, $rentPrice, $autoSave = true) {
        if (!$objUserInfo) {
            return null;
        }
        
        $factor = 40;
        switch ($objUserInfo->vip_level)
        {
        case \common\models\Pub_user_info::VIP_LEVEL_DIAMOND:
            $factor = 10;
            break;
        case \common\models\Pub_user_info::VIP_LEVEL_GOLDEN:
            $factor = 20;
            break;
        case \common\models\Pub_user_info::VIP_LEVEL_SILVER:
            $factor = 30;
            break;
        case \common\models\Pub_user_info::VIP_LEVEL_NORMAL:
            $factor = 40;
            break;
        default :
            $factor = 40;
            break;
        }
        
        $log = \common\models\Pub_user_integration::create($objUserInfo->id, intval(round($rentPrice / $factor)), \common\models\Pub_user_integration::TYPE_RENTCAR);
        $objUserInfo->cur_integration += $log->integral;
        if ($autoSave) {
            $log->save();
            $objUserInfo->save();
        }
        return $log;
    }
    
}

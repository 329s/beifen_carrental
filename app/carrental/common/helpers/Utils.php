<?php

namespace common\helpers;

class Utils
{

    const RELATEVE_TO_ROOT = '../../../..';
    
    public static function boolvalue($value) {
        if (strcasecmp($value, 'false') == 0) {
            return false;
        }
        if (empty($value)) {
            return false;
        }
        return true;
    }
    
    public static function randomStr($length) {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len = 62;
        while($length > $len) {
            $str .= $str;
            $len += 62;
        }
        $str = str_shuffle($str);
        return substr($str, 0, $length);
    }
    
    /**
     * convert string with underline character to hump formation, for example, 
     * abc_def => AbcDef
     * @param string $str
     * @param boolean $ucfirst
     * @return string
     */
    public static function underline2Hump($str, $ucfirst = true) {
        $arr = explode('_', $str);
        foreach ($arr as $k => $v) {
            $arr[$k] = ucfirst($v);
        }
        if (!$ucfirst) {
            $arr[0] = strtolower($arr[0]);
        }
        return implode('', $arr);
    }

    public static function getIP() {
        return $_SERVER["REMOTE_ADDR"];
    }
    
    public static function isMobile() {

        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock = preg_match('|\(.*?\)|', $useragent, $matches) > 0 ? $matches[0] : '';
        $mobile_os_list = array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
        $mobile_token_list = array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');
        $found_mobile = self::checkSubstrs($mobile_os_list, $useragent_commentsblock) ||
                self::checkSubstrs($mobile_token_list, $useragent);
        if ($found_mobile) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function checkSubstrs($substrs, $text){
        foreach ($substrs as $substr)
            if (false !== strpos($text,$substr)) { 
            return true;
        }
        return false;
    }
    
    public static function getRootUrl() {
        $urlBase = \Yii::$app->request->getBaseUrl();
        if (isset(\Yii::$app->params['app.relative'])) {
            $pos = strpos($urlBase, \Yii::$app->params['app.relative']);
            if ($pos !== false) {
                $urlBase = substr($urlBase, 0, $pos);
            }
        }
        return $urlBase;
    }
    
    public static function toFileUri($filePath) {
        if (empty($filePath)) {
            return '';
        }
        $baseUrl = self::getRootUrl();
        return $baseUrl.ltrim($filePath, '\\/');
    }
    
    public static function toFileAbsoluteUrl($filePath) {
        return \Yii::$app->request->getHostInfo().self::toFileUri($filePath);
    }
    
    public static function toTimestamp($dateStr, $timeStr = false) {
        if (is_integer($dateStr)) {
            return $dateStr;
        }
        $dateStr = strval($dateStr);
        if (empty($dateStr)) {
            return 0;
        }
        else if (preg_match('/^\d+$/', $dateStr)) {
            return intval($dateStr);
        }
        else {
            if (preg_match('/^\d+-\d+-\d+$/', $dateStr)) {
                if ($timeStr) {
                    $dateStr .= ' '.$timeStr;
                }
            }
            return strtotime($dateStr);
        }
    }
    
    public static function humanDeltaTime($delta) {
        $txt = '';
        if ($delta > 0) {
            if ($delta > 86400) {
                $day = round($delta / 86400);
                $txt .= \Yii::t('locale', '{number} days', ['number'=>$day]);
                $delta %= 86400;
            }
            if ($delta > 3600) {
                $hour = round($delta / 3600);
                $delta %= 3600;
                $txt .= \Yii::t('locale', '{number} hours', ['number'=>$hour]);
            }
            if ($delta > 60) {
                $m = round($delta / 60);
                $delta %= 60;
                $txt .= \Yii::t('locale', '{number} minutes', ['number'=>$m]);
            }
            $txt .= \Yii::t('locale', '{number} seconds', ['number'=>$delta]);
        }
        
        return $txt;
    }

    public static function validatePhoneno($phone) {
        if (preg_match('/^((\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$)$/', $phone)) {
            return true;
        }
        return false;
    }
    
    public static function queryUrlPost($url, $params, $timeout = 30, $header = false) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // 以返回的形式接收信息
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        // 设置为POST方式
        curl_setopt( $ch, CURLOPT_POST, 1 );
        if (!empty($params)) {
            if (is_string($params)) {
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
            }
            else {
                curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
            }
        }
        // 不验证https证书
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        if (is_array($header) && !empty($header)) {
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $header ); 
        }
        else {
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                'Accept: application/json',
            ) ); 
        }
        // 发送数据
        $response = curl_exec( $ch );
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpError = curl_error($ch);
        // 不要忘记释放资源
        curl_close( $ch );
        
        if ($httpCode == 200) {
            return array($httpCode, $response);
        }
        else {
            \Yii::error("query url:{$url} with mothod:POST failed with http code:{$httpCode} error:{$httpError}");
        }
        
        return array($httpCode, $httpError);
    }
    
    public static function queryUrlGet($url, $params, $timeout = 30, $exopts = []) {
        if (!empty($params)) {
            $paramsStr = http_build_query($params);
            if (!empty($paramsStr)) {
                $url .= ((strpos($url, '?') === false) ? '?' : '&') . $paramsStr;
            }
        }
        //\Yii::error($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // 以返回的形式接收信息
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 0 );
        // 不验证https证书
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Accept: application/json',
        ) ); 
        if (is_array($exopts)) {
            foreach ($exopts as $k => $v) {
                curl_setopt($ch, $k, $v);
            }
        }
        // 发送数据
        $response = curl_exec( $ch );
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpError = curl_error($ch);
        // 不要忘记释放资源
        curl_close( $ch );
        
        if ($httpCode == 200) {
            return array($httpCode, $response);
        }
        else {
            \Yii::error("query url:{$url} with mothod:GET failed with http code:{$httpCode} error:{$httpError}");
        }
        
        return array($httpCode, $httpError);
    }
    
}
<?php

namespace common\helpers;

/**
 * @desc
 * 自定义函数类
 *
 * @author
 * 嬴益虎，欧阳文进
 */
class MyFunction {

    // 模拟浏览器，得到一个会话
    public static function fGetUrl($url) {
        //启动一个CURL会话
        $ch = curl_init();

        // 要访问的地址
        curl_setopt($ch, CURLOPT_URL, $url);

        // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);

        //模拟用户使用的浏览器，在HTTP请求中包含一个”user-agent”头的字符串。
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");

        //发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
        #curl_setopt($ch, CURLOPT_POST, 1);
        //要传送的所有数据，如果要传送一个文件，需要一个@开头的文件名
        #curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        //连接关闭以后，存放cookie信息的文件名称
        #curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        // 包含cookie信息的文件名称，这个cookie文件可以是Netscape格式或者HTTP风格的header信息。
        #curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        // 设置curl允许执行的最长秒数
        //curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        // 获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // 执行操作
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // 关闭CURL会话
        curl_close($ch);
        return array('code' => $httpcode, 'content' => $result);
    }

    /*
     * 通过CURL POST得到数据
     */

    public static function http_post_data($url, $data_string) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string))
        );
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = array($return_code, $return_content, curl_error($ch));
        curl_close($ch);
        
        return $result;
    }

    /**
     * ######
     * @desc
     * 加密字符串，常用于加密密码
     *
     * @param
     * string	$password	//待加密的字符串
     * boolean	$isAdmin	//加混淆码
     *
     * @return
     * string
     *
     * @example
     * 
     */
    public static function funHashPassword($password, $isAdmin = false) {

        // 管理员验证，加混淆码
        if ($isAdmin) {
            return md5(md5($password) . 'shenglang&*(>!');
        } else {
            return md5(md5($password) . 'qiantai!#%');
        }
    }

    /**
     * ######
     * @desc
     * 用于提示信息 跳转
     *
     * @param
     * string	$strMes		(提示信息)
     * string	$url		(跳转到那一页)
     * string	$strTarGet	(target)
     *
     * @return
     * void
     *
     * @example
     * 
     */
    static function funAlert($strMes, $url = '', $strTarGet = '') {
        header("content-type:text/html;charset=utf-8");
        if (!empty($strMes)) {
            echo "<script>alert('$strMes');</script>";
        } else {
            echo "<script>alert('".Yii::t('base', 'Illegal Operation')."');</script>";
        }

        //跳转
        if (!empty($url) && !empty($strTarGet)) {
            echo "<script>window.open('$url', '$strTarGet');</script>";
        } else if (!empty($url) && $url != '-1') {
            echo "<script>location.href='$url';</script>";
        } else if (!empty($url) && $url == '-1') {
            echo "<script>history.go(-1);</script>";
        }
    }

    /**
     * 取得系统设置值
     * @author	嬴益虎(whoneed@yeah.net)
     * @param	string	$strKey		需要取得的key数组
     * @time	2011-10-11
     * @return	array or string
     */
    static function funGetSetting($strKey = '') {

        // 返回
        $strReturn = array();

        //查询缓存是否存在,存在直接返回，不存在，查询数据，进行缓存并且返回
        $strReturn = Yii::app()->cache->get('setting');

        if ($strReturn) {
            
        } else {
            // 取得配制表信息
            $objConfig = Rbac_config::model()->findAll();
            if ($objConfig) {
                foreach ($objConfig as $k => $v) {
                    $strReturn[$v->ope_type] = $v->ope_value;
                }

                // 设置缓存
                $strReturn = serialize($strReturn);
                Yii::app()->cache->set('setting', $strReturn, 99999999);
            }
        }

        if (!$strReturn) {
            self::funAlert(Yii::t('base', 'Data Error').'!');
        } else {
            /*
              $str = "\$arr = ".$strReturn.";";
              @eval($str);
              $strReturn = $arr; */
            $strReturn = unserialize($strReturn);

            if ($strKey)
                $strReturn = $strReturn[$strKey];
        }

        return $strReturn;
    }

    /**
     * 获取IP
     * @author	欧阳文进(591947605@qq.com)
     * @time	2011-10-12
     * @return	string
     */
    static function funGetIP() {
        return $_SERVER["REMOTE_ADDR"];
    }

    /**
     * 支持utf8中文字符截取
     * @param	string $text		待处理字符串
     * @param	int $start			从第几位截断
     * @param	int $sublen			截断几个字符
     * @param	string $code		字符串编码
     * @param	string $ellipsis	附加省略字符
     * @time	2011-10-14
     * @return	string
     */
    static function funCSubstr($string, $start = 0, $sublen = 12, $ellipsis = '', $code = 'UTF-8') {
        if ($code == 'UTF-8') {
            $tmpstr = '';
            $i = $start;
            $n = 0;
            $str_length = strlen($string); //字符串的字节数
            while (($n + 0.5 < $sublen) and ( $i < $str_length)) {
                $temp_str = substr($string, $i, 1);
                $ascnum = Ord($temp_str); //得到字符串中第$i位字符的ascii码
                if ($ascnum >= 224) {  //如果ASCII位高与224，
                    $tmpstr .= substr($string, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
                    $i = $i + 3;            //实际Byte计为3
                    $n++;    //字串长度计1
                } elseif ($ascnum >= 192) { //如果ASCII位高与192，
                    $tmpstr .= substr($string, $i, 3); //根据UTF-8编码规范，将2个连续的字符计为单个字符
                    $i = $i + 3;            //实际Byte计为2
                    $n++;    //字串长度计1
                } else {     //其他情况下，包括小写字母和半角标点符号，
                    $tmpstr .= substr($string, $i, 1);
                    $i = $i + 1;   //实际的Byte数计1个
                    $n = $n + 0.5;   //小写字母和半角标点等与半个高位字符宽...
                }
            }
            if (strlen($tmpstr) < $str_length) {
                $tmpstr .= $ellipsis; //超过长度时在尾处加上省略号
            }
            return $tmpstr;
        } else {
            $strlen = strlen($string);
            if ($sublen != 0)
                $sublen = $sublen * 2;
            else
                $sublen = $strlen;
            $tmpstr = '';
            for ($i = 0; $i < $strlen; $i++) {
                if ($i >= $start && $i < ($start + $sublen)) {
                    if (ord(substr($string, $i, 1)) > 129)
                        $tmpstr.= substr($string, $i, 2);
                    else
                        $tmpstr.= substr($string, $i, 1);
                }
                if (ord(substr($string, $i, 1)) > 129)
                    $i++;
            }
            if (strlen($tmpstr) < $strlen)
                $tmpstr.= $ellipsis;
            return $tmpstr;
        }
    }

    /**
     * 判断是否为指定长度内字符串
     * @param	$C_char （待检测的字符串）
     * @param	$I_len1 （目标字符串长度的下限）
     * @param	$I_len2 （目标字符串长度的上限）
     * @time	2011-10-14
     * @return	布尔值
     */
    static function funCheckLengthBetween($C_cahr, $I_len1, $I_len2 = 100) {
        $C_cahr = trim($C_cahr);
        if (strlen($C_cahr) < $I_len1)
            return false;
        if (strlen($C_cahr) > $I_len2)
            return false;
        return true;
    }

    /**
     * 判断是否为有效邮件地址
     * @param	$C_mailaddr （待检测的邮件地址）
     * @return	布尔值
     * @time 2011-10-14
     */
    static function funCheckEmailAddr($C_mailaddr) {
        if (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*$/", $C_mailaddr)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 判断是否为合法电话号码
     * @author	Arthur <ArthurXF@gmail.com>
     * @param	$C_telephone （待检测的电话号码）
     * @return	布尔值
     * @time 2011-10-14
     */
    static function funCheckTelephone($C_telephone) {
        if (!preg_match("/^[+]?[0-9]+([xX-][0-9]+)*$/", $C_telephone))
            return false;
        return true;
    }

    /**
     * 判断是否为有效网址
     * @param	$C_weburl （待检测的网址）
     * @return	布尔值
     * 备 注：无
     */
    static function funCheckWebAddr($C_weburl) {
        if (!preg_match("/^http:\/\/[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*$/", $C_weburl)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 过滤GET,POST
     * 作trim, htmlspecialchars 处理
     * @author	嬴益虎(whoneed@yeah.net)
     * @param	string	$data			需要处理的数据
     * @param	boolean	$isFilterHtml	是否需要htmlspecialchars处理	
     * @time	2011-10-12
     * @return	string
     */
    static function funFilterData($data, $isFilterHtml = true) {
        $data = trim($data);

        if ($isFilterHtml)
            $data = htmlspecialchars($data);

        return $data;
    }

    /**
     * 返回浏览器类型和版本
     * @return  array
     */
    static function funBrowserVer() {
        $browsers = array(
            ".*opera[ /]([0-9.]{1,10})" => "opera",
            ".compatible; MSIE[ /]([0-9.]{1,10}).*" => "ie",
            ".*Firefox/([0-9.+]{1,10})" => "firefox",
            ".Version/([0-9.+]{1,10})" => "Safari",
            ".Chrome/([0-9.+]{1,10})" => "Chrome",
        );
        if (empty($_SERVER["HTTP_USER_AGENT"]))
            return '';
        $browser_info = array();
        foreach ($browsers as $match => $browser_name) {
            if (preg_match('#' . $match . '#i', $_SERVER["HTTP_USER_AGENT"], $match)) {
                $browser_info[] = $browser_name;
                $browser_info[] = $match[1];
                $browser_info[] = $browser_name . '.' . $match[1];
            }
        }
        return $browser_info;
    }

    /**
     * 得到指定日期的星期几 周一到周日 分别返回 1 - 7
     * @author	嬴益虎(whoneed@yeah.net)
     * @param	int	$intTime	需要处理的时间，如果为空，默认为当前时间
     * @time	2011-11-10
     * @return	int
     */
    static function funGWDay($intTime = 0) {
        if (!$intTime)
            $intTime = time();
        $current = 0;
        $week = '';

        $week = date('D', $intTime);
        switch ($week) {
            case 'Mon':
                $current = 1;
                break;
            case 'Tue':
                $current = 2;
                break;
            case 'Wed':
                $current = 3;
                break;
            case 'Thu':
                $current = 4;
                break;
            case 'Fri':
                $current = 5;
                break;
            case 'Sat':
                $current = 6;
                break;
            case 'Sun':
                $current = 7;
                break;
        }

        return $current;
    }

    /**
     * 缓存函数
     * 用于前端缓存，和后端清除缓存
     * @author	嬴益虎(whoneed@yeah.net)
     * @param	string	$strKey		缓存的key
     * @param	object	$objValue	缓存的值
     * @param	boolean	$isClear	是否需要清除缓存; 默认不清除
     * @param	int		$intCTime	缓存时间
     * @time	2011-11-10
     * @return	int
     */
    static function funGCCache($strKey = '', $objValue = -1, $isClear = false, $intCTime = 300) {

        $objReturn = false;

        // 清除缓存
        if ($isClear) {
            Yii::app()->cache->flush();
        }
        // 读取设置缓存
        else {
            if (!empty($strKey)) {
                // 设置缓存
                if ($objValue != -1) {
                    $value = serialize($objValue);
                    Yii::app()->cache->set($strKey, $value, $intCTime);
                }
                // 读取缓存
                else {
                    $value = Yii::app()->cache->get($strKey);
                    if ($value)
                        $objReturn = unserialize($value);
                }
            }
        }

        return $objReturn;
    }

    static function funGSJpass($length = 8) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $password;
    }

    /**
     * 导出excel
     * @param	string	strName  excel名称
     * @param	array	arrExcelTitle 标头文字说明
     * @param	array	arrExcelCont  数据列表
     * @param	array	arrExcelTotalCont  底部统计
     * @time	2011-12-09
     */
    static function ExcelExport($strName, $arrExcelTitle, $arrExcelCont, $arrExcelTotalCont = null) {
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Pragma: no-cache');
        header('Content-type: application/xls');

        $filename = $strName . ".xls";
        $filename = mb_convert_encoding($filename, 'gb2312', 'utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // 标题处理
        $_strTitle = '';
        foreach ($arrExcelTitle as $k => $v) {
            $_strTitle .= mb_convert_encoding($v . "\t", 'gb2312', 'utf-8');
        }
        print_r($_strTitle);

        // 内容处理
        echo "\t\n";
        foreach ($arrExcelCont as $k => $v) {
            foreach ($v as $k2 => $v2) {
                print_r(mb_convert_encoding($v[$k2] . "\t", 'gb2312', 'utf-8'));
            }
            echo "\t\n";
        }

        // 底部总计
        // 修改于2011-12-14
        if ($arrExcelTotalCont) {
            echo "\t\n";
            foreach ($arrExcelTotalCont as $k => $v) {
                foreach ($v as $k2 => $v2) {
                    print_r(mb_convert_encoding($v[$k2] . "\t", 'gb2312', 'utf-8'));
                }
                echo "\t\n";
            }
        }
    }

    // 输出jSON并且推出
    static function funEchoJSON($arrData = array()) {
        echo json_encode($arrData);
        exit;
    }

    // 输出AJAX请求的json
    static function funEchoJSON_Ajax($message, $statusCode = 200, $navTabId = '', $rel = '', $callbackType = '', $forwardUrl = '', $forwardTitle = '', $attributes=null) {
        $arrData = array(
            'statusCode' => $statusCode,
            'message' => $message,
            'navTabId' => $navTabId,
            'rel' => $rel,
            'callbackType' => $callbackType,
            'forwardUrl' => $forwardUrl,
            'forwardTitle' => $forwardTitle,
        );
        if ($attributes) {
            $arrData['attributes'] = $attributes;
        }
        echo json_encode($arrData, JSON_HEX_AMP|JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * 限制脚本运行个数
     * @param       string  $strName                需要检测的脚本名称
     * @param       int             $intNum                 默认不超过的线程数1个(不包含)
     * @return  boolean                                     true:可以继续   false:已经达到最大脚本限制
     */
    public static function funCheckThread($strName = '', $intNum = 1) {
        set_time_limit(0);

        // 定义将要运行的语句
        $strExec = '';
        $isReturn = true;

        $strExec = "ps -ef | grep php | grep '{$strName}' | grep -v grep | grep -v '>>' | wc -l";
        $count = exec($strExec);
        echo "process count:" . ($count - 1) . "\tpid:" . getmypid() . "\n";
        if ($count > $intNum) {
            $isReturn = false;
        }

        return $isReturn;
    }
    
    /**
     * 限制脚本运行个数WINDOWS
     * @param       string  $strName                需要检测的脚本名称
     * @return  boolean                             true:存在   false:不存在
     */
    public static function funCheckThreadWin($strName = '') {
        
        $strReturn = Yii::app()->cache->get($strName);
        
        if($strReturn){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * WINDOWS脚本运行在执行标记
     * @param       string  $strName                需要检测的脚本名称
     * @return  boolean                             true:存在   false:不存在
     */
    public static function funThreadWinStart($strName = '') {
        echo date("Y-m-d H:i:s",time())."Execute $strName\r\n";
        return Yii::app()->cache->set($strName, 1, 99999999);
    }
    
    /**
     * WINDOWS脚本运行结束标记
     * @param       string  $strName                需要检测的脚本名称
     * @return  boolean                             true:存在   false:不存在
     */
    public static function funThreadWinEnd($strName = '') {
        echo date("Y-m-d H:i:s",time())."End execute $strName\r\n";
        return Yii::app()->cache->set($strName, 0, 99999999);
    }

    public static function jiangPinArr() {
        $prizeArr = array(
            '0' => array('id' => 2002, 'min' => 141, 'max' => 154, 'prize' => '小银票', 'v' => 25),
            '2' => array('id' => 2001, 'min' => 155, 'max' => 164, 'prize' => '大银票', 'v' => 25),
            '3' => array('id' => 2003, 'min' => 249, 'max' => 258, 'prize' => '10元宝', 'v' => 50),
            '4' => array('id' => 2004, 'min' => 259, 'max' => 267, 'prize' => '50元宝', 'v' => 50),
            '5' => array('id' => 2005, 'min' => 268, 'max' => 275, 'prize' => '100元宝', 'v' => 50),
            '6' => array('id' => 2006, 'min' => 200, 'max' => 205, 'prize' => '大体力丹', 'v' => 110),
            '7' => array('id' => 2007, 'min' => 206, 'max' => 219, 'prize' => '小体力丹', 'v' => 110),
            '8' => array('id' => 2008, 'min' => 224, 'max' => 228, 'prize' => '美女用品 - 鲜花*99', 'v' => 50),
            '9' => array('id' => 2009, 'min' => 229, 'max' => 235, 'prize' => '美女用品 - 红烛*1', 'v' => 50),
            '10' => array('id' => 2010, 'min' => 236, 'max' => 241, 'prize' => '美女用品 - 生龙活虎丹*1', 'v' => 50),
            '11' => array('id' => 2011, 'min' => 242, 'max' => 247, 'prize' => '美女用品 - 胭脂*1', 'v' => 50),
            '12' => array('id' => 2012, 'min' => 113, 'max' => 126, 'prize' => '四星秘典', 'v' => 40),
            '13' => array('id' => 2013, 'min' => 127, 'max' => 135, 'prize' => '五星秘典', 'v' => 40),
            '14' => array('id' => 2014, 'min' => 277, 'max' => 290, 'prize' => '3星随机卡牌 - 英雄卡', 'v' => 100),
            '15' => array('id' => 2015, 'min' => 291, 'max' => 303, 'prize' => '3星随机卡牌 - 装备卡', 'v' => 90),
            '18' => array('id' => 2016, 'min' => 305, 'max' => 318, 'prize' => '4星随机卡牌 - 英雄卡', 'v' => 15),
            '19' => array('id' => 2017, 'min' => 319, 'max' => 331, 'prize' => '4星随机卡牌 - 装备卡', 'v' => 15),
            '22' => array('id' => 2018, 'min' => 333, 'max' => 346, 'prize' => '5星随机卡牌 - 英雄卡', 'v' => 3),
            '23' => array('id' => 2019, 'min' => 347, 'max' => 359, 'prize' => '5星随机卡牌 - 装备卡', 'v' => 2),
            '26' => array('id' => 2020, 'min' => 1, 'max' => 9, 'prize' => '5星装备魂石 - 武器', 'v' => 4),
            '27' => array('id' => 2021, 'min' => 10, 'max' => 18, 'prize' => '5星装备魂石 - 防具', 'v' => 3),
            '28' => array('id' => 2022, 'min' => 19, 'max' => 27, 'prize' => '5星装备魂石 - 灵宠', 'v' => 3),
            '29' => array('id' => 2023, 'min' => 85, 'max' => 92, 'prize' => '5星英雄魂石 - 魏国', 'v' => 4),
            '30' => array('id' => 2024, 'min' => 93, 'max' => 99, 'prize' => '5星英雄魂石 - 吴国', 'v' => 4),
            '31' => array('id' => 2025, 'min' => 100, 'max' => 105, 'prize' => '5星英雄魂石 - 蜀国', 'v' => 4),
            '32' => array('id' => 2026, 'min' => 106, 'max' => 111, 'prize' => '5星英雄魂石 - 群雄', 'v' => 3),
            '33' => array('id' => 33, 'min' => 57, 'max' => 63, 'prize' => '2155.com优惠券10元：5OQ97GJ7BLRP', 'v' => 20),
            '34' => array('id' => 34, 'min' => 64, 'max' => 70, 'prize' => '2155.com优惠券20元：GW7SM6UTE7H7', 'v' => 10),
            '35' => array('id' => 35, 'min' => 71, 'max' => 77, 'prize' => '2155.com优惠券30元：X27FZK4DXPWA', 'v' => 10),
            '36' => array('id' => 36, 'min' => 78, 'max' => 83, 'prize' => '2155.com优惠券50元：280QXCVE2T4J', 'v' => 10),
        );
        return $prizeArr;
    }

    public static function getPrize() {
        $prizeArr = array(
            '0' => array('id' => 2002, 'prize' => '小银票'),
            '2' => array('id' => 2001, 'prize' => '大银票'),
            '3' => array('id' => 2003, 'prize' => '10元宝'),
            '4' => array('id' => 2004, 'prize' => '50元宝'),
            '5' => array('id' => 2005, 'prize' => '100元宝'),
            '6' => array('id' => 2006, 'prize' => '大体力丹'),
            '7' => array('id' => 2007, 'prize' => '小体力丹'),
            '8' => array('id' => 2008, 'prize' => '美女用品 - 鲜花'),
            '9' => array('id' => 2009, 'prize' => '美女用品 - 红烛'),
            '10' => array('id' => 2010, 'prize' => '美女用品 - 生龙活虎丹'),
            '11' => array('id' => 2011, 'prize' => '美女用品 - 胭脂'),
            '12' => array('id' => 2012, 'prize' => '四星秘典'),
            '13' => array('id' => 2013, 'prize' => '五星秘典'),
            '14' => array('id' => 2014, 'prize' => '3星随机卡牌 - 英雄卡'),
            '15' => array('id' => 2015, 'prize' => '3星随机卡牌 - 装备卡'),
            '18' => array('id' => 2016, 'prize' => '4星随机卡牌 - 英雄卡'),
            '19' => array('id' => 2017, 'prize' => '4星随机卡牌 - 装备卡'),
            '22' => array('id' => 2018, 'prize' => '5星随机卡牌 - 英雄卡'),
            '23' => array('id' => 2019, 'prize' => '5星随机卡牌 - 装备卡'),
            '26' => array('id' => 2020, 'prize' => '5星装备魂石 - 武器'),
            '27' => array('id' => 2021, 'prize' => '5星装备魂石 - 防具'),
            '28' => array('id' => 2022, 'prize' => '5星装备魂石 - 灵宠'),
            '29' => array('id' => 2023, 'prize' => '5星英雄魂石 - 魏国'),
            '30' => array('id' => 2024, 'prize' => '5星英雄魂石 - 吴国'),
            '31' => array('id' => 2025, 'prize' => '5星英雄魂石 - 蜀国'),
            '32' => array('id' => 2026, 'prize' => '5星英雄魂石 - 群雄'),
            '33' => array('id' => 33, 'prize' => '<a href="http://www.2155.com/" target="_blank">2155.com</a>优惠券10元：5OQ97GJ7BLRP'),
            '34' => array('id' => 34, 'prize' => '<a href="http://www.2155.com/" target="_blank">2155.com</a>优惠券20元：GW7SM6UTE7H7'),
            '35' => array('id' => 35, 'prize' => '<a href="http://www.2155.com/" target="_blank">2155.com</a>优惠券30元：X27FZK4DXPWA'),
            '36' => array('id' => 36, 'prize' => '<a href="http://www.2155.com/" target="_blank">2155.com</a>优惠券50元：280QXCVE2T4J'),
            '101' => array('id' => 2050, 'prize' => '抽奖5次 - 铜宝箱+钥匙,各 10个'),
            '102' => array('id' => 2051, 'prize' => '抽奖10次 - 银宝箱+钥匙.各 10个'),
            '103' => array('id' => 2052, 'prize' => '抽奖25次 - 金宝箱+钥匙,各 10个'),
            '104' => array('id' => 2053, 'prize' => '抽奖50次 - 鲜花*100,红烛*10,胭脂*5,生龙活虎丹*1'),
            '105' => array('id' => 2054, 'prize' => '抽奖100次 - 金宝箱+钥匙,各50个'),
            '106' => array('id' => 2055, 'prize' => '抽奖150次 - 5星武将随机卡1个,金宝箱+钥匙,各50个'),
            '107' => array('id' => 2054, 'prize' => '抽奖200次 - 金宝箱+钥匙,各50个'),
            '108' => array('id' => 2056, 'prize' => '抽奖250次 - 5星武器随机卡1个,金宝箱+钥匙,各50个'),
            '109' => array('id' => 2054, 'prize' => '抽奖300次 - 金宝箱+钥匙,各50个'),
            '110' => array('id' => 2057, 'prize' => '抽奖400次 - 5星灵宠随机卡1个,金宝箱+钥匙,各50个')
        );
        return $prizeArr;
    }
    
    public static function getMailaddress() {
        $mailAccepters = array(
            /*'张成' => 'zhangcheng@yeahgame.com.cn',
            '胡剑' => 'hujian669378820@yeahgame.com.cn',
            '李鑫云' => '42073613@qq.com',
			'夏明艳'=>'821305478@qq.com',*/
        );
        return $mailAccepters;
    }

    public static function getRand($proArr) {
        $result = '';

        //概率数组的总概率精度 
        $proSum = array_sum($proArr);

        //概率数组循环 
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset($proArr);

        return $result;
    }

    public static function sendNotice($serverId, $content) {
        $apiUrl = Yii::app()->params['mmhgz_interface'] . "&method=gmmsg&server_id=$serverId";

        $post_data = array(
            'extra' => $content,
        );

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);
        $result = json_decode($result, TRUE);
        return $result;
    }

    public static function sendOption($method, $serverId, $param1 = '', $param2 = '', $extra = '') {
        $apiUrl = Yii::app()->params['mmhgz_interface'] . "&method=$method&server_id=$serverId";

        $post_data = array(
            'param1' => $param1,
            'param2' => $param2,
            'extra' => $extra,
        );

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 600 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);

        $result = json_decode($result, TRUE);
        return $result;
    }

    //获取角色具体信息
    public static function sendRole($method, $serverId, $param1 = '') {
        $apiUrl = Yii::app()->params['mmhgz_interface'] . "&method=$method&server_id=$serverId";


        $post_data = array(
            'param1' => $param1,
        );

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);
        $result = json_decode($result, TRUE);
        return $result;
    }

    //发送邮件
    public static function sendMail($method, $serverId, $content, $param1 = '') {
        $apiUrl = Yii::app()->params['mmhgz_interface'] . "&method=$method&server_id=$serverId";

        $post_data = array();
        if ($method == 'mail2all') {
            $post_data['extra'] = $content;
        } else if ($method == 'mail2name') {
            $post_data['param1'] = $param1;
            $post_data['extra'] = $content;
        }

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);
        $result = json_decode($result, TRUE);
        return $result;
    }
    
     //玩家
    public static function sendUser($method, $serverId, $content, $param2 = '') {
        $apiUrl = Yii::app()->params['mmhgz_interface'] . "&method=$method&server_id=$serverId";

        $post_data = array();
        $arrMethod1 = array('kickoutuser','forcekickoutuser','unfreezeuser','unfreezespeech');
        $arrMethod2 = array('freezeuser','freezespeech');
        if (in_array($method,$arrMethod1)) {
            $post_data['extra'] = $content;
        } else if (in_array($method,$arrMethod2)) {
            $post_data['param2'] = $param2;
            $post_data['extra'] = $content;
        }else{
        		return;
        }

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);
        $result = json_decode($result, TRUE);
        return $result;
    }

    //写文件
    public static function writeFile($url, $content, $mode = 'a+') {
        $fp = fopen($url, $mode) or die('文件不存在！');
        fwrite($fp, "\xEF\xBB\xBF");
        fwrite($fp, $content) or die('无法写入！');
        fclose($fp);
    }

    //数组key
    public static function array_item($arr, $key) {
        if (array_key_exists($key, $arr)) {
            return($arr[$key]);
        }
        return('');
    }

    //数据处理
    public static function dumpData($info, $savefile = false, $count = false) {
        $code = MyFunction::array_item($info, 'code');
        $data = MyFunction::array_item($info, 'data');
        if ($code != 0) {
            if ($savefile) {
                return $data;
            } else {
                echo "<h3>$data</h3>";
                return;
            }
        }

        $keys = MyFunction::array_item($data, 'keys');
        $data_list = MyFunction::array_item($data, 'data');
        $title = MyFunction::array_item($data, 'title');

        $content = "";


        if ($savefile) {
            $content .= "$title\r\n";
            $column = join(",", $keys);
            $content .= $column . "\r\n";
            foreach ($data_list as $_ele) {
                $_ele = join(",", $_ele);
                $content .= $_ele . "\r\n";
            }
            return $content;
        } else {
            echo "<h3>$title</h3>\n";
            echo "<table class=\"table\" width=\"1200\" layoutH=\"138\">\n";
            echo "<thead>\n";
            foreach ($keys as $_key) {
                echo "<th>$_key</th>\n";
            }
            echo "</thead>\n<tbody>";

            //content
            $arrData = array();
            foreach ($data_list as $_ele) {

                echo "<tr>\n";
                foreach ($_ele as $k => $_one) {
				
					echo "<td>$_one</td>\n";
                    $arrData[$k] += $_one;
				
                }
                echo "</tr>\n";
            }

            if ($count) {
                echo "<tr>\n";
                foreach ($arrData as $k => $_val) {
                    if ($k <= 1) {
                        if ($k == 0) {
                            echo "<td>小计：</td>\n";
                        } else {
                            echo "<td></td>\n";
                        }
                    } else {
                        echo "<td>$_val</td>\n";
                    }
                }
                echo "</tr>\n";
            }

            echo "</tbody></table>\n";
        }
    }

    /**
     * 按某一key进行排序
     * 
     * @param $array 二维数组
     */
    public static function arraySort($array, $sortKey, $reverse = false) {
        $return = array();
        $sortArray = array();
        foreach ($array as $key => &$arr) {
            $sortArray[$key] = $arr[$sortKey];
        }
        unset($arr);

        if ($reverse) {
            arsort($sortArray);
        } else {
            asort($sortArray);
        }
        foreach ($sortArray as $key => &$arr) {
            $return[$key] = $array[$key];
        }

        return $return;
    }
    
    public static function mkdirs($path, $mode = 0777) {
        if (strpos($path,"\\") !== false){ $path = str_replace("\\",'/',$path); }
        if (strpos($path,"//") !== false){ $path = str_replace("//",'/',$path); }
        $dirs = explode('/',$path);
        $pos = strrpos($path, ".");
        if ($pos === false) { // note: three equal signs
            // not found, means path ends in a dir not file
            $subamount=0;
        }
        else {
            $subamount=1;
        }
        for ($c=0;$c < count($dirs) - $subamount; $c++) {
            $thispath="";
            for ($cc=0; $cc <= $c; $cc++) {
                $thispath.=$dirs[$cc].'/';
            }
            if (!file_exists($thispath)) {
                if (!@mkdir($thispath,$mode))return false;
            }
        }
        return true;
    }

    public static function compareVersion($version1, $version2) {
        $arr1 = explode('.', $version1);
        $arr2 = explode('.', $version2);
        $num1 = count($arr1);
        $num2 = count($arr2);
        $maxNum = $num1 > $num2 ? $num1 : $num2;
        $v1 = 0;
        $v2 = 0;

        for ($i = 0; $i < $maxNum; $i++) {
            if ($i < $num1) {
                $v1 = intval($arr1[$i]);
            }
            else {
                $v1 = 0;
            }
            if ($i < $num2) {
                $v2 = intval($arr2[$i]);
            }
            else {
                $v2 = 0;
            }

            if ($v1 < $v2) {
                return -1;
            }
            else if ($v1 > $v2) {
                return 1;
            }
        }

        return 0;
    }
}

?>

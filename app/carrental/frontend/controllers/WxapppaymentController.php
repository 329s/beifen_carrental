<?php
namespace frontend\controllers;
use Yii;
/**
* 微信小程序支付回调接口
*/
class WxapppaymentController extends \yii\web\Controller
{

	public function behaviors()
    {
        return [
        'csrf' => [
                'class' => \common\helpers\NoCsrfBehavior::className(),
                'controller' => $this,
                'actions' => [
                    'order_alipay',
                    'pc_alipay_gateway',
                    'payparam',
                    'notify_url',
                    'get_openid'
                ],
            ]
        ];
    }

    // 获取openid
    public function actionGet_openid(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
        do{
            $params = \Yii::$app->request->post();
            if (!isset($params['js_code'])) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;//4001
                $arrData['desc']   = \Yii::t('locale', 'Missing required parameter!');//缺少必要参数
                break;
            }
            $appid  = \Yii::$app->params['wxappconfig']['appid'];
            $secret = \Yii::$app->params['AppSecret'];
            $openidurl ='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&grant_type=authorization_code&js_code='.$params['js_code'];

            $data = file_get_contents($openidurl);
            $arr = json_decode($data);

            if($arr->errcode == '40029'){
                $arrData['result'] = $arr->errcode;
                $arrData['desc'] = $arr->errmsg;
                break;
            }else{
                $arrData['data'] = $arr;
            }
        }while (0);
        echo json_encode($arrData);
    }

    /*
    {
"result": 0,
"desc": "Success.",
-"UrlInfo": {
"return_code": "SUCCESS",
"return_msg": "OK",
"appid": "wx6257364ee334cd75",
"mch_id": "1395342202",
"nonce_str": "Sgma4Hhlb4oiuV16",
"sign": "1C885B5BAF96079381854A42FC3FFCD3",
"result_code": "SUCCESS",
"prepay_id": "wx3114285590435598834dbb502851401000",
"trade_type": "APP"
}
}*/

    public function actionPayparam(){
    	$arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
    	do{
    		$params = \Yii::$app->request->post();
            $requiredFields = ['serial', 'session_id','openid'];
            foreach ($requiredFields as $k) {
                if (!isset($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;//4001
                    $arrData['desc']   = \Yii::t('locale', 'Missing required parameter!');//缺少必要参数
                    break;
                }
            }

            //登陆的用户信息
            $seid_data = \Yii::$app->session->readSession($params['session_id']);
            if(!$seid_data){
                $useid=0;
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                $arrData['desc']   = \Yii::t('locale', 'Login required, current is guest user.');//当前是游客模式，请先登录。
                break;//sjj，先注释调，上线后取消注释
            }else{
                $session_data    = \frontend\components\CommonModule::unserialize_php($seid_data);
                if(empty($session_data['__id'])){
                    $useid=0;
                    $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                    $arrData['desc']   = \Yii::t('locale', 'Login required, current is guest user.');//当前是游客模式，请先登录。
                    break;
                }else{
                    $useid           = $session_data['__id'];
                }
            }

            $config              = \Yii::$app->params['wxappconfig'];

            $cdb                 = \common\models\Pro_vehicle_order::find(true);
            $cdb->where(['serial'=> $params['serial']]);
            $objOrder            = $cdb->one();

            if($objOrder->pay_source > 0){
            	$arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc']   = '该订单已付款';
                break;
            }

            $config['nonce_str'] 	= \common\helpers\Utils::randomStr(32);//随机字符串
            $config['out_trade_no'] = $params['serial'];//商户订单号
            $config['openid'] = $params['openid'];//商户订单号
            $config['total_fee']    = intval(($objOrder->total_amount)*100);//订单金额
            $config['trade_type']   = 'JSAPI';//交易类型
            $config['body']         = '小程序支付';//交易类型
            $config['spbill_create_ip'] = \common\helpers\Utils::getIP();//终端ip
            $config['sign'] = $this->getSign($config);

            $post_xm = $this->ToXml($config);
            $wxurl  = $this->getUrl();
            $UrlInfo = \frontend\components\CommonModule::getUrlInfo($wxurl,$post_xm);
            $arrData['UrlInfo']        = $this->FromXml($UrlInfo);
            // $arrData['config']        = $config;
    	}while (0);
    	echo json_encode($arrData);
    }

    public function getUrl($value='')
    {
    	return 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    }

    public function getSign($urlObj='')
    {
    	$buff = "";
    	ksort($urlObj);
		foreach ($urlObj as $k => $v)
		{
			if($k != "sign"){
				$buff .= $k . "=" . $v . "&";
			}
		}

		$buff = trim($buff, "&");
		$key=\Yii::$app->params['payment.weixin.appkey'];
		$buff = $buff.'&key='.$key;
		$buff = strtoupper(md5($buff));
		return $buff;
    }

    public function ToXml($data)
	{
		if(!is_array($data) || count($data) <= 0)
		{
    		throw new WxPayException("数组数据异常！");
    	}

    	$xml = "<xml>";
    	foreach ($data as $key=>$val)
    	{
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml;
	}

	/**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
	public function FromXml($xml)
	{
		if(!$xml){
			throw new WxPayException("xml数据异常！");
		}
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $arr;
	}



    public function actionNotify_url($value='')
    {
        $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        $post     = $_REQUEST;

        if($post  == null){
            $post = file_get_contents("php://input");
        }

        if($post  == null){
            $post = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        }

        if (empty($post) || $post == null || $post == '') {
            echo $str;
            exit('Notify 非法回调');
        }

        $post_data = $this->FromXml($post);

        $date=date('Y-m-d H:i:s',time());
        $b=json_encode($post_data);
        file_put_contents('weixinapp.txt',"$date'->'$b'\n",FILE_APPEND);
        // exit();
        /*$post_data = array(
            "appid"=>"wx6257364ee334cd75",
            "bank_type"=>"CFT",
            "cash_fee"=>"1",
            "fee_type"=>"CNY",
            "is_subscribe"=>"N",
            "mch_id"=>"1395342202",
            "nonce_str"=>"sTAb27ju4MilKFIm0Ut6ONDVSRG8vyCQ",
            "openid"=>"oHTLE5FtFOJm2BtaNFdeTm1rzdxA",
            "out_trade_no"=>"200050026473",
            "result_code"=>"SUCCESS",
            "return_code"=>"SUCCESS",
            "sign"=>"646201BD0D80E5C7B988DD415826633B",
            "time_end"=>"20181101154034",
            "total_fee"=>"1",
            "trade_type"=>"JSAPI",
            "transaction_id"=>"4200000197201811012996358318"
        );*/

        // {"appid":"wx6257364ee334cd75","bank_type":"CMB_CREDIT","cash_fee":"27900","fee_type":"CNY","is_subscribe":"N","mch_id":"1395342202","nonce_str":"rijuvnSD8CGIVsU0lEqA35dOz4NTZk1X","openid":"oHTLE5PsHef7P70sO7P_h7fyWSKY","out_trade_no":"200079028505","result_code":"SUCCESS","return_code":"SUCCESS","sign":"4534E2CF82A50B28EF68F904B0F87493","time_end":"20181216113552","total_fee":"27900","trade_type":"JSAPI","transaction_id":"4200000204201812169298949833"}

        $post_sign = $post_data['sign'];

        //重新生成签名
        $newSign = $this->getSign($post_data);
        if($newSign == $post_sign){
            // 数据处理
            $out_trade_no = isset($post_data['out_trade_no']) && !empty($post_data['out_trade_no']) ? $post_data['out_trade_no'] : 0;
            $cdb = \common\models\Pro_vehicle_order::find();
            $cdb->where(['serial' => $out_trade_no]);
            $objOrder = $cdb->one();
            if($objOrder->pay_source > 0 && $objOrder->paid_amount > 0){
                echo $str;
                exit();
            }else{
                // 数据更新
                $objFormData = new \common\models\Form_pro_vehicle_order();
                $time=time();
                if($objOrder && $objOrder->pay_source == 0){
                    $objFormData = new \backend\models\Form_pro_vehicle_order_price_detail();
                    $objModel    = new \common\models\Pro_vehicle_order_price_detail();
                    $objModel->summary_amount  = $post_data['total_fee']/100;
                    $objModel->price_rent = $objOrder->price_rent;
                    $objModel->price_optional_service = $objOrder->price_optional_service;
                    $objModel->belong_office_id = $objOrder->belong_office_id;
                    $objModel->time = $time;
                    $objModel->pay_source = 6;
                    $objModel->belong_office_id = $objOrder->belong_office_id;
                    $objModel->order_id = $objOrder->id;
                    $objModel->serial = $objOrder->id.'-2-'.$time;
                    $objModel->type = 2;
                    $objModel->status = '0';
                    // echo "<pre>";
                    // print_r($objModel);
                    // echo "</pre>";
                    $objModel->save();


                    //Pro_purchase_order表支付记录
                    if ($objModel->summary_amount && $objModel->pay_source) {
                        $objOrder->paid_amount += $objModel->summary_amount;
                        $objOrder->pay_source = $objModel->pay_source;

                        $objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehicleOrder($objOrder, $objModel->summary_amount, $objModel->belong_office_id, \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT, $objModel->time);
                        // echo "<pre>";
                        // print_r($objPurchaseOrder);
                        // echo "</pre>";die;
                        $objPurchaseOrder->save();
                    }


                    $objOrder->save();

                    echo $str;
                    exit();
                }
            }

        }

        /*$arrData['post_sign'] = $post_sign;
        $arrData['newSign'] = $newSign;
        echo json_encode($arrData);*/
    }
}

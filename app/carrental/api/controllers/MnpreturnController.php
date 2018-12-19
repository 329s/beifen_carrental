<?php
namespace frontend\controllers;

/**
* 快钱第三方支付交易后实时通知
*/
class MnpreturnController extends \common\helpers\AuthorityController
{
	public function behaviors(){
		return [];
	}
	public function init(){
	    //去掉Yii2.0 csrf验证
        $this->enableCsrfValidation = false;
	}

	public function actionReturn(){

		/*$arr = \Yii::$app->request->post();
        $date=date('Y-m-d H:i:s',time());
        $b=json_encode($arr);
        file_put_contents('mnp.txt',"$date:$b'\n",FILE_APPEND);*/
		$orgTxnType  		= $_POST['orgTxnType'];			//交易类型:PUR 消费交易; INP 分期消费交易;PRE 预授权交易;CFM 预授权完成交易;VTX 撤销交易;RFD 退货交易;00201 冲正交易;
		$orgExternalTraceNo = $_POST['orgExternalTraceNo']; //原始外部跟踪号
		$processFlag 		= $_POST['processFlag'];        //处理结果 0：成功 1：失败
		$txnType 			= $_POST['txnType'];            //交易类型
		$amt				= $_POST['amt'];				//交易金额
		$externalTraceNo 	= $_POST['externalTraceNo'];	//外部跟踪编号，一般为商家订单号
		$terminalOperId 	= $_POST['terminalOperId'];		//操作员编号

		$terminalId 	    = $_POST['terminalId'];		//终端编号
		$merchantId 	    = $_POST['merchantId'];		//商户编号

		$authCode	 		= $_POST['authCode'];			//授权码
		$RRN 				= $_POST['RRN'];				//系统参考号
		$txnTime 			= $_POST['txnTime'];			//交易时间 格式：yyyyMMdd HHmmss
		$shortPAN 			= $_POST['shortPAN'];           //缩略卡号
		$responseCode 		= $_POST['responseCode'];       //交易返回码
		$responseMessage 	= $_POST['responseMessage'];    //交易返回信息
		$cardType 			= $_POST['cardType'];			//卡类型
		$issuerId 			= $_POST['issuerId'];			//发卡机构
		$issuerIdView 		= $_POST['issuerIdView'];		//发卡机构名称
		$signature 			= $_POST['signature'];			//签名

		$signature=base64_decode($signature);

		$data=$processFlag.$txnType.$orgTxnType.$amt.$externalTraceNo.$terminalOperId.$authCode.$RRN.$txnTime.$shortPAN.$responseCode.$cardType.$issuerId;

        $path = $_SERVER['DOCUMENT_ROOT'].'/app/carrental/frontend/web/mnp/';
		$fp = fopen($path.'vposPHP.cer', "r");
		$cert = fread($fp, 8192);
		fclose($fp);
		$pubkeyid = openssl_get_publickey($cert);


		$ok = openssl_verify($data, $signature, $pubkeyid);

		$file = fopen("test.txt","w");

		if ($ok == 1) {

			fwrite($file,'ok');
			$now  = time();
			$data = array(
				'processflag'  		 => $processFlag,
				'txntype'  		 	 => $txnType,
				'orgtxntype'  		 => $orgTxnType,
				'externaltraceno'  	 => $externalTraceNo,
				'orgexternaltraceno' => $orgExternalTraceNo,
				'amt'  				 => $amt,
				'terminaloperid'  	 => $terminalOperId,
				'terminalid'    	 => $terminalId,
				'merchantid'    	 => $merchantId,
				'authcode'  		 => $authCode,
				'rrn'  				 => $RRN,
				'txntime'  			 => strtotime($txnTime),
				'shortpan'  		 => $shortPAN,
				'responsecode'  	 => $responseCode,
				'responsemessage'  	 => $responseMessage,
				'cardtype'  		 => $cardType,
				'issuerid'  		 => $issuerId,
				'issueridview'  	 => $issuerIdView,
				'signature'  		 => $signature,
				'created'  			 => $now
			);

			$objMnpPayReturn = \Yii::$app->db;
			$objMnpPayReturn->createCommand()->insert('Pro_mnp_pay_return',$data)->execute();
			if($processFlag == 0){//支付成功
				// Pro_vehicle_order_price_detail
				// 订单记录pro_vehicle_order_change_log
				// pro_purchase_order 支付记录
				// 订单改变状态pro_vehicle_order
				$cdb = \common\models\Pro_vehicle_order::find();
	            $cdb->where(['serial' => $externalTraceNo]);
	            $objOrder = $cdb->one();
	            // $objFormData = new \common\models\Form_pro_vehicle_order();
	            if($objOrder){
	                $objFormData = new \backend\models\Form_pro_vehicle_order_price_detail();
	                $objModel    = new \common\models\Pro_vehicle_order_price_detail();
		                // 判断是租金还是押金
	                	if($txnType == 'PRE'){//预授权返回
	                		// if($amt > $objOrder->paid_deposit){
		                		$objModel->summary_deposit = $amt;
			                	$objModel->deposit_pay_source = 7;//预授权
			                	$objModel->price_deposit_violation = $amt;

			                	$objOrder->paid_deposit += $objModel->summary_deposit;
			                    $objOrder->deposit_pay_source = $objModel->deposit_pay_source;
	                		// }
	                	}elseif ($txnType == 'PUR' || $txnType == 'INP') {//PUR:消费交易; INP：分期消费交易
	                		if($amt == $objOrder->total_amount){//租金
	                			$objModel->summary_amount  = $amt;
		                		$objModel->pay_source = 9;//快钱
		                		$objModel->price_rent = $amt-$objOrder->price_poundage-$objOrder->price_basic_insurance-$objOrder->price_optional_service;
				                $objModel->price_poundage = $objOrder->price_poundage;//手续费
				                $objModel->price_basic_insurance = $objOrder->price_basic_insurance;//基本服务费
				                $objModel->price_optional_service = $objOrder->price_optional_service;//基本服务费

				                $objOrder->paid_amount += $objModel->summary_amount;
		                    	$objOrder->pay_source = $objModel->pay_source;
	                		}else{//续租租金或者押金price_deposit_violation,price_deposit
	                			if($amt == $objOrder->price_deposit_violation || $amt == $objOrder->price_deposit){
	                				$objModel->summary_deposit  = $amt;
			                		$objModel->deposit_pay_source = 9;//快钱
			                		$objModel->price_deposit_violation = $amt;

					                $objOrder->paid_deposit += $objModel->summary_deposit;
			                    	$objOrder->deposit_pay_source = $objModel->deposit_pay_source;
	                			}else{//续租
	                				$objModel->summary_amount  = $amt;
			                		$objModel->pay_source = 9;//快钱
			                		$objModel->price_rent = $amt;

					                $objOrder->paid_amount += $objModel->summary_amount;
			                    	$objOrder->pay_source = $objModel->pay_source;
	                			}
	                		}
	                		/*$objModel->summary_amount  = $amt;
		                	$objModel->pay_source = 9;//快钱
		                	if($amt == $objOrder->total_amount){
			                	$objModel->price_rent = $amt-$objOrder->price_poundage-$objOrder->price_basic_insurance;
				                $objModel->price_poundage = $objOrder->price_poundage;//手续费
				                $objModel->price_basic_insurance = $objOrder->price_basic_insurance;//基本服务费
		                	}else{
		                		$objModel->price_rent = $amt;
		                	}

		                	$objOrder->paid_amount += $objModel->summary_amount;
		                    $objOrder->pay_source = $objModel->pay_source;*/

	                	}


		                $objFormData->load($objModel);

		                // $objModel->price_optional_service = $objOrder->price_optional_service;
		                $objModel->belong_office_id = $objOrder->belong_office_id;
		                $objModel->time = $now;
		                $objModel->status = 0;
		                $objModel->serial = $objOrder->id.'-2-'.$now;
		                $objModel->type = 2;//1应缴  2已缴
		                $objModel->order_id = $objOrder->id;
		                $objModel->edit_user_id = $objOrder->edit_user_id;


		            	$objModel->save();
		            //Pro_purchase_order表支付记录
		                if ($objModel->summary_amount && $objModel->pay_source) {//租金
		                    $objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehicleOrderMNP($objOrder, $objModel->summary_amount, $objModel->belong_office_id, \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT, $objModel->time);
		                    $objPurchaseOrder->save();
		                }elseif ($objModel->summary_deposit && $objModel->deposit_pay_source) {//押金
		                	$objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehicleOrderMNP($objOrder, $objModel->summary_deposit, $objModel->belong_office_id, \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT, $objModel->time);
		                    $objPurchaseOrder->save();
		                }


		                $objOrder->save();


	            }
			}
			echo '0';

		} elseif ($ok == 0) {

			fwrite($file,'no');

		} else { 

			fwrite($file,'never');

		} 


	}

	public function actionTest(){
		$txnType = 'PUR';
		$amt ='530';
		$now = time();
		$cdb = \common\models\Pro_vehicle_order::find();
        $cdb->where(['serial' => '130050016760']);
        $objOrder = $cdb->one();
        // $objFormData = new \common\models\Form_pro_vehicle_order();
        if($objOrder){
            $objFormData = new \backend\models\Form_pro_vehicle_order_price_detail();
            $objModel    = new \common\models\Pro_vehicle_order_price_detail();
                // 判断是租金还是押金
            	if($txnType == 'PRE'){//预授权返回
            		// if($amt > $objOrder->paid_deposit){
            			echo "1";
                		$objModel->summary_deposit = $amt;
	                	$objModel->deposit_pay_source = 7;//预授权
	                	$objModel->price_deposit_violation = $amt;

	                	$objOrder->paid_deposit += $objModel->summary_deposit;
	                    $objOrder->deposit_pay_source = $objModel->deposit_pay_source;
            		// }
            	}elseif ($txnType == 'PUR' || $txnType == 'INP') {//PUR:消费交易; INP：分期消费交易
            		if($amt == $objOrder->total_amount){//租金
            			echo "2";
            			$objModel->summary_amount  = $amt;
                		$objModel->pay_source = 9;//快钱
                		$objModel->price_rent = $amt-$objOrder->price_poundage-$objOrder->price_basic_insurance;
		                $objModel->price_poundage = $objOrder->price_poundage;//手续费
		                $objModel->price_basic_insurance = $objOrder->price_basic_insurance;//基本服务费

		                $objOrder->paid_amount += $objModel->summary_amount;
                    	$objOrder->pay_source = $objModel->pay_source;
            		}else{//续租租金或者押金price_deposit_violation,price_deposit
            			if($amt == $objOrder->price_deposit_violation || $amt == $objOrder->price_deposit){
            				echo "3";
            				$objModel->summary_deposit  = $amt;
	                		$objModel->deposit_pay_source = 9;//快钱
	                		$objModel->price_deposit_violation = $amt;

			                $objOrder->paid_deposit += $objModel->summary_deposit;
	                    	$objOrder->deposit_pay_source = $objModel->deposit_pay_source;
            			}else{//续租
            				echo "4";
            				$objModel->summary_amount  = $amt;
	                		$objModel->pay_source = 9;//快钱
	                		$objModel->price_rent = $amt;

			                $objOrder->paid_amount += $objModel->summary_amount;
	                    	$objOrder->pay_source = $objModel->pay_source;
            			}
            		}
            		/*$objModel->summary_amount  = $amt;
                	$objModel->pay_source = 9;//快钱
                	if($amt == $objOrder->total_amount){
	                	$objModel->price_rent = $amt-$objOrder->price_poundage-$objOrder->price_basic_insurance;
		                $objModel->price_poundage = $objOrder->price_poundage;//手续费
		                $objModel->price_basic_insurance = $objOrder->price_basic_insurance;//基本服务费
                	}else{
                		$objModel->price_rent = $amt;
                	}

                	$objOrder->paid_amount += $objModel->summary_amount;
                    $objOrder->pay_source = $objModel->pay_source;*/

            	}


                $objFormData->load($objModel);

                $objModel->price_optional_service = $objOrder->price_optional_service;
                $objModel->belong_office_id = $objOrder->belong_office_id;
                $objModel->time = $now;
                $objModel->status = 0;
                $objModel->serial = $objOrder->id.'-2-'.$now;
                $objModel->type = 2;//1应缴  2已缴
                $objModel->order_id = $objOrder->id;
                $objModel->edit_user_id = $objOrder->edit_user_id;


            	// $objModel->save();
            //Pro_purchase_order表支付记录
                if ($objModel->summary_amount && $objModel->pay_source) {//租金
                    $objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehicleOrderMNP($objOrder, $objModel->summary_amount, $objModel->belong_office_id, \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT, $objModel->time);
                    // $objPurchaseOrder->save();
                }elseif ($objModel->summary_deposit && $objModel->deposit_pay_source) {//押金
                	$objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehicleOrderMNP($objOrder, $objModel->summary_deposit, $objModel->belong_office_id, \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT, $objModel->time);
                    // $objPurchaseOrder->save();
                }


                // $objOrder->save();


        }
	}


	public function actionOqs(){
		// echo "string";die;
		$r_mermbercode=$_REQUEST['merchantId'];
		$r_orderId=$_REQUEST['orderId'];
		$r_reqTime=$_REQUEST['reqTime'];
		$r_ext1=$_REQUEST['ext1'];
		$r_ext2=$_REQUEST['ext2'];
		$r_MAC=$_REQUEST['MAC'];

		// $arr = \Yii::$app->request->request();
		$arr = array(
			'merchantId'=>$r_mermbercode,
			'orderId'=>$r_orderId,
			'reqTime'=>$r_reqTime,
			'ext1'=>$r_ext1,
			'ext2'=>$r_ext2,
			'MAC'=>$r_MAC,
		);
        $date=date('Y-m-d H:i:s',time());
        $b=json_encode($arr);
        file_put_contents('mnp.txt',"$date:$b\n",FILE_APPEND);


		$r_sign='orderId='.$r_orderId.'reqTime='.$r_reqTime;




		// 将 MAC  进行 decode 处理
		$MAC=base64_decode($r_MAC);
		// 

		$path = $_SERVER['DOCUMENT_ROOT'].'/app/carrental/frontend/web/mnp/';
		$fp = fopen($path.'vposPHP.cer', "r");
		$cert = fread($fp, 8192); 
		fclose($fp); 
		$pubkeyid = openssl_get_publickey($cert); 
		$ok = openssl_verify($r_sign, $MAC, $pubkeyid); 
		//var_dump($ok); echo"<hr/>";
		  
		// $ok=1;//接收快钱参数验签成功
		if($ok == 1){
			$r_djuge='<h1><font color=green>TRUE</font></h1>';

		$reqTime=$_REQUEST['reqTime'];
		$respTime=date('YmdHis');

		//00 –  请求成功 
		//56 –  无此记录 
		//96 –  系统异常 
		//其他  –  失败 
		$responseCode="00";

		$orderId=$_REQUEST['orderId'];
		// 订单信息
		$cdb = \common\models\Pro_vehicle_order::find(true);
        $cdb->where(['serial' => $orderId]);
        $objOrder = $cdb->one();

		$merchantId=$r_mermbercode;//商户编号
		$merchantName="yika";//商户名称
		$amt=$objOrder->total_amount-$objOrder->paid_amount;
		$ext_address='易卡租车';
		$ext_orderId=date('YmdHis');
		//$fp=fopen($reqTime.'_'.$orderId,'w');
		$xml_sign='<MessageContent><reqTime>'.$reqTime.'</reqTime><respTime>'.$respTime.'</respTime><responseCode>'.$responseCode.'</responseCode><message><orderId>'.$orderId.'</orderId><merchantId>'.$merchantId.'</merchantId><merchantName>'.$merchantName.'</merchantName><amt>'.$amt.'</amt><amt2></amt2><amt3></amt3><amt4></amt4><ext><orderId><value>'.$ext_orderId.'</value><chnName>订单号</chnName></orderId><address><value>'.$ext_address.'</value><chnName>收件人地址</chnName></address></ext></message></MessageContent>';

		// fetch private key from file and ready it

		$pem_path=$path.'81233007512000890.pem';


		$fp = fopen($pem_path, "r");
		$priv_key = fread($fp, 8192);
		fclose($fp);
		$pkeyid = openssl_get_privatekey($priv_key);

		// compute signature
		openssl_sign($xml_sign, $signMsg, $pkeyid,OPENSSL_ALGO_SHA1);

		// free the key from memory
		openssl_free_key($pkeyid);

		$MAC = base64_encode($signMsg);


		//\\

		$xml_info='<?xml version="1.0" encoding="UTF-8"?><ResponseMessage><MAC>'.$MAC.'</MAC>'.$xml_sign.'</ResponseMessage>';

		echo $xml_info;

		}else{
			$r_djuge='<h1><font color=red>FALSE</font></h1>';
		}







	}
}


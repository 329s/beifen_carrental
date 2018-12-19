<?php
namespace frontend\controllers;
require_once '../../common/vendors/alipay/pagepay/service/AlipayTradeService.php';
require_once '../../common/vendors/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';
// require_once dirname(dirname(dirname ( __FILE__ ))).'/common/vendors/alipay/pagepay/service/AlipayTradeService.php';
// require_once dirname(dirname(dirname ( __FILE__ ))).'/common/vendors/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

use Yii;
// use yii\db\ActiveRecord;
// use frontend\components\AlipayTradeService;
// class PpaymentController extends \common\helpers\AuthorityController
class PpaymentController extends \yii\web\Controller
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
                ],
            ]
        ];
    }
    // public function actionAlipay(){
    // 	$alipay = new \AlipayTradePagePayContentBuilder();
    // 	$alipay->setOutTradeNo('2001');
    // 	$alipay->setTotalAmount('0.01');
    // 	$alipay->setSubject('租车订单');
    // 	$config =  \Yii::$app->params['alipay'];
    // 	$serviceObj = new \AlipayTradeService($config);
    // 	$result = $serviceObj->pagePay($alipay,$config['return_url'],$config['notify_url']);
    // 	var_dump($result);

    // }

    /*PC点击支付*/
    public function actionOrder_alipay(){
        // $id = \Yii::$app->request->get('order_id');
        $arrResult = \frontend\components\OrderService::getOrderBySerial(\Yii::$app->request->get('order_id'));
        // $arrResult = \frontend\components\OrderService::getOrderBySerial('120050012987');
        $arrData = $arrResult[0];
        $objOrder = $arrResult[1];
        do{
            $alipay = new \AlipayTradePagePayContentBuilder();
            $alipay->setOutTradeNo($objOrder->serial);
            $alipay->setTotalAmount($objOrder->total_amount);
            // $alipay->setTotalAmount('0.01');
            $alipay->setSubject('租车订单');
            $config =  \Yii::$app->params['alipay'];
            $serviceObj = new \AlipayTradeService($config);
            $result = $serviceObj->pagePay($alipay,$config['return_url'],$config['notify_url']);
            var_dump($result);
        }while (0);
        // echo json_encode($id);
    }


    public function actionPc_alipay_gateway(){
        // echo "string";die;
        $arr = \Yii::$app->request->post();
        $date=date('Y-m-d H:i:s',time());
        $b=json_encode($arr);
        file_put_contents('alipayPost.txt',"$date'-----alipayPost>'$b'\n",FILE_APPEND);
        // exit();
        // sjj
        /*$arr = array(
                'gmt_create'=>'2018-01-09 13:49:08',
                'charset'=>'UTF-8',
                'gmt_payment'=>'2018-01-09 13:49:23',
                'notify_time'=>'2018-01-09 13:49:24',
                'subject'=>'\u79df\u8f66\u8ba2\u5355',
                'sign'=>'dNjk87cLIkPlscBLKI3e\/r9LnlZLMK1bcGPH1TnibQ8HzLNwoxWziCOYRogQ+Ih7RmD+TOxTs\/5DH7CWm\/xw5IGH6MRiYterKTV74GR02HUSsG3a\/oE+rDjk1+3BfzuXQmtbc6HHXiktBs5MjXTmOKM37z4AVy7MS\/tjpliXL\/n\/01DJPfQu4YComXZ5a6zs0E2uAoTthCo2APfsUSc8NA0SNXUCBHWH03J\/2UIr0YFpr+0KHKkOZLL+vinAxdbv26SQMHkeodZnga0v9aAZlptFH7Xe0qIR0ZoDV0sKoTcRpG3jf2ME96slaESi77J5f3Q8CKSqMtgUJr\/I\/0eVXw==',
                'buyer_id'=>'2088602008540719',
                'invoice_amount'=>'0.01',
                'version'=>'1.0',
                'notify_id'=>'7d9401d14f8d7ae94120038cddf34fdlhd',
                'fund_bill_list'=>'[{\"amount\":\"0.01\",\"fundChannel\":\"ALIPAYACCOUNT\"}]',
                'notify_type'=>'trade_status_sync',
                'out_trade_no'=>'120050013330',
                'total_amount'=>'0.01',
                'trade_status'=>'TRADE_SUCCESS',
                'trade_no'=>'2018010921001004710557002987',
                'auth_app_id'=>'2017121100556083',
                'receipt_amount'=>'0.01',
                'point_amount'=>'0.00',
                'app_id'=>'2017121100556083',
                'buyer_pay_amount'=>'0.01',
                'sign_type'=>'RSA2',
                'seller_id'=>'2088411974941635',
            );*/
        // sjj
        $config =  \Yii::$app->params['alipay'];
        $alipaySevice = new \AlipayTradeService($config);
        $alipaySevice->writeLog(var_export(\Yii::$app->request->post(),true));
        // $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($arr);
        // $result = 1;
        $time = time();

        /* 实际验证过程建议商户添加以下校验。
        1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
        4、验证app_id是否为该商户本身。
        */
        if($result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代

            
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            
            //商户订单号

            $out_trade_no = \Yii::$app->request->post('out_trade_no');

            //支付宝交易号

            $trade_no = \Yii::$app->request->post('trade_no');

            //交易状态
            $trade_status = \Yii::$app->request->post('trade_status');
            $total_amount = \Yii::$app->request->post('total_amount');
            
            /*$out_trade_no = '120050013330';
            $trade_status = 'TRADE_SUCCESS';
            $total_amount = '0.01';*/

            if($trade_status == 'TRADE_FINISHED') {

                //判断该笔订单是否在商户网站中已经做过处理
                    //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                    //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                    //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            } else if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                    //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                    //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                    //如果有做过处理，不执行商户的业务程序            
                //注意：
                //付款完成后，支付宝系统发送该交易状态通知



                // 订单


                $cdb = \common\models\Pro_vehicle_order::find();
                $cdb->where(['serial' => $out_trade_no]);
                $objOrder = $cdb->one();
                // $objFormData = new \common\models\Form_pro_vehicle_order();
                if($objOrder && $objOrder->pay_source == 0){
                    $objFormData = new \backend\models\Form_pro_vehicle_order_price_detail();
                    $objModel    = new \common\models\Pro_vehicle_order_price_detail();
                    $objModel->summary_amount  = $total_amount;
                    $objModel->price_rent = $objOrder->price_rent;
                    $objModel->price_optional_service = $objOrder->price_optional_service;
                    $objModel->belong_office_id = $objOrder->belong_office_id;
                    $objModel->time = $time;
                    $objModel->pay_source = 5;
                    $objModel->belong_office_id = $objOrder->belong_office_id;
                    $objModel->order_id = $objOrder->id;
                    $objModel->serial = $objOrder->id.'-2-'.$time;
                    $objModel->type = 2;
                    $objModel->status = '0';

                    $objModel->save();


                    //Pro_purchase_order表支付记录
                    if ($objModel->summary_amount && $objModel->pay_source) {
                        $objOrder->paid_amount += $objModel->summary_amount;
                        $objOrder->pay_source = $objModel->pay_source;

                        $objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehiclePcOrder($objOrder, $objModel->summary_amount, $objModel->belong_office_id, \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT, $objModel->time);
                        // echo "<pre>";
                        // print_r($objPurchaseOrder);
                        // echo "</pre>";die;
                        $objPurchaseOrder->save();
                    }


                    $objOrder->save();


                }
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            echo "success"; //请不要修改或删除
        }else {
            //验证失败
            echo "fail";

        }
    }
}
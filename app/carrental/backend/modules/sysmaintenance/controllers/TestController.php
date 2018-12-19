<?php
namespace backend\modules\sysmaintenance\controllers;

/**
 * Description of TestController
 *
 * @author kevin
 */
class TestController  extends \backend\components\AuthorityController
{
    
    public function beforeAction($action) {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            return false;
        }
        return parent::beforeAction($action);
    }
    
    public function actionIndex() {
        return $this->render('index');
    }
    
    public function actionOuter_api() {
        
        return $this->renderPartial('outer_api');
    }
    
    public function actionLunar_calendar() {
        
        return $this->renderPartial('lunar_calendar');
    }
    
    public function actionPaymentcallback() {
        return $this->renderPartial('paymentcallback');
    }
    
    public function actionSend_sms() {
        //$arrHtml = [];
        
        $text = \common\components\SmsComponent::formatSmsContent(\common\components\Consts::KEY_SMS_ORDER_BOOKED_BY_OFFICE, [
            'CNAME'=>'白杨', 
            'AUTOMODEL'=>'别克英朗',
            'USETIME'=>  date('Y-m-d H:i', time()),
            'SHOPADDRESS'=>'宾虹路1010号宾虹路店',
            'SHOPTELEPHONE'=>'0000-0000000',
            'ORDERID'=>'10101234567890',
        ]);
        
        if (!$text) {
            $text = '我是白杨';
        }
        
        $arrResult = \common\components\SmsComponent::send('18695276890', 8888888, [
            'game' => $text,
            'code' => '0000',
            
            'appKey' => 'd580ad56b4b5',
        ]);
        
        echo ($arrResult[1]);
    }
    
    public function actionDistance() {
        $srcCoordinate = '116.481028,39.989643';
        $dstCoordinate = '114.465302,40.004717';
        
        $result = \common\components\MapApiGaode::create()->getDistance($srcCoordinate, $dstCoordinate);
        
        if ($result[0] < 0) {
            echo \yii\helpers\Html::tag('div', $result[1], ['class'=>'alert alert-danger']);
        }
        else {
            $txt = "distance between [{$srcCoordinate} - {$dstCoordinate}] is: {$result[0]}";
            echo \yii\helpers\Html::tag('div', $txt, ['class'=>'alert alert-info']);
        }
    }
    
    private function formatTestApiParams($params) {
        ksort($params);
        $arrVerifys = [];
        foreach ($params as $k => $v) {
            $k = strval($k);
            $v = strval($v);
            $arrVerifys[] = "{$k}={$v}";
        }
        $arrVerifys[] = \frontend\components\ApiModule::KEY;
        $params['sign'] = md5(implode("|", $arrVerifys));
        return $params;
    }

    public function actionApi_car_list() {
        $shopId = intval(\Yii::$app->request->get('sid'));
        $takeCarTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->get('take_car_time'));
        $returnCarTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->get('return_car_time'));
        $result = '';
        $msg = '';
        $carList = [];
        if ($shopId) {
            $url = \Yii::$app->request->getHostInfo(). \common\helpers\Utils::getRootUrl().'app/carrental/frontend/web/api/car_list';
            $response = \common\helpers\Utils::queryUrlGet($url, 
                $this->formatTestApiParams(['sid'=>$shopId, 'take_car_time'=>$takeCarTime, 'return_car_time'=>$returnCarTime, 'time'=>time()]), 
                30, [CURLOPT_COOKIE=>'PHPSESSID=qfufssau8qntkq16mgljt66cd6; _csrf=05c9a9e3fb2bc7d6f2214640bde4b4f833c0d0f8a5237c688a44386dc6bc267da%3A2%3A%7Bi%3A0%3Bs%3A5%3A%22_csrf%22%3Bi%3A1%3Bs%3A32%3A%22lx7pMsr2925LrvsmbOERgBZIoN0k7SuP%22%3B%7D']);
            $httpCode = $response[0];
            if ($httpCode == 200) {
                $jsonData = json_decode($response[1], true);
                $result = $jsonData['result'];
                $msg = $jsonData['desc'];
                if ($result == \frontend\components\ApiModule::CODE_SUCCESS) {
                    $carList = $jsonData['car_list'];
                }
            }
            else {
                $result = $httpCode;
                $msg = $response[1];
            }
        }
        $arrData = [
            'shopId' => $shopId,
            'takeCarTime' => $takeCarTime,
            'returnCarTime' => $returnCarTime,
            'result' => $result,
            'msg' => $msg,
            'carList' => $carList,
        ];
        return $this->renderPartial('api_car_list', $arrData);
    }
    
    public function actionApi_order_preview() {
        $arrData = [];
        return $this->renderPartial('api_order_preview', $arrData);
    }
    
}

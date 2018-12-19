<?php
namespace frontend\controllers;

/**
 * Description of OrderController
 *
 * @author kevin
 */
class OrderController extends \common\helpers\AuthorityController {

    private $actionKey = \frontend\components\ApiModule::KEY;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function($rule, $action) {
                            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_NOT_LOGIN, 'desc' => \Yii::t('locale', 'Login required.')]);
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'order' => ['post'],
                    'order_preview' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction1($action) {
        $preVerify = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
        if (!$preVerify[0]) {
            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_ON_MAINTENANCE, 'desc' => $preVerify[1]]);
            return false;
        }
        $arrParams = [];
        $sign = '';
        $params = [];
        if ($action->id == 'order' || $action->id == 'order_preview') {
            $params = \Yii::$app->request->post();
        }
        else {
            $params = \Yii::$app->request->get();
        }
        foreach ($params as $k => $v) {
            if ($k == 'sign') {
                $sign = $v;
            }
            else {
                $arrParams[$k] = $v;
            }
        }

        $arrVerifys = [];
        ksort($arrParams);
        foreach ($arrParams as $k => $v) {
            $k = strval($k);
            if (is_array($v)) {
                $v = implode("|", $v);
            }
            else {
                $v = strval($v);
            }
            $arrVerifys[] = "{$k}={$v}";
        }
        $arrVerifys[] = $this->actionKey;

        $mySign = md5(implode("|", $arrVerifys));
        if ($mySign == $sign) {
            return true;
        }

        echo json_encode(['result'=> \frontend\components\ApiModule::CODE_INVALID_PACKAGE, 'desc' => \Yii::t('locale', 'Invalid access!')]);
        return false;
    }

    /**
    *@param    "car_id": "50",
    *@param    "days": "1.0",
    *@param    "end_time": "1498723200",
    *@param    "price_type": "1",
    *@param     "return_sid": "20",
    *@param    "ser_list": "1|2",
    *@param    "sid": "20",
    *@param    "start_time": "1498636800",
    *@param    "time": "1498635476",
    *@param    "sign": "b9498845af575b96f9a9c7effdf48207"
    *@return   {
                    "result": 0,
                    "desc": "成功",
                    "order_id": "110050006328",
                    "total_price": 133,
                    "rent_price": 133,
                    "price_type": 1,
                    "pledge_cash": "3000.00",
                    "car_pledge_cash": 0,
                    "car": {
                        "car_id": 50,
                        "car_name": "测试11111111111111",
                        "carriage": 0,
                        "car_image": "http://gm.yikazc.com:8010/public/upload/vehicle/models/50_0_201703151540.jpg",
                        "car_mode": 1,
                        "seat": 5,
                        "consume": "1.8L",
                        "gearboxmode": "2",
                        "property_text": "1.8L|自动|5座"
                    },
                    "start_time": 1498636800,
                    "end_time": 1498723200,
                    "status": 1,
                    "preferential_info": "",
                    "preferential_price": 0,
                    "store": {
                        "sid": 20,
                        "store_name": "宾虹路门店"
                    },
                    "re_store": {
                        "sid": 20,
                        "store_name": "宾虹路门店"
                    },
                    "ser_list": [
                        {
                            "ser_id": 1,
                            "ser_price": 0,
                            "ser_count": 1,
                            "ser_name": "手续费"
                        },
                        {
                            "ser_id": 2,
                            "ser_price": 0,
                            "ser_count": 1,
                            "ser_name": "基本保险"
                        }
                    ]
                }
    */
    public function actionOrder_preview() {
        $params = \Yii::$app->request->post();
       
        $arrData = \frontend\components\OrderService::processOrder($params, false);
        
        echo json_encode($arrData);
    }

    public function actionOrder() {
        $params = \Yii::$app->request->post();
        //sjj
       //  $date=date('Y-m-d H:i:s',time());
       //  $a=json_encode($params);
       // file_put_contents('preview.txt',"$a'.'\n",FILE_APPEND);
        //sjj
        $arrData = \frontend\components\OrderService::processOrder($params, true);
        
        echo json_encode($arrData);
    }

    public function actionCancel_order() {
        $arrResult = \frontend\components\OrderService::getOrderBySerial(\Yii::$app->request->get('order_id'));
        $arrData = $arrResult[0];
        $objOrder = $arrResult[1];
        do
        {
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            
            if ($objOrder->status > \common\models\Pro_vehicle_order::STATUS_BOOKED) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_ORDER_CANNOT_CANCEL;
                $arrData['desc'] = \Yii::t('locale', 'Order cannot be canceled.');
                break;
            }
            
            if ($objOrder->paid_amount > 0) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_ORDER_CANNOT_CANCEL;
                $arrData['desc'] = '很抱歉，因您的订单已支付确认，系统暂不支持该设备取消订单，请到店或联系客服来取消订单。';
                break;
            }
            
            $objOrder->status = \common\models\Pro_vehicle_order::STATUS_CANCELLED;
            if (!$objOrder->save()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = \Yii::t('locale', 'Cancel order failed.');
                break;
            }
            
            \common\components\SmsComponent::send($objOrder->customer_telephone, \common\components\Consts::KEY_SMS_ORDER_CANCELED, [
                'CNAME'=>$objOrder->customer_name, 
                'ORDERID'=>$objOrder->serial,
            ]);
        }while (0);

        echo json_encode($arrData);
    }

    public function actionGet_order_list() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            if (\Yii::$app->user->getIsGuest()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                break;
            }
            
            $cdb = \common\models\Pub_user::find();
            $cdb->where(['id' => \Yii::$app->user->id]);
            $objUser = $cdb->one();
            if (!$objUser) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required.');
                break;
            }
            
            $objUserInfo = \common\models\Pub_user_info::findById($objUser->info_id);
            if (!$objUserInfo) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;
                $arrData['desc'] = \Yii::t('locale', 'User identity information needed.');
                break;
            }
            
            $cdb = \common\models\Pro_vehicle_order::find(true);
            $cdb->where(['user_id' => $objUserInfo->id]);
            $arrRows = $cdb->all();
            
            $arrOfficeIds = [];
            $arrVehicleModelIds = [];
            foreach ($arrRows as $row) {
                if (!isset($arrVehicleModelIds[$row->vehicle_model_id])) {
                    $arrVehicleModelIds[$row->vehicle_model_id] = 1;
                }
                if (!isset($arrOfficeIds[$row->office_id_rent])) {
                    $arrOfficeIds[$row->office_id_rent] = 1;
                }
                if (!isset($arrOfficeIds[$row->office_id_return])) {
                    $arrOfficeIds[$row->office_id_return] = 1;
                }
            }
            
            $arrVehicleModels = [];
            $arrOfficeNames = [];
            if (!empty($arrVehicleModelIds)) {
                $arrVehicleModels = \common\components\VehicleModule::getVehicleModelObjects(array_keys($arrVehicleModelIds));
            }
            if (!empty($arrOfficeIds)) {
                $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
            }
            
            $arrOrders = [];
            foreach ($arrRows as $objOrder) {
                $arrOrders[] = \frontend\components\OrderService::getOrderAttributes($objOrder, false, $arrVehicleModels, $arrOfficeNames);
            }
            
            $arrData['orders'] = $arrOrders;
            
        }while (0);

        echo json_encode($arrData);
    }
    
    public function actionOrder_detail() {
        $arrResult = \frontend\components\OrderService::getOrderBySerial(\Yii::$app->request->get('order_id'));
        $arrData = $arrResult[0];
        $objOrder = $arrResult[1];
        do
        {
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            
            $orderData = \frontend\components\OrderService::getOrderAttributes($objOrder, true);
            foreach ($orderData as $k => $v) {
                $arrData[$k] = $v;
            }
            
        }while (0);

        $time   = time();
        $date   = date('Y-m-d H:i:s',time());
        $b      = json_encode($arrData);
        file_put_contents('detail.txt',"$date-->$b\n",FILE_APPEND);

        echo json_encode($arrData);
    }
    
}

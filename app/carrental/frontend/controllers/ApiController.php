<?php
namespace frontend\controllers;

/**
 * Api controller
 */
class ApiController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;
    private $actionKey = \frontend\components\ApiModule::KEY;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
        ];
    }
    
    public function beforeAction($action) {
        $preVerify = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
        if (!$preVerify[0]) {
            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_ON_MAINTENANCE, 'desc' => $preVerify[1]]);
            return false;
        }
        //return true;
        $arrParams = [];
        $sign = '';
        $params = \Yii::$app->request->get();
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
            $v = strval($v);
            $arrVerifys[] = "{$k}={$v}";
        }
        $arrVerifys[] = $this->actionKey;
        
        $mySign = md5(implode("|", $arrVerifys));
        if ($mySign == $sign) {
            return true;
        }
        
        \Yii::error("verify api failed str:".implode("|", $arrVerifys)." my_sign:{$mySign} sign:{$sign}", 'api');
        echo json_encode(['result'=> \frontend\components\ApiModule::CODE_INVALID_PACKAGE, 'desc' => \Yii::t('locale', 'Invalid access!')]);
        return false;
    }
    
    public function actionInit() {
        $arrData = ['result'=>\frontend\components\ApiModule::CODE_SUCCESS, 'msg'=>\Yii::t('locale', 'Success')];
        
        do
        {
            $version = \Yii::$app->request->get('version');
            $arrData['version'] = $version;
            
            $arrInitialKeys = \common\models\Pro_initial::findAll(['status'=>\common\components\Consts::STATUS_ENABLED]);
            foreach ($arrInitialKeys as $row) {
                $arrData[$row->name] = $row->value;
            }
            
            $arrImageList = [];
            
            $arrImageRows = \common\models\Pro_activity_image::findAll(['type'=>\common\models\Pro_activity_image::TYPE_APP_HOME_IMAGES, 'status'=>\common\models\Pro_activity_image::STATUS_ENABLED]);
            // sjj
            // $cdb = \common\models\Pro_activity_image::find();
            // $cdb->where(['type'=>\common\models\Pro_activity_image::TYPE_APP_HOME_IMAGES,'status'=>\common\models\Pro_activity_image::STATUS_ENABLED]);
            // $cdb->orderBy('bind_param desc');
            // $arrImageRows = $cdb->all();

            //
            foreach ($arrImageRows as $row) {
                $arrImageList[] = [
                    'image' => \common\helpers\Utils::toFileAbsoluteUrl($row->image),
                    'link' => $row->href,
                    'title' => $row->name,
                    'content' => $row->remark,
                    'icon' => $row->icon,
                ];
            }
            
            $arrData['image_list'] = $arrImageList;
            
        } while (0);
        
        echo json_encode($arrData);
    }

    


    public function actionCity_list() {
        $cdb = \common\models\Pro_city::find();
        $cdb->where("type=".\common\models\Pro_city::TYPE_CITY);
        $cdb->andWhere("status=".\common\models\Pro_city::STATUS_NORMAL);
        
        $arrRows = $cdb->all();
        $arrCities = [];
        $arrHotCities = [];
        
        foreach ($arrRows as $row) {
            $o = [
                'cid' => $row->city_code,
                'city' => $row->name,
                'city_code' => $row->city_code,
            ];
            $arrCities[] = $o;
            if (($row->flag & \common\models\Pro_city::FLAG_HOT)) {
                $arrHotCities[] = $o;
            }
        }
        
        $arrData = [
            'result' => \frontend\components\ApiModule::CODE_SUCCESS,
            'desc' => 'Success',
            'hot_city' => $arrHotCities,
            'city_list' => $arrCities
        ];
        echo json_encode($arrData);
    }
    
    public function actionShop_list() {
        $cityCode = \Yii::$app->request->get('cid');
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            if (empty($cityCode)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            
            $objCity = \common\models\Pro_city::findOne(['city_code'=>$cityCode, 'type'=>\common\models\Pro_city::TYPE_CITY]);
            if (!$objCity) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_SHOP_FOR_CITY;
                $arrData['desc'] = \Yii::t('carrental', 'There is no shop for this city');
                break;
            }
            
            $arrCityIds = [$objCity->id];
            $cdb = \common\models\Pro_city::find();
            $cdb->select(['id']);
            $cdb->where(['belong_id' => $objCity->id]);
            $cdb->andWhere("status >= :status", [':status' => \common\models\Pro_city::STATUS_NORMAL]);
            $arrRows = $cdb->all();
            foreach ($arrRows as $row) {
                $arrCityIds[] = $row->id;
            }
            
            $arrShops = [];
            if (!empty($arrCityIds)) {
                $cdb0 = \common\models\Pro_city::find();
                $cdb0->where(['id' => $arrCityIds]);
                $arrRows = $cdb0->all();
                $arrCityNames = [];
                foreach ($arrRows as $row) {
                    $arrCityNames[$row->id] = $row->name;
                }
                $cdb = \common\models\Pro_office::find();
                $cdb->where(['city_id' => $arrCityIds]);
                $cdb->andWhere("`status`=".\common\models\Pro_office::STATUS_NORMAL);
                $arrRows = $cdb->all();
                
                $r = new \common\models\Pro_office();
                foreach ($arrRows as $row) {
                    $arrShops[] = [
                        'sid' => $row->id,
                        'shop_name' => $row->fullname,
                        'cid' => $cityCode,
                        'aid' => $row->city_id,
                        'area_name' => (isset($arrCityNames[$row->city_id]) ? $arrCityNames[$row->city_id] : ''),
                        'address' => $row->address,
                        'phone' => $row->telephone,
                        'airport' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_AIR_PORT) ? 1 : 0),
                        'train_station' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_TRAIN_STATION) ? 1 : 0),
                        'bus_station' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_BUS_STATION) ? 1 : 0),
                        'open_time' => $row->open_time,
                        'end_time' => $row->close_time,
                        'gps_x' => $row->geo_x,
                        'gps_y' => $row->geo_y,
                    ];
                }
            }
            
            $arrData['shop_list'] = $arrShops;
        }while (0);
        
        echo json_encode($arrData);
    }
    
    public function actionShop_info() {
        $shopId = intval(\Yii::$app->request->get('sid'));
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            if (empty($shopId)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }

            $cdb = \common\models\Pro_office::find();
            $cdb->where(['id' => $shopId]);
            $row = $cdb->one();
            $shopInfo = null;
            if ($row) {
                $cityCode = '';
                $cdb = \common\models\Pro_city::find();
                $cdb->where(['id' => $row->city_id]);
                $city = $cdb->one();
                if ($city && !empty($city->belong_id)) {
                    $cityCode = $city->city_code;
                }
                
                $previewArray = [];
                $commentArray = [];
                
                $cdb2 = \common\models\Pro_office_comments::find();
                $cdb2->where(['office_id'=> $shopId]);
                $cdb2->orderBy('created_at desc');
                $commentCount = $cdb2->count();
                $cdb2->limit(5);
                $cdb2->offset(0);
                $arrCommentRows = $cdb2->all();
                $arrUserIds = [];
                foreach ($arrCommentRows as $_o) {
                    if (!isset($arrUserIds[$_o->user_id])) {
                        $arrUserIds[$_o->user_id] = 1;
                    }
                }
                $arrUserObjects = \common\components\UserModule::getUserObjectsArray(array_keys($arrUserIds));
                foreach ($arrCommentRows as $_o) {
                    $commentArray[] = [
                        'name' => (isset($arrUserObjects[$_o->user_id]) ? $arrUserObjects[$_o->user_id] : ''),
                        'user_id' => $_o->user_id,
                        'message' => $_o->comment,
                        'time' => $_o->created_at,
                    ];
                }
                
                $shopInfo = [
                    'sid' => $row->id,
                    'shop_name' => $row->fullname,
                    'cid' => $cityCode,
                    'aid' => $row->city_id,
                    'area_name' => ($city ? $city->name : ''),
                    'address' => $row->address,
                    'airport' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_AIR_PORT) ? 1 : 0),
                    'train_station' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_TRAIN_STATION) ? 1 : 0),
                    'bus_station' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_BUS_STATION) ? 1 : 0),
                    'open_time' => $row->open_time,
                    'end_time' => $row->close_time,
                    'gps_x' => $row->geo_x,
                    'gps_y' => $row->geo_y,
                    'phone' => $row->telephone,
                    
                    'shop_grade' => '4.5',
                    'comment_count' => $commentCount,
                    'preview' => $previewArray,
                    'comment' => $commentArray,
                ];
                $arrData['shop'] = $shopInfo;
            }
            else {
                $arrData['result'] = \frontend\components\ApiModule::CODE_OFFICE_NOT_EXISTS;
                $arrData['desc'] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Office')]);
            }
        }while (0);
        
        echo json_encode($arrData);
    }
    
    public function actionCar_list() {
        $shopId = intval(\Yii::$app->request->get('sid'));
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            if (empty($shopId)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            
            $takeCarTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->get('take_car_time'));
            $returnCarTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->get('return_car_time'));
            // TODO look for orders to find witch car is valid
            
            if ($takeCarTime == 0) {
                $takeCarTime = time();
            }
            
            $arrLeftCountByVehicleModel = \common\components\OrderModule::getVehicleLeftCountByTimeRegion($shopId, $takeCarTime, $returnCarTime);
            
            if ($returnCarTime <= $takeCarTime) {
                $returnCarTime = $takeCarTime + 86400;
            }

            //杭州萧山便利点延后两小时下单
            /*$ishz = \common\components\CheckModule::check_office_order_time($shopId,$takeCarTime);
            if(!$ishz){
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                $arrData['desc'] = '请提前两个小时下单';
                break;
            }*/
            $rentTimeData = \common\models\Pri_renttime_data::create($takeCarTime, $returnCarTime);
            
            // sjj 判断是否春节期间,春节月租价调整
            $festival_id=2;
            $objFesitval = \common\models\Pro_festival::findById($festival_id);
            if($objFesitval->status == 0){
                if($takeCarTime <= $objFesitval->start_time && $returnCarTime >= $objFesitval->end_time){
                    $is_fesitval = 1;
                }else{
                    $is_fesitval = 0;
                }
            }
            // sjj
            $arrVehicles = [];
            $carriageArr  = \common\components\VehicleModule::getVehicleCarriagesArray();
            if (!empty($arrLeftCountByVehicleModel)) {
                $cdb = \common\models\Pro_vehicle_model::find();
                $cdb->where(['id' => array_keys($arrLeftCountByVehicleModel)]);
                $arrRows = $cdb->all();
                
                $arrFeePlans = [];
                $arrVehicleModelIds = [];
                foreach ($arrRows as $row) {
                    $arrVehicleModelIds[$row->id] = 1;
                }
                if (!empty($arrVehicleModelIds)) {
                    $arrFeePlans = \common\components\VehicleModule::getFeePlanObjects(array_keys($arrVehicleModelIds), $shopId);
                }
                
                foreach ($arrRows as $row) {
                    $feeOnline = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_APP, $shopId, $row->id);
                    $feeOffice = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE, $shopId, $row->id);
                    if ($feeOnline || $feeOffice) {
                        $priceOnlineInfo = null;
                        $priceOfficeInfo = null;
                        if ($feeOnline) {
                            $priceOnlineInfo = $feeOnline->getPriceForDuration($rentTimeData->startTime, $rentTimeData->endTime, \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE);
                        }
                        if ($feeOffice) {
                            $priceOfficeInfo = $feeOffice->getPriceForDuration($rentTimeData->startTime, $rentTimeData->endTime, \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE);
                        }
                        $feeDefault = $feeOnline ? $feeOnline : $feeOffice;
                        
                        $arrVehicles[] = [
                            'car_id' => $row->id,
                            'car_name' => $row->vehicle_model,
                            'car_image' => \common\components\VehicleModule::getVehicleModelImageUrl($row->image_0),
                            // 'carriage' => $carriageArr[$row->carriage],
                            'carriage' => $row->carriage,
                            'seat' => $row->seat,
                            'rent_deposit' =>$row->rent_deposit,
                            'consume' => $row->vehicleEmissionHumanText(),
                            'car_type' => $row->vehicleFlagArrayData(),
                            'car_mode' => $row->vehicle_type,
                            'gearboxmode' => (($row->gearbox & \common\models\Pro_vehicle_model::GEARBOX_AUTO) ? '2' : '1'),
                            'property_text' => $row->getPropertyHumanDisplayText(),
                            'left' => (isset($arrLeftCountByVehicleModel[$row->id]) ? $arrLeftCountByVehicleModel[$row->id] : 0),
                            'price_shop' => ($feeOffice ? (($priceOfficeInfo['price'] && $rentTimeData->days) ? ceil($priceOfficeInfo['price'] / $rentTimeData->days) : $feeOffice->getDayPriceByTime($takeCarTime)) : 0),
                            'price_3days' => $feeDefault->price_3days,
                            'price_online' => ($feeOnline ? (($priceOnlineInfo['price'] && $rentTimeData->days) ? ceil($priceOnlineInfo['price'] / $rentTimeData->days) : $feeOnline->getDayPriceByTime($takeCarTime)) : 0),
                            'price_week' => $feeDefault->price_week,
                            'price_month' => $feeDefault->price_month,
                            'price_month' => $is_fesitval ? $feeDefault->special_festivals_price_month : $feeDefault->price_month,
                            'special_festivals_price_month' => $feeDefault->special_festivals_price_month,
                        ];
                    }
                }
            }
            
            $arrData['sid'] = $shopId;
            $arrData['car_list'] = $arrVehicles;
        }while (0);
        
        echo json_encode($arrData);
    }
    
    public function actionService_price() {
        $carId = intval(\Yii::$app->request->get('car_id'));
        $shopId = intval(\Yii::$app->request->get('sid'));
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            if (empty($carId)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            
            $objVehicleModel = \common\models\Pro_vehicle_model::findById($carId);
            if (!$objVehicleModel) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_CAR_NOT_EXISTS;
                $arrData['desc'] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]);
                break;
            }
            
            $startTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->get('start_time'));
            $endTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->get('end_time'));
            if (empty($startTime) || empty($endTime)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('carrental', 'Time should not be empty!');
                break;
            }
            $rentTimeData = \common\models\Pri_renttime_data::create($startTime, $endTime);
            $days = $rentTimeData->days;
            if ($days < 1) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('carrental', 'Time should not be empty!');
                break;
            }
            
            $arrServicePrices = [];
            $arrServiceRows = \common\models\Pro_service_price::findAllServicePrices($shopId);
            foreach ($arrServiceRows as $row) {
                if ($row->app_enablement > 0) {
                    $arrServicePrices[] = $row;
                }
            }
            
            $arrData['car_id'] = $carId;
            
            $arrServices = [];
            $arrServices[] = [
                'ser_id' => \common\models\Pro_service_price::ID_POUNDAGE,
                'ser_name' => \Yii::t('locale', 'Poundage'),
                'required' => '1',
                'default_count' => 1,
                'ser_price' => ''.intval($objVehicleModel->poundage),
                'ser_tips' => '',
            ];
            $arrServices[] = [
                'ser_id' => \common\models\Pro_service_price::ID_BASIC_INSURANCE,
                'ser_name' => \Yii::t('locale', 'Basic insurance'),
                'required' => '1',
                // 'default_count' => 1,
                'default_count' => $row->getActualCount($rentTimeData),
                'ser_price' => ''.intval($objVehicleModel->basic_insurance),
                'ser_tips' => '',
            ];
            /*$arrServices[] = [
                'ser_id' => \common\models\Pro_service_price::ID_DESIGNATED_DRIVING,
                'ser_name' => \Yii::t('locale', 'Designated driving'),
                'required' => '0',
                'default_count' => 0,
                'ser_price' => ''.floatval($objVehicleModel->designated_driving_price),
                'ser_tips' => '',
            ];
            $arrServices[] = [
                'ser_id' => \common\models\Pro_service_price::ID_DESIGNATED_DRIVING_OVERTIME,
                'ser_name' => \Yii::t('locale', '{type} overtime price', ['type'=>\Yii::t('locale', 'Designated driving')]),
                'required' => '0',
                'default_count' => 0,
                'ser_price' => ''.floatval($objVehicleModel->overtime_price_designated),
                'ser_tips' => '',
            ];*/
            if ($rentTimeData->hours > 0) {
                $arrServices[] = [
                    'ser_id'=>\common\models\Pro_service_price::ID_OVERTIME,
                    'ser_name'=>\Yii::t('carrental', 'Overtime service fee'),
                    'required' => '1',
                    'ser_price'=> floatval($objVehicleModel->overtime_price_personal), 
                    'default_count'=>$rentTimeData->hours,
                    'ser_tips' => '',
                ];
            }
            
            foreach ($arrServicePrices as $row) {
                $arrServices[] = [
                    'ser_id' => $row->id,
                    'ser_name' => $row->name,
                    'required' => ''.($row->requirement > 0 ? 1 : 0),
                    'default_count' => $row->getActualCount($rentTimeData),
                    'ser_price' => ''.$row->getActualUnitPrice(),
                    'ser_tips' => $row->tips,
                ];
            }
            
            $arrData['server'] = $arrServices;
            
        }while (0);
        
        echo json_encode($arrData);
    }
    
    public function actionActivity_list() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            $cdb = \common\models\Pro_activity_info::find();
            $cdb->where(['<>', 'status', \common\models\Pro_activity_info::STATUS_DISABLED]);
            $arrRows = $cdb->all();
            
            $arrCityIds = [];
            foreach ($arrRows as $row) {
                if (!isset($arrCityIds[$row->city_id])) {
                    $arrCityIds[$row->city_id] = 1;
                }
            }
            $arrCityNames = \common\components\CityModule::getCityNamesArray(array_keys($arrCityIds));
            
            $arrList = [];
            foreach ($arrRows as $row) {
                $arrList[] = [
                    'act_id' => $row->id,
                    'start_time' => $row->start_time,
                    'end_time' => $row->end_time,
                    'title' => $row->title,
                    'content' => $row->content,
                    'city' => (isset($arrCityNames[$row->city_id]) ? $arrCityNames[$row->city_id] : ''),
                    'link' => $row->href,
                    'icon' => $row->icon,
                ];
            }
            
            $arrData['activity_list'] = $arrList;
        }while (0);
        
        echo json_encode($arrData);
    }
    
    public function actionActivity_info() {
        $activityId = intval(\Yii::$app->request->get('act_id'));
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            if (empty($activityId)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'ID should not be empty!');
                break;
            }
            
            $cdb = \common\models\Pro_activity_info::find();
            $cdb->where(['id'=>$activityId]);
            $cdb->andWhere(['<>', 'status', \common\models\Pro_activity_info::STATUS_DISABLED]);
            $objActivity = $cdb->one();
            
            if (!$objActivity) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_RECORD_NOT_EXISTS;
                $arrData['desc'] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Activity')]);
                break;
            }
            
            $arrCityNames = \common\components\CityModule::getCityNamesArray($objActivity->city_id);
            $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds($objActivity->office_id);
            
            $objCity = \common\models\Pro_city::findById($objActivity->city_id);
            $cityCode = $objCity ? $objCity->city_code : '';
            
            $arrCityData = [
                'act_id' => $objActivity->id,
                'start_time' => $objActivity->start_time,
                'end_time' => $objActivity->end_time,
                'title' => $objActivity->title,
                'content' => $objActivity->content,
                'cid' => $cityCode,
                'city' => (isset($arrCityNames[$objActivity->city_id]) ? $arrCityNames[$objActivity->city_id] : ''),
                'store_id' => $objActivity->office_id,
                'store_name' => (isset($arrOfficeNames[$objActivity->office_id]) ? $arrOfficeNames[$objActivity->office_id] : ''),
                'link' => $objActivity->href,
                'icon' => $objActivity->icon,
            ];
            
            $arrData['activity_info'] = $arrCityData;
        }while (0);
        
        echo json_encode($arrData);
    }
    
    public function actionCheck_order_time() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            $startTime = \Yii::$app->request->get('start_time');
            $endTime = \Yii::$app->request->get('end_time');
            
            if (!is_numeric($startTime) || !is_numeric($endTime)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle price info')]);
                break;
            }
            
            /*得到开始时间和结束时间之差的日时分秒*/
            $timediff = \frontend\components\ApiModule::timediff($startTime,$endTime);
            $arrData['timediff'] = $timediff;


            $arrFestivals = \common\components\OptionsModule::getFestivalsArray();
            $isMatchFestival = false;
            foreach ($arrFestivals as $id => $festival) {
                if ($festival->isContainsTime($startTime, $endTime)) {
                    $isMatchFestival = true;
                    $arrData['is_festival'] = '1';
                    $arrData['name'] = $festival->name;
                    $arrData['start_time'] = $festival->start_time;
                    $arrData['end_time'] = $festival->end_time;
                }
            }
            if (!$isMatchFestival) {
                $arrData['is_festival'] = '0';
                $arrData['name'] = '';
                $arrData['start_time'] = 0;
                $arrData['end_time'] = 0;
            }
            
            // sjj
            if($timediff['day'] == '30'){
                $arrData['is_festival'] = '0';
                $arrData['name'] = '30天月租价';
                $arrData['start_time'] = $startTime;
                $arrData['end_time'] = $endTime;
            }
            //判断三天打包价时间
            if($timediff['day'] == '3' && $arrData['is_festival'] == '0'){
                $res = \frontend\components\ApiModule::is_discount_period($startTime,$endTime);
                
                if($res==1){
                    $arrData['is_festival'] = '1';
                    $arrData['name'] = \Yii::t('locale', 'Not in');
                    $arrData['start_time'] = $startTime;
                    $arrData['end_time'] = $endTime;
                }
            }
     
        }while(0);
        echo json_encode($arrData);
    }
    
    public function actionCar_daily_prices() {
        $shopId = intval(\Yii::$app->request->get('sid'));
        $carId = intval(\Yii::$app->request->get('car_id'));
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            if (empty($shopId) || empty($carId)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            
            //$takeCarTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->get('take_car_time'));
            $startTime = 0;
            if ($startTime == 0) {
                $startTime = time();
            }
            
            $arrData['sid'] = $shopId;
            $arrData['car_id'] = $carId;
            
            $orderSource = \common\models\Pro_vehicle_order::ORDER_SOURCE_APP;
            $arrFeePlans = \common\components\VehicleModule::getFeePlanObjects($carId, $shopId);
            $objFeePlan = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, $orderSource, $shopId, $carId);
            if (!$objFeePlan) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_CAR_PRICES_FOUND;
                $arrData['desc'] = \Yii::t('carrental', 'No car rent prices found!');
                break;
            }
            
            if (date('H', $startTime) >= \common\components\Consts::HOUR_AS_NEXT_DAY) {
                $startTime = strtotime(date('Y-m-d', $startTime + 3600 * (25 - \common\components\Consts::HOUR_AS_NEXT_DAY)));
            }
            
            $arrPrices = [];
            $delta = 86400;
            $tim = $startTime;
            $endTime = $startTime + $delta * \common\components\Consts::CAR_DAILY_RENT_PRICE_DEFAULT_COUNT;
            while ($tim < $endTime) {
                $price = $objFeePlan->getDayPriceByTime($tim);
                
                $arrPrices[] = $price;
                
                $tim += $delta;
            }
            
            $arrData['price_list'] = $arrPrices;
        }while (0);
        
        echo json_encode($arrData);
        
    }
    
}

<?php
namespace frontend\controllers;

/**
 * Api controller
 */
class PapiController extends \yii\web\Controller
{
    // public $enableCsrfValidation = false;
    // private $actionKey = \frontend\components\ApiModule::KEY;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
        ];
    }
    
    public function init(){
        header('Access-Control-Allow-Origin:http://m.yikazc.com');
        header('Access-Control-Allow-Origin:http://yikazc.com');
        header('Access-Control-Allow-Origin:*');
        $this->enableCsrfValidation = false;
    }
    public function beforeAction1($action) {
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



    /**
    *@desc 城市列表
    */
    public function actionCity_list($value='')
    {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        
        do{
            $cdb = \common\models\Pro_city::find();
            $cdb->where("type=".\common\models\Pro_city::TYPE_CITY);//3
            $cdb->andWhere("status=".\common\models\Pro_city::STATUS_NORMAL);//0
            
            $arrRows = $cdb->all();

            $arrCities = [];
            $arrHotCities = [];
            
            if(empty($arrRows)){
                $arrData['result'] = '1';
                $arrData['desc']   = '没有查到相关数据';
                break;
            }
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
        }while (0);
        echo json_encode($arrData);
    }

    /**
    *@desc 通过城市得到门店列表
    *@param cid
    */
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

    /*
     *PC端通过城市查门店信息
     */
    public function actionGet_shop_bycity(){
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
                        'airport' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_AIR_PORT) ? 1 : 0),
                        'train_station' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_TRAIN_STATION) ? 1 : 0),
                        'bus_station' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_BUS_STATION) ? 1 : 0),
                        'sid' => $row->id,
                        'shop_name' => $row->fullname,
                        'cid' => $cityCode,
                        'city' => $objCity->name,
                        'aid' => $row->city_id,
                        'area_name' => (isset($arrCityNames[$row->city_id]) ? $arrCityNames[$row->city_id] : ''),
                        'title' => $row->fullname,
                        'shortName' => $row->shortname,
                        'address' => $row->address,
                        'phone' => $row->telephone,
                        'imgUrl' => $row->image_info,
                        'pos' => ['x'=>$row->geo_x,'y'=>$row->geo_y],
                        'workTime' =>$row->open_time.'-'.$row->close_time,
                    ];
                }
            }
            
            $arrData['shop_list'] = $arrShops;
        }while (0);
        echo json_encode($arrData);
    }


    /**
    *@desc 所有城市和门店
    */
    public function actionAllcity_shop($value='')
    {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do{
            $cdb = \common\models\Pro_city::find();
            $cdb->where("type=".\common\models\Pro_city::TYPE_CITY);//3
            $cdb->andWhere("status=".\common\models\Pro_city::STATUS_NORMAL);//0
            
            $arrRows = $cdb->asarray()->all();

            $arrCities = [];
            $arrHotCities = [];
            
            if(empty($arrRows)){
                $arrData['result'] = '1';
                $arrData['desc']   = '没有查到相关数据';
                break;
            }

            // $arrData['city'] = $arrRows;
            foreach ($arrRows as $key => $value) {
                $cityCode = $value['city_code'];
                if (empty($cityCode)) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                    $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                    break;
                }
                
                $objCity = \common\models\Pro_city::findOne(['city_code'=>$cityCode, 'type'=>\common\models\Pro_city::TYPE_CITY]);//3
                if (!$objCity) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_NO_SHOP_FOR_CITY;//
                    $arrData['desc'] = \Yii::t('carrental', 'There is no shop for this city');
                    break;
                }
                
                $arrCityIds = [$objCity->id];
                $cdb = \common\models\Pro_city::find();
                $cdb->select(['id']);
                $cdb->where(['belong_id' => $objCity->id]);
                $cdb->andWhere("status >= :status", [':status' => \common\models\Pro_city::STATUS_NORMAL]);//0
                $arrCityRows = $cdb->all();

                foreach ($arrCityRows as $row) {
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
                            'title' => $row->fullname,
                            'shortName' => $row->shortname,
                            'address' => $row->address,
                            'phone' => $row->telephone,
                            // 'imgUrl' => $row->image_info,
                            'imgUrl' => $this->GetImgUrl($row->image_info),
                            'pos' => ['x'=>$row->geo_x,'y'=>$row->geo_y],
                            'area' => $objCity->name,
                            /*'sid' => $row->id,
                            'shop_name' => $row->fullname,
                            'cid' => $cityCode,
                            'aid' => $row->city_id,
                            'area_name' => (isset($arrCityNames[$row->city_id]) ? $arrCityNames[$row->city_id] : ''),
                            'phone' => $row->telephone,
                            'airport' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_AIR_PORT) ? 1 : 0),
                            'train_station' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_TRAIN_STATION) ? 1 : 0),
                            'bus_station' => (($row->landmark & \common\models\Pro_office::LANDMARK_NEAR_BUS_STATION) ? 1 : 0),
                            'open_time' => $row->open_time,
                            'end_time' => $row->close_time,
                            'gps_x' => $row->geo_x,
                            'gps_y' => $row->geo_y,*/
                        ];
                    }
                }
                $arrData[$cityCode]['area'] = $objCity->name;
                $arrData[$cityCode]['list'] = $arrShops;

                $arrData['data'][$cityCode]['area'] = $objCity->name;
                $arrData['data'][$cityCode]['list'] = $arrShops;
            }
        }while (0);
        echo json_encode($arrData);
    }

    /**
    *@desc PC端通过地址或者经纬度判断附近门店是否可以送车服务
    *@param address 地址
    *@return $arrData
    */
    public function actionGetshopbyaddress(){
        $address = trim(\Yii::$app->request->post('address'));
        // $address='金华市胜利街如家酒店';
        // $address='dff';
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
            // 通过地址判断经纬度
            $map = \common\components\MapApiGaode::create();
            $arrCoordinateResult = $map->getCoordinateByAddress($address);
            if($arrCoordinateResult[0] === false && $arrCoordinateResult[1] != 'OK'){
                $arrData['result'] = '1';
                $arrData['desc']   = $arrCoordinateResult[1];
                 $arrData['ss']   = $arrCoordinateResult;
                 $arrData['address']   = $address;
                break;
            }else{
                $addressXandY = $arrCoordinateResult[0];
            }

            // 得到所有门店
            $AllshopAddress = \frontend\components\CommonModule::getAllShopInfo();
            $distance=0;
            foreach ($AllshopAddress as $key => $value) {
                $distanceResult = \common\components\DistanceService::getDistanceByCoordinates($addressXandY, $value['xy']);

                if ($distanceResult[0] < 0) {
                    $arrResult['result'] = 1;
                    $arrResult['desc'] = $distanceResult[1];
                } else {
                    if($distance == 0){
                        $distance = $distanceResult[0];
                        $sid = $value['id'];
                    }
                    if($distance > $distanceResult[0]){
                        $distance = $distanceResult[0];
                        $sid = $value['id'];
                    }


                }
            }
            if($distance < 10){
                $arrData['price'] = '0';
                $arrData['distance'] = $distance;
                $arrData['sid'] = intval($sid);
            }elseif ($distance > 20) {
                $arrData['result'] = '1';
                $arrData['desc'] = '该地还没有送车服务';
            }else{
                $arrData['price'] = '30';
                $arrData['distance'] = $distance;
                $arrData['sid'] = intval($sid);
            }

        }while (0);

        echo json_encode($arrData);

    }


    /**
    *@desc 车型标签
    *@return    1: "火爆",
                2: "新车",
                4: "热销",
                8: "舒适型",
                16: "经济型",
                32: "风尚型",
                64: "商务型",
                128: "SUV",
                256: "MPV"
    */
    public function actionGetallflag($value='')
    {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
            $flags = \common\models\Pro_vehicle_model::getVehicleFlagsArray();
            $imgs = \common\models\Pro_vehicle_model::getVehicleFlagsImgArray();
            if($flags){
                foreach ($flags as $key => $value) {
                    $arr['id'] = $key;
                    $arr['flag'] = $value;
                    $arr['imgs'] = $imgs[$key];
                    $arrData['flags'][] = $arr;
                }
            }else{
                $arrData['result'] = 1;
                $arrData['desc'] = '未获取到车型标签';
            }
        }while(0);
        echo json_encode($arrData);
    }

    /**
    *@desc 获取车型品牌
    */
    public function actionGetallbrand()
    {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
            $cdb = \common\models\Pro_vehicle_brand::find();
            $cdb->select(['id','name']);
            $cdb->where(['belong_brand' => 0]);
            $cdb->andWhere(['flag' => 0]);//0
            $arrBrandRows = $cdb->asarray()->all();
            if($arrBrandRows){
                $arrData['brand'] = $arrBrandRows;
            }else{
                $arrData['result'] = 1;
                $arrData['desc'] = '未获取到品牌列表';
            }
            /*foreach ($arrBrandRows as $row) {
                $arrBrandRows[] = $row->id;
            }*/
        }while(0);
        echo json_encode($arrData);
    }

    public function actionService_price() {
        $carId = intval(\Yii::$app->request->get('car_id'));
        $shopId = intval(\Yii::$app->request->get('sid'));
        // $carId = 50;
        // $shopId = 20;
        
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
            // 
            // $time = time();
            // $startTime=$time;
            // $endTime=$time+86400*2;
            // 
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


    public function GetImgUrl($arrImages){
            $_imagesArray = [];

            $arr = explode(',', $arrImages);
            $arrImageIds = [];
            foreach ($arr as $v0) {
                $arrImageIds[] = intval($v0);
            }

            if (!empty($arrImageIds)) {
                $cdb = \common\models\Pro_image::find();
                $cdb->where(['id' => $arrImageIds]);
                $arrRows = $cdb->all();
                foreach ($arrRows as $row) {
                    // $_imagesArray[intval($row->id)] = $row->path;
                    $_imagesArray[] = \Yii::$app->request->getHostInfo().$row->path;
                }
            }

            return $_imagesArray;
            // echo json_encode($_imagesArray);
    }


    // PC端车分期购车意向方法
    public function actionBuy_car(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            $params = \Yii::$app->request->post();
            $arrData = \frontend\components\ProBuycar::processOrder($params, true);
        }while(0);
        echo json_encode($arrData);
    }



}
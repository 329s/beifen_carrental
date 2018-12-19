<?php 
namespace frontend\controllers;
/**
* @author sjj
* @since 2017-8-5
* @example app新接口和单程往返接口
*/
// class NeworderController extends \common\helpers\AuthorityController
class NeworderController extends \yii\web\Controller
{
	public $enableCsrfValidation = false;
    private $actionKey = \frontend\components\ApiModule::KEY;//ae027603f7aac1a3ae3e83edaf0abf33

	public function behaviors()
    {
        return [
        ];
    }
	public function beforeAction111($action) {
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
	*@example    自驾专区车型列表
    *@param sid  店铺id
	*@param address_take_car  上门送车地址
	*@param take_car_time    取车时间
	*@param return_car_time  还车时间
	*@param sign 安全性验证
    *@param [vehicle_type] 车型列表筛选 1,2,4,8,16,32,64  或者0：全部/不传(弃用)
	*@param [vehicle_flag] 车型列表标签筛选 1,2,4,8,16,32,64  或者0：全部/不传
	*@param [orderby] 价格排序 desc asc
	*@param [priceSort] 价格区间0-300  300-500  500-
	*@return $arrData 返回可租车辆列表
	*@url http://www.ykzc_test.com/app/carrental/frontend/web/index.php/neworder/rental_car_list
	*/
	public function actionRental_car_list()
	{
       
        $shopId = intval(\Yii::$app->request->get('sid'));
        if(!$shopId){
            $address_take_car = trim(\Yii::$app->request->get('address_take_car'));
            $address_take_car_info = $this->getNearShopAndMetre($address_take_car);
            $shopId = $address_take_car_info['sid'];
        }
        $vehicle_flag = intval(\Yii::$app->request->get('vehicle_flag'));
        $orderby = trim(\Yii::$app->request->get('orderby'));
        $priceSort = trim(\Yii::$app->request->get('priceSort'));


        // $shopId = 20;
        // $vehicle_flag = 1;
		// $orderby = 'desc';
		// $priceSort = '500-';
        //一：价格区间筛选
        if($priceSort){
            $arrPriceSort = explode('-',$priceSort);
            if(empty($arrPriceSort[1])){
                $arrPriceSort[1] = 10000;
            }
        }

        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        $now = time();

		do{
			if (empty($shopId)) {
				$arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;//3001
				$arrData['desc']   = \Yii::t('locale', 'Invalid parameter!');//参数非法！
				break;
			}
			$takeCarTime = \Yii::$app->request->get('take_car_time');
            $returnCarTime = \Yii::$app->request->get('return_car_time');
            //如果没有起租时间默认当前时间
            if($takeCarTime == 0){
            	$takeCarTime = $now;
            }
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
            //在库待租车辆系列数量
            $arrLeftCountByVehicleModel = \common\components\OrderModule::getVehicleLeftCountByTimeRegion($shopId, $takeCarTime, $returnCarTime);
            $arrLeftCountByVehicleModel = array_filter($arrLeftCountByVehicleModel);
            
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
            $arr  = \common\components\VehicleModule::getVehicleCarriagesArray();
            if (!empty($arrLeftCountByVehicleModel)) {
            	//查询在库待租车辆的系列信息
                $cdb = \common\models\Pro_vehicle_model::find();
                $cdb->where(['id' => array_keys($arrLeftCountByVehicleModel)]);


                $arrRows = $cdb->all();
                //SELECT * FROM `pro_vehicle_model` WHERE (`id` IN (25, 24, 26, 33, 36, 13, 42, 37, 18, 7, 16, 22, 35, 30, 3, 45, 46, 43, 6, 48, 50, 68, 2, 79, 80, 83)) AND (`vehicle_type`=1)
                $arrFeePlans = [];
                $arrVehicleModelIds = [];//在库待租车子系列id
                foreach ($arrRows as $row) {
                    $arrVehicleModelIds[$row->id] = 1;
                }
                //门店各系车辆每天租金价格
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
                        //价格筛选
			            if($priceSort){
			            	$price_online = ($feeOnline ? (($priceOnlineInfo['price'] && $rentTimeData->days) ? ceil($priceOnlineInfo['price'] / $rentTimeData->days) : $feeOnline->getDayPriceByTime($takeCarTime)) : 0);
			            	if($price_online>= $arrPriceSort[0] && $price_online <= $arrPriceSort[1]){
			            		$arrVehicles[] = [
		                            'car_id' => $row->id,
		                            'vehicle_type' => $row->vehicle_type,
		                            'car_name' => $row->vehicle_model,
		                            'car_image' => \common\components\VehicleModule::getVehicleModelImageUrl($row->image_0),
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
                                    // 'price_month' => $feeDefault->price_month,
                                    'price_month' => $is_fesitval ? $feeDefault->special_festivals_price_month : $feeDefault->price_month,
		                            'special_festivals_price_month' => $feeDefault->special_festivals_price_month,
		                        ];
			            	}

			            }else{
	                        //vehicle_flag车型标签筛选
                            if(isset($vehicle_flag) && $vehicle_flag > 0){
                                $car_type = $row->vehicleFlagArrayData();
                                if(in_array($vehicle_flag,$car_type)){
                                    $arrVehicles[] = [
                                        'car_id' => $row->id,
                                        'vehicle_type' => $row->vehicle_type,
                                        'car_name' => $row->vehicle_model,
                                        'car_image' => \common\components\VehicleModule::getVehicleModelImageUrl($row->image_0),
                                        'carriage' =>$row->carriage,
                                        'rent_deposit' =>$row->rent_deposit,
                                        'seat' => $row->seat,
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
                                        // 'price_month' => $feeDefault->price_month,
                                        'price_month' => $is_fesitval ? $feeDefault->special_festivals_price_month : $feeDefault->price_month,
                                        'special_festivals_price_month' => $feeDefault->special_festivals_price_month,
                                    ];
                                }
                            }else{
                                $arrVehicles[] = [
    	                            'car_id' => $row->id,
    	                            'vehicle_type' => $row->vehicle_type,
    	                            'car_name' => $row->vehicle_model,
    	                            'car_image' => \common\components\VehicleModule::getVehicleModelImageUrl($row->image_0),
    	                            'carriage' => $row->carriage,//$row->carriage,//$carriageArr[$row->carriage],
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
    	                            // 'price_month' => $feeDefault->price_month,
                                    'price_month' => $is_fesitval ? $feeDefault->special_festivals_price_month : $feeDefault->price_month,
                                    'special_festivals_price_month' => $feeDefault->special_festivals_price_month,
    	                        ];
                            }

			            }
                    }
                }
            }
			// 三：价格排揎筛选
            if($orderby){
            	$arrVehicles = $this->array_sort($arrVehicles,'price_online',$orderby);
            }

            $arrData['sid'] = $shopId;
            $arrData['car_list'] = $arrVehicles;

		}while (0);
		echo json_encode($arrData);

	}


	/**
	*@example 车系价格详情
	*@param vehicle_model_id  车系id 50
	*@param officeId  门店id 20
	*@param take_car_time  起租时间
	*@param sign
	*@return $arrData
	*/
	public function actionVehicle_model_price_detail()
	{
		$vehicle_model_id = intval(\Yii::$app->request->post('vehicle_model_id'));
		$officeId = intval(\Yii::$app->request->post('officeId'));
		// $vehicle_model_id = 50;
		// $officeId = 20;
        	$now = time();
			// $takeCarTime = \Yii::$app->request->get('take_car_time');
            // $returnCarTime = \Yii::$app->request->get('return_car_time');
            //如果没有起租时间默认当前时间
            // if($takeCarTime == 0){
                // $takeCarTime = $now;
            	$takeCarTime = mktime(0,0,0,date('m'),1,date('Y'));
            // }
            $returnCarTime = $takeCarTime + 86400*31;


		$arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
        	if (empty($vehicle_model_id) || empty($officeId)) {
				$arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;//3001
				$arrData['desc']   = \Yii::t('locale', 'Invalid parameter!');//参数非法！
				break;
			}

            // 车型信息
            $cdb1 = \common\models\Pro_vehicle_model::find();
            $cdb1->where(['id' => $vehicle_model_id]);
            $carModelInfo = $cdb1->one();
            $arrData['carModelInfo'] = array(
                    'rent_deposit' => $carModelInfo->rent_deposit,
                    'property_text' => $carModelInfo->getPropertyHumanDisplayText(),
                    'vehicle_model' => $carModelInfo->vehicle_model,
            );
            /**/
            $gearboxName = \common\components\VehicleModule::getVehicleGearboxTypesArray();
            $carModelInfo->gearbox = $gearboxName[$carModelInfo->gearbox];
            $oil_label_arr = \common\components\VehicleModule::getVehicleOilLabelsArray();
            $carModelInfo->oil_label = $oil_label_arr[$carModelInfo->oil_label];


            $arrData['carModelAllInfo'] = $carModelInfo->attributes;
			//门店节假日价格和在线手机节假日价格
			$arrFeePlans = \common\components\VehicleModule::getFeePlanObjects($vehicle_model_id, $officeId);
			//在线节假日价格
			$feeOnline = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_APP, $officeId, $vehicle_model_id);
            //门店节假日价格
            $feeOffice = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE, $officeId, $vehicle_model_id);

            $priceOnlineInfo = null;
            $priceOfficeInfo = null;
            $rentTimeData = \common\models\Pri_renttime_data::create($takeCarTime, $returnCarTime);
            if ($feeOnline) {
                $priceOnlineInfo = $feeOnline->getPriceForDuration($rentTimeData->startTime, $rentTimeData->endTime, \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE);
            }
            if ($feeOffice) {
                $priceOfficeInfo = $feeOffice->getPriceForDuration($rentTimeData->startTime, $rentTimeData->endTime, \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE);
            }
            // $feeDefault = $feeOnline ? $feeOnline : $feeOffice;
            //$arrFestivals = \common\components\OptionsModule::getFestivalsArray();
            // $arrData['arrFeePlans'] = $arrFeePlans;
            $arrData['priceOnlineInfo'] = $priceOnlineInfo['details'];
            $arrData['priceOfficeInfo'] = $priceOfficeInfo['details'];

        }while (0);

        echo json_encode($arrData);
	}


    /**
    *@example 订单提交前所生成的订单显示
    *@param car_id
    *@param days
    *@param end_time
    *@param price_type
    *@param return_sid
    *@param ser_list
    *@param sid
    *@param start_time
    *@param time
    *@param sign
    *@return $arrData
    *@url    /app/carrental/frontend/web/index.php/neworder/order_preview
    */
    public function actionOrder_preview() {

        $params = \Yii::$app->request->post();
        /*$params = [
            'address_return_car'=> "金华市区店口镇五金城21单元",
            'address_take_car'=> "金华市区丹光东路9-13号",
            'car_id'=> "24",
            'days'=> "2.0",
            'end_time'=> time()+86400*23+3600,
            'price_type'=> "3",
            'return_sid'=> "116",
            'ser_list'=> "1|2",
            'sid'=> "23",
            'start_time'=> time()+86400*21+3600,
            'time'=> time(),
            'sign'=> "a1dbc405c5fd6d597bcb8958452edd20",

        ];*/

        $address_take_car_info = $this->getNearShopAndMetre($params['address_take_car']);
        $address_return_car_info = $this->getNearShopAndMetre($params['address_return_car']);

        $params['price_take_car']=$address_take_car_info['price'];

        $params['price_return_car']=$address_return_car_info['price'];
        /*echo "<pre>";
        print_r($params);
        echo "</pre>";die;*/
        /*$b       =json_encode($params);
        file_put_contents('preview.txt',"$b\n",FILE_APPEND);*/

        $arrData = \frontend\components\NewOrderService::processOrder($params, false);
        echo json_encode($arrData);
    }
    public function actionOrder() {
        $params = \Yii::$app->request->post();
        $address_take_car_info = $this->getNearShopAndMetre($params['address_take_car']);
        $address_return_car_info = $this->getNearShopAndMetre($params['address_return_car']);
        $params['price_take_car']=$address_take_car_info['price'];

        $params['price_return_car']=$address_return_car_info['price'];

        $arrData = \frontend\components\NewOrderService::processOrder($params, true);

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
            // $cdb->where(['id' => 18988]);
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

	/**
	*@example 二维数组排序
    *@param $arr 二维数组
    *@param $keys 二维数组中的某个key值
	*/
    public function array_sort($arr, $keys, $type = 'desc') {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }




    /*
    *@desc 根据地址得到经纬度
    *@param $address
    */
    public function getXandYByaddress($address)
    {
        $map = \common\components\MapApiGaode::create();
        $arrCoordinateResult = $map->getCoordinateByAddress($address);
        if(!$arrCoordinateResult[0]){
            $arrData['result'] = -1;
            $arrData['desc'] = $arrCoordinateResult[1];
            return $arrData;
        }else{
            return $arrCoordinateResult;
        }
    }
    /*
    *@所有门店的经纬度
    */
    public function getAllShopInfo(){
        $cdb = \common\models\Pro_office::find();
        $cdb->where(['status' => 0]);
        $cdb->andWhere(['parent_id' => 0]);
        $arrRows = $cdb->all();
        foreach ($arrRows as $key => $value) {
            if($value->geo_x && $value->geo_y){
                $arr[$key]['xy'] = $value->geo_x.','.$value->geo_y;
                $arr[$key]['id'] = $value->id;
            }
        }
        return $arr;
    }
    /*得到所有开通单程功能的门店经纬度*/
    public function getOneWayShopInfo(){
        $cdb = \common\models\Pro_office::find();
        $cdb->where(['status' => 0]);
        $cdb->andWhere(['parent_id' => 0]);
        $cdb->andWhere(['isonewayoffice' => 1]);
        $arrRows = $cdb->all();
        foreach ($arrRows as $key => $value) {
            if($value->geo_x && $value->geo_y){
                $arr[$key]['xy'] = $value->geo_x.','.$value->geo_y;
                $arr[$key]['id'] = $value->id;
            }
        }
        return $arr;
    }
    /*
    *@desc 送车上门最近门店
    *@param $address
    *@return arr
    *    $arr['price'] = $price;
    *    $arr['distance'] = $distance;
    *    $arr['sid'] = $sid;
    */
    public function getNearShopAndMetre($address)
    {
        //根据地址得到经纬度
        $addressXandY = $this->getXandYByaddress($address);
        if(isset($addressXandY['result'])){
            $arr['addressXandY']=$addressXandY;
            return $arr;
            exit();
        }
        // 得到所有门店
        $AllshopAddress = $this->getAllShopInfo();
        $distance=0;
        $distanceArr = array();
        foreach ($AllshopAddress as $key => $value) {
            $distanceResult = \common\components\DistanceService::getDistanceByCoordinates($addressXandY['0'], $value['xy']);
            /*if ($distanceResult[0] < 0) {
                $arrResult['result'] = -1;
                $arrResult['desc'] = $distanceResult[1];
            } else {
                if($distance == 0){
                    $distance = $distanceResult[0];
                    $sid = $value['id'];
                }elseif ($distanceResult[0] < $distance) {
                    $distance = $distanceResult[0];
                    $sid = $value['id'];
                }

            }*/

            if($distanceResult[0] < 0){
                $arrResult['result'] = -1;
                $arrResult['desc'] = $distanceResult[1];
            }else{
                $distanceArr[$key]['distance'] = $distanceResult[0];
                $distanceArr[$key]['sid']      = $value['id'];
            }
        }

        $arrd = $this->array_sort($distanceArr, 'distance', 'desc');

        foreach ($arrd as $key => $value) {
            $distance = $value['distance'];
            $sid      = $value['sid'];
        }


        $price = \frontend\components\ApiModule::getPriceByDistance($distance);

        $arr['price'] = $price;
        $arr['distance'] = $distance;
        $arr['sid'] = intval($sid);
        return $arr;
    }

    /**
    *@desc   送车上门根据经纬度得到最近门店
    *@param  $gao_x
    *@param  $gao_y
    *@return $arr
    *@url    http://www.yikazc.com/app/carrental/frontend/web/index.php/neworder/get-shop-by-x-y
    */
    public function getNearShopAndMetreByXY($gaoXY){
        // 得到所有门店
        $AllshopAddress = $this->getOneWayShopInfo();
        if(empty($AllshopAddress)){
            $arr['result'] = -1;
            $arr['desc']   = '该城市没有开通单程租车';
            return $arr;
        }
        $distance=0;
        $distanceArr = array();
        foreach ($AllshopAddress as $key => $value) {
            $distanceResult = \common\components\DistanceService::getDistanceByCoordinates($gaoXY, $value['xy']);

            if($distanceResult[0] < 0){
                $arrResult['result'] = -1;
                $arrResult['desc'] = $distanceResult[1];
            }else{
                $distanceArr[$key]['distance'] = $distanceResult[0];
                $distanceArr[$key]['sid']      = $value['id'];
            }
        }

        $arrd  = \common\components\CheckModule::array_sort($distanceArr, 'distance', 'desc');
        // 取最后一个元素，最近门店
        $arrd  = end($arrd);
        $price = \frontend\components\ApiModule::getPriceByDistance($arrd['distance']);

        $arr['result']    = 0;
        $arr['price']    = $price;
        $arr['distance'] = $arrd['distance'];
        $arr['sid']      = $arrd['sid'];
        return $arr;
    }

    /*app根据经纬度得到最近门店*/
    public function actionGetShopByXY(){
        $gao_x = trim(\Yii::$app->request->post('gao_x'));
        $gao_y = trim(\Yii::$app->request->post('gao_y'));
        // $date=date('Y-m-d H:i:s',time());
        // file_put_contents('gaoXY.txt',"$date':$gao_x,$gao_y\n",FILE_APPEND);
        if(empty($gao_x)){
            $arrData['result'] = -1;
            $arrData['desc']   = '参数非法！';
            echo json_encode($arrData);
            exit();
        }
        if(empty($gao_y)){
            $arrData['result'] = -1;
            $arrData['desc']   = '参数非法！';
            echo json_encode($arrData);
            exit();
        }
        $gao_xy  = $gao_x.','.$gao_y;
        $arr     = $this->getNearShopAndMetreByXY($gao_xy);
        // $b       =json_encode($arr);
        // file_put_contents('gaoXY.txt',"$b\n",FILE_APPEND);

        if($arr['result'] != 0){
            $arrData['result'] = -1;
            $arrData['desc']   = $arr['desc'];
            echo json_encode($arrData);
            exit();
        }
        if(isset($arr['distance'])){
            if ($arr['distance'] > 40) {
                $arrData['result'] = -1;
                $arrData['desc']   = '距离太远不接受送车服务';
                echo json_encode($arrData);
            }else{
                $arrData['result'] = 0;
                $arrData['desc']   = '查询成功';
                $arrData['arr']    = $arr;
                echo json_encode($arrData);
            }
        }else{
            $arrData['result'] = -1;
            $arrData['desc']   = '查询失败';
            echo json_encode($arrData);
        }
    }


    /**
     *单程租车上车上门地址和上门取车地址距离计算
     *@param   take_address_x
     *@param   take_address_y
     *@param   return_address_x
     *@param   return_address_y
     *@return  $arrData
     *@url    /app/carrental/frontend/web/index.php/neworder/get-km-by-x-y
     */
    public function actionGetKmByXY(){

        $params = \Yii::$app->request->post();

        $requiredFields   = [
            'take_address_x',
            'take_address_y',
            'return_address_x',
            'return_address_y',
        ];


        $arrData = ['result'=>\frontend\components\ApiModule::CODE_SUCCESS, 'desc'=>\Yii::t('locale', 'Success')];
        do{
            foreach ($requiredFields as $key => $value) {
                //判断所传参数是否缺少
                if (!isset($params[$value])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;//4001
                    $arrData['desc']    = \Yii::t('locale', 'Missing required parameter!');//缺少必要参数
                    break;
                }

            }
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }

            $take_address_xy   = $params['take_address_x'].','.$params['take_address_y'];
            $return_address_xy = $params['return_address_x'].','.$params['return_address_y'];


            $distanceResult = \common\components\DistanceService::getDistanceByCoordinates($take_address_xy, $return_address_xy);

            if($distanceResult[1] == 'OK'){
                if($distanceResult[0] < 20){
                    $arrData['result'] = '-1';
                    $arrData['desc'] = \Yii::t('locale', 'The distance is too close to support service');
                    $arrData['address_km'] = $distanceResult[0];
                }else{
                    $arrData['address_km'] = $distanceResult[0];
                }
            }else{
                $arrData['result'] = '-1';
                $arrData['desc'] = '查询错误';
            }
        }while (0);

        echo json_encode($arrData);

    }

    /*安卓接口 最近还车门店和两地之间的距离*/
    public function actionGetShopAndTwoAddressKm(){
        $params = \Yii::$app->request->post();

        $requiredFields   = [
            'take_address_x',
            'take_address_y',
            'return_address_x',
            'return_address_y',
        ];


        $arrData = ['result'=>\frontend\components\ApiModule::CODE_SUCCESS, 'desc'=>\Yii::t('locale', 'Success')];
        do{
            foreach ($requiredFields as $key => $value) {
                //判断所传参数是否缺少
                if (!isset($params[$value])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;//4001
                    $arrData['desc']    = \Yii::t('locale', 'Missing required parameter!');//缺少必要参数
                    break;
                }

            }
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }

            $take_address_xy   = $params['take_address_x'].','.$params['take_address_y'];
            $return_address_xy = $params['return_address_x'].','.$params['return_address_y'];

            //最近还车门店
            $arr     = $this->getNearShopAndMetreByXY($return_address_xy);
            if($arr['result'] != 0){
                $arrData['result'] = -1;
                $arrData['desc']   = $arr['desc'];
                echo json_encode($arrData);
                exit();
            }
            if(isset($arr['distance'])){
                if ($arr['distance'] > 40) {
                    $arrData['result'] = -1;
                    $arrData['desc']   = '距离太远不接受送车服务';
                    echo json_encode($arrData);
                    exit();
                }else{
                    // $arrData['result'] = 0;
                    // $arrData['desc']   = '查询成功';
                    $arrData['arr']    = $arr;
                    // echo json_encode($arrData);
                }
            }else{
                $arrData['result'] = -1;
                $arrData['desc']   = '查询失败';
                echo json_encode($arrData);
                exit();
            }



            $distanceResult = \common\components\DistanceService::getDistanceByCoordinates($take_address_xy, $return_address_xy);

            if($distanceResult[1] == 'OK'){
                if($distanceResult[0] < 20){
                    $arrData['result'] = '-1';
                    $arrData['desc'] = \Yii::t('locale', 'The distance is too close to support service');
                    $arrData['address_km'] = $distanceResult[0];
                }else{
                    $arrData['address_km'] = $distanceResult[0];
                }
            }else{
                $arrData['result'] = '-1';
                $arrData['desc'] = '查询错误';
            }
        }while (0);

        echo json_encode($arrData);

    }

    /*最近门店返回*/
    public function actionTest()
    {
        $address = trim(\Yii::$app->request->post('address'));
        // $address='店口镇五金城21单元';
        // $address='金华婺城区城中街道胜利街凯旋楼';

        $aaa=$this->getNearShopAndMetre($address);
        // echo json_encode($aaa);die;
        if(isset($aaa['distance'])){
            if($aaa['distance'] > 40){
                $arrData['result'] = -1;
                $arrData['desc'] = '距离太远不接受送车服务';
                echo json_encode($arrData);
            }else{
                $aaa['result'] = 0;
                echo json_encode($aaa);
            }
        }else{
            echo json_encode($aaa['addressXandY']);
        }

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


            foreach ($arrImageRows as $row) {
                $arrImageList[] = [
                    'image' => \common\helpers\Utils::toFileAbsoluteUrl($row->image),
                    'link' => $row->href,
                    'title' => $row->name,
                    'content' => $row->remark,
                    'icon' => $row->icon,
                    // 'bind_param' => $row->bind_param,
                ];
            }
            
            $arrData['image_list'] = $arrImageList;
            
        } while (0);
        
        echo json_encode($arrData);
    }




    // 以下方法单程用车
    /**
     *@desc 判断最近门店是否有车提供单程用车
     *@param flag 8:舒适型 16：经济型 64 商务型
     *@param shopId
     *@param takeCarTime
     *@param returnCarTime
     *@license http://www.ykzc_beifen.com/app/carrental/frontend/web/index.php/neworder/is-have-car
     */
    public function actionIsHaveCar(){
        $postData = \Yii::$app->request->post();
        $time = time();

        /*$date=date('Y-m-d H:i:s',time());
        $b=json_encode($postData);
        file_put_contents('isHaveCar.txt',"$date'>'$b'\n",FILE_APPEND);*/

        $optionalFields = [
            'shopId',
            'flag',
            'takeCarTime',
            'returnCarTime',
        ];
        /*$postData = [
            'shopId'=>23,
            'flag'=>8,
            'takeCarTime'=>1521540778,
            'returnCarTime'=>1521555178,
        ];*/
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
            //判断所传参数是否缺少
            foreach ($optionalFields as $key => $value) {
                if (!isset($postData[$value])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;//4001
                    $arrData['desc'] = \Yii::t('locale', 'Missing required parameter!');//缺少必要参数
                    break;
                }
            }
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {//0
                break;
            }


            // 判断该门店是否开通单程租车
            $objOfficeInfo = \common\models\Pro_office::findById($postData['shopId']);
            if ($objOfficeInfo->isonewayoffice == 0) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;//1005
                $arrData['desc'] = \Yii::t('locale', 'Sorry, This shop did not open one-way car');
                break;
            }

            //如果第一次标签没传的话就循环看哪个标签车型有就传哪个
            if(empty($postData['flag'])){
                $arrFlag = [16,8,64];
                foreach ($arrFlag as $key => $value) {
                    $postData['flag'] = $value;
                    $arrData['flag']=$value;
                    $arrLeftCountByVehicleModel = \common\components\OrderModule::getVehicleOneLeftCountByTimeRegion($postData['shopId'], $postData['takeCarTime'], $postData['returnCarTime'],$postData['flag']);
                    if(!empty($arrLeftCountByVehicleModel)){
                        break;
                    }
                }
            }else{
                // 同一区域的所有门店
                //在库待租车辆系列数量
                $arrLeftCountByVehicleModel = \common\components\OrderModule::getVehicleOneLeftCountByTimeRegion($postData['shopId'], $postData['takeCarTime'], $postData['returnCarTime'],$postData['flag']);
            }
            if(!empty($arrLeftCountByVehicleModel)){
                //查询在库待租车辆的系列信息
                $cdb = \common\models\Pro_vehicle_model::find();
                $cdb->where(['id' => array_keys($arrLeftCountByVehicleModel)]);
                $arrRows = $cdb->all();
                foreach ($arrRows as $row) {
                    $car_type = $row->vehicleFlagArrayData();
                    if(!in_array($postData['flag'],$car_type)){
                        unset($arrLeftCountByVehicleModel[$row->id]);
                    }
                }
            }
            if(empty($arrLeftCountByVehicleModel)){
                $arrData['isHaveCar']=0;
            }else{
                $model_id = array_keys($arrLeftCountByVehicleModel);
                $arrData['model_id'] = $model_id[0];
                $arrData['isHaveCar']=1;
                $arrData['arrLeftCountByVehicleModel']=$arrLeftCountByVehicleModel;
            }
        }while (0);
        echo json_encode($arrData);

    }

    /**
     *@desc 单程租车需要支付的大概价格预览
     *@param flag 8:舒适型 16：经济型 64 商务型
     *@param car_id
     *@param price_type 6
     *@param start_time
     *@param end_time
     *@param address_take_car
     *@param price_take_car
     *@param sid
     *@param address_return_car
     *@param price_return_car
     *@param return_sid
     *@param address_km
     *@param ser_list
     *@param days
     *@param return_sid
     *@param return_sid
     *@license http://www.yikazc.com/app/carrental/frontend/web/index.php/neworder/one-way-order-preview
     */
    public function actionOneWayOrderPreview(){
        $params = \Yii::$app->request->post();
        $time   = time();
        $date   = date('Y-m-d H:i:s',time());
        $b      = json_encode($params);
        file_put_contents('OneWayOrderPreview.txt',"$date-->$b\n",FILE_APPEND);
        // $arrData = ['result'  => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        // $params['flag']=16;
        // $params['car_id']="50";//自己找
        // $params['days']="3";//处理小时
        // $params['end_time']=$time+3600*4;
        // $params['price_type']="6";
        // $params['return_sid']="26";
        // $params['ser_list']="0";
        // $params['sid']="23";
        // $params['start_time']=$time+3600;
        // $params['time']=$time;
        // $params['sign']="b9498845af575b96f9a9c7effdf48207";

        // $params['address_take_car']="金华市迎宾大道151号";
        // $params['address_return_car']="兰溪市府前路81号";
        // $params['price_take_car']="0";
        // $params['price_return_car']="0";
        // $params['address_km']="27";//两地公里数
        /*$arrLeftCountByVehicleModel = \common\components\OrderModule::getVehicleOneLeftCountByTimeRegion($params['sid'],$params['start_time'],$params['end_time'],$flag);

        foreach ($arrLeftCountByVehicleModel as $key => $value) {
            if($value){
                $params['car_id'] = $key;
            }
        }*/
        $arrData = \frontend\components\NewOrderService::processOneWayOrder($params,false);

        echo json_encode($arrData);
    }

    /**
     *@desc 单程租车订单提交
    */
    public function actionOneWayOrder(){
        $params = \Yii::$app->request->post();
        $arrData = \frontend\components\NewOrderService::processOneWayOrder($params,true);

        echo json_encode($arrData);
    }




}
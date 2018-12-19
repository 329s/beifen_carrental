<?php
namespace frontend\controllers;

/**
 * Description of PorderController
 *@desc pc端订单
 *@since 2017-11-10
 *@author sjj
 */
class PorderController extends \common\helpers\AuthorityController
{
	private $actionKey = \frontend\components\ApiModule::KEY;//ae027603f7aac1a3ae3e83edaf0abf33
    public function behaviors()
    {
        return [
        ];
    }
    public function init(){
        //去掉Yii2.0 csrf验证
        header('Access-Control-Allow-Origin:http://m.yikazc.com');
        $this->enableCsrfValidation = false;
	}

 //    public function beforeAction1($action) {
 //    	$preVerify = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
 //        if (!$preVerify[0]) {
 //            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_ON_MAINTENANCE, 'desc' => $preVerify[1]]);
 //            return false;
 //        }
 //        //return true;
 //        $arrParams = [];
 //        $sign = '';
 //        $params = \Yii::$app->request->get();
 //        foreach ($params as $k => $v) {
 //            if ($k == 'sign') {
 //                $sign = $v;
 //            }
 //            else {
 //                $arrParams[$k] = $v;
 //            }
 //        }
        
 //        $arrVerifys = [];
 //        ksort($arrParams);
 //        foreach ($arrParams as $k => $v) {
 //            $k = strval($k);
 //            $v = strval($v);
 //            $arrVerifys[] = "{$k}={$v}";
 //        }
 //        $arrVerifys[] = $this->actionKey;
        
 //        $mySign = md5(implode("|", $arrVerifys));
 //        if ($mySign == $sign) {
 //            return true;
 //        }
        
 //        \Yii::error("verify api failed str:".implode("|", $arrVerifys)." my_sign:{$mySign} sign:{$sign}", 'api');
 //        echo json_encode(['result'=> \frontend\components\ApiModule::CODE_INVALID_PACKAGE, 'desc' => \Yii::t('locale', 'Invalid access!')]);
 //        return false;
 //    }

    /**
    *@example    自驾专区车型列表
    *@param sid  店铺id
    *@param isTakeCarAddress  是否送车上门1是0否
	*@param address_take_car  上门送车地址
	*@param take_car_time    取车时间
	*@param return_car_time  还车时间
	*@param sign 安全性验证
    *@param [vehicle_type] 车型列表筛选 1,2,4,8,16,32,64  或者0：全部/不传(弃用)
	*@param [vehicle_flag] 车型列表标签筛选 1,2,4,8,16,32,64  或者0：全部/不传
	*@param [orderby] 价格排序 desc asc
	*@param [priceSort] 价格区间0-300  300-500  500-
	*@return $arrData 返回可租车辆列表
	*/
    public function actionRental_model_car_list(){

        $takeCarTime = \Yii::$app->request->post('take_car_time');
        $returnCarTime = \Yii::$app->request->post('return_car_time');
        $now = time();

        $isTakeCarAddress = intval(\Yii::$app->request->post('isTakeCarAddress'));
        if ($isTakeCarAddress) {
            $address_take_car = trim(\Yii::$app->request->post('address_take_car'));
            $address_take_car_info = $this->getNearShopAndMetre($address_take_car);
            $shopId = $address_take_car_info['sid'];
        }else{
            $shopId = intval(\Yii::$app->request->post('sid'));
        }




    	$arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
    	do{
    		if (empty($shopId) || empty($takeCarTime || empty($returnCarTime))) {
				$arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;//3001
				$arrData['desc']   = \Yii::t('locale', 'Invalid parameter!');//参数非法！
				break;
			}
			//在库待租车辆系列数量
            $arrLeftCountByVehicleModel = \common\components\OrderModule::getVehicleLeftCountByTimeRegion($shopId, $takeCarTime, $returnCarTime);
            $arrLeftCountByVehicleModel = array_filter($arrLeftCountByVehicleModel);

            // echo "<pre>";
            // print_r($arrLeftCountByVehicleModel);
            // echo "</pre>";die;
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

            // $arrData['arrLeftCountByVehicleModel'] =$arrLeftCountByVehicleModel;
            // $arrData['time']=$rentTimeData;
             $carriageArr  = \common\components\VehicleModule::getVehicleCarriagesArray();

            if(!empty($arrLeftCountByVehicleModel)){
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
                	//该车系线上线下价格
                	$feeOnline = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_APP, $shopId, $row->id);
                    $feeOffice = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE, $shopId, $row->id);
                    if($feeOffice || $feeOnline){
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
                            'carriage' => $row->carriage,//$carriageArr[$row->carriage],
                            'seat' => $row->seat,
                            'rent_deposit' =>$row->rent_deposit,
                            'consume' => $row->vehicleEmissionHumanText(),
                            'car_type' => $row->vehicleFlagArrayData(),
                            'brand' =>$row->brand,
                            'price_detail' => $this->pc_price_detail($row->id,$shopId),
                            //'car_type_v' => $row->vehicleFlagDisplayString(),
                            // 'car_type_ss' => $row->getVehicleFlagsArray(),
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
                            'rent_deposit'=> $row->rent_deposit,
                            'basic_insurance'=> $row->basic_insurance,
                            'poundage'=> $row->poundage,
                        ];
                    }

                }
            }

            $arrData['car_list'] = $arrVehicles;

    	}while (0);
    	echo json_encode($arrData);
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
        $addressXandY = \frontend\components\CommonModule::getXandYByaddress($address);
        if(isset($addressXandY['result'])){
            $arr['addressXandY']=$addressXandY;
            return $arr;
            exit();
        }
        // 得到所有门店
        $AllshopAddress = \frontend\components\CommonModule::getAllShopInfo();
        $distance=0;
        foreach ($AllshopAddress as $key => $value) {
            $distanceResult = \common\components\DistanceService::getDistanceByCoordinates($addressXandY['0'], $value['xy']);
            if ($distanceResult[0] < 0) {
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

            }
        }
        // 超过10公里收30元
        if($distance < 10){
                $arr['price'] = '0';
                $arr['distance'] = $distance;
                $arr['sid'] = intval($sid);
            }elseif ($distance > 20) {
                $arr['result'] = '1';
                $arr['desc'] = '该地还没有送车服务';
            }else{
                $arr['price'] = '30';
                $arr['distance'] = $distance;
                $arr['sid'] = intval($sid);
            }


        // if($distance>10){
        //     $price = '30';
        // }else{
        //     $price = '0';
        // }
        // $arr['price'] = $price;
        // $arr['distance'] = $distance;
        // $arr['sid'] = intval($sid);
        return $arr;
    }



    /**
	*@example 车系价格详情
	*@param vehicle_model_id  车系id 50
	*@param officeId  门店id 20
	*@param
	*@param sign
	*@return $arrData
	*/
	public function pc_price_detail($vehicle_model_id,$officeId){
		/*$vehicle_model_id = 50;
		$officeId = 20;*/
        $now = time();
        $takeCarTime = $now;
        $returnCarTime = $takeCarTime + 86400*22;
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
            // $arrData['carModelInfo'] = array(
            //         'rent_deposit' => $carModelInfo->rent_deposit,
            //         'property_text' => $carModelInfo->getPropertyHumanDisplayText(),
            //         'vehicle_model' => $carModelInfo->vehicle_model,
            // );
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
            foreach ($priceOnlineInfo['details'] as $key => $value) {
            	$days = $takeCarTime + 86400*($key);
            	$datepriceOnline[date('m',$days).date('d',$days)] = $value;
            	$datepriceOffice[date('m',$days).date('d',$days)] = $priceOfficeInfo['details'][$key];
            }
            $arrData['priceOnlineInfo'] = $datepriceOnline;
            $arrData['priceOfficeInfo'] = $datepriceOffice;



        }while (0);
        // echo json_encode($arrData);
        return $arrData;
	}

	/**
	*@desc 订单下单前
	*/
	public function actionPorder_preview(){
        $params = \Yii::$app->request->post();
        /*$params['car_id']="50";
        $time = time();
        $params['start_time']=$time;
        $params['end_time']=$time+86400*2;
        $params['days']="2.0";
        $params['price_type']="3";//
        $params['return_sid']="20";
        $params['ser_list']="1|2|12";
        $params['sid']="20";
        // $params['time']=time();
        $params['sign']="b9498845af575b96f9a9c7effdf48207";
        $params['address_take_car']="兰溪市汽车西站";
        $params['address_return_car']="金华南站";
        $params['isTakeCarAddress']="0";
        $params['isReturnCarAddress']="0";*/

        $isTakeCarAddress = $params['isTakeCarAddress'];
        $isReturnCarAddress = $params['isReturnCarAddress'];
        if($params['isTakeCarAddress'] == '1'){
            $address_take_car_info = $this->getNearShopAndMetre($params['address_take_car']);
            $params['price_take_car']=$address_take_car_info['price'];
            $params['sid'] = $address_take_car_info['sid'];
            unset($params['isTakeCarAddress']);
        }else{
            $params['price_take_car'] = 0;
            unset($params['isTakeCarAddress']);
        }
        if($params['isReturnCarAddress'] == '1'){
            $address_return_car_info = $this->getNearShopAndMetre($params['address_return_car']);
            $params['price_return_car']=$address_return_car_info['price'];
            $params['return_sid'] = $address_return_car_info['sid'];
            unset($params['isReturnCarAddress']);
        }else{
            $params['price_return_car'] = 0;
            unset($params['isReturnCarAddress']);
        }


        $service_price = \frontend\components\PorderService::service_price($params['car_id'],$params['sid'],$params['start_time'],$params['end_time']);
        $params['ser_list'] = $service_price['ser_list'];
        $arrData = \frontend\components\PorderService::processOrder($params, false);
        if(empty($arrData['result'])){
            $arrData['server'] = $service_price['server'];
        }
        $arrData['isTakeCarAddress'] = $isTakeCarAddress;
        $arrData['isReturnCarAddress'] = $isReturnCarAddress;

        echo json_encode($arrData);

    }

    /*提交订单保存数据库*/
    public function actionPorder() {
        $params = \Yii::$app->request->post();
        // $params['car_id']="50";
        /*$time = time();
        $params['start_time']=$time;
        $params['end_time']=$time+86400*2;
        $params['days']="2.0";
        $params['price_type']="1";//
        $params['return_sid']="30";
        $params['ser_list']="1|2|12";
        $params['sid']="20";
        // $params['time']=time();
        $params['sign']="b9498845af575b96f9a9c7effdf48207";
        $params['address_take_car']="兰溪市汽车西站";
        $params['address_return_car']="金华南站";
        $params['isTakeCarAddress']="0";
        $params['isReturnCarAddress']="0";*/

        $isTakeCarAddress = $params['isTakeCarAddress'];
        $isReturnCarAddress = $params['isReturnCarAddress'];
        if($params['isTakeCarAddress'] == '1'){
            $address_take_car_info = $this->getNearShopAndMetre($params['address_take_car']);
            $params['price_take_car']=$address_take_car_info['price'];
            $params['sid']=$address_take_car_info['sid'];
            unset($params['isTakeCarAddress']);
        }else{
            $params['price_take_car'] = 0;
            unset($params['isTakeCarAddress']);
        }
        if($params['isReturnCarAddress'] == '1'){
            $address_return_car_info = $this->getNearShopAndMetre($params['address_return_car']);
            $params['price_return_car']=$address_return_car_info['price'];
            $params['return_sid']=$address_return_car_info['sid'];
            unset($params['isReturnCarAddress']);
        }else{
            $params['price_return_car'] = 0;
            unset($params['isReturnCarAddress']);
        }


        // $service_price = \frontend\components\PorderService::service_price($params['car_id'],$params['sid'],$params['start_time'],$params['end_time']);
        // $params['ser_list'] = $service_price['ser_list'];
        $arrData = \frontend\components\PorderService::processOrder($params, true);
        // if(empty($arrData['result'])){
        //     $arrData['server'] = $service_price['server'];
        // }
        $arrData['isTakeCarAddress'] = $isTakeCarAddress;
        $arrData['isReturnCarAddress'] = $isReturnCarAddress;

        echo json_encode($arrData);
    }



    /*车型信息*/
    public function actionVehicle_model_detail()
    {
        $vehicle_model_id = \Yii::$app->request->get('vehicle_model_id');
        // $vehicle_model_id = 50;
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
            $arrData['vehicle_model_id'] = $vehicle_model_id;
            if (empty($vehicle_model_id)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;//3001
                $arrData['desc']   = \Yii::t('locale', 'Invalid parameter!');//参数非法！
                break;
            }

            // 车型信息
            $cdb1 = \common\models\Pro_vehicle_model::find();
            $cdb1->where(['id' => $vehicle_model_id]);
            $carModelInfo = $cdb1->one();
            // $arrData['carModelInfo'] = array(
            //         'rent_deposit' => $carModelInfo->rent_deposit,
            //         'property_text' => $carModelInfo->getPropertyHumanDisplayText(),
            //         'vehicle_model' => $carModelInfo->vehicle_model,
            // );
            $gearboxName = \common\components\VehicleModule::getVehicleGearboxTypesArray();
            $carModelInfo->gearbox = $gearboxName[$carModelInfo->gearbox];

            $carModelInfo->emission = \common\components\VehicleModule::getVehicleEmissionDisplayValue($carModelInfo->emission);

            $arrAirIntakeModes = \common\models\Pro_vehicle_model::getAirIntakeModesArray();
            $carModelInfo->air_intake_mode =$arrAirIntakeModes[$carModelInfo->air_intake_mode];

            $arrDrivingModes = \common\models\Pro_vehicle_model::getDrivingModesArray();
            $carModelInfo->driving_mode =$arrDrivingModes[$carModelInfo->driving_mode];

            $getChair = \common\models\Pro_vehicle_model::getChair();
            $carModelInfo->chair =$getChair[$carModelInfo->chair];

            $getYesOrNoArray = \common\models\Pro_vehicle_model::getYesOrNoArray();
            $carModelInfo->radar =$getYesOrNoArray[$carModelInfo->radar];
            $carModelInfo->gps =$getYesOrNoArray[$carModelInfo->gps];

            $arrVehicleTypes = \common\models\Pro_vehicle_model::getTypesArray();
            $carModelInfo->vehicle_type =$arrVehicleTypes[$carModelInfo->vehicle_type];
            $carModelInfo->vehicle_flag =$carModelInfo->vehicleFlagDisplayString();

            //
            $carriage = \common\components\VehicleModule::getVehicleCarriagesArray();
            $carModelInfo->carriage = $carriage[$carModelInfo->carriage];

            $seat = \common\components\VehicleModule::getVehicleSeatsArray();
            $carModelInfo->seat = $seat[$carModelInfo->seat];
            $arrData['carModelAllInfo'] = $carModelInfo->attributes;
        }while (0);
        echo json_encode($arrData);
    }


    /**
    *@example    订单列表
    *@return $arrData 返回订单列表
    */
    public function actionGet_order_list() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            $intPage = intval(\Yii::$app->request->get('page'));
            if ($intPage == 0){
                $intPage = 1;
            }
            $numPerPage = intval(\Yii::$app->request->get('rows'));
            $numPerPage = intval($numPerPage);
            if (!$numPerPage){
                $numPerPage = '500';
            }
            // 订单条件查询
            $status = trim(\Yii::$app->request->get('status'));
            $arrData['status'] = $status;
            // sjj
            if(empty($status)){//如果不存在则查询全部
                $arrConditionInfo = [];
            }else{
                $arrstatus = explode('|',$status);
                if(isset($arrstatus[1])){//1|1,1|2,2|2
                    if($arrstatus[1] == '1'){//预定未付status=1 and pay_source=1
                        $arrConditionInfo = ['and','status = 1  ','pay_source = 0'];
                    }else{//预定已付2 (status=1 or status=2) and pay_source=2  ['and', 'pay_source=2', ['or', 'status=1', 'status=2']]
                        $arrConditionInfo = ['and', 'pay_source>0', ['or', 'status=1', 'status=2']];
                    }
                }else{
                    $arrConditionInfo = ['status'=>$arrstatus];
                }
            }
            // sjj

            if (\Yii::$app->user->getIsGuest()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                break;
            }
            $cdb = \common\models\Pub_user::find();
            $cdb->where(['id' => \Yii::$app->user->id]);
            // $cdb->where(['id' => '13436']);
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
            
            $cdb->andwhere($arrConditionInfo);
            $cdb->orderBy("id desc");
            $count = $cdb->count();
            $pages = new \yii\data\Pagination(['totalCount'=>$count]);
            $pages->setPageSize($numPerPage);
            $pages->setPage($intPage - 1);
            $cdb->limit($pages->getLimit());
            $cdb->offset($pages->getOffset());
            $arrRows = $cdb->all();

            // $sql = $cdb->createCommand()->getRawSql();

            // $arrData['sql'] = $sql;
            $arrData['count'] = $count;

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
                $arrOrders_info = \frontend\components\PorderService::getOrderAttributes($objOrder, false, $arrVehicleModels, $arrOfficeNames);
                // $arrData['s'] = $arrOrders_info;
                switch ($objOrder->status) {
                    case 1:
                        if($objOrder->pay_source == '0'){
                            $arrOrders_info['status_name'] = '预定未付';
                        }else{
                            $arrOrders_info['status_name'] = '预定已付';
                        }
                        break;
                    case 2:
                        $arrOrders_info['status_name'] = '预定已付';
                        break;
                    case 10:
                        $arrOrders_info['status_name'] = '租赁中';
                        break;
                    case 100:
                        $arrOrders_info['status_name'] = '违章待查';
                        break;
                    case 101:
                        $arrOrders_info['status_name'] = '已完成';
                        break;
                    case 400:
                        $arrOrders_info['status_name'] = '已取消';
                        break;
                    default:
                        $arrOrders_info['status_name'] = '全部';
                        break;
                }
                $arrOrders[] = $arrOrders_info;
            }
            
            $arrData['orders'] = $arrOrders;
            
        }while (0);

        echo json_encode($arrData);
    }



    /**
    *@example    订单详情
    *@return $arrData 返回订单详情
    */
    public function actionOrder_detail() {
        $arrResult = \frontend\components\OrderService::getOrderBySerial(\Yii::$app->request->get('order_id'));
        $arrData = $arrResult[0];
        $objOrder = $arrResult[1];
        do
        {
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            
            $orderData = \frontend\components\PorderService::getOrderAttributes($objOrder, true);
            foreach ($orderData as $k => $v) {
                $arrData[$k] = $v;
            }
            
        }while (0);

        echo json_encode($arrData);
    }

    /**
    *@example    订单跟踪110050012395
    *@return $arrData 返回订单详情操作
    */
    public function actionOrder_change_way() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do{
            $serial = \Yii::$app->request->get('order_id');
            // $serial = '130024012054';
            $cdb = \common\models\Pro_vehicle_order_change_log::find(true);
            $cdb->select('id,serial,vehicle_model_id,status,pay_source,office_id_rent,office_id_return,start_time,end_time,new_end_time,created_at');
            $cdb->where(['serial' => $serial]);
            $cdb->orderBy("id asc");
            $arrRows = $cdb->asarray()->all();

            // 门店
            $ids[] = $arrRows[0]['office_id_rent'];
            $ids[] = $arrRows[0]['office_id_return'];
            $officeObj = \common\models\Pro_office::find();
            $officeObj->select('id,fullname,shortname,city_id');
            $officeObj->where(['id'=>$ids]);
            $officeObj->indexBy('id');
            $offices = $officeObj->asArray()->all();

            // 城市
            /*$cityObj = \common\models\Pro_city::find();
            foreach ($offices as $key => $value) {
                # code...
            }*/
            // $arrData['offices'] =$offices;
            foreach ($arrRows as $key => $value) {
                // 车型信息
                $cdb1 = \common\models\Pro_vehicle_model::find();
                $cdb1->where(['id' => $value['vehicle_model_id']]);
                $carModelInfo = $cdb1->one();
                $arrRows[$key]['carModelInfo'] = array(
                        'vehicle_model' => $carModelInfo->vehicle_model,
                        'property_text' => $carModelInfo->getPropertyHumanDisplayText(),
                );
                $arrRows[$key]['create_date'] = date('Y-m-d H:i:s',$value['created_at']);
                $arrRows[$key]['new_end_time_date'] = date('Y-m-d H:i:s',$value['new_end_time']);
                $arrRows[$key]['takeStore'] = $offices[$value['office_id_rent']]['fullname'];
                $arrRows[$key]['returnStore'] = $offices[$value['office_id_return']]['fullname'];
                switch ($value['status']) {
                    case '1':
                        if($value['pay_source'] == '0'){
                            $arrRows[$key]['status_name'] = '预定未付';
                        }else{
                            $arrRows[$key]['status_name'] = '预定已付';
                        }
                        break;
                    case '2':
                        if($value['pay_source'] == '0'){
                            $arrRows[$key]['status_name'] = '预定未付';
                        }else{
                            $arrRows[$key]['status_name'] = '预定已付';
                        }
                        break;
                    case '10':
                        if($value['new_end_time']>$arrRows[$key-1]['new_end_time']){
                            // $arrRows[$key]['status_name'] = '租赁中';
                            $arrRows[$key]['status_name'] = '续租中';
                            $arrRows[$key-1]['new_end_time'] = $value['new_end_time'];
                            // unset($arrRows[$key]);
                        }else{
                            $arrRows[$key]['status_name'] = '租赁中';
                        }
                        break;
                    case '100':
                        $arrRows[$key]['status_name'] = '违章待查';
                        break;
                    case '101':
                        $arrRows[$key]['status_name'] = '已完成';
                        break;
                    case '400':
                        $arrRows[$key]['status_name'] = '已取消';
                        break;
                    default:
                        $arrRows[$key]['status_name'] = '全部';
                        break;
                }
            }
            $arrData['Pro_vehicle_order_change_log'] = $arrRows;
            // $cdb1 = \common\models\Pro_vehicle_order::find(true);
            // $cdb1->select('id,serial,new_end_time');
            // $cdb1->where(['serial' => $serial]);
            // $cdb1->orderBy("id asc");
            // $order = $cdb1->asarray()->all();
            // $arrData['order'] = $order;


            // $cdb2 = \common\models\Pro_vehicle_order_relet::find(true);
            // $cdb2->select('id,serial,new_end_time');
            // $cdb2->where(['order_id' => 12054]);
            // $cdb2->orderBy("id asc");
            // $order_relet = $cdb2->asarray()->all();
            // $arrData['order_relet'] = $order_relet;
        }while (0);

        echo json_encode($arrData);
    }

    /*PC端取消订单*/
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


    /*PC端页面*/
    public function actionStore(){
        $code = \Yii::$app->request->get('city_code');
        $cdb = \common\models\Pro_city::find();
        $cdb->where(['city_code' => $code]);
        $objOrder = $cdb->one();


        $ho = \Yii::$app->params['pchost'];
        return $this->renderPartial('store',[
            'ho'       =>$ho,
            'objOrder' =>$objOrder,
        ]);
    }


    public function actionMtest(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do{
            $code = \Yii::$app->request->post('abc');
            $arrData['re'] = $code;
        }while (0);
        echo json_encode($arrData);
    }




}
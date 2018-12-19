<?php
namespace frontend\controllers;

/**
 * Description of PorderController
 *@desc pc端订单
 *@since 2017-11-10
 *@author sjj
 */
class WxapporderController extends \common\helpers\AuthorityController
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
        header('Access-Control-Allow-Origin:http://yikazc.com');
        header('Access-Control-Allow-Origin:*');
        $this->enableCsrfValidation = false;
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
        $isTakeCarAddress = $params['isTakeCarAddress'];
        $isReturnCarAddress = $params['isReturnCarAddress'];
        if($params['isTakeCarAddress'] == '1'){
            $address_take_car_info = $this->getNearShopAndMetre($params['address_take_car']);
            $params['price_take_car']=$address_take_car_info['price'];
            $params['sid'] = $address_take_car_info['sid'];
            unset($params['isTakeCarAddress']);
        }else{
            $params['price_take_car'] = 0;
            $params['address_take_car'] = '';
            unset($params['isTakeCarAddress']);
        }
        if($params['isReturnCarAddress'] == '1'){
            $address_return_car_info = $this->getNearShopAndMetre($params['address_return_car']);
            $params['price_return_car']=$address_return_car_info['price'];
            $params['return_sid'] = $address_return_car_info['sid'];
            unset($params['isReturnCarAddress']);
        }else{
            $params['price_return_car'] = 0;
            $params['address_return_car'] = '';
            unset($params['isReturnCarAddress']);
        }
        if($params['session_id']){
            $session_id = $params['session_id'];
            unset($params['session_id']);
        }else{
            $session_id = '';
        }


        $service_price = \frontend\components\PorderService::service_price($params['car_id'],$params['sid'],$params['start_time'],$params['end_time']);
        $params['ser_list'] = $service_price['ser_list'];
        $arrData = \frontend\components\WxapporderService::processOrder($params, false,$session_id);
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

        $isTakeCarAddress = $params['isTakeCarAddress'];
        $isReturnCarAddress = $params['isReturnCarAddress'];
        if($params['isTakeCarAddress'] == '1'){
            $address_take_car_info = $this->getNearShopAndMetre($params['address_take_car']);
            $params['price_take_car']=$address_take_car_info['price'];
            $params['sid'] = $address_take_car_info['sid'];
            unset($params['isTakeCarAddress']);
        }else{
            $params['price_take_car'] = 0;
            $params['address_take_car'] = '';
            unset($params['isTakeCarAddress']);
        }
        if($params['isReturnCarAddress'] == '1'){
            $address_return_car_info = $this->getNearShopAndMetre($params['address_return_car']);
            $params['price_return_car']=$address_return_car_info['price'];
            $params['return_sid'] = $address_return_car_info['sid'];
            unset($params['isReturnCarAddress']);
        }else{
            $params['price_return_car'] = 0;
            $params['address_return_car'] = '';
            unset($params['isReturnCarAddress']);
        }
        if($params['session_id']){
            $session_id = $params['session_id'];
            unset($params['session_id']);
        }else{
            $session_id = '';
        }



        $arrData = \frontend\components\WxapporderService::processOrder($params, true,$session_id);

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

            $sess_id = \Yii::$app->request->get('id');
            $sess_info = \frontend\components\CommonModule::getUserId($sess_id);
            // echo "<pre>";
            // print_r($sess_info);
            // echo "</pre>";die;

            if(empty($sess_info)){
                $uid =0;
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                break;
            }else{
                $uid = $sess_info;
            }
            /*if (\Yii::$app->user->getIsGuest()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                break;
            }*/
            $cdb = \common\models\Pub_user::find();
            // $cdb->where(['id' => \Yii::$app->user->id]);
            $cdb->where(['id' => $uid]);
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
                $arrOrders_info = \frontend\components\WxapporderService::getOrderAttributes($objOrder, false, $arrVehicleModels, $arrOfficeNames);
                    $arrOrders_info['start_time_date'] = date('m-d',$objOrder->start_time);
                    $arrOrders_info['start_time_time'] = date('H:i',$objOrder->start_time);
                    $arrOrders_info['end_time_date'] = date('m-d',$objOrder->end_time);
                    $arrOrders_info['end_time_time'] = date('H:i',$objOrder->end_time);
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
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do{
            $id = \Yii::$app->request->get('sess_id');
            if(empty($id)){
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = '参数非法！';
                // echo "string";
                // die;
                break;
            }
            $arrResult = \frontend\components\WxapporderService::getOrderBySerial(\Yii::$app->request->get('order_id'),$id);
            $arrData = $arrResult[0];
            $objOrder = $arrResult[1];
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            
            $orderData = \frontend\components\WxapporderService::getOrderAttributes($objOrder, true);
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
            if(empty($serial)){
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = '订单号不能为空';
                break;
            }
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
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => '取消成功'];
        do
        {
            $id = \Yii::$app->request->get('sess_id');
            if(empty($id)){
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = '参数非法！';
                break;
            }
            $arrResult = \frontend\components\WxapporderService::getOrderBySerial(\Yii::$app->request->get('order_id'),$id);
            $arrData = $arrResult[0];
            $objOrder = $arrResult[1];
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
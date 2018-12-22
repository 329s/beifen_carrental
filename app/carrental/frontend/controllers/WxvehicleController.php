<?php
namespace frontend\controllers;

/**
* 微信公众号车辆查询信息
*/
class WxvehicleController extends \yii\web\Controller
{
	public function behaviors()
	{
		return[];
	}
	public function init(){
		//去掉Yii2.0 csrf验证
        header('Access-Control-Allow-Origin:http://m.yikazc.com');
        header('Access-Control-Allow-Origin:http://yikazc.com');
        header('Access-Control-Allow-Origin:*');
        $this->enableCsrfValidation = false;
    }

    public function actionGet_vehicle_info(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
    		$plate_number = strtoupper(\Yii::$app->request->post('plate_number'));
    		// 车辆信息
    		$tblNameVehicle = \common\models\Pro_vehicle::tableName();
    		$tblNameVehicleModel = \common\models\Pro_vehicle_model::tableName();
    		$tblNameOffice = \common\models\Pro_office::tableName();

    		$queryVehicle = \common\models\Pro_vehicle::find();//vehicle_property
		    $queryVehicle->select(["{$tblNameVehicle}.*,
		    	{$tblNameVehicleModel}.emission,
		    	{$tblNameVehicleModel}.air_intake_mode,
		    	{$tblNameVehicleModel}.image_0,
		    	{$tblNameVehicleModel}.vehicle_model,
		    	{$tblNameVehicleModel}.carriage,
		    	{$tblNameVehicleModel}.gearbox,
		    	{$tblNameOffice}.fullname"]);
		    $queryVehicle->leftJoin($tblNameVehicleModel, "{$tblNameVehicle}.model_id = {$tblNameVehicleModel}.id");
		    $queryVehicle->leftJoin($tblNameOffice, "{$tblNameVehicle}.stop_office_id = {$tblNameOffice}.id");
		    $queryVehicle->where(["{$tblNameVehicle}.plate_number"=>$plate_number]);
		    $arrVehicleObjects = $queryVehicle->asArray()->one();

            if(!$arrVehicleObjects){
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = '查询失败';
                break;
            }
		    // $arrData['arrVehicleObjects'] = $arrVehicleObjects;

		    $vehicleInfo = $this->adorn_params($arrVehicleObjects);
		    $arrData['adorn_vehicle'] = $vehicleInfo;


    		// 当月开始时间和结束时间
    		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
			$endThismonth = mktime(23,59,59,date('m'),date('t'),date('Y'));

			// 当月该车所有订单
			$orderModel = \common\models\Pro_vehicle_order::find();
			$orderModel->where(['and', ['>=', 'start_time',$beginThismonth], ['<=', 'start_time',$endThismonth]]);
			$orderModel->andWhere(['and',['>=','status','10'],['<','status','400']]);
			$orderModel->andWhere(['=','vehicle_id',$arrVehicleObjects['id']]);
			$orders = $orderModel->asArray()->all();

    		// $arrData['orders'] = $orders;

    		$all_rent_days = array_sum(array_column($orders, 'rent_days'));
    		$all_total_amount = array_sum(array_column($orders, 'total_amount'));
    		$all_paid_amount = array_sum(array_column($orders, 'paid_amount'));
    		$arrData['adorn_vehicle']['count'] = count($orders);
    		$arrData['adorn_vehicle']['all_rent_days'] = $all_rent_days;
    		$arrData['adorn_vehicle']['all_total_amount'] = $all_total_amount;
            $arrData['adorn_vehicle']['all_paid_amount'] = $all_paid_amount;

            // 是否本月在租
    		$arrData['adorn_vehicle']['isrent'] = 0;
    		$officeModel = \common\models\Pro_office::find();
    		foreach ($orders as $key => $value) {
    			if($value['status'] == '10'){
    				$office = $officeModel->where(['id'=>$value['office_id_rent']])->one();
    				$arrData['adorn_vehicle']['office_id_rent'] = $office->fullname;
    				$arrData['adorn_vehicle']['address'] = $office->address;
                    $arrData['adorn_vehicle']['start_date'] = date('Y/m/d',$value['start_time']);
    				$arrData['adorn_vehicle']['start_time'] = date('H:i:s',$value['start_time']);
                    $arrData['adorn_vehicle']['new_end_date'] = date('Y/m/d',$value['new_end_time']);
    				$arrData['adorn_vehicle']['new_end_time'] = date('H:i:s',$value['new_end_time']);
    				$arrData['adorn_vehicle']['rent_per_day'] = intval($value['rent_per_day']);
    				$arrData['adorn_vehicle']['rent_days'] = $value['rent_days'];
                    $arrData['adorn_vehicle']['status'] = '在租';
    				$arrData['adorn_vehicle']['isrent'] = 1;
    			}
    		}
            // 是否上月延租
            if($arrData['adorn_vehicle']['isrent'] == 0){
                $order_Model = \common\models\Pro_vehicle_order::find();
                $order_Model->where(['<','start_time',$beginThismonth]);
                $order_Model->andWhere(['>','new_end_time',$beginThismonth]);
                $order_Model->andWhere(['=','status','10']);
                $order_Model->andWhere(['=','vehicle_id',$arrVehicleObjects['id']]);
                $order = $order_Model->asArray()->one();
                if($order){
                    $arrData['adorn_vehicle']['isrent'] = 1;//在租
                    $office = $officeModel->where(['id'=>$order['office_id_rent']])->one();
                    $arrData['adorn_vehicle']['office_id_rent'] = $office->fullname;
                    $arrData['adorn_vehicle']['address'] = $office->address;
                    $arrData['adorn_vehicle']['start_date'] = date('Y/m/d',$order['start_time']);
                    $arrData['adorn_vehicle']['start_time'] = date('H:i:s',$order['start_time']);
                    $arrData['adorn_vehicle']['new_end_date'] = date('Y/m/d',$order['new_end_time']);
                    $arrData['adorn_vehicle']['new_end_time'] = date('H:i:s',$order['new_end_time']);
                    $arrData['adorn_vehicle']['rent_per_day'] = intval($order['rent_per_day']);
                    $arrData['adorn_vehicle']['rent_days'] = $order['rent_days'];
                    $arrData['adorn_vehicle']['status'] = '上月续租';

                    if($order['new_end_time']>$endThismonth){
                        $days = date('t');
                        $arrData['adorn_vehicle']['all_rent_days'] += $days;
                        $arrData['adorn_vehicle']['all_total_amount'] += (intval($order['rent_per_day']) * $days);
                        $arrData['adorn_vehicle']['count']++;
                    }else{
                        $days = date('d',$order['new_end_time']);
                        $arrData['adorn_vehicle']['all_rent_days'] += $days;
                        $arrData['adorn_vehicle']['all_total_amount'] += (intval($order['rent_per_day']) * $days);
                        $arrData['adorn_vehicle']['count']++;

                    }

                }
            }

            $pageApiParams = $this->getUrl($arrVehicleObjects['locator_device']);
            $arrData['adorn_vehicle']['url_is_null'] = $pageApiParams['url_is_null'];
            $arrData['adorn_vehicle']['url'] = $pageApiParams['url'];

        }while (0);
        echo json_encode($arrData);
    }

    /*数据处理*/
    public function adorn_params($params='')
    {
    	$getVehicleStatusArray = \common\components\VehicleModule::getVehicleStatusArray();

    	$getVehiclePropertiesArray = \common\components\VehicleModule::getVehiclePropertiesArray();

    	$getVehicleCarriagesArray = \common\components\VehicleModule::getVehicleCarriagesArray();
    	$getVehicleGearboxTypesArray = \common\components\VehicleModule::getVehicleGearboxTypesArray();
    	$getVehicleCarriagesArray = \common\components\VehicleModule::getVehicleCarriagesArray();
    	$air_intake_mode_Array = array('1'=>'L','2'=>'T');
    	$emission = $params['emission']/1000;
    	$air_intake_mode = $air_intake_mode_Array[$params['air_intake_mode']];

    	$data['image'] = \common\components\VehicleModule::getVehicleModelImageUrl($params['image_0']);
    	$data['status'] = $getVehicleStatusArray[$params['status']];
    	$data['vehicle_model'] = $params['vehicle_model'];
    	$data['vehicle_property'] = $getVehiclePropertiesArray[$params['vehicle_property']];
    	$data['text'] = $params['plate_number'].'|'.$getVehicleCarriagesArray[$params['carriage']].'|'.$getVehicleGearboxTypesArray[$params['gearbox']].'|'.$emission.$air_intake_mode;
    	$data['stop_office_id'] = $params['fullname'];
    	return $data;
    }


    /*根据手机号码和验证码来查询车辆*/
    public function actionGet_vehicles(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
            // $mobile = \Yii::$app->request->post('mobile');
            // $code = \Yii::$app->request->post('code');
            $params = \Yii::$app->request->post();
            $requiredFields = ['mobile', 'code'];
            foreach ($requiredFields as $k) {
                if (!isset($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                    $arrData['desc'] = \Yii::t('locale', 'Missing required parameter!');
                    break;
                }
            }
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }

            $mobile = $params['mobile'];
            $code = $params['code'];
            $zone = (isset($params['zone']) ? $params['zone'] : '86');
            // $verifyResult = \common\components\UserModule::verifyUserPhoneSmsCode($mobile, $code, $zone);
            // if (!$verifyResult[0]) {
            //     $arrData['result'] = \frontend\components\ApiModule::CODE_PHONE_CODE_INVALID;
            //     $arrData['desc'] = $verifyResult[1];
            //     break;
            // }
            // 查询车辆
            $vehicleModel = \common\models\Pro_vehicle::find();
            $vehicleModel->select('id,plate_number,mobile');
            $vehicleModel->where(['=','mobile',$mobile]);
            $vehicles = $vehicleModel->asArray()->all();
            if($vehicles){
                $arrData['vehicles'] = $vehicles;
            }else{
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = '没有车辆记录';
                break;
            }

        }while(0);
        echo json_encode($arrData);
    }

    /**
    *@desc 汽车在线跟踪url拼接
    */
    public function getUrl($locator_device=''){
        if(empty($locator_device)){
            $arrData['url'] = '';
            $arrData['url_is_null'] = '1';
            return $arrData;
        }else{
            //$url = 'http://pageapi.gpsoo.net/third?method=jump&appkey=dbd77ada93ca392d9f2712d6f2beb6ca&account=aaa&page=tracking&target=252411111122222';
            $pageApiParams = \Yii::$app->params['pageApi'];
            $arrData['url_is_null'] = '0';
            $url = 'https://pageapi.gpsoo.net/third?method=jump&appkey='.$pageApiParams['appkey'].'&account='.$pageApiParams['account'].'&page=tracking&target='.$locator_device;
            $arrData['url'] = $url;
            return $arrData;
        }


    }



    public function actionGet_vehicle_info_id(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
            $vehicle_id = strtoupper(\Yii::$app->request->post('vehicle_id'));
            // 车辆信息
            $tblNameVehicle = \common\models\Pro_vehicle::tableName();
            $tblNameVehicleModel = \common\models\Pro_vehicle_model::tableName();
            $tblNameOffice = \common\models\Pro_office::tableName();

            $queryVehicle = \common\models\Pro_vehicle::find();//vehicle_property
            $queryVehicle->select(["{$tblNameVehicle}.*,
                {$tblNameVehicleModel}.emission,
                {$tblNameVehicleModel}.air_intake_mode,
                {$tblNameVehicleModel}.image_0,
                {$tblNameVehicleModel}.vehicle_model,
                {$tblNameVehicleModel}.carriage,
                {$tblNameVehicleModel}.gearbox,
                {$tblNameOffice}.fullname"]);
            $queryVehicle->leftJoin($tblNameVehicleModel, "{$tblNameVehicle}.model_id = {$tblNameVehicleModel}.id");
            $queryVehicle->leftJoin($tblNameOffice, "{$tblNameVehicle}.stop_office_id = {$tblNameOffice}.id");
            $queryVehicle->where(["{$tblNameVehicle}.id"=>$vehicle_id]);
            $arrVehicleObjects = $queryVehicle->asArray()->one();

            if(!$arrVehicleObjects){
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = '查询失败';
                break;
            }
            // $arrData['arrVehicleObjects'] = $arrVehicleObjects;

            $vehicleInfo = $this->adorn_params($arrVehicleObjects);
            $arrData['adorn_vehicle'] = $vehicleInfo;


            // 当月开始时间和结束时间
            $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
            $endThismonth = mktime(23,59,59,date('m'),date('t'),date('Y'));

            // 当月该车所有订单
            $orderModel = \common\models\Pro_vehicle_order::find();
            $orderModel->where(['and', ['>=', 'start_time',$beginThismonth], ['<=', 'start_time',$endThismonth]]);
            $orderModel->andWhere(['and',['>=','status','10'],['<','status','400']]);
            $orderModel->andWhere(['=','vehicle_id',$arrVehicleObjects['id']]);
            $orders = $orderModel->asArray()->all();

            // $arrData['orders'] = $orders;

            $all_rent_days = array_sum(array_column($orders, 'rent_days'));
            $all_total_amount = array_sum(array_column($orders, 'total_amount'));
            $all_paid_amount = array_sum(array_column($orders, 'paid_amount'));
            $arrData['adorn_vehicle']['count'] = count($orders);
            $arrData['adorn_vehicle']['all_rent_days'] = $all_rent_days;
            $arrData['adorn_vehicle']['all_total_amount'] = $all_total_amount;
            $arrData['adorn_vehicle']['all_paid_amount'] = $all_paid_amount;

            // 是否本月在租
            $arrData['adorn_vehicle']['isrent'] = 0;
            $officeModel = \common\models\Pro_office::find();
            foreach ($orders as $key => $value) {
                if($value['status'] == '10'){
                    $office = $officeModel->where(['id'=>$value['office_id_rent']])->one();
                    $arrData['adorn_vehicle']['office_id_rent'] = $office->fullname;
                    $arrData['adorn_vehicle']['address'] = $office->address;
                    $arrData['adorn_vehicle']['start_date'] = date('Y/m/d',$value['start_time']);
                    $arrData['adorn_vehicle']['start_time'] = date('H:i:s',$value['start_time']);
                    $arrData['adorn_vehicle']['new_end_date'] = date('Y/m/d',$value['new_end_time']);
                    $arrData['adorn_vehicle']['new_end_time'] = date('H:i:s',$value['new_end_time']);
                    $arrData['adorn_vehicle']['rent_per_day'] = intval($value['rent_per_day']);
                    $arrData['adorn_vehicle']['rent_days'] = $value['rent_days'];
                    $arrData['adorn_vehicle']['status'] = '在租';
                    $arrData['adorn_vehicle']['isrent'] = 1;
                }
            }
            // 是否上月延租
            if($arrData['adorn_vehicle']['isrent'] == 0){
                $order_Model = \common\models\Pro_vehicle_order::find();
                $order_Model->where(['<','start_time',$beginThismonth]);
                $order_Model->andWhere(['>','new_end_time',$beginThismonth]);
                $order_Model->andWhere(['=','status','10']);
                $order_Model->andWhere(['=','vehicle_id',$arrVehicleObjects['id']]);
                $order = $order_Model->asArray()->one();
                if($order){
                    $arrData['adorn_vehicle']['isrent'] = 1;//在租
                    $office = $officeModel->where(['id'=>$order['office_id_rent']])->one();
                    $arrData['adorn_vehicle']['office_id_rent'] = $office->fullname;
                    $arrData['adorn_vehicle']['address'] = $office->address;
                    $arrData['adorn_vehicle']['start_date'] = date('Y/m/d',$order['start_time']);
                    $arrData['adorn_vehicle']['start_time'] = date('H:i:s',$order['start_time']);
                    $arrData['adorn_vehicle']['new_end_date'] = date('Y/m/d',$order['new_end_time']);
                    $arrData['adorn_vehicle']['new_end_time'] = date('H:i:s',$order['new_end_time']);
                    $arrData['adorn_vehicle']['rent_per_day'] = intval($order['rent_per_day']);
                    $arrData['adorn_vehicle']['rent_days'] = $order['rent_days'];
                    $arrData['adorn_vehicle']['status'] = '上月续租';

                    if($order['new_end_time']>$endThismonth){
                        $days = date('t');
                        $arrData['adorn_vehicle']['all_rent_days'] += $days;
                        $arrData['adorn_vehicle']['all_total_amount'] += (intval($order['rent_per_day']) * $days);
                        $arrData['adorn_vehicle']['count']++;
                    }else{
                        $days = date('d',$order['new_end_time']);
                        $arrData['adorn_vehicle']['all_rent_days'] += $days;
                        $arrData['adorn_vehicle']['all_total_amount'] += (intval($order['rent_per_day']) * $days);
                        $arrData['adorn_vehicle']['count']++;

                    }

                }
            }

            $pageApiParams = $this->getUrl($arrVehicleObjects['locator_device']);
            $arrData['adorn_vehicle']['url_is_null'] = $pageApiParams['url_is_null'];
            $arrData['adorn_vehicle']['url'] = $pageApiParams['url'];
            $arrData['fx_data']['imgSrc'] = $arrData['adorn_vehicle']['image'];
            $arrData['fx_data']['car_name'] = $arrData['adorn_vehicle']['vehicle_model'];
            $arrData['fx_data']['car_type'] = $arrData['adorn_vehicle']['vehicle_property'];
            $arrData['fx_data']['car_info'] = $arrData['adorn_vehicle']['text'];
            $arrData['fx_data']['flag'] = $arrData['adorn_vehicle']['status'];
            $arrData['fx_data']['car_stop'] = $arrData['adorn_vehicle']['stop_office_id'];
            $arrData['fx_data']['start_date'] = $arrData['adorn_vehicle']['start_date'];
            $arrData['fx_data']['start_time'] = $arrData['adorn_vehicle']['start_time'];
            $arrData['fx_data']['address'] = $arrData['adorn_vehicle']['address'];
            $arrData['fx_data']['rent_shop'] = $arrData['adorn_vehicle']['office_id_rent'];
            $arrData['fx_data']['new_end_date'] = $arrData['adorn_vehicle']['new_end_date'];
            $arrData['fx_data']['new_end_time'] = $arrData['adorn_vehicle']['new_end_time'];
            $arrData['fx_data']['rent_days'] = $arrData['adorn_vehicle']['rent_days'];
            $arrData['fx_data']['count'] = $arrData['adorn_vehicle']['count'];
            $arrData['fx_data']['all_rent_days'] = $arrData['adorn_vehicle']['all_rent_days'];
            $arrData['fx_data']['all_total_amount'] = $arrData['adorn_vehicle']['all_total_amount'];
            $arrData['fx_data']['rent_per_day'] = $arrData['adorn_vehicle']['rent_per_day'];



        }while (0);
        echo json_encode($arrData);

    }




}

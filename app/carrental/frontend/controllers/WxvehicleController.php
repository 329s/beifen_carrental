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
    		$plate_number = \Yii::$app->request->post('plate_number');
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

    		// 是否在租
    		$officeModel = \common\models\Pro_office::find();
    		foreach ($orders as $key => $value) {
    			if($value['status'] == '10'){
    				$office = $officeModel->where(['id'=>$value['office_id_rent']])->one();
    				$arrData['adorn_vehicle']['office_id_rent'] = $office->fullname;
    				$arrData['adorn_vehicle']['address'] = $office->address;
    				$arrData['adorn_vehicle']['start_time'] = $value['start_time'];
    				$arrData['adorn_vehicle']['new_end_time'] = $value['new_end_time'];
    				$arrData['adorn_vehicle']['rent_per_day'] = $value['rent_per_day'];
    				$arrData['adorn_vehicle']['rent_days'] = $value['rent_days'];
    				$arrData['adorn_vehicle']['status'] = '在租';
    			}
    		}


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
}

<?php
namespace backend\controllers;

use common\helpers;

/**
 * Api controller
 */
class ApiController extends \common\helpers\BasicController
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGet_imagefield_html() {
        $name = \Yii::$app->request->getParam('field');
        $imageFieldHtml = \common\helpers\CEasyUI::inputField(\common\helpers\CMyHtml::INPUT_IMAGEFIELD, $name, '', '', ['readonly'=>false, 'editable'=>false, 'style'=>"width:200px", 'width'=>'200px', 'height'=>'140px', 'src'=>'', 'fileSize'=>'600KB']);
        echo $imageFieldHtml;
        exit(0);
    }

    public function actionGaode_get_cityid() {
        $cityName = \Yii::$app->request->getParam('city');
        $url = 'http://restapi.amap.com/v3/config/district';

        $params = array(
            'key' => \Yii::$app->params['map.gaode.appkey'],
            'keywords' => $cityName,
            'showbiz' => false,
        );
        
        $arrData = [];
        $result = \common\helpers\Utils::queryUrlGet($url, $params);
        if ($result[0] == 200) {
            $response = $result[1];
            $oResult = json_decode($response);
            if (isset($oResult->status) && $oResult->status == 1) {
                if (isset($oResult->districts)) {
                    foreach ($oResult->districts as $district) {
                        $arrData[] = [
                            'value' => $district->citycode,
                            'text' => $district->name."({$district->citycode})",
                        ];
                    }
                }
                \Yii::info("got gode city id result: {$response}", 'sdk');
                //return array(true, \Yii::t('locale', 'Success'));
            }
            else if (isset($oResult->info)) {
                \Yii::warning("get gaode cityid by city:{$cityName} failed with http error:{$oResult->status} errmsg:{$oResult->info}.", 'sdk');
                MyFunction::funEchoJSON_Ajax($oResult->info, 300);
            }
        }
        else {
            \Yii::error("get gaode cityid by city:{$cityName} failed with http error:{$result[0]} errmsg:{$result[1]}.", 'sdk');
            MyFunction::funEchoJSON_Ajax($result[1], 300);
        }
        
        echo json_encode($arrData);
    }
    
    public function actionGaode_get_route() {
        $cityName = \Yii::$app->request->getParam('city');
        $url = 'http://restapi.amap.com/v3/direction/driving';

        $params = array(
            'key' => \Yii::$app->params['map.gaode.appkey'],
            'origin' => 'geo_x,geo_y',
            'destination' => 'geo_x,geo_y',
        );
        
    }
    
    public function actionService_price_between_office() {
        $vehicleModelId = intval(\Yii::$app->request->getParam('vehicle_model'));
        $office1 = intval(\Yii::$app->request->getParam('office1'));
        $office2 = intval(\Yii::$app->request->getParam('office2'));
        
        $arrData = ['result' => 0, 'desc' => \Yii::t('locale', 'Success'), 'price'=>0];
        
        $resultPriceDiffOffice = \common\components\OrderModule::getPriceByDistanceOfOffices($vehicleModelId, $office1, $office2);
        if ($resultPriceDiffOffice['result'] != 0) {
            $arrData['result'] = $resultPriceDiffOffice['result'];
            $arrData['desc'] = $resultPriceDiffOffice['desc'];
        }
        else {
            $arrData['price'] = $resultPriceDiffOffice['price'];
        }
        
        echo json_encode($arrData);
    }
    
    public function actionService_price_by_address2office() {
        $vehicleModelId = intval(\Yii::$app->request->getParam('vehicle_model'));
        $address = \Yii::$app->request->getParam('address');
        $office = intval(\Yii::$app->request->getParam('office'));
        
        $arrData = ['result' => 0, 'desc' => \Yii::t('locale', 'Success'), 'price'=>0];
        $resultPriceTakeCar = \common\components\OrderModule::getPriceByAddressToOffice($vehicleModelId, $address, $office);
        if ($resultPriceTakeCar['result'] != 0) {
            $arrData['result'] = $resultPriceTakeCar['result'];
            $arrData['desc'] = $resultPriceTakeCar['desc'];
        }
        else {
            $arrData['price'] = $resultPriceTakeCar['price'];
        }
        
        echo json_encode($arrData);
    }
    
    public function actionOptional_service_prices() {
        $officeId = intval(\Yii::$app->request->getParam('office'));
        $arrRows = \common\models\Pro_service_price::findAllServicePrices($officeId);
        $arrData = [];
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->getActualUnitPrice();
        }
        echo json_encode($arrData);
    }
    
    public function actionSearchVehicleModel() {
        $name = \Yii::$app->request->getParam('name');
        $arrData = [];
        
        if ($name) {
            $cdb = \common\models\Pro_vehicle_model::find();
            $cdb->select(['id', 'vehicle_model', 'poundage', 'basic_insurance', 
                'rent_deposit', 'overtime_price_personal']);
            $cdb->where(['like', 'vehicle_model', $name]);
            $cdb->limit(10);
            $arrRows = $cdb->asArray()->all();
            
            foreach ($arrRows as $row) {
                $arrData[] = ['id'=>$row['id'], 'text' => $row['vehicle_model'], 
                    'poundage'=>$row['poundage'],
                    'basic_insurance'=>$row['basic_insurance'], 
                    'rent_deposit'=>$row['rent_deposit'], 
                    'overtime_price_personal'=>$row['overtime_price_personal']
                ];
            }
        }
        
        echo json_encode($arrData);
    }
    
}
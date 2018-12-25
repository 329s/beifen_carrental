<?php

namespace backend\controllers;

use common\helpers\MyFunction;

/**
 * Description of PrintController
 *
 * @author kevin
 */
class PrintController  extends \backend\components\AuthorityController
{
    
    public function actionBooking_vehicle_order() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        if (!$orderId) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
        if (!$objOrder) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Order')]), 300);
        }
        $objVehicle = \common\models\Pro_vehicle::findById($objOrder->vehicle_id);
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]), 300);
        }
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
        if (!$objVehicleModel) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle model')]), 300);
        }else{
            //燃油
            $oil_label_arr = \common\components\VehicleModule::getVehicleOilLabelsArray();
            $objVehicleModel->oil_label = $oil_label_arr[$objVehicleModel->oil_label];
        }
        
        $arrData = [
            'objOrder'=>$objOrder,
            'objVehicle' => $objVehicle,
            'objVehicleModel' => $objVehicleModel,
        ];
        
        return $this->renderPartial('booking_vehicle_order', $arrData);
    }
    
    public function actionDispatch_vehicle_order() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        if (!$orderId) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
        if (!$objOrder) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Order')]), 300);
        }
        $objUserInfo = \common\models\Pub_user_info::findById($objOrder->user_id);
        if (!$objUserInfo) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Customer')]), 300);
        }
        $objVehicle = \common\models\Pro_vehicle::findById($objOrder->vehicle_id);
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]), 300);
        }
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
        if (!$objVehicleModel) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle model')]), 300);
        }else{
            //燃油
            $oil_label_arr = \common\components\VehicleModule::getVehicleOilLabelsArray();
            $objVehicleModel->oil_label = $oil_label_arr[$objVehicleModel->oil_label];
        }
        
        $arrData = [
            'objOrder'=>$objOrder,
            'objUserInfo'=>$objUserInfo,
            'objVehicle' => $objVehicle,
            'objVehicleModel' => $objVehicleModel,
        ];
        
        return $this->renderPartial('dispatch_vehicle_order', $arrData);
    }
    
    public function actionValidation_vehicle_order() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        if (!$orderId) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
        if (!$objOrder) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Order')]), 300);
        }
        $objVehicle = \common\models\Pro_vehicle::findById($objOrder->vehicle_id);
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]), 300);
        }
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
        if (!$objVehicleModel) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle model')]), 300);
        }else{
            //燃油
            $oil_label_arr = \common\components\VehicleModule::getVehicleOilLabelsArray();
            $objVehicleModel->oil_label = $oil_label_arr[$objVehicleModel->oil_label];
        }
        $objPickupValidation = null;
        $objDropoffValidation = null;
        if ($objOrder->validation_id_0) {
            $objPickupValidation = \common\models\Pro_vehicle_validation_order::findById($objOrder->validation_id_0);
        }
        if ($objOrder->validation_id_1) {
            $objDropoffValidation = \common\models\Pro_vehicle_validation_order::findById($objOrder->validation_id_1);
        }
        
        $arrData = [
            'objOrder'=>$objOrder,
            'objVehicle' => $objVehicle,
            'objVehicleModel' => $objVehicleModel,
            'objPickupValidation' => $objPickupValidation,
            'objDropoffValidation' => $objDropoffValidation,
        ];
        
        return $this->renderPartial('validation_vehicle_order', $arrData);
    }
    
    public function actionSettlement_vehicle_order() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        if (!$orderId) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
        if (!$objOrder) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Order')]), 300);
        }
        $objVehicle = \common\models\Pro_vehicle::findById($objOrder->vehicle_id);
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]), 300);
        }
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
        if (!$objVehicleModel) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle model')]), 300);
        }else{
            //燃油
            $oil_label_arr = \common\components\VehicleModule::getVehicleOilLabelsArray();
            $objVehicleModel->oil_label = $oil_label_arr[$objVehicleModel->oil_label];
        }
        
        $arrData = [
            'objOrder'=>$objOrder,
            'objVehicle' => $objVehicle,
            'objVehicleModel' => $objVehicleModel,
        ];
        
        return $this->renderPartial('settlement_vehicle_order', $arrData);
    }
    
    public function actionRelet_vehicle_order() {
        $reletOrderId = intval(\Yii::$app->request->getParam('id'));
        if (!$reletOrderId) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objOrderRelet = \common\models\Pro_vehicle_order_relet::findById($reletOrderId);
        if (!$objOrderRelet) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Order')]), 300);
        }
        
        $objOrder = \common\models\Pro_vehicle_order::findById($objOrderRelet->order_id);
        if (!$objOrder) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Order')]), 300);
        }
        $objVehicle = \common\models\Pro_vehicle::findById($objOrder->vehicle_id);
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]), 300);
        }
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
        if (!$objVehicleModel) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle model')]), 300);
        }
        
        $arrData = [
            'objOrder'=>$objOrder,
            'objVehicle' => $objVehicle,
            'objVehicleModel' => $objVehicleModel,
            'objOrderRelet'=>$objOrderRelet,
        ];
        
        return $this->renderPartial('relet_vehicle_order', $arrData);
    }
    
}
    
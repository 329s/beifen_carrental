<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\controllers;

/**
 * Description of RentalController
 *
 * @author kevin
 */
class RentalController extends \backend\components\AuthorityController
{
    public function getView() {
        $prefix = \backend\components\AdminHtmlService::getViewPrefix();
        if ($prefix) {
            return \Yii::createObject([
                'class' => \common\components\ViewExtend::className(),
                'prefix' => $prefix,
            ]);
        }
        return parent::getView();
    }
    
    public function actionWaiting_index() {
        $arrData = ['vehicleStatus' => \common\models\Pro_vehicle::STATUS_NORMAL];
        return $this->renderPartial('waiting_index', $arrData);
    }
    
    public function actionWaiting_book() {
        $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
        $orderId = intval(\Yii::$app->request->getParam('order_id'));
        $arrData = [
            'vehicleId' => $vehicleId,
            'orderId' => $orderId,
        ];
        return $this->renderPartial('booking', $arrData);
    }
    
    public function actionBooking() {
        $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
        $orderId = intval(\Yii::$app->request->getParam('order_id'));
        $arrData = [
            'vehicleId' => $vehicleId,
            'orderId' => $orderId,
        ];
        return $this->renderPartial('booking', $arrData);
    }
    
    
}

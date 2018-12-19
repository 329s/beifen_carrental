<?php

namespace console\controllers;

class OrderController extends \yii\console\Controller
{
    
    public function actionCheckTimeups() {
        if (true) {
            return;
        }
        $timeup = time() - \common\components\OrderModule::ORDER_MAX_WAITING_SECONDS;
        
        \common\models\Pro_vehicle_order::updateAll(
            ['status' => \common\models\Pro_vehicle_order::STATUS_CANCELLED],
            [
                'and',
                'status' => \common\models\Pro_vehicle_order::STATUS_WAITING,
                'vehicle_id' => 0,
                ['<=', 'paid_amount', 0],
                ['<', 'created_at', $timeup]
            ]);
        
    }
    
    public function actionCheckViolation() {
        $cdb = \common\models\Pro_vehicle_order::find(true);
        $cdb->where(['status' => \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING]);
        
        $arrRows = $cdb->all();
        $arrOrdersBuVehicleId = [];
        foreach ($arrRows as $row) {
            if (isset($arrOrdersBuVehicleId[$row->vehicle_id])) {
                $arrOrdersBuVehicleId[$row->vehicle_id][$row->id] = $row;
            }
            else {
                $arrOrdersBuVehicleId[$row->vehicle_id] = [$row->id => $row];
            }
        }
        
        if (!empty($arrOrdersBuVehicleId)) {
            $cdb = \common\models\Pro_vehicle::find(true);
            $cdb->where(['id' => array_keys($arrOrdersBuVehicleId)]);
            $arrRows = $cdb->all();
            
            foreach ($arrRows as $row) {
                // vehicles
            }
        }
        
    }
    
    public function actionUpdateVehicleMaintenanceCheckPoint() {
        /*
        $cdb = \common\models\Pro_vehicle::find(true);
        $arrRows = $cdb->all();
        foreach ($arrRows as $row) {
            if ($row->updateNextMaintenanceCheckPoint()) {
                $row->save();
            }
        }
         * 
         */
    }
    
}
<?php

namespace backend\components;

/**
 * Description of NoticeService
 *
 * @author kevin
 */
class NoticeService extends BaseService
{
    
    public static function currentlyStatus()
    {
        $findKeys = ['order-waiting-count', 'order-dispatching-count', 'order-returning-count', 
            'vehicle-renewal-count', 'vehicle-annual-inspection-count',
            'vehicle-upkeep-by-mileage-count', 'vehicle-upkeep-by-time-count', 'vehicle-maintenance-count',
            'vehicle-saled-count'];
        $arrData = [];
        if (\Yii::$app->user->isGuest) {
            foreach ($findKeys as $k) {
                $arrData[$k] = 0;
            }
            return $arrData;
        }
        $authOfficeId = AdminModule::getAuthorizedOfficeId();
        foreach ($findKeys as $k) {
            $val = \Yii::$app->cache->get($k.'-'.$authOfficeId);
            // \Yii::$app->cache->flush();
            if ($val === false) {
            // if (true) {
                return self::reloadCurrentlyStatus();
            }
            $arrData[$k] = $val;
        }
        return $arrData;
    }
    
    public static function reloadCurrentlyStatus()
    {
        $curTime = time();
        $orderWarningDuration = 86400;
        $authOfficeId = AdminModule::getAuthorizedOfficeId();
        $arrData = [];
        $query = \common\models\Pro_vehicle_order::find();
        $query->select(['id', 'serial', 'type']);
        $newOrderCount = $query->where(['status'=> \common\models\Pro_vehicle_order::STATUS_WAITING])->count();
        $dispatchingOrderCount = $query->where(['and', ['<', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING], ['>', 'start_time', $curTime-$orderWarningDuration]])->count();
        $returningOrderCount = $query->where(['and', ['status'=>\common\models\Pro_vehicle_order::STATUS_RENTING], ['<', 'new_end_time', $curTime+$orderWarningDuration]])->count();
        
        $warnningMellage = \common\components\Consts::DEFAULT_MILEAGE_WARNING;
        $warnningDuration = \common\components\Consts::DEFAULT_DAYS_WARNING*86400;
        $warnningTime = $curTime+$warnningDuration;
        $queryVehicle = \common\models\Pro_vehicle::find();
        // $renewalCount = $queryVehicle->where("(tci_renewal_time < {$warnningTime} OR vci_renewal_time < {$warnningTime}) ")->count();
        $renewalCount = $queryVehicle->where("(tci_renewal_time < {$warnningTime} OR vci_renewal_time < {$warnningTime}) AND status < 5 ")->count();
        $annualInspectionCount = $queryVehicle->where("annual_inspection_time < {$warnningTime} AND status < 5 ")->count();
        // $annualInspectionCount = $queryVehicle->where("annual_inspection_time < {$warnningTime} ")->count();
        $upkeepByMileageCount = $queryVehicle->where("next_upkeep_mileage > 0 AND cur_kilometers+{$warnningMellage} > next_upkeep_mileage AND status < ".\common\models\Pro_vehicle::STATUS_MAINTENANCE)->count();
        $upkeepByTimeCount = $queryVehicle->where("next_upkeep_time > 0 AND last_upkeep_time+{$warnningDuration} > next_upkeep_time AND status < ".\common\models\Pro_vehicle::STATUS_MAINTENANCE)->count();
        $maintenanceCount = $queryVehicle->where("status = ".\common\models\Pro_vehicle::STATUS_MAINTENANCE)->count();
        $saledCount = $queryVehicle->where("status = ".\common\models\Pro_vehicle::STATUS_SAILED)->count();
        
        $arrData['order-waiting-count'] = $newOrderCount;
        $arrData['order-dispatching-count'] = $dispatchingOrderCount;
        $arrData['order-returning-count'] = $returningOrderCount;
        $arrData['vehicle-renewal-count'] = $renewalCount;
        $arrData['vehicle-annual-inspection-count'] = $annualInspectionCount;
        $arrData['vehicle-upkeep-by-mileage-count'] = $upkeepByMileageCount;
        $arrData['vehicle-upkeep-by-time-count'] = $upkeepByTimeCount;
        $arrData['vehicle-maintenance-count'] = $maintenanceCount;
        $arrData['vehicle-saled-count'] = $saledCount;
        /*[order-waiting-count] => 2
            [order-dispatching-count] => 1
            [order-returning-count] => 109
            [vehicle-renewal-count] => 224
            [vehicle-annual-inspection-count] => 95
            [vehicle-upkeep-by-mileage-count] => 11
            [vehicle-upkeep-by-time-count] => 1
            [vehicle-maintenance-count] => 66
            [vehicle-saled-count] => 37*/
        foreach ($arrData as $k => $v) {
            \Yii::$app->cache->set($k.'-'.$authOfficeId, $v, 180);
        }
        // echo "<pre>";
        // print_r($arrData);
        // echo "</pre>";die;
        return $arrData;
    }
    
}

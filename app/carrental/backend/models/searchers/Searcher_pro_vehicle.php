<?php

namespace backend\models\searchers;

/**
 * Description of Searcher_pro_vehicle
 *
 * @author kevin
 */
class Searcher_pro_vehicle extends \common\helpers\ActiveSearcherModel
{
    public $status;
    public $office_id;
    public $vehicle_brand;
    public $vehicle_series;
    public $vehicle_model;
    public $plate_number;
    public $vehicle_change_time;
    
    public $action;
    
    public function rules() {
        return [
            [['status', 'office_id', 'vehicle_brand', 'vehicle_series', 'vehicle_model'], 'integer'],
            [['plate_number', 'action'], 'string'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle();
        return $model;
    }
    
    public function getCustomConditions() {
        $arrCondition = [];
        if ($this->plate_number) {
            $arrCondition['plate_number'] = ['like', 'plate_number', $this->plate_number];
        }
        $isOfficeLimit = true;
        $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if ($authOfficeId == \common\components\OfficeModule::HEAD_OFFICE_ID) {
            $isOfficeLimit = false;
        }
        if (!empty($this->action)) {
            $curTime = time();
            $warnningMellage = \common\components\Consts::DEFAULT_MILEAGE_WARNING;
            $warnningDuration = \common\components\Consts::DEFAULT_DAYS_WARNING*86400;
            $warnningTime = $curTime+$warnningDuration;
            if ($this->action == 'find_vehicles') {
                $isOfficeLimit = false;
            }
            elseif ($this->action == 'recentlyrenewal') {//近期续保
                // $arrCondition['action'] = ['or', ['<', 'tci_renewal_time', $warnningTime], ['<', 'vci_renewal_time', $warnningTime]];
                $arrCondition['action'] = ['and',['<','status',5],['or', ['<', 'tci_renewal_time', $warnningTime], ['<', 'vci_renewal_time', $warnningTime]]];//sjj
            }
            elseif ($this->action == 'recentlyannual') {//近期年检
                //$arrCondition['action'] = ['<', 'annual_inspection_time', $warnningTime];
                $arrCondition['action'] = ['and',['<','annual_inspection_time',$warnningTime],['<','status',5]];//sjj
            }
            elseif ($this->action == 'periodicmaintenance') {//近期周期保养
                $arrCondition['action'] = ['and', "next_upkeep_mileage > 0", "cur_kilometers+{$warnningMellage} > next_upkeep_mileage", ['<', 'status', \common\models\Pro_vehicle::STATUS_MAINTENANCE]];//4维护
            }
            elseif ($this->action == 'stagemaintenance') {//
                $arrCondition['action'] = ['and', "next_upkeep_time > 0", "last_upkeep_time+{$warnningDuration} > next_upkeep_time", ['<', 'status', \common\models\Pro_vehicle::STATUS_MAINTENANCE]];
            }
            elseif ($this->action == 'undermaintenance') {//维修保养
                $arrCondition['action'] = ['status'=>\common\models\Pro_vehicle::STATUS_MAINTENANCE];
            }
            elseif ($this->action == 'saled') {
                $arrCondition['action'] = ['status'=>\common\models\Pro_vehicle::STATUS_SAILED];//5已售
            }
        }
        if ($this->office_id > 0) {
            $arrCondition['office_id'] = ['or', ['belong_office_id'=>$this->office_id], ['stop_office_id'=>$this->office_id]];
        }
        elseif ($isOfficeLimit) {
            // office area limit support
            $arrOfficeIds = \common\components\OfficeModule::getAuthedOfficeIdArrayByOfficeId($authOfficeId);
            if (empty($arrOfficeIds)) {
                $arrOfficeIds = 0;
            }
            $arrCondition['office_id'] = ['or', ['belong_office_id'=>$arrOfficeIds], ['stop_office_id'=>$arrOfficeIds]];
        }
        if ($this->status) {
            if ($this->status == \common\models\Pro_vehicle::STATUS_BOOKED || $this->status == \common\models\Pro_vehicle::STATUS_RENTED) {
                $findStatus = ($this->status == \common\models\Pro_vehicle::STATUS_BOOKED ? \common\models\Pro_vehicle_order::STATUS_BOOKED : \common\models\Pro_vehicle_order::STATUS_RENTING);
                $arrVehicleIds = \common\components\OrderModule::getVehicleIdsByStatus($findStatus);
                $arrCondition['status'] = ['id' => (empty($arrVehicleIds) ? 0 : $arrVehicleIds)];
            }
            else if ($this->status == \common\models\Pro_vehicle::STATUS_NORMAL) {
                $arrVehicleIds = \common\components\OrderModule::getVehicleIdsByTimeRegion(time(), 0);
                $arrCondition['status'] = ['id' => (empty($arrVehicleIds) ? 0 : $arrVehicleIds)];
            }
            else {
                $arrCondition['status'] = ['status'=>$this->status];
            }
        }
        $arrCondition['vehicle_brand'] = false;
        $arrCondition['vehicle_series'] = false;
        if ($this->vehicle_model) {
            $arrCondition['vehicle_model'] = ['model_id' => $this->vehicle_model];
        }
        elseif ($this->vehicle_brand || $this->vehicle_series) {
            $cdb2 = \common\models\Pro_vehicle_model::find();
            if ($this->vehicle_brand) {
                $cdb2->andWhere(['brand'=>$this->vehicle_brand]);
            }
            if ($this->vehicle_series) {
                $cdb2->andWhere(['model_series'=>$this->vehicle_series]);
            }
            $arrRows2 = $cdb2->select(['id'])->asArray()->all();
            $arrModelIds = [];
            foreach ($arrRows2 as $row) {
                $arrModelIds[] = $row['id'];
            }
            if (empty($arrModelIds)) {
                $arrModelIds[] = 0;
            }
            $arrCondition['vehicle_model'] = ['model_id' => $arrModelIds];
        }

        //
        if($this->vehicle_change_time){
            $vehicle_change_time = strtotime($this->vehicle_change_time);
            $monthend = mktime(23,59,59,date('m',$vehicle_change_time),date('t',$vehicle_change_time),date('Y',$vehicle_change_time));
            $cdb3 = \common\models\Pro_vehicle_office_change::find();
            $cdb3->andWhere(['and', ['>=', 'created_at', $vehicle_change_time], ['<=', 'created_at', $monthend]]);
            $rows = $cdb3->select("vehicle_id")->groupBy(['vehicle_id'])->asArray()->all();

            if($rows){
                $array_column = array_column($rows,'vehicle_id');
                $arrCondition['id'] = ['in','id', array_values($array_column)];
            }else{
                $arrCondition['id'] = ['in','id', array('0')];
            }

        }
        return $arrCondition;
    }
    
}

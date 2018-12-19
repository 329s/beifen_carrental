<?php

namespace common\components;

class OrderModule {
    
    const ORDER_MAX_WAITING_SECONDS = 21600; // (3600 * 6);
    const ORDER_SHORT_RENTING_DAYS = 3;
    const ORDER_VIOLATION_CHECKING_DURATION = 1296000; // (86400*15);
    
    public static function getOrderStatusArray() {
        return [
            \common\models\Pro_vehicle_order::STATUS_WAITING => \Yii::t('locale', 'Non-confirmed'),
            \common\models\Pro_vehicle_order::STATUS_BOOKED => \Yii::t('locale', 'Booked'),
            \common\models\Pro_vehicle_order::STATUS_RENTING => \Yii::t('locale', 'Rented'),
            \common\models\Pro_vehicle_order::STATUS_COMPLETED => \Yii::t('locale', 'Completed'),
            \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING => \Yii::t('locale', 'Violation checking'),
            \common\models\Pro_vehicle_order::STATUS_CANCELLED => \Yii::t('locale', 'Closed'),
        ];
    }
    
    public static function getOrderTypeArray() {
        return [
            \common\models\Pro_vehicle_order::TYPE_PERSONAL => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Personal')]),
            \common\models\Pro_vehicle_order::TYPE_ENTERPRISE => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Enterprise')]),
            \common\models\Pro_vehicle_order::TYPE_UNIVERSAL => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Universal')]),
        ];
    }
    
    public static function getPriceTypeArray() {
        return [
            \common\models\Pro_vehicle_order::PRICE_TYPE_OFFICE => \Yii::t('locale', '{type} price', ['type' => \Yii::t('locale', 'Office')]),
            \common\models\Pro_vehicle_order::PRICE_TYPE_MULTIDAYS => \Yii::t('locale', '{type} price', ['type' => \Yii::t('locale', 'Packaged')]),
            \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE => \Yii::t('locale', '{type} price', ['type' => \Yii::t('locale', 'Online')]),
            \common\models\Pro_vehicle_order::PRICE_TYPE_WEEK => \Yii::t('locale', '{type} price', ['type' => \Yii::t('carrental', 'Week rent')]),
            \common\models\Pro_vehicle_order::PRICE_TYPE_MONTH => \Yii::t('locale', '{type} price', ['type' => \Yii::t('carrental', 'Month rent')]),
        ];
    }
	
	 public static function getHourPriceTypeArray() {
        return [
            \common\models\Pro_vehicle_order::PRICE_TYPE_HOUR => \Yii::t('locale', '{type} price', ['type' => \Yii::t('carrental', 'HOUR rent')]),
        ];
    }
    
    public static function getOrderPayTypeArray() {
        return [
            \common\models\Pro_vehicle_order::PAY_TYPE_NONE => \Yii::t('locale', 'Non-paid'),
            \common\models\Pro_vehicle_order::PAY_TYPE_CASH => \Yii::t('locale', 'Cash'),
            \common\models\Pro_vehicle_order::PAY_TYPE_SWIPE_CARD => \Yii::t('locale', 'Swipe card'),
            //\common\models\Pro_vehicle_order::PAY_TYPE_CHEQUE => \Yii::t('locale', 'Cheque'),
            \common\models\Pro_vehicle_order::PAY_TYPE_ONLINE_BANKING => \Yii::t('locale', 'Online banking'),
            \common\models\Pro_vehicle_order::PAY_TYPE_ALIPAY => \Yii::t('locale', 'Alipay'),
            \common\models\Pro_vehicle_order::PAY_TYPE_WEIXIN => \Yii::t('locale', 'Weixin'),
            \common\models\Pro_vehicle_order::PAY_TYPE_PRE_LICENSING => \Yii::t('locale', 'Pre-licensing'),
            \common\models\Pro_vehicle_order::PAY_TYPE_MEMBER_CARD => \Yii::t('locale', 'Member card'),
            \common\models\Pro_vehicle_order::PAY_TYPE_KUAIQIAN => \Yii::t('locale', 'Kuaiqian'),
            \common\models\Pro_vehicle_order::PAY_TYPE_ABC => \Yii::t('locale', 'ABC'),
        ];
    }
    
    public static function getOrderSourceArray() {
        return [
            \common\models\Pro_vehicle_order::ORDER_SOURCE_APP => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Cellphone')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_WEBSITE => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Website')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Office')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_CTRIP => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Ctrip')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_ZHIZUN => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Zhizun')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_TUANGOU => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Tuangou')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_TELEPHONE => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Telephone')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_PROXY => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Proxy')]),
            \common\models\Pro_vehicle_order::ORDER_SOURCE_OTHER => \Yii::t('locale', '{name} order', ['name' => \Yii::t('locale', 'Other')]),
        ];
    }
    
    public static function getSettlementTypeArray() {
        return [
           // \common\models\Pro_vehicle_order::SETTLEMENT_TYPE_NONE => \Yii::t('carrental', 'Not settlemented'),
            \common\models\Pro_vehicle_order::SETTLEMENT_TYPE_COMPLETED => \Yii::t('carrental', 'Return car completed'),
            \common\models\Pro_vehicle_order::SETTLEMENT_TYPE_INSTALLMENT => \Yii::t('carrental', 'Installment settlement'),
            \common\models\Pro_vehicle_order::SETTLEMENT_TYPE_ONACCOUNT => \Yii::t('carrental', 'Return car on account'),
            '4' => '无效订单',
        ];
    }
    
    public static function getOrderPayTypeText($payType) {
        $arr = self::getOrderPayTypeArray();
        return (isset($arr[$payType]) ? $arr[$payType] : '');
    }
    
    public static function getOptionalServiceNameArray() {
        $arrData = [];
        $cdb = \common\models\Pro_service_price::find();
        $cdb->where(['flag' => \common\models\Pro_service_price::FLAG_ENABLED, 'office_id'=>0]);
        $arrRows = $cdb->all();
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->name;
        }
        return $arrData;
    }
    
    public static function makeQueryConditionForOrderTimeRegion($startTime, $endTime) {
        $cond = null;
        if ($startTime && $startTime < $endTime) {
            $cond = ['and', ['>', 'new_end_time', $startTime], ['<', 'start_time', $endTime]];
        }
        elseif ($startTime) {
            $cond = ['and', ['<=', 'start_time', $startTime], ['>', 'new_end_time', $startTime]];
        }
        elseif ($endTime) {
            $cond = ['and', ['<', 'start_time', $endTime], ['>=', 'new_end_time', $endTime]];
        }
        return $cond;
    }
    
    public static function getVehicleIdsByTimeRegion($startTime, $endTime, $orderSource = 0, $vehicleModelId = 0, $skipOrderId = 0, $havingVehicleId = 0, $includingBooked = false) {
        $vehicleIds = [];
        $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if ($vehicleModelId) {
            $objFee = \common\models\Pro_vehicle_fee_plan::findByOrderSourceAndVehicleModel($orderSource, $vehicleModelId, $authOfficeId);
            if (!$objFee) {
                return $vehicleIds;
            }
        }
        
        $cdb0 = \common\models\Pro_vehicle_order::find(true);
        if ($includingBooked) {
            $cdb0->where(['status' => \common\models\Pro_vehicle_order::STATUS_RENTING]);
        }
        else {
            $cdb0->where(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);
        }
        if ($vehicleModelId) {
            $cdb0->andWhere(['vehicle_model_id' => $vehicleModelId]);
        }
        $timeRegionCondition = self::makeQueryConditionForOrderTimeRegion($startTime, $endTime);
        if ($timeRegionCondition) {
            $cdb0->andWhere($timeRegionCondition);
        }
        if ($skipOrderId) {
            $cdb0->andWhere(['<>', 'id', $skipOrderId]);
        }
        $arrRows = $cdb0->select(['vehicle_id'])->asArray()->all();
        $arrExistingVehicle = [];
        foreach ($arrRows as $row) {
            $existingVehicleId = 0;
            if ($row['vehicle_id']) {
                $existingVehicleId = $row['vehicle_id'];
            }
            if ($existingVehicleId && $existingVehicleId != $havingVehicleId) {
                $arrExistingVehicle[$existingVehicleId] = 1;
            }
        }
        $cdb = \common\models\Pro_vehicle::find();
        $cdb->where(['status' => \common\models\Pro_vehicle::STATUS_NORMAL]);
        if ($vehicleModelId) {
             $cdb->andWhere(['model_id' => $vehicleModelId]);
        }
        if (!empty($arrExistingVehicle)) {
            $cdb->andWhere(['not in', 'id', array_keys($arrExistingVehicle)]);
        }
        //if ($authOfficeId >= 0) {
        //    $cdb->andWhere(['stop_office_id' => $authOfficeId]);
        //}
        $arrRows2 = $cdb->select(['id'])->asArray()->all();
        foreach ($arrRows2 as $row) {
            $vehicleIds[] = $row['id'];
        }
        
        return $vehicleIds;
    }
    
    public static function getVehicleLeftCountByTimeRegion($officeId, $startTime, $endTime) {
        $arrLeftCountByVehicleModel = [];
        $queryOrder = \common\models\Pro_vehicle_order::find(true);
        $queryOrder->where(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);//10
        $timeRegionCondition = self::makeQueryConditionForOrderTimeRegion($startTime, $endTime);
        if ($timeRegionCondition) {
            $queryOrder->andWhere($timeRegionCondition);
        }
        $arrOrderRows = $queryOrder->select(['vehicle_id'])->asArray()->groupBy('vehicle_id')->all();
        //在租车辆id：SELECT `vehicle_id` FROM `pro_vehicle_order` WHERE (`status` <= 10) AND ((`new_end_time` > 1501921258) AND (`start_time` < 1501932058))

        $arrUsedCountByModel = [];
        $arrUsedCountByVehicleId = [];
        foreach ($arrOrderRows as $row) {
            $_c = (isset($arrUsedCountByVehicleId[$row['vehicle_id']]) ? $arrUsedCountByVehicleId[$row['vehicle_id']] : 0);
            $arrUsedCountByVehicleId[$row['vehicle_id']] = $_c + 1;
        }
        
        $queryVehicle = \common\models\Pro_vehicle::find(true);
        $queryVehicle->where(['status' => \common\models\Pro_vehicle::STATUS_NORMAL]);//在库待租1
        //$officeId 20 门店同区域的门店id条件
        $tmp = \common\models\Pro_vehicle::find(false);
        $cond2 = $tmp->applyOfficeLimitation($officeId);
        if ($cond2) {
            $queryVehicle->andWhere($cond2);
        }
        $arrRows = $queryVehicle->asArray()->all();
        //停靠在这些门店的在库待租车辆：SELECT * FROM `pro_vehicle` WHERE (`status`=1) AND ((`stop_office_id` IN ('20', '21', '22', '23', '24', '36', '62', '63', '111')))
        //
        foreach ($arrRows as $row) {
            //在库待租车辆所属系列总数
            $_c = (isset($arrLeftCountByVehicleModel[$row['model_id']]) ? $arrLeftCountByVehicleModel[$row['model_id']] : 0);
            $arrLeftCountByVehicleModel[$row['model_id']] = $_c + 1;
            
            //再租车辆所属系列总数
            if (isset($arrUsedCountByVehicleId[$row['id']])) {
                $_c = (isset($arrUsedCountByModel[$row['model_id']]) ? $arrUsedCountByModel[$row['model_id']] : 0);
                $arrUsedCountByModel[$row['model_id']] = $_c + $arrUsedCountByVehicleId[$row['id']];
            }
        }
        
        foreach ($arrUsedCountByModel as $k => $v) {
            $_c = (isset($arrLeftCountByVehicleModel[$k]) ? $arrLeftCountByVehicleModel[$k] : 0);
            if ($_c) {
                $arrLeftCountByVehicleModel[$k] = $_c - $v;
            }
        }
        
        
        return $arrLeftCountByVehicleModel;
    }
    
    /*app单程租车车辆*/
    public static function getVehicleOneLeftCountByTimeRegion($officeId, $startTime, $endTime,$flag) {
        $arrLeftCountByVehicleModel = [];
        $queryOrder = \common\models\Pro_vehicle_order::find(true);
        $queryOrder->where(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);//10
        $timeRegionCondition = self::makeQueryConditionForOrderTimeRegion($startTime, $endTime);
        if ($timeRegionCondition) {
            $queryOrder->andWhere($timeRegionCondition);
        }
        $arrOrderRows = $queryOrder->select(['vehicle_id'])->asArray()->groupBy('vehicle_id')->all();
        //在租车辆id：SELECT `vehicle_id` FROM `pro_vehicle_order` WHERE (`status` <= 10) AND ((`new_end_time` > 1501921258) AND (`start_time` < 1501932058))
        $arrUsedCountByModel = [];
        $arrUsedCountByVehicleId = [];
        foreach ($arrOrderRows as $row) {
            $_c = (isset($arrUsedCountByVehicleId[$row['vehicle_id']]) ? $arrUsedCountByVehicleId[$row['vehicle_id']] : 0);
            $arrUsedCountByVehicleId[$row['vehicle_id']] = $_c + 1;
        }

        $queryVehicle = \common\models\Pro_vehicle::find(true);
        $queryVehicle->where(['status' => \common\models\Pro_vehicle::STATUS_NORMAL,'isoneway'=>1]);//在库待租1
        //$officeId 20 门店同区域的门店id条件
        $tmp = \common\models\Pro_vehicle::find(false);
        $cond2 = $tmp->applyOfficeLimitation($officeId);
        if ($cond2) {
            $queryVehicle->andWhere($cond2);
        }
        $arrRows = $queryVehicle->asArray()->all();
        /*$sql=$queryVehicle->createCommand()->getRawSql();
        echo "<pre>";
        print_r($sql);
        echo "</pre>";*/
        //停靠在这些门店的在库待租车辆：SELECT * FROM `pro_vehicle` WHERE (`status`=1) AND ((`stop_office_id` IN ('20', '21', '22', '23', '24', '36', '62', '63', '111')))
        foreach ($arrRows as $row) {
            //在库待租车辆所属系列总数
            $_c = (isset($arrLeftCountByVehicleModel[$row['model_id']]) ? $arrLeftCountByVehicleModel[$row['model_id']] : 0);
            $arrLeftCountByVehicleModel[$row['model_id']] = $_c + 1;

            //再租车辆所属系列总数
            if (isset($arrUsedCountByVehicleId[$row['id']])) {
                $_c = (isset($arrUsedCountByModel[$row['model_id']]) ? $arrUsedCountByModel[$row['model_id']] : 0);
                $arrUsedCountByModel[$row['model_id']] = $_c + $arrUsedCountByVehicleId[$row['id']];
            }
        }

        // 通过flag标签查询可供租车的所属车型
        $cdb = \common\models\Pro_vehicle_model::find();
        $cdb->where(['id' => array_keys($arrLeftCountByVehicleModel)]);
        $arrRowsModel = $cdb->all();
        foreach ($arrRowsModel as $row) {
            $car_type = $row->vehicleFlagArrayData();
            if(!in_array($flag,$car_type)){
                unset($arrLeftCountByVehicleModel[$row->id]);
            }
        }


        foreach ($arrUsedCountByModel as $k => $v) {
            //
            $_c = (isset($arrLeftCountByVehicleModel[$k]) ? $arrLeftCountByVehicleModel[$k] : 0);
            if ($_c) {
                $arrLeftCountByVehicleModel[$k] = $_c - $v;
            }
        }

        return $arrLeftCountByVehicleModel;
    }


    public static function getVehicleLeftCountByModelId($vehicleModelId, $startTime, $endTime) {
        $leftCount = 0;
        $queryOrder = \common\models\Pro_vehicle_order::find(true);
        $queryOrder->where(['vehicle_model_id' => $vehicleModelId]);
        $queryOrder->andWhere(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);
        $timeRegionCondition = self::makeQueryConditionForOrderTimeRegion($startTime, $endTime);
        if ($timeRegionCondition) {
            $queryOrder->andWhere($timeRegionCondition);
        }
        $arrRows = $queryOrder->select(['vehicle_id'])->asArray()->all();
        $arrExistingVehicle = [];
        $emptyCount = 0;
        foreach ($arrRows as $row) {
            if ($row['vehicle_id']) {
                $arrExistingVehicle[$row['vehicle_id']] = 1;
            }
            else {
                $emptyCount++;
            }
        }
        $queryVehicle = \common\models\Pro_vehicle::find(true);
        $queryVehicle->where(['model_id' => $vehicleModelId, 
            'status' => \common\models\Pro_vehicle::STATUS_NORMAL]);
        $arrRows = $queryVehicle->select(['id'])->asArray()->all();
        foreach ($arrRows as $row) {
            if (isset($arrExistingVehicle[$row['id']])) {
                $leftCount++;
            }
        }
        
        $leftCount -= $emptyCount;
        return $leftCount;
    }
    /*单程用车判断车型还剩余几辆可租*/
    public static function oneWaygetVehicleLeftCountByModelId($vehicleModelId, $startTime, $endTime) {
        $leftCount = 0;
        $queryOrder = \common\models\Pro_vehicle_order::find(true);
        $queryOrder->where(['vehicle_model_id' => $vehicleModelId]);
        $queryOrder->andWhere(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);
        $timeRegionCondition = self::makeQueryConditionForOrderTimeRegion($startTime, $endTime);
        if ($timeRegionCondition) {
            $queryOrder->andWhere($timeRegionCondition);
        }
        $arrRows = $queryOrder->select(['vehicle_id'])->asArray()->all();
        $arrExistingVehicle = [];
        $emptyCount = 0;
        foreach ($arrRows as $row) {
            if ($row['vehicle_id']) {
                $arrExistingVehicle[$row['vehicle_id']] = 1;
            }
            else {
                $emptyCount++;
            }
        }
        $queryVehicle = \common\models\Pro_vehicle::find(true);
        $queryVehicle->where(['model_id' => $vehicleModelId, 
            'status' => \common\models\Pro_vehicle::STATUS_NORMAL]);
        $arrRows = $queryVehicle->select(['id'])->asArray()->all();

        foreach ($arrRows as $row) {
            // if (isset($arrExistingVehicle[$row['id']])) {
                $leftCount++;
            // }
        }
        
        $leftCount -= $emptyCount;
        return $leftCount;
    }



    
    public static function hasVehicleRented($vehicleId, $startTime, $endTime, $skipUserId = 0, $skipOrderId = 0) {
        $cdb = \common\models\Pro_vehicle_order::find(true);
        $cdb->where(['vehicle_id' => $vehicleId]);
        $cdb->andWhere(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);
        $timeRegionCondition = self::makeQueryConditionForOrderTimeRegion($startTime, $endTime);
        if ($timeRegionCondition) {
            $cdb->andWhere($timeRegionCondition);
        }
        if (!empty($skipUserId)) {
            $cdb->andWhere(['<>', 'user_id', $skipUserId]);
        }
        if (!empty($skipOrderId)) {
            $cdb->andWhere(['<>', 'id', $skipOrderId]);
        }
        if ($cdb->exists()) {
            return true;
        }
        return false;
    }
    
    public static function getRentDays($startTime, $endTime) {
        return \common\models\Pri_renttime_data::create($startTime, $endTime);
    }
    
    public static function validateRentDays($rentDays, $startTime, $endTime,$payType=0) {
        $rentTimeData = \common\models\Pri_renttime_data::create($startTime, $endTime,$payType);
        $minRentDays = 1;
        $arrData = ['result'=>0, 'desc'=>\Yii::t('locale', 'Success')];
        if ($rentDays != $rentTimeData->days) {
            $arrData['result'] = -1;
            \Yii::error("validate rent days:{$rentDays} between ".  date('Y-m-d H:i:s', $startTime).'-'.date('Y-m-d H:i:s', $endTime)." not match myself rent_days:{$rentTimeData->days}", 'order');
            $arrData['desc'] = \Yii::t('locale', 'Rent days not match rent time region.'); // . " real_rent_days:{$realRentDays} rent_days:{$rentDays}";
            return $arrData;
        }
        if ($rentDays < $minRentDays) {
            $arrData['result'] = -2;
            $arrData['desc'] = \Yii::t('locale', 'Rent days less than {days}.', ['days' => $minRentDays]);
            return $arrData;
        }
        $curTime = time();
        if ($endTime <= $startTime) {
            $arrData['result'] = -3;
            \Yii::error("validate rent days:{$rentDays} between ".  date('Y-m-d H:i:s', $startTime).'-'.date('Y-m-d H:i:s', $endTime)." while the end time earlier than start time.", 'order');
            $arrData['desc'] = \Yii::t('carrental', 'End time should not earlier than start time!');
            return $arrData;
        }
        if ($startTime > $curTime + Consts::MAX_VALID_RENT_PERIOD_DAYS*86400) {
            $arrData['result'] = -3;
            \Yii::error("validate rent days:{$rentDays} between ".  date('Y-m-d H:i:s', $startTime).'-'.date('Y-m-d H:i:s', $endTime)." while the end time over the limit of days:".Consts::MAX_VALID_RENT_PERIOD_DAYS.".", 'order');
            $arrData['desc'] = \Yii::t('carrental', 'Please do not select the rent time that over {time}', ['time'=>date('Y-m-d H:i', $curTime + Consts::MAX_VALID_RENT_PERIOD_DAYS*86400)]);
            return $arrData;
        }
        
        $arrFestivals = \common\components\OptionsModule::getFestivalsArray();
        foreach ($arrFestivals as $id => $obj) {
            if ($obj->alldays_required) {
                // if some rent days in festival days
                if (!$obj->isValidRentTime($startTime, $endTime)) {
                    $arrData['result'] = -2;
                    $arrData['desc'] = \Yii::t('carrental', 'When rent car in {name}, you should rent car for all days between {start} and {end}.', ['name'=>$obj->name, 'start'=>date('Y-m-d', $obj->start_time), 'end'=>date('Y-m-d', $obj->end_time)]);
                    return $arrData;
                }
            }
        }
        return $arrData;
    }
    
    public static function calculateOrderPriceData($orderId, $startTime, $endTime, $orderSource = 0, $priceType = 0, $birthday = null,$userisnew = 1) {
        $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
        if (!$objOrder) {
            \Yii::error("calculate order price by order:{$orderId} failed, could not find the vehicle.", 'order');
            return false;
        }
        $objVehicle = \common\models\Pro_vehicle::findById($objOrder->vehicle_id);
        if (!$objVehicle) {
            \Yii::error("calculate order price by vehicle:{$objOrder->vehicle_id} failed, could not find the vehicle.", 'order');
            return false;
        }
        
        return self::calculateVehicleModelRentPriceData($objVehicle->model_id, $startTime, $endTime, $objOrder->office_id_rent, $orderSource, $priceType, $birthday,$userisnew);
    }
    
    public static function calculateVehicleRentPriceData($vehicleId, $startTime, $endTime, $orderSource = 0, $priceType = 0, $birthday = null,$userisnew = 1) {
        $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
        if (!$objVehicle) {
            \Yii::error("calculate order price by vehicle:{$vehicleId} failed, could not find the vehicle.", 'order');
            return false;
        }
        
        return self::calculateVehicleModelRentPriceData($objVehicle->model_id, $startTime, $endTime, $objVehicle->stop_office_id, $orderSource, $priceType, $birthday,$userisnew);
    }
    
    public static function calculateVehicleModelRentPriceData($vehicleModelId, $startTime, $endTime, $officeId, $orderSource = 0, $priceType = 0, $birthday = null,$userisnew = 1) {
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($vehicleModelId);
        if (!$objVehicleModel) {
            \Yii::error("calculate order price by vehicle model:{$vehicleModelId} failed, could not find the vehicle model:{$vehicleModelId}.", 'order');
            return false;
        }
        
        //PRICE_TYPE_ONLINE=>3,在线支付价格   ORDER_SOURCE_OFFICE=>3,门店下单   ORDER_SOURCE_APP=>1,手机app下单
        /*if ($priceType == \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE
            && $orderSource == \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE) {
            $orderSource = \common\models\Pro_vehicle_order::ORDER_SOURCE_APP;
        }*/
        /*sjj替换上面*/
        if ($priceType == \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE && $orderSource == \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE) {//在线价格门店下单
            $orderSource = \common\models\Pro_vehicle_order::ORDER_SOURCE_APP;//手机来源
        }elseif ($priceType == 1 && $orderSource == 1) {//门店价格，手机下单
            $orderSource = 0;
        }elseif($priceType == 3 && $orderSource == 2){
            $orderSource = \common\models\Pro_vehicle_order::ORDER_SOURCE_APP;//手机来源
        }
        // echo $orderSource;
        /*sjj替换上面*/
        
        $arrFeePlans = VehicleModule::getFeePlanObjects($vehicleModelId, $officeId);
        $objFeePlan = VehicleModule::getFeePlanObjectFromArray($arrFeePlans, $orderSource, $officeId, $vehicleModelId);
        if (!$objFeePlan) {
            \Yii::error("calculate order price by vehicle model:{$vehicleModelId} failed, could not find the fee plan by vehicle model:{$vehicleModelId}.", 'order');
            return false;
        }
        
        $arrPriceData = $objFeePlan->getPriceForDuration($startTime, $endTime, $priceType, $birthday,$userisnew);
        return $arrPriceData;
    }
    
    public static function getVehicleStatusByVehicleIds($arrVehicleIds, $startTime = 0, $endTime = 0) {
        $arrData = [];
        if ($arrVehicleIds) {
            $cdb = \common\models\Pro_vehicle_order::find(true);
            $cdb->select(['vehicle_id', 'status', 'start_time', 'new_end_time']);
            $cdb->where(['vehicle_id' => $arrVehicleIds]);
            if (!$startTime) {
                $startTime = time();
            }
            $timeRegionCondition = self::makeQueryConditionForOrderTimeRegion($startTime, $endTime);
            if ($timeRegionCondition) {
                $cdb->andWhere($timeRegionCondition);
            }
            $arrRows = $cdb->asArray()->all();
            foreach ($arrRows as $row) {
                if ($row['status'] == \common\models\Pro_vehicle_order::STATUS_BOOKED || $row['status'] == \common\models\Pro_vehicle_order::STATUS_RENTING) {
                    $status = ($row['status'] == \common\models\Pro_vehicle_order::STATUS_BOOKED ? \common\models\Pro_vehicle::STATUS_BOOKED : \common\models\Pro_vehicle::STATUS_RENTED);
                    $arrData[$row['vehicle_id']] = ['status'=>$status, 'start_time'=>$row['start_time'], 'end_time'=>$row['new_end_time']];
                }
            }
        }
        return $arrData;
    }
    
    public static function getVehicleIdsByStatus($status, $startTime = 0, $endTime = 0) {
        $arrData = [];
        if ($status) {
            $cdb = \common\models\Pro_vehicle_order::find();
            $cdb->select(['vehicle_id', 'status']);
            $cdb->where(['status' => $status]);
            if (!$startTime) {
                $startTime = time();
            }
            $timeRegionCondition = self::makeQueryConditionForOrderTimeRegion($startTime, $endTime);
            if ($timeRegionCondition) {
                $cdb->andWhere($timeRegionCondition);
            }
            $arrRows = $cdb->asArray()->all();
            foreach ($arrRows as $row) {
                $arrData[$row['vehicle_id']] = 1;
            }
        }
        return array_keys($arrData);
    }
    
    public static function getCityAndOfficeTextByOfficeId($officeId) {
        $objOffice = \common\models\Pro_office::findById($officeId);
        $text = '';
        if ($objOffice) {
            $objCity = \common\models\Pro_city::findById($objOffice->city_id);
            if ($objCity) {
                $text .= $objCity->name.'/';
            }
            $text .= $objOffice->shortname;
        }
        return $text;
    }
    
    public static function getCityTextByOfficeId($officeId) {
        $objOffice = \common\models\Pro_office::findById($officeId);
        $text = '';
        if ($objOffice) {
            $objCity = \common\models\Pro_city::findById($objOffice->city_id);
            if ($objCity) {
                $text = $objCity->name;
            }
        }
        return $text;
    }
    
    public static function getUserRentingCarCount($userId, $startTime, $endTime, $skipOrderId = 0) {
        $cdb = \common\models\Pro_vehicle_order::find(true);
        $cdb->where(['user_id'=>$userId]);
        $cdb->andWhere(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);
        $timeRegionCondition = self::makeQueryConditionForOrderTimeRegion($startTime, $endTime);
        if ($timeRegionCondition) {
            $cdb->andWhere($timeRegionCondition);
        }
        if (!empty($skipOrderId)) {
            $cdb->andWhere(['<>', 'id', $skipOrderId]);
        }
        
        return $cdb->count();
    }
    
    public static function getOrderReletMaxEndTime($orderId) {
        $cdb = \common\models\Pro_vehicle_order_relet::find(true);
        $cdb->where(['order_id' => $orderId]);
        $arrRows = $cdb->all();
        $maxEndTime = 0;
        foreach ($arrRows as $row) {
            if ($row->new_end_time > $maxEndTime) {
                $maxEndTime = $row->new_end_time;
            }
        }
        
        return $maxEndTime;
    }
    
    /**
     * get service price by two diffirent offices
     * @param integer $vehicleModelId
     * @param integer $officeId1
     * @param integer $officeId2
     * @return array ['result'=>0, 'desc'=>'message', 'price'=>price]
     */
    public static function getPriceByDistanceOfOffices($vehicleModelId, $officeId1, $officeId2) {
        $arrResult = ['result'=>0, 'desc'=>\Yii::t('locale', 'Success'), 'price'=>0];
        do
        {
            if ($officeId1 == $officeId2) {
                break;
            }

            if (empty($vehicleModelId) || !$officeId1 || !$officeId2) {
                $arrResult['result'] = -1;
                $arrResult['desc'] = \Yii::t('locale', 'Invalid parameter!');
                \Yii::error("vehicleModelId:{$vehicleModelId} officeId1:{$officeId1} officeId2:{$officeId2} ");
                break;
            }
            
            $objVehicleModel = \common\models\Pro_vehicle_model::findById($vehicleModelId);
            if (!$objVehicleModel) {
                $arrResult['result'] = -1;
                $arrResult['desc'] = \Yii::t('carrental', 'Vehicle model by ID:{id} does not exists!', ['id'=>$vehicleModelId]);
                break;
            }

            $objOffice1 = null;
            $objOffice2 = null;
            $cdb = \common\models\Pro_office::find(true);
            $cdb->where(['id'=>[$officeId1, $officeId2]]);
            $arrRows = $cdb->all();
            foreach ($arrRows as $row) {
                if ($row->id == $officeId1) {
                    $objOffice1 = $row;
                }
                else {
                    $objOffice2 = $row;
                }
            }

            if (!$objOffice1) {
                $arrResult['result'] = -1;
                $arrResult['desc'] = \Yii::t('carrental', 'Office by ID:{id} does not exists!', ['id'=>$officeId1]);
                break;
            }
            if (!$objOffice2) {
                $arrResult['result'] = -1;
                $arrResult['desc'] = \Yii::t('carrental', 'Office by ID:{id} does not exists!', ['id'=>$officeId2]);
                break;
            }
            
            $distanceResult = DistanceService::getDistanceByCoordinates($objOffice1->getCoordinate(), $objOffice2->getCoordinate());
            if ($distanceResult[0] < 0) {
                $arrResult['result'] = -1;
                $arrResult['desc'] = $distanceResult[1];
            }
            else {
                $distance = $distanceResult[0];
                $coeff = $objVehicleModel->getMileagePriceInfo();
                if (is_array($coeff)) {
                    krsort($coeff);
                    foreach ($coeff as $range => $c) {
                        if ($distance > $range) {
                            $coeff = $c;
                            break;
                        }
                    }
                    if (!is_numeric($coeff)) {
                        $coeff = 0;
                    }
                }
                $price = $distance * floatval($coeff);

                $arrResult['price'] = $price;
            }
            
        }while(0);
        return $arrResult;
    }
    
    /**
     * get service price by office delivery a car to customer or take car from customer address
     * @param integer $vehicleModelId
     * @param string $address
     * @param integer $officeId
     * @return array ['result'=>0, 'desc'=>'message', 'price'=>price]
     */
    public static function getPriceByAddressToOffice($vehicleModelId, $address, $officeId) {
        $arrResult = ['result'=>0, 'desc'=>\Yii::t('locale', 'Success'), 'price'=>0];
        do
        {
            if (empty($address)) {
                break;
            }
            else {
                // Currently take car and return car beyond office is free.
                break;
            }
            
            if (!$officeId || !$vehicleModelId) {
                $arrResult['result'] = -1;
                $arrResult['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            
            $objVehicleModel = \common\models\Pro_vehicle_model::findById($vehicleModelId);
            if (!$objVehicleModel) {
                $arrResult['result'] = -1;
                $arrResult['desc'] = \Yii::t('carrental', 'Vehicle model by ID:{id} does not exists!', ['id'=>$vehicleModelId]);
                break;
            }

            $cdb = \common\models\Pro_office::find(true);
            $cdb->where(['id'=>$officeId]);
            $objOffice = $cdb->one();
            if (!$objOffice) {
                $arrResult['result'] = -1;
                $arrResult['desc'] = \Yii::t('carrental', 'Office by ID:{id} does not exists!', ['id'=>$officeId]);
                break;
            }
            
            $distanceResult = DistanceService::getDistanceBetweenAddressToCoordate($address, $objOffice->getCoordinate());
            if ($distanceResult[0] < 0) {
                $arrResult['result'] = -1;
                $arrResult['desc'] = $distanceResult[1];
                break;
            }
            
            $distance = intval($distanceResult[0]) - Consts::DELIVERRY_CAR_BY_OFFICE_FREE_MIN_MILEAGE;  // 减去6公里免费距离 
            if ($distance > 0) {
                $arrResult['price'] = $distance * Consts::DELEVERRY_CAR_BY_OFFICE_PRICE_COEFFICENT;
            }
            
        }while(0);
        return $arrResult;
    }
    
    public static function getUserInfoByOrderFormData($objFormData) {
        if (empty($objFormData->customer_id) || empty($objFormData->customer_id_type)) {
            return null;
        }
        $cdbUserInfo = \common\models\Pub_user_info::find();
        $cdbUserInfo->where(['identity_type' => $objFormData->customer_id_type, 'identity_id' => $objFormData->customer_id]);
        return $cdbUserInfo->one();
    }
    
    public static function instanceUserInfoByOrderFormData($objFormData) {
        $arrResult = ['result'=>-1, 'desc'=>\Yii::t('locale', 'Failed'), 'userInfo'=>null, 'isCreated' => false];
        do
        {
            if (empty($objFormData->customer_id)) {
                $arrResult['desc'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>'ID number']);
                break;
            }
            if (empty($objFormData->customer_telephone)) {
                $arrResult['desc'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>'Telephone']);
                break;
            }

            $cdbUserInfo = \common\models\Pub_user_info::find();
            $cdbUserInfo->where(['identity_type' => $objFormData->customer_id_type, 'identity_id' => $objFormData->customer_id]);
            $objUserInfo = $cdbUserInfo->one();

            $objUser = null;
            $cdbUser = \common\models\Pub_user::find();
            if ($objUserInfo) {
                if ($objUserInfo->credit_level <= \common\models\Pub_user_info::CREDIT_LEVEL_FORBIDEN) {
                    $arrResult['desc'] = \Yii::t('carrental', 'Sorry, the customer is in black list, the rent was forbidden.');
                    break;
                }

                $cdbUser->where(['info_id' => $objUserInfo->id]);
                $objUser = $cdbUser->one();
            }
            else {
                // create a user
                $objUserInfo = new \common\models\Pub_user_info();
                $objUserInfo->name = $objFormData->customer_name;
                $objUserInfo->identity_type = $objFormData->customer_id_type;
                $objUserInfo->identity_id = $objFormData->customer_id;
                $objUserInfo->telephone = $objFormData->customer_telephone;
                $objUserInfo->fixedphone = $objFormData->customer_fixedphone;
                $objUserInfo->email = '';
                $objUserInfo->emergency_contact = $objFormData->emergency_contact_name;
                $objUserInfo->emergency_telephone = $objFormData->emergency_contact_phone;
                $objUserInfo->home_address = $objFormData->customer_address;
                $objUserInfo->post_code = $objFormData->customer_postcode;
                $objUserInfo->driver_license_time = $objFormData->customer_driver_license_time;
                $objUserInfo->driver_license_expire_time = $objFormData->customer_driver_license_expire_time;
                $objUserInfo->company_name = $objFormData->customer_employer;
                $objUserInfo->company_address = $objFormData->customer_employer_address;
                $objUserInfo->company_postcode = $objFormData->customer_employer_postcode;
                $objUserInfo->company_telephone = $objFormData->customer_employer_phone;
                $objUserInfo->company_license = $objFormData->customer_employer_certificate_id;
                $objUserInfo->belong_office_id = $objFormData->belong_office_id;
                $objUserInfo->credit_card_type = \common\models\Pub_user_info::CREDIT_CARD_TYPE_NORMAL;
                $objUserInfo->gender = \common\models\Pub_user_info::GENDER_UNKNOWN;
                $objUserInfo->user_type = \common\models\Pub_user_info::USER_TYPE_PERSONAL;
                $objUserInfo->credit_level = \common\models\Pub_user_info::CREDIT_LEVEL_STAR_1;
                if (!$objUserInfo->save()) {
                    $errText = $objUserInfo->getErrorAsHtml();
                    $arrResult['desc'] = (empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText);
                    break;
                }
                
                $arrResult['isCreated'] = true;
            }
            if (!$objUser) {
                $cdbUser->where(['account' => $objFormData->customer_telephone]);
                $objUser = $cdbUser->one();
            }
            if ($objUser && $objUser->unfreeze_at > time()) {
                $arrResult['desc'] = \Yii::t('locale', 'User has been freezed!');
                break;
            }

            if ($objUser) {
                if (empty($objUser->info_id)) {
                    $objUser->info_id = $objUserInfo->id;
                    $objUser->save();
                }
            }
            else {
                $objUser = new \common\models\Pub_user();
                $objUser->account = $objFormData->customer_telephone;
                $objUser->setPassword(md5(substr($objUserInfo->telephone, -6, 6)));
                $objUser->generateAuthKey();
                $objUser->status = \common\models\Pub_user::STATUS_ACTIVE;
                $objUser->info_id = $objUserInfo->id;
                if (!$objUser->save()) {
                    $errText = $objUser->getErrorAsHtml();
                    $arrResult['desc'] = (empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText);
                    break;
                }
                \common\components\SmsComponent::send($objUser->account, \common\components\Consts::KEY_SMS_USER_SIGNUP, ['CNAME'=>$objUser->account]);
            }
            
            $arrResult['result'] = 0;
            $arrResult['desc'] = \Yii::t('locale', 'Success');
            $arrResult['userInfo'] = $objUserInfo;
        }while(0);
        return $arrResult;
    }
    
    public static function getIsHighSpeedArray() {
        return [
           '0' => '否',
           '1' => '是',
        ];
    }


}

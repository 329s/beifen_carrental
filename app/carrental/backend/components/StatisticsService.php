<?php

namespace backend\components;

class StatisticsService
{
    
    public static function getCarStatusRentalData() {
        $tblNameOrder = \common\models\Pro_vehicle_order::tableName();
        $tblNameVehicleViolation = \common\models\Pro_vehicle_violation::tableName();
        $tblNameVehicle = \common\models\Pro_vehicle::tableName();
        
        ini_set('memory_limit', '1024M');
        $queryOrder = \common\models\Pro_vehicle_order::find();
        $cndOrderCondition = "{$tblNameOrder}.status >= ".\common\models\Pro_vehicle_order::STATUS_RENTING." AND {$tblNameOrder}.status <=".\common\models\Pro_vehicle_order::STATUS_COMPLETED;
        $arrRentingRows = $queryOrder->where($cndOrderCondition)->asArray()->all();
        // $arrRentingRows = $queryOrder->where(['=', 'status', 10])->asArray()->all();
        // $aa=$queryOrder->createCommand()->getRawSql();
        // return $aa;
        // return $arrRentingRows;
        
        $arrVehicleIds = [];
        $arrOrderIds = [];
        foreach ($arrRentingRows as $row) {
            $arrOrderIds[] = $row['id'];
            if ($row['vehicle_id']) {
                if (!isset($arrVehicleIds[$row['vehicle_id']])) {
                    $arrVehicleIds[$row['vehicle_id']] = 1;
                }
            }
        }
        
        $queryViolation = \common\models\Pro_vehicle_violation::find();
        $queryViolation->select(["{$tblNameVehicleViolation}.status", 'notified_at']);
        $queryViolation->leftJoin($tblNameOrder, "{$tblNameVehicleViolation}.order_id = {$tblNameOrder}.id");
        $queryViolation->where($cndOrderCondition);
        $arrViolationObjects = $queryViolation->asArray()->all() ;
        $queryVehicle = \common\models\Pro_vehicle::find();
        $queryVehicle->select(["{$tblNameVehicle}.belong_office_id"]);
        $queryVehicle->leftJoin($tblNameOrder, "{$tblNameVehicle}.id = {$tblNameOrder}.vehicle_id");
        $queryVehicle->where($cndOrderCondition);
        $arrVehicleObjects = $queryVehicle->asArray()->all();
        // $aa=$queryVehicle->createCommand()->getRawSql();
        // return $aa;

        $rentingCount = 0;              // 在租车辆
        $shortRentingCount = 0;         // 短租车辆
        $longRentingCount = 0;          // 长租车辆
        $curDayTakingCount = 0;         // 今日应取车
        $curDayReturningCount = 0;      // 今日应还车
        $twoHourReturningCount = 0;     // 2小时内应还车
        $overtimeRentingCount = 0;      // 逾期未还
        $poundageOvertimeCount = 0;     // 预授权到期
        $stagingPaymentCount = 0;       // 应分期结算
        $diffOfficeReturningCount = 0;  // 异店还车
        $violationCheckingCount = 0;    // 待查违章
        $arrearsReturnedCount = 0;      // 还车挂账
        $needViolationCheckCount = 0;   // 需查违章
        $violationWouldOvertimeCount = 0;// 违章将到期
        $violationCount = 0;            // 已违章单
        $violationOvertimeCount = 0;    // 违章结算超期 
        $bookingCount = 0;
        
        $curTime = time();
        $dayStartTime = strtotime(date('Y-m-d').' 00-00-00');
        $dayEndTime = strtotime(date('Y-m-d').' 23-59-59');
        
        foreach ($arrRentingRows as $row) {
            $objVehicle = (isset($arrVehicleObjects[$row['vehicle_id']]) ? $arrVehicleObjects[$row['vehicle_id']] : null);
            if ($row['status'] == \common\models\Pro_vehicle_order::STATUS_RENTING) {
                $rentingCount++;
                if ($row['rent_days'] <= \common\components\OrderModule::ORDER_SHORT_RENTING_DAYS) {
                    $shortRentingCount++;
                }
                else {
                    $longRentingCount++;
                }
                
                if ($row['new_end_time'] - $curTime <= 7200) {
                    $twoHourReturningCount++;
                }

                if ($row['new_end_time'] >= $dayStartTime && $row['new_end_time'] <= $dayEndTime) {
                    $curDayReturningCount++;
                }
            
                if ($row['new_end_time'] <= $curTime) {
                    $overtimeRentingCount++;
                }
                
                if ($objVehicle && $row['office_id_return'] != $objVehicle['belong_office_id']) {
                    $diffOfficeReturningCount++;
                }
            }
            else if ($row['status'] == \common\models\Pro_vehicle_order::STATUS_BOOKED 
                || $row['status'] == \common\models\Pro_vehicle_order::STATUS_WAITING) {
                $bookingCount++;
                
                if ($row['start_time'] >= $dayStartTime && $row['start_time'] <= $dayEndTime) {
                    $curDayTakingCount++;
                }
                
            }
            
            if ($row['status'] == \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING) {
                $violationCheckingCount++;
                
                $needViolationCheckCount++;
                
                if ($row['car_returned_at'] + \common\components\OrderModule::ORDER_VIOLATION_CHECKING_DURATION >= $curTime) {
                    $poundageOvertimeCount++;
                }
            }
            
            if ($row['paid_amount'] < $row['total_amount']) {
                $stagingPaymentCount++;
                if ($row['status'] >= \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING) {
                    $arrearsReturnedCount++;
                }
            }
            
        }
        
        foreach ($arrViolationObjects as $row) {
            if ($row['status'] != \common\models\Pro_vehicle_violation::STATUS_PROCESSED) {
                $violationCount++;
                if ($row['notified_at'] + \common\components\VehicleModule::VEHICLE_VIOLATION_ORDER_PROCESS_MAX_TIME >= $curTime) {
                    $violationWouldOvertimeCount++;
                    
                    
                }
                else if ($row['notified_at'] + \common\components\VehicleModule::VEHICLE_VIOLATION_ORDER_PROCESS_MAX_TIME - 86400 >= $curTime) {
                    $violationOvertimeCount++;
                }
            }
        }
        
        $arrRentalData = [
            'rentingCount' => $rentingCount,
            'shortRentingCount' => $shortRentingCount,
            'longRentingCount' => $longRentingCount,
            'curDayTakingCount' => $curDayTakingCount,
            'curDayReturningCount' => $curDayReturningCount,
            'twoHourReturningCount' => $twoHourReturningCount,
            'overtimeRentingCount' => $overtimeRentingCount,
            'poundageOvertimeCount' => $poundageOvertimeCount,
            'stagingPaymentCount' => $stagingPaymentCount,
            'diffOfficeReturningCount' => $diffOfficeReturningCount,
            'violationCheckingCount' => $violationCheckingCount,
            'arrearsReturnedCount' => $arrearsReturnedCount,
            'needViolationCheckCount' => $needViolationCheckCount,
            'violationWouldOvertimeCount' => $violationWouldOvertimeCount,
            'violationCount' => $violationCount,
            'violationOvertimeCount' => $violationOvertimeCount,
            'bookingCount' => $bookingCount,
        ];
        
        return $arrRentalData;
    }
    
    public static function getCarStatusStateData() {
        $totalVehicleCount = 0;
        $waitingRentVehicleCount = 0;
        $rentingVehicleCount = 0;
        $bookingVehicleCount = 0;
        $shortRentingVehicleCount = 0;
        $longRentingVehicleCount = 0;
        $curDayDispathingVehicleCount = 0;
        $twoHourDispatchingVehicleCount = 0;
        $overtimeOrderCount = 0;
        $maintenanceVehicleCount = 0;
        $needUpkeepVehicleCount = 0;
        $needUpkeep2VehicleCount = 0;
        $needYearcheckVehicleCount = 0;
        $needRenewalVehicleCount = 0;
        $needPaybackVehicleCount = 0;
        $dispatchingVehicleCount = 0;
        
        $curTime = time();
        $dayStartTime = strtotime(date('Y-m-d', $curTime).' 00-00-00');
        $dayEndTime = strtotime(date('Y-m-d', $curTime).' 23-59-59');
        
        $cdb = \common\models\Pro_vehicle_order::find();
        $cdb->where(['and', ['>=', 'status', \common\models\Pro_vehicle_order::STATUS_BOOKED], ['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]]);
        $arrOrderRows = $cdb->all();
        $arrVehicleStatus = [];
        $arrRentingVehicleIds = [];
        foreach ($arrOrderRows as $row) {
            $vehicleId = $row->vehicle_id;
            if (!isset($arrVehicleStatus[$vehicleId]) || $arrVehicleStatus[$vehicleId] < $row->status) {
                $arrVehicleStatus[$vehicleId] = $row->status;
            }
            
            if ($row->start_time <= $dayEndTime && $row->new_end_time >= $dayStartTime) {
                $arrRentingVehicleIds[$vehicleId] = 1;
            }
            
            if ($row->status == \common\models\Pro_vehicle_order::STATUS_RENTING) {
                $rentingVehicleCount++;
                if ($row->rent_days <= \common\components\OrderModule::ORDER_SHORT_RENTING_DAYS) {
                    $shortRentingVehicleCount++;
                }
                else {
                    $longRentingVehicleCount++;
                }
                
            }
            else if ($row->status == \common\models\Pro_vehicle_order::STATUS_BOOKED) {
                $bookingVehicleCount++;
                
                if ($row->start_time - $curTime <= 7200) {
                    $twoHourDispatchingVehicleCount++;
                }

                if ($row->start_time >= $dayStartTime && $row->start_time <= $dayEndTime) {
                    $curDayDispathingVehicleCount++;
                }
            
                if ($row->start_time < $curTime) {
                    $overtimeOrderCount++;
                }
                
            }
        }
        
        $cdb1 = \common\models\Pro_vehicle::find();
        $cdb1->where(['>=', 'status', \common\models\Pro_vehicle::STATUS_NORMAL]);
        $arrVehicleRows = $cdb1->all();
        
        $warningMileage = \common\components\Consts::DEFAULT_MILEAGE_WARNING;
        $defaultWarningTime = 2592000;
        
        foreach ($arrVehicleRows as $row) {
            $totalVehicleCount++;
            if ($row->status == \common\models\Pro_vehicle::STATUS_MAINTENANCE) {
                $maintenanceVehicleCount++;
            }
            
            if ($row->next_upkeep_mileage && $row->cur_kilometers + $warningMileage >= $row->next_upkeep_mileage) {
                $needUpkeepVehicleCount++;
            }
            else if ($row->next_upkeep_time && $curTime + $defaultWarningTime >= $row->next_upkeep_time) {
                $needUpkeepVehicleCount++;
            }
            
            if ($row->annual_inspection_time && $curTime + $defaultWarningTime >= $row->annual_inspection_time) {
                $needYearcheckVehicleCount++;
            }
            
            if ($row->tci_renewal_time && $curTime + $defaultWarningTime >= $row->tci_renewal_time) {
                $needRenewalVehicleCount++;
            }
            if ($row->vci_renewal_time && $curTime + $defaultWarningTime >= $row->vci_renewal_time) {
                $needRenewalVehicleCount++;
            }
            
            if ($row->belong_office_id != $row->stop_office_id) {
                $dispatchingVehicleCount++;
            }
            // $needPaybackVehicleCount there is no supported data yet.
        }
        
        $cdb2 = \common\models\Pro_vehicle::find();
        $cdb2->where(['<', 'status', \common\models\Pro_vehicle::STATUS_MAINTENANCE]);
        if (!empty($arrRentingVehicleIds)) {
            $cdb2->andWhere(['not in', 'id', array_keys($arrRentingVehicleIds)]);
        }
        $waitingRentVehicleCount = $cdb2->count();
        
        $arrStateInfo = [
            'totalVehicleCount' => $totalVehicleCount,
            'waitingRentVehicleCount' => $waitingRentVehicleCount,
            'rentingVehicleCount' => $rentingVehicleCount,
            'bookingVehicleCount' => $bookingVehicleCount,
            'shortRentingVehicleCount' => $shortRentingVehicleCount,
            'longRentingVehicleCount' => $longRentingVehicleCount,
            'curDayDispathingVehicleCount' => $curDayDispathingVehicleCount,
            'twoHourDispatchingVehicleCount' => $twoHourDispatchingVehicleCount,
            'overtimeOrderCount' => $overtimeOrderCount,
            'maintenanceVehicleCount' => $maintenanceVehicleCount,
            'needUpkeepVehicleCount' => $needUpkeepVehicleCount,
            'needUpkeep2VehicleCount' => $needUpkeep2VehicleCount,
            'needYearcheckVehicleCount' => $needYearcheckVehicleCount,
            'needRenewalVehicleCount' => $needRenewalVehicleCount,
            'needPaybackVehicleCount' => $needPaybackVehicleCount,
            'dispatchingVehicleCount' => $dispatchingVehicleCount,
        ];
        return $arrStateInfo;
    }

    // 车辆租赁情况统计(柱状图)
    // 在租车辆|短租车辆|长租车辆|今日应还车|2小时内应还车|逾期未还|预授权到期|应分期结算|异店还车|待查违章|还车挂账|需查违章|违章将到期|已违章单|违章结算超期 
    public static function getCarstatus_rental_data() {
        $arrRentalData = self::getCarStatusRentalData();
        
        $arrRentalData2 = [
            $arrRentalData['rentingCount'],
            $arrRentalData['shortRentingCount'],
            $arrRentalData['longRentingCount'],
            $arrRentalData['curDayReturningCount'],
            $arrRentalData['twoHourReturningCount'],
            $arrRentalData['overtimeRentingCount'],
            $arrRentalData['poundageOvertimeCount'],
            $arrRentalData['stagingPaymentCount'],
            $arrRentalData['diffOfficeReturningCount'],
            $arrRentalData['violationCheckingCount'],
            $arrRentalData['arrearsReturnedCount'],
            $arrRentalData['needViolationCheckCount'],
            $arrRentalData['violationWouldOvertimeCount'],
            $arrRentalData['violationCount'],
            $arrRentalData['violationOvertimeCount'],
        ];
        
        return $arrRentalData2;
    }
    
    // 车辆状态信息统计(柱状图) 
    // 车辆总数|待租车辆|在租车辆|预定车辆|短租车辆|长租车辆|今日取车|2小时内取车|逾期订单|维修保养|应周期保养|应阶段保养|近期年检|近期续保|近期还贷 
    public static function getCarstatus_state_data() {
        $arrStateInfo = self::getCarStatusStateData();

        $arrStateInfo2 = [
            $arrStateInfo['totalVehicleCount'],
            $arrStateInfo['waitingRentVehicleCount'],
            $arrStateInfo['rentingVehicleCount'],
            $arrStateInfo['bookingVehicleCount'],
            $arrStateInfo['shortRentingVehicleCount'],
            $arrStateInfo['longRentingVehicleCount'],
            $arrStateInfo['curDayDispathingVehicleCount'],
            $arrStateInfo['twoHourDispatchingVehicleCount'],
            $arrStateInfo['overtimeOrderCount'],
            $arrStateInfo['maintenanceVehicleCount'],
            $arrStateInfo['needUpkeepVehicleCount'],
            $arrStateInfo['needUpkeep2VehicleCount'],
            $arrStateInfo['needYearcheckVehicleCount'],
            $arrStateInfo['needRenewalVehicleCount'],
            $arrStateInfo['needPaybackVehicleCount'],
        ];
        return $arrStateInfo2;
    }
    
    public static function analyseVehicleIncomeOutcomeData($arrIncomeData, $arrOutcomeData) {
        $arrStatisticItems = [
            [
                'incomename' => '还车收费',
                'outcomename' => '结算退费',
            ],
            [
                'incomename' => '违章收费',
                'outcomename' => '违章退费',
            ],
            [
                'incomename' => '预交定金',
                'outcomename' => '退预定金',
            ],
            [
                'incomename' => '预交租金',
                'outcomename' => '退预租金',
            ],
            [
                'incomename' => '续交租金',
                'outcomename' => '',
            ],
            [
                'incomename' => '租车押金',
                'outcomename' => '清退押金',
            ],
            [
                'incomename' => '违章押金',
                'outcomename' => '清退押金',
            ],
        ];
        $arrStatisticsData = [];
        foreach ($arrStatisticItems as $i => $cfg) {
            $arrStatisticsData[$i] = [
                'cash' => 0,
                'cheque' => 0,
                'swipe_card' => 0,
                'online_banking' => 0,
                'member' => 0,
                'alipay' => 0,
                'wxpay' => 0,
                'income' => 0,
                'outcome' => 0,
                'incomename' => $cfg['incomename'],
                'outcomename' => $cfg['outcomename'],
            ];
        }
        
        $arrSummaryData = [
            'cash' => 0,
            'cheque' => 0,
            'swipe_card' => 0,
            'online_banking' => 0,
            'member' => 0,
            'alipay' => 0,
            'wxpay' => 0,
            'income' => 0,
            'outcome' => 0,
            
            'outcome_fuel' => 0,
            'outcome_maintenance' => 0,
            'outcome_insurance' => 0,
            'outcome_disignated_driving' => 0,
            'outcome_other' => 0,
            'summary_other' => 0,
        ];
        
        foreach ($arrIncomeData as $row) {
            $i = 0;
            if ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_BOOK) {
                $i = 2;
            }
            else if ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT) {
                $i = 3;
            }
            else if ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT_RENEWAL) {
                $i = 4;
            }
            else if ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT) {
                $i = 5;
            }
            else if ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RETURN) {
                $i = 0;
            }
            else if ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_SETTLEMENT) {
                $i = 0;
            }
            else if ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_RELET) {
                $i = 4;
            }
            else if ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_VIOLATION) {
                $i = 6;
            }
            
            $amount = $row->receipt_amount;
            if ($row->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_CASH) {
                $arrStatisticsData[$i]['cash'] += $amount;
                $arrSummaryData['cash'] += $amount;
            }
            else if ($row->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_CHEQUE) {
                $arrStatisticsData[$i]['cheque'] += $amount;
                $arrSummaryData['cheque'] += $amount;
            }
            else if ($row->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_SWIPE_CARD) {
                $arrStatisticsData[$i]['swipe_card'] += $amount;
                $arrSummaryData['swipe_card'] += $amount;
            }
            else if ($row->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_ONLINE_BANKING) {
                $arrStatisticsData[$i]['online_banking'] += $amount;
                $arrSummaryData['online_banking'] += $amount;
            }
            else if ($row->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_MEMBER_CARD) {
                $arrStatisticsData[$i]['member'] += $amount;
                $arrSummaryData['member'] += $amount;
            }
            else if ($row->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_ALIPAY) {
                $arrStatisticsData[$i]['alipay'] += $amount;
                $arrSummaryData['alipay'] += $amount;
            }
            else if ($row->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_WEIXIN) {
                $arrStatisticsData[$i]['wxpay'] += $amount;
                $arrSummaryData['wxpay'] += $amount;
            }
            $arrStatisticsData[$i]['income'] += $amount;
            $arrSummaryData['income'] += $amount;
        }
        foreach ($arrOutcomeData as $row) {
            $amount = $row->amount;
            $i = -1;
            if ($row->sub_type == \common\models\Pro_expenditure_order::SUB_TYPE_VEHICLE_ORDER_BOOKING_RETURNS) {
                $i = 2;
            }
            else if ($row->sub_type == \common\models\Pro_expenditure_order::SUB_TYPE_VEHICLE_ORDER_RENTING_RETURNS) {
                $i = 3;
            }
            else if ($row->sub_type == \common\models\Pro_expenditure_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT_RETURNS) {
                $i = 5;
            }
            else if ($row->sub_type == \common\models\Pro_expenditure_order::SUB_TYPE_VEHICLE_ORDER_VIOLATION_DEPOSIT_RETURNS) {
                $i = 6;
            }
            else if ($row->sub_type == \common\models\Pro_expenditure_order::SUB_TYPE_VEHICLE_ORDER_SETTLEMENT_RETURNS) {
                $i = 0;
            }
            else if ($row->sub_type == \common\models\Pro_expenditure_order::SUB_TYPE_VEHICLE_ORDER_VIOLATION_RETURNS) {
                $i = 1;
            }
            else {
                $factor = \common\models\Pro_expenditure_order::SUB_TYPE_VEHICLE_COST_FACTOR;
                if ($row->sub_type == $factor + \common\models\Pro_vehicle_cost::TYPE_OIL) {
                    $arrSummaryData['outcome_fuel'] += $amount;
                }
                else if ($row->sub_type == $factor + \common\models\Pro_vehicle_cost::TYPE_MAINTENANCE
                    || $row->sub_type == $factor + \common\models\Pro_vehicle_cost::TYPE_UPKEEP) {
                    $arrSummaryData['outcome_maintenance'] += $amount;
                }
                else if ($row->sub_type == $factor + \common\models\Pro_vehicle_cost::TYPE_RENEWAL) {
                    $arrSummaryData['outcome_insurance'] += $amount;
                }
                else if ($row->sub_type == $factor + \common\models\Pro_vehicle_cost::TYPE_DESIGNATING) {
                    $arrSummaryData['outcome_disignated_driving'] += $amount;
                }
                else {
                    $arrSummaryData['outcome_other'] += $amount;
                }
                $arrSummaryData['summary_other'] += $amount;
            }
            if ($i >= 0) {
                $arrStatisticsData[$i]['outcome'] += $amount;
                $arrSummaryData['outcome'] += $amount;
            }
        }
        
        $arrSummaryData['amount'] = $arrSummaryData['income'] - $arrSummaryData['outcome'];
        
        $arrStatisticsData['summary'] = $arrSummaryData;
        
        return $arrStatisticsData;
    }
    
    public static function getVehicleCostRecords($vehicleIds) {
        $arrCostRowsArray = [];
        $cdb20 = \common\models\Pro_vehicle_cost::find();
        $cdb20->andWhere(['vehicle_id'=>$vehicleIds]);
        //$cdb20->andWhere(['and', ['>=', 'cost_time',$startTime], ['<=', 'cost_time',$endTime]]);
        $arrCostRowsArray[] = $cdb20->all();
        $cdb21 = \common\models\Pro_vehicle_designating_cost::find();
        $cdb21->andWhere(['vehicle_id'=>$vehicleIds]);
        //$cdb21->andWhere(['and', ['>=', 'time',$startTime], ['<=', 'time',$endTime]]);
        $arrCostRowsArray[] = $cdb21->all();
        $cdb22 = \common\models\Pro_vehicle_insurance::find();
        $cdb22->andWhere(['vehicle_id'=>$vehicleIds]);
        //$cdb22->andWhere(['and', ['>=', 'time',$startTime], ['<=', 'time',$endTime]]);
        $arrCostRowsArray[] = $cdb22->all();
        $cdb23 = \common\models\Pro_vehicle_oil_cost::find();
        $cdb23->andWhere(['vehicle_id'=>$vehicleIds]);
        //$cdb23->andWhere(['and', ['>=', 'time',$startTime], ['<=', 'time',$endTime]]);
        $arrCostRowsArray[] = $cdb23->all();
        
        $arrRows = [];
        foreach ($arrCostRowsArray as $arrCostRows) {
            foreach ($arrCostRows as $row) {
                $arrRows[] = $row;
            }
        }
        
        return $arrRows;
    }
    
    public static function getVehicleCostRecordsByTime($startTime, $endTime) {
        $arrCostRowsArray = [];
        $cdb20 = \common\models\Pro_vehicle_cost::find();
        $cdb20->where(['and', ['>=', 'cost_time',$startTime], ['<=', 'cost_time',$endTime]]);
        $arrCostRowsArray[] = $cdb20->all();
        $cdb21 = \common\models\Pro_vehicle_designating_cost::find();
        $cdb21->where(['and', ['>=', 'time',$startTime], ['<=', 'time',$endTime]]);
        $arrCostRowsArray[] = $cdb21->all();
        $cdb22 = \common\models\Pro_vehicle_insurance::find();
        $cdb22->where(['and', ['>=', 'time',$startTime], ['<=', 'time',$endTime]]);
        $arrCostRowsArray[] = $cdb22->all();
        $cdb23 = \common\models\Pro_vehicle_oil_cost::find();
        $cdb23->where(['and', ['>=', 'time',$startTime], ['<=', 'time',$endTime]]);
        $arrCostRowsArray[] = $cdb23->all();
        
        $arrRows = [];
        foreach ($arrCostRowsArray as $arrCostRows) {
            foreach ($arrCostRows as $row) {
                $arrRows[] = $row;
            }
        }
        
        return $arrRows;
    }
    
    public static function getTodaySignupedUsers() {
        $curTime = time();
        $dayStartTime = strtotime(date('Y-m-d', $curTime).' 00-00-00');
        $dayEndTime = strtotime(date('Y-m-d', $curTime).' 23-59-59');
        
        $signUpUserCount = 0;
        
        $cdb1 = \common\models\Pub_user_info::find();
        $cdb1->select(['id']);
        $cdb1->where(['and', ['>=', 'created_at', $dayStartTime], ['<=', 'created_at', $dayEndTime]]);
        $arrRows = $cdb1->all();
        $arrUserId = [];
        foreach ($arrRows as $row) {
            $signUpUserCount++;
            $arrUserId[] = $row->id;
        }
        
        $cdb2 = \common\models\Pub_user::find();
        $cdb2->where(['and', ['>=', 'created_at', $dayStartTime], ['<=', 'created_at', $dayEndTime]]);
        $cdb2->andWhere(['not in', 'info_id', $arrUserId]);
        $count = $cdb2->count();
        
        $signUpUserCount += $count;
        
        return $signUpUserCount;
    }
    
    public static function getWaitingRentVehicleCount() {
        $dayStartTime = strtotime(date('Y-m-d').' 00-00-00');
        $cdb = \common\models\Pro_vehicle_order::find();
        $cdb->select(['vehicle_id']);
        $cdb->where(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);
        $arrTimeCondition = \common\components\OrderModule::makeQueryConditionForOrderTimeRegion($dayStartTime, 0);
        if ($arrTimeCondition) {
            $cdb->andWhere($arrTimeCondition);
        }
        $arrOrderRows = $cdb->all();
        $arrVehicleIds = [];
        foreach ($arrOrderRows as $row) {
            $arrVehicleIds[$row->vehicle_id] = 1;
        }
        
        $cdb2 = \common\models\Pro_vehicle::find();
        $cdb2->where(['<', 'status', \common\models\Pro_vehicle::STATUS_MAINTENANCE]);
        if (!empty($arrVehicleIds)) {
            $cdb2->andWhere(['not in', 'id', array_keys($arrVehicleIds)]);
        }
        return $cdb2->count();
    }
    
    public static function getDailyOrderDataV0($startTime, $endTime, $belongOfficeId = 0) {
        $arrAdminIds = [];
        $arrOfficeIds = [];
        $arrOrderIds = [];
        $arrVehicleIds = [];
        
        $arrDailyDataRows = [];
        $cdb1 = \common\models\Pro_purchase_order::find();
        $cdb1->where(['and', ['>=', 'purchased_at',$startTime], ['<=', 'purchased_at',$endTime]]);
        $cdb1->andWhere(['status'=>\common\models\Pro_purchase_order::STATUS_SUCCEES]);
        if ($belongOfficeId) {
            $cdb1->andWhere(['belong_office_id'=>$belongOfficeId]);
        }
        $arrIncomeRows = $cdb1->all();
        foreach ($arrIncomeRows as $row) {
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
            if (!isset($arrOfficeIds[$row->belong_office_id])) {
                $arrOfficeIds[$row->belong_office_id] = 1;
            }
            if (!isset($arrOrderIds[$row->bind_id])) {
                $arrOrderIds[$row->bind_id] = 1;
            }
        }
        
        $arrAdminNames = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrOrders = [];
        $arrVehicleIdsByOrderIds = [];
        
        if (!empty($arrOrderIds)) {
            $cdb3 = \common\models\Pro_vehicle_order::find(true);
            $cdb3->where(['id'=>array_keys($arrOrderIds)]);
            $_rows = $cdb3->all();
            foreach ($_rows as $row) {
                $arrOrders[$row->id] = $row;
                
                if (!isset($arrVehicleIds[$row->vehicle_id])) {
                    $arrVehicleIds[$row->vehicle_id] = 1;
                }
                if (!isset($arrVehicleIdsByOrderIds[$row->id])) {
                    $arrVehicleIdsByOrderIds[$row->id] = $row->vehicle_id;
                }
            }
            
        }
        $arrVehiclePlates = [];
        if (!empty($arrVehicleIds)) {
            $cdb3 = \common\models\Pro_vehicle::find(true);
            $cdb3->where(['id'=>  array_keys($arrVehicleIds)]);
            $_rows = $cdb3->all();
            foreach ($_rows as $row) {
                $arrVehiclePlates[$row->id] = $row->plate_number;
            }
        }
        //$arrVehicleNames = \common\components\VehicleModule::getVehicleModelNamesArrayByIds(array_keys($arrVehicleModelIds));
        
        $arrDataByOrderId = [];
        foreach ($arrIncomeRows as $row) {
            $tmpOrderId = $row->bind_id;
            $tmpVehicleId = isset($arrVehicleIdsByOrderIds[$tmpOrderId]) ? $arrVehicleIdsByOrderIds[$tmpOrderId] : 0;
            
            $objOrder = isset($arrOrders[$tmpOrderId]) ? $arrOrders[$tmpOrderId] : null;
            if ($objOrder) {
                $o = isset($arrDataByOrderId[$tmpOrderId]) ? $arrDataByOrderId[$tmpOrderId] : \backend\models\Sts_vehicle_order_data::create([
                    'time' => $row->purchased_at,
                    'serial' => $objOrder->serial,
                    'plate' => isset($arrVehiclePlates[$tmpVehicleId]) ? $arrVehiclePlates[$tmpVehicleId] : '',
                    'customer_name' => isset($arrOrders[$tmpOrderId]) ? $arrOrders[$tmpOrderId]->customer_name : '',
                    'handler' => isset($arrAdminNames[$row->edit_user_id]) ? $arrAdminNames[$row->edit_user_id] : '',
                    'office' => isset($arrOfficeNames[$row->belong_office_id]) ? $arrOfficeNames[$row->belong_office_id] : '',
                    'days' => $objOrder->rent_days,
                    'pay_source' => $objOrder->pay_source,
                    'remark' => $objOrder->remark,
                ]);

                $amount = $row->receipt_amount;

                if ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_BOOK
                    || $row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT
                    || $row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT_RENEWAL
                    || $row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_RELET) {
                    $o->rent_amount += $amount;
                }
                elseif ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT) {
                    if ($row->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_PRE_LICENSING) {
                        $o->pre_licensing_amount += $amount;
                    }
                    else {
                        $o->deposit_amount += $amount;
                    }
                }
                elseif ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_OPTIONAL_SERVICE) {
                    $o->optional_amount += $amount;
                }
                elseif ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_DELAY_WASTE_WORKER) {
                    $o->delay_cost += $amount;
                }
                elseif ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_DAMAGE) {
                    $o->car_damage_amount += $amount;
                }
                elseif ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_OIL) {
                    $o->oil_amount += $amount;
                }
                elseif ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_VIOLATION) {
                    $o->violation_amount += $amount;
                }
                elseif ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_POUNDAGE) {
                    $o->poundage_amount += $amount;
                }
                elseif ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_SERVICE) {
                    $o->service_amount += $amount;
                }
                elseif ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ACCESSORIES) {
                    $o->accessories_amount += $amount;
                }
                else {
                    $o->other_amount += $amount;
                }

                $o->total_amount += $amount;

                $arrDataByOrderId[$tmpOrderId] = $o;
            }
        }
        
        foreach ($arrDataByOrderId as $o) {
            $arrDailyDataRows[] = $o;
        }
        
        return $arrDailyDataRows;
    }

    public static function getDailyOrderData($startTime, $endTime, $belongOfficeId = 0) {
        $arrAdminIds = [];
        $arrOfficeIds = [];
        $arrOrderIds = [];
        $arrVehicleIds = [];
        
        $arrDailyDataRows = [];
        
        $cdb0 = \common\models\Pro_vehicle_order_price_detail::find();
        $cdb0->where(['status'=>\common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL]);
        $cdb0->andWhere(['type'=> \common\models\Pro_vehicle_order_price_detail::TYPE_PAID]);
        $cdb0->andWhere(['and', ['>=', 'time',$startTime], ['<=', 'time',$endTime]]);
        if ($belongOfficeId) {
            $cdb0->andWhere(['belong_office_id'=>$belongOfficeId]);
        }
        $arrIncomeRows = $cdb0->asArray()->all();
        $priceKeys = \common\models\Pro_vehicle_order_price_detail::getPriceKeys();
        
        $arrOrderDetailsByOrderId = [];
        
        foreach ($arrIncomeRows as $row) {
            if (!isset($arrAdminIds[$row['edit_user_id']])) {
                $arrAdminIds[$row['edit_user_id']] = 1;
            }
            if (!isset($arrOfficeIds[$row['belong_office_id']])) {
                $arrOfficeIds[$row['belong_office_id']] = 1;
            }
            if (!isset($arrOrderIds[$row['order_id']])) {
                $arrOrderIds[$row['order_id']] = 1;
            }
            
            if (!isset($arrOrderDetailsByOrderId[$row['order_id']])) {
                $arrOrderDetailsByOrderId[$row['order_id']] = [];
            }
            
            $arr = $arrOrderDetailsByOrderId[$row['order_id']];
            
            if (floatval($row['summary_amount'])) {
                if (!isset($arr[$row['pay_source']])) {
                    $arr[$row['pay_source']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
                }
                
                $arr[$row['pay_source']]->addAmountPricesWithData($row, $priceKeys);
            }
            if (floatval($row['summary_deposit'])) {
                if (!isset($arr[$row['deposit_pay_source']])) {
                    $arr[$row['deposit_pay_source']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
                }
                
                $arr[$row['deposit_pay_source']]->addDepositPricesWithData($row, $priceKeys);
            }
            
            $arrOrderDetailsByOrderId[$row['order_id']] = $arr;
        }
        
        $arrAdminNames = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrOrders = [];
        $arrVehicleIdsByOrderIds = [];
        $arrVehiclePlates = [];
        
        if (!empty($arrOrderIds)) {
            $cdb3 = \common\models\Pro_vehicle_order::find(true);
            $cdb3->where(['id'=>array_keys($arrOrderIds)]);
            $_rows = $cdb3->asArray()->all();
            foreach ($_rows as $row) {
                $arrOrders[$row['id']] = $row;
                
                if (!isset($arrVehicleIds[$row['vehicle_id']])) {
                    $arrVehicleIds[$row['vehicle_id']] = 1;
                }
                if (!isset($arrVehicleIdsByOrderIds[$row['id']])) {
                    $arrVehicleIdsByOrderIds[$row['id']] = $row['vehicle_id'];
                }
            }
        }
        if (!empty($arrVehicleIds)) {
            $cdb3 = \common\models\Pro_vehicle::find(true);
            $cdb3->where(['id'=>  array_keys($arrVehicleIds)]);
            $_rows = $cdb3->asArray()->all();
            foreach ($_rows as $row) {
                $arrVehiclePlates[$row['id']] = $row['plate_number'];
            }
        }
        
        $arrConvertKeys = [
            'rent_amount' => ['price_rent', 'price_overtime', 'price_overmileage'],
            'optional_amount' => 'price_optional_service',
            'delay_cost' => 'price_working_loss',
            'car_damage_amount' => 'price_car_damage',
            'oil_amount' => 'price_oil',
            'price_different_office' => 'price_different_office',
            'price_designated_driving' => 'price_designated_driving',
            'price_agency' => 'price_agency',
            'violation_amount' => 'price_violation',
            'price_poundage' => 'price_poundage',
            'price_basic_insurance' => 'price_basic_insurance',
            'poundage_amount' => ['price_poundage', 'price_agency', 'price_oil_agency'],
            'service_amount' => ['price_take_car', 'price_return_car', 'price_designated_driving', 'price_designated_driving_overtime', 'price_designated_driving_overmileage'],
            'accessories_amount' => 'price_accessories',
            'other_amount' => ['price_other', /*'price_basic_insurance',*/ 'price_insurance_overtime'],
        ];
        
        //$arrDataByOrderId = [];
		
        foreach ($arrOrderDetailsByOrderId as $orderId => $arr) {
            foreach ($arr as $paySource => $row) {
                $row->summary();
                $tmpVehicleId = isset($arrVehicleIdsByOrderIds[$orderId]) ? $arrVehicleIdsByOrderIds[$orderId] : 0;

                $objOrder = isset($arrOrders[$orderId]) ? $arrOrders[$orderId] : null;
                if ($objOrder) {
                    $o = \backend\models\Sts_vehicle_order_data::create([
                        'time' => $row->time,
                        'serial' => $objOrder['serial'],
                        'source' => $objOrder['source'],
                        'plate' => isset($arrVehiclePlates[$tmpVehicleId]) ? $arrVehiclePlates[$tmpVehicleId] : '',
                        'customer_name' => $objOrder['customer_name'],
                        'handler' => isset($arrAdminNames[$row['edit_user_id']]) ? $arrAdminNames[$row['edit_user_id']] : '',
                        'office' => isset($arrOfficeNames[$row['belong_office_id']]) ? $arrOfficeNames[$row['belong_office_id']] : '',
                        'days' => $objOrder['rent_days'],
                        'pay_source' => $paySource,
                        'remark' => $objOrder['remark'],
                    ]);

                    if (floatval($row['summary_deposit'])) {
                        if ($row['deposit_pay_source'] == \common\models\Pro_vehicle_order::PAY_TYPE_PRE_LICENSING) {
                            $o->pre_licensing_amount += floatval($row['summary_deposit']);
                        }
                        else {
                            $o->deposit_amount += floatval($row['summary_deposit']);
                        }
                    }

                    foreach ($arrConvertKeys as $k1 => $k2) {
                        if (is_array($k2)) {
                            foreach ($k2 as $k3) {
                                $o->$k1 += floatval($row[$k3]);
                            }
                        }
                        else {
                            $o->$k1 += floatval($row[$k2]);
                        }
                    }

                    $o->total_amount += $row['summary_amount'];

                    //$arrDataByOrderId[$orderId] = $o;
                    $arrDailyDataRows[] = $o;
                }

            }
        }
        header("Content-type:text/html;charset=utf-8");
        //foreach ($arrDataByOrderId as $o) {
        //    $arrDailyDataRows[] = $o;
        //}
		// echo '<pre>';
        // print_r($arrDailyDataRows);exit;
        return $arrDailyDataRows;
    }
    
    public static function getDailyOrderDataColumns()
    {
        $dailyDataColumns = [
            'serial',
            'office',
            'plate',
			'customer_name',
			'pre_licensing_amount',
			'days',
			'deposit_amount', 
			'rent_amount', 
			'price_basic_insurance',
			'optional_amount',
            // ['attribute'=>'time','format'=>'text','value'=>function($model){return date('Y-m-d H:i:s', $model['time']); }], 
            // 'office', 'handler', 
            'handler',
            'car_damage_amount', 
			'oil_amount',
			'price_different_office',
            'delay_cost',
            'price_designated_driving',
			'violation_amount',
           'price_agency',
           'price_poundage',
			 
              // 'service_amount', 'accessories_amount', 
            'other_amount', 'total_amount', 
            ['attribute'=>'pay_source','value'=>function($model){ $arr = \common\components\OrderModule::getOrderPayTypeArray(); return (isset($arr[$model['pay_source']])?$arr[$model['pay_source']]:''); }], 
            ['attribute'=>'operation_type','value'=>function($model){ $arr = [0=>'']; return (isset($arr[$model['operation_type']])?$arr[$model['operation_type']]:''); }], 
            // ['attribute'=>'from_xtrip','value'=>function($model){ return $model['from_xtrip']> 0 ? '√' : ''; }], 
            ['attribute'=>'source','value'=>function($model){$arr = \common\components\OrderModule::getOrderSourceArray(); return (isset($arr[$model['source']])?$arr[$model['source']]:''); } ],
            'remark'
        ];
        return $dailyDataColumns;
    }
    
    public static function getMonthlyOrderIncomeData($orderStatus, $monthDate, $belongOfficeId) {
        if (empty($monthDate)) {
            $monthDate = date('Y-m');
        }
        if (empty($orderStatus)) {
            $orderStatus = \common\models\Pro_vehicle_order::STATUS_COMPLETED;
        }
        if ($belongOfficeId == \common\components\OfficeModule::HEAD_OFFICE_ID) {
            $belongOfficeId = 0;
        }
        $startTime = strtotime($monthDate.'-01 00:00:00');
        $endTime = strtotime(date('Y-m', $startTime + (32*86400)).'-01 00:00:00') - 1;
        
        $cdb = \common\models\Pro_vehicle_order::find();
        $cdb->orderBy("start_time asc");
        
        // conditions
        if ($belongOfficeId) {
            $cdb->andWhere(['belong_office_id' => $belongOfficeId]);
        }
        if ($orderStatus) {
            if ($orderStatus == \common\models\Pro_vehicle_order::STATUS_COMPLETED) {
                $cdb->andWhere(['status'=>[$orderStatus, \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING]]);
            }
            else {
                $cdb->andWhere(['status'=>$orderStatus]);
            }
        }
        if ($orderStatus == \common\models\Pro_vehicle_order::STATUS_COMPLETED
                || $orderStatus == \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING) {
            $cdb->andWhere(['and', ['>=', 'settlemented_at', $startTime], ['<=', 'settlemented_at', $endTime]]);
        }
        else {
            $cdb->andWhere(\common\components\OrderModule::makeQueryConditionForOrderTimeRegion($startTime, $endTime));
        }
        
        $arrOrderRows = $cdb->asArray()->all();

        $priceKeys = \common\models\Pro_vehicle_order_price_detail::getPriceKeys();

        $arrModelIds = [];
        $arrVehicleIds = [];
        $arrAdminIds = [];
        $arrUserIds = [];
        $arrOfficeIds = [];
        $arrOrders = [];
        foreach ($arrOrderRows as $row) {
            if (!isset($arrModelIds[$row['vehicle_model_id']])) {
                $arrModelIds[$row['vehicle_model_id']] = 1;
            }
            if (!isset($arrVehicleIds[$row['vehicle_id']])) {
                $arrVehicleIds[$row['vehicle_id']] = 1;
            }
            if (!isset($arrOfficeIds[$row['belong_office_id']])) {
                $arrOfficeIds[$row['belong_office_id']] = 1;
            }
            if (!isset($arrOfficeIds[$row['office_id_rent']])) {
                $arrOfficeIds[$row['office_id_rent']] = 1;
            }
            if (!isset($arrOfficeIds[$row['office_id_return']])) {
                $arrOfficeIds[$row['office_id_return']] = 1;
            }
            if ($row['edit_user_id'] && !isset($arrAdminIds[$row['edit_user_id']])) {
                $arrAdminIds[$row['edit_user_id']] = 1;
            }
            if ($row['settlement_user_id'] && !isset($arrAdminIds[$row['settlement_user_id']])) {
                $arrAdminIds[$row['settlement_user_id']] = 1;
            }
            if (!isset($arrUserIds[$row['user_id']])) {
                $arrUserIds[$row['user_id']] = 1;
            }

            $arrOrders[$row['id']] = $row;
        }

        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrOffices = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrModelNames = \common\components\VehicleModule::getVehicleModelNamesArrayByIds(array_keys($arrModelIds));
        $arrVehicleObjects = \common\components\VehicleModule::getVehicleObjects(array_keys($arrVehicleIds));
        $arrUserInfos = \common\components\UserModule::getUserInfoObjectsByUserIdArray(array_keys($arrUserIds));
        $arrVipLevels = \common\components\UserModule::getVipLevelsArray();
        /*
        $cdb2 = \common\models\Pro_vehicle_order_price_detail::find();
        $cdb2->where(['status'=>\common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL]);
        $cdb2->andWhere(['order_id'=>array_keys($arrOrders)]);
        $cdb2->andWhere(['type'=>\common\models\Pro_vehicle_order_price_detail::TYPE_PAID]);
        $arrPaidDetailRows = $cdb2->asArray()->all();
        $arrPaidBeforeCurMonth = [];
        $arrPaidCurMonth = [];
        $arrPaidTotal = [];
        foreach ($arrPaidDetailRows as $row) {
            if (!isset($arrPaidTotal[$row['order_id']])) {
                $arrPaidTotal[$row['order_id']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
            }
            $arrPaidTotal[$row['order_id']]->addAmountPricesWithData($row, $priceKeys);

            if ($row['time'] < $startTime) {
                if (!isset($arrPaidBeforeCurMonth[$row['order_id']])) {
                    $arrPaidBeforeCurMonth[$row['order_id']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
                }
                $arrPaidBeforeCurMonth[$row['order_id']]->addAmountPricesWithData($row, $priceKeys);
            }
            elseif ($row['time'] <= $endTime) {
                if (!isset($arrPaidCurMonth[$row['order_id']])) {
                    $arrPaidCurMonth[$row['order_id']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
                }
                $arrPaidCurMonth[$row['order_id']]->addAmountPricesWithData($row, $priceKeys);
            }
        }
        */
        $cdb3 = \common\models\Pro_vehicle_order_price_detail::find();
        $cdb3->where(['status'=>\common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL]);
        $cdb3->andWhere(['order_id'=>array_keys($arrOrders)]);
        $cdb3->andWhere(['type'=>\common\models\Pro_vehicle_order_price_detail::TYPE_SHOULD_PAY]);
        $arrTurnoverDetailRows = $cdb3->asArray()->all();
        $arrTurnoverBeforeCurMonth = [];
        $arrTurnoverCurMonth = [];
        foreach ($arrTurnoverDetailRows as $row) {
            if ($row['time'] < $startTime) {
                if (!isset($arrTurnoverBeforeCurMonth[$row['order_id']])) {
                    $arrTurnoverBeforeCurMonth[$row['order_id']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
                }
                $arrTurnoverBeforeCurMonth[$row['order_id']]->addAmountPricesWithData($row, $priceKeys);
            }
            elseif ($row['time'] <= $endTime) {
                if (!isset($arrTurnoverCurMonth[$row['order_id']])) {
                    $arrTurnoverCurMonth[$row['order_id']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
                }
                $arrTurnoverCurMonth[$row['order_id']]->addAmountPricesWithData($row, $priceKeys);
            }
        }
        
        $arrData = [];
        $tmpOrder = new \common\models\Pro_vehicle_order();
        $tmpOrder->rent_days = 0;
        foreach ($arrOrderRows as $row) {
            $vipLevel = (isset($arrUserInfos[$row['user_id']]) ? $arrUserInfos[$row['user_id']]['vip_level'] : 0);
            $o = array_merge([], $row);
            
            $o['edit_user_disp'] = (isset($arrAdmins[$row['edit_user_id']]) ? $arrAdmins[$row['edit_user_id']] : '');
            $o['settlement_user_disp'] = (isset($arrAdmins[$row['settlement_user_id']]) ? $arrAdmins[$row['settlement_user_id']] : '');
            $o['vehicle_model_name'] = (isset($arrModelNames[$row['vehicle_model_id']]) ? $arrModelNames[$row['vehicle_model_id']] : '');
            $o['plate_number'] = (isset($arrVehicleObjects[$row['vehicle_id']]) ? $arrVehicleObjects[$row['vehicle_id']]['plate_number'] : '');
            $o['belong_office_disp'] = (isset($arrOffices[$row['belong_office_id']]) ? $arrOffices[$row['belong_office_id']] : '');
            $o['rent_office_disp'] = (isset($arrOffices[$row['office_id_rent']]) ? $arrOffices[$row['office_id_rent']] : '');
            $o['return_office_disp'] = (isset($arrOffices[$row['office_id_return']]) ? $arrOffices[$row['office_id_return']] : '');
            $o['customer_vip_level'] = (isset($arrVipLevels[$vipLevel]) ? $arrVipLevels[$vipLevel] : '');
            
            $o['last_month_amount'] = (isset($arrTurnoverBeforeCurMonth[$row['id']]) ? $arrTurnoverBeforeCurMonth[$row['id']]->summary()->summary_amount : '');
            $o['cur_month_amount'] = (isset($arrTurnoverCurMonth[$row['id']]) ? $arrTurnoverCurMonth[$row['id']]->summary()->summary_amount : '');
            
            $arrData[] = $o;
        }
        
        return $arrData;
    }
    // sjj 替换上面方法 通过条件
    /**
    *@param $orderStatus        订单状态
    *@param $monthDate          订单时间 结束日期
    *@param $belongOfficeId     所属门店
    *@param $date_start         开始日期
    *@param $pay_type         租车类型
    */
    public static function getMonthlyOrderIncomeDataNew($orderStatus, $monthDate, $belongOfficeId,$date_start,$pay_type=0) {
        if (empty($monthDate)) {
            $monthDate = date('Y-m-d');
        }
        if (empty($orderStatus)) {
            $orderStatus = \common\models\Pro_vehicle_order::STATUS_COMPLETED;
        }
        if ($belongOfficeId == \common\components\OfficeModule::HEAD_OFFICE_ID) {
            $belongOfficeId = 0;
        }
       /* $startTime = strtotime($monthDate.'-01 00:00:00');
        $endTime = strtotime(date('Y-m', $startTime + (32*86400)).'-01 00:00:00') - 1;*/
        $startTime = strtotime($date_start.'00:00:00');
        $endTime = strtotime($monthDate.'23:59:59');

        $cdb = \common\models\Pro_vehicle_order::find();
        $cdb->orderBy("start_time asc");
        
        // conditions
        if ($belongOfficeId) {
            $cdb->andWhere(['belong_office_id' => $belongOfficeId]);
        }
        if ($orderStatus) {
            if ($orderStatus == \common\models\Pro_vehicle_order::STATUS_COMPLETED) {
                $cdb->andWhere(['status'=>[$orderStatus, \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING]]);
            }
            else {
                $cdb->andWhere(['status'=>$orderStatus]);
            }
        }
		
        if ($orderStatus == \common\models\Pro_vehicle_order::STATUS_COMPLETED || $orderStatus == \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING) {
            $cdb->andWhere(['and', ['>=', 'settlemented_at', $startTime], ['<=', 'settlemented_at', $endTime]]);
        }
        else {
			if($pay_type != 6){
				$cdb->andWhere(\common\components\OrderModule::makeQueryConditionForOrderTimeRegion($startTime, $endTime));
			}
        }
		
		if($pay_type > 0){
			$cdb->andWhere(['pay_type'=>$pay_type]);
		}
        // $customer_name = '丰利波';
        // $cdb->andWhere('customer_name LIKE :keywords2', [':keywords2' => '%'.$customer_name.'%']);
       
        $arrOrderRows = $cdb->asArray()->all();
		
        $priceKeys = \common\models\Pro_vehicle_order_price_detail::getPriceKeys();

        $arrModelIds = [];
        $arrVehicleIds = [];
        $arrAdminIds = [];
        $arrUserIds = [];
        $arrOfficeIds = [];
        $arrOrders = [];
        foreach ($arrOrderRows as $row) {
            if (!isset($arrModelIds[$row['vehicle_model_id']])) {
                $arrModelIds[$row['vehicle_model_id']] = 1;
            }
            if (!isset($arrVehicleIds[$row['vehicle_id']])) {
                $arrVehicleIds[$row['vehicle_id']] = 1;
            }
            if (!isset($arrOfficeIds[$row['belong_office_id']])) {
                /*sjj 所属门店不能是门店下的便利店*/
                $cdbb = \common\models\Pro_office::find(true);
                $cdbb->select(['parent_id']);
                $cdbb->where(['id' => $row['belong_office_id']]);
                $parent_id = $cdbb->asArray()->one();
                if($parent_id['parent_id'] > 0){
                    $arrOfficeIds[$parent_id['parent_id']] = 1;
                }else{
                    $arrOfficeIds[$row['belong_office_id']] = 1;
                }
                /*sjj*/
                // $arrOfficeIds[$row['belong_office_id']] = 1;
            }
            if (!isset($arrOfficeIds[$row['office_id_rent']])) {
                $arrOfficeIds[$row['office_id_rent']] = 1;
            }
            if (!isset($arrOfficeIds[$row['office_id_return']])) {
                $arrOfficeIds[$row['office_id_return']] = 1;
            }
            if ($row['edit_user_id'] && !isset($arrAdminIds[$row['edit_user_id']])) {
                $arrAdminIds[$row['edit_user_id']] = 1;
            }
            if ($row['settlement_user_id'] && !isset($arrAdminIds[$row['settlement_user_id']])) {
                $arrAdminIds[$row['settlement_user_id']] = 1;
            }
            if (!isset($arrUserIds[$row['user_id']])) {
                $arrUserIds[$row['user_id']] = 1;
            }

            $arrOrders[$row['id']] = $row;
        }

        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrOffices = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrModelNames = \common\components\VehicleModule::getVehicleModelNamesArrayByIds(array_keys($arrModelIds));
        $arrVehicleObjects = \common\components\VehicleModule::getVehicleObjects(array_keys($arrVehicleIds));
        $arrUserInfos = \common\components\UserModule::getUserInfoObjectsByUserIdArray(array_keys($arrUserIds));
        $arrVipLevels = \common\components\UserModule::getVipLevelsArray();
        $arrSettlementStatus = \common\components\VehicleModule::getVehicleSettlementStatusArray();
        /*
        $cdb2 = \common\models\Pro_vehicle_order_price_detail::find();
        $cdb2->where(['status'=>\common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL]);
        $cdb2->andWhere(['order_id'=>array_keys($arrOrders)]);
        $cdb2->andWhere(['type'=>\common\models\Pro_vehicle_order_price_detail::TYPE_PAID]);
        $arrPaidDetailRows = $cdb2->asArray()->all();
        $arrPaidBeforeCurMonth = [];
        $arrPaidCurMonth = [];
        $arrPaidTotal = [];
        foreach ($arrPaidDetailRows as $row) {
            if (!isset($arrPaidTotal[$row['order_id']])) {
                $arrPaidTotal[$row['order_id']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
            }
            $arrPaidTotal[$row['order_id']]->addAmountPricesWithData($row, $priceKeys);

            if ($row['time'] < $startTime) {
                if (!isset($arrPaidBeforeCurMonth[$row['order_id']])) {
                    $arrPaidBeforeCurMonth[$row['order_id']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
                }
                $arrPaidBeforeCurMonth[$row['order_id']]->addAmountPricesWithData($row, $priceKeys);
            }
            elseif ($row['time'] <= $endTime) {
                if (!isset($arrPaidCurMonth[$row['order_id']])) {
                    $arrPaidCurMonth[$row['order_id']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
                }
                $arrPaidCurMonth[$row['order_id']]->addAmountPricesWithData($row, $priceKeys);
            }
        }
        */
        $cdb3 = \common\models\Pro_vehicle_order_price_detail::find();
        $cdb3->where(['status'=>\common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL]);
        $cdb3->andWhere(['order_id'=>array_keys($arrOrders)]);
        $cdb3->andWhere(['type'=>\common\models\Pro_vehicle_order_price_detail::TYPE_SHOULD_PAY]);
        $arrTurnoverDetailRows = $cdb3->asArray()->all();
        $arrTurnoverBeforeCurMonth = [];
        $arrTurnoverCurMonth = [];
        foreach ($arrTurnoverDetailRows as $row) {
            if ($row['time'] < $startTime) {
                if (!isset($arrTurnoverBeforeCurMonth[$row['order_id']])) {
                    $arrTurnoverBeforeCurMonth[$row['order_id']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
                }
                $arrTurnoverBeforeCurMonth[$row['order_id']]->addAmountPricesWithData($row, $priceKeys);
            }
            elseif ($row['time'] <= $endTime) {
                if (!isset($arrTurnoverCurMonth[$row['order_id']])) {
                    $arrTurnoverCurMonth[$row['order_id']] = \common\models\Pro_vehicle_order_price_detail::createObjectWithBaseData($row, $priceKeys);
                }
                $arrTurnoverCurMonth[$row['order_id']]->addAmountPricesWithData($row, $priceKeys);
            }
        }
        
        $arrData = [];
        $tmpOrder = new \common\models\Pro_vehicle_order();
        $tmpOrder->rent_days = 0;
        foreach ($arrOrderRows as $row) {
            $vipLevel = (isset($arrUserInfos[$row['user_id']]) ? $arrUserInfos[$row['user_id']]['vip_level'] : 0);
            $o = array_merge([], $row);
            
            $o['edit_user_disp'] = (isset($arrAdmins[$row['edit_user_id']]) ? $arrAdmins[$row['edit_user_id']] : '');
            $o['settlement_user_disp'] = (isset($arrAdmins[$row['settlement_user_id']]) ? $arrAdmins[$row['settlement_user_id']] : '');
            $o['vehicle_model_name'] = (isset($arrModelNames[$row['vehicle_model_id']]) ? $arrModelNames[$row['vehicle_model_id']] : '');
            $o['plate_number'] = (isset($arrVehicleObjects[$row['vehicle_id']]) ? $arrVehicleObjects[$row['vehicle_id']]['plate_number'] : '');
            $o['belong_office_disp'] = (isset($arrOffices[$row['belong_office_id']]) ? $arrOffices[$row['belong_office_id']] : '');
            $o['rent_office_disp'] = (isset($arrOffices[$row['office_id_rent']]) ? $arrOffices[$row['office_id_rent']] : '');
            $o['return_office_disp'] = (isset($arrOffices[$row['office_id_return']]) ? $arrOffices[$row['office_id_return']] : '');
            $o['customer_vip_level'] = (isset($arrVipLevels[$vipLevel]) ? $arrVipLevels[$vipLevel] : '');
            $o['settlement_status_name'] = (isset($arrSettlementStatus[$row['settlement_status']]) ? $arrSettlementStatus[$row['settlement_status']] : '');
            
            $o['last_month_amount'] = (isset($arrTurnoverBeforeCurMonth[$row['id']]) ? $arrTurnoverBeforeCurMonth[$row['id']]->summary()->summary_amount : '');
            $o['cur_month_amount'] = (isset($arrTurnoverCurMonth[$row['id']]) ? $arrTurnoverCurMonth[$row['id']]->summary()->summary_amount : '');
            $o['unit_price_basic_insurance'] = $row['unit_price_basic_insurance'] * $row['rent_days'];
            
            $arrData[] = $o;
        }
        
        return $arrData;
    }
    // sjj
    
    public static function getMonthlyOrderIncomeDataColumns($status,$pay_type = 0)
    {
        $cols = [
            'serial' => 'serial',
            'belong_office_id' => ['attribute'=>'belong_office_id','header'=>'车辆所属门店','value'=>function($model){ return $model['belong_office_disp']; }], 
            //sjj
            'office_id_rent' => ['attribute'=>'office_id_rent','header'=>'在租门店','value'=>function($model){ return $model['rent_office_disp']; }], 
            'settlement_status' => ['attribute'=>'settlement_status','header'=>'结算状态','value'=>function($model){ return $model['settlement_status_name']; }], 
            //sjj
            'vehicle_id' => ['attribute'=>'vehicle_id','value'=>function($model){ return $model['plate_number']; }], 
            'vehicle_model_id' => ['attribute'=>'vehicle_model_id','value'=>function($model){ return $model['vehicle_model_name']; }], 
            'type' => ['attribute'=>'type','value'=>function($model){ $arr = \common\components\OrderModule::getOrderTypeArray(); return (isset($arr[$model['type']]) ? $arr[$model['type']] : ''); }], 
            'customer_name' => 'customer_name',
            'customer_telephone' => 'customer_telephone',
            'customer_vip_level' => 'customer_vip_level',
            'total_amount' => 'total_amount',
            'paid_amount' => 'paid_amount',
            'price_rent' => ['attribute'=>'price_rent', 'format'=>'text', 'value'=>function($model){ return $model['price_rent']+$model['price_overtime']+$model['price_overmileage'];}],
            'rent_per_day' => 'rent_per_day', 
            'price_overtime' => 'price_overtime', 
            'price_overmileage' => 'price_overmileage', 
            'price_designated_driving' => ['attribute'=>'price_designated_driving', 'format'=>'text', 'value'=>function($model){ return $model['price_designated_driving']+$model['price_designated_driving_overtime']+$model['price_designated_driving_overmileage'];}], 
            'price_designated_driving_overtime' => 'price_designated_driving_overtime', 
            'price_designated_driving_overmileage' => 'price_designated_driving_overmileage', 
            'price_oil' => 'price_oil', 
            'price_oil_agency' => 'price_oil_agency', 
            'price_car_damage' => 'price_car_damage', 
            'price_violation' => 'price_violation', 
            'price_other' => 'price_other', 
            'price_poundage' => 'price_poundage', 
            'price_deposit' => ['attribute'=>'price_deposit','header'=>'租赁押金','format'=>'text','value'=>function($model){return ($model['price_deposit']+$model['price_deposit_violation']).(($model['deposit_pay_source']==\common\models\Pro_vehicle_order::PAY_TYPE_PRE_LICENSING)?'(预授权)':''); }], 
            'price_optional_service' => 'price_optional_service', 
            'price_preferential' => 'price_preferential', 
            'unit_price_basic_insurance' => 'unit_price_basic_insurance', 
            'price_insurance_overtime' => 'price_insurance_overtime', 
            'price_different_office' => 'price_different_office',
            'price_bonus_point_deduction' => 'price_bonus_point_deduction', 
            'price_gift' => 'price_gift', 
            'price_take_car' => 'price_take_car', 
            'price_return_car' => 'price_return_car', 
            'preferential_type' => 'preferential_type', 
            'preferential_info' => 'preferential_info', 
            'start_time' => ['attribute'=>'start_time','format'=>'text','value'=>function($model){return date('Y-m-d H:i:s', $model['start_time']); }], 
            'new_end_time' => ['attribute'=>'new_end_time','format'=>'text','value'=>function($model){return date('Y-m-d H:i:s', $model['new_end_time']); }], 
            'settlemented_at' => ['attribute'=>'settlemented_at','format'=>'text','value'=>function($model){return date('Y-m-d H:i:s', $model['settlemented_at']); }], 
            'car_dispatched_at' => ['attribute'=>'car_dispatched_at','header'=>'承租时间','format'=>'text','value'=>function($model){return date('Y-m-d H:i:s', $model['car_dispatched_at']); }], 
            'car_returned_at' => ['attribute'=>'car_returned_at','header'=>'还车时间','format'=>'text','value'=>function($model){return date('Y-m-d H:i:s', $model['car_returned_at']); }], 
            'car_dispatched_date' => ['attribute'=>'car_dispatched_at','header'=>'承租时间','format'=>'text','value'=>function($model){return date('m-d', $model['car_dispatched_at']); }], 
            'car_returned_date' => ['attribute'=>'car_returned_at','header'=>'还车时间','format'=>'text','value'=>function($model){return date('m-d', $model['car_returned_at']); }], 
            'settlemented_date' => ['attribute'=>'settlemented_date','header'=>'结算日期','format'=>'text','value'=>function($model){return date('m-d', $model['settlemented_at']); }], 
            'rent_days' => 'rent_days', 
            'price_left' => ['attribute'=>'price_left','header'=>'挂账','value'=>function($model){ return $model['total_amount'] - $model['paid_amount']; }], 
            'source' => ['attribute'=>'source','value'=>function($model){ $arr = \common\components\OrderModule::getOrderSourceArray(); return (isset($arr[$model['source']]) ? $arr[$model['source']] : ''); }], 
            'pay_source' => ['attribute'=>'pay_source','value'=>function($model){ $arr = \common\components\OrderModule::getOrderPayTypeArray(); return (isset($arr[$model['pay_source']]) ? $arr[$model['pay_source']] : ''); }], 
            'deposit_pay_source' => ['attribute'=>'deposit_pay_source','value'=>function($model){ $arr = \common\components\OrderModule::getOrderPayTypeArray(); return (isset($arr[$model['deposit_pay_source']]) ? $arr[$model['deposit_pay_source']] : ''); }], 
            'price_pre_license' => ['attribute'=>'price_pre_license','header'=>'预授权','format'=>'text','value'=>function($model){return ($model['price_deposit']+$model['price_deposit_violation']).($model['deposit_pay_source']==\common\models\Pro_vehicle_order::PAY_TYPE_PRE_LICENSING?'(预授权)':''); }],
            'last_month_amount' => ['attribute'=>'last_month_amount','header'=>'上月营业额','format'=>'text','value'=>function($model){return $model['last_month_amount']; }],
            'cur_month_amount' => ['attribute'=>'cur_month_amount','header'=>'本月营业额','format'=>'text','value'=>function($model){return $model['cur_month_amount']; }],
            'price_delay_working' => 'price_working_loss',
            'price_depreciation' => ['attribute'=>'price_depreciation','header'=>'折旧费','format'=>'text','value'=>function($model){return ''; }],
            'price_discount' => ['attribute'=>'price_discount','header'=>'折让','format'=>'text','value'=>function($model){return ''; }],
            'price_other_amount' => ['attribute'=>'price_other', 'format'=>'text', 'value'=>function($model){ 
                return $model['price_other']+$model['price_poundage']+$model['price_oil_agency']+
                        $model['price_take_car']+$model['price_return_car']+$model['price_violation']+$model['price_insurance_overtime']+$model['price_different_office']; }],
            'remark' => 'remark',
            'total_settlement_amount' => ['attribute'=>'total_settlement_amount', 'header'=>'总结算额','format'=>'text','value'=>function($model){ return $model['last_month_amount']+$model['cur_month_amount']; }],
        ];
       
        $columns = null;
        if ($status == \common\models\Pro_vehicle_order::STATUS_COMPLETED || $status == \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING) {
            $columns = [$cols['serial'], $cols['source'], $cols['vehicle_id'], 
                $cols['vehicle_model_id'], $cols['customer_name'],$cols['customer_telephone'], //$cols['customer_telephone'], $cols['customer_vip_level'], 
                $cols['start_time'], $cols['car_returned_at'], $cols['settlemented_at'], 
                $cols['car_dispatched_date'], $cols['car_returned_date'], $cols['settlemented_date'], 
                $cols['rent_per_day'], $cols['rent_days'], 
                $cols['total_amount'], $cols['pay_source'], $cols['price_pre_license'], 
                $cols['last_month_amount'], $cols['price_rent'],$cols['unit_price_basic_insurance'],$cols['price_optional_service'],
                $cols['price_car_damage'], $cols['price_delay_working'], $cols['price_depreciation'],
                $cols['price_oil'], $cols['price_designated_driving'], $cols['price_other_amount'],
                $cols['price_discount'], $cols['price_preferential'], $cols['cur_month_amount'],
                $cols['total_settlement_amount'], $cols['paid_amount'], 
                $cols['price_left'], $cols['belong_office_id'],$cols['office_id_rent'],$cols['settlement_status']
            ];
        }
        else {
            $columns = [$cols['serial'], $cols['source'], $cols['vehicle_id'], $cols['vehicle_model_id'], 
                $cols['customer_name'], $cols['customer_telephone'], $cols['customer_vip_level'], 
                $cols['deposit_pay_source'], $cols['price_deposit'], $cols['paid_amount'], 
                $cols['total_amount'], $cols['belong_office_id'], $cols['office_id_rent'],
                $cols['start_time'], $cols['new_end_time'], $cols['rent_days'], $cols['price_left'],$cols['settlement_status']
            ];
        }
        return $columns;
    }
    

    /**
    *@author sjj
    *@desc 统计订单结算后预授权退还日期
    *@since 2017-9-26
    */
    static public function getReturnDepositOrderCountByUser()
    {
        $userinfo = \Yii::$app->user->identity;
        $belong_office_id = $userinfo->belong_office_id;
        $cdb = \common\models\Pro_office::find();
        if($belong_office_id > 0){
            $cdb->select(['id']);
            $cdb->where(['=', 'status', 0]);
            $cdb->andWhere(['=', 'parent_id', $belong_office_id]);
            $arrOfficesRows = $cdb->asArray()->all();
            $offices[] = $belong_office_id;
            foreach ($arrOfficesRows as $key => $value) {
                $offices[] = $value['id'];
            }

        }else{//小于0为总admin账号
            $cdb->select(['id']);
            $cdb->where(['=', 'status', 0]);
            $arrOfficesRows = $cdb->asArray()->all();
            foreach ($arrOfficesRows as $key => $value) {
                $offices[] = $value['id'];
            }
        }

        // 100待查违章
        $time = time()-86400*10;//$car_returned_at<$time
        $cdb2 = \common\models\Pro_vehicle_order::find();
        $cdb2->where(['=', 'status', 100]);
        $cdb2->andWhere(['<', 'car_returned_at', $time]);
        $cdb2->andWhere(['in', 'belong_office_id', $offices]);
        $orders = $cdb2->count();
        // $sql=$cdb2->createCommand()->getRawSql();
        return $orders;
    }


}
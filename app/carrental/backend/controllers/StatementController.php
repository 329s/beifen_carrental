<?php
namespace backend\controllers;

use Yii;
use common\helpers\MyFunction;

/**
 * Description of StatementController
 *
 * @author kevin
 */
class StatementController  extends \backend\components\AuthorityController
{
    private $pageSize = 20;
    
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
    
    public function actionCarstatus() {
        // 在租车辆|短租车辆|长租车辆|今日应还车|2小时内应还车|逾期未还|预授权到期|应分期结算|异店还车|待查违章|还车挂账|需查违章|违章将到期|已违章单|违章结算超期 
        $arrRentalLabels = [
            '在租车辆',
            '短租车辆',
            '长租车辆',
            '今日应还车',
            '2小时内应还车',
            '逾期未还',
            '预授权到期',
            '应分期结算',
            '异店还车',
            '待查违章',
            '还车挂账',
            '需查违章',
            '违章将到期',
            '已违章单',
            '违章结算超期',
        ];
        // 车辆总数|待租车辆|在租车辆|预定车辆|短租车辆|长租车辆|今日取车|2小时内取车|逾期订单|维修保养|应周期保养|应阶段保养|近期年检|近期续保|近期还贷 
        $arrStateLabels = [
            '车辆总数',
            '待租车辆',
            '在租车辆',
            '预定车辆',
            '短租车辆',
            '长租车辆',
            '今日取车',
            '2小时内取车',
            '逾期订单',
            '维修保养',
            '应周期保养',
            '应阶段保养',
            '近期年检',
            '近期续保',
            '近期还贷',
        ];
        
        $arrData = [
            'arrRentalLabels' => $arrRentalLabels,
            'arrRentalData' => \backend\components\StatisticsService::getCarstatus_rental_data(),
            'arrStateLabels' => $arrStateLabels,
            'arrStateData' => \backend\components\StatisticsService::getCarstatus_state_data(),
        ];
        
        return $this->renderPartial('carstatus', $arrData);
    }
    
    public function actionCarstatus_rental_data() {
        $arrData = \backend\components\StatisticsService::getCarstatus_rental_data();
        echo json_encode($arrData);
    }
    
    // 车辆状态信息统计(柱状图)
    // 车辆总数|待租车辆|在租车辆|预定车辆|短租车辆|长租车辆|今日取车|2小时内取车|逾期订单|维修保养|应周期保养|应阶段保养|近期年检|近期续保|近期还贷 
    public function actionCarstatus_state_data() {
        $arrData = \backend\components\StatisticsService::getCarstatus_state_data();
        echo json_encode($arrData);
    }
    
    // 每日经营报表 
    public function actionDaily_old() {
        /*
         * 今日账目统计
            今日账目明细表
                时间|合同号|项目|摘要|收入|支出|经手人
                    合计：收入|支出
                    今日收入支出结余：
            今日账目收支统计
                收入项目|现金支付|支票支付|刷卡支付|网银支付|会员支付|费用小计|支出项目|支出金额
                收入合计。。。                                                 支出合计
                                今日收入支出结余：
            今日其他支出统计
                加油支出|维修保养|保险支出|代驾支出|日常其他
                                其他支出合计：
            未退押金账目统计
                租车押金总计：     违章押金总计：
        经营分析日报
            今日经营预测
                昨日逾期车辆(辆)          昨日逾期订单(辆)
                        合计                      合计
                今日应取车辆              今日应还车辆
                        合计(辆)                  合计(辆 元)
                今日应分期结算            今日预授权提醒
                        合计(辆 元)               合计(辆)
            今日经营情况
                今日出车车辆              今日结算订单
                        合计(辆)                  合计(辆)
                今日新增订单              今日取消订单
                        合计(辆 元)               合计(辆 元)
                新增客户数量              客户咨询数量
                        (位)                      (位)
            明日经营预测
                明日应还车辆              明日应分期结算
                        合计(辆 元)               合计(辆 元)
                明日取车数量              明日预授权提醒
                        合计(辆)                  合计(辆)
            每日经营日报短信发送设置
                。。。
         */
        
        $belongOfficeId = intval(\Yii::$app->request->getParam('office_id'));
        $date = \Yii::$app->request->getParam('date');
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        if ($belongOfficeId == \common\components\OfficeModule::HEAD_OFFICE_ID) {
            $belongOfficeId = 0;
        }
        $startTime = strtotime($date.' 00:00:00');
        $endTime = strtotime($date.' 23:59:59');
        
        $arrAdminIds = [];
        $arrOfficeIds = [];
        $arrOrderIds = [];
        $arrReletOrderIds = [];
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
            if ($row->type == \common\models\Pro_purchase_order::TYPE_VEHICLE_ORDER
                || $row->type == \common\models\Pro_purchase_order::TYPE_VIOLATION) {
                if (!isset($arrOrderIds[$row->bind_id])) {
                    $arrOrderIds[$row->bind_id] = 1;
                }
            }
            elseif ($row->type == \common\models\Pro_purchase_order::TYPE_VEHICLE_RELET) {
                if (!isset($arrReletOrderIds[$row->bind_id])) {
                    $arrReletOrderIds[$row->bind_id] = 1;
                }
            }
        }
        
        $cdb2 = \common\models\Pro_expenditure_order::find();
        $cdb2->where(['and', ['>=', 'expenditured_at',$startTime], ['<=', 'expenditured_at',$endTime]]);
        $cdb2->andWhere(['status'=>\common\models\Pro_expenditure_order::STATUS_SUCCEES]);
        if ($belongOfficeId) {
            $cdb2->andWhere(['belong_office_id'=>$belongOfficeId]);
        }
        $arrOutcomeRows = $cdb2->all();
        $arrCostItemIds = [];
        foreach ($arrOutcomeRows as $row) {
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
            if (!isset($arrOfficeIds[$row->belong_office_id])) {
                $arrOfficeIds[$row->belong_office_id] = 1;
            }
            
            if (isset($arrCostItemIds[$row->type])) {
                if (!isset($arrCostItemIds[$row->type][$row->bind_id])) {
                    $arrCostItemIds[$row->type][$row->bind_id] = 1;
                }
            }
            else {
                $arrCostItemIds[$row->type] = [$row->bind_id=>1];
            }
        }
        $arrOrderIdsByReletIds = [];
        if (!empty($arrReletOrderIds)) {
            $cdb3 = \common\models\Pro_vehicle_order_relet::find(true);
            $cdb3->where(['id'=>array_keys($arrReletOrderIds)]);
            $_row = $cdb3->all();
            foreach ($_row as $row) {
                if (!isset($arrOrderIds[$row->order_id])) {
                    $arrOrderIds[$row->order_id] = 1;
                }
                if (!isset($arrOrderIdsByReletIds[$row->id])) {
                    $arrOrderIdsByReletIds[$row->id] = $row->order_id;
                }
            }
        }
        $arrCostItemVehicleIds = [];
        foreach ($arrCostItemIds as $t => $a) {
            $cdb3 = null;
            if (($t & \common\models\Pro_expenditure_order::SUB_TYPE_VEHICLE_COST_FACTOR)) {
                if (($t & \common\models\Pro_vehicle_cost::TYPE_RENEWAL) != 0) {
                    $cdb3 = \common\models\Pro_vehicle_insurance::find(true);
                }
                elseif (($t & \common\models\Pro_vehicle_cost::TYPE_DESIGNATING) != 0) {
                    $cdb3 = \common\models\Pro_vehicle_designating_cost::find(true);
                }
                elseif (($t & \common\models\Pro_vehicle_cost::TYPE_OIL) != 0) {
                    $cdb3 = \common\models\Pro_vehicle_oil_cost::find(true);
                }
                else {
                    $cdb3 = \common\models\Pro_vehicle_cost::find(true);
                }
                
                $cdb3->where(['id' => array_keys($a)]);
                $_rows = $cdb3->all();
                
                $_arr = [];
                foreach ($_rows as $row) {
                    $_arr[$row->id] = $row->vehicle_id;
                    if (!isset($arrVehicleIds[$row->vehicle_id])) {
                        $arrVehicleIds[$row->vehicle_id] = 1;
                    }
                }
                $arrCostItemVehicleIds[$t] = $_arr;
            }
        }
        
        $arrAdminNames = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrOrders = [];
        $arrVehicleIdsByOrderIds = [];
        
        if (!empty($arrOrderIds)) {
            $cdb3 = \common\models\Pro_vehicle_order::find(true);
            $cdb3->where(['id'=>  array_keys($arrOrderIds)]);
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
        
        foreach ($arrIncomeRows as $row) {
            $o = [
                'time' => $row->purchased_at,
                'serial' => $row->serial,
                'item' => $row->getTypeText(),
                'summary' => $row->getAbstract(),
                'income' => $row->receipt_amount,
                'outcome' => 0,
                'handler' => isset($arrAdminNames[$row->edit_user_id]) ? $arrAdminNames[$row->edit_user_id] : '',
                'office' => isset($arrOfficeNames[$row->belong_office_id]) ? $arrOfficeNames[$row->belong_office_id] : '',
            ];
            
            $tmpOrderId = 0;
            if ($row->type == \common\models\Pro_purchase_order::TYPE_VEHICLE_ORDER
                || $row->type == \common\models\Pro_purchase_order::TYPE_VIOLATION) {
                $tmpOrderId = $row->bind_id;
            }
            else if ($row->type == \common\models\Pro_purchase_order::TYPE_VIOLATION) {
                if (isset($arrOrderIdsByReletIds[$row->bind_id])) {
                    $tmpOrderId = $arrOrderIdsByReletIds[$row->bind_id];
                }
            }
            $tmpVehicleId = isset($arrVehicleIdsByOrderIds[$tmpOrderId]) ? $arrVehicleIdsByOrderIds[$tmpOrderId] : 0;
            
            $o['plate'] = isset($arrVehiclePlates[$tmpVehicleId]) ? $arrVehiclePlates[$tmpVehicleId] : '';
            $o['customer_name'] = isset($arrOrders[$tmpOrderId]) ? $arrOrders[$tmpOrderId]->customer_name : '';
            
            $arrDailyDataRows[] = $o;
        }
        foreach ($arrOutcomeRows as $row) {
            $o = [
                'time' => $row->expenditured_at,
                'serial' => $row->serial,
                'item' => $row->getTypeText(),
                'summary' => $row->getAbstract(),
                'income' => 0,
                'outcome' => $row->amount,
                'handler' => isset($arrAdminNames[$row->edit_user_id]) ? $arrAdminNames[$row->edit_user_id] : '',
                'office' => isset($arrOfficeNames[$row->belong_office_id]) ? $arrOfficeNames[$row->belong_office_id] : '',
            ];
            
            $tmpVehicleId = 0;
            $tmpOrderId = 0;
            if (($row->type & \common\models\Pro_expenditure_order::SUB_TYPE_VEHICLE_COST_FACTOR) != 0) {
                if (isset($arrCostItemIds[$row->type])) {
                    $_a = $arrCostItemIds[$row->type];
                    $tmpVehicleId = isset($_a[$row->bind_id]) ? $_a[$row->bind_id] : 0;
                }
            }
            else {
                if (isset($arrOrderIdsByReletIds[$row->bind_id])) {
                    $tmpOrderId = $arrOrderIdsByReletIds[$row->bind_id];
                    $tmpVehicleId = isset($arrVehicleIdsByOrderIds[$tmpOrderId]) ? $arrVehicleIdsByOrderIds[$tmpOrderId] : 0;
                }
            }
            
            $o['plate'] = isset($arrVehiclePlates[$tmpVehicleId]) ? $arrVehiclePlates[$tmpVehicleId] : '';
            $o['customer_name'] = isset($arrOrders[$tmpOrderId]) ? $arrOrders[$tmpOrderId]->customer_name : '';
            
            $arrDailyDataRows[] = $o;
        }
        
        $arrStatisticsRows = \backend\components\StatisticsService::analyseVehicleIncomeOutcomeData($arrIncomeRows, $arrOutcomeRows);
        $arrSummaryData = $arrStatisticsRows['summary'];
        unset($arrStatisticsRows['summary']);
        
        $arrDailyDataModels = \backend\components\StatisticsService::getDailyOrderData($startTime, $endTime, $belongOfficeId);
        $_arrDailyDataRow = [];
        foreach ($arrDailyDataModels as $model) {
            $_arrDailyDataRow[] = $model->getAttributes();
        }
        $arrDailyData = [
            'rows'=>$_arrDailyDataRow,
            // 'footer'=>[
            //     ['other_amount'=>'合计：', 'total_amount'=>0]
            //     //['summary'=>'合计：', 'income'=>$arrSummaryData['income'], 'outcomename'=>'', 'outcome'=>$arrSummaryData['outcome']],
            //     //['summary'=>'今日收入支出结余：', 'income'=>$arrSummaryData['amount']],
            // ]
        ];
        // $total = 0;
        // foreach ($arrDailyData['rows'] as $row) {
        //     $total += $row['total_amount'];
        // }
        // $arrDailyData['footer'][0]['total_amount'] = $total;

        $arrDailySummaryData = [
            'rows'=>$arrStatisticsRows,
            'footer'=>[
                [
                    'incomename' => '收入合计',
                    'cash' => $arrSummaryData['cash'],
                    'cheque' => $arrSummaryData['cheque'],
                    'swipe_card' => $arrSummaryData['swipe_card'],
                    'online_banking' => $arrSummaryData['online_banking'],
                    'member' => $arrSummaryData['member'],
                    'alipay' => $arrSummaryData['alipay'],
                    'wxpay' => $arrSummaryData['wxpay'],
                    'income' => $arrSummaryData['income'],
                    'outcomename' => '支出合计',
                    'outcome' => $arrSummaryData['outcome'],
                ],
                ['outcomename'=>'今日收入支出结余：', 'outcome'=>$arrSummaryData['amount']],
            ]
        ];
        
        $arrDailyOtherOutcomeData = [
            'rows'=>[
                [
                    'fuel' => $arrSummaryData['outcome_fuel'],
                    'maintenance' => $arrSummaryData['outcome_maintenance'],
                    'insurance' => $arrSummaryData['outcome_insurance'],
                    'disignated_driving' => $arrSummaryData['outcome_disignated_driving'],
                    'other' => $arrSummaryData['outcome_other'],
                ],
            ],
            'footer'=>[
                ['disignated_driving'=>'其他支出合计：', 'other'=>$arrSummaryData['summary_other']],
            ]
        ];
        
        $arrAnalysisData = [
            'vehicle_deposit_need_return' => 0,
            'violation_deposit_need_return' => 0,
            'yesterday_overtime_cars' => 0,
            'yesterday_overtime_orders' => 0,
            'today_need_take' => 0,
            'today_need_return' => 0,
            'today_need_period_settlement' => 0,
            'today_need_period_settlement_amount' => 0,
            'today_pre_licensing_remind' => 0,
            'today_pre_licensing_remind_amount' => 0,
            'today_dispatched' => 0,
            'today_dispatched_amount' => 0,
            'today_settlemented' => 0,
            'today_settlemented_amount' => 0,
            'today_new_orders' => 0,
            'today_new_orders_amount' => 0,
            'today_canceled_orders' => 0,
            'today_canceled_orders_amount' => 0,
            'tomorrow_need_return' => 0,
            'tomorrow_need_return_amount' => 0,
            'tomorrow_need_period_settlement' => 0,
            'tomorrow_need_period_settlement_amount' => 0,
            'tomorrow_pre_licensing_remind' => 0,
            'tomorrow_pre_licensing_remind_amount' => 0,
            'tomorrow_need_take' => 0,
        ];
        
        $cdbOrder = \common\models\Pro_vehicle_order::find();
        
        $cdbOrder->andWhere(['<', 'start_time', $endTime + 86400]);
        //$cdbOrder->andWhere(['>', 'new_end_time', $startTime]);
        $arrRows = $cdbOrder->all();
        foreach ($arrRows as $row) {
            if ($row->status == \common\models\Pro_vehicle_order::STATUS_RENTING) {
                if ($row->new_end_time >= $startTime) {
                    $arrAnalysisData['vehicle_deposit_need_return'] += $row->price_deposit;
                    if ($row->new_end_time < $endTime) {
                        $arrAnalysisData['today_need_return']++;
                    }
                    else {
                        $arrAnalysisData['tomorrow_need_return']++;
                        $arrAnalysisData['tomorrow_need_return_amount'] += $row->total_amount;
                    }
                }
                else if ($row->new_end_time < $startTime) {
                    $arrAnalysisData['yesterday_overtime_cars']++;
                    $arrAnalysisData['yesterday_overtime_orders']++;
                }
                
                if ($row->start_time>=$startTime && $row->start_time <= $endTime) {
                    $arrAnalysisData['today_dispatched']++;
                    $arrAnalysisData['today_dispatched_amount'] += $row->paid_amount;
                }
            }
            else if ($row->status == \common\models\Pro_vehicle_order::STATUS_BOOKED) {
                if ($row->start_time < $startTime) {
                    $arrAnalysisData['yesterday_overtime_orders']++;
                    $arrAnalysisData['today_need_take']++;
                }
                else if ($row->start_time < $endTime) {
                    $arrAnalysisData['today_need_take']++;
                }
                else {
                    $arrAnalysisData['tomorrow_need_take']++;
                }
                
                if ($row->start_time>=$startTime && $row->start_time <= $endTime) {
                    $arrAnalysisData['today_new_orders']++;
                    $arrAnalysisData['today_new_orders_amount'] += $row->paid_amount;
                }
            }
            else if ($row->status == \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING) {
                if ($row->new_end_time + \common\components\OrderModule::ORDER_VIOLATION_CHECKING_DURATION >= $startTime) {
                    $arrAnalysisData['violation_deposit_need_return'] += $row->price_deposit;
                }
                
                if ($row->new_end_time>=$startTime && $row->new_end_time <= $endTime) {
                    $arrAnalysisData['today_settlemented']++;
                    $arrAnalysisData['today_settlemented_amount'] += $row->paid_amount;
                }
            }
            else if ($row->status == \common\models\Pro_vehicle_order::STATUS_CANCELLED) {
                if ($row->updated_at>=$startTime && $row->updated_at <= $endTime) {
                    $arrAnalysisData['today_canceled_orders']++;
                    $arrAnalysisData['today_canceled_orders_amount'] += $row->paid_amount;
                }
            }
        }
        
        $arrDepositData = [
            [
                'item_0' => '租车押金总计：',
                'value_0' => $arrAnalysisData['vehicle_deposit_need_return'],
                'item_1' => '违章押金总计：',
                'value_1' => $arrAnalysisData['violation_deposit_need_return'],
            ]
        ];
        
        $arrTodayBusinessForecastData = [
            ['item' => '昨日逾期车辆', 'cars' => $arrAnalysisData['yesterday_overtime_cars']],
            ['item' => '昨日逾期订单', 'cars' => $arrAnalysisData['yesterday_overtime_orders']],
            ['item' => '今日应取车辆', 'cars' => $arrAnalysisData['today_need_take']],
            ['item' => '今日应还车辆', 'cars' => $arrAnalysisData['today_need_return']],
            ['item' => '今日应分期结算', 'cars' => $arrAnalysisData['today_need_period_settlement'], 'amount'=>$arrAnalysisData['today_need_period_settlement_amount']],
            ['item' => '今日预授权提醒', 'cars' => $arrAnalysisData['today_pre_licensing_remind'], 'amount'=>$arrAnalysisData['today_pre_licensing_remind_amount']],
        ];
        $arrTodayBusinessData = [
            ['item' => '今日出车车辆', 'cars' => $arrAnalysisData['today_dispatched'], 'amount'=>$arrAnalysisData['today_dispatched_amount']],
            ['item' => '今日结算车辆', 'cars' => $arrAnalysisData['today_settlemented'], 'amount'=>$arrAnalysisData['today_settlemented_amount']],
            ['item' => '今日新增订单', 'cars' => $arrAnalysisData['today_new_orders'], 'amount'=>$arrAnalysisData['today_new_orders_amount']],
            ['item' => '今日取消订单', 'cars' => $arrAnalysisData['today_canceled_orders'], 'amount'=>$arrAnalysisData['today_canceled_orders_amount']],
            ['item' => '新增客户数量', 'num' => \common\models\Pub_user::find()->where(['and', ['>=', 'created_at', $startTime], ['<=', 'created_at', $endTime]])->count()],
            ['item' => '客户咨询数量', 'num' => \backend\models\Pro_user_consult::find()->where(['and', ['>=', 'created_at', $startTime], ['<=', 'created_at', $endTime]])->count()],
        ];
        $arrTomorrowBusinessForecastData = [
            ['item' => '明日应还车辆', 'cars' => $arrAnalysisData['tomorrow_need_return'], 'amount'=>$arrAnalysisData['tomorrow_need_return_amount']],
            ['item' => '明日应分期结算', 'cars' => $arrAnalysisData['tomorrow_need_period_settlement'], 'amount'=>$arrAnalysisData['tomorrow_need_period_settlement_amount']],
            ['item' => '明日取车数量', 'cars' => $arrAnalysisData['tomorrow_need_take']],
            ['item' => '明日预授权提醒', 'cars' => $arrAnalysisData['tomorrow_pre_licensing_remind']],
        ];
        
        if (\Yii::$app->request->getParam('export') == 'excel') {
            $model = new \backend\models\Sts_vehicle_order_data();
            $dailyDataColumns = [['attribute'=>'time','format'=>'text','value'=>function($model){return date('Y-m-d H:i:s', $model->time); }], 
                'office', 'handler', 'plate', 'customer_name', 
                'days', 'pre_licensing_amount', 'deposit_amount', 'rent_amount', 
                'optional_amount', 'delay_cost', 'car_damage_amount', 'oil_amount',
                'violation_amount', 'poundage_amount', 'service_amount', 'accessories_amount', 
                'other_amount', 'total_amount', 
                ['attribute'=>'pay_source','value'=>function($model){ $arr = \common\components\OrderModule::getOrderPayTypeArray(); return (isset($arr[$model->pay_source])?$arr[$model->pay_source]:''); }], 
                ['attribute'=>'operation_type','value'=>function($model){ $arr = [0=>'']; return (isset($arr[$model->operation_type])?$arr[$model->operation_type]:''); }], 
                ['attribute'=>'from_xtrip','value'=>function($model){ return $model->from_xtrip > 0 ? '√' : ''; }], 
                'remark'];
            \moonland\phpexcel\Excel::export([
                'models' => $arrDailyDataModels,
                'columns' => $dailyDataColumns,
                'headers' => $model->attributeLabels(),
                'fileName' => '日报表-'.$date,
                'format' => 'Excel2007',
            ]);
        }
        else {
            $arrData = [
                'arrDailyData' => $arrDailyData,
                'arrDailySummaryData' => $arrDailySummaryData,
                'arrDailyOtherOutcomeData' => $arrDailyOtherOutcomeData,
                'arrDepositData' => $arrDepositData,
                'arrTodayBusinessForecastData' => $arrTodayBusinessForecastData,
                'arrTodayBusinessData' => $arrTodayBusinessData,
                'arrTomorrowBusinessForecastData' => $arrTomorrowBusinessForecastData,
                'date' => (empty($date) ? '' : $date),
                'belongOfficeId' => (empty($belongOfficeId) ? '' : $belongOfficeId),
            ];

            return $this->renderPartial('daily', $arrData);
        }
    }
    
    // 每日经营报表 
    public function actionDaily() {
        $belongOfficeId = intval(\Yii::$app->request->getParam('office_id'));
        $date = \Yii::$app->request->getParam('date');
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        if ($belongOfficeId == \common\components\OfficeModule::HEAD_OFFICE_ID) {
            $belongOfficeId = 0;
        }
        $startTime = strtotime($date.' 00:00:00');
        $endTime = strtotime($date.' 23:59:59');
        
        $arrDailyDataModels = \backend\components\StatisticsService::getDailyOrderData($startTime, $endTime, $belongOfficeId);
        $arrData = [
            'arrDailyDataModels' => $arrDailyDataModels,
            'dailyDataColumns' => \backend\components\StatisticsService::getDailyOrderDataColumns(),
            'date' => (empty($date) ? '' : $date),
            'belongOfficeId' => (empty($belongOfficeId) ? '' : $belongOfficeId),
        ];

        return $this->renderPartial('daily', $arrData);
    }
    
    public function actionDailyExport() {
        $belongOfficeId = intval(\Yii::$app->request->getParam('office_id'));
        $date = \Yii::$app->request->getParam('date');
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        if ($belongOfficeId == \common\components\OfficeModule::HEAD_OFFICE_ID) {
            $belongOfficeId = 0;
        }
        $startTime = strtotime($date.' 00:00:00');
        $endTime = strtotime($date.' 23:59:59');
        
        $arrDailyDataModels = \backend\components\StatisticsService::getDailyOrderData($startTime, $endTime, $belongOfficeId);
        $model = new \backend\models\Sts_vehicle_order_data();
        // echo "string";exit;
        \moonland\phpexcel\Excel::export([
            'models' => $arrDailyDataModels,
            'columns' => \backend\components\StatisticsService::getDailyOrderDataColumns(),
            'headers' => $model->attributeLabels(),
            'fileName' => '日报表-'.$date,
            'format' => 'Excel2007',
        ]);
    }
    
    // 月度经营报表 
    public function actionMonthly() {
        /*
         * 月度账目统计
            按日期月度账目统计报表（点击日期可查看当日账目明细）
                。。。
            按车辆月度账目统计报表
                。。。
            按车型月度账目统计报表
                。。。
            按费用月度账目统计报表
                。。。
            月度其他支出统计
                。。。
         */
        
        $arrMonthlyDateDataRows = [];
        $arrMonthlyVehicleDataRows = [];
        $arrMonthlyVehicleModelDataRows = [];
        
        $year = \Yii::$app->request->getParam('year');
        $month = \Yii::$app->request->getParam('month');
        if (empty($year)) {
            $year = date('Y');
        }
        if (empty($month)) {
            $month = date('m');
        }
        $startTime = strtotime("{$year}-{$month}-01 00:00:00");
        // 下月第一天起始时间-1
        $endTime = strtotime(date('Y-m', $startTime+32*86400).'-01 00:00:00') - 1;
        //Yii::error(" monthly start_time:{$startTime} start_date:{$year}-{$month}-01 00:00:00 endtime:{$endTime} end_date:".date('Y-m', $startTime+32*86400).'-01 00:00:00');
        
        $arrVehicleInfos = [];
        $arrVehicleModelInfos = [];
        $cdb01 = \common\models\Pro_vehicle::find();
        $arrVehicleRows = $cdb01->all();
        $cdb02 = \common\models\Pro_vehicle_model::find();
        $arrVehicleModelRows = $cdb02->all();
        foreach ($arrVehicleRows as $row) {
            $arrVehicleInfos[$row->id] = ['plate_number'=>$row->plate_number, 'model_id'=>$row->model_id];
        }
        foreach ($arrVehicleModelRows as $row) {
            $arrVehicleModelInfos[$row->id] = ['name'=>$row->vehicle_model];
        }
        
        $cdb1 = \common\models\Pro_purchase_order::find();
        $cdb1->where(['and', ['>=', 'purchased_at',$startTime], ['<=', 'purchased_at',$endTime]]);
        $cdb1->andWhere(['status'=>\common\models\Pro_purchase_order::STATUS_SUCCEES]);
        $arrIncomeRows = $cdb1->all();
        
        $cdb2 = \common\models\Pro_expenditure_order::find();
        $cdb2->where(['and', ['>=', 'expenditured_at',$startTime], ['<=', 'expenditured_at',$endTime]]);
        $cdb2->andWhere(['status'=>\common\models\Pro_expenditure_order::STATUS_SUCCEES]);
        $arrOutcomeRows = $cdb2->all();
        
        $cdb10 = \common\models\Pro_vehicle_order::find();
        $cdb10->where(['and', ['>', 'new_end_time',$startTime], ['<', 'start_time',$endTime]]);
        $cdb10->andWhere(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
        $arrVehicleOrderRows = $cdb10->all();
        
        $arrCostRowsArray = \backend\components\StatisticsService::getVehicleCostRecordsByTime($startTime, $endTime);
        
        $arrTmpData = [];
        foreach ($arrIncomeRows as $row) {
            $key = date('Y-m-d', $row->purchased_at);
            if (!isset($arrTmpData[$key])) {
                $arrTmpData[$key] = [
                    'name' => $key,
                    'income' => 0,
                    'outcome' => 0,
                    'subtotal' => 0,
                ];
            }
            
            $amount = $row->receipt_amount;
            $arrTmpData[$key]['income'] += $amount;
        }
        foreach ($arrOutcomeRows as $row) {
            $key = date('Y-m-d', $row->expenditured_at);
            if (!isset($arrTmpData[$key])) {
                $arrTmpData[$key] = [
                    'name' => $key,
                    'income' => 0,
                    'outcome' => 0,
                    'subtotal' => 0,
                ];
            }
            
            $amount = $row->amount;
            $arrTmpData[$key]['outcome'] += $amount;
        }
        ksort($arrTmpData);
        $arrMonthlyDateSummary = [
            'income' => 0,
            'outcome' => 0,
            'subtotal' => 0,
        ];
        foreach ($arrTmpData as $o) {
            $o['subtotal'] = $o['income'] - $o['outcome'];
            $arrMonthlyDateDataRows[] = $o;
            
            $arrMonthlyDateSummary['income'] += $o['income'];
            $arrMonthlyDateSummary['outcome'] += $o['outcome'];
            $arrMonthlyDateSummary['subtotal'] += $o['subtotal'];
        }
        
        $arrStatisticsRows = \backend\components\StatisticsService::analyseVehicleIncomeOutcomeData($arrIncomeRows, $arrOutcomeRows);
        $arrSummaryData = $arrStatisticsRows['summary'];
        unset($arrStatisticsRows['summary']);
        
        $arrVehicleStatisticsData = [];
        $arrVehicleModelStatisticsData = [];
        $tmpVehicleId = 0;
        $tmpVehicleModelId = 0;
        foreach ($arrVehicleOrderRows as $row) {
            $tmpVehicleId = $row->vehicle_id;
            $o = isset($arrVehicleInfos[$tmpVehicleId]) ? $arrVehicleInfos[$tmpVehicleId] : null;
            if (!$o) {
                $tmpVehicleId = 0;
                $tmpVehicleModelId = 0;
            }
            else {
                $tmpVehicleModelId = $o['model_id'];
            }
                
            if (!isset($arrVehicleStatisticsData[$tmpVehicleId])) {
                $arrVehicleStatisticsData[$tmpVehicleId] = [
                    'name'=>($o ? $o['plate_number'] : \Yii::t('carrental', 'Non plate number').'/'.Yii::t('carrental', 'Booking vehicle model')), 
                    'income'=>0, 'outcome'=>0, 'subtotal'=>0
                ];
            }
            if (!isset($arrVehicleModelStatisticsData[$tmpVehicleModelId])) {
                $arrVehicleModelStatisticsData[$tmpVehicleModelId] = [
                    'name'=>(isset($arrVehicleModelInfos[$tmpVehicleModelId]) ? $arrVehicleModelInfos[$tmpVehicleModelId]['name'] : \Yii::t('carrental', 'Unknown vehicle model')), 
                    'income'=>0, 'outcome'=>0, 'subtotal'=>0
                ];
            }
            
            $amount = $row->paid_amount;
            $arrVehicleStatisticsData[$tmpVehicleId]['income'] += $amount;
            $arrVehicleModelStatisticsData[$tmpVehicleModelId]['income'] += $amount;
        }
        foreach ($arrCostRowsArray as $row) {
            $tmpVehicleId = $row->vehicle_id;
            $o = isset($arrVehicleInfos[$tmpVehicleId]) ? $arrVehicleInfos[$tmpVehicleId] : null;
            if (!$o) {
                $tmpVehicleId = 0;
                $tmpVehicleModelId = 0;
            }
            else {
                $tmpVehicleModelId = $o['model_id'];
            }

            if (!isset($arrVehicleStatisticsData[$tmpVehicleId])) {
                $arrVehicleStatisticsData[$tmpVehicleId] = [
                    'name'=>($o ? $o['plate_number'] : \Yii::t('carrental', 'Non plate number').'/'.Yii::t('carrental', 'Booking vehicle model')), 
                    'income'=>0, 'outcome'=>0, 'subtotal'=>0
                ];
            }
            if (!isset($arrVehicleModelStatisticsData[$tmpVehicleModelId])) {
                $arrVehicleModelStatisticsData[$tmpVehicleModelId] = [
                    'name'=>(isset($arrVehicleModelInfos[$tmpVehicleModelId]) ? $arrVehicleModelInfos[$tmpVehicleModelId]['name'] : \Yii::t('carrental', 'Unknown vehicle model')), 
                    'income'=>0, 'outcome'=>0, 'subtotal'=>0
                ];
            }

            $amount = $row->getExpenditureAmount();
            $arrVehicleStatisticsData[$tmpVehicleId]['outcome'] += $amount;
            $arrVehicleModelStatisticsData[$tmpVehicleModelId]['outcome'] += $amount;
        }
        
        $vehicleTotalIncome = 0;
        $vehicleTotalOutcome = 0;
        $vehicleModelTotalIncome = 0;
        $vehicleModelTotalOutcome = 0;
        foreach ($arrVehicleStatisticsData as $vehicleId => $o) {
            $arrMonthlyVehicleDataRows[] = [
                'name' => $o['name'],
                'income' => $o['income'],
                'outcome' => $o['outcome'],
                'subtotal' => $o['income'] - $o['outcome'],
            ];
            $vehicleTotalIncome += $o['income'];
            $vehicleTotalOutcome += $o['outcome'];
        }
        foreach ($arrVehicleModelStatisticsData as $vehicleModelId => $o) {
            $arrMonthlyVehicleModelDataRows[] = [
                'name' => $o['name'],
                'income' => $o['income'],
                'outcome' => $o['outcome'],
                'subtotal' => $o['income'] - $o['outcome'],
            ];
            $vehicleModelTotalIncome += $o['income'];
            $vehicleModelTotalOutcome += $o['outcome'];
        }
        
        $arrMonthlyDateData = [
            'rows'=>$arrMonthlyDateDataRows,
            'footer'=>[
                ['name'=>'合计：', 'income'=>$arrMonthlyDateSummary['income'], 'outcome'=>$arrMonthlyDateSummary['outcome'], 'subtotal'=>$arrMonthlyDateSummary['subtotal']],
            ]
        ];
        $arrMonthlyVehicleData = [
            'rows'=>$arrMonthlyVehicleDataRows,
            'footer'=>[
                ['name'=>'合计：', 'income'=>$vehicleTotalIncome, 'outcome'=>$vehicleTotalOutcome, 'subtotal'=>$vehicleTotalIncome - $vehicleTotalOutcome],
            ]
        ];
        $arrMonthlyVehicleModelData = [
            'rows'=>$arrMonthlyVehicleModelDataRows,
            'footer'=>[
                ['name'=>'合计：', 'income'=>$vehicleModelTotalIncome, 'outcome'=>$vehicleModelTotalOutcome, 'subtotal'=>$vehicleModelTotalIncome - $vehicleModelTotalOutcome],
            ]
        ];
        
        $arrMonthlySummaryData = [
            'rows'=>$arrStatisticsRows,
            'footer'=>[
                [
                    'incomename' => '收入合计',
                    'cash' => $arrSummaryData['cash'],
                    'cheque' => $arrSummaryData['cheque'],
                    'swipe_card' => $arrSummaryData['swipe_card'],
                    'online_banking' => $arrSummaryData['online_banking'],
                    'member' => $arrSummaryData['member'],
                    'alipay' => $arrSummaryData['alipay'],
                    'wxpay' => $arrSummaryData['wxpay'],
                    'income' => $arrSummaryData['income'],
                    'outcomename' => '支出合计',
                    'outcome' => $arrSummaryData['outcome'],
                ],
                ['outcomename'=>'月度收入支出结余：', 'outcome'=>$arrSummaryData['amount']],
            ]
        ];
        
        $arrMonthlyOtherOutcomeData = [
            'rows'=>[
                [
                    'fuel' => $arrSummaryData['outcome_fuel'],
                    'maintenance' => $arrSummaryData['outcome_maintenance'],
                    'insurance' => $arrSummaryData['outcome_insurance'],
                    'disignated_driving' => $arrSummaryData['outcome_disignated_driving'],
                    'other' => $arrSummaryData['outcome_other'],
                ],
            ],
            'footer'=>[
                ['disignated_driving'=>'其他支出合计：', 'other'=>$arrSummaryData['summary_other']],
            ]
        ];
        
        $arrData = [
            'arrMonthlyDateData' => $arrMonthlyDateData,
            'arrMonthlyVehicleData' => $arrMonthlyVehicleData,
            'arrMonthlyVehicleModelData' => $arrMonthlyVehicleModelData,
            'arrMonthlySummaryData' => $arrMonthlySummaryData,
            'arrMonthlyOtherOutcomeData' => $arrMonthlyOtherOutcomeData,
            'year' => $year,
            'month' => $month,
        ];
        
        return $this->renderPartial('monthly', $arrData);
    }
    
    // 车辆收入统计 
    public function actionCarincome() {
        /*
         * 车辆牌号|出租天数|统计天数|出租率(%)|车辆未结收入(元)|车辆已结收入(元)|车辆支出(元)|合计(元)
        查询条件：
            车牌号码，车辆型号，统计时间：起-止，所属门店
         */
        $arrData = [
            'pageSize' => $this->pageSize,
        ];
        
        return $this->renderPartial('carincome', $arrData);
    }
    
    public function actionCarincomedata_list() {
        // get pagination
        $intPage = intval(Yii::$app->request->getParam('page'));
        if ($intPage == 0)
            $intPage = 1;
        $numPerPage = intval(Yii::$app->request->getParam('rows'));
        $numPerPage = intval($numPerPage);
        if (!$numPerPage)
            $numPerPage = $this->pageSize;
        
        // get order
        $intSort = strval(Yii::$app->request->getParam('sort'));
        $intSortDirection = strval(Yii::$app->request->getParam('order'));
        if (!empty($intSort) && !empty($intSortDirection)) {
            $order = $intSort . " " . $intSortDirection;
        }
        
        $cdb = \common\models\Pro_vehicle::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $plateNumber = \Yii::$app->request->getParam('plate_number');
        $vehicleModel = intval(\Yii::$app->request->getParam('vehicle_model_id'));
        $startTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->getParam('start_time'), '00:00:00');
        $endTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->getParam('end_time'), '23:59:59');
        $belongOffice = intval(\Yii::$app->request->getParam('office_id'));
        if (!empty($plateNumber)) {
            $cdb->andWhere(['plate_number' => $plateNumber]);
        }
        if ($vehicleModel) {
            $cdb->andWhere(['model_id' => $vehicleModel]);
        }
        if ($belongOffice) {
            $cdb->andWhere(['belong_office_id' => $belongOffice]);
        }
        
        $statisticDays = 1;
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        $arrOfficeIds = [];
        $arrVehicleIds = [];
        $arrVehicleModelIds = [];
        
        // 判断是否需要详细收入，详细支出项目统计
        // 
        
        $curTime = time();
        $earlistTime = $curTime;
        foreach ($arrRows as $row) {
            if (!isset($arrOfficeIds[$row->belong_office_id])) {
                $arrOfficeIds[$row->belong_office_id] = 1;
            }
            if (!isset($arrVehicleModelIds[$row->model_id])) {
                $arrVehicleModelIds[$row->model_id] = 1;
            }
            $arrVehicleIds[] = $row->id;
            if ($row->created_at < $earlistTime) {
                $earlistTime = $earlistTime;
            }
        }
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrVehicleNames = \common\components\VehicleModule::getVehicleModelNamesArrayByIds(array_keys($arrVehicleModelIds));
        
        $arrDataByVehicleIds = [];
        
        if (!empty($arrVehicleIds)) {
            $cdb2 = \common\models\Pro_vehicle_order::find(true);
            $cdb2->where(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
            $cdb2->andWhere(['vehicle_id' => $arrVehicleIds]);
            
            $arrOrderRows = $cdb2->all();
            $arrCostRows = \backend\components\StatisticsService::getVehicleCostRecords($arrVehicleIds);
            
            foreach ($arrOrderRows as $row) {
                $o = (isset($arrDataByVehicleIds[$row->id]) ? $arrDataByVehicleIds[$row->vehicle_id] : [
                    'rent_days' => 0,
                    'income0' => 0,
                    'income1' => 0,
                    'outcome' => 0,
                ]);
                
                $o['rent_days'] += $row->rent_days;
                $o['income0'] += $row->total_amount - $row->paid_amount;
                $o['income1'] = $row->paid_amount;
            }
            foreach ($arrCostRows as $row) {
                $o = (isset($arrDataByVehicleIds[$row->id]) ? $arrDataByVehicleIds[$row->vehicle_id] : [
                    'rent_days' => 0,
                    'income0' => 0,
                    'income1' => 0,
                    'outcome' => 0,
                ]);
                
                $o['outcome'] = $row->getExpenditureAmount();
            }
        }
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = (isset($arrDataByVehicleIds[$row->id]));
            
            $rentDays = ($o ? $o['rent_days'] : 0);
            
            $arrData[] = [
                'name' => $row->plate_number,
                'model' => isset($arrVehicleNames[$row->model_id]) ? $arrVehicleNames[$row->model_id] : '',
                'belong_office_id' => $row->belong_office_id,
                'rent_days' => $rentDays,
                'statistic_days' => $statisticDays,
                'rent_rate' => round(($rentDays * 100.0 / $statisticDays), 2),
                'income0' => ($o ? $o['income0'] : 0),     // 未结收入
                'income1' => ($o ? $o['income1'] : 0),     // 已结收入
                'outcome' => ($o ? $o['outcome'] : 0),
                'summary' => ($o ? $o['income1'] - $o['outcome'] : 0),
                
                'belong_office_disp' => (isset($arrOfficeNames[$row->belong_office_id]) ? $arrOfficeNames[$row->belong_office_id] : ''),
            ];
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    // 车辆收益总表 
    public function actionCarsummary() {
        /*
         * 车辆牌号|出租天数|统计天数|出租率(%)|购车费用(元)|购置税费(元)|车辆收入(元)|车辆支出(元)|管理分摊(元)|合计(元)|收益率(%)
        查询条件：
            车牌号码，车辆型号，购买时间，所属门店
         */
        $arrData = [
            'pageSize' => $this->pageSize,
        ];
        
        return $this->renderPartial('carsummary', $arrData);
    }
    
    public function actionCarsummarydata_list() {
        // get pagination
        $intPage = intval(Yii::$app->request->getParam('page'));
        if ($intPage == 0)
            $intPage = 1;
        $numPerPage = intval(Yii::$app->request->getParam('rows'));
        $numPerPage = intval($numPerPage);
        if (!$numPerPage)
            $numPerPage = $this->pageSize;
        
        // get order
        $intSort = strval(Yii::$app->request->getParam('sort'));
        $intSortDirection = strval(Yii::$app->request->getParam('order'));
        if (!empty($intSort) && !empty($intSortDirection)) {
            $order = $intSort . " " . $intSortDirection;
        }
        
        $cdb = \common\models\Pro_vehicle::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $plateNumber = \Yii::$app->request->getParam('plate_number');
        $vehicleModel = intval(\Yii::$app->request->getParam('vehicle_model_id'));
        $baughtTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->getParam('baught_time'));
        $belongOffice = intval(\Yii::$app->request->getParam('office_id'));
        if (!empty($plateNumber)) {
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        $arrOfficeIds = [];
        
        $arrOfficeIds = [];
        $arrVehicleIds = [];
        $arrVehicleModelIds = [];
        
        // 判断是否需要详细收入，详细支出项目统计
        // 
        
        foreach ($arrRows as $row) {
            if (!isset($arrOfficeIds[$row->belong_office_id])) {
                $arrOfficeIds[$row->belong_office_id] = 1;
            }
            if (!isset($arrVehicleModelIds[$row->model_id])) {
                $arrVehicleModelIds[$row->model_id] = 1;
            }
            $arrVehicleIds[] = $row->id;
        }
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrVehicleNames = \common\components\VehicleModule::getVehicleModelNamesArrayByIds(array_keys($arrVehicleModelIds));
        
        $arrDataByVehicleIds = [];
        
        if (!empty($arrVehicleIds)) {
            $cdb2 = \common\models\Pro_vehicle_order::find(true);
            $cdb2->where(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
            $cdb2->andWhere(['vehicle_id' => $arrVehicleIds]);
            
            $arrOrderRows = $cdb2->all();
            $arrCostRows = \backend\components\StatisticsService::getVehicleCostRecords($arrVehicleIds);
            
            foreach ($arrOrderRows as $row) {
                $o = (isset($arrDataByVehicleIds[$row->id]) ? $arrDataByVehicleIds[$row->vehicle_id] : [
                    'rent_days' => 0,
                    'income0' => 0,
                    'income1' => 0,
                    'outcome' => 0,
                ]);
                
                $o['rent_days'] += $row->rent_days;
                $o['income0'] += $row->total_amount - $row->paid_amount;
                $o['income1'] = $row->paid_amount;
            }
            foreach ($arrCostRows as $row) {
                $o = (isset($arrDataByVehicleIds[$row->id]) ? $arrDataByVehicleIds[$row->vehicle_id] : [
                    'rent_days' => 0,
                    'income0' => 0,
                    'income1' => 0,
                    'outcome' => 0,
                ]);
                
                $o['outcome'] = $row->getExpenditureAmount();
            }
        }
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = (isset($arrDataByVehicleIds[$row->id]));
            
            $rentDays = ($o ? $o['rent_days'] : 0);
            
            $arrData[] = [
                'name' => $row->plate_number,
                'model' => isset($arrVehicleNames[$row->model_id]) ? $arrVehicleNames[$row->model_id] : '',
                'belong_office_id' => $row->belong_office_id,
                'rent_days' => $rentDays,
                'statistic_days' => 0,
                'rent_rate' => 0,   // 出租率
                'baught_price' => $row->baught_price,
                'baught_tax' => $row->baught_tax,
                'income' => 0,
                'outcome' => 0,
                'management_allocation' => 0,   // 管理分摊
                'summary' => 0,
                'earnings_rate' => 0,   // 收益率
                
                'belong_office_disp' => (isset($arrOfficeNames[$row->belong_office_id]) ? $arrOfficeNames[$row->belong_office_id] : ''),
            ];
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
            'footer' => [
                [
                    'name' => Yii::t('locale', 'Total amount').":",
                    'rent_days' => 0,
                    'statistic_days' => 0,
                    'rent_rate' => 0,   // 出租率
                    'baught_price' => $row->baught_price,
                    'baught_tax' => $row->baught_tax,
                    'income' => 0,
                    'outcome' => 0,
                    'management_allocation' => 0,   // 管理分摊
                    'summary' => 0,
                    'earnings_rate' => 0,   // 收益率
                ]
            ]
        ];
        
        echo json_encode($arrListData);
    }
    
    // 会员充值统计 
    public function actionUserrecharge() {
        /*
         * 会员名称|会员卡号|会员类型|充值总额|当前余额|历史消费|当前积分
        查询条件：
            客户类型，名称，身份证，会员卡，类型，分店
         */
        $arrData = [
            'pageSize' => $this->pageSize,
        ];
        
        return $this->renderPartial('userrecharge', $arrData);
    }
    
    public function actionUserrechargedata_list() {
        // get pagination
        $intPage = intval(Yii::$app->request->getParam('page'));
        if ($intPage == 0)
            $intPage = 1;
        $numPerPage = intval(Yii::$app->request->getParam('rows'));
        $numPerPage = intval($numPerPage);
        if (!$numPerPage)
            $numPerPage = $this->pageSize;
        
        // get order
        $intSort = strval(Yii::$app->request->getParam('sort'));
        $intSortDirection = strval(Yii::$app->request->getParam('order'));
        if (!empty($intSort) && !empty($intSortDirection)) {
            $order = $intSort . " " . $intSortDirection;
        }
        
        $cdb = \common\models\Pub_user_info::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $customerName = \Yii::$app->request->getParam('customer_name');
        $customerId = \Yii::$app->request->getParam('customer_id');
        $cardNumber = \Yii::$app->request->getParam('card_number');
        $vipLevel = intval(\Yii::$app->request->getParam('vip_level'));
        $belongOffice = intval(\Yii::$app->request->getParam('office_id'));
        if (!empty($customerName)) {
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        $arrOfficeIds = [];
        
        
        foreach ($arrRows as $row) {
            //if (!isset($arrOfficeIds[$row->belong_office_id])) {
            //    $arrOfficeIds[$row->belong_office_id] = 1;
            //}
        }
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $arrData[] = [
                'name' => $row->name,
                'card_number' => '',
                'vip_level' => $row->vip_level,
                'belong_office_id' => 0,
                'recharge_amount' => 0,
                'current_balance' => 0,   // 当前余额
                'historical_consumption' => 0,  // 历史消费
                'integration' => 0,     // 当前积分
                
                //'belong_office_disp' => (isset($arrOfficeNames[$row->belong_office_id]) ? $arrOfficeNames[$row->belong_office_id] : ''),
            ];
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
            'footer' => [
                [
                    'name' => Yii::t('locale', 'Total amount').":",
                    'recharge_amount' => 0,
                    'current_balance' => 0,
                    'current_balance' => 0,
                    'historical_consumption' => 0,
                    'integration' => 0,
                ]
            ]
        ];
        
        echo json_encode($arrListData);
    }
    
    // 车辆支出管理 
    public function actionCaroutcome() {
        /*
         * 车船费
            序号|车辆牌号|缴费时间|缴费金额
            查询条件：
                车辆牌号，缴费额度：起-止，缴费日期：起-止
        投保费
            序号|车辆牌号|投保时间|保险公司|投保类型(保险类型)|保单号码|保费(元)|保额(元)|备注
            查询条件：
                车辆牌号，保单号，保费额度：起-止，投保日期：起-止
        违章费
            序号|车辆牌号|缴费时间|缴费金额|说明
            查询条件：
                车辆牌号，缴费额度：起-止，缴费日期：起-止
        发票费
            序号|车辆牌号|开票时间|(6%)税费金额(元)|对应结算单(点击可查看，页面很复杂1)
            查询条件：
                车辆牌号，税费额度：起-止，开票日期：起-止
        刷卡费
            序号|车辆牌号|刷卡时间|(0.72%)佣金金额(元)|对应结算单(点击可查看，页面很复杂1)
            查询条件：
                车辆牌号，佣金额度：起-止，刷卡日期：起-止
        加油费
            序号|车辆牌号|加油时间|油品型号(#)|油量(升)|金额(元)|付款方式|加油用途|公里数|加油人
            查询条件：
                车辆牌号，加油人，加油额度：起-止，加油日期：起-止
        代驾费
            序号|车辆牌号|支出时间|代驾司机|司机劳务费|路桥费|停车费|加油费|说明
            查询条件：
                车辆牌号，司机姓名，劳务额度：起-止，支出日期：起-止
        维修/保养/其他费用
            序号|车辆牌号|支出时间|支出项目|支出金额|说明
            查询条件：
                车辆牌号，支出项目，支出额度：起-止，支出日期：起-止
         */
        $arrData = [
            
        ];
        
        return $this->renderPartial('caroutcome', $arrData);
    }
    
    public function actionCaroutcome_index() {
        $type = intval(\Yii::$app->request->getParam('type'));
        
        $arrData = [
            'type' => $type,
        ];
        return $this->renderPartial('caroutcome_index', $arrData);
    }
    
    public function actionOrderbymonthly() {
        $belongOfficeId = intval(\Yii::$app->request->getParam('office_id'));
        $status = intval(\Yii::$app->request->getParam('status'));
        $pay_type = intval(\Yii::$app->request->getParam('pay_type'));
        $date = \Yii::$app->request->getParam('date');
        $date_start = \Yii::$app->request->getParam('date_start');
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        else {
            $date = date('Y-m-d', strtotime($date));
        }

        if (empty($date_start)) {
            $date_start = date('Y-m-01');
        }else{
            if($date_start>$date){
                $date_start = date('Y-m-d');
            }else{
                $date_start = date('Y-m-d', strtotime($date_start));
            }
        }
		
        /*echo "$date_startcustomer_telephone".'------';
        echo "$date";*/
        // $arrData = \backend\components\StatisticsService::getMonthlyOrderIncomeData($status, $date, $belongOfficeId);
        
        $arrData = \backend\components\StatisticsService::getMonthlyOrderIncomeDataNew($status, $date, $belongOfficeId,$date_start,$pay_type);
        /*echo "<pre>";
        print_r($arrData);
        echo "</pre>";die;*/
		
        return $this->renderPartial('orderbymonthly', [
            'columns'=>\backend\components\StatisticsService::getMonthlyOrderIncomeDataColumns($status,$pay_type),
            'models'=>$arrData,
            'date'=>$date,
            'date_start'=>$date_start,
            'status'=>$status,
            'pay_type'=>$pay_type,
            'belongOfficeId'=>($belongOfficeId?$belongOfficeId:''),
        ]);
    }
    
    public function actionOrderbymonthlyExport() {
        $belongOfficeId = intval(\Yii::$app->request->getParam('office_id'));
        $status = intval(\Yii::$app->request->getParam('status'));
        $pay_type = intval(\Yii::$app->request->getParam('pay_type'));
		
        $date = \Yii::$app->request->getParam('date');
        $date_start = \Yii::$app->request->getParam('date_start');
		
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        else {
            $date = date('Y-m-d', strtotime($date));
        }

        if (empty($date_start)) {
            $date_start = date('Y-m-01');
        }else{
            if($date_start>$date){
                $date_start = date('Y-m-d');
            }else{
                $date_start = date('Y-m-d', strtotime($date_start));
            }
        }
     
        // $arrData = \backend\components\StatisticsService::getMonthlyOrderIncomeData($status, $date, $belongOfficeId);
		
        $arrData = \backend\components\StatisticsService::getMonthlyOrderIncomeDataNew($status, $date, $belongOfficeId,$date_start,$pay_type);
		
        $model = new \common\models\Pro_vehicle_order();
        $arrStatusText = \common\components\OrderModule::getOrderStatusArray();
        $arrStatusText[\common\models\Pro_vehicle_order::STATUS_COMPLETED] = '历史结算';
        $arrStatusText[\common\models\Pro_vehicle_order::STATUS_RENTING] = '在租';

        /*$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = ['memoryCacheSize' => '16MB'];
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);*/
        // echo "<pre>";
        // print_r($arrData);
        // echo "</pre>";die;
        \moonland\phpexcel\Excel::export([
            'models' => $arrData,
            'columns' => \backend\components\StatisticsService::getMonthlyOrderIncomeDataColumns($status,$pay_type),
            'headers' => $model->attributeLabels(),
            'fileName' => \Yii::t('locale', '{type} order list', ['type'=>(isset($arrStatusText[$status]) ? $arrStatusText[$status] : '')]),
            'format' => 'Excel2007',
        ]);
    }
    
}
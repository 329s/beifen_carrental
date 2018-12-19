<?php

namespace backend\modules\sysmaintenance\controllers;

/**
 * Default controller for the `sysmaintenance` module
 */
class DefaultController extends \backend\components\AuthorityController
{
    
    public function beforeAction($action) {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            return false;
        }
        return parent::beforeAction($action);
    }
    
    public function actionIndex() {
        return $this->render('index');
    }
    
    public function actionFixpurchaseorder() {
        if (true) {
            echo \yii\helpers\Html::tag('div', "暂不开放此服务", ['class'=>'alert alert-danger']);
            return;
        }
        // @warning 警告：执行完毕请立即删除此代码以免重复操作。
        
        $arr = [
            //'130025001535' => ['amount'=>396, 'tim'=>1486172588, 'sub_type'=>\common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_BOOK],
        ];
        
        \backend\components\DataUpgradeFixerService::fixPurchaseOrder($arr);
    }
    
    public function actionFixpaymentdetail() {
        if (false) {
            echo \yii\helpers\Html::tag('div', "暂不开放此服务", ['class'=>'alert alert-danger']);
            return;
        }
        
        $isFixLostPurchase = true;
        $isSkipDeposit = true;
        $isSave = intval(\Yii::$app->request->getParam('is_save'));
        
        $logs = \backend\components\DataUpgradeFixerService::fixPaymentDetail($isFixLostPurchase, $isSkipDeposit, $isSave);
        
        return $this->renderPartial('fixpaymentdetail', ['logs'=>$logs, 'isSave'=>$isSave]);
    }
    
    public function actionCheckorderdetail()
    {
        $cdb0 = \common\models\Pro_vehicle_order::find();
        $cdb0->where(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
        $arrAllOrderObjects = $cdb0->all();
        $officeIds = [];
        foreach ($arrAllOrderObjects as $row) {
            if (!isset($officeIds[$row->belong_office_id])) {
                $officeIds[$row->belong_office_id] = 1;
            }
        }
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($officeIds));
        $priceKeys = \common\models\Pro_vehicle_order_price_detail::getPriceKeys();
        $logs = [];
        foreach ($arrAllOrderObjects as $objOrder) {
            $cdb2 = \common\models\Pro_purchase_order::find();
            $cdb2->where(['bind_id'=>$objOrder->id]);
            $cdb2->andWhere(['>=', 'status', \common\models\Pro_purchase_order::STATUS_SUCCEES]);
            $cdb2->orderBy("purchased_at ASC");
            $arrPurchases = $cdb2->all();
            
            $cdb3 = \common\models\Pro_vehicle_order_price_detail::find();
            $cdb3->where(['order_id'=>$objOrder->id]);
            $cdb3->andWhere(['>=', 'status', \common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL]);
            $arrDetailsRows = $cdb3->all();
            
            $objDetailNeedPay = new \common\models\Pro_vehicle_order_price_detail();
            $objDetailTotalPaid = new \common\models\Pro_vehicle_order_price_detail();
            
            $xpaidAmount = 0;
            $xpaidDeposit = 0;
            foreach ($arrPurchases as $row) {
                if ($row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT) {
                    $xpaidDeposit += floatval($row->amount);
                }
                else {
                    $xpaidAmount += floatval($row->amount);
                }
            }
            
            foreach ($arrDetailsRows as $row) {
                if ($row->type == \common\models\Pro_vehicle_order_price_detail::TYPE_SHOULD_PAY) {
                    $obj = $objDetailNeedPay;
                }
                elseif($row->type == \common\models\Pro_vehicle_order_price_detail::TYPE_PAID) {
                    $obj = $objDetailTotalPaid;
                }
                
                foreach ($priceKeys as $k) {
                    $v = floatval($obj->$k) + floatval($row->$k);
                    $obj->$k = $v;
                }
            }
            $objDetailNeedPay->summary();
            $objDetailTotalPaid->summary();
            
            $log = $objDetailNeedPay->getAttributes();
            $log['order'] = $objOrder->serial;
            $log['total_amount0'] = $objOrder->total_amount;
            $log['paid_amount0'] = $objOrder->paid_amount;
            $log['deposit_amount0'] = $objOrder->getTotalDepositPrice();
            $log['paid_deposit0'] = $objOrder->paid_deposit;
            $log['total_amount1'] = $objDetailNeedPay->summary_amount;
            $log['paid_amount1'] = $objDetailTotalPaid->summary_amount;
            $log['deposit_amount1'] = $objDetailNeedPay->summary_deposit;
            $log['paid_deposit1'] = $objDetailTotalPaid->summary_deposit;
            $log['paid_amount2'] = $xpaidAmount;
            $log['paid_deposit2'] = $xpaidDeposit;
            $log['start_time'] = $objOrder->start_time;
            $log['end_time'] = $objOrder->new_end_time;
            $log['customer'] = $objOrder->customer_name;
            $log['office'] = isset($arrOfficeNames[$objOrder->belong_office_id]) ? $arrOfficeNames[$objOrder->belong_office_id] : '';
            
            if ($log['total_amount0'] != $log['total_amount1']) {
                $logs[] = $log;
            }
            elseif ($log['paid_amount0'] != $log['paid_amount1'] || $log['paid_amount2'] != $log['paid_amount2']) {
                $logs[] = $log;
            }
            elseif ($log['deposit_amount0'] != $log['deposit_amount1']) {
                $logs[] = $log;
            }
            
        }
        
        return $this->renderPartial('checkorderdetail', ['logs'=>$logs]);
    }
    
    public function actionGetorderchangelog() {
        $filterModel = new \backend\models\searchers\Searcher_pro_vehicle_order_change_log();
        $dataProvider = $filterModel->search(\Yii::$app->request->getParams());
        
        return $this->renderPartial('getorderchangelog', [
            'dataProvider'=>$dataProvider, 
            'filterModel'=>$filterModel,
        ]);
    }
    
    public function actionOrderchangelogExport() {
        $filterModel = new \backend\models\searchers\Searcher_pro_vehicle_order_change_log();
        $filterModel->setPagerInfo(false);
        $dataProvider = $filterModel->search(\Yii::$app->request->getParams());
        
        $dataProvider->manualFormatModelValues();
        
        $columns = [];
        $skipFields = ['id', 'updated_at'];
        $model = new \common\models\Pro_vehicle_order_change_log();
        foreach ($model->attributes() as $attr) {
            if (in_array($attr, $skipFields)) {
                continue;
            }
            $col = $attr;
        }
        $fileEndFix = '';
        if (!empty($filterModel->serial)) {
            $fileEndFix = '-'.$filterModel->serial;
        }
        
        \moonland\phpexcel\Excel::export([
            'models' => $dataProvider->getModels(),
            'columns' => $columns,
            'headers' => $model->attributeLabels(),
            'fileName' => '订单修改记录'.$fileEndFix,
            'format' => 'Excel2007',
        ]);
    }
    
    public function actionUpgradevehiclefeeplan() {
        if (false) {
            echo \yii\helpers\Html::tag('div', "暂不开放此服务", ['class'=>'alert alert-danger']);
            return;
        }
        
        $isSave = intval(\Yii::$app->request->getParam('is_save'));
        
        $logs = \backend\components\DataUpgradeFixerService::upgradeVehicleFeeplan($isSave);
        
        return $this->renderPartial('upgradevehiclefeeplan', ['logs'=>$logs, 'isSave'=>$isSave]);
    }
    
    public function actionTestui() {
        return $this->renderPartial('testui');
    }
    
}

<?php

namespace backend\controllers;

use common\helpers\MyFunction;
/**
 * Description of InternalServiceController
 *
 * @author kevin
 */
class InternalServiceController extends \backend\components\AuthorityController
{
    
    public $pageSize = 20;
    
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
    
    public function actionBooking() {
        $filterModel = new \backend\models\searchers\Searcher_pro_inner_applying();
        
        return $this->renderPartial('booking', ['filterModel' => $filterModel]);
    }
    
    public function actionUsevehicle_add() {
        $action = \Yii::$app->request->getParam('action');
        if (!empty($action)) {
            $arrResult = \backend\components\InternalServiceService::processApplyingEdit();
            if ($arrResult[0]) {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 200, '', '', 'refreshCurrentX', '');
            }
            else {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 300);
            }
        }
        return $this->renderPartial('usevehicle_edit', ['action'=>'insert', 'objItem'=>null, 
            'saveUrl'=>\yii\helpers\Url::to(['/internal-service/usevehicle_add'])]);
    }
    
    public function actionUsevehicle_edit() {
        $action = \Yii::$app->request->getParam('action');
        $itemId = intval(\Yii::$app->request->getParam('id'));
        $objItem = $itemId ? \backend\models\Pro_inner_applying::findById($itemId) : null;
        if (!empty($action)) {
            $arrResult = \backend\components\InternalServiceService::processApplyingEdit();
            if ($arrResult[0]) {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 200, '', '', 'refreshCurrentX', '');
            }
            else {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 300);
            }
        }
        
        if ($objItem) {
            $action = 'update';
        }
        else {
            $action = 'insert';
        }
        return $this->renderPartial('usevehicle_edit', ['action'=>$action, 'objItem'=>$objItem, 
            'saveUrl'=>\yii\helpers\Url::to(['/internal-service/usevehicle_edit'])]);
    }
    
    public function actionUsevehicle_approval() {
        $action = \Yii::$app->request->getParam('action');
        $itemId = intval(\Yii::$app->request->getParam('id'));
        $objItem = $itemId ? \backend\models\Pro_inner_applying::findById($itemId) : null;
        if (!empty($action)) {
            $arrResult = \backend\components\InternalServiceService::processApplyingApproval();
            if ($arrResult[0]) {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 200, '', '', 'refreshCurrentX', '');
            }
            else {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 300);
            }
        }
        return $this->renderPartial('usevehicle_approval', ['action'=>'approval', 'objItem'=>$objItem, 
            'saveUrl'=>\yii\helpers\Url::to(['/internal-service/usevehicle_approval'])]);
    }
    
    public function actionUsevehicle_delete() {
        $arrResult = \backend\components\InternalServiceService::processApplyingDelete();
        if ($arrResult[0]) {
            MyFunction::funEchoJSON_Ajax($arrResult[1], 200, '', '', 'refreshCurrent');
        }
        else {
            MyFunction::funEchoJSON_Ajax($arrResult[1], 300);
        }
    }
    
    public function actionApplying() {
        $filterModel = new \backend\models\searchers\Searcher_pro_inner_applying();
        
        return $this->renderPartial('applying', ['filterModel' => $filterModel]);
    }
    
    public function actionApplying_list() {
        $params = \Yii::$app->request->getParams();
        $filterModel = new \backend\models\searchers\Searcher_pro_inner_applying();
        $filterModel->loadPagination($params);
        $filterModel->loadSort($params);
        $dataProvider = $filterModel->search($params);
        
        $dataProvider->manualFormatModelValues();
        $arrRows = $dataProvider->getModels();
        
        $arrData = [];
        foreach ($arrRows as $k => $row) {
            $o = $row->getAttributes();
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($dataProvider->getTotalCount()),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionApplying_add() {
        $action = \Yii::$app->request->getParam('action');
        if (!empty($action)) {
            $arrResult = \backend\components\InternalServiceService::processApplyingEdit();
            if ($arrResult[0]) {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 200, '', '', 'refreshCurrentX', '');
            }
            else {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 300);
            }
        }
        return $this->renderPartial('applying_edit', ['action'=>'insert', 'objItem'=>null, 
            'saveUrl'=>\yii\helpers\Url::to(['/internal-service/applying_add'])]);
    }
    
    public function actionApplying_edit() {
        $action = \Yii::$app->request->getParam('action');
        $itemId = intval(\Yii::$app->request->getParam('id'));
        $objItem = $itemId ? \backend\models\Pro_inner_applying::findById($itemId) : null;
        if (!empty($action)) {
            $arrResult = \backend\components\InternalServiceService::processApplyingEdit();
            if ($arrResult[0]) {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 200, '', '', 'refreshCurrentX', '');
            }
            else {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 300);
            }
        }
        
        if ($objItem) {
            $action = 'update';
        }
        else {
            $action = 'insert';
        }
        return $this->renderPartial('applying_edit', ['action'=>$action, 'objItem'=>$objItem, 
            'saveUrl'=>\yii\helpers\Url::to(['/internal-service/applying_edit'])]);
    }
    
    public function actionApplying_approval() {
        $action = \Yii::$app->request->getParam('action');
        $itemId = intval(\Yii::$app->request->getParam('id'));
        $objItem = $itemId ? \backend\models\Pro_inner_applying::findById($itemId) : null;
        if (!empty($action)) {
            $arrResult = \backend\components\InternalServiceService::processApplyingApproval();
            if ($arrResult[0]) {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 200, '', '', 'refreshCurrentX', '');
            }
            else {
                MyFunction::funEchoJSON_Ajax($arrResult[1], 300);
            }
        }
        return $this->renderPartial('applying_approval', ['action'=>'approval', 'objItem'=>$objItem, 
            'saveUrl'=>\yii\helpers\Url::to(['/internal-service/applying_approval'])]);
    }
    
    public function actionApplying_delete() {
        $arrResult = \backend\components\InternalServiceService::processApplyingDelete();
        if ($arrResult[0]) {
            MyFunction::funEchoJSON_Ajax($arrResult[1], 200, '', '', 'refreshCurrent');
        }
        else {
            MyFunction::funEchoJSON_Ajax($arrResult[1], 300);
        }
    }
    
}

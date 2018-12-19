<?php
namespace backend\controllers;

use Yii;
use common\helpers\MyFunction;

/**
 * Description of OrderhourController
 *
 * @author kevin
 */
class OrderhourController  extends \backend\components\AuthorityController
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
    
    public function actionIndex() {
        return $this->renderPartial('index');
    }
    
    public function actionWaiting_index() {
        $arrData = ['status' => \common\models\Pro_vehicle_order::STATUS_WAITING];
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionBooking_index() {
        $arrData = ['status' => \common\models\Pro_vehicle_order::STATUS_BOOKED];
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionRenting_index() {
        $arrData = ['status' => \common\models\Pro_vehicle_order::STATUS_RENTING];
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionViolation_index() {
        $arrData = ['status' => \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING];
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionStatement_index() {
        $arrData = ['status' => \common\models\Pro_vehicle_order::STATUS_COMPLETED];
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionCanceled_index() {
        $arrData = ['status' => \common\models\Pro_vehicle_order::STATUS_CANCELLED];
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionOrder_with_vehicle_list() {
        // get pagination
        $intPage = intval(Yii::$app->request->getParam('page'));
        if ($intPage == 0)
            $intPage = 1;
        $numPerPage = intval(Yii::$app->request->getParam('rows'));
        if (!$numPerPage)
            $numPerPage = $this->pageSize;
        
        // get order
        $intSort = strval(Yii::$app->request->getParam('sort'));
        $intSortDirection = strval(Yii::$app->request->getParam('order'));
        if (!empty($intSort) && !empty($intSortDirection)) {
            if ($intSort == 'booking_time') {
                $intSort = 'created_at';
            }
            $order = $intSort . " " . $intSortDirection;
        }
        
        $cdb = \common\models\Pro_vehicle_order::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $status = intval(\Yii::$app->request->getParam('status'));
        $pay_type = intval(\Yii::$app->request->getParam('pay_type'));
        $serial = \Yii::$app->request->getParam('serial');
        $plateNumber = \Yii::$app->request->getParam('plate_number');
        $vehicle_model_id = intval(\Yii::$app->request->getParam('vehicle_model_id'));
        $customer_name = \Yii::$app->request->getParam('customer_name');
        $customer_telephone = \Yii::$app->request->getParam('customer_telephone');
        $office_id = \Yii::$app->request->getParam('office_id');
        $userId = intval(\Yii::$app->request->getParam('user_id'));
        $settlement_status = intval(\Yii::$app->request->getParam('settlement_status'));
		
		
		
		if($pay_type){
			$cdb->andWhere(['pay_type'=>$pay_type]);//支付方式
		}
        if (!empty($settlement_status)) {
            $cdb->andWhere(['settlement_status' => $settlement_status]);
        }
        if ($status) {
            if ($status == \common\models\Pro_vehicle_order::STATUS_COMPLETED) {
                $cdb->andWhere(['status'=>[$status, \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING]]);
            }
            else {
                $cdb->andWhere(['status'=>$status]);
            }
        }
        if (!empty($serial)) {
            $cdb->andWhere(['serial' => $serial]);
        }
        if (!empty($plateNumber)) {
            $tmpFinder = \common\models\Pro_vehicle::find();
            $tmpFinder->select(['id']);
            $tmpFinder->where('plate_number LIKE :keywords', [':keywords' => '%'.$plateNumber.'%']);
            $tmpArr = $tmpFinder->all();
            $vehicleIds = [];
            foreach ($tmpArr as $row) {
                $vehicleIds[] = $row->id;
            }
            if (empty($vehicleIds)) {
                $cdb->andWhere(['vehicle_id' => 0]);
            }
            else {
                $cdb->andWhere(['vehicle_id' => $vehicleIds]);
            }
        }
        if (!empty($vehicle_model_id)) {
            $cdb->andWhere(['vehicle_model_id' => $vehicle_model_id]);
        }
        if (!empty($customer_name)) {
            $cdb->andWhere('customer_name LIKE :keywords2', [':keywords2' => '%'.$customer_name.'%']);
        }
        if (!empty($customer_telephone)) {
            $cdb->andWhere('customer_telephone LIKE :keywords3', [':keywords3' => '%'.$customer_telephone.'%']);
        }
        if (!empty($office_id)) {
            $cdb->andWhere(['belong_office_id' => $office_id]);
        }
        if ($userId) {
            $cdb->andWhere(['user_id' => $userId]);
            //if (!$status) {
            //    $cdb->andWhere(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
            //}
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();

        $arrModelIds = [];
        $arrVehicleIds = [];
        $arrAdminIds = [];
        $arrUserIds = [];
        $arrOfficeIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrModelIds[$row->vehicle_model_id])) {
                $arrModelIds[$row->vehicle_model_id] = 1;
            }
            if (!isset($arrVehicleIds[$row->vehicle_id])) {
                $arrVehicleIds[$row->vehicle_id] = 1;
            }
            if (!isset($arrOfficeIds[$row->belong_office_id])) {
                $arrOfficeIds[$row->belong_office_id] = 1;
            }
            if (!isset($arrOfficeIds[$row->office_id_rent])) {
                $arrOfficeIds[$row->office_id_rent] = 1;
            }
            if (!isset($arrOfficeIds[$row->office_id_return])) {
                $arrOfficeIds[$row->office_id_return] = 1;
            }
            if ($row->edit_user_id && !isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
            if ($row->settlement_user_id && !isset($arrAdminIds[$row->settlement_user_id])) {
                $arrAdminIds[$row->settlement_user_id] = 1;
            }
            if (!isset($arrUserIds[$row->user_id])) {
                $arrUserIds[$row->user_id] = 1;
            }
        }
        
        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrOffices = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrModelNames = \common\components\VehicleModule::getVehicleModelNamesArrayByIds(array_keys($arrModelIds));
        $arrVehicleObjects = \common\components\VehicleModule::getVehicleObjects(array_keys($arrVehicleIds));
        $arrUserInfos = \common\components\UserModule::getUserInfoObjectsByUserIdArray(array_keys($arrUserIds));
        $arrVipLevels = \common\components\UserModule::getVipLevelsArray();
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $vipLevel = (isset($arrUserInfos[$row->user_id]) ? $arrUserInfos[$row->user_id]->vip_level : 0);
            $o = $row->getAttributes();
            
            $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
            $o['settlement_user_disp'] = (isset($arrAdmins[$row->settlement_user_id]) ? $arrAdmins[$row->settlement_user_id] : '');
            $o['vehicle_model_name'] = (isset($arrModelNames[$row->vehicle_model_id]) ? $arrModelNames[$row->vehicle_model_id] : '');
            $o['plate_number'] = (isset($arrVehicleObjects[$row->vehicle_id]) ? $arrVehicleObjects[$row->vehicle_id]->plate_number : '');
            $o['belong_office_disp'] = (isset($arrOffices[$row->belong_office_id]) ? $arrOffices[$row->belong_office_id] : '');
            $o['rent_office_disp'] = (isset($arrOffices[$row->office_id_rent]) ? $arrOffices[$row->office_id_rent] : '');
            $o['return_office_disp'] = (isset($arrOffices[$row->office_id_return]) ? $arrOffices[$row->office_id_return] : '');
            $o['customer_vip_level'] = (isset($arrVipLevels[$vipLevel]) ? $arrVipLevels[$vipLevel] : '');
            $o['daily_rent_details'] = $row->getDailyRentDetailedPriceArray();
            
            $arrData[] = $o;
        }
		// echo '<pre>';
        // print_r($arrData);exit;
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        // print_r($arrListData);exit;
        echo json_encode($arrListData);
    }
    
    public function actionEdit() {
        $processResult = \backend\components\OrderService::processEdit();
		
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'attributes' => \yii\helpers\ArrayHelper::getValue($processResult, 'attributes', ''),
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
            ]);
        }
        
        $orderId = intval(\Yii::$app->request->getParam('id'));
        $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
        $orderType = intval(\Yii::$app->request->getParam('type'));
        $arrData = [
            'vehicleId' => $vehicleId,
            'orderType' => $orderType,
            'objVehicleOrder' => empty($orderId) ? null : \common\models\Pro_vehicle_order::findById($orderId),
        ];
        if ($vehicleId) {
            $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
            if ($objVehicle) {
                $arrData['vehicleModelId'] = $objVehicle->model_id;
            }
        }
        // echo "<pre>";
        // print_r($arrData);
        // echo "</pre>";die;
        return $this->renderPartial('edit', $arrData);
    }
    
    public function actionCancel() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        $cdb = \common\models\Pro_vehicle_order::find();
        $cdb->where(['id' => $orderId]);
        $objOrder = $cdb->one();
        if (!$objOrder) {
             MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        if ($objOrder->status >= \common\models\Pro_vehicle_order::STATUS_RENTING) {
            $arrStatuses = \common\components\OrderModule::getOrderStatusArray();
            $errStatus = isset($arrStatuses[$objOrder->status]) ? $arrStatuses[$objOrder->status] : '';
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'Cancel order failed, the order status is {status}!', ['status'=>$errStatus]), 300);
        }
        else {
            $objOrder->status = \common\models\Pro_vehicle_order::STATUS_CANCELLED;
            $objOrder->save();
            
            \common\components\SmsComponent::send($objOrder->customer_telephone, \common\components\Consts::KEY_SMS_ORDER_CANCELED, [
                'CNAME'=>$objOrder->customer_name, 
                'ORDERID'=>$objOrder->serial,
            ]);
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Cancel order succeed'), 200, '', '', 'refreshCurrent', '');
        }
    }

    public function actionComplete() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        $cdb = \common\models\Pro_vehicle_order::find();
        $cdb->where(['id' => $orderId]);
        $objOrder = $cdb->one();
        if (!$objOrder) {
             MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        if ($objOrder->status != \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING) {
            $arrStatuses = \common\components\OrderModule::getOrderStatusArray();
            $errStatus = isset($arrStatuses[$objOrder->status]) ? $arrStatuses[\common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING] : '';
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'Complete order failed, the order status is not {status}!', ['status'=>$errStatus]), 300);
        }
        else {
            $objOrder->status = \common\models\Pro_vehicle_order::STATUS_COMPLETED;
            $objOrder->save();
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Complete order succeed'), 200, '', '', 'refreshCurrent', '');
        }
    }

    public function actionOrder_relet() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        $cdb = \common\models\Pro_vehicle_order::find();
        $cdb->where(['id' => $orderId]);
        $objOrder = $cdb->one();
        if (!$objOrder) {
             MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        if ($objOrder->status != \common\models\Pro_vehicle_order::STATUS_RENTING) {
            $arrStatuses = \common\components\OrderModule::getOrderStatusArray();
            $errStatus = isset($arrStatuses[$objOrder->status]) ? $arrStatuses[\common\models\Pro_vehicle_order::STATUS_RENTING] : '';
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'Relet order failed, the order status is not {status}!', ['status'=>$errStatus]), 300);
        }
        
        $arrData = [
            'objOrder' => $objOrder,
        ];
        
        return $this->renderPartial('order_relet', $arrData);
    }
    
    public function actionOrder_relet_list() {
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
        
        $cdb = \common\models\Pro_vehicle_order_relet::find();
        $cdb->select("*");
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $orderId = intval(\Yii::$app->request->getParam('order_id'));
        $status = intval(\Yii::$app->request->getParam('status'));
        $cdb->where(['order_id' => $orderId]);
        if (!empty($status)) {
            $cdb->andWhere(['status'=>$status]);
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();

        $arrAdminIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
        }
        
        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrStatus = \common\components\OrderModule::getOrderStatusArray();
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
            $o['status_disp'] = (isset($arrStatus[$row->status]) ? $arrStatus[$row->status] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionOrder_relet_view() {
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objOrderRelet = \common\models\Pro_vehicle_order_relet::findById($intID);

        if (!$objOrderRelet) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
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
            'objData' => $objOrderRelet,
            'objOrder' => $objOrder,
            'objVehicle' => $objVehicle,
            'objVehicleModel' => $objVehicleModel,
        ];
        
        return $this->renderPartial('order_relet_view', $arrData);
    }
    
    public function actionOrder_relet_add() {
        $processResult = \backend\components\OrderService::processReletEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'attributes' => \yii\helpers\ArrayHelper::getValue($processResult, 'attributes', ''),
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
            ]);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        $objOrderRelet = ($intID ? \common\models\Pro_vehicle_order_relet::findById($intID) : null);
        if (!$objOrderRelet) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
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
            'action' => 'insert',
            'saveUrl' => \yii\helpers\Url::to('/order/order_relet_add'),
            'objData' => $objOrderRelet,
            'objOrder' => $objOrder,
            'objVehicle' => $objVehicle,
            'objVehicleModel' => $objVehicleModel,
        ];
        
        return $this->renderPartial('order_relet_edit', $arrData);
    }
    
    public function actionOrder_relet_edit() {
        $processResult = \backend\components\OrderService::processReletEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'attributes' => \yii\helpers\ArrayHelper::getValue($processResult, 'attributes', ''),
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
            ]);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        $objOrderRelet = ($intID ? \common\models\Pro_vehicle_order_relet::findById($intID) : null);
        if (!$objOrderRelet) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
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
            'action' => $objOrderRelet ? 'update' : 'insert',
            'saveUrl' => \yii\helpers\Url::to('/order/order_relet_edit'),
            'objData' => $objOrderRelet,
            'objOrder' => $objOrder,
            'objVehicle' => $objVehicle,
            'objVehicleModel' => $objVehicleModel,
        ];
        
        return $this->renderPartial('order_relet_edit', $arrData);
    }
    
    public function actionOrder_relet_delete() {
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_vehicle_order_relet::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        $maxEndTime = 0;
        $objMainOrder = \common\models\Pro_vehicle_order::findById($objData->order_id);
        if ($objMainOrder) {
            $arrRows = \common\models\Pro_vehicle_order_relet::findAll(['order_id' => $objData->order_id]);
            foreach ($arrRows as $row) {
                if ($row->id != $objData->id) {
                    if ($row->new_end_time >= $objData->new_end_time) {
                        MyFunction::funEchoJSON_Ajax(Yii::t('carrental', 'There is orders that relet beyond this return car time!'), 300);
                        exit(0);
                    }
                    if ($maxEndTime < $row->new_end_time) {
                        $maxEndTime = $row->new_end_time;
                    }
                }
            }
        }
        
        $originPrice = $objData->total_amount;
        $objData->delete();
        
        if ($objMainOrder) {
            if ($maxEndTime >= $objMainOrder->end_time) {
                $objMainOrder->onUpdateEndTime($maxEndTime);
                //$objMainOrder->new_end_time = $maxEndTime;
            }
            else {
                $objMainOrder->onUpdateEndTime($objMainOrder->end_time);
                //$objMainOrder->new_end_time = $objMainOrder->end_time;
            }
            
            $objMainOrder->save();
        }
        
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionGet_order_price() {
        $type = \Yii::$app->request->getParam('type');
		
        $startTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->getParam('start_time'));
        $endTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->getParam('end_time'));
        $orderSource = intval(\Yii::$app->request->getParam('source_type'));
        $priceType = intval(\Yii::$app->request->getParam('pay_type'));
        $userId = intval(\Yii::$app->request->getParam('user_id'));
		
        //$isOffice = intval(\Yii::$app->request->getParam('is_office'));
        $arrResult = ['code'=>0, 'msg'=>\Yii::t('locale', 'Success')];
        do
        {
            if (empty($startTime)) {
                $arrResult['code'] = 400;
                $arrResult['msg'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Start time')]);
                break;
            }
            if (empty($endTime)) {
                $arrResult['code'] = 400;
                $arrResult['msg'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'End time')]);
                break;
            }
            if ($endTime < $startTime) {
                $arrResult['code'] = 400;
                $arrResult['msg'] = \Yii::t('carrental', 'End time should not earlier than start time!');
                break;
            }
            $birthday = null;
            if ($userId) {
                $objUserInfo = \common\models\Pub_user_info::findById($userId);
                if ($objUserInfo) {
                    $birthday = $objUserInfo->getBirthday();
                }
                // sjj 判断新老用户
                $re = \common\models\Pro_vehicle_order::CheckCustomerIsNew($userId);
            
                if($re > 0){
                    $userisnew = 0;//老用户
                }else{
                    $userisnew = 1;//新用户
                }
                // sjj
            }else{
                $userisnew = 1;//新用户
            }
            
            $arrPriceInfo = null;
            $price = 0;
            $arrDetails = [];
			
            if ($type == 'vehicle_id') {
                // echo "1";
                $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
                if (!$vehicleId) {
                    $arrResult['code'] = 400;
                    $arrResult['msg'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Vehicle')]);
                    break;
                }
                $arrPriceInfo = \common\components\OrderModule::calculateVehicleRentPriceData($vehicleId, $startTime, $endTime, $orderSource, $priceType, $birthday, $userisnew);
                if ($arrPriceInfo === false) {
                    $arrResult['code'] = 400;
                    $arrResult['msg'] = \Yii::t('carrental', 'Current vehicle model does not configured rental fee, please configure the rental fee first or contact the administrator!');
                    break;
                }
                
                $price = $arrPriceInfo['price'];
                $arrDetails = $arrPriceInfo['details'];
            }
            else if ($type == 'vehicle_model_id') {
                /*      [_] => 1500097067
                        [type] => vehicle_model_id
                        [vehicle_model_id] => 79
                        [office_id] => 25
                        [order_id] => 0
                        [source_type] => 3
                        [pay_type] => 1
                        [vehicle_id] => 327
                        [start_time] => 1499956680
                        [end_time] => 1500129480
                        [user_id] => 13790
                        //后台车辆租赁登记
                */
                        // echo "2";//车辆租赁登记   
						
                $vehicleModelId = intval(\Yii::$app->request->getParam('vehicle_model_id'));
                if (empty($vehicleModelId)) {
                    $arrResult['code'] = 400;
                    $arrResult['msg'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Vehicle model')]);
                    break;
                }
                $feeOfficeId = intval(\Yii::$app->request->getParam('office_id'));
                if ($feeOfficeId <= 0) {
                    $feeOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
                    if ($feeOfficeId < 0) {
                        $feeOfficeId = 0;
                    }
                }
				
                $arrPriceInfo = \common\components\OrderModule::calculateVehicleModelRentPriceData($vehicleModelId, $startTime, $endTime, $feeOfficeId, $orderSource, $priceType, $birthday,$userisnew);
                // echo "string";die;
				
                if ($arrPriceInfo === false) {
                    $arrResult['code'] = 400;
                    $arrResult['msg'] = \Yii::t('carrental', 'Current vehicle model does not configured rental fee, please configure the rental fee first or contact the administrator!');
                    break;
                }
                
                $price = $arrPriceInfo['price'];
                $arrDetails = $arrPriceInfo['details'];
            }
            else if ($type == 'order_id') {
                // echo "3";//,在租车辆列表编辑订单、待查违章列表、历史结算列表进入
                $orderId = intval(\Yii::$app->request->getParam('order_id'));
                if (empty($orderId)) {
                    $arrResult['code'] = 400;
                    $arrResult['msg'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Order')]);
                    break;
                }

                $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
                if (!$objOrder) {
                    $arrResult['code'] = 400;
                    $arrResult['msg'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Order')]);
                    break;
                }
                
                // echo "<pre>";
                // print_r($objOrder);
                // print_r($startTime);
                // echo "</pre>";
                // if ($startTime != $objOrder->start_time || empty($objOrder->vehicle_id)) {
                if (abs($startTime - $objOrder->start_time)>60 || empty($objOrder->vehicle_id)) {//两个时间本相同的时候由于可能日期选择中少了秒数而导致两个时间相差
                    // var_dump($startTime,$objOrder->start_time,$objOrder->vehicle_id);
                    // echo "22";
                    if (empty($objOrder->vehicle_id)) {
                        $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
                        if (!$vehicleId) {
                            $arrResult['code'] = 400;
                            $arrResult['msg'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Vehicle')]);
                            break;
                        }
                        $arrPriceInfo = \common\components\OrderModule::calculateVehicleRentPriceData($vehicleId, $startTime, $endTime, $orderSource, $priceType, $birthday,$userisnew);
                    }
                    else {
                        $arrPriceInfo = \common\components\OrderModule::calculateOrderPriceData($orderId, $startTime, $endTime, $orderSource, $priceType, $birthday,$userisnew);
                    }
                    if ($arrPriceInfo === false) {
                        $arrResult['code'] = 400;
                        $arrResult['msg'] = \Yii::t('carrental', 'Current vehicle model does not configured rental fee, please configure the rental fee first or contact the administrator!');
                        break;
                    }
                    
                    $price = $arrPriceInfo['price'];
                    $arrDetails = $arrPriceInfo['details'];
                    /*echo "<pre>";
                    print_r();
                    echo "</pre>";*/

                }
                else {
                    // $arrDeltaData = $objOrder->onUpdateEndTime($endTime);
                    $arrDeltaData = $objOrder->onOneWayUpdateEndTime($endTime);
                    $price = $objOrder->price_rent;
                    $arrDetails = $objOrder->getDailyRentDetailedPriceArray();
                    $arrResult['origin_overtime_price'] = $arrDeltaData['origin_overtime_price'];
                    $arrResult['now_overtime_price'] = $arrDeltaData['now_overtime_price'];
                    $arrResult['optional_service'] = $arrDeltaData['optional_service'];
                    //sjj其他费用:加油费+车损费+违章费用+超时保费+加油代办价格+其他价格+个人自驾超时费用
                    $arrResult['other_price'] = $arrDeltaData['other_price'];
                    //优惠金额
                    $arrResult['free_price'] = $arrDeltaData['price_preferential'];
                    // $arrResult['price_poundage'] = $arrDeltaData['price_poundage'];//手续费
                    // $arrResult['unit_price_basic_insurance'] = $arrDeltaData['unit_price_basic_insurance'];//基本服务费费
                    $arrResult['price_different_office'] = $arrDeltaData['price_different_office'];//异店还车费
                    $arrResult['price_take_car'] = $arrDeltaData['price_take_car'];//送车上门服务费
                    $arrResult['price_return_car'] = $arrDeltaData['price_return_car'];//上门取车服务费
                }
            }
            else if ($type == 'order_relet') {
                // echo "4";
                $orderId = intval(\Yii::$app->request->getParam('order_id'));
                if (empty($orderId)) {
                    $arrResult['code'] = 400;
                    $arrResult['msg'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Order')]);
                    break;
                }
                
                $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
                if (!$objOrder) {
                    $arrResult['code'] = 400;
                    $arrResult['msg'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Order')]);
                    break;
                }
                
                if ($endTime <= $objOrder->new_end_time) {
                    $arrResult['code'] = 400;
                    $arrResult['msg'] = \Yii::t('carrental', 'Order relet time is not valid!');
                    break;
                }
                
                $arrDeltaData = $objOrder->onUpdateEndTime($endTime);
                $price = $arrDeltaData['price'];
                $arrDetails = $arrDeltaData['details'];
                $arrResult['origin_overtime_price'] = $arrDeltaData['origin_overtime_price'];
                $arrResult['now_overtime_price'] = $arrDeltaData['now_overtime_price'];
                $arrResult['optional_service'] = $arrDeltaData['optional_service'];
                //sjj其他费用:加油费+车损费+违章费用+超时保费+加油代办价格+其他价格+个人自驾超时费用
                $arrResult['other_price'] = $arrDeltaData['other_price'];
                //优惠金额
                $arrResult['free_price'] = $arrDeltaData['price_preferential'];
                // $arrResult['price_poundage'] = $arrDeltaData['price_poundage'];//手续费
                // $arrResult['unit_price_basic_insurance'] = $arrDeltaData['unit_price_basic_insurance'];//基本服务费费
                $arrResult['price_different_office'] = $arrDeltaData['price_different_office'];//异店还车费
                $arrResult['price_take_car'] = $arrDeltaData['price_take_car'];//送车上门服务费
                $arrResult['price_return_car'] = $arrDeltaData['price_return_car'];//上门取车服务费

                if (isset($arrDeltaData['calc_start_time'])) {
                    $startTime = $arrDeltaData['calc_start_time'];
                }
                
            }
            
            $arrResult['start_time'] = $startTime;
            $arrResult['end_time'] = $endTime;
            $arrResult['value'] = $price;
            $arrResult['details'] = $arrDetails;
			// print_r($arrResult);exit;
        }while(0);
        echo json_encode($arrResult);
    }
    
    public function actionGet_order_endtime() {
        $orderId = intval(Yii::$app->request->getParam('id'));
        $arrResult = ['code'=>0, 'value'=>''];
        do
        {
            if (!$orderId) {
                $arrResult['code'] = -1;
                $arrResult['msg'] = Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')])]);
                break;
            }
            $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
            if (!$objOrder) {
                $arrResult['code'] = -1;
                $arrResult['msg'] = Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Order')]);
                break;
            }
            
            $arrResult['value'] = date('Y-m-d H:i:s', $objOrder->new_end_time);
            
        }while(0);
        echo json_encode($arrResult);
    }
    
    public function actionBooking_dispatch() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        $arrData = [
            'orderId' => $orderId,
        ];
        
        if (!empty($orderId)) {
            $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
            if ($objOrder) {
                $arrData['vehicleId'] = $objOrder->vehicle_id;
            }
        }
        
        return $this->renderPartial('booking_dispatch', $arrData);
    }
    
    public function actionRenting_settlement() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        $arrData = [
            'orderId' => $orderId,
        ];
        
        if (!empty($orderId)) {
            $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
            if ($objOrder) {
                $arrData['vehicleId'] = $objOrder->vehicle_id;
            }
        }
        
        return $this->renderPartial('renting_settlement', $arrData);
        
    }
    
    public function actionViolation_settlement() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        $arrData = [
            'orderId' => $orderId,
        ];
        
        if (!empty($orderId)) {
            $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
            if ($objOrder) {
                $arrData['vehicleId'] = $objOrder->vehicle_id;
            }
        }
        
        return $this->renderPartial('violation_settlement', $arrData);
        
    }
    
    public function actionSettlement() {

        $orderId = intval(\Yii::$app->request->getParam('id'));
        $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
        $orderType = intval(\Yii::$app->request->getParam('type'));
        $arrData = [
            'vehicleId' => $vehicleId,
            'orderType' => $orderType,
        ];
        
        if ($vehicleId) {
            $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
            if ($objVehicle) {
                $arrData['vehicleModelId'] = $objVehicle->model_id;
            }
        }
        
        if (!empty($orderId)) {
            $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
            $arrData['objVehicleOrder'] = $objOrder;
        }
        else {
            $arrData['objVehicleOrder'] = null;
        }
        
        return $this->renderPartial('settlement', $arrData);
    }
    
    public function actionConfirm_booked() {
        $orderId = intval(\Yii::$app->request->getParam('id'));
        if (!$orderId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')])]), 300);
        }
        $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
        if (!$objOrder) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Order')]), 300);
        }
        
        if ($objOrder->status != \common\models\Pro_vehicle_order::STATUS_BOOKED) {
            MyFunction::funEchoJSON_Ajax(Yii::t('carrental', 'The order were not booked status, so the order should not be confirmed.'), 300);
        }
        else if ($objOrder->confirmed_at > 0) {
            MyFunction::funEchoJSON_Ajax(Yii::t('carrental', 'The order had already been confirmed.'), 300);
        }
        
        $objOrder->confirmed_at = time();
        $objOrder->save();
        MyFunction::funEchoJSON_Ajax(Yii::t('carrental', 'Confirmed success.'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionPurchase_order() {
        $orderType = intval(\Yii::$app->request->getParam('order_type'));
        $orderSerial = \Yii::$app->request->getParam('order_id');
        if (empty($orderSerial)) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')])]), 300);
        }
        
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
    }
    
    public function actionPurchase_index() {
        return $this->renderPartial('purchase_index');
    }
    
    public function actionPurchase_order_list() {
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
        
        $cdb = \common\models\Pro_purchase_order::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $channelType = intval(\Yii::$app->request->getParam('channel_type'));
        $status = intval(\Yii::$app->request->getParam('status'));
        $serial = \Yii::$app->request->getParam('serial');
        if (!$channelType) {
            $cdb->where(['<>', 'channel_type', \common\models\Pro_purchase_order::CHANNEL_TYPE_OFFICE]);
        }
        else {
            $cdb->where(['channel_type'=>$channelType]);
        }
        if (!empty($status)) {
            $cdb->andWhere(['status'=>$status]);
        }
        if (!empty($serial)) {
            $cdb->andWhere(['serial'=>$serial]);
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();

        $arrAdminIds = [];
        $arrUserIds = [];
        $arrOfficeIds = [];
        $arrOrderIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
            if (!isset($arrUserIds[$row->user_id])) {
                $arrUserIds[$row->user_id] = 1;
            }
            if (!isset($arrOfficeIds[$row->belong_office_id])) {
                $arrOfficeIds[$row->belong_office_id] = 1;
            }
            if (!isset($arrOrderIds[$row->bind_id])) {
                $arrOrderIds[$row->bind_id] = 1;
            }
        }
        
        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrUsers = \common\components\UserModule::getUserInfoObjectsByUserIdArray(array_keys($arrUserIds));
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrOrderSerials = [];
        if (!empty($arrOrderIds)) {
            $cdborders = \common\models\Pro_vehicle_order::find(true);
            $cdborders->where(['id'=>array_keys($arrOrderIds)]);
            $arrOrderRows = $cdborders->all();
            foreach ($arrOrderRows as $row) {
                $arrOrderSerials[$row->id] = $row->serial;
            }
        }
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
            $o['user_disp'] = (isset($arrUsers[$row->user_id]) ? $arrUsers[$row->user_id]->name : '');
            $o['belong_office_disp'] = (isset($arrOfficeNames[$row->belong_office_id]) ? $arrOfficeNames[$row->belong_office_id] : '');
            $o['order_serial'] = (isset($arrOrderSerials[$row->bind_id]) ? $arrOrderSerials[$row->bind_id] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionUserrentlist_index() {
        $userId = \Yii::$app->request->getParam('user_id');
        $arrData = [
            'userId' => $userId,
        ];
        return $this->renderPartial('userrentlist_index', $arrData);
    }
    
    public function actionGet_order_paid_amount() {
        $orderId = intval(\Yii::$app->request->getParam('order_id'));
        $arrRet = ['code'=>0, 'amount'=>0];
        if (!$orderId) {
            $arrRet['code'] = -1;
        }
        else {
            $cdb = \common\models\Pro_vehicle_order::find(true);
            $cdb->where(['id' => $orderId]);
            $objOrder = $cdb->one();
            if ($objOrder) {
                $arrRet['amount'] = $objOrder->paid_amount;
            }
            else {
                $arrRet['code'] = -1;
            }
        }
        
        echo json_encode($arrRet);
    }
    
    public function actionExport_order_data() {
        $cdb = \common\models\Pro_vehicle_order::find();
        $cdb->orderBy("start_time asc");
        
        // conditions
        $status = intval(\Yii::$app->request->getParam('status'));
        $serial = \Yii::$app->request->getParam('serial');
        $plateNumber = \Yii::$app->request->getParam('plate_number');
        $vehicle_model_id = intval(\Yii::$app->request->getParam('vehicle_model_id'));
        $customer_name = \Yii::$app->request->getParam('customer_name');
        $customer_telephone = \Yii::$app->request->getParam('customer_telephone');
        $office_id = \Yii::$app->request->getParam('office_id');
        $userId = intval(\Yii::$app->request->getParam('user_id'));
        if ($status) {
            if ($status == \common\models\Pro_vehicle_order::STATUS_COMPLETED) {
                $cdb->andWhere(['status'=>[$status, \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING]]);
            }
            else {
                $cdb->andWhere(['status'=>$status]);
            }
        }
        if (!empty($serial)) {
            $cdb->andWhere(['serial' => $serial]);
        }
        if (!empty($plateNumber)) {
            $tmpFinder = \common\models\Pro_vehicle::find();
            $tmpFinder->select(['id']);
            $tmpFinder->where('plate_number LIKE :keywords', [':keywords' => '%'.$plateNumber.'%']);
            $tmpArr = $tmpFinder->all();
            $vehicleIds = [];
            foreach ($tmpArr as $row) {
                $vehicleIds[] = $row->id;
            }
            if (empty($vehicleIds)) {
                $cdb->andWhere(['vehicle_id' => 0]);
            }
            else {
                $cdb->andWhere(['vehicle_id' => $vehicleIds]);
            }
        }
        if (!empty($vehicle_model_id)) {
            $cdb->andWhere(['vehicle_model_id' => $vehicle_model_id]);
        }
        if (!empty($customer_name)) {
            $cdb->andWhere('customer_name LIKE :keywords2', [':keywords2' => '%'.$customer_name.'%']);
        }
        if (!empty($customer_telephone)) {
            $cdb->andWhere('customer_telephone LIKE :keywords3', [':keywords3' => '%'.$customer_telephone.'%']);
        }
        if (!empty($office_id)) {
            $cdb->andWhere(['belong_office_id' => $office_id]);
        }
        if ($userId) {
            $cdb->andWhere(['user_id' => $userId]);
            //if (!$status) {
            //    $cdb->andWhere(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
            //}
        }
        
        $arrRows = $cdb->all();

        $arrModelIds = [];
        $arrVehicleIds = [];
        $arrAdminIds = [];
        $arrUserIds = [];
        $arrOfficeIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrModelIds[$row->vehicle_model_id])) {
                $arrModelIds[$row->vehicle_model_id] = 1;
            }
            if (!isset($arrVehicleIds[$row->vehicle_id])) {
                $arrVehicleIds[$row->vehicle_id] = 1;
            }
            if (!isset($arrOfficeIds[$row->belong_office_id])) {
                $arrOfficeIds[$row->belong_office_id] = 1;
            }
            if (!isset($arrOfficeIds[$row->office_id_rent])) {
                $arrOfficeIds[$row->office_id_rent] = 1;
            }
            if (!isset($arrOfficeIds[$row->office_id_return])) {
                $arrOfficeIds[$row->office_id_return] = 1;
            }
            if ($row->edit_user_id && !isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
            if ($row->settlement_user_id && !isset($arrAdminIds[$row->settlement_user_id])) {
                $arrAdminIds[$row->settlement_user_id] = 1;
            }
            if (!isset($arrUserIds[$row->user_id])) {
                $arrUserIds[$row->user_id] = 1;
            }
        }
        
        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrOffices = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrModelNames = \common\components\VehicleModule::getVehicleModelNamesArrayByIds(array_keys($arrModelIds));
        $arrVehicleObjects = \common\components\VehicleModule::getVehicleObjects(array_keys($arrVehicleIds));
        $arrUserInfos = \common\components\UserModule::getUserInfoObjectsByUserIdArray(array_keys($arrUserIds));
        $arrVipLevels = \common\components\UserModule::getVipLevelsArray();
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $vipLevel = (isset($arrUserInfos[$row->user_id]) ? $arrUserInfos[$row->user_id]->vip_level : 0);
            $o = $row->getAttributes();
            
            $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
            $o['settlement_user_disp'] = (isset($arrAdmins[$row->settlement_user_id]) ? $arrAdmins[$row->settlement_user_id] : '');
            $o['vehicle_model_name'] = (isset($arrModelNames[$row->vehicle_model_id]) ? $arrModelNames[$row->vehicle_model_id] : '');
            $o['plate_number'] = (isset($arrVehicleObjects[$row->vehicle_id]) ? $arrVehicleObjects[$row->vehicle_id]->plate_number : '');
            $o['belong_office_disp'] = (isset($arrOffices[$row->belong_office_id]) ? $arrOffices[$row->belong_office_id] : '');
            $o['rent_office_disp'] = (isset($arrOffices[$row->office_id_rent]) ? $arrOffices[$row->office_id_rent] : '');
            $o['return_office_disp'] = (isset($arrOffices[$row->office_id_return]) ? $arrOffices[$row->office_id_return] : '');
            $o['customer_vip_level'] = (isset($arrVipLevels[$vipLevel]) ? $arrVipLevels[$vipLevel] : '');
            $o['daily_rent_details'] = $row->getDailyRentDetailedPriceArray();
            
            $arrData[] = $o;
        }
        
        $cols = [
            'serial' => 'serial',
            'belong_office_id' => ['attribute'=>'belong_office_id','value'=>function($model){ return $model['belong_office_disp']; }], 
            'vehicle_id' => ['attribute'=>'vehicle_id','value'=>function($model){ return $model['plate_number']; }], 
            'vehicle_model_id' => ['attribute'=>'vehicle_model_id','value'=>function($model){ return $model['vehicle_model_name']; }], 
            'type' => ['attribute'=>'type','value'=>function($model){ $arr = \common\components\OrderModule::getOrderTypeArray(); return (isset($arr[$model['type']]) ? $arr[$model['type']] : ''); }], 
            'customer_name' => 'customer_name',
            'customer_telephone' => 'customer_telephone',
            'customer_vip_level' => 'customer_vip_level',
            'total_amount' => 'total_amount',
            'paid_amount' => 'paid_amount',
            'price_rent' => 'price_rent',
            'rent_per_day' => 'rent_per_day', 
            'price_overtime' => 'price_overtime', 
            'price_overmileage' => 'price_overmileage', 
            'price_designated_driving' => 'price_designated_driving', 
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
            'price_insurance_overtime' => 'price_insurance_overtime', 
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
            'price_left' => ['attribute'=>'price_left','value'=>function($model){ return $model['total_amount'] - $model['paid_amount']; }], 
            'source' => ['attribute'=>'source','value'=>function($model){ $arr = \common\components\OrderModule::getOrderSourceArray(); return (isset($arr[$model['source']]) ? $arr[$model['source']] : ''); }], 
            'pay_source' => ['attribute'=>'pay_source','value'=>function($model){ $arr = \common\components\OrderModule::getOrderPayTypeArray(); return (isset($arr[$model['pay_source']]) ? $arr[$model['pay_source']] : ''); }], 
            'deposit_pay_source' => ['attribute'=>'deposit_pay_source','value'=>function($model){ $arr = \common\components\OrderModule::getOrderPayTypeArray(); return (isset($arr[$model['deposit_pay_source']]) ? $arr[$model['deposit_pay_source']] : ''); }], 
            'price_pre_license' => ['attribute'=>'price_pre_license','header'=>'预授权','format'=>'text','value'=>function($model){return ($model['price_deposit']+$model['price_deposit_violation']).($model['deposit_pay_source']==\common\models\Pro_vehicle_order::PAY_TYPE_PRE_LICENSING?'(预授权)':''); }],
            'last_month_amount' => ['attribute'=>'last_month_amount','header'=>'上月营业额','format'=>'text','value'=>function($model){return ''; }],
            'cur_month_amount' => ['attribute'=>'cur_month_amount','header'=>'本月营业额','format'=>'text','value'=>function($model){return $model['paid_amount']; }],
            'price_delay_working' => ['attribute'=>'price_delay_working','header'=>'误工费','format'=>'text','value'=>function($model){return ''; }],
            'price_depreciation' => ['attribute'=>'price_depreciation','header'=>'折旧费','format'=>'text','value'=>function($model){return ''; }],
            'price_discount' => ['attribute'=>'price_discount','header'=>'折让','format'=>'text','value'=>function($model){return ''; }],
            'price_other_amount' => ['attribute'=>'price_other_amount','header'=>'其他','format'=>'text','value'=>function($model){return ''; }],
            'remark' => 'remark',
        ];
        
        $columns = null;
        if ($status == \common\models\Pro_vehicle_order::STATUS_COMPLETED) {
            $columns = [$cols['serial'], $cols['source'], $cols['vehicle_id'], 
                $cols['vehicle_model_id'], $cols['customer_name'], //$cols['customer_telephone'], $cols['customer_vip_level'], 
                $cols['start_time'], $cols['car_returned_at'], $cols['settlemented_at'], 
                $cols['car_dispatched_date'], $cols['car_returned_date'], $cols['settlemented_date'], 
                $cols['rent_per_day'], $cols['rent_days'], 
                $cols['total_amount'], $cols['pay_source'], $cols['price_pre_license'], 
                $cols['last_month_amount'], $cols['price_rent'], $cols['price_optional_service'],
                $cols['price_car_damage'], $cols['price_delay_working'], $cols['price_depreciation'],
                $cols['price_oil'], $cols['price_designated_driving'], $cols['price_other_amount'],
                $cols['price_discount'], $cols['price_preferential'], $cols['cur_month_amount'],
                $cols['paid_amount'], $cols['remark'],
                $cols['price_left'], $cols['belong_office_id'],
            ];
        }
        else {
            $columns = [$cols['serial'], $cols['source'], $cols['vehicle_id'], $cols['vehicle_model_id'], 
                $cols['customer_name'], $cols['customer_telephone'], $cols['customer_vip_level'], 
                $cols['deposit_pay_source'], $cols['price_deposit'], $cols['paid_amount'], 
                $cols['total_amount'], $cols['belong_office_id'], 
                $cols['start_time'], $cols['new_end_time'], $cols['rent_days'], $cols['price_left']
            ];
        }
            
        $model = new \common\models\Pro_vehicle_order();
        $arrStatusText = \common\components\OrderModule::getOrderStatusArray();
        $arrStatusText[\common\models\Pro_vehicle_order::STATUS_COMPLETED] = '历史结算';
        $arrStatusText[\common\models\Pro_vehicle_order::STATUS_RENTING] = '在租';
        \moonland\phpexcel\Excel::export([
            'models' => $arrData,
            'columns' => $columns,
            'headers' => $model->attributeLabels(),
            'fileName' => \Yii::t('locale', '{type} order list', ['type'=>(isset($arrStatusText[$status]) ? $arrStatusText[$status] : '')]),
            'format' => 'Excel2007',
        ]);
    }
    
    public function actionPaymentinput() {
        $orderId = intval(\Yii::$app->request->getParam('order_id'));
        $isRelet = intval(\Yii::$app->request->getParam('is_relet'));
        $isSettlement = intval(\Yii::$app->request->getParam('is_settlement'));
        $orderAction = \Yii::$app->request->getParam('order_action');
        
        $action = \Yii::$app->request->getParam('action');
		
        if (!empty($action)) {
            $objFormData = new \backend\models\Form_pro_vehicle_order_price_detail();
			
            if ($objFormData->load(\Yii::$app->request->post())) {
                $objOrder = \common\models\Pro_vehicle_order::findById($objFormData->order_id);
				
                if (!$objOrder) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('carrental', 'The main order does not exists.'), 300);
                }
                $objUserInfo = \common\models\Pub_user_info::findById($objOrder->user_id);
                $objModel = new \common\models\Pro_vehicle_order_price_detail();
				
                if ($objFormData->type != \common\models\Pro_vehicle_order_price_detail::TYPE_PAID) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} content is invalid.', ['name'=>$objModel->getAttributeLabel('type')]), 300);
                }
               
                if (!$objFormData->save($objModel)) {
                    $errText = $objFormData->getErrorAsHtml();
                    MyFunction::funEchoJSON_Ajax(empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText, 300);
                }
                
                $objModel->belong_office_id = $objOrder->belong_office_id;
                $objModel->summary();
                if ($objModel->summary_amount && $objModel->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_NONE) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} content is invalid.', ['name'=>$objModel->getAttributeLabel('pay_source')]), 300);
                }
                if ($objModel->summary_deposit && $objModel->deposit_pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_NONE) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} content is invalid.', ['name'=>$objModel->getAttributeLabel('deposit_pay_source')]), 300);
                }
                
                if ($objModel->save()) {
                    if ($objModel->summary_deposit && $objModel->deposit_pay_source) {
                        $objOrder->paid_deposit += $objModel->summary_deposit;
                        $objOrder->deposit_pay_source = $objModel->deposit_pay_source;
                        
                        $objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehicleOrder($objOrder, $objModel->summary_deposit, $objModel->belong_office_id, \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT, $objModel->time);
                        $objPurchaseOrder->save();
                    }
                    if ($objModel->summary_amount && $objModel->pay_source) {
                        $objOrder->paid_amount += $objModel->summary_amount;
                        if ($isSettlement) {
                            $objOrder->settlement_pay_source = $objModel->pay_source;
                            if ($objOrder->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_NONE) {
                                $objOrder->pay_source = $objModel->pay_source;
                            }
                        }
                        else {
                            $objOrder->pay_source = $objModel->pay_source;
                        }
                        
                        $objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehicleOrder($objOrder, $objModel->summary_amount, $objModel->belong_office_id, ($isRelet ? \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT_RENEWAL : \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT), $objModel->time);
                        $objPurchaseOrder->save();
                    }
                    $remarkKey = 'remark';
                    if ($isSettlement) {
                        $remarkKey = 'settlement_remark';
                    }
                    if (!empty($objModel->remark) && empty($objOrder->$remarkKey)) {
                        $objOrder->$remarkKey = $objModel->remark;
                    }
                    
                    $objOrder->save();
                    
                    /*if ($objUserInfo) {
                        if (floatval($objModel->price_rent)) {
                            $integralLog = UserModule::onUserConsumeByRent($objUserInfo, $objModel->price_rent, false);
                            if ($integralLog) {
                                $integralLog->save();
                            }
                            $objUserInfo->onConsumeAmount($objModel->summary_amount);
                            $objUserInfo->save();
                        }
                        elseif (floatval($objModel->summary_amount)) {
                            $objUserInfo->onConsumeAmount($objModel->summary_amount);
                            $objUserInfo->save();
                        }
                    }*/
                    
                    $url = '';
                    $callbackType = '';
                    if ($orderAction == 'insert') {
                        $url = \yii\helpers\Url::to(['order/edit', 'id'=>$objModel->order_id]);
                        $callbackType = 'refreshorder';
                    }
                    MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'Input successfully!'), 200, '', '', $callbackType, $url, '', ['order_id'=>$objModel->order_id, 'is_relet'=>$isRelet]);
                }
                else {
                    MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'Sorry, the operation failed!'), 300);
                }
            }
            else {
                $errText = $objFormData->getErrorAsHtml();
                MyFunction::funEchoJSON_Ajax(empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText, 300);
            }
            
        }
        
        $arrData = [
            'orderId' => $orderId,
            'isRelet' => $isRelet,
            'isSettlement' => $isSettlement,
            'orderAction' => $orderAction,
        ];
		
        return $this->renderPartial('paymentinput', $arrData); 
    }
    
    public function actionPaymentdetail_index() {
        $serial = \Yii::$app->request->getParam('serial');
        return $this->renderPartial('paymentdetail_index', [
            'serial' => $serial,
        ]);
    }
    
    public function actionPaymentdetail_list() {
        // get pagination
        $intPage = intval(Yii::$app->request->getParam('page'));
        if ($intPage == 0) {
            $intPage = 1;
        }
        $numPerPage = intval(Yii::$app->request->getParam('rows'));
        if (!$numPerPage) {
            $numPerPage = $this->pageSize;
        }
        
        $arrData = [];
        $count = 0;
        
        $serial = \Yii::$app->request->getParam('serial');
        $objOrder = NULL;
        if (!empty($serial)) {
            $objOrder = \common\models\Pro_vehicle_order::findById($serial, 'serial');
        }
        
        if ($objOrder) {
            // get order
            $intSort = strval(Yii::$app->request->getParam('sort'));
            $intSortDirection = strval(Yii::$app->request->getParam('order'));
            if (!empty($intSort) && !empty($intSortDirection)) {
                $order = $intSort . " " . $intSortDirection;
            }

            $cdb = \common\models\Pro_vehicle_order_price_detail::find();
            $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");

            // conditions
            $cdb->where(['type'=>\common\models\Pro_vehicle_order_price_detail::TYPE_PAID]);
            $cdb->andWhere(['>=', 'status', \common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL]);
            $cdb->andWhere(['order_id'=>$objOrder->id]);

            // pagiation
            $count = $cdb->count();
            $pages = new \yii\data\Pagination(['totalCount'=>$count]);
            $pages->setPageSize($numPerPage);
            $pages->setPage($intPage - 1);
            $cdb->limit($pages->getLimit());
            $cdb->offset($pages->getOffset());

            $arrRows = $cdb->all();

            $arrAdminIds = [];
            $arrOfficeIds = [];
            $arrOrderIds = [];
            foreach ($arrRows as $row) {
                if (!isset($arrAdminIds[$row->edit_user_id])) {
                    $arrAdminIds[$row->edit_user_id] = 1;
                }
                if (!isset($arrOfficeIds[$row->belong_office_id])) {
                    $arrOfficeIds[$row->belong_office_id] = 1;
                }
                if (!isset($arrOrderIds[$row->order_id])) {
                    $arrOrderIds[$row->order_id] = 1;
                }
            }

            $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
            $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
            $arrOrderSerials = [];
            if (!empty($arrOrderIds)) {
                $cdborders = \common\models\Pro_vehicle_order::find(true);
                $cdborders->where(['id'=>array_keys($arrOrderIds)]);
                $arrOrderRows = $cdborders->all();
                foreach ($arrOrderRows as $row) {
                    $arrOrderSerials[$row->id] = $row->serial;
                }
            }

            foreach ($arrRows as $row) {
                $o = $row->getAttributes();
                $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
                $o['belong_office_disp'] = (isset($arrOfficeNames[$row->belong_office_id]) ? $arrOfficeNames[$row->belong_office_id] : '');
                $o['order_serial'] = (isset($arrOrderSerials[$row->order_id]) ? $arrOrderSerials[$row->order_id] : '');

                $arrData[] = $o;
            }
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionPaymentdetail_delete() {
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_vehicle_order_price_detail::findById($intID);
        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'Data does not exist!'), 300);
        }
        if ($objData->type != \common\models\Pro_vehicle_order_price_detail::TYPE_PAID) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'The record can not be deleted!'), 300);
        }
        
        $objOrder = \common\models\Pro_vehicle_order::findById($objData->order_id);
        if (!$objOrder) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Order')]), 300);
        }
        
        $cdb = \common\models\Pro_purchase_order::find();
        $cdb->where(['bind_id'=>$objData->order_id, 'status'=>\common\models\Pro_purchase_order::STATUS_SUCCEES, 'purchased_at'=>$objData->time]);
        $arrRows = $cdb->all();
        $arrPurchases = [];
        $notFound = true;
        $summaryAmmount = floatval($objData->summary_amount);
        $summaryDeposit = floatval($objData->summary_deposit);
        if ($summaryAmmount) {
            if (floatval($objOrder->paid_amount) < $summaryAmmount) {
                MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'Sorry, the operation failed!'), 300);
            }
            $notFound = true;
            foreach ($arrRows as $row) {
                if (floatval($row->amount) == $summaryAmmount && $row->sub_type != \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT) {
                    $arrPurchases[] = $row;
                    $notFound = false;
                    break;
                }
            }
        }
        if ($summaryDeposit) {
            if (floatval($objOrder->paid_deposit) < $summaryDeposit) {
                MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'Sorry, the operation failed!'), 300);
            }
            $notFound = true;
            foreach ($arrRows as $row) {
                if (floatval($row->amount) == $summaryDeposit && $row->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT) {
                    $arrPurchases[] = $row;
                    $notFound = false;
                    break;
                }
            }
        }
        if ($notFound) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('carrental', 'The payment order verify information in imcompleted, you can not complete this operation.'), 300);
        }
        foreach ($arrPurchases as $objPurchase) {
            if ($objPurchase->channel_type != \common\models\Pro_purchase_order::CHANNEL_TYPE_OFFICE) {
                MyFunction::funEchoJSON_Ajax(\Yii::t('carrental', 'The payment order comes from third party channel, please process the order via refound procedure.'), 300);
            }
        }
        
        foreach ($arrPurchases as $objPurchase) {
            $objPurchase->status = \common\models\Pro_purchase_order::STATUS_DELETED;
            $objPurchase->edit_user_id = \Yii::$app->user->id;
            $objPurchase->save();
        }
        if ($summaryAmmount) {
            $objOrder->paid_amount = floatval($objOrder->paid_amount) - $summaryAmmount;
        }
        if ($summaryDeposit) {
            $objOrder->paid_deposit = floatval($objOrder->paid_deposit) - $summaryDeposit;
        }
        $objOrder->save();
        $objData->status = \common\models\Pro_vehicle_order_price_detail::STATUS_DISABLED;
        $objData->edit_user_id = \Yii::$app->user->id;
        $objData->save();
        
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
}

<?php
namespace backend\controllers;

use Yii;
use common\helpers\MyFunction;
// use moonland\phpexcel\Excel;

/**
 * Description of VehicleController
 *
 * @author kevin
 */
class VehicleController extends \backend\components\AuthorityController
{
    private $pageSize = 20;
    
    public function actionAa(){
        $params = \Yii::$app->request->getParams();
        $filterModel = new \backend\models\searchers\Searcher_pro_vehicle();
        $filterModel->setPagerInfo(false);
        //$filterModel->loadPagination($params);
        //$filterModel->loadSort($params);
        $dataProvider = $filterModel->search($params, '');
        
        $dataProvider->manualFormatModelValues();
        
        $model = new \common\models\Pro_vehicle();
        // var_dump($dataProvider->getModels());exit;
        \moonland\phpexcel\Excel::export([
            'models' => $dataProvider->getModels(),
            'columns' => [
                'plate_number', 'model_id', 'status', 'engine_number', 'vehicle_number', 
                'color', 'baught_time', 'cur_kilometers', 'belong_office_id', 'stop_office_id', 
                'gps_id', 'annual_inspection_time', 'tci_renewal_time', 'vci_renewal_time', 
                'edit_user_id', 'updated_at'
            ],
            'headers' => $model->attributeLabels(),
            'fileName' => '车辆信息列表',
            'format' => 'Excel2007',
        ]);
    }
    public function getView() {
        $prefix = \backend\components\AdminHtmlService::getViewPrefix();
        if ($prefix && $this->action->id != 'index') {
            return \Yii::createObject([
                'class' => \common\components\ViewExtend::className(),
                'prefix' => $prefix,
            ]);
        }
        return parent::getView();
    }
    
    public function actionIndex() {
        $action = \Yii::$app->request->getParam('action');
        $arrParams = [];
        if (!empty($action)) {
            $arrParams['action'] = $action;
        }
        return $this->renderPartial('index', $arrParams);
    }
    
    public function actionAllIndex() {
        $arrParams = ['type'=>'all', 'action'=>\Yii::$app->request->getParam('action')];
        return $this->renderPartial('vehiclelist', $arrParams);
    }

    public function actionAllIndex_change($value='')
    {
        
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
        
        /*echo "$date_start".'------';
        echo "$date";*/
        // $arrData = \backend\components\StatisticsService::getMonthlyOrderIncomeData($status, $date, $belongOfficeId);
        
        $arrData = \backend\components\StatisticsService::getMonthlyOrderIncomeDataNew($status, $date, $belongOfficeId,$date_start,$pay_type);
        
        return $this->renderPartial('vehiclechangelist', [
            'columns'=>\backend\components\VehicleService::getMonthlyOrderIncomeDataColumns($status,$pay_type),
            'models'=>$arrData,
            'date'=>$date,
            'date_start'=>$date_start,
            'status'=>$status,
            'pay_type'=>$pay_type,
            'belongOfficeId'=>($belongOfficeId?$belongOfficeId:''),
        ]);
    }
    
    public function actionRecentlyrenewalIndex() {
        $arrParams = ['type'=>'recentlyrenewal', 'action'=>\Yii::$app->request->getParam('action')];
        return $this->renderPartial('vehiclelist', $arrParams);
    }
    
    public function actionRecentlyannualIndex() {
        $arrParams = ['type'=>'recentlyannual', 'action'=>\Yii::$app->request->getParam('action')];
        return $this->renderPartial('vehiclelist', $arrParams);
    }
    
    public function actionPeriodicmaintenanceIndex() {
        $arrParams = ['type'=>'periodicmaintenance', 'action'=>\Yii::$app->request->getParam('action')];
        return $this->renderPartial('vehiclelist', $arrParams);
    }
    
    public function actionStagemaintenanceIndex() {
        $arrParams = ['type'=>'stagemaintenance', 'action'=>\Yii::$app->request->getParam('action')];
        return $this->renderPartial('vehiclelist', $arrParams);
    }
    
    public function actionUndermaintenanceIndex() {
        $arrParams = ['type'=>'undermaintenance', 'action'=>\Yii::$app->request->getParam('action')];
        return $this->renderPartial('vehiclelist', $arrParams);
    }
    
    public function actionSaledIndex() {
        $arrParams = ['type'=>'saled', 'action'=>\Yii::$app->request->getParam('action')];
        return $this->renderPartial('vehiclelist', $arrParams);
    }
    
    public function actionWaiting_index() {
        $arrData = ['vehicleStatus' => \common\models\Pro_vehicle::STATUS_NORMAL];
        return $this->renderPartial('waiting_index', $arrData);
    }
    
    public function actionBooking_index() {
        $arrData = ['vehicleStatus' => \common\models\Pro_vehicle::STATUS_BOOKED];
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionRenting_index() {
        $arrData = ['vehicleStatus' => \common\models\Pro_vehicle::STATUS_RENTED];
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionMaintenance_index() {
        $arrData = ['vehicleStatus' => \common\models\Pro_vehicle::STATUS_MAINTENANCE];
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionViolation_index() {
        $arrData = ['vehicleStatus' => \common\models\Pro_vehicle::STATUS_MAINTENANCE];
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionVehicle_list() {
        $params = \Yii::$app->request->getParams();

        $filterModel = new \backend\models\searchers\Searcher_pro_vehicle();
        $filterModel->loadPagination($params);
        $filterModel->loadSort($params);
        $dataProvider = $filterModel->search($params, '');
        
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
    
    public function actionVehicleExport() {
        $params = \Yii::$app->request->getParams();
        $filterModel = new \backend\models\searchers\Searcher_pro_vehicle();
        $filterModel->setPagerInfo(false);
        //$filterModel->loadPagination($params);
        //$filterModel->loadSort($params);
        $dataProvider = $filterModel->search($params, '');
        
        $dataProvider->manualFormatModelValues();
        
        $model = new \common\models\Pro_vehicle();
        // var_dump($dataProvider->getModels());exit;
        \moonland\phpexcel\Excel::export([
            'models' => $dataProvider->getModels(),
            'columns' => [
                'plate_number', 'model_id', 'status', 'engine_number', 'vehicle_number', 
                'color', 'baught_time', 'cur_kilometers', 'belong_office_id', 'stop_office_id', 
                'gps_id', 'annual_inspection_time', 'tci_renewal_time', 'vci_renewal_time', 
                'edit_user_id', 'updated_at'
            ],
            'headers' => $model->attributeLabels(),
            'fileName' => '车辆信息列表',
            'format' => 'Excel2007',
        ]);
    }
    
    public function actionNonbooked_vehicle_list() {
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
        
        // check the vehicle order times
        $orderSource = intval(\Yii::$app->request->getParam('source_type'));
        $payType = intval(\Yii::$app->request->getParam('pay_type'));
        $vehicleModelId = intval(\Yii::$app->request->getParam('vehicle_model_id'));
        $startTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->getParam('start_time'));
        $endTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->getParam('end_time'));
        $skipOrderId = intval(\Yii::$app->request->getParam('skip_order_id'));
        $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
        $plateNumber = \Yii::$app->request->getParam('plate_number');
        
        $checkStartTime = $startTime;
        //分配车辆提前两小时判断去除
        /*if ($checkStartTime > 0) {
            $checkStartTime -= 3600*2;
        }*/
        
        // conditions
        $arrVehicleIds = \common\components\OrderModule::getVehicleIdsByTimeRegion($checkStartTime, $endTime, 0, $vehicleModelId, $skipOrderId, $vehicleId);
        
        $cdb = \common\models\Pro_vehicle::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        $count = 0;
        $arrData = [];
        
        if (!empty($arrVehicleIds)) {
            $cdb->andWhere(['id'=>$arrVehicleIds]);
            if (!empty($plateNumber)) {
                $cdb->andWhere('plate_number LIKE :keywords', [':keywords' => '%'.$plateNumber.'%']);
            }
            
            // pagiation
            $count = $cdb->count();
            $pages = new \yii\data\Pagination(['totalCount'=>$count]);
            $pages->setPageSize($numPerPage);
            $pages->setPage($intPage - 1);
            $cdb->limit($pages->getLimit());
            $cdb->offset($pages->getOffset());

            $arrRows = $cdb->all();
            
            $arrData = \common\components\VehicleModule::formatVehicleDatagridDataArray($arrRows, true, $startTime, $endTime, $payType);
            
            if ($vehicleId) {
                $isFoundVehicle = false;
                foreach ($arrRows as $row) {
                    if ($row->id == $vehicleId) {
                        $isFoundVehicle = true;
                        break;
                    }
                }
                
                if (!$isFoundVehicle) {
                    $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
                    if ($objVehicle) {
                        $arrTmp = \common\components\VehicleModule::formatVehicleDatagridDataArray([$objVehicle], true, $startTime, $endTime, $payType);
                        foreach ($arrTmp as $row) {
                            $arrData[] = $row;
                        }
                    }
                }
            }
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionWaiting_vehicle_list() {
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
        
        // conditions
        $vehicleModelId = intval(\Yii::$app->request->getParam('vehicle_model_id'));
        $startTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->getParam('start_time'));
        $endTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->getParam('end_time'));
        $plateNumber = \Yii::$app->request->getParam('plate_number');
        $stopOfficeId = intval(\Yii::$app->request->getParam('stop_office_id'));
        if (!$startTime) {
            $startTime = time();
        }
        
        $arrVehicleIds = \common\components\OrderModule::getVehicleIdsByTimeRegion($startTime, $endTime, 0, 0, 0, 0, true);
        
        $count = 0;
        $arrData = [];
        if (!empty($arrVehicleIds)) {
            $cdb = \common\models\Pro_vehicle::find();
            $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
            $cdb->where(['id'=>$arrVehicleIds]);
            if (!empty($plateNumber)) {
                $cdb->andWhere('plate_number LIKE :keywords', [':keywords' => '%'.$plateNumber.'%']);
            }
            if ($stopOfficeId > 0) {
                $cdb->andWhere(['stop_office_id' => $stopOfficeId]);
            }
            if ($vehicleModelId > 0) {
                $cdb->andWhere(['model_id' => $vehicleModelId]);
            }
            
            // pagiation
            $count = $cdb->count();
            $pages = new \yii\data\Pagination(['totalCount'=>$count]);
            $pages->setPageSize($numPerPage);
            $pages->setPage($intPage - 1);
            $cdb->limit($pages->getLimit());
            $cdb->offset($pages->getOffset());

            $arrRows = $cdb->all();
            
            $arrData = \common\components\VehicleModule::formatVehicleDatagridDataArray($arrRows, true, 0, 0, \common\models\Pro_vehicle_order::PRICE_TYPE_OFFICE);
        
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionAdd() {
        $processResult = \backend\components\VehicleService::processEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $arrData = [
            'action' => 'insert',
            'objVehicle' => null,
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/add']),
        ];
        
        return $this->renderPartial('edit', $arrData);
    }
    
    public function actionEdit() {
        $processResult = \backend\components\VehicleService::processEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $action = \Yii::$app->request->getParam('action');
        $intId = intval(\Yii::$app->request->getParam('id'));
        $objVehicle = ($intId ? \common\models\Pro_vehicle::findById($intId) : null);
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        $arrData = [
            'action' => (empty($action) ? ($objVehicle ? 'update' : 'insert') : $action),
            'objVehicle' => $objVehicle,
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/edit']),
        ];
        return $this->renderPartial('edit', $arrData);
    }
    
    public function actionDelete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_vehicle::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        // check orders
        $cdb2 = \common\models\Pro_vehicle_order::find(true);
        $cdb2->where(['vehicle_id' => $intID]);
        if ($cdb2->one()) {
            if (true) {
                MyFunction::funEchoJSON_Ajax(Yii::t('carrental', 'The car has order processed history, please do not delete the car!'), 300);
            }
            $cdb2->where(['vehicle_id' => $intID]);
            $cdb2->andWhere(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING]);
            if ($cdb2->one()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('carrental', 'The car has order processed history, please do not delete the car!'), 300);
            }
            else {
                $objData->status = \common\models\Pro_vehicle::STATUS_DELETED;
                $objData->save();
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
            }
        }
        else {
            $objData->delete();
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
        }
    }
    
    public function actionSetsailed() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $cdb = \common\models\Pro_vehicle::find();
        $cdb->where(['id' => $intID]);
        $objData = $cdb->one();

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }

        // check orders
        $cdb2 = \common\models\Pro_vehicle_order::find(true);
        $cdb2->where(['vehicle_id' => $intID]);
        $cdb2->andWhere(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);
        if ($cdb2->one()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('carrental', 'The car has order processing, please do not sale the car!'), 300);
        }
        $objData->status = \common\models\Pro_vehicle::STATUS_SAILED;
        $objData->save();
        
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionModel_index() {
        return $this->renderPartial('model_index');
    }
    
    public function actionModel_list() {
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
        
        $cdb = \common\models\Pro_vehicle_model::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $vehicleModel = \Yii::$app->request->getParam('vehicle_model');
        if (!empty($vehicleModel)) {
            $cdb->andWhere(['id'=>intval($vehicleModel)]);
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        
        $cdb2 = \common\models\Pro_vehicle_brand::find();
        $arrBrandRows = $cdb2->all();
        $arrBrands = [];
        foreach ($arrBrandRows as $row) {
            $arrBrands[$row->id] = $row->name;
        }
        $arrVehicleTypes = \common\models\Pro_vehicle_model::getTypesArray();
        $arrGearboxTypes = \common\components\VehicleModule::getVehicleGearboxTypesArray();
        $arrDrivingModes = \common\models\Pro_vehicle_model::getDrivingModesArray();
        $arrAirIntakeModes = \common\models\Pro_vehicle_model::getAirIntakeModesArray();
        $arrUserIds = [];
        $arrVehicleModelIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrUserIds[$row->edit_user_id])) {
                $arrUserIds[$row->edit_user_id] = 1;
            }
            $arrVehicleModelIds[] = $row->id;
        }
        $arrUsers = \backend\components\AdminModule::getUserNamesArray(array_keys($arrUserIds));
        $arrFeePlans = \common\components\VehicleModule::getFeePlanObjects($arrVehicleModelIds);
        
        $feeOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if ($feeOfficeId < 0) {
            $feeOfficeId = 0;
        }
        
        $now = time();
        
        $imageFields = ['image_0', 'image_a', 'image_b', 'image_c', 'image_d'];
        
        $arrData = [];

        // 燃油数组
        $oil_label_arr = \common\components\VehicleModule::getVehicleOilLabelsArray();


        foreach ($arrRows as $row) {
            $feeOnline = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_APP, $feeOfficeId, $row->id);
            $feeOffice = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE, $feeOfficeId, $row->id);
            $feeDefault = $feeOffice ? $feeOffice : null/* $feeOnline */;
            $o = $row->getAttributes();
            foreach ($imageFields as $_f) {
                $o[$_f] = \common\helpers\Utils::toFileUri($row->$_f);
            }

            $o['oil_label'] = $oil_label_arr[$row->oil_label];
            // fee infos
            $o['price_online'] = ($feeOnline ? $feeOnline->getDayPriceByTime($now) : 0);
            $o['price_office'] = ($feeOffice ? $feeOffice->getDayPriceByTime($now) : 0);
            $o['price_3days'] = ($feeDefault ? $feeDefault->price_3days : 0);
            $o['price_week'] = ($feeDefault ? $feeDefault->price_week : 0);
            $o['price_15days'] = ($feeDefault ? $feeDefault->price_15days : 0);
            $o['price_month'] = ($feeDefault ? $feeDefault->price_month : 0);

            $o['brand_disp'] = (isset($arrBrands[$row->brand]) ? $arrBrands[$row->brand] : '');
            $o['model_series_disp'] = (isset($arrBrands[$row->model_series]) ? $arrBrands[$row->model_series] : '');
            $o['vehicle_type_disp'] = (isset($arrVehicleTypes[$row->vehicle_type]) ? $arrVehicleTypes[$row->vehicle_type] : '');
            $o['vehicle_flag_disp'] = $row->vehicleFlagDisplayString();
            $o['emission_disp'] = $row->vehicleEmissionHumanText();
            $o['gearbox_disp'] = (isset($arrGearboxTypes[$row->gearbox]) ? $arrGearboxTypes[$row->gearbox] : '');
            $o['driving_mode_disp'] = (isset($arrDrivingModes[$row->driving_mode]) ? $arrDrivingModes[$row->driving_mode] : '');
            $o['air_intake_mode_disp'] = (isset($arrAirIntakeModes[$row->air_intake_mode]) ? $arrAirIntakeModes[$row->air_intake_mode] : '');
            $o['edit_user_disp'] = (isset($arrUsers[$row->edit_user_id]) ? $arrUsers[$row->edit_user_id] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionAddmodel() {
        $processResult = \backend\components\VehicleService::processModelEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $arrData = [
            'action' => 'insert',
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/addmodel']),
        ];
        return $this->renderPartial('editmodel', $arrData);
    }
    
    public function actionEditmodel() {
        $processResult = \backend\components\VehicleService::processModelEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $action = \Yii::$app->request->getParam('action');
        $intId = intval(\Yii::$app->request->getParam('id'));
        $objVehicleModel = ($intId ? \common\models\Pro_vehicle_model::findById($intId) : null);
        if (!$objVehicleModel) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        $arrData = [
            'action' => (empty($action) ? ($objVehicleModel ? 'update' : 'insert') : $action),
            'objVehicleModel' => $objVehicleModel,
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/editmodel']),
        ];
        return $this->renderPartial('editmodel', $arrData);
    }
    
    public function actionDeletemodel() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_vehicle_model::findById($intID);
        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        if (\common\models\Pro_vehicle::findOne(['model_id' => $intID])) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('carrental', 'There is still vehicel uses the vehicle model, please do not delete it.'), 300);
        }

        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionGetmodelnames() {
        $options = [];
        
        $brandId = intval(\Yii::$app->request->getParam('brand'));
        $seriesId = intval(\Yii::$app->request->getParam('series'));
        if ($brandId) {
            $options['brand'] = $brandId;
        }
        if ($seriesId) {
            $options['series'] = $seriesId;
        }
        
        if (intval(\Yii::$app->request->getParam('enableall'))) {
            $options['enableall'] = true;
        }
        elseif (intval(\Yii::$app->request->getParam('enablenone'))) {
            $options['enablenone'] = true;
        }
        if (intval(Yii::$app->request->getParam('enableadd'))) {
            $options['enableadd'] = true;
        }
        
        $arrData = \common\components\VehicleModule::getVehicleModelNamesWithPriceArray($options);
        
        return json_encode($arrData);
    }
    
    public function actionBrand_index() {
        return $this->renderPartial('brand_index');
    }
    
    public function actionBrand_list() {
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
        
        $cdb = \common\models\Pro_vehicle_brand::find();
        $cdb->select("*");
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $intShowModelSeries = intval(Yii::$app->request->getParam('display_model_series'));
        if (!$intShowModelSeries) {
            $cdb->where(["belong_brand" => 0]);
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        
        $arrBrands = [];
        foreach ($arrRows as $row) {
            if ($row->belong_brand == 0) {
                $arrBrands[$row->id] = $row->name;
            }
        }
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['name_x'] = ($row->belong_brand && isset($arrBrands[$row->belong_brand]) ? $arrBrands[$row->belong_brand] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionAddbrand() {
        $processResult = \backend\components\VehicleService::processBrandEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $belongBrandId = intval(Yii::$app->request->getParam('belong_id'));
        $arrData = [
            'action' => 'insert',
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/addbrand']),
            'belongBrand' => $belongBrandId,
        ];
        return $this->renderPartial('editbrand', $arrData);
    }
    
    public function actionEditbrand() {
        $processResult = \backend\components\VehicleService::processBrandEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $action = \Yii::$app->request->getParam('action');
        $intId = intval(\Yii::$app->request->getParam('id'));
        $objVehicleBrand = ($intId ? \common\models\Pro_vehicle_brand::findById($intId) : null);
        if (!$objVehicleBrand) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        $arrData = [
            'action' => (empty($action) ? ($objVehicleBrand ? 'update' : 'insert') : $action),
            'objVehicleModel' => $objVehicleBrand,
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/editbrand']),
        ];
        $arrData['objVehicleBrand'] = $objVehicleBrand;
        $arrData['belongBrand'] = $objVehicleBrand ? $objVehicleBrand->belong_brand : intval(Yii::$app->request->getParam('belong_id'));
        return $this->renderPartial('editbrand', $arrData);
    }
    
    public function actionDeletebrand() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objData = \common\models\Pro_vehicle_brand::findById($intID);
        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        $arrFindIds = [$intID];
        if ($objData->belong_brand == 0) {
            $arrRows = \common\models\Pro_vehicle_brand::find()->select(['id'])->where(['belong_brand' => $intID])->asArray()->all();
            foreach ($arrRows as $row) {
                $arrFindIds[] = intval($row['id']);
            }
        }
        if (\common\models\Pro_vehicle_model::find()->where(['or', ['brand' => $arrFindIds], ['model_series' => $arrFindIds]])->exists()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('carrental', 'There is still vehicle model uses the vehicle brand, please do not delete it.'), 300);
        }

        if ($objData->belong_brand == 0) {
            \common\models\Pro_vehicle_brand::deleteAll(["belong_brand" => $intID]);
        }
        
        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionGetvehiclebrands() {
        $arrBrands = \common\components\VehicleModule::getVehicleBrandsArray(false);
        $arrData = [];
        if (intval(\Yii::$app->request->getParam('enableall'))) {
            $arrData[] = ['id'=>0, 'text'=>  Yii::t('locale', 'All')];
        }
        elseif (intval(\Yii::$app->request->getParam('enablenone'))) {
            $arrData[] = ['id'=>0, 'text'=>  Yii::t('locale', 'None')];
        }
        
        foreach ($arrBrands as $k => $v) {
            $arrData[] = ['id'=>$k, 'text'=>$v];
        }
        
        if (intval(Yii::$app->request->getParam('enableadd'))) {
            $arrData[] = ['id'=>-128, 'text'=>  Yii::t('locale', 'Add').'...'];
        }
        
        echo json_encode($arrData);
    }
    
    public function actionGetvehiclesubbrands() {
        $brandId = intval(Yii::$app->request->getParam('brand'));
        $arrSubBrands = \common\components\VehicleModule::getSubVehicleBrandsArray($brandId, false);
        $arrData = [];
        if (intval(\Yii::$app->request->getParam('enableall'))) {
            $arrData[] = ['id'=>0, 'text'=>  Yii::t('locale', 'All')];
        }
        elseif (intval(\Yii::$app->request->getParam('enablenone'))) {
            $arrData[] = ['id'=>0, 'text'=>  Yii::t('locale', 'None')];
        }
        
        foreach ($arrSubBrands as $k => $v) {
            $arrData[] = ['id'=>$k, 'text'=>$v];
        }
        
        if (intval(Yii::$app->request->getParam('enableadd'))) {
            $arrData[] = ['id'=>-128, 'text'=>  Yii::t('locale', 'Add').'...'];
        }
        
        echo json_encode($arrData);
    }
    
    public function actionFeeplan_index() {
        return $this->renderPartial('feeplan_index');
    }
    
    public function actionFeeplan_list() {
        // get pagination
        $intPage = intval(Yii::$app->request->getParam('page'));
        if ($intPage == 0)
            $intPage = 1;
        $numPerPage = intval(Yii::$app->request->getParam('rows'));
        $numPerPage = intval($numPerPage);
        if (!$numPerPage)
            $numPerPage = $this->pageSize;
        
        // get order
        $sortField = strval(Yii::$app->request->getParam('sort'));
        $sortDirection = strval(Yii::$app->request->getParam('order'));
        $order = '';
        if (!empty($sortField) && !empty($sortDirection)) {
            $order .= $sortField . " " . $sortDirection;
        }
        if ($sortField != 'vehicle_model_id') {
            $order .= " vehicle_model_id ASC";
        }
        
        $cdb = \common\models\Pro_vehicle_fee_plan::find();
        $cdb->orderBy($order);
        
        // conditions
        $vehicleModel = \Yii::$app->request->getParam('vehicle_model');
        $feeOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if ($feeOfficeId > 0) {
            $cdb->andWhere(['office_id'=>[$feeOfficeId, 0]]);
        }
        elseif ($feeOfficeId == 0) {
            $cdb->andWhere(['office_id'=>$feeOfficeId]);
        }
        if (!empty($vehicleModel)) {
            $cdb->andWhere(['vehicle_model_id'=>intval($vehicleModel)]);
        }
        $cdb->groupBy(['vehicle_model_id', 'office_id']);
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();

        $arrUserIds = [];
        $arrOfficeIds = [];
        $arrVehicleModelIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrUserIds[$row->edit_user_id])) {
                $arrUserIds[$row->edit_user_id] = 1;
            }
            if (!isset($arrOfficeIds[$row->office_id])) {
                $arrOfficeIds[$row->office_id] = 1;
            }
            if (!isset($arrVehicleModelIds[$row->vehicle_model_id])) {
                $arrVehicleModelIds[$row->vehicle_model_id] = 1;
            }
        }
        $arrUsers = \backend\components\AdminModule::getUserNamesArray(array_keys($arrUserIds));
        $arrVehicleNames = \common\components\VehicleModule::getVehicleModelNamesArrayByIds(array_keys($arrVehicleModelIds));
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        $arrOfficeNames[0] = \Yii::t('locale', 'Universal');
        
        $feeTemplate = new \common\models\Pro_vehicle_fee_plan();
        $feeTemplate->setFestivalNames(\common\components\OptionsModule::getFestivalsArray());
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['edit_user_disp'] = (isset($arrUsers[$row->edit_user_id]) ? $arrUsers[$row->edit_user_id] : '');
            $o['vehicle_model_disp'] = (isset($arrVehicleNames[$row->vehicle_model_id]) ? $arrVehicleNames[$row->vehicle_model_id] : '');
            $o['office_disp'] = (isset($arrOfficeNames[$row->office_id]) ? $arrOfficeNames[$row->office_id] : '');
            
            foreach ($feeTemplate->festivalFieldsArray as $field => $festivalId) {
                $o[$field] = (isset($row->festivalPricesArray[$festivalId]) ? $row->festivalPricesArray[$festivalId] : '');
            }
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionAddfeeplan() {
        $processResult = \backend\components\VehicleService::processFeeplanEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $yiiRequester = \Yii::$app->request;
        $intId = intval($yiiRequester->getParam('id'));
        $objVehicleFeePlan = ($intId ? \common\models\Pro_vehicle_fee_plan::findById($intId) : null);
        $arrFeesBySources = [];
        if ($objVehicleFeePlan) {
            $cdb = \common\models\Pro_vehicle_fee_plan::find();
            $cdb->where(['vehicle_model_id'=>$objVehicleFeePlan->vehicle_model_id, 'office_id'=>$objVehicleFeePlan->office_id]);
            $arrRows = $cdb->all();
            foreach ($arrRows as $row) {
                $arrFeesBySources[$row->source] = $row;
            }
        }
        $arrData = [
            'action' => 'insert',
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/addfeeplan']),
            'arrFeesBySources' => $arrFeesBySources,
            'vehicleModelId' => ($objVehicleFeePlan ? $objVehicleFeePlan->vehicle_model_id : ''),
            'officeId' => ($objVehicleFeePlan ? $objVehicleFeePlan->office_id : ''),
        ];
        $addFeeId = intval($yiiRequester->getParam('add_office_by_fee_id'));
        if ($addFeeId) {
            $objVehicleFeePlan = \common\models\Pro_vehicle_fee_plan::findById($addFeeId);
            if ($objVehicleFeePlan) {
                $arrData['vehicleModelId'] = $objVehicleFeePlan->vehicle_model_id;
                $arrData['officeId'] = '';
            }
        }
        
        return $this->renderPartial('editfeeplan', $arrData);
    }
    
    public function actionEditfeeplan() {
        $processResult = \backend\components\VehicleService::processFeeplanEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $yiiRequester = \Yii::$app->request;
        $intId = intval($yiiRequester->getParam('id'));
        $objVehicleFeePlan = null;
        if ($intId) {
            $objVehicleFeePlan = \common\models\Pro_vehicle_fee_plan::findById($intId);
        }
        
        $arrFeesBySources = [];
        if ($objVehicleFeePlan) {
            $cdb = \common\models\Pro_vehicle_fee_plan::find();
            $cdb->where(['vehicle_model_id'=>$objVehicleFeePlan->vehicle_model_id, 'office_id'=>$objVehicleFeePlan->office_id]);
            $arrRows = $cdb->all();
            foreach ($arrRows as $row) {
                $arrFeesBySources[$row->source] = $row;
            }
        }
        
        $arrData = [
            'action' => 'save',
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/editfeeplan']),
            'arrFeesBySources' => $arrFeesBySources,
            'vehicleModelId' => ($objVehicleFeePlan ? $objVehicleFeePlan->vehicle_model_id : ''),
            'officeId' => ($objVehicleFeePlan ? $objVehicleFeePlan->office_id : ''),
        ];
        $addFeeId = intval($yiiRequester->getParam('add_office_by_fee_id'));
        if ($addFeeId) {
            $objVehicleFeePlan = \common\models\Pro_vehicle_fee_plan::findById($addFeeId);
            if ($objVehicleFeePlan) {
                $arrData['vehicleModelId'] = $objVehicleFeePlan->vehicle_model_id;
                $arrData['officeId'] = '';
            }
        }
        return $this->renderPartial('editfeeplan', $arrData);
    }
    
    public function actionDeletefeeplan() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_vehicle_fee_plan::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }

        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionRent_options() {
        return $this->renderPartial('rent_options');
    }
    
    public function actionRent_register() {
        $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
        $orderId = intval(\Yii::$app->request->getParam('order_id'));
        $arrData = [
            'vehicleId' => $vehicleId,
            'orderId' => $orderId,
        ];
        return $this->renderPartial('rent_register', $arrData);
    }
    
    public function actionSettings() {
        return $this->renderPartial('settings');
    }
    
    public function actionValidation_edit() {
        $action = \Yii::$app->request->getParam('action');
        $purpose = Yii::$app->request->getParam('purpose');
        $intID = intval(\Yii::$app->request->getParam('id'));
        
        $objValidation = null;
        $objFormData = new \backend\models\Form_pro_vehicle_validation_order();
        if (!$objFormData->load(Yii::$app->request->post())) {
            $errText = $objFormData->getErrorAsHtml();
            if ($errText) {
                Yii::error($errText, 'validation');
            }
            MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
        }
        
        if ($action == 'insert') {
            $objValidation = new \common\models\Pro_vehicle_validation_order();
        }
        elseif ($action == 'update') {
            if (!$intID) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
            }
            
            $objValidation = \common\models\Pro_vehicle_validation_order::findById($intID);
            if (!$objValidation) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('carrental', 'Vehicle validation info')]), 300);
            }
            
            if ($objFormData->vehicle_id != $objValidation->vehicle_id) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not match!', ['name'=>  Yii::t('locale', 'Vehicle')]), 300);
            }
            
        }
        else {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Unknown action!!'), 300);
        }
        
        $objFormData->save($objValidation);
        
        $objVehicle = \common\models\Pro_vehicle::findById($objFormData->vehicle_id);
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>  Yii::t('locale', 'Vehicle')]), 300);
        }
        
        $objOrder = null;
        if ($purpose == 'vehicle_dispatch' || $purpose == 'vehicle_validation') {
            $objOrder = \common\models\Pro_vehicle_order::findById($objValidation->order_id);
            if (!$objOrder) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>  Yii::t('locale', 'Order')]), 300);
            }
            
            if ($purpose == 'vehicle_dispatch') {
                if ($objOrder->status > \common\models\Pro_vehicle_order::STATUS_BOOKED) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} status({status}) not support this operation!', ['name'=>Yii::t('locale', 'Order'), 'status'=>$objOrder->getStatusText()]), 300);
                }
            }
            else if ($purpose == 'vehicle_validation') {
                if ($objOrder->status != \common\models\Pro_vehicle_order::STATUS_RENTING) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} status({status}) not support this operation!', ['name'=>Yii::t('locale', 'Order'), 'status'=>$objOrder->getStatusText()]), 300);
                }
            }
        }
        
        if ($objValidation->save()) {
            if ($action == 'insert') {
                $objOrigionValidation = \common\models\Pro_vehicle_validation_order::findById($objVehicle->validation_id);
                if ($objOrigionValidation) {
                    if ($objOrigionValidation->validated_at < $objValidation->validated_at) {
                        $objVehicle->validation_id = $objValidation->id;
                        $objVehicle->save();
                    }
                }
                else {
                    $objVehicle->validation_id = $objValidation->id;
                    $objVehicle->save();
                }
            }
            
            if ($objVehicle->cur_kilometers < $objValidation->mileage) {
                $objVehicle->cur_kilometers = $objValidation->mileage;
                $objVehicle->save();
            }
            
            if ($purpose == 'vehicle_dispatch') {
                $objOrder->validation_id_0 = $objValidation->id;
                if (false && $objOrder->paid_amount < $objOrder->price_rent) {
                    \Yii::warning("order:{$objOrder->serial} dispatch vehicle while the order paid:{$objOrder->paid_amount} not enough for total:{$objOrder->price_rent}", 'order');
                    MyFunction::funEchoJSON_Ajax(Yii::t('carrental', 'The order were not fully paid, so the vehicle cannot be dispathed.'), 300, '', '', 'refreshCurrentX', '');
                }
                $objOrder->car_dispatched_at = time();
                if (!$objOrder->confirmed_at) {
                    $objOrder->confirmed_at = $objOrder->car_dispatched_at;
                }
                $objOrder->status = \common\models\Pro_vehicle_order::STATUS_RENTING;
                $objOrder->save();
            }
            elseif ($purpose == 'vehicle_validation') {
                $objOrder->validation_id_1 = $objValidation->id;
                $objOrder->car_returned_at = time();
                $objOrder->status = \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING;
                $objOrder->save();
            }
            
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'closeNavTab', '');
        }
        else {
            $errText = $objValidation->getErrorAsHtml();
            MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
        }
    }
    
    public function actionValidation() {
        $intID = intval(\Yii::$app->request->getParam('id'));
        $purpose = \Yii::$app->request->getParam('purpose');
        $objOrder = null;
        $objVehicle = null;
        $arrOrderPurpose = ['vehicle_dispatch'=>1, 'vehicle_validation'=>1];
        if (isset($arrOrderPurpose[$purpose])) {
            $orderId = intval(\Yii::$app->request->getParam('order_id'));
            if (!$orderId) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')])]), 300);
            }
            $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
            if (!$objOrder) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Order')]), 300);
            }
            $objVehicle = \common\models\Pro_vehicle::findById($objOrder->vehicle_id);
        }
        else {
            $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
            if (!$vehicleId) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', 'Vehicle')]), 300);
            }
            $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
        }
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>  Yii::t('locale', 'Vehicle')]), 300);
        }
        
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
        if (!$objVehicleModel) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>  Yii::t('locale', 'Vehicle model')]), 300);
        }
        
        $arrColors = \common\components\VehicleModule::getVehicleColorsArray();
        $arrStatus = \common\components\VehicleModule::getVehicleStatusArray();
        
        $arrData = [];
        $arrData['vehiclePlateNo'] = $objVehicle->plate_number;
        $arrData['vehicleModelName'] = $objVehicleModel->vehicle_model;
        $arrData['vehicleColorText'] = isset($arrColors[$objVehicle->color]) ? $arrColors[$objVehicle->color] : '';
        $arrData['vehicleStatusText'] = isset($arrColors[$objVehicle->status]) ? $arrStatus[$objVehicle->status] : '';
        $arrData['vehicleMileage'] = $objVehicle->cur_kilometers;
        $arrData['vehicleId'] = $objVehicle->id;
        $arrData['orderId'] = ($objOrder ? $objOrder->id : 0);
        $arrData['purpose'] = $purpose;
        
        $objData = null;
        $objPrevValidation = null;
        if ($purpose == 'vehicle_dispatch') {
            if ($objOrder->validation_id_0) {
                $objData = \common\models\Pro_vehicle_validation_order::findById($objOrder->validation_id_0);
            }
            elseif ($objVehicle->validation_id) {
                $objData = \common\models\Pro_vehicle_validation_order::findById($objVehicle->validation_id);
            }
        }
        elseif ($purpose == 'vehicle_validation') {
            if ($objOrder->validation_id_0) {
                $objPrevValidation = \common\models\Pro_vehicle_validation_order::findById($objOrder->validation_id_0);
            }
            if (!$objPrevValidation && $objVehicle->validation_id) {
                \Yii::warning("open vehicle:{$objVehicle->id} validation view for validate vehicle when vehicle were returned by customer, while could not find vehicle dispatchment validation info by order:{$objOrder->id}.", 'order');
                $objPrevValidation = \common\models\Pro_vehicle_validation_order::findById($objVehicle->validation_id);
            }
        }
        elseif ($purpose == 'management_validation') {
            if ($objVehicle->validation_id) {
                $objPrevValidation = \common\models\Pro_vehicle_validation_order::findById($objVehicle->validation_id);
            }
        }
        $arrData['objPrevValidation'] = $objPrevValidation;
        
        if (!$objData && $intID) {
            $objData = \common\models\Pro_vehicle_validation_order::findById($intID);
        }
        
        if ($objData) {
            $arrData['action'] = 'update';
            $arrData['objData'] = $objData;
        }
        else {
            $arrData['action'] = 'insert';
            if ($objVehicle->validation_id) {
                $objData = \common\models\Pro_vehicle_validation_order::findById($objVehicle->validation_id);
            }
            $arrData['objData'] = $objData;
        }
        
        return $this->renderPartial('validation', $arrData);
    }
    
    public function actionViolation_info() {
        $purpose = \Yii::$app->request->getParam('purpose');
        $objOrder = null;
        $objVehicle = null;
        if ($purpose == 'order') {
            $orderId = intval(\Yii::$app->request->getParam('order_id'));
            if (!$orderId) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')])]), 300);
            }
            $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
            if (!$objOrder) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Order')]), 300);
            }
            $objVehicle = \common\models\Pro_vehicle::findById($objOrder->vehicle_id);
        }
        else {
            $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
            if (!$vehicleId) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
            }
            $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
        }
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>  Yii::t('locale', 'Vehicle')]), 300);
        }
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
        if (!$objVehicleModel) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>  Yii::t('locale', 'Vehicle model')]), 300);
        }
        
        $arrData = [
            'objVehicle' => $objVehicle,
            'vehicleId' => $objVehicle->id,
            'orderId' => ($objOrder ? $objOrder->id : 0),
            'vehicleModelName' => $objVehicleModel->vehicle_model,
            'objOrder' => $objOrder,
        ];
        
        return $this->renderPartial('violation_info', $arrData);
    }
    
    public function actionViolation_list() {
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
        
        $cdb = \common\models\Pro_vehicle_violation::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
        $orderId = intval(\Yii::$app->request->getParam('order_id'));
        $status = intval(\Yii::$app->request->getParam('status'));
        $cdb->where(['vehicle_id' => $vehicleId]);
        if (!empty($status)) {
            $cdb->andWhere(['status'=>$status]);
        }
        if (!empty($orderId)) {
            $cdb->andWhere(['order_id'=>$orderId]);
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
        $arrStatus = \common\components\VehicleModule::getVehicleViolationStatusArray();
        
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
    
    public function actionViolation_add() {
        $processResult = \backend\components\VehicleService::processViolationEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $arrData = [
            'action' => 'insert',
            'objViolation' => null,
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/violation_add']),
        ];
        return $this->renderPartial('violation_edit', $arrData);
    }
    
    public function actionViolation_edit() {
        $processResult = \backend\components\VehicleService::processViolationEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $action = \Yii::$app->request->getParam('action');
        $intId = intval(\Yii::$app->request->getParam('id'));
        $objViolation = ($intId ? \common\models\Pro_vehicle_violation::findById($intId) : null);
        if (!$objViolation) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        $arrData = [
            'action' => (empty($action) ? ($objViolation ? 'update' : 'insert') : $action),
            'objViolation' => $objViolation,
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/violation_edit']),
        ];
        return $this->renderPartial('violation_edit', $arrData);
    }
    
    public function actionViolation_delete() {
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_vehicle_violation::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }

        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionMaintenance_config_all() {
        return $this->renderPartial('maintenance_config_all');
    }
    
    public function actionMaintenance_config_title_list() {
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
        
        $cdb = \common\models\Pro_vehicle_maintenance_config::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        
        $arrUserIds = [];
        $arrBrandIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrUserIds[$row->edit_user_id])) {
                $arrUserIds[$row->edit_user_id] = 1;
            }
            if (!isset($arrBrandIds[$row->belong_brand])) {
                $arrBrandIds[$row->belong_brand] = 1;
            }
        }
        $arrUsers = \backend\components\AdminModule::getUserNamesArray(array_keys($arrUserIds));
        $arrBrands = \common\components\VehicleModule::getVehicleBrandNamesArrayByIds(array_keys($arrBrandIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['edit_user_disp'] = (isset($arrUsers[$row->edit_user_id]) ? $arrUsers[$row->edit_user_id] : '');
            $o['belong_brand_disp'] = (isset($arrBrands[$row->belong_brand]) ? $arrBrands[$row->belong_brand] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionMaintenance_config_index() {
        $getType = Yii::$app->request->getParam('gettype');
        $objMaintenanceConfig = null;
        $objVehicle = null;
        $objVehicleModel = null;
        $belongId = 0;
        if ($getType == 'vehicle') {
            $vehicleId = intval(\Yii::$app->request->getParam('id'));
            if (!$vehicleId) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', 'Vehicle')]), 300);
            }
            $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
            if (!$objVehicle) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Vehicle')]), 300);
            }
            $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
            if (!$objVehicleModel) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Vehicle model')]), 300);
            }
        }
        else {
            $intId = intval(\Yii::$app->request->getParam('id'));
            if (!$intId) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
            }
            $objMaintenanceConfig = \common\models\Pro_vehicle_maintenance_config::findById($intId);
            $belongId = $intId;
        }
        
        if ($objMaintenanceConfig) {
            $hideInfo = Yii::$app->request->getParam('hide_info');
            $hideTool = Yii::$app->request->getParam('hide_tool');
            $arrData = [
                'objMaintenanceConfig' => $objMaintenanceConfig,
                'belongId' => $belongId,
                'objVehicle' => $objVehicle,
                'objVehicleModel' => $objVehicleModel,
                'hideInfo' => (empty($hideInfo) ? false : true),
                'hideTool' => (empty($hideTool) ? false : true),
            ];

            return $this->renderPartial('maintenance_config_index', $arrData);
        }
        else {
            $arrData = [
                'objMaintenanceConfig' => $objMaintenanceConfig,
                'action' => 'insert',
                'brandId' => $objVehicleModel->brand,
                'objVehicle' => $objVehicle,
                'objVehicleModel' => $objVehicleModel,
            ];

            return $this->renderPartial('maintenance_config_edit', $arrData);
        }
    }
    
    public function actionMaintenance_config_xadd() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $objFormData = new \backend\models\Form_pro_vehicle_maintenance_config();
        if ($objFormData->load(Yii::$app->request->post())) {
            $objMaintenanceConfig = \common\models\Pro_vehicle_maintenance_config::findOne(['name' => $objFormData->name]);
            if (!$objMaintenanceConfig) {
                $objMaintenanceConfig = new \common\models\Pro_vehicle_maintenance_config();
                $objFormData->save($objMaintenanceConfig);

                if ($objMaintenanceConfig->save()) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrent', '');
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                }
            } else {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, this {name} already exists!', ['{name}'=>Yii::t('locale', 'brand')]), 300);
            }
        }
        else {
            $errText = $objFormData->getErrorAsHtml();
            MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
            exit();
        }
    }
        
    public function actionMaintenance_config_xedit() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        $objFormData = new \backend\models\Form_pro_vehicle_maintenance_config();
        if ($objFormData->load(Yii::$app->request->post())) {
            if (!$intID && $objFormData->id) {
                $intID = $objFormData->id;
            }
            if (!$intID) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
            }
            $objMaintenanceConfig = \common\models\Pro_vehicle_maintenance_config::findById($intID);
            if (!$objMaintenanceConfig) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
            } else {
                $objFormData->save($objMaintenanceConfig);

                if ($objMaintenanceConfig->save()) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrent', '');
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                }
            }
        }
        else {
            $errText = $objFormData->getErrorAsHtml();
            MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
        }
    }
    
    public function actionMaintenance_config_edit() {
        $action = \Yii::$app->request->getParam('action');
        $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
        if (!$vehicleId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', 'Vehicle')]), 300);
        }
        $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Vehicle')]), 300);
        }
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
        if (!$objVehicleModel) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Vehicle model')]), 300);
        }
        
        if ($action == 'insert') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $objFormData = new \backend\models\Form_pro_vehicle_maintenance_config();
            if ($objFormData->load(Yii::$app->request->post())) {
                $objMaintenanceConfig = \common\models\Pro_vehicle_maintenance_config::findOne(['name' => $objFormData->name]);
                if (!$objMaintenanceConfig) {
                    $objMaintenanceConfig = new \common\models\Pro_vehicle_maintenance_config();
                    $objFormData->save($objMaintenanceConfig);
                    
                    if ($objMaintenanceConfig->save()) {
                        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrentX', '');
                    } else {
                        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                    }
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, this {name} already exists!', ['{name}'=>Yii::t('locale', 'brand')]), 300);
                }
            }
            else {
                $errText = $objFormData->getErrorAsHtml();
                MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
                exit();
            }
        }
        elseif ($action == 'update') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $intID = intval(Yii::$app->request->getParam('id'));
            if (!$intID) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
            }
            $objMaintenanceConfig = \common\models\Pro_vehicle_maintenance_config::findById($intID);
            if (!$objMaintenanceConfig) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
            } else {
                $objFormData = new \backend\models\Form_pro_vehicle_brand();
                if ($objFormData->load(Yii::$app->request->post())) {
                    $objFormData->save($objMaintenanceConfig);

                    if ($objMaintenanceConfig->save()) {
                        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrentX', '');
                    } else {
                        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                    }
                }
                else {
                    $errText = $objFormData->getErrorAsHtml();
                    MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
                }
            }
        }
        
        $intID = intval(\Yii::$app->request->getParam('id'));
        $objMaintenanceConfig = null;
        if ($intID) {
            $objMaintenanceConfig = \common\models\Pro_vehicle_maintenance_config::findById($intID);
        }
        
        $arrData = [
            'objMaintenanceConfig' => $objMaintenanceConfig,
            'action' => ($objMaintenanceConfig ? 'update' : 'insert'),
            'brandId' => $objVehicleModel->brand,
            'vehicleId' => $vehicleId,
        ];
        return $this->renderPartial('maintenance_config_edit', $arrData);
    }
    
    public function actionMaintenance_config_delete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_vehicle_maintenance_config::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        \common\models\Pro_vehicle_maintenance_config_item::deleteAll(['belong_id'=>$intID]);
        
        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionChange_maintenance_config() {
        $action = \Yii::$app->request->getParam('action');
        if ($action == 'update') {
            $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
            $configId = intval(\Yii::$app->request->getParam('upkeep_config_id'));
            if (!$vehicleId) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', 'Vehicle')]), 300);
            }
            if (!$configId) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('carrental', 'Maintenance config')]), 300);
            }
            $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
            if (!$objVehicle) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Vehicle')]), 300);
            }
            $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
            if (!$objVehicleModel) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Vehicle model')]), 300);
            }
            $objMaintenanceConfig = \common\models\Pro_vehicle_maintenance_config::findById($configId);
            if (!$objMaintenanceConfig) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('carrental', 'Maintenance config')]), 300);
            }
            
            if (false) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrentX', '');
            }
            else {
                $errText = $objVehicle->getErrorAsHtml();
                MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
            }
        }
        
        $intID = intval(\Yii::$app->request->getParam('id'));
        $objMaintenanceConfig = null;
        if ($intID) {
            $objMaintenanceConfig = \common\models\Pro_vehicle_maintenance_config::findById($intID);
        }
        
        $arrData = [
            'objMaintenanceConfig' => $objMaintenanceConfig,
            'action' => ($objMaintenanceConfig ? 'update' : 'insert'),
            'brandId' => $objVehicleModel->brand,
            'vehicleId' => $vehicleId,
        ];
        return $this->renderPartial('maintenance_config_edit', $arrData);
    }
    
    public function actionMaintenance_config_item_list() {
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
        
        $cdb = \common\models\Pro_vehicle_maintenance_config_item::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $belongId = intval(\Yii::$app->request->getParam('belong_id'));
        $cdb->where(['belong_id'=>$belongId]);
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        
        $arrUserIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrUserIds[$row->edit_user_id])) {
                $arrUserIds[$row->edit_user_id] = 1;
            }
        }
        $arrUsers = \backend\components\AdminModule::getUserNamesArray(array_keys($arrUserIds));
        $arrTypes = \common\components\VehicleModule::getVehicleMaintenanceCheckPointTypesArray();
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['type_disp'] = (isset($arrTypes[$row->type]) ? $arrTypes[$row->type] : '');
            $o['edit_user_disp'] = (isset($arrUsers[$row->edit_user_id]) ? $arrUsers[$row->edit_user_id] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionMaintenance_edit() {
        $action = \Yii::$app->request->getParam('action');
        if (!empty($action)) {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $objFormData = new \backend\models\Form_pro_vehicle_maintenance_config_item();
            $belongId = intval(\Yii::$app->request->getParam('belong_id'));
            $objFormData->belong_id = $belongId;
            if (!$objFormData->load(Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
            }
            if (empty($objFormData->belong_id)) {
                
                if (!$belongId) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
                }
                $objMaintenanceConfig = \common\models\Pro_vehicle_maintenance_config::findById($belongId);
                if (!$objMaintenanceConfig) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('carrental', 'Maintenance config')]), 300);
                }

                $objFormData->belong_id = $belongId;
            }
            
            $objItem = null;
            if ($action == 'insert') {               
                $objItem = new \common\models\Pro_vehicle_maintenance_config_item();
                $objFormData->save($objItem);
            }
            else if ($action == 'update') {
                $itemId = intval(\Yii::$app->request->getParam('id'));
                if (!$itemId && $objFormData->id) {
                    $itemId = intval($objFormData->id);
                }
                $objItem = \common\models\Pro_vehicle_maintenance_config_item::findById($itemId);
                if (!$objItem) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
                }
                else {
                    $objFormData->save($objItem);
                }
            }
            else {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
            }
            
            if ($objItem->save()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrent', '');
            } else {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
            }
        }
        
    }
    
    public function actionMaintenance_delete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_vehicle_maintenance_config_item::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }

        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionExpenditure_index() {
        $type = intval(Yii::$app->request->getParam('type'));
        $vehicleId = intval(Yii::$app->request->getParam('id'));
        
        $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
        if (!$objVehicle) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Vehicle')]), 300);
        }
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
        if (!$objVehicleModel) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Vehicle model')]), 300);
        }
        
        $arrTypes = \common\components\VehicleModule::getVehicleExpenditureTypesArray();
        
        $arrData = [
            'type' => $type,
            'vehicleId' => $vehicleId,
            'objVehicle' => $objVehicle,
            'objVehicleModel' => $objVehicleModel,
            'typeName' => (isset($arrTypes[$type]) ? $arrTypes[$type] : ''),
        ];
        
        return $this->renderPartial('expenditure_index', $arrData);
    }
    
    public function actionExpenditure_list() {
        // get pagination
        $intPage = intval(Yii::$app->request->getParam('page'));
        if ($intPage == 0)
            $intPage = 1;
        $numPerPage = intval(Yii::$app->request->getParam('rows'));
        $numPerPage = intval($numPerPage);
        if (!$numPerPage)
            $numPerPage = $this->pageSize;
        
        // type
        $type = intval(Yii::$app->request->getParam('type'));
        
        // get order
        $intSort = strval(Yii::$app->request->getParam('sort'));
        $intSortDirection = strval(Yii::$app->request->getParam('order'));
        if (!empty($intSort) && !empty($intSortDirection)) {
            $order = $intSort . " " . $intSortDirection;
        }
        
        $cdb = null;
        $costTimeField = 'cost_time';
        $priceField = 'cost_price';
        if ($type == \common\models\Pro_vehicle_cost::TYPE_RENEWAL) {
            $cdb = \common\models\Pro_vehicle_insurance::find();
            $costTimeField = 'time';
            $priceField = 'price';
        }
        elseif ($type == \common\models\Pro_vehicle_cost::TYPE_DESIGNATING) {
            $cdb = \common\models\Pro_vehicle_designating_cost::find();
            $costTimeField = 'time';
            $priceField = 'driver_fee';
        }
        elseif ($type == \common\models\Pro_vehicle_cost::TYPE_OIL) {
            $cdb = \common\models\Pro_vehicle_oil_cost::find();
            $costTimeField = 'time';
            $priceField = 'amount';
        }
        else {
            $cdb = \common\models\Pro_vehicle_cost::find();
        }
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
        $getAll = intval(\Yii::$app->request->getParam('getall'));
        $cdb->where(['type'=>$type]);
        if ($vehicleId || !$getAll) {
            $cdb->andWhere(['vehicle_id'=>$vehicleId]);
        }
        else if ($getAll) {
            $plateNumber = \Yii::$app->request->getParam('plate_number');
            $startTime = \Yii::$app->request->getParam('start_time');
            $endTime = \Yii::$app->request->getParam('end_time');
            $priceMin = intval(\Yii::$app->request->getParam('price_min'));
            $priceMax = intval(\Yii::$app->request->getParam('price_max'));
            if (!empty($startTime)) {
                $startTime .= ' 00:00:00';
            }
            if (!empty($endTime)) {
                $endTime .= ' 23:59:59';
            }
            $startTime = \common\helpers\Utils::toTimestamp($startTime);
            $endTime = \common\helpers\Utils::toTimestamp($endTime);
            if (!empty($plateNumber)) {
                $objVehicle = \common\models\Pro_vehicle::findOne(['plate_number'=>$plateNumber]);
                $cdb->andWhere(['vehicle_id'=>($objVehicle ? $objVehicle->id : 0)]);
            }
            if ($startTime) {
                $cdb->andWhere(['>=', $costTimeField, $startTime]);
            }
            if ($endTime) {
                $cdb->andWhere(['<=', $costTimeField, $endTime]);
            }
            if ($priceMin) {
                $cdb->andWhere(['>=', $priceField, $priceMin]);
            }
            if ($priceMax) {
                $cdb->andWhere(['<=', $priceField, $priceMax]);
            }
            
            if ($type == \common\models\Pro_vehicle_cost::TYPE_RENEWAL) {
                $insuranceNo = \Yii::$app->request->getParam('insurance_no');
                if (!empty($insuranceNo)) {
                    $cdb->andWhere(['insurance_no'=>$insuranceNo]);
                }
            }
            else if ($type == \common\models\Pro_vehicle_cost::TYPE_OIL) {
                $oilTanker = \Yii::$app->request->getParam('oil_tanker');
                if (!empty($oilTanker)) {
                    $cdb->andWhere(['oil_tanker'=>$oilTanker]);
                }
            }
            else if ($type == \common\models\Pro_vehicle_cost::TYPE_DESIGNATING) {
                $driverName = \Yii::$app->request->getParam('driver');
                if (!empty($driverName)) {
                    $cdb->andWhere(['driver'=>$driverName]);
                }
            }
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        
        $arrUserIds = [];
        $arrVehicleIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrUserIds[$row->edit_user_id])) {
                $arrUserIds[$row->edit_user_id] = 1;
            }
            if (!isset($arrVehicleIds[$row->vehicle_id])) {
                $arrVehicleIds[$row->vehicle_id] = 1;
            }
        }
        $arrUsers = \backend\components\AdminModule::getUserNamesArray(array_keys($arrUserIds));
        $arrVehicles = \common\components\VehicleModule::getVehicleObjects(array_keys($arrVehicleIds));
        //$arrTypes = \common\components\VehicleModule::getVehicleExpenditureTypesArray();
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributeValues();
            if (isset($o['edit_user_id'])) {
                $o['edit_user_disp'] = (isset($arrUsers[$o['edit_user_id']]) ? $arrUsers[$o['edit_user_id']] : '');
            }
            if (isset($o['vehicle_id'])) {
                $o['plate_number'] = (isset($arrVehicles[$o['vehicle_id']]) ? $arrVehicles[$o['vehicle_id']]->plate_number : '');
            }
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionExpenditure_add() {
        $processResult = \backend\components\VehicleService::processExpenditureEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $type = intval(Yii::$app->request->getParam('type'));
        $arrData = [
            'action' => 'insert',
            'type' => $type,
            'objItem' => null,
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/expenditure_add']),
        ];
        
        return $this->renderPartial('expenditure_edit', $arrData);
    }
    
    public function actionExpenditure_edit() {
        $processResult = \backend\components\VehicleService::processExpenditureEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        // TODO
        $type = intval(Yii::$app->request->getParam('type'));
        $intId = intval(\Yii::$app->request->getParam('id'));
        $arrData = [
            'action' => 'update',
            'type' => $type,
            'objItem' => null,
            'saveUrl' => \yii\helpers\Url::to(['/vehicle/expenditure_edit']),
        ];
        
        return $this->renderPartial('expenditure_edit', $arrData);
    }
    
    public function actionExpenditure_delete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $type = intval(Yii::$app->request->getParam('type'));
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objData = null;
        if ($type == \common\models\Pro_vehicle_cost::TYPE_RENEWAL) {
            $objData = \common\models\Pro_vehicle_insurance::findById($intID);
        }
        elseif ($type == \common\models\Pro_vehicle_cost::TYPE_DESIGNATING) {
            $objData = \common\models\Pro_vehicle_designating_cost::findById($intID);
        }
        elseif ($type == \common\models\Pro_vehicle_cost::TYPE_OIL) {
            $objData = \common\models\Pro_vehicle_oil_cost::findById($intID);
        }
        else {
            $objData = \common\models\Pro_vehicle_cost::findById($intID);
        }

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        $objData->delete();
        
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionSearch_plates() {
        $plate = Yii::$app->request->getParam('plate');
        $query = \common\models\Pro_vehicle::find();
        $query->where(['status'=> \common\models\Pro_vehicle::STATUS_NORMAL]);
        $query->andWhere(['like', 'plate_number', $plate]);
        $query->limit(10);
        $rows = $query->asArray()->all();
        $arrData = [];
        foreach ($rows as $row) {
            $arrData[] = [
                'id' => $row['id'],
                'plate' => $row['plate_number'],
            ];
        }
        echo json_encode($arrData);
    }



    public function actionVehicle_id_office_change($value='')
    {
        $vehicle_id = \Yii::$app->request->getParam('vehicle_id');
        return $this->renderPartial('vehicle_office_change', [
            'vehicle_id' => $vehicle_id,
        ]);
    }
    public function actionVehicle_office_change_list($value='')
    {
        # code...
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

        $vehicle_id = \Yii::$app->request->getParam('vehicle_id');
        // $vehicle_id = 221;
        if (!empty($vehicle_id)) {
            $objVehicle = \common\models\Pro_vehicle::findById($vehicle_id, 'id');
        }

        if($objVehicle){
            $intSort = strval(Yii::$app->request->getParam('sort'));
            $intSortDirection = strval(Yii::$app->request->getParam('order'));
            if (!empty($intSort) && !empty($intSortDirection)) {
                $order = $intSort . " " . $intSortDirection;
            }

            $cdb = \common\models\Pro_vehicle_office_change::find();
            $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");

            // conditions
            $cdb->where(['vehicle_id'=>$vehicle_id]);

            // pagiation
            $count = $cdb->count();
            $pages = new \yii\data\Pagination(['totalCount'=>$count]);
            $pages->setPageSize($numPerPage);
            $pages->setPage($intPage - 1);
            $cdb->limit($pages->getLimit());
            $cdb->offset($pages->getOffset());

            $arrRows = $cdb->all();

            $cdbOffice = \common\models\Pro_office::find();
            $cdbOffice->where(['status'=>0]);
            $OfficeArray = $cdbOffice->select('id,fullname')->asArray()->all();
            foreach ($OfficeArray as $key => $value) {
                $offices[$value['id']]  = $value['fullname'];
            }
            // echo "<pre>";
            // print_r($offices);
            // echo "</pre>";die;
            foreach ($arrRows as $row) {
                $o = $row->getAttributes();
                $o['vehicle_id'] = $objVehicle->plate_number;
                $o['belong_office_id'] = $offices[$o['belong_office_id']];
                $o['new_belong_office_id'] = $offices[$o['new_belong_office_id']];
                $o['updated_at'] = date('Y-m-d H:i:s',$o['updated_at']);
                $o['created_at'] = date('Y-m-d H:i:s',$o['created_at']);

                $arrData[] = $o;
            }
            
        }
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        echo json_encode($arrListData);
    }

}

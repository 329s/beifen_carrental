<?php
namespace backend\controllers;

use Yii;
use common\helpers\MyFunction;

/**
 * Description of OptionsController
 *
 * @author kevin
 */
class OptionsController  extends \backend\components\AuthorityController
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
    
    public function actionUsualinfo() {
        return $this->renderPartial('usualinfo');
    }
    
    public function actionFestival_index() {
        return $this->renderPartial('festival_index');
    }
    
    public function actionFestival_list() {
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
        
        $cdb = \common\models\Pro_festival::find();
        $cdb->select("*");
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
        
        $arrAdminIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
        }
        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionFestival_add() {
        $processResult = \backend\components\OptionsService::processFestivalEdit();
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
            'saveUrl' => \yii\helpers\Url::to(['/options/festival_add']),
        ];
        
        return $this->renderPartial('festival_edit', $arrData);
    }
    
    public function actionFestival_edit() {
        $processResult = \backend\components\OptionsService::processFestivalEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $action = Yii::$app->request->getParam('action');
        $intId = intval(Yii::$app->request->getParam('id'));
        $objFesitval = ($intId ? \common\models\Pro_festival::findOne(['id' => $intId]) : null);
        
        $arrData = [
            'action' => (empty($action) ? ($objFesitval ? 'update' : 'insert') : $action),
            'objFesitval' => $objFesitval,
            'saveUrl' => \yii\helpers\Url::to(['/options/festival_edit']),
        ];
        return $this->renderPartial('festival_edit', $arrData);
    }
    
    public function actionVehicle_validation_options_index() {
        $belongId = intval(Yii::$app->request->getParam('belong_id'));
        $isChildren = intval(Yii::$app->request->getParam('is_children'));
        $intId = intval(Yii::$app->request->getParam('id'));
        $arrData = [
            'belongId' => $belongId,
            'isChildren' => $isChildren,
        ];
        if ($intId) {
            $cdb = \common\models\Pro_vehicle_validation_config::find();
            $cdb->where(['id'=>$intId]);
            $objData = $cdb->one();
            if ($objData) {
                $arrData['belongId'] = $objData->id;
                $arrData['isChildren'] = 1;
                $arrData['name'] = $objData->name;
            }
        }
        return $this->renderPartial('vehicle_validation_options_index', $arrData);
    }
    
    public function actionVehicle_validation_options_list() {
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
        
        $cdb = \common\models\Pro_vehicle_validation_config::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id asc");
        
        // conditions
        
        // pagiation
        //$count = $cdb->count();
        //$pages = new \yii\data\Pagination(['totalCount'=>$count]);
        //$pages->setPageSize($numPerPage);
        //$pages->setPage($intPage - 1);
        //$cdb->limit($pages->getLimit());
        //$cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        
        $arrAdminIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
        }
        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['value_flag_disp'] = implode("|", $row->getValueFlagNamesArray());
            $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
            
            if ($row->belong_id) {
                if (isset($arrData[$row->belong_id])) {
                    $arr = $arrData[$row->belong_id];
                    if (isset($arr['children'])) {
                        $arrData[$row->belong_id]['children'][] = $o;
                    }
                    else {
                        $arrData[$row->belong_id]['children'] = [$o];
                    }
                }
                else {
                    $arrData[$row->belong_id] = ['children'=>[$o]];
                }
            }
            else {
                $o['children'] = [];
                $arrData[$row->id] = $o;
            }
        }
        
        $arrListData = [];
        foreach ($arrData as $o) {
            $arrListData[] = $o;
        }
        
        //$arrListData = [
        //    'total' => intval($count),
        //    'rows' => $arrData,
        //];
        
        echo json_encode($arrListData);
    }
    
    public function actionVehicle_validation_options_add() {
        $processResult = \backend\components\OptionsService::processVehicleValidationOptionsEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $belongId = intval(Yii::$app->request->getParam('belong_id'));
        $arrData = [
            'action' => 'insert',
            'saveUrl' => \yii\helpers\Url::to(['/options/vehicle_validation_options_add']),
            'objData' => null,
            'belongId' => $belongId,
        ];
        if ($belongId) {
            $cdb = \common\models\Pro_vehicle_validation_config::find();
            $cdb->select('value_flag');
            $cdb->where(['id' => $belongId]);
            $objTmp = $cdb->one();
            if ($objTmp) {
                $arrData['parentValueFlag'] = $objTmp->value_flag;
            }
        }
        
        return $this->renderPartial('vehicle_validation_options_edit', $arrData);
    }
    
    public function actionVehicle_validation_options_edit() {
        $processResult = \backend\components\OptionsService::processVehicleValidationOptionsEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        $action = Yii::$app->request->getParam('action');
        $intId = intval(Yii::$app->request->getParam('id'));
        $belongId = intval(Yii::$app->request->getParam('belong_id'));
        $objData = ($intId ? \common\models\Pro_vehicle_validation_config::findOne(['id' => $intId]) : null);
        
        $arrData = [
            'action' => (empty($action) ? ($objData ? 'update' : 'insert') : $action),
            'saveUrl' => \yii\helpers\Url::to(['/options/vehicle_validation_options_edit']),
            'objData' => $objData,
            'belongId' => $belongId,
        ];
        if ($belongId) {
            $cdb = \common\models\Pro_vehicle_validation_config::find();
            $cdb->select('value_flag');
            $cdb->where(['id' => $belongId]);
            $objTmp = $cdb->one();
            if ($objTmp) {
                $arrData['parentValueFlag'] = $objTmp->value_flag;
            }
        }
        
        return $this->renderPartial('vehicle_validation_options_edit', $arrData);
    }
    
    public function actionVehicle_validation_options_delete() {
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_vehicle_validation_config::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        if ($objData->belong_id == 0) {
            \common\models\Pro_vehicle_validation_config::deleteAll(['belong_id' => $intID]);
        }
        
        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'reloadCurrent');
    }
    
    public function actionRent_contract_options() {
        $cdb = \common\models\Pro_rent_contract_options::find();
        
        $arrContractTypes = [
            \common\models\Pro_rent_contract_options::TYPE_BOOKING => \Yii::t('locale', '{name} setting', ['name'=> \Yii::t('carrental', 'Booking order')]),
            \common\models\Pro_rent_contract_options::TYPE_DISPATCHING => \Yii::t('locale', '{name} setting', ['name'=> \Yii::t('carrental', 'Dispatching order')]),
            \common\models\Pro_rent_contract_options::TYPE_SETTLEMENT => \Yii::t('locale', '{name} setting', ['name'=> \Yii::t('carrental', 'Settlement notice')]),
            \common\models\Pro_rent_contract_options::TYPE_VIOLATION => \Yii::t('locale', '{name} setting', ['name'=> \Yii::t('carrental', 'Violation order')]),
        ];
        
        $arrContracts = [];
        
        foreach ($arrContractTypes as $type => $name) {
            $obj = $cdb->where(['type'=>$type])->one();
            if (!$obj) {
                $obj = new \common\models\Pro_rent_contract_options();
                $obj->type = $type;
                $obj->name = $name;
                $obj->flag = \common\models\Pro_rent_contract_options::STATUS_ENABLED;
                $obj->edit_user_id = Yii::$app->user->id;
                
                $obj->save();
            }
            $arrContracts[$type] = $obj;
        }
        
        $objContractRenting = $cdb->where(['type'=>\common\models\Pro_rent_contract_options::TYPE_RENTIG])->one();
        if (!$objContractRenting) {
            $objContractRenting = new \common\models\Pro_rent_contract_options();
            $objContractRenting->type = \common\models\Pro_rent_contract_options::TYPE_RENTIG;
            $objContractRenting->name = \Yii::t('carrental', 'Vehicle renting contract');
            $objContractRenting->flag = \common\models\Pro_rent_contract_options::STATUS_ENABLED;
            $objContractRenting->edit_user_id = Yii::$app->user->id;
            $objContractRenting->save();
        }
        
        $arrData = [
            'arrContracts' => $arrContracts,
            'objContractRenting' => $objContractRenting,
        ];
        
        return $this->renderPartial('rent_contract_options', $arrData);
    }
    
    public function actionRent_contract_options_edit() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $objFormData = new \backend\models\Form_pro_rent_contract_options();
        if (!$objFormData->load(Yii::$app->request->post())) {
            $errText = $objFormData->getErrorAsHtml();
            MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
        }
        if (empty($objFormData->id)) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objContract = \common\models\Pro_rent_contract_options::findById($objFormData->id);
        if (!$objContract) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Object')]), 300);
        }
        
        $objFormData->save($objContract);
        if ($objContract->save()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', '', '');
        }
        else {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
        }
    }
    
    public function actionService_price_options() {
        $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        $arrServices = \common\models\Pro_service_price::findAllServicePrices($authOfficeId);
        
        $arrData = [
            'arrServices' => $arrServices,
        ];
        return $this->renderPartial('service_price_options', $arrData);
    }
    
    public function actionService_price_edit() {
        if (\backend\components\AdminModule::getCurRoleAuthoration() < \backend\models\Rbac_role::AUTHORITY_OFFICE_MANAGER) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $serviceId = intval(\Yii::$app->request->getParam('id'));
        $servicePrice = intval(\Yii::$app->request->getParam('price'));
        
        if (empty($serviceId)) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if ($authOfficeId < 0) {
            $authOfficeId = 0;
        }
        
        $objService = \common\models\Pro_service_price::findByIdAndOffice($serviceId, $authOfficeId);
        if (!$objService) {
            if ($authOfficeId > 0) {
                $baseService = \common\models\Pro_service_price::findByIdAndOffice($serviceId, 0);
                if ($baseService) {
                    $objService = new \common\models\Pro_service_price();
                    $attrs = $baseService->getAttributes();
                    foreach ($attrs as $k => $v) {
                        if ($k != '_id' && $k != 'edit_user_id' && $k != 'created_at' && $k != 'updated_at') {
                            $objService->$k = $v;
                        }
                    }
                    $objService->office_id = $authOfficeId;
                }
            }
        }
        if (!$objService) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Object')]), 300);
        }
        
        $objService->price = $servicePrice;
        
        if ($objService->save()) {
            MyFunction::funEchoJSON_Ajax('', 200, '', '', 'refreshCurrent', '');
        }
        else {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
        }
    }
    
    public function actionVehicle_options_index() {
        return $this->renderPartial('vehicle_options_index');
    }
    
    public function actionConfig_rent_edit() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $scope = 'ConfigRent';
        $params = Yii::$app->request->post();
        $formData = null;
        if (isset($params[$scope])) {
            $formData = $params[$scope];
        }
        if (empty($formData)) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
        }
        
        $intId = intval($formData['id']);
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objConfig = \common\models\Pro_config_rent::findById($intId);
        if (!$objConfig) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Object')]), 300);
        }
        
        if (isset($formData['int_value'])) {
            if (!preg_match('/^\d+\.?[0-9]{0,2}$/', $formData['int_value'])) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Invalid parameter!'), 300);
            }
            
            $objConfig->int_value = intval($formData['int_value']);
        }
        if (isset($formData['float_value'])) {
            if (!preg_match('/^\d+\.?[0-9]{0,2}$/', $formData['float_value'])) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Invalid parameter!'), 300);
            }
            
            $objConfig->float_value = floatval($formData['float_value']);
        }
        if (isset($formData['str_value'])) {
            $objConfig->str_value = $formData['str_value'];
        }
        if (isset($formData['flag'])) {
            $objConfig->flag = intval($formData['flag']);
        }
        if (isset($formData['status'])) {
            $objConfig->status = intval($formData['status']);
        }
        
        $objConfig->save();
    }
    
    public function actionConfig_sms_edit() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $scope = 'ConfigSMS';
        $params = Yii::$app->request->post();
        $formData = null;
        if (isset($params[$scope])) {
            $formData = $params[$scope];
        }
        if (empty($formData)) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
        }
        
        $intId = intval($formData['id']);
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objConfig = \common\models\Pro_config_sms::findById($intId);
        if (!$objConfig) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Object')]), 300);
        }
        
        if (isset($formData['send_interval'])) {
            if (!preg_match('/^\d+$/', $formData['send_interval'])) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Invalid parameter!'), 300);
            }
            
            $objConfig->send_interval = intval($formData['send_interval']);
        }
        if (isset($formData['send_flag'])) {
            if (!preg_match('/^\d+$/', $formData['send_flag'])) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Invalid parameter!'), 300);
            }
            
            $objConfig->send_flag = floatval($formData['send_flag']);
        }
        if (isset($formData['title'])) {
            $objConfig->title = $formData['title'];
        }
        if (isset($formData['content'])) {
            $objConfig->content = $formData['content'];
        }
        if (isset($formData['status'])) {
            $objConfig->status = intval($formData['status']);
        }
        
        $objConfig->save();
    }
    
    public function actionPreferential_combo_data() {
        $cdb = \common\models\Pro_preferential_info::find();
        $cdb->where(['status'=>  \common\models\Pro_preferential_info::STATUS_NORMAL]);
        $arrRows = $cdb->all();
        $arrData = [];
        foreach ($arrRows as $row) {
            $arrData[] = [
                'value' => $row->name,
                'text' => $row->name,
                'amount' => $row->amount,
                'type' => $row->process_type,
            ];
        }
        
        echo json_encode($arrData);
    }
    
    public function actionApp_initial_index() {
        return $this->renderPartial('app_initial_index');
    }
    
    public function actionApp_initial_list() {
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
        
        $cdb = \common\models\Pro_initial::find();
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
        
        $arrAdminIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
        }
        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionApp_initial_edit() {
        $action = Yii::$app->request->getParam('action');
        $intId = intval(Yii::$app->request->getParam('id'));
        $objInitial = null;
        if ($intId) {
            $objInitial = \common\models\Pro_initial::findOne(['id' => $intId]);
        }
        
        // 提交并入库
        if ($action == 'insert') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $objFormData = new \backend\models\Form_pro_initial();
            if ($objFormData->load(Yii::$app->request->post())) {
                $objInitial = \common\models\Pro_initial::findOne(['name' => $objFormData->name]);
                if (!$objInitial) {
                    $objInitial = new \common\models\Pro_initial();
                    $objFormData->save($objInitial);
                    
                    if ($objInitial->save()) {
                        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrentX', '');
                    } else {
                        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                    }
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, this {name} already exists!', ['name'=>Yii::t('locale', 'Item')]), 300);
                }
            }
            else {
                $errText = $objFormData->getErrorAsHtml();
                MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
            }
        }
        elseif ($action == 'update') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            if (!$objInitial) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
            } else {
                $objFormData = new \backend\models\Form_pro_initial();
                if ($objFormData->load(Yii::$app->request->post())) {
                    $objFormData->save($objInitial);

                    if ($objInitial->save()) {
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

        $arrData = [];
        if (empty($action)) {
            if ($objInitial) {
                $arrData['action'] = 'update';
            }
            else {
                $arrData['action'] = 'insert';
            }
        }
        $arrData['objInitial'] = $objInitial;
        return $this->renderPartial('app_initial_edit', $arrData);
    }
    
}
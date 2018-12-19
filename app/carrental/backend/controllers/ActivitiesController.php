<?php
namespace backend\controllers;

use Yii;
use common\helpers\MyFunction;

/**
 * Description of ActivitiesController
 *
 * @author kevin
 */
class ActivitiesController  extends \backend\components\AuthorityController
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
    
    public function actionImage_activities_index() {
        return $this->renderPartial('image_activities_index');
    }
    
    public function actionImage_activities_list() {
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
        
        $cdb = \common\models\Pro_activity_image::find();
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
    
    public function actionImage_activities_add() {
        $processResult = \backend\components\ActivitiesService::processImageActivityEdit();
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
            'saveUrl' => \yii\helpers\Url::to(['/activities/image_activities_add']),
        ];
        
        return $this->renderPartial('image_activities_edit', $arrData);
    }
    
    public function actionImage_activities_edit() {
        $processResult = \backend\components\ActivitiesService::processImageActivityEdit();
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
        $objActivityImage = ($intId ? \common\models\Pro_activity_image::findById($intId) : null);
        
        $arrData = [
            'action' => (empty($action) ? ($objActivityImage ? 'update' : 'insert') : $action),
            'objActivityImage' => $objActivityImage,
            'saveUrl' => \yii\helpers\Url::to(['/activities/image_activities_edit']),
        ];
        return $this->renderPartial('image_activities_edit', $arrData);
    }
    
    public function actionImage_activities_delete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $cdb = \common\models\Pro_activity_image::find();
        $cdb->where(['id' => $intID]);
        $objData = $cdb->one();

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }

        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionText_activities_index() {
        return $this->renderPartial('text_activities_index');
    }
    
    public function actionText_activities_list() {
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
        
        $cdb = \common\models\Pro_activity_info::find();
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
        $arrCityIds = [];
        $arrOfficeIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
            if (!isset($arrCityIds[$row->city_id])) {
                $arrCityIds[$row->city_id] = 1;
            }
            if (!isset($arrOfficeIds[$row->office_id])) {
                $arrOfficeIds[$row->office_id] = 1;
            }
        }
        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrCityNames = \common\components\CityModule::getCityNamesArray(array_keys($arrCityIds));
        $arrOffices = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
            $o['city_disp'] = (isset($arrCityNames[$row->city_id]) ? $arrCityNames[$row->city_id] : ($row->city_id == 0 ? \Yii::t('locale', 'All {name}', ['name'=>  \Yii::t('locale', 'cities')]) : ''));
            $o['office_disp'] = (isset($arrOffices[$row->office_id]) ? $arrOffices[$row->office_id] : ($row->office_id == 0 ? \Yii::t('locale', 'All {name}', ['name'=>  \Yii::t('locale', 'offices')]) : ''));
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionText_activities_add() {
        $processResult = \backend\components\ActivitiesService::processTextActivityEdit();
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
            'saveUrl' => \yii\helpers\Url::to(['/activities/text_activities_add']),
        ];
        return $this->renderPartial('text_activities_edit', $arrData);
    }
    
    public function actionText_activities_edit() {
        $processResult = \backend\components\ActivitiesService::processTextActivityEdit();
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
        $objActivityInfo = ($intId ? \common\models\Pro_activity_info::findById($intId) : null);
        
        $arrData = [
            'action' => (empty($action) ? ($objActivityInfo ? 'update' : 'insert') : $action),
            'objActivityInfo' => $objActivityInfo,
            'saveUrl' => \yii\helpers\Url::to(['/activities/text_activities_edit']),
        ];
        
        return $this->renderPartial('text_activities_edit', $arrData);
    }
    
    public function actionText_activities_delete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $cdb = \common\models\Pro_activity_info::find();
        $cdb->where(['id' => $intID]);
        $objData = $cdb->one();

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }

        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionGift_code_index() {
        return $this->renderPartial('gift_code_index');
    }
    
    public function actionGift_code_list() {
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
        
        $cdb = \common\models\Pro_gift_code::find();
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
        $arrUserIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
            if (!isset($arrUserIds[$row->customer_id])) {
                $arrUserIds[$row->customer_id] = 1;
            }
        }
        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrUsers = \common\components\UserModule::getUserInfoObjectsByUserIdArray(array_keys($arrUserIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
            $o['customer_disp'] = (isset($arrUsers[$row->customer_id]) ? $arrUsers[$row->customer_id]->name : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionGift_code_add() {
        
        $arrData = [];
        $arrData['action'] = 'insert';
        
        return $this->renderPartial('gift_code_edit', $arrData);
    }
    
    public function actionGift_code_edit() {
        $action = Yii::$app->request->getParam('action');
        $intId = intval(Yii::$app->request->getParam('id'));
        $objGiftCode = null;
        if ($intId) {
            $objGiftCode = \common\models\Pro_gift_code::findById($intId);
        }
        
        // 提交并入库
        if ($action == 'insert') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $objFormData = new \backend\models\Form_pro_gift_code();
            if ($objFormData->load(Yii::$app->request->post())) {
                $objGiftCode = \common\models\Pro_gift_code::findOne(['title' => $objFormData->title]);
                if (!$objGiftCode) {
                    $objGiftCode = new \common\models\Pro_gift_code();
                    $objFormData->save($objGiftCode);
                    
                    if ($objGiftCode->save()) {
                        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrentX', '');
                    } else {
                        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                    }
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, this {name} already exists!', ['name'=>Yii::t('locale', 'Preferential code')]), 300);
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

            if (!$objGiftCode) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
            } else {
                $objFormData = new \backend\models\Form_pro_gift_code();
                if ($objFormData->load(Yii::$app->request->post())) {
                    $objFormData->save($objGiftCode);

                    if ($objGiftCode->save()) {
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
            if ($objGiftCode) {
                $arrData['action'] = 'update';
            }
            else {
                $arrData['action'] = 'insert';
            }
        }
        $arrData['objGiftCode'] = $objGiftCode;
        return $this->renderPartial('gift_code_edit', $arrData);
    }
    
    public function actionGift_code_delete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $cdb = \common\models\Pro_gift_code::find();
        $cdb->where(['id' => $intID]);
        $objData = $cdb->one();

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }

        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionGift_code_batch_add() {
        $action = Yii::$app->request->getParam('action');
        $intId = intval(Yii::$app->request->getParam('id'));
        $objGiftCode = null;
        if ($intId) {
            $objGiftCode = \common\models\Pro_gift_code::findById($intId);
        }
        
        // 提交并入库
        if ($action == 'insert') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }
            
            $type = intval(\Yii::$app->request->getParam('type'));
            $size = intval(\Yii::$app->request->getParam('size'));
            $status = intval(\Yii::$app->request->getParam('status'));
            $amount = floatval(\Yii::$app->request->getParam('amount'));
            
            $arrTypes = \common\models\Pro_gift_code::getTypesArray();
            $arrStatus = \common\models\Pro_gift_code::getStatusArray();
            
            if (!isset($arrTypes[$type])) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Please select the correct preferential type!'), 300);
            }
            if (!isset($arrStatus[$status])) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
            }
            
            if ($size <= 0) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Please input the correct preferential count!'), 300);
            }
            elseif ($size > 10000) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Count too large!'), 300);
            }
            
            // insert
            $i = 0;
            $curTime = time();
            $adminId = \Yii::$app->user->id;
            $autoSN = \common\models\Pro_gift_code::autoSN();
            while($i < $size) {
                $obj = new \common\models\Pro_gift_code();
                $obj->sn = $autoSN;
                $obj->type = $type;
                $obj->status = $status;
                $obj->amount = $amount;
                $obj->edit_user_id = $adminId;
                if ($status == \common\models\Pro_gift_code::STATUS_NORMAL) {
                    $obj->activated_at = $curTime;
                }
                
                $obj->save();
                
                $autoSN++;
                $i++;
            }
            
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrentX', '');
        }
        
        $arrData = [
            'action' => 'insert',
        ];
        return $this->renderPartial('gift_code_batch_add', $arrData);
    }
    
}


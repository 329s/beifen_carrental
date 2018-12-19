<?php
namespace backend\controllers;

use Yii;
use common\helpers\MyFunction;

/**
 * Description of OfficeController
 *
 * @author kevin
 */
class OfficeController  extends \backend\components\AuthorityController
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
    
    public function actionOffice_list() {
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
        
        $intCityId = intval(\Yii::$app->request->getParam('city_id'));
        
        $cdb = \common\models\Pro_office::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        if ($intCityId) {
            $arrCityId = \common\components\CityModule::getSubCityIdsByCityId($intCityId);
            if (empty($arrCityId)) {
                $arrCityId[] = $intCityId;
            }
            $cdb->andWhere(['city_id' => $arrCityId]);
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        
        $arrOfficeNames = [];
        $arrOfficeIds = [];
        $arrCityIds = [];
        $arrAreaIds = [];
        $arrAdminIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrOfficeIds[$row->parent_id])) {
                $arrOfficeIds[$row->parent_id] = 1;
            }
            if (!isset($arrCityIds[$row->city_id])) {
                $arrCityIds[$row->city_id] = 1;
            }
            if (!isset($arrAreaIds[$row->area_id])) {
                $arrAreaIds[$row->area_id] = 1;
            }
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
            $arrOfficeNames[$row->id] = $row->shortname;
        }
        $tmpOfficeIdArr = [];
        foreach($arrOfficeIds as $officeId => $_) {
            if (!isset($arrOfficeNames[$officeId])) {
                $tmpOfficeIdArr[] = $officeId;
            }
        }
        if (!empty($tmpOfficeIdArr)) {
            $arr = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds($tmpOfficeIdArr);
            foreach ($arr as $id => $name) {
                $arrOfficeNames[$id] = $name;
            }
        }
        $arrCityNames = \common\components\CityModule::getCityNamesArray(array_keys($arrCityIds));
        $arrAreaNames = \common\components\CityModule::getCityAreaNamesArray(array_keys($arrAreaIds));
        $arrAdminNames = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            
            $o['city_disp'] = (isset($arrCityNames[$row->city_id]) ? $arrCityNames[$row->city_id] : '');
            $o['area_disp'] = (isset($arrAreaNames[$row->area_id]) ? $arrAreaNames[$row->area_id] : '');
            $o['parent_office'] = (isset($arrOfficeNames[$row->parent_id]) ? $arrOfficeNames[$row->parent_id] : '');
            $o['edit_user_disp'] = (isset($arrAdminNames[$row->edit_user_id]) ? $arrAdminNames[$row->edit_user_id] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionOffice_regiondata() {
        $arrData = \common\components\OfficeModule::getOfficeComboTreeData();
        echo json_encode($arrData);
    }
    
    public function actionAdd() {
        $processResult = \backend\components\OfficeService::processEdit();
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
            'saveUrl' => \yii\helpers\Url::to(['/office/add']),
        ];
        return $this->renderPartial('edit', $arrData);
    }
    
    public function actionEdit() {
        $processResult = \backend\components\OfficeService::processEdit();
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
        $objData = ($intId ? \common\models\Pro_office::findById($intId) : null);
        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        $arrData = [
            'action' => (empty($action) ? ($objData ? 'update' : 'insert') : $action),
            'objData' => $objData,
            'saveUrl' => \yii\helpers\Url::to(['/office/edit']),
        ];

        return $this->renderPartial('edit', $arrData);
    }
    
    public function actionDelete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intId = intval(Yii::$app->request->getParam('id'));
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }

        $objOffice = \common\models\Pro_office::findById($intId);
        if (!$objOffice) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
        }

        $objOffice->status = \common\models\Pro_office::STATUS_CLOSED;
        $objOffice->save();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, 'page100002', '', 'refreshCurrent', '');
    }
    
    public function actionUsercomments_index() {
        return $this->renderPartial('usercomments_index');
    }
    
    public function actionUsercomments_list() {
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
        
        $intOffice = intval(\Yii::$app->request->getParam('office_id'));
        
        $cdb = \common\models\Pro_office_comments::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        if ($intOffice) {
            $cdb->andWhere(['office_id' => $intOffice]);
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $o = $row->getAttributes();
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionUsercomments_process() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intId = intval(Yii::$app->request->getParam('id'));
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }

        $objData = \common\models\Pro_office_comments::findById($intId);
        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
        }

        $objData->status = \common\models\Pro_office_comments::STATUS_PROCESSED;
        $objData->save();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrent', '');
    }
    
    public function actionJoinapplying_index() {
        return $this->renderPartial('joinapplying_index');
    }
    
    public function actionJoinapplying_list() {
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
        
        //$intOffice = intval(\Yii::$app->request->getParam('office_id'));
        
        $cdb = \common\models\Pro_join_applying::find();
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
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionJoinapplying_process() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intId = intval(Yii::$app->request->getParam('id'));
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }

        $objData = \common\models\Pro_join_applying::findById($intId);
        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
        }

        $objData->status = \common\models\Pro_join_applying::STATUS_PROCESSED;
        $objData->save();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrent', '');
    }
    
}

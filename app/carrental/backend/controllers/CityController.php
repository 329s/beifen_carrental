<?php
namespace backend\controllers;

use Yii;
use common\helpers\MyFunction;

/**
 * Description of CityController
 *
 * @author kevin
 */
class CityController  extends \backend\components\AuthorityController
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
        $isHideToolBar = \common\helpers\Utils::boolvalue(\Yii::$app->request->getParam('hide_toolbar'));
        $cityId = intval(\Yii::$app->request->getParam('id'));
        
        $arrData = [];
        if ($cityId != 0) {
            $cdb = \common\models\Pro_city::find();
            $objCity = $cdb->where("`id`={$cityId}")->one();
            if ($objCity) {
                $arrData['isHideToolBar'] = $isHideToolBar;
                $arrData['cityId'] = $cityId;
                $arrData['cityName'] = $objCity->name;
            }
        }
        
        return $this->renderPartial('index', $arrData);
    }
    
    public function actionCity_list() {
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
        
        $intRegion = intval(Yii::$app->request->getParam('region'));
        
        $cdb = \common\models\Pro_city::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        $cdb->where(['type' => \common\models\Pro_city::TYPE_CITY]);
        
        // conditions
        if (!empty($intRegion)) {
            $cdb->andWhere(['belong_id' => $intRegion]);
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());
        
        $arrCityIds = [];
        $arrCities = [];
        $arrRows = $cdb->all();
        foreach ($arrRows as $row) {
            $arrCityIds[] = $row->id;
            $arrCities[$row->id] = ['city' => $row, 'children'=>[]];
        }
        if (!empty($arrCityIds)) {
            $arrRows = $cdb->where(['belong_id' => $arrCityIds])->all();
            foreach ($arrRows as $row) {
                if (isset($arrCities[$row->belong_id])) {
                    $arrCities[$row->belong_id]['children'][] = $row;
                }
            }
        }
        // echo "<pre>";
        // // print_r($arrCityIds);
        // print_r($arrCities);
        // echo "</pre>";die;
        
        $arrData = [];
        foreach ($arrCityIds as $cityId) {
            if (isset($arrCities[$cityId])) {
                $row = $arrCities[$cityId]['city'];
                $o = $row->getAttributeValues();
                
                if (!empty($arrCities[$cityId]['children'])) {
                    $o['children'] = [];
                    foreach ($arrCities[$cityId]['children'] as $row) {
                        $o['children'][] = $row->getAttributeValues();
                    }
                }
            }
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionAdd() {
        $processResult = \backend\components\CityService::processEdit();
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
            'cityType' => \Yii::$app->request->getParam('type'),
            'belongId' => \Yii::$app->request->getParam('belong_id'),
            'city' => null,
            'action' => 'insert',
            'saveUrl' => \yii\helpers\Url::to(['/city/add']),
        ];
        
        return $this->renderPartial('edit', $arrData);
    }
    
    public function actionEdit() {
        $processResult = \backend\components\CityService::processEdit();
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
        $objCity = ($intId ? \common\models\Pro_city::findById($intId) : null);
        $arrData = [
            'action' => (empty($action) ? ($objCity ? 'update' : 'insert') : $action),
            'cityType' => \Yii::$app->request->getParam('type'),
            'belongId' => \Yii::$app->request->getParam('belong_id'),
            'city' => $objCity,
            'saveUrl' => \yii\helpers\Url::to(['/city/edit']),
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

        $objData = \common\models\Pro_city::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }

        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionAlterstatus() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        $intStatus = intval(Yii::$app->request->getParam('status'));
        $boolSkipNotifyMessage = \common\helpers\Utils::boolvalue(Yii::$app->request->getParam('skip_notify_msg'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_city::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        $objData->status = $intStatus;
        $objData->save();
        if ($boolSkipNotifyMessage) {
            exit();
        }
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionProvince_index() {
        return $this->renderPartial('province_index');
    }
    
    public function actionIndex_area() {
        return $this->renderPartial('index_area');
    }
    
    public function actionCity_area_list() {
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
        
        $intRegion = intval(Yii::$app->request->getParam('region'));
        
        $cdb = \common\models\Pro_city_area::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        if (!empty($intRegion)) {
            $arrRows = \common\models\Pro_city::findAll(['type'=>\common\models\Pro_city::TYPE_CITY, 'belong_id'=>$intRegion]);
            $arrCityIds = [];
            foreach ($arrRows as $row) {
                $arrCityIds[] = $row->id;
            }
            if (empty($arrCityIds)) {
                $cdb->andWhere(['id'=>0]);
            }
            else {
                $cdb->andWhere(['city_id' => $arrCityIds]);
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
        
        $arrCityIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrCityIds[$row->city_id])) {
                $arrCityIds[$row->city_id] = 1;
            }
        }
        $arrCityNames = \common\components\CityModule::getCityNamesArray(array_keys($arrCityIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            
            $o['city_disp'] = (isset($arrCityNames[$row->city_id]) ? $arrCityNames[$row->city_id] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionAdd_area() {
        $processResult = \backend\components\CityService::processEditArea();
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
            'cityId' => \Yii::$app->request->getParam('city_id'),
            'action' => 'insert',
            'saveUrl' => \yii\helpers\Url::to(['/city/add_area']),
        ];
        
        return $this->renderPartial('edit_area', $arrData);
    }
    
    public function actionEdit_area() {
        $processResult = \backend\components\CityService::processEditArea();
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
        $objCityArea = ($intId ? \common\models\Pro_city_area::findById($intId) : null);
        $arrData = [
            'action' => (empty($action) ? ($objCityArea ? 'update' : 'insert') : $action),
            'objData' => $objCityArea,
            'saveUrl' => \yii\helpers\Url::to(['/city/edit_area']),
        ];

        return $this->renderPartial('edit_area', $arrData);
    }
    
    public function actionDelete_area() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_city_area::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }

        $objData->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200);
    }
    
    public function actionAlterstatus_area() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        $intStatus = intval(Yii::$app->request->getParam('status'));
        $boolSkipNotifyMessage = \common\helpers\Utils::boolvalue(Yii::$app->request->getParam('skip_notify_msg'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objData = \common\models\Pro_city_area::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        $objData->status = $intStatus;
        $objData->save();
        if ($boolSkipNotifyMessage) {
            exit();
        }
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200);
    }
    
}

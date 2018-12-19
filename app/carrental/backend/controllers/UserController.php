<?php
namespace backend\controllers;

use Yii;
use common\helpers\MyFunction;

/**
 * Description of UserController
 *
 * @author kevin
 */
class UserController  extends \backend\components\AuthorityController
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
    
    public function actionUser_list() {
        $params = \Yii::$app->request->getParams();
		
        $filterModel = new \backend\models\searchers\Searcher_pub_user_info();
        $filterModel->loadPagination($params);
        $filterModel->loadSort($params);
        $dataProvider = $filterModel->search($params, '');
        
        $dataProvider->manualFormatModelValues();
        $arrRows = $dataProvider->getModels();
        
        $arrMemberIds = [];
        $arrMemberCardInfos = [];
        foreach ($dataProvider->originModelDatas as $model) {
            if ($model['member_id'] && !isset($arrMemberIds[$model['member_id']])) {
                $arrMemberIds[$model['member_id']] = 1;
            }
        }
        $arrMemberCards = \common\components\UserModule::getMemberCardInfosArray(array_keys($arrMemberIds));
        foreach ($dataProvider->originModelDatas as $model) {
            $arrMemberCardInfos[$model['id']] = (isset($arrMemberCards[$model['member_id']])?$arrMemberCards[$model['member_id']] : null);
        }
        
        $arrData = [];
        foreach ($arrRows as $k => $row) {
            $o = $row->getAttributes();
            $o['member_card_amount'] = (isset($arrMemberCardInfos[$o['id']]) ? $arrMemberCardInfos[$o['id']]['amount'] : 0);
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($dataProvider->getTotalCount()),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionUserExport() {
        $params = \Yii::$app->request->getParams();
		if(empty($params['created_at'])){
			$params['created_at'] = date('Y-m').'-01';
		}
		
        $filterModel = new \backend\models\searchers\Searcher_pub_user_info();
        $filterModel->setPagerInfo(['pageSize'=>1000]);
        //$filterModel->loadPagination($params);
        //$filterModel->loadSort($params);
        $dataProvider = $filterModel->search($params, '');
        // $dataProvider->setModelsAsArray();
        $dataProvider->manualFormatModelValues();
        $arrRows = $dataProvider->getModels();
       
        $arrUserIds = [];
        $arrMemberIds = [];
        $arrMemberCardInfos = [];
        foreach ($dataProvider->originModelDatas as $model) {
            $arrUserIds[] = $model['id'];
            if ($model['member_id'] && !isset($arrMemberIds[$model['member_id']])) {
                $arrMemberIds[$model['member_id']] = 1;
            }
        }
        $arrMemberCards = \common\components\UserModule::getMemberCardInfosArray(array_keys($arrMemberIds));
        foreach ($dataProvider->originModelDatas as $model) {
            $arrMemberCardInfos[$model['id']] = (isset($arrMemberCards[$model['member_id']])?$arrMemberCardInfos[$model['member_id']] : null);
        }
        $queryOrder = \common\models\Pro_vehicle_order::find(true);
        $queryOrder->select(['user_id', 'start_time']);
        $queryOrder->where(['user_id'=>$arrUserIds]);
        $queryOrder->andWhere(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
        $queryOrder->orderBy(['start_time'=>SORT_ASC]);
        $queryOrder->groupBy('user_id');
        $arrOrderRows = $queryOrder->asArray()->all();
		
        $arrFirstRentTime = [];
        foreach ($arrOrderRows as $row) {
            $arrFirstRentTime[$row['user_id']] = empty($row['start_time'])?'':date('Y-m-d H:i:s', $row['start_time']);
        }
    
        $arrData = [];
        foreach ($arrRows as $k => $row) {
            $o = $row->getAttributes();
            $o['member_card_amount'] = (isset($arrMemberCardInfos[$o['id']]) ? $arrMemberCardInfos[$o['id']] : 0);
            $o['first_rent_time'] = (isset($arrFirstRentTime[$o['id']]) ? $arrFirstRentTime[$o['id']] : '');
            // if(isset($arrFirstRentTime[$o['id']])){
                $arrData[] = $o;

            // }
        }
	
        $model = new \common\models\Pub_user_info();
        $columns = [];
        $skipColumns = ['app_id'=>1, 'created_at'=>1, 'updated_at'=>1, 'finger_no'=>1, 'finger_info'=>1];
        foreach ($model->attributes() as $c) {
            if (!isset($skipColumns[$c])) {
                $columns[] = $c;
            }
        }
        array_push($columns, 'member_card_amount','first_rent_time');
        
        /*$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = ['memoryCacheSize' => '16MB'];
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);*/
        \moonland\phpexcel\Excel::export([
            'models' => $arrData,
            'columns' => $columns,
            'headers' => array_merge($model->attributeLabels(), ['first_rent_time'=>'首次租车时间']),
            'fileName' => '客户信息列表',
            'format' => 'Excel2007',
        ]);
    }
    
    public function actionAdd() {
        $processResult = \backend\components\CustomerService::processEdit();
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
            'objUserInfo' => null,
            'objMemberInfo' => null,
            'saveUrl' => \yii\helpers\Url::to(['/user/add']),
        ];
        return $this->renderPartial('edit', $arrData);
    }
    
    public function actionEdit() {
        $action = Yii::$app->request->getParam('action');
        $intId = intval(Yii::$app->request->getParam('id'));
        $objUserInfo = ($intId ? \common\models\Pub_user_info::findById($intId) : null);
        
        $processResult = \backend\components\CustomerService::processEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        
        $objMemberInfo = null;
        if ($objUserInfo) {
            $action = 'update';
            if ($objUserInfo->member_id) {
                $objMemberInfo = \common\models\Pro_member_card::findById($objUserInfo->member_id);
            }
        }
        $arrData = [
            'action' => empty($action) ? 'insert' : 'update',
            'objUserInfo' => $objUserInfo,
            'objMemberInfo' => $objMemberInfo,
            'saveUrl' => \yii\helpers\Url::to(['/user/edit']),
        ];
        return $this->renderPartial('edit', $arrData);
    }
    
    public function actionDelete() {
        $processResult = \backend\components\CustomerService::processDelete();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
    }
    
    public function actionBlacklistAdd() {
        $processResult = \backend\components\CustomerService::processEdit();
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
            'objUserInfo' => null,
            'objMemberInfo' => null,
            'saveUrl' => \yii\helpers\Url::to(['/user/blacklist-add']),
        ];
        return $this->renderPartial('edit', $arrData);
    }
    
    public function actionBlacklistEdit() {
        $action = Yii::$app->request->getParam('action');
        $intId = intval(Yii::$app->request->getParam('id'));
        $objUserInfo = ($intId ? \common\models\Pub_user_info::findById($intId) : null);
        
        $processResult = \backend\components\CustomerService::processEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        
        $objMemberInfo = null;
        if ($objUserInfo) {
            $action = 'update';
            if ($objUserInfo->member_id) {
                $objMemberInfo = \common\models\Pro_member_card::findById($objUserInfo->member_id);
            }
        }
        $arrData = [
            'action' => empty($action) ? 'insert' : 'update',
            'objUserInfo' => $objUserInfo,
            'objMemberInfo' => $objMemberInfo,
            'saveUrl' => \yii\helpers\Url::to(['/user/blacklist-edit']),
        ];
        return $this->renderPartial('edit', $arrData);
    }
    
    public function actionBlacklistDelete() {
        $processResult = \backend\components\CustomerService::processDelete();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
    }
    
    public function actionGetuserdetailview() {
        $intID = intval(Yii::$app->request->getParam('id'));
        $cdb = \common\models\Pub_user::find();
        $cdb->where(['id' => $intID]);
        $objUser = $cdb->one();
        $objUserInfo = null;
        if ($objUser) {
            $cdb = \common\models\Pub_user_info::find();
            $cdb->where(['id' => $objUser->info_id]);
            $objUserInfo = $cdb->one();
        }
        
        $arrData = [];
        if ($objUserInfo) {
            $arrVips = \common\components\UserModule::getVipLevelsArray();
            
            $arrData = [
                [
                    [$objUserInfo->getAttributeLabel('name'), $objUserInfo->name],
                    [$objUserInfo->getAttributeLabel('identity_id'), $objUserInfo->identity_id],
                    [$objUserInfo->getAttributeLabel('vip_level'), (isset($arrVips[$objUserInfo->vip_level]) ? $arrVips[$objUserInfo->vip_level] : '')],
                ],
                [
                    [$objUserInfo->getAttributeLabel('telephone'), $objUserInfo->telephone],
                    [$objUserInfo->getAttributeLabel('email'), $objUserInfo->email],
                ],
            ];
        }
        
        $htmlArray = [];
        $htmlArray[] = \common\helpers\CMyHtml::beginPanel('', ['height'=>'40px']);
        $htmlArray[] = \yii\helpers\Html::style(".dv-table td {border:0;} .dv-label {font-weight:bold; color:#15428B; width:100px; }", ['type'=>'text/css']);
        $htmlArray[] = \common\helpers\CMyHtml::beginTag('table', ['class'=>'dv-table', 'border'=>'0', 'style'=>'width:100%;']);
        $htmlArray[] = \common\helpers\CMyHtml::beginTag('tbody');
        
        foreach ($arrData as $row) {
            $htmlArray[] = \common\helpers\CMyHtml::beginTag('tr');
            foreach ($row as $ele) {
                $htmlArray[] = \common\helpers\CMyHtml::tag('td', $ele[0], ['class'=>'dv-label']);
                $htmlArray[] = \common\helpers\CMyHtml::tag('td', $ele[1]);
            }
            $htmlArray[] = \common\helpers\CMyHtml::endTag('tr');
        }
        
        $htmlArray[] = \common\helpers\CMyHtml::endTag('tbody');
        $htmlArray[] = \common\helpers\CMyHtml::endTag('table');
        $htmlArray[] = \common\helpers\CMyHtml::endPanel();
        
        echo implode("\n", $htmlArray);
    }
    
    public function actionSearchuserslike() {
        $name = Yii::$app->request->getParam('name');
        $arrData = [];
        
        if ($name) {
            $cdb = \common\models\Pub_user_info::find();
            $cdb->where(['like', 'name', $name]);
            $cdb->limit(10);
            $arrRows = $cdb->asArray()->all();
            
            $arrCreditLevelNames = \common\models\Pub_user_info::getCreditLevelsArray();
            
            foreach ($arrRows as $row) {
                $arrData[] = [
                    'id' => $row['id'],
                    'text' => $row['name'],
                    'user_id' => $row['id'],
                    'identity_type' => $row['identity_type'],
                    'identity_id' => $row['identity_id'],
                    'telephone' => $row['telephone'],
                    'fixedphone' => $row['fixedphone'],
                    'email' => $row['email'],
                    'vip_level' => $row['vip_level'],
                    'home_address' => $row['home_address'],
                    'post_code' => $row['post_code'],
                    'driver_license_time' => $row['driver_license_time'],
                    'driver_license_expire_time' => $row['driver_license_expire_time'],
                    'company_name' => $row['company_name'],
                    'company_address' => $row['company_address'],
                    'company_postcode' => $row['company_postcode'],
                    'company_telephone' => $row['company_telephone'],
                    'company_license' => $row['company_license'],
                    'emergency_contact' => $row['emergency_contact'],
                    'emergency_telephone' => $row['emergency_telephone'],
                    'credit_level' => $row['credit_level'],
                    'credit_level_disp' => (isset($arrCreditLevelNames[$row['credit_level']]) ? $arrCreditLevelNames[$row['credit_level']] : $row['credit_level']),
                    'blacklist_reason' => $row['blacklist_reason'],
                ];
            }
        }
        
        echo json_encode($arrData);
    }
    
    public function actionAccount_index() {
        $hideRealNameAccounts = intval(Yii::$app->request->getParam('hide_real_name'));
        return $this->renderPartial('account_index', [
            'hideRealNameAccounts' => $hideRealNameAccounts,
        ]);
    }
    
    public function actionAccount_list() {
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
            $order = $intSort . " " . $intSortDirection;
        }
        
        $cdb = \common\models\Pub_user::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $status = intval(\Yii::$app->request->getParam('status'));
        $account = \Yii::$app->request->getParam('account');
        $hideRealNameAccounts = intval(\Yii::$app->request->getParam('hide_real_name'));
        if (empty($status)) {
            $status = \common\models\Pub_user::STATUS_ACTIVE;
        }
        if ($status) {
            $cdb->andWhere(['status'=>$status]);
        }
        if ($account != '') {
            $cdb->andWhere('account LIKE :keywords1', [':keywords1' => '%'.$account.'%']);
        }
        if ($hideRealNameAccounts) {
            $cdb->andWhere(['info_id'=>0]);
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        
        $arrUserInfos = [];
        $arrAccounts = [];
        foreach ($arrRows as $row) {
            $arrAccounts[] = $row->account;
        }
        if (!empty($arrAccounts)) {
            $cdbUserInfo = \common\models\Pub_user_info::find();
            $cdbUserInfo->where(['telephone'=>$arrAccounts]);
            $arrUserInfoRows = $cdbUserInfo->all();
            foreach ($arrUserInfoRows as $row) {
                $arrUserInfos[$row->telephone] = 1;
            }
        }
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['has_user_info'] = isset($arrUserInfos[$row->account]) ? 1 : 0;
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionAccount_realname_match() {
        $intID = intval(\Yii::$app->request->getParam('id'));
        $action = \Yii::$app->request->getParam('action');
        $objAccount = null;
        if ($intID) {
            $objAccount = \common\models\Pub_user::findIdentity($intID);
        }
        if (!empty($action)) {
            if (!$intID) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
            }
            if (!$objAccount) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
            }
            $infoId = intval(\Yii::$app->request->getParam('info_id'));
            if (!$infoId) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
            }
            
            $objUserInfo = \common\models\Pub_user_info::findById($infoId);
            if (!$objUserInfo) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
            }
            
            $objAccount->info_id = $infoId;
            $objAccount->save();
            
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrentX', '');
        }
        
        if (!$objAccount) {
            echo \yii\helpers\Html::tag('div', Yii::t('locale', 'ID should not be empty!'), ['class'=>'alert alert-danger']);
            return;
        }
        
        $arrUserInfos = [];
        $cdb = \common\models\Pub_user_info::find();
        $cdb->where(['telephone' => $objAccount->account]);
        $arrUserInfoRows = $cdb->all();
        foreach ($arrUserInfoRows as $row) {
            $arrUserInfos[$row->id] = $row->name.':ID('.$row->identity_id.') TEL('.$row->telephone.')';
        }
        
        if (empty($arrUserInfos)) {
            echo \yii\helpers\Html::tag('div', Yii::t('carrental', 'No real name authentication information'), ['class'=>'alert alert-danger']);
            return;
        }
        
        return $this->renderPartial('account_realname_match', [
            'accountId' => $intID,
            'arrUserInfos' => $arrUserInfos,
        ]);
    }
    
    public function actionConsult_index() {
        return $this->renderPartial('consult_index');
    }
    
    public function actionConsult_list() {
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
        
        $cdb = \backend\models\Pro_user_consult::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $officeId = intval(\Yii::$app->request->getParam('office_id'));
        $status = intval(\Yii::$app->request->getParam('status'));
        if ($officeId) {
            $cdb->andWhere(['office_id' => $officeId]);
        }
        else {
            $officeId = \backend\components\AdminModule::getAuthorizedOfficeId();
            if ($officeId != \common\components\OfficeModule::HEAD_OFFICE_ID) {
                $cdb->andWhere(['office_id'=>$officeId]);
            }
        }
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
        
        $arrOfficeIds = [];
        $arrAdminIds = [];
        foreach ($arrRows as $row) {
            if (!isset($arrAdminIds[$row->edit_user_id])) {
                $arrAdminIds[$row->edit_user_id] = 1;
            }
            if (!isset($arrOfficeIds[$row->office_id])) {
                $arrOfficeIds[$row->office_id] = 1;
            }
        }
        
        $arrAdmins = \backend\components\AdminModule::getUserNamesArray(array_keys($arrAdminIds));
        $arrStatus = \backend\models\Pro_user_consult::getStatusArray();
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['edit_user_disp'] = (isset($arrAdmins[$row->edit_user_id]) ? $arrAdmins[$row->edit_user_id] : '');
            $o['status_disp'] = (isset($arrStatus[$row->status]) ? $arrStatus[$row->status] : '');
            $o['office_disp'] = (isset($arrOfficeNames[$row->office_id]) ? $arrOfficeNames[$row->office_id] : ($row->office_id == \common\components\OfficeModule::HEAD_OFFICE_ID ? \Yii::t('locale', 'Head office') : ''));
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionConsult_add() {
        $processResult = \backend\components\CustomerService::processConsultEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        
        return $this->renderPartial('consult_edit', ['saveUrl'=>\yii\helpers\Url::to(['/user/consult_add'])]);
    }
    
    public function actionConsult_edit() {
        $processResult = \backend\components\CustomerService::processConsultEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        
        return $this->renderPartial('consult_edit', ['saveUrl'=>\yii\helpers\Url::to(['/user/consult_edit'])]);
    }
    
    public function actionCunsolt_delete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $objItem = \backend\models\Pro_user_consult::findById($intID);

        if (!$objItem) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        $objItem->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionSms_index() {
        $arrData = [
            'type' => \common\models\Pub_user_sms::TYPE_SENT,
        ];
        return $this->renderPartial('sms_index', $arrData);
    }
    
    public function actionSmsrecv_index() {
        $arrData = [
            'type' => \common\models\Pub_user_sms::TYPE_RECEIVED,
        ];
        return $this->renderPartial('sms_index', $arrData);
    }
    
    public function actionSms_list() {
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
        
        $cdb = \common\models\Pub_user_sms::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $type = intval(\Yii::$app->request->getParam('type'));
        $customerId = intval(\Yii::$app->request->getParam('customer_id'));
        $status = intval(\Yii::$app->request->getParam('status'));
        
        $cdb->where(['type' => $type]);
        if ($customerId) {
            $cdb->andWhere(['customer_id' => $customerId]);
        }
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
        $arrStatus = \common\models\Pub_user_sms::getStatusArray();
        
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
    
    public function actionSms_add() {
        $processResult = \backend\components\CustomerService::processSmsEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        
        return $this->renderPartial('sms_edit', ['saveUrl'=>\yii\helpers\Url::to(['/user/sms_add'])]);
    }
    
    public function actionSms_edit() {
        $processResult = \backend\components\CustomerService::processSmsEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        
        return $this->renderPartial('sms_edit', ['saveUrl'=>\yii\helpers\Url::to(['/user/sms_edit'])]);
    }
    
    public function actionSms_delete() {
        $processResult = \backend\components\CustomerService::processSmsDelete();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
    }
    
    public function actionSmsrecv_add() {
        $processResult = \backend\components\CustomerService::processSmsEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        
        return $this->renderPartial('sms_edit', ['saveUrl'=>\yii\helpers\Url::to(['/user/smsrecv_add'])]);
    }
    
    public function actionSmsrecv_edit() {
        $processResult = \backend\components\CustomerService::processSmsEdit();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
        
        return $this->renderPartial('sms_edit', ['saveUrl'=>\yii\helpers\Url::to(['/user/smsrecv_edit'])]);
    }
    
    public function actionSmsrecv_delete() {
        $processResult = \backend\components\CustomerService::processSmsDelete();
        if ($processResult[0] != \backend\components\Consts::CODE_NOACTION) {
            \common\widgets\JsonResultWidget::widget([
                'code'=>$processResult[0] == \backend\components\Consts::CODE_OK ? 200 : 300,
                'message' => $processResult[1],
                'callbackType' => \yii\helpers\ArrayHelper::getValue($processResult, 'callbackType', ''),
                'forwardUrl' => \yii\helpers\ArrayHelper::getValue($processResult, 'forwardUrl', ''),
                'navTabId' => \yii\helpers\ArrayHelper::getValue($processResult, 'navTabId', ''),
            ]);
        }
    }
    
    public function actionOptions() {
        return $this->renderPartial('options');
    }
    
    public function actionBlacklist_index() {
        return $this->renderPartial('blacklist_index');
    }
    
    public function actionMembercard_index() {
        return $this->renderPartial('membercard_index');
    }
    
    public function actionMembercard_list() {
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
        
        $cdb = \common\models\Pro_member_card::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        $type = intval(\Yii::$app->request->getParam('type'));
        $status = intval(\Yii::$app->request->getParam('status'));
        
        if ($type) {
            $cdb->andWhere(['type' => $type]);
        }
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
    
    public function actionMembercard_add() {
        $processResult = \backend\components\CustomerService::processMembercardEdit();
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
            'saveUrl' => \yii\helpers\Url::to(['/user/membercard_add']),
            'objMemberCard' => null,
        ];
        return $this->renderPartial('membercard_edit', $arrData);
    }
    
    public function actionMembercard_edit() {
        $processResult = \backend\components\CustomerService::processMembercardEdit();
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
        $objMemberCard = ($intId ? \common\models\Pro_member_card::findById($intId) : null);
        if (!$objMemberCard) {
            MyFunction::funEchoJSON_Ajax(\Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        $arrData = [
            'action' => (empty($action) ? ($objMemberCard ? 'update' : 'insert') : $action),
            'saveUrl' => \yii\helpers\Url::to(['/user/membercard_edit']),
            'objMemberCard' => $objMemberCard,
        ];
        return $this->renderPartial('membercard_edit', $arrData);
    }
    
    public function actionMembercard_delete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        
        $objData = \common\models\Pro_member_card::findById($intID);

        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        $objData->status = \common\models\Pro_member_card::STATUS_DISABLED;
        $objData->save();
        
        //\common\models\Pub_user_info::updateAll(['member_id'=>0], ['member_id'=>$intID]);
        
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200, '', '', 'refreshCurrent');
    }
    
    public function actionFeedback_index() {
        return $this->renderPartial('feedback_index');
    }
    
    public function actionFeedback_list() {
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
        
        $cdb = \common\models\Pro_feedback::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        //if ($intOffice) {
        //    $cdb->andWhere(['office_id' => $intOffice]);
        //}
        
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
            if (!isset($arrUserIds[$row->user_id])) {
                $arrUserIds[$row->user_id] = 1;
            }
        }
        $arrUserObjects = \common\components\UserModule::getUserInfoObjectsByUserIdArray(array_keys($arrUserIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['customer_name'] = isset($arrUserObjects[$row->user_id]) ? $arrUserObjects[$row->user_id]->name : '';
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionFeedback_process() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intId = intval(Yii::$app->request->getParam('id'));
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }

        $objData = \common\models\Pro_feedback::findById($intId);
        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
        }

        $objData->status = \common\models\Pro_feedback::STATUS_PROCESSED;
        $objData->save();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrent', '');
    }
    
    public function actionLongrentapplying_index() {
        return $this->renderPartial('longrentapplying_index');
    }
    
    public function actionLongrentapplying_list() {
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
        
        $intOffice = intval(\Yii::$app->request->getParam('office_id_take_car'));
        
        $cdb = \common\models\Pro_long_rent_applying::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        // conditions
        if ($intOffice) {
            $cdb->andWhere(['office_id_take_car' => $intOffice]);
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
            $o = $row->getAttributes();
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionLongrentapplying_process() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intId = intval(Yii::$app->request->getParam('id'));
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }

        $objData = \common\models\Pro_long_rent_applying::findById($intId);
        if (!$objData) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
        }

        $objData->status = \common\models\Pro_long_rent_applying::STATUS_PROCESSED;
        $objData->save();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrent', '');
    }
    
    public function actionBook_vehicle() {
        $userId = intval(Yii::$app->request->getParam('id'));
        
        $arrData = [
            'userId' => $userId,
        ];
        
        return $this->renderPartial('book_vehicle', $arrData);
    }


    /*会员申请相关信息*/
    public function actionRelated_apply(){
    $type = empty(Yii::$app->request->getParam('type')) ? 1 : intval(Yii::$app->request->getParam('type'));
    $arrData = [
        'type' => $type,
    ];
    return $this->renderPartial('related_apply', $arrData);
}

public function actionBuy_car(){
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
    
    $cdb = \common\models\Pro_buy_car::find();
    $cdb->orderBy((isset($order) && !empty($order)) ? $order : "add_time desc");
    
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

public function actionPrint(){
    $intPage = intval(Yii::$app->request->getParam('page'));
    $status = \Yii::$app->request->getParam('status');
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

    $startTime = strtotime($date_start.'00:00:00');
    $endTime = strtotime($date.'23:59:59');


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
    
    $cdb = \common\models\Pro_sign_up::find();
    $cdb->where(['status' => 0]);
    $cdb->andWhere(['and', ['>=', 'created_at', $startTime], ['<=', 'created_at', $endTime]]);
    $cdb->orderBy("created_at desc");
    
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
    $getStatusArray = \common\models\Pro_sign_up::getStatusArray();
    $getSexArray = \common\models\Pro_sign_up::getSexArray();
    $getWayArray = \common\models\Pro_sign_up::getWayArray();
    $getSourceArray = \common\models\Pro_sign_up::getSourceArray();
    foreach ($arrRows as $row) {
        $o = $row->getAttributes();
        $o['status'] = $getStatusArray[$o['status']];
        $o['sex'] = $getSexArray[$o['sex']];
        $o['way'] = $getWayArray[$o['way']];
        $o['source'] = $getSourceArray[$o['source']];
        $o['created_at'] = date('Y-m-d H:i:s',$o['created_at']);
        $arrData[] = $o;
    }
    
    $arrListData = [
        'total' => intval($count),
        'rows' => $arrData,
    ];


    return $this->renderPartial('print', [
            'columns'=>['id','name','phone','status','city','sex','way','source','remark','created_at'],
            'models'=>$arrData,
            'date'=>$date,
            'date_start'=>$date_start,
            // 'status'=>$status,

        ]);
}
public function actionPrintExport(){
    $intPage = intval(Yii::$app->request->getParam('page'));
    if ($intPage == 0)
        $intPage = 1;
    $numPerPage = intval(Yii::$app->request->getParam('rows'));
    $numPerPage = intval($numPerPage);
    if (!$numPerPage)
        $numPerPage = $this->pageSize;
    

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

    $startTime = strtotime($date_start.'00:00:00');
    $endTime = strtotime($date.'23:59:59');


    // get order
    $intSort = strval(Yii::$app->request->getParam('sort'));
    $intSortDirection = strval(Yii::$app->request->getParam('order'));
    if (!empty($intSort) && !empty($intSortDirection)) {
        $order = $intSort . " " . $intSortDirection;
    }
    
    //$intOffice = intval(\Yii::$app->request->getParam('office_id'));
    
    $cdb = \common\models\Pro_sign_up::find();
    $cdb->where(['status' => 0]);
    $cdb->andWhere(['and', ['>=', 'created_at', $startTime], ['<=', 'created_at', $endTime]]);
    $cdb->orderBy("created_at desc");
    
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
    $getStatusArray = \common\models\Pro_sign_up::getStatusArray();
    $getSexArray = \common\models\Pro_sign_up::getSexArray();
    $getWayArray = \common\models\Pro_sign_up::getWayArray();
    $getSourceArray = \common\models\Pro_sign_up::getSourceArray();
    foreach ($arrRows as $row) {
        $o = $row->getAttributes();
        $o['status'] = $getStatusArray[$o['status']];
        $o['sex'] = $getSexArray[$o['sex']];
        $o['way'] = $getWayArray[$o['way']];
        $o['source'] = $getSourceArray[$o['source']];
        $o['created_at'] = date('Y-m-d H:i:s',$o['created_at']);
        $arrData[] = $o;
    }
    
    $arrListData = [
        'total' => intval($count),
        'rows' => $arrData,
    ];
    $model = new \common\models\Pro_sign_up();

    \moonland\phpexcel\Excel::export([
            'models' => $arrData,
            'columns' => ['id','name','phone','status','city','sex','way','source','remark','created_at'],
            'headers' => $model->attributeLabels(),
            'fileName' => \Yii::t('locale', '{type} order list', ['type'=>'意向客户']),
            'format' => 'Excel2007',
        ]);


}
public function actionSign_up_update(){
    $intId = intval(Yii::$app->request->getParam('id'));
    if (!$intId) {
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
    }
    $obj = \common\models\Pro_sign_up::find();
    $objsign = $obj->where(['id'=>$intId])->one();
        // $objsign = new \common\models\Pro_sign_up();
        $objsign->name       = $objsign->name;
        $objsign->status     = 1;
        $objsign->phone      = $objsign->phone;
        $objsign->sex        = $objsign->sex;
        $objsign->way        = $objsign->way;
        $objsign->source     = $objsign->source;
        $objsign->city       = $objsign->city;
        $objsign->remark     = $objsign->remark;
        $objsign->updated_at = time();

        if(!$objsign->save()){
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }else{
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Successful operation!'));
        }


}

/**
*@desc 教练邀请名单
*/
public function actionCoach_invitation($value='')
{
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
    $cdb = \common\models\Pro_invitation::find();
    $cdb->orderBy("created_at desc");
    
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
/**
*@desc 教练邀请报名名单状态修改（即是否买车）
*/
public function actionInvitation_update($value='')
{
    $intId = intval(Yii::$app->request->getParam('id'));
    if (!$intId) {
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
    }
    $obj = \common\models\Pro_invitation::find();
    $objsign = $obj->where(['id'=>$intId])->one();
        // $objsign = new \common\models\Pro_sign_up();
        // $objsign->name       = $objsign->name;
        $objsign->status     = 1;
        // $objsign->phone      = $objsign->phone;
        $objsign->sex        = $objsign->sex;
        // $objsign->way        = $objsign->way;
        // $objsign->source     = $objsign->source;
        // $objsign->city       = $objsign->city;
        // $objsign->remark     = $objsign->remark;
        $objsign->updated_at = time();

        if(!$objsign->save()){
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }else{
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Successful operation!'));
        }
}





public function actionSign_up(){
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
    
    $cdb = \common\models\Pro_sign_up::find();
    $cdb->orderBy("created_at desc");
    
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

public function actionInstalment(){
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
    
    $cdb = \common\models\Pro_instalment::find();
    $cdb->orderBy("created_at desc");
    
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

public function actionInstalment_update(){
    $intId = intval(Yii::$app->request->getParam('id'));
    if (!$intId) {
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
    }
    $obj = \common\models\Pro_instalment::find();
    $objsign = $obj->where(['id'=>$intId])->one();
        // $objsign = new \common\models\Pro_sign_up();
        $objsign->name       = $objsign->name;
        $objsign->status     = 1;

        $objsign->updated_at = time();

        if(!$objsign->save()){
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }else{
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Successful operation!'));
        }


}


public function actionBuycar_look(){
    $id = intval(Yii::$app->request->getParam('id'));
    
    if (!$id) {
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
    }

    // 提交并入库
    if (Yii::$app->request->getParam('action') == 'update') {
        
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        $intStatus = intval(Yii::$app->request->getParam('status'));

        $objBuycar = \common\models\Pro_buy_car::findOne(['id' => $id]);
    
        $objBuycar->status = $intStatus;
        
        if ($objBuycar->save()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, 'page100003', '', 'closeCurrent', '');
        } else {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
        }
        
    }

    $objInfo = \common\models\Pro_buy_car::findOne(['id' => $id]);
    $data = [];
    $data['objInfo'] = $objInfo;
    
    return $this->renderPartial('buycar_look', $data);
}

public function actionInvestapply_look(){
    $id = intval(Yii::$app->request->getParam('id'));
    
    if (!$id) {
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
    }

    // 提交并入库
    if (Yii::$app->request->getParam('action') == 'update') {
        
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        $intStatus = intval(Yii::$app->request->getParam('status'));
        $objJoin = \common\models\Pro_join_applying::findOne(['id' => $id]);
        $objJoin->status = $intStatus;
        if ($objJoin->save()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, 'page100003', '', 'closeCurrent', '');
        } else {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
        }
        
    }

    $objInfo = \common\models\Pro_join_applying::findOne(['id' => $id]);
    $data = [];
    $data['objInfo'] = $objInfo;
    
    return $this->renderPartial('investapply_look', $data);
}
    
}
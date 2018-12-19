<?php

namespace backend\modules\rbac\controllers;

use Yii;
use common\helpers\MyFunction;

/**
 * Description of UserController
 *
 * @author kevin
 */
class UserController extends \backend\components\AuthorityController
{
    
    public function actionIndex()
    {
        return $this->renderPartial('index');
    }
    
    // 新增管理员
    public function actionAdd() {
        $data = [
            'action' => 'create',
            'objAdmin' => null,
        ];
        return $this->renderPartial("edit", $data);
    }

    // 编辑管理员
    public function actionEdit() {
        $action = \Yii::$app->request->getParam('action');
        // 回显数据
        $intID = \Yii::$app->request->getParam('id');
        if (!$intID && $action == 'update') {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        $objAdmin = $intID ? \backend\models\Rbac_admin::findIdentity($intID) : null;

        if (!$objAdmin) {
            if ($action == 'update') {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
            }
        }

        // 提交并入库
        if (!empty($action)) {
            $authoration = \backend\components\AdminModule::getCurRoleAuthoration();
            if ($authoration < \backend\models\Rbac_role::AUTHORITY_OFFICE_MANAGER) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }
            $objOperator = \backend\models\Rbac_admin::findIdentity(\Yii::$app->user->id);
            if (!$objOperator) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }
            
            $objFormData = new \backend\models\Form_rbac_admin();
            if ($action == 'create') {
                $objFormData->setScenario($action);
            }
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
            }
            
            $objRole = \backend\models\Rbac_role::findById($objFormData->role_id);
            if (!$objRole) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Authoration not valid'), 300);
            }
            $authRole = \Yii::$app->authManager->getRole($objRole->genAuthRoleKey());
            if (!$authRole) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Authoration not valid'), 300);
            }
            if ($objRole->authority >= $authoration && !$objOperator->isAdministrator()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Authoration not valid'), 300);
            }
            
            if ($action == 'create') {
                $newUser = $objFormData->signup($objOperator->app_id);
                if ($newUser) {
                    \Yii::$app->authManager->assign($authRole, $newUser->id);

                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Adding success!'), 200, 'page100001', '', 'closeCurrent', '');
                }
                else {
                    $errText = $objFormData->getErrorAsHtml();
                    MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Adding failed!') : $errText), 300);
                }
            }
            else {
                $originAuthRoles = \Yii::$app->authManager->getRolesByUser($objAdmin->id);

                if ($objAdmin->isAdministrator()) {
                    if ($objOperator->id != $objAdmin->id) {
                        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
                    }
                }
                
                if (!$objFormData->save($objAdmin)) {
                    $errText = $objFormData->getErrorAsHtml();
                    MyFunction::funEchoJSON_Ajax((empty($errText) ? Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
                }
                $objAdmin->save();

                foreach ($originAuthRoles as $originAuthRole) {
                    if ($originAuthRole->name != $authRole->name) {
                        \Yii::$app->authManager->revoke($originAuthRole, $objAdmin->id);
                    }
                }
                if (!\Yii::$app->authManager->getAssignment($authRole->name, $objAdmin->id)) {
                    \Yii::$app->authManager->assign($authRole, $objAdmin->id);
                }

                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Modify success!'), 200, 'page100001', '', 'closeCurrent', '');
            }
        }
        else {
            if ($objAdmin) {
                $action = 'update';
            }
            else {
                $action = 'create';
            }
        }
        
        $data = [];
        $data['action'] = $action;
        $data['objAdmin'] = $objAdmin;
        return $this->renderPartial('edit', $data);
    }

    // 删除管理员
    public function actionDelete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = intval(Yii::$app->request->getParam('id'));
        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }

        $cdb = \backend\models\Rbac_admin::find();
        $cdb->where(['id' => $intID]);
        $objAdmin = $cdb->one();

        if (!$objAdmin) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }
        
        \Yii::$app->authManager->revokeAll($objAdmin->id);

        $objAdmin->delete();
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'), 200);
    }

    // 列表页快捷操作
    public function actionOperate() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intID = Yii::$app->request->getParam('id');

        if (!$intID) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'ID should not be empty!'), 300);
        }
        $cdb = \backend\models\Rbac_admin::find();
        $cdb->where("id={$intID}");
        $objAdmin = $cdb->one();

        if (!$objAdmin) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Data does not exist!'), 300);
        }

        $act = \Yii::$app->request->getParam('act');

        if ($act == 'active') {// 激活
            $objAdmin->status = \backend\models\Rbac_admin::STATUS_ACTIVE;
            $objAdmin->save();
        } elseif ($act == 'lock') {// 锁定
            $objAdmin->status = 0;
            $objAdmin->save();
        }
        else {
            
        }
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Successful operation!'), 200);
    }

}

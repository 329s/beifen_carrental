<?php
namespace backend\controllers;

use Yii;
use common\helpers\MyFunction;

/**
 * Description of RbacController
 *
 * @author kevin
 */
class Rbac2Controller  extends \backend\components\AuthorityController
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
    
    //put your code here
    // 显示管理员列表
    public function actionAdmin_index() {
        return $this->renderPartial('admin_index');
    }
    
    public function actionAdmin_list() {
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
        
        $cdb = \backend\models\Rbac_admin::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        $objAdmin = \backend\models\Rbac_admin::findIdentity(\Yii::$app->user->id);
        $authority = \backend\components\AdminModule::getCurRoleAuthoration();
        if ($objAdmin && $authority >= \backend\models\Rbac_role::AUTHORITY_OFFICE_MANAGER) {
            if ($objAdmin->belong_office_id != \common\components\OfficeModule::HEAD_OFFICE_ID) {
                $cdb->andWhere(['belong_office_id'=>$objAdmin->belong_office_id]);
            }
            else {
                if (!$objAdmin->isAdministrator()) {
                    $cdb->andWhere("username NOT LIKE '{$objAdmin->getAdmistratorNamePrefix()}%'");
                }
            }
        }
        else {
            $cdb->where(['id'=>0]);
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
        foreach ($arrRows as $row) {
            if (!isset($arrOfficeIds[$row->belong_office_id])) {
                $arrOfficeIds[$row->belong_office_id] = 1;
            }
        }
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['belong_office_disp'] = (isset($arrOfficeNames[$row->belong_office_id]) ? $arrOfficeNames[$row->belong_office_id] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }


    // 新增管理员
    public function actionAdmin_add() {
        $data = [
            'action' => 'create',
            'objAdmin' => null,
        ];
        return $this->renderPartial("admin_edit", $data);
    }

    // 编辑管理员
    public function actionAdmin_edit() {
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
        return $this->renderPartial('admin_edit', $data);
    }

    // 删除管理员
    public function actionAdmin_delete() {
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
    public function actionAdmin_direct() {
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

        $act = Yii::$app->request->getParam('act');

        if ($act == 'active') {// 激活
            $objAdmin->status = \backend\models\Rbac_admin::STATUS_ACTIVE;
            $objAdmin->save();
        } elseif ($act == 'lock') {// 锁定
            $objAdmin->status = 0;
            $objAdmin->save();
        }
        MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Successful operation!'), 200);
    }

    public function actionAdmin_log_index() {
        return $this->renderPartial('admin_log_index');
    }
    
    public function actionAdmin_log_list() {
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
        
        $cdb = \backend\models\Rbac_admin_log::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        $officeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if ($officeId != \common\components\OfficeModule::HEAD_OFFICE_ID) {
            $cdb2 = \backend\models\Rbac_admin::find();
            $cdb2->where(['belong_office_id'=>$officeId]);
            $arrRows = $cdb2->all();
            $arrAdminIds = [];
            foreach ($arrRows as $row) {
                $arrAdminIds[] = $row->id;
            }
            if (empty($arrAdminIds)) {
                $arrAdminIds[] = 0;
            }
            $cdb->andWhere(['user_id'=>$arrAdminIds]);
        }
        
        // pagiation
        $count = $cdb->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();
        
        $cdb2 = \backend\models\Rbac_admin::find();
        $userRows = $cdb2->select(['id', 'username'])->all();
        $arrUserNames = [];
        foreach ($userRows as $row) {
            $arrUserNames[$row->id] = $row->username;
        }
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $o['admin_name'] = (isset($arrUserNames[$row->user_id]) ? $arrUserNames[$row->user_id] : '');
            
            $arrData[] = $o;
        }
        
        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];
        
        echo json_encode($arrListData);
    }
    
    public function actionRole_index() {
        $objRole = \backend\models\Rbac_role::findAll([]);

        $data = [];
        $data['objRole'] = $objRole;
        return $this->renderPartial('role_index', $data);
    }
    
    public function actionRole_list() {
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
        
        $cdb = \backend\models\Rbac_role::find();
        $cdb->orderBy((isset($order) && !empty($order)) ? $order : "id desc");
        
        $authority = \backend\components\AdminModule::getCurRoleAuthoration();
        $cdb->where(['<=', 'authority', $authority]);
        
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

    public function actionRole_delete() {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }
        
        $intId = intval(Yii::$app->request->getParam('id'));
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        
        $objRole = \backend\models\Rbac_role::findById($intId);
        if ($objRole) {
            $objRole->delete();
            $authRoleName = $objRole->genAuthRoleKey();
            $authRole = \Yii::$app->authManager->getRole($authRoleName);
            \Yii::$app->authManager->remove($authRole);
            
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'));
        }
        else {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
    }

    public function actionRole_add() {
        if (Yii::$app->request->getParam('action') == 'insert') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }
            
            $objAdmin = \backend\models\Rbac_admin::findIdentity(\Yii::$app->user->id);
            if (!$objAdmin) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $strRoleName = trim(Yii::$app->request->getParam('role_name'));
            $intStatus = intval(Yii::$app->request->getParam('status'));
            $intAuthority = intval(Yii::$app->request->getParam('authority'));

            $objRole = \backend\models\Rbac_role::findOne(['role_name' => $strRoleName]);
            if (!$objRole) {
                $objRole = new \backend\models\Rbac_role();
                $objRole->role_name = $strRoleName;
                $objRole->status = $intStatus;
                $objRole->authority = $intAuthority;
                $objRole->app_id = $objAdmin->app_id;

                if ($objRole->save()) {
                    $authRoleName = $objRole->genAuthRoleKey();
                    $authRole = \Yii::$app->authManager->createRole($authRoleName);
                    $authRole->description = $objRole->role_name;
                    $authRole->data = $objRole->genAuthRoleData();
                    \Yii::$app->authManager->add($authRole);
                    
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, 'page100003', '', 'closeCurrent', '');
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                }
            } else {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, this role name already exists!'), 300);
            }
        }
        return $this->renderPartial('role_add');
    }

    public function actionRole_edit() {
        $intId = intval(Yii::$app->request->getParam('id'));
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }

        // 提交并入库
        if (Yii::$app->request->getParam('action') == 'update') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $strRoleName = trim(Yii::$app->request->getParam('role_name'));
            $intStatus = intval(Yii::$app->request->getParam('status'));
            $intAuthority = intval(Yii::$app->request->getParam('authority'));
            $intId = intval(Yii::$app->request->getParam('id'));

            $objRole = \backend\models\Rbac_role::findOne(['id' => $intId]);
            if (!$objRole) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
            } else {
                $objRole->role_name = $strRoleName;
                $objRole->status = $intStatus;
                $objRole->authority = $intAuthority;
                
                $authRoleName = $objRole->genAuthRoleKey();
                $authRole = \Yii::$app->authManager->getRole($authRoleName);
                $authRole->data->authority = $objRole->authority;
                $authRole->description = $objRole->role_name;
                
                if ($objRole->save()) {
                    \Yii::$app->authManager->update($authRoleName, $authRole);
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, 'page100003', '', 'closeCurrent', '');
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                }
            }
        }

        $objRole = \backend\models\Rbac_role::findOne(['id' => $intId]);

        $data = [];
        $data['objRole'] = $objRole;
        return $this->renderPartial('role_edit', $data);
    }

    public function actionRole_column() {
        $intId = intval(Yii::$app->request->getParam('id'));
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        // 取得目前角色所有的权限 
        $objRole = \backend\models\Rbac_role::findById($intId);
        if (!$objRole) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        $authRole = \Yii::$app->authManager->getRole($objRole->genAuthRoleKey());
        $authPermissions = \Yii::$app->authManager->getPermissionsByRole($authRole->name);
        $allColumns = \backend\modules\rbac\models\Permission::getAll();
        $authColumnIds = \backend\modules\rbac\components\AdminMenuAuth::convertAuthedPermissions($authPermissions);

        // 提交并入库
        if (Yii::$app->request->getParam('action') == 'update') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $arrSelect = Yii::$app->request->getParam('selected_column');
            if ($arrSelect && $intId) {
                $addColumnPermissions = [];
                foreach ($arrSelect as $v) {
                    if (isset($allColumns[$v])) {
                        if (isset($authColumnIds[$v])) {
                            unset($authColumnIds[$v]);
                        }
                        else {
                            $columnInfo = $allColumns[$v];
                            $permission = \Yii::$app->authManager->getPermission($columnInfo->getAuthPermissionKey());
                            if (!$permission) {
                                $permission = \Yii::$app->authManager->createPermission($columnInfo->getAuthPermissionKey());
                                $permission->description = $columnInfo->description;
                                $permission->data = $columnInfo->genAuthPermissionData();
                                \Yii::$app->authManager->add($permission);
                            }
                            $addColumnPermissions[$v] = $permission;
                        }
                    }
                }
                
                foreach ($authColumnIds as $columnId => $permission) {
                    \Yii::$app->authManager->removeChild($authRole, $permission);
                }
                foreach ($addColumnPermissions as $columnId => $permission) {
                    \Yii::$app->authManager->addChild($authRole, $permission);
                }
                
            }
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'closeCurrent', '');
        }

        // 取得所有可用栏目
        $arrAuth = \backend\modules\rbac\components\AdminMenuAuth::findAllColumn(true, false, false);

        $data = [];
        $data['arrAuth'] = $arrAuth;
        $data['intId'] = $intId;
        $data['arrRC'] = $authColumnIds;
        return $this->renderPartial('role_column', $data);
    }





    // 后台权限方法
    public function actionColumn_management() {
        $columnList = \backend\modules\rbac\components\AdminMenuAuth::findAllColumn(true,false,false);

        $data = [];
        $data['columnList'] = $columnList;
        return $this->renderPartial('column_management', $data);
    }

    public function actionColumn_list(){
        $cdb = \backend\models\Rbac_column::find();
        $cdb->orderBy("column_code asc");
        $arrRows = $cdb->all();

        $arrData = [];
        foreach ($arrRows as $row) {
            $o = $row->getAttributes();
            $arrData[] = $o;
        }

        $arrListData = [
            'rows' => $arrData,
        ];

        echo json_encode($arrListData);
    }

    public function actionColumn_add(){

        if (Yii::$app->request->getParam('action') == 'insert') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $objAdmin = \backend\models\Rbac_admin::findIdentity(\Yii::$app->user->id);
            if (!$objAdmin) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }
            $strColumnCode = trim(Yii::$app->request->getParam('column_code'));
            $strColumnName = trim(Yii::$app->request->getParam('column_name'));
            $strColumnUrl = trim(Yii::$app->request->getParam('column_url'));
            $strColumnIcon = trim(Yii::$app->request->getParam('column_icon'));
            $intColumnOrder = intval(Yii::$app->request->getParam('c_order'));
            $intColumnStatus = intval(Yii::$app->request->getParam('status'));


            $objCode = \backend\models\Rbac_column::findOne(['column_code' => $strColumnCode]);
            $objName = \backend\models\Rbac_column::findOne(['column_name' => $strColumnName]);

            if (!$objCode && !$objName) {
                $objColumn = new \backend\models\Rbac_column();
                $objColumn->column_code = $strColumnCode;
                $objColumn->column_name = $strColumnName;
                $objColumn->column_url = $strColumnUrl;
                $objColumn->column_icon = $strColumnIcon;
                $objColumn->c_order = $intColumnOrder;
                $objColumn->status = $intColumnStatus;


                if ($objColumn->save()) {
                    $authColumnName = $objColumn->genAuthColumnKey();
                    $authColumn = \Yii::$app->authManager->createRole($authColumnName);
                    $authColumn->data = $objColumn->genAuthColumnData();
                    \Yii::$app->authManager->add($authColumn);
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, 'page100003', '', 'closeCurrent', '');
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                }
            } else {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, this code or name already exists!'), 300);
            }
        }
        return $this->renderPartial('column_add');
    }

    public function actionColumn_edit() {
        $intId = intval(Yii::$app->request->getParam('id'));

        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }

        // 提交并入库
        if (Yii::$app->request->getParam('action') == 'update') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $strColumnCode = trim(Yii::$app->request->getParam('column_code'));
            $strColumnName = trim(Yii::$app->request->getParam('column_name'));
            $strColumnUrl = trim(Yii::$app->request->getParam('column_url'));
            $strColumnIcon = trim(Yii::$app->request->getParam('column_icon'));
            $intColumnOrder = intval(Yii::$app->request->getParam('c_order'));
            $intColumnStatus = intval(Yii::$app->request->getParam('status'));
            $intId = intval(Yii::$app->request->getParam('id'));

            $objColumn = \backend\models\Rbac_column::findOne(['id' => $intId]);

            if (!$objColumn) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
            } else {

                $objColumn->column_code = $strColumnCode;
                $objColumn->column_name = $strColumnName;
                $objColumn->column_url = $strColumnUrl;
                $objColumn->column_icon = $strColumnIcon;
                $objColumn->c_order = $intColumnOrder;
                $objColumn->status = $intColumnStatus;
                if ($objColumn->save()) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, 'page100003', '', 'closeCurrent', '');
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                }
            }
        }

        $objColumn = \backend\models\Rbac_Column::findOne(['id' => $intId]);
        $data = [];
        $data['objColumn'] = $objColumn;
        return $this->renderPartial('column_edit', $data);
    }

    public function actionColumn_del(){
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }

        $intId = intval(Yii::$app->request->getParam('id'));
        if (!$intId) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        $objColumn = \backend\models\Rbac_Column::findById($intId);


        if ($objColumn) {
            $objNode = \backend\models\Rbac_permission::findOne(['name' => $objColumn['column_code']]);
            $objNodeInfo = array();
            $objNextInfo = array();
            if($objNode){
                $objNodeInfo = $objNode->getAttributes();
                if($objNodeInfo){
                    $objNodeNext = \backend\models\Rbac_permission::findOne(['parent' => $objNodeInfo['name']]);
                    if($objNodeNext){
                        $objNextInfo = $objNodeNext->getAttributes();
                    }
                }
            }
            if($objNodeInfo || $objNextInfo){
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the affiliate node is not deleted!'), 300);
            }else{
                $objColumn->delete();
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'));
            }
        }
        else {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
    }


    public function actionNode_index() {

        // $columnList = \backend\modules\rbac\components\AdminMenuAuth::findAllColumn(true,false,false);


        $data = [];

        // $data['columnList'] = $columnList;

        return $this->renderPartial('node_index', $data);
    }

    public function actionNode_list(){

        // get pagination
        $intPage = intval(Yii::$app->request->getParam('page'));
        if ($intPage == 0)
            $intPage = 1;
        $numPerPage = intval(Yii::$app->request->getParam('rows'));
        $numPerPage = intval($numPerPage);
        if (!$numPerPage)
            $numPerPage = $this->pageSize;

        $parent_id = trim(Yii::$app->request->getParam('parent_id'));
        $cdb = \backend\models\Rbac_permission::find();
        if($parent_id){
            $cdb->where('parent = "'.$parent_id.'"');
        }else{
            $cdb->where('parent = ""');
        }

        $cdb->orderBy("name asc");

        // pagiation
        $count = $cdb->count();

        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
        $cdb->limit($pages->getLimit());
        $cdb->offset($pages->getOffset());

        $arrRows = $cdb->all();

        $arrData = array();
        foreach ($arrRows as $row) {
            $val = $row->getAttributes();
            $arrData[] = $val;
        }

        $arrListData = [
            'total' => intval($count),
            'rows' => $arrData,
        ];

        echo json_encode($arrListData);
    }

    public function actionNode_add(){
        if (Yii::$app->request->getParam('action') == 'insert') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }

            $objAdmin = \backend\models\Rbac_admin::findIdentity(\Yii::$app->user->id);
            if (!$objAdmin) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }


            $strNodeName = trim(Yii::$app->request->getParam('name'));
            $strNodeCategory = trim(Yii::$app->request->getParam('category'));
            $strNodeParent = trim(Yii::$app->request->getParam('parent'));
            $strNodeHref = trim(Yii::$app->request->getParam('href'));
            $strNodeIcon = trim(Yii::$app->request->getParam('icon'));
            $strNodeIconTraditional = trim(Yii::$app->request->getParam('icon_traditional'));
            $strNodeDescription = trim(Yii::$app->request->getParam('description'));
            $intNodeOrder = intval(Yii::$app->request->getParam('c_order'));
            $intNodeStatus = intval(Yii::$app->request->getParam('status'));

            $objNode = \backend\models\Rbac_permission::findOne(['name' => $strNodeName]);
            if(isset($strNodeParent)){
                $objParent = \backend\models\Rbac_permission::findOne(['parent' => $strNodeParent]);
                if(empty($objParent)){
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, parent name non existent!'), 300);
                }
            }

            if (!$objNode) {
                $objNode = new \backend\models\Rbac_permission();
                $objNode->name = $strNodeName;
                $objNode->category = $strNodeCategory;
                $objNode->parent = $strNodeParent;
                $objNode->href = $strNodeHref;
                $objNode->icon = $strNodeIcon;
                $objNode->icon_traditional = $strNodeIconTraditional;
                $objNode->description = $strNodeDescription;
                $objNode->c_order = $intNodeOrder;
                $objNode->status = $intNodeStatus;


                if ($objNode->save()) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, 'page100003', '', 'closeCurrent', '');
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                }
            } else {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, this node name already exists!'), 300);
            }
        }
        return $this->renderPartial('node_add');
    }

    public function actionNode_edit() {
        $strName = trim(Yii::$app->request->getParam('name'));

        if (!$strName) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }

        // 提交并入库
        if (Yii::$app->request->getParam('action') == 'update') {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
            }
            $strNodeName = trim(Yii::$app->request->getParam('name'));
            $strNodeCategory = trim(Yii::$app->request->getParam('category'));
            $strNodeParent = trim(Yii::$app->request->getParam('parent'));
            $strNodeHref = trim(Yii::$app->request->getParam('href'));
            $strNodeIcon = trim(Yii::$app->request->getParam('icon'));
            $strNodeIconTraditional = trim(Yii::$app->request->getParam('icon_traditional'));
            $strNodeDescription = trim(Yii::$app->request->getParam('description'));
            $intNodeOrder = intval(Yii::$app->request->getParam('c_order'));
            $intNodeStatus = intval(Yii::$app->request->getParam('status'));

            $objNode = \backend\models\Rbac_permission::findOne(['name' => $strNodeName]);
            if(isset($strNodeParent)){
                $objParent = \backend\models\Rbac_permission::findOne(['parent' => $strNodeParent]);
                if(empty($objParent)){
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, parent name non existent!'), 300);
                }
            }

            if (!$objNode) {
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation failed!'), 300);
            } else {
                $objNode->name = $strNodeName;
                $objNode->category = $strNodeCategory;
                $objNode->parent = $strNodeParent;
                $objNode->href = $strNodeHref;
                $objNode->icon = $strNodeIcon;
                $objNode->icon_traditional = $strNodeIconTraditional;
                $objNode->description = $strNodeDescription;
                $objNode->c_order = $intNodeOrder;
                $objNode->status = $intNodeStatus;

                if ($objNode->save()) {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, 'page100003', '', 'closeCurrent', '');
                } else {
                    MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the operation fails, please re-submit!'), 300);
                }
            }
        }

        $objNode = \backend\models\Rbac_permission::findOne(['name' => $strName]);
        $data = [];
        $data['objNode'] = $objNode;
        return $this->renderPartial('node_edit', $data);
    }

    public function actionNode_del(){
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
        }

        $nodeName = trim(Yii::$app->request->getParam('name'));
        if (!$nodeName) {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
        $objNode = \backend\models\Rbac_permission::findOne(['name' => $nodeName]);

        if ($objNode) {
            $objNodeInfo = $objNode->getAttributes();
            $objNextInfo = array();
            if($objNodeInfo){
                $objNodeNext = \backend\models\Rbac_permission::findOne(['parent' => $objNodeInfo['name']]);
                if($objNodeNext){
                    $objNextInfo = $objNodeNext->getAttributes();
                }
            }
            if($objNextInfo){
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the affiliate node is not deleted!'), 300);
            }else{
                $objNode->delete();
                MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Deleted successfully!'));
            }
        }
        else {
            MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Sorry, the parameter is not correct!'), 300);
        }
    }
}


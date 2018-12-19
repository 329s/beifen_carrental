<?php

namespace console\controllers;

/**
 * Description of UpgradeController
 *
 * @author kevin
 */
class UpgradeController extends \yii\console\Controller
{
    
    public function actionAuthoration()
    {
        /**
         * @var yii\rbac\DbManager
         */
        $authManager = \Yii::$app->authManager;
        /**
         * @var yii\db\Connection
         */
        $db = \Yii::$app->db;
        $q = \backend\models\Rbac_role::find();
        $originRoles = $q->all();
        $existingRoles = $authManager->getRoles();
        $existingPermissions = $authManager->getPermissions();
        $rolesById = [];
        foreach ($originRoles as $row) {
            $name = $row->genAuthRoleKey();
            if (!isset($existingRoles[$name])) {
                $role = $authManager->createRole($name);
                $role->description = $row->role_name;
                $role->data = $row->genAuthRoleData();
                
                $authManager->add($role);
                
                $rolesById[$row->id] = $role;
            }
            else {
                $rolesById[$row->id] = $existingRoles[$name];
            }
        }
        
        $originAsignments = $db->createCommand('SELECT * FROM rbac_user_role')->queryAll();
        foreach ($originAsignments as $row) {
            $role = isset($rolesById[$row['role_id']]) ? $rolesById[$row['role_id']] : null;
            if ($role) {
                if (!$authManager->getAssignment($role->name, $row['user_id'])) {
                    $authManager->assign($role, $row['user_id']);
                }
            }
        }
        
        $existingRbacPermissions = \backend\modules\rbac\models\Permission::getAll();
        
        $allColumns = $db->createCommand('SELECT * FROM rbac_column')->queryAll();
        $convertUrls = [
            'vehicle/inner_booking' => 'internal-service/booking',
            'vehicle/applying' => 'internal-service/applying',
            'vehicle/waiting_index' => 'rental/waiting_index',
            'vehicle/rent_register' => 'rental/booking',
            'statement/monthly' => 'statement/orderbymonthly',
            
            'rbac/admin_index' => 'rbac2/admin_index',
            'rbac/role_index' => 'rbac2/role_index',
            'rbac/admin_log_index' => 'rbac2/admin_log_index',
        ];
        $permissionsByColId = [];
        foreach ($allColumns as $row) {
            if (!isset($existingRbacPermissions[$row['column_code']])) {
                $menu = new \backend\modules\rbac\models\Permission();
                $menu->name = $row['column_code'];
                $menu->category = 'menu';
                $menu->description = $row['column_name'];
                $menu->href = isset($convertUrls[$row['column_url']]) ? $convertUrls[$row['column_url']] : $row['column_url'];
                $menu->icon_traditional = $row['column_icon'];
                if (strlen($menu->name) >= 6) {
                    $menu->parent = substr($menu->name, 0, 3);
                }
                else {
                    $menu->parent = '';
                }
                $menu->c_order = $row['c_order'];
                $menu->status = $row['status'] == 1 ? 0 : -10;
                $menu->target = $row['is_iframe'] ? '_blank' : '';
                $menu->save();
                
                $existingRbacPermissions[$menu->name] = $menu;
            }
            else {
                $menu = $existingRbacPermissions[$row['column_code']];
            }
            
            $menuName = $menu->getAuthPermissionKey();
            if (!isset($existingPermissions[$menuName])) {
                $permission = $authManager->createPermission($menuName);
                $permission->description = $menu->description;
                $permission->data = $menu->genAuthPermissionData();
                
                $authManager->add($permission);
                
                $permissionsByColId[$row['id']] = $permission;
                $existingPermissions[$menuName] = $permission;
            }
            else {
                $permissionsByColId[$row['id']] = $existingPermissions[$menuName];
            }
            
        }
        
        foreach ($existingRbacPermissions as $menu) {
            $menuName = $menu->getAuthPermissionKey();
            if (!isset($existingPermissions[$menuName])) {
                $permission = $authManager->createPermission($menuName);
                $permission->description = $menu->description;
                $permission->data = $menu->genAuthPermissionData();
                
                $authManager->add($permission);
                
                if ($menu->category == 'menu') {
                    $permissionsByColId[$row['id']] = $permission;
                }
            }
            else {
                if ($menu->category == 'menu') {
                    $permissionsByColId[$row['id']] = $existingPermissions[$menuName];
                }
            }
        }
        
        $originColIds = $db->createCommand('SELECT * FROM rbac_role_column')->queryAll();
        foreach ($originColIds as $row) {
            $role = $role = isset($rolesById[$row['role_id']]) ? $rolesById[$row['role_id']] : null;
            if ($role) {
                $permission = isset($permissionsByColId[$row['column_id']]) ? $permissionsByColId[$row['column_id']] : null;
                if (!$authManager->hasChild($role, $permission)) {
                    $authManager->addChild($role, $permission);
                }
            }
        }
        
        $adminUser = \backend\models\Rbac_admin::findOne(['username'=>'admin']);
        if ($adminUser) {
            $adminRoles = \Yii::$app->authManager->getRolesByUser($adminUser->id);
            if (count($adminRoles) == 1) {
                foreach ($adminRoles as $adminRole) {
                    $adminPermissions = ['rbac2/role_index', 'rbac2/role_column'];
                    foreach ($adminPermissions as $permissionName) {
                        $permission = \Yii::$app->authManager->getPermission($permissionName);
                        if (!$permission) {
                            continue;
                        }
                        if (!\Yii::$app->authManager->hasChild($adminRole, $permission)) {
                            \Yii::$app->authManager->addChild($adminRole, $permission);
                        }
                    }
                }
            }
        }
    }
    
    public function actionOrders() {
        $query = \common\models\Pro_vehicle_order::find(true);
        $pageSize = 500;
        $page = 0;
        $count = $query->count();
        $pages = new \yii\data\Pagination(['totalCount'=>$count]);
        $pages->setPageSize($pageSize);
        $query->limit($pages->getLimit());
        
        $db = \Yii::$app->db;
        $tmpOrder = new \common\models\Pro_vehicle_order();
        $tmpOrder->rent_days = 0;
        while ($page * $pageSize < $count) {
            $pages->setPage($page);
            $query->offset($pages->getOffset());
            
            $arrRows = $query->asArray()->all();
            foreach ($arrRows as $row) {
                $tmpOrder->resetDailyRentDetailedPriceArrayInfoWithData($row['daily_rent_detailed_info'], true);
                $row['daily_rent_detailed_info'] = $tmpOrder->daily_rent_detailed_info;
                $db->createCommand("UPDATE ".\common\models\Pro_vehicle_order::tableName()." SET daily_rent_detailed_info='{$row['daily_rent_detailed_info']}' WHERE id={$row['id']}")->execute();
            }
            
            $page++;
        }
        
        $page2 = 0;
        $query2 = \common\models\Pro_vehicle_order_change_log::find(true);
        $count2 = $query2->count();
        $pages2 = new \yii\data\Pagination(['totalCount'=>$count2]);
        $pages2->setPageSize($pageSize);
        $query2->limit($pages2->getLimit());
        
        while ($page2 * $pageSize < $count2) {
            $pages2->setPage($page2);
            $query2->offset($pages2->getOffset());
            
            $arrRows = $query2->asArray()->all();
            foreach ($arrRows as $row) {
                $tmpOrder->resetDailyRentDetailedPriceArrayInfoWithData($row['daily_rent_detailed_info'], true);
                $row['daily_rent_detailed_info'] = $tmpOrder->daily_rent_detailed_info;
                $db->createCommand("UPDATE ".\common\models\Pro_vehicle_order_change_log::tableName()." SET daily_rent_detailed_info='{$row['daily_rent_detailed_info']}' WHERE id={$row['id']}")->execute();
            }
            
            $page2++;
        }
        
    }
    
    public function actionAppidSupport() {
        $arrSkipTables = ['migration', 'rbac_permission', 'rbac_column', 'rbac_role_column', 'rbac_user_role'];
        /**
         * @var yii\db\Connection
         */
        $db = \Yii::$app->db;
        $insertKey = 'app_id';
        $defaultValue = 1001;
        $insertKeyComment = '应用ID';
        $arrTables = $db->createCommand("SHOW TABLES")->queryAll();
        foreach ($arrTables as $table) {
            $prefix = substr($table, 0, 4);
            if ($prefix == 'auth' || $prefix == 'yii_') {
                continue;
            }
            elseif (in_array($table, $arrSkipTables)) {
                continue;
            }
            $tableSchema = $db->getSchema()->getTableSchema($table);
            if (empty($tableSchema->primaryKey)) {
                $this->stderr("The table:{$table} does not have any primary key, the table alterment is skipped.");
                continue;
            }
            $afterKey = $tableSchema->primaryKey[count($tableSchema->primaryKey)-1];
            
            if (isset($tableSchema->columns[$insertKey])) {
                $dbName = \Yii::$app->db->driverName;
                $sql = "SELECT * FROM information_schema.statistics WHERE table_schema='{$dbName}' AND table_name = '{$table}' AND column_name = '{$insertKey}'";
                if (\Yii::$app->db->createCommand($sql)->queryOne()) {
                    continue;
                }
                $sql = "ALTER TABLE `{$table}` \n".
                    "  ADD INDEX `index_{$insertKey}` (`{$insertKey}`);";
            }
            else {
                $sql = "ALTER TABLE `{$table}` \n".
                    "  ADD COLUMN `{$insertKey}`  int(10) UNSIGNED NOT NULL DEFAULT '{$defaultValue}' COMMENT '{$insertKeyComment}' AFTER `{$afterKey}`,".
                    "  ADD INDEX `index_{$insertKey}` (`{$insertKey}`);";
            }
            
            $this->stdout("processing table:{$table}...");
            $this->stdout($sql);
            if ($db->createCommand($sql)->execute() > 0) {
                $this->stdout('finished.');
            }
            else {
                $this->stdout('failed!');
            }
            
        }
        
        $arrAlterUniqueKeys = [
            'pro_city_area' => ['index_name'=>''],
        ];
        
    }
    
}

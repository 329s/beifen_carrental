<?php

namespace backend\modules\rbac\controllers;

/**
 * Default controller for the `permissions` module
 */
class PermissionsController extends \backend\components\AuthorityController
{
    
    public function beforeAction($action) {
        if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
            return false;
        }
        return parent::beforeAction($action);
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->renderPartial('index');
    }
    
    public function actionMenuManagement()
    {
        return $this->renderPartial('menu-management');
    }
    
    public function actionSystemManagement()
    {
        return $this->renderPartial('system-management');
    }
    
    public function actionEdit()
    {
        $action = \Yii::$app->request->get('act');
        $name = \Yii::$app->request->get('key');
        $callback = \Yii::$app->request->get('cb');
        $objForm = new \backend\modules\rbac\models\PermissionForm();
        if ($action == 'save') {
            if (!$objForm->load(\Yii::$app->request->post())) {
                $errText = $objForm->getErrorAsHtml();
                \common\widgets\JsonResultWidget::widget([
                    'code' => 300,
                    'message' => empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText,
                ]);
            }
            
            $mode = \Yii::$app->request->post('action');
            $objItem = \backend\modules\rbac\models\Permission::findOne(['name' => $objForm->name]);
            $originItem = null;
            if ($mode == 'create') {
                if ($objItem) {
                    \common\widgets\JsonResultWidget::widget([
                        'code' => 300,
                        'message' => \Yii::t('locale', 'Sorry, this {name} already exists!', ['name'=>$objForm->getAttributeLabel('name')]),
                    ]);
                }
                $objItem = new \backend\modules\rbac\models\Permission();
                $objItem->name = $objForm->name;
            }
            else {
                $originName = \Yii::$app->request->post('originName');
                if ($objForm->name != $originName) {
                    if ($objItem) {
                        \common\widgets\JsonResultWidget::widget([
                            'code' => 300,
                            'message' => \Yii::t('locale', 'Sorry, this {name} already exists!', ['name'=>$objForm->getAttributeLabel('name')]),
                        ]);
                    }
                    $originItem = \backend\modules\rbac\models\Permission::findOne(['name' => $originName]);
                    if (!$originItem) {
                        \common\widgets\JsonResultWidget::widget([
                            'code' => 300,
                            'message' => \Yii::t('locale', '{name} not exists!', ['name'=>$objForm->getAttributeLabel('name')]),
                        ]);
                    }
                    $objItem = new \backend\modules\rbac\models\Permission();
                    $objItem->name = $objForm->name;
                }
            }
            if (!$objForm->save($objItem)) {
                $errText = $objForm->getErrorAsHtml();
                \common\widgets\JsonResultWidget::widget([
                    'code' => 300,
                    'message' => empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText,
                ]);
            }
            if ($objItem->save()) {
                $permissionKey = $objItem->getAuthPermissionKey();
                $permission = \Yii::$app->authManager->getPermission($permissionKey);
                if ($permission) {
                    $permission->description = $objItem->description;
                    $permission->data = $objItem->genAuthPermissionData();
                    \Yii::$app->authManager->update($objItem->name, $permission);
                }
                else {
                    $permission = \Yii::$app->authManager->createPermission($permissionKey);
                    $permission->description = $objItem->description;
                    $permission->data = $objItem->genAuthPermissionData();

                    \Yii::$app->authManager->add($permission);
                }
                
                if ($originItem) {
                    $permissionKey = $originItem->getAuthPermissionKey();
                    $permission = \Yii::$app->authManager->getPermission($permissionKey);
                    if ($permission) {
                        \Yii::$app->authManager->remove($permission);
                    }
                    $originItem->delete();
                }
                
                \common\widgets\JsonResultWidget::widget([
                    'code' => 200,
                    'message' => \Yii::t('locale', 'Congratulations, successful operation!'),
                    'callbackType' => 'closeCurrent',
                ]);
            }
            else {
                $errText = $objItem->getErrorAsHtml();
                \common\widgets\JsonResultWidget::widget([
                    'code' => 300,
                    'message' => ($errText ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText),
                ]);
            }
        }
        if (!empty($name)) {
            $objData = \backend\modules\rbac\models\Permission::findById($name, 'name');
            if ($objData) {
                if ($action == 'create') {
                    $objForm->category = $objData->category;
                    $objForm->parent = $objData->parent;
                    $objForm->c_order = $objData->c_order;
                }
                elseif ($action == 'edit') {
                    $objForm->load($objData->getAttributes(), '');
                }
            }
        }
        return $this->renderPartial('edit', ['action'=>$action, 'objForm'=>$objForm, 'callback'=>$callback]);
    }
    
    public function actionDelete()
    {
        $name = \Yii::$app->request->get('key');
        if (empty($name)) {
            \common\widgets\JsonResultWidget::widget([
                'code' => 300,
                'message' => \Yii::t('locale', 'Invalid parameter!'),
            ]);
        }
        $objData = \backend\modules\rbac\models\Permission::findOne(['name'=>$name]);
        if (!$objData) {
            \common\widgets\JsonResultWidget::widget([
                'code' => 300,
                'message' => \Yii::t('locale', 'Data does not exist!'),
            ]);
        }
        $permissionKey = $objData->getAuthPermissionKey();
        $permission = \Yii::$app->authManager->getPermission($permissionKey);
        if ($permission) {
            \Yii::$app->authManager->remove($permission);
        }
        $objData->delete();
        \common\widgets\JsonResultWidget::widget([
            'code' => 200,
            'message' => \Yii::t('locale', 'Deleted successfully!'),
        ]);
    }
    
    public function actionMenuTreeData()
    {
        $menuData = \backend\modules\rbac\components\AdminMenuAuth::findAllColumn(false, false, false);
        
    }
    
}

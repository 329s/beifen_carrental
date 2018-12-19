<?php

namespace backend\modules\rbac\components;

use Yii;
use common\helpers\MyFunction;

// 权限运算model
class AdminMenuAuth
{

    // 取得所有栏目
    public static function findAllColumn($isDHide = false, $filterColumnCodes = [], $menuOnly = true) {
        // 栏目数组
        $arrCData = [];

        $cdb = \backend\modules\rbac\models\Permission::find();
        
        // 过滤停用的栏目
        if ($isDHide) {
            $cdb->andWhere(['status'=>0]);
        }

        $cdb->orderBy("c_order asc, name asc");

        $LAN_ARRS = [];
        if (Yii::$app->language != "zh-CN") {
            //$lanPath = Yii::$app->getBasePath();
            //$lanPath .= DIRECTORY_SEPARATOR . Yii::$app->language . "/rbac.php";
            //if (is_file($lanPath)) {
            //    spl_autoload_unregister(['YiiBase', 'autoload']);
            //    $LAN_ARRS = require_once $lanPath;
            //    spl_autoload_register(['YiiBase', 'autoload']);
            //}
        }
        
        $arrRows = $cdb->asArray()->all();
        if ($arrRows) {
            foreach ($arrRows as $k => $v) {
                if ($filterColumnCodes !== false && !isset($filterColumnCodes[$v['name']])) {
                    continue;
                }
                $o = array_merge([], $v);   // $v->getAttributes();
                $_name = \Yii::t('rbac', $v['name']);
                if (Yii::$app->language != "zh-CN" && array_key_exists($v['name'], $LAN_ARRS)) {
                    $o['name'] = $LAN_ARRS[$v['name']];
                }
                else if (!empty($_name)) {
                    $o['name'] = $_name;
                }
                else {
                    $o['name'] = $v['name'];
                }
                $arrCData[$k] = $o;
            }
        } else {
            MyFunction::funAlert(\Yii::t('locale', 'Column data error!'));
            exit;
        }

        // 转换数组
        return self::convertGAColumn($arrCData, $menuOnly);
    }
    
    public static function convertGAColumn($arrData = [], $menuOnly = true) {
        if ($arrData) {
            $arrTemp = [];
            $arrParentMap = [];
            
            foreach ($arrData as $k => $o) {
                if ($o['parent'] != '' && !isset($arrParentMap[$o['name']])) {
                    $arrParentMap[$o['name']] = $o['parent'];
                }
            }
            
            foreach ($arrData as $k => $o) {
                if ($o['parent'] == '') {
                    $arrTemp[$o['name']] = ['o' => $o];
                    continue;
                }
                elseif ($menuOnly && $o['category'] != 'menu') {
                    if ($menuOnly !== 999 || $o['category'] != 'node') {
                        continue;
                    }
                }
                
                $parent = $o['parent'];
                $parents = [$parent];
                while ($parent && !isset($arrTemp[$parent])) {
                    $parent = isset($arrParentMap[$parent]) ? $arrParentMap[$parent] : false;
                    if ($parent) {
                        array_push($parents, $parent);
                    }
                }

                $arrParent = &$arrTemp;
                do
                {
                    $parent = array_pop($parents);
                    $arrT = &$arrParent[$parent];
                    if (!isset($arrT['children'])) {
                        $arrT['children'] = [];
                    }
                    $arrParent = &$arrT['children'];
                } while(!empty($parents));

                $arrParent[$o['name']] = ['o' => $o];
            }
            $arrData = $arrTemp;
        }
        return $arrData;
    }
    
    public static function convertAuthedPermissions($authPermissions) {
        $authColumns = [];
        foreach ($authPermissions as $permission) {
            if ($permission->data->type == \backend\modules\rbac\models\Permission::TYPE_MENU
                || $permission->data->type == \backend\modules\rbac\models\Permission::TYPE_NODE) {
                $authColumns[$permission->data->code] = $permission;
            }
            else {
                $authColumns[$permission->name] = $permission;
            }
        }
        
        return $authColumns;
    }

    // 取得指定用户id所对应的栏目 
    public static function findUAColumn($userId = 0) {
        $authPermissions = Yii::$app->authManager->getPermissionsByUser($userId);
        $authColumns = self::convertAuthedPermissions($authPermissions);
        if (empty($authColumns)) {
            \Yii::$app->user->logout();
            MyFunction::funAlert(Yii::t('locale', 'Sorry, no operating privileges for current user!'));
            echo "<script>parent.location='".\yii\helpers\Url::to(['site/login'])."'</script>";
            exit;
        }
        
        return self::findAllColumn(true, $authColumns);
    }

}

<?php

namespace backend\modules\rbac\components;

/**
 * Description of PermissionsService
 *
 * @author kevin
 */
class PermissionsService
{
    
    public static function getMenuPermissionsAsCombotreeData()
    {
        $allPermissions = AdminMenuAuth::findAllColumn(false, false, 999);
        $arrData = self::_convertTreePermissionsToCombotreeData($allPermissions);
        return $arrData;
    }
    
    public static function _convertTreePermissionsToCombotreeData($arr) {
        $arrData = [];
        foreach ($arr as $item) {
            $o = ['id'=>$item['o']['name'], 'text'=>$item['o']['description']];
            if (isset($item['children'])) {
                $o['checkable'] = true;
                $o['children'] = self::_convertTreePermissionsToCombotreeData($item['children']);
            }
            $arrData[] = $o;
        }
        return $arrData;
    }
    
    public static function getMenuPermissionsAsTreeData()
    {
        $query = \backend\modules\rbac\models\Permission::find();
        $arrRows = $query->asArray()->all();
        
        $arrMenuKeys = [];
        foreach ($arrRows as $row) {
            if ($row['category'] == 'menu') {
                $arrMenuKeys[$row['name']] = $row['name'];
            }
        }
        /*
        $arrData = [];
        foreach ($arrRows as $row) {
            $arrData[] = [
                'id' => $row['name'],
                'text' => $row['description'],
                'parent' => empty($row['parent']) ? '#' : $row['parent'],
            ];
        }
        return $arrData;
        */
        return self::convertMenuPermissionsTreeData($arrRows, $arrMenuKeys);
    }
    
    public static function convertMenuPermissionsTreeData($rows, $menuKeys)
    {
        $arrData = [];
        
        foreach ($rows as $row) {
            
        }
        
        return $arrData;
    }
    
}

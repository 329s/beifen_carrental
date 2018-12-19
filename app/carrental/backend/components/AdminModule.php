<?php

namespace backend\components;

class AdminModule
{
    
    private static $_authedOfficeId = null;
    private static $_curRoleAuthoration = null;
    
    public static function getUserNamesArray($userIdArray) {
        $arrData = [];
        $cdb2 = \backend\models\Rbac_admin::find();
        $cdb2->select(['id', 'username']);
        if (is_array($userIdArray) && !empty($userIdArray)) {
            $cdb2->where(['id' => $userIdArray]);
        }
        else {
            $cdb2->where(['id' => intval($userIdArray)]);
        }
        $arrRows = $cdb2->asArray()->all();
        foreach ($arrRows as $row) {
            $arrData[$row['id']] = $row['username'];
        }
        return $arrData;
    }
    
    public static function getCurUserName() {
        $identity = \Yii::$app->user->getIdentity();
        if ($identity instanceof \backend\models\Rbac_admin) {
            return $identity->username;
        }
        elseif ($identity instanceof \common\models\Pub_user) {
            return $identity->account;
        }
        return '';
    }
    
    public static function getAuthorizedOfficeId() {
        if (\Yii::$app->user->isGuest) {
            return 0;
        }
        if (static::$_authedOfficeId === null) {
            $identity = \Yii::$app->user->getIdentity();
            if ($identity instanceof \backend\models\Rbac_admin) {
                static::$_authedOfficeId = $identity->belong_office_id;
            }
            else {
                static::$_authedOfficeId = 0;
            }
        }
        return static::$_authedOfficeId;
    }
    
    public static function getAdminActualOfficeId() {
        $officeId = static::getAuthorizedOfficeId();
        return ($officeId >= 0 ? $officeId : 0);
    }
    
    public static function isAuthorizedHeadOffice() {
        $officeId = self::getAuthorizedOfficeId();
        return $officeId == \common\components\OfficeModule::HEAD_OFFICE_ID;
    }
    
    public static function getAuthorizedVehicleOfficeId() {
        $officeId = static::getAuthorizedOfficeId();
        if ($officeId >= 0) {
            if (self::getCurRoleAuthoration() >= \backend\models\Rbac_role::AUTHORITY_OFFICE_MANAGER) {
                $officeId = -1;
            }
        }
        return $officeId;
    }
    
    public static function getCurRoleAuthoration() {
        if (\Yii::$app->user->isGuest) {
            return 0;
        }
        if (static::$_curRoleAuthoration === null) {
            $authority = 0;
            $authRoles = \Yii::$app->authManager->getRolesByUser(\Yii::$app->user->id);
            if ($authRoles) {
                foreach ($authRoles as $authRole) {
                    if ($authority < $authRole->data->authority) {
                        $authority = $authRole->data->authority;
                    }
                }
            }
            static::$_curRoleAuthoration = $authority;
        }
        return static::$_curRoleAuthoration;
    }
    
    public static function getRolesArray($forUserId = 0) {
        $authoraty = self::getCurRoleAuthoration();
        $cdb = \backend\models\Rbac_role::find();
        if ($forUserId > 0 && !\Yii::$app->user->isGuest && \Yii::$app->user->id == $forUserId) {
            $cdb->where(['<=', 'authority', $authoraty]);
        }
        else {
            $cdb->where(['<', 'authority', $authoraty]);
        }
        
        $arrRows = $cdb->all();
        $arrData = [];
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->role_name;
        }
        return $arrData;
    }
    
    public static function getCurAuthRoleDisplayName() {
        $officeId = self::getAuthorizedOfficeId();
        $officeName = '';
        $roleName = '';
        if ($officeId > 0) {
            $objOffice = \common\models\Pro_office::findById($officeId);
            if ($objOffice) {
                $officeName = $objOffice->shortname;
            }
        }
        
        if (!\Yii::$app->user->isGuest) {
            $authRoles = \Yii::$app->authManager->getRolesByUser(\Yii::$app->user->id);
            foreach ($authRoles as $authRole) {
                $roleName .= (empty($roleName) ? '' : ',').$authRole->description;
            }
        }
        
        if (!empty($officeName)) {
            $roleName .= "({$officeName})";
        }
        
        return $roleName;
    }
    
    public static function getAdminAuthOfficeDisplayName() {
        $officeId = self::getAuthorizedOfficeId();
        $officeName = \Yii::t('carrental', 'Not authorized office');
        if ($officeId > 0) {
            $objOffice = \common\models\Pro_office::findById($officeId);
            if ($objOffice) {
                $officeName = $objOffice->shortname;
            }
        }
        elseif ($officeId == \common\components\OfficeModule::HEAD_OFFICE_ID) {
            $officeName = \Yii::t('locale', 'Head office');
        }
        return $officeName;
    }
    
    public static function getAdminNameById($adminId) {
        if ($adminId) {
            $objAdmin = \backend\models\Rbac_admin::findById($adminId);
            if ($objAdmin) {
                return $objAdmin->username;
            }
        }
        return '';
    }
    
}

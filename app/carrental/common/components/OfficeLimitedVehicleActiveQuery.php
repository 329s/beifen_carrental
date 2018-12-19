<?php

namespace common\components;

class OfficeLimitedVehicleActiveQuery extends OfficeLimitedActiveQuery
{
    
    public function verifyOfficeLimitation() {
        $officeId = \backend\components\AdminModule::getAuthorizedVehicleOfficeId();
        
        if ($officeId >= 0) {
            $authoration = \backend\components\AdminModule::getCurRoleAuthoration();
            if ($authoration < \backend\models\Rbac_role::AUTHORITY_OFFICE_MANAGER) {
                $officeCondition = \common\models\Pro_office::getOfficeIdsForOfficeLimitedCondition($officeId, false);
                $this->applyOfficeLimitation($officeCondition);
            }
        }
    }
    
}

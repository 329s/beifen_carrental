<?php

namespace common\components;

class ActivitiesModule {
    
    public static function getActivityImageTypesArray() {
        return \common\models\Pro_activity_image::getTypesArray();
    }
    
}

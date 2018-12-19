<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

/**
 * Description of SysmaintenanceService
 *
 * @author kevin
 */
class SysmaintenanceService {
    //put your code here
    
    public static function isMaintenance() {
        $val = false;
        if (isset(\Yii::$app->params['maintenancing'])) {
            $val = \common\helpers\Utils::boolvalue(\Yii::$app->params['maintenancing']);
        }
        return $val;
    }
    
    public static function getSkipMaintenanceLockControllers() {
        return [
            'sysmaintenance' => 1,
            'test' => 1,
            'payment',
        ];
    }
    
    public static function verifyMaintenanceStatus($action) {
        $arr = [true, 'success'];
        if (static::isMaintenance()) {
            $skipControllers = static::getSkipMaintenanceLockControllers();
            if (!isset($skipControllers[$action->controller->getUniqueId()])) {
                $arr[0] = false;
                $arr[1] = \Yii::t('locale', 'Sorry, the system is on maintenance now, please try it later.');
                $curTie = time();
                $finishTime = isset(\Yii::$app->params['maintenancefinishtime']) ? strtotime(\Yii::$app->params['maintenancefinishtime']) : 0;
                $delta = ($finishTime > $curTie) ? ($finishTime - $curTie) : 0;
                if ($delta) {
                    $arr[1] .= \Yii::t('locale', 'The system is expected to open after {text}.', ['text'=> \common\helpers\Utils::humanDeltaTime($delta)]);
                }
            }
        }
        return $arr;
    }
    
}

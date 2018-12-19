<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

/**
 * Description of DistanceService
 *
 * @author kevin
 */
class DistanceService {
    
    const UNIT_METER = 0;
    const UNIT_KILOMETER = 1;
    
    /**
     * get distance between two coordinates
     * @param string $coordinates1
     * @param string $coordinates2
     * @param integer $unit
     * @return array [distance, message] if distance less than zero, that means an error occurs
     * @throws \Exception
     */
    public static function getDistanceByCoordinates($coordinates1, $coordinates2, $unit = DistanceService::UNIT_KILOMETER)
    {
        if (is_array($coordinates1) && isset($coordinates1[0]) && isset($coordinates1[1])) {
            $coordinates1 = $coordinates1[0].','.$coordinates1[1];
        }
        if (is_array($coordinates2) && isset($coordinates2[0]) && isset($coordinates2[1])) {
            $coordinates2 = $coordinates2[0].','.$coordinates2[1];
        }
        
        $map = MapApiGaode::create();
        
        $result = $map->getDistance($coordinates1, $coordinates2);
        $distance = $result[0];
        $desc = $result[1];
        
        if ($distance > 0) {
            if ($unit != static::UNIT_METER) {
                if ($unit == static::UNIT_KILOMETER) {
                    $distance = round($distance / 1000);
                }
                else {
                    throw new \Exception("Giving invalid unit type:{$unit}");
                }
            }
        }
        
        return [$distance, $desc];
    }
    
    /**
     * get distance between an addres and a coordinate
     * @param string $address
     * @param string $coordinate
     * @return array [distance, message] if distance less than zero, that means an error occurs
     */
    public static function getDistanceBetweenAddressToCoordate($address, $coordinate) {
        $arrResult = [-1, 'ERROR'];
        if (empty($address)) {
            $arrResult[1] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Address')]);
            return $arrResult;
        }
        
        $map = MapApiGaode::create();
        
        $arrCoordinateResult = $map->getCoordinateByAddress($address);
        if ($arrCoordinateResult[0] === false) {
            $arrResult[1] = \Yii::t('locale', 'Please fill the right and complete address!');
            return $arrResult;
        }
        
        return static::getDistanceByCoordinates($arrCoordinateResult[0], $coordinate);
    }
    
}

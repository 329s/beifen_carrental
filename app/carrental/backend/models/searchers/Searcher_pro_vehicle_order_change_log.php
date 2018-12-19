<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\models\searchers;

/**
 * Description of Searcher_pro_vehicle_order_change_log
 *
 * @author kevin
 */
class Searcher_pro_vehicle_order_change_log extends \common\helpers\ActiveSearcherModel
{
    
    public $serial = '';
    
    public function rules() {
        return [
            [['serial'], 'safe'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_order_change_log();
        return $model;
    }
    
    public function getCustomConditions() {
        return [
            'serial' => ['like', 'serial', $this->serial],
        ];
    }
    
}

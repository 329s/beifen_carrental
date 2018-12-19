<?php

namespace backend\models\searchers;

/**
 * Description of Searcher_pro_inner_applying
 *
 * @author kevin
 */
class Searcher_pro_inner_applying extends \common\helpers\ActiveSearcherModel
{
    public $type;
    public $status;
    public $office_id;
    public $applyer;
    public $plate_number;
    public $applyed_time;
    
    public function rules() {
        return [
            [['type', 'status', 'office_id'], 'integer'],
            [['applyer', 'plate_number'], 'string'],
            [['applyed_time'], 'date'],
        ];
    }
    
    public function attributeLabels() {
        $labels = parent::attributeLabels();
        $labels['applyed_time'] = \Yii::t('carrental', 'Applyed time');
        return $labels;
    }
    
    public function getActiveRecordModel() {
        $model = new \backend\models\Pro_inner_applying();
        return $model;
    }
    
    public function getCustomConditions() {
        $arrConditions = [];
        if ($this->applyer) {
            $arrConditions['applyer'] = ['like', 'applyer', $this->applyer];
        }
        if ($this->plate_number) {
            $arrConditions['plate_number'] = ['like', 'plate_number', $this->plate_number];
        }
        if ($this->applyed_time) {
            $startTime = \common\helpers\Utils::toTimestamp($this->applyed_time.' 00:00:00');
            $endTime = \common\helpers\Utils::toTimestamp($this->applyed_time.' 23:59:59');
            $arrConditions['applyed_time'] = ['and', ['>=', 'created_at', $startTime], ['<=', 'created_at', $endTime]];
        }
        return $arrConditions;
    }
    
}

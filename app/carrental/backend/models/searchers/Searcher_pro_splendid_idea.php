<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\models\searchers;

/**
 * Description of Searcher_pro_splendid_idea
 *
 * @author kevin
 */
class Searcher_pro_splendid_idea extends \common\helpers\ActiveSearcherModel
{
    public $type;
    public $status;
    public $keyword;
    
    public function rules() {
        return [
            [['type', 'status'], 'integer'],
            [['keyword'], 'string'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_splendid_idea();
        return $model;
    }
    
    public function getCustomConditions() {
        $arrCondition = [];
        if ($this->keyword) {
            $arrCondition['keyword'] = ['like', 'title', $this->keyword];
        }
        return $arrCondition;
    }
    
    public function getPagerInfo() {
        $pager = parent::getPagerInfo();
        if (!$pager) {
            $pager = $this->setPagerInfo(['pageSize' => 5]);
        }
        return $pager;
    }
    
}

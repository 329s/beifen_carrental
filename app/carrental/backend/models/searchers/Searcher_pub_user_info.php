<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\models\searchers;

/**
 * Description of Searcher_pub_user_info
 *
 * @author kevin
 */
class Searcher_pub_user_info extends \common\helpers\ActiveSearcherModel
{
    public $maxstatus;
    public $office_id;
    public $name;
    public $telephone;
    public $created_at;
    public $e_time;
    
    public function rules() {
        return [
            [['maxstatus', 'office_id'], 'integer'],
            [['name', 'telephone'], 'string'],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pub_user_info();
        return $model;
    }
    
    public function getCustomConditions() {
        $arrCondition = [];
        if ($this->name) {
            $arrCondition['name'] = ['like', 'name', $this->name];
        }
        if ($this->telephone) {
            $arrCondition['telephone'] = ['like', 'telephone', $this->telephone];
        }
        
        $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if ($authOfficeId != \common\components\OfficeModule::HEAD_OFFICE_ID) {
            if ($authOfficeId > 0) {
                $arrCondition['office_id'] = ['belong_office_id' => \common\components\OfficeModule::isOfficeIdAuthorized($this->office_id) ? $this->office_id : $authOfficeId];
            }
            else {
                $arrCondition['office_id'] = ['id'=>0];
            }
        }
        else if ($this->office_id) {
            $arrCondition['office_id'] = ['belong_office_id' => $this->office_id];
        }
        
        if ($this->maxstatus) {
            $arrCondition['maxstatus'] = ['<=', 'credit_level', $this->maxstatus];
        }
        
        
        if($this->created_at){
            $arrCondition['created_at'] = ['>=', 'created_at', strtotime($this->created_at)];
        } 
		if(!empty($this->e_time)){
            $arrCondition['e_time'] = ['<=', 'created_at', strtotime($this->e_time.' 23:59:59')];
        }
        return $arrCondition;
    }
    
}

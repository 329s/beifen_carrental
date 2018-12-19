<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\helpers;

/**
 * Description of ActiveSearcherModel
 *
 * @author kevin
 */
class ActiveSearcherModel extends ActiveFormModel
{
    
    private $__pagerInfo = null;
    private $__sorterInfo = null;
    
    public function formName() {
        return 'S';
    }
    
    /**
     * @param array $params
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public function search($params, $searcherFormName = null) {
        $model = $this->getActiveRecordModel();
        $dataConfig = [];
        if ($this->getPagerInfo() || $this->getPagerInfo() === false) {
            $dataConfig['pagination'] = $this->getPagerInfo();
        }
        if ($this->getSorterInfo()) {
            $dataConfig['sort'] = $this->getSorterInfo();
        }
        $dataProvider = $model::createDataProvider($dataConfig);
        
        if (!($this->load($params, $searcherFormName) && $this->validate())) {
            return $dataProvider;
        }
        
        $dataProvider->setFilterParams($this);
        
        $attributes = $this->getAttributes();
        $customConditions = $this->getCustomConditions();
        foreach ($attributes as $k => $v) {
            if ($v) {
                if (isset($customConditions[$k])) {
                    if ($customConditions[$k]) {
                        $dataProvider->query->andFilterWhere($customConditions[$k]);
                    }
                    unset($customConditions[$k]);
                }
                elseif ($model->hasAttribute($k)) {
                    $dataProvider->query->andFilterWhere([$k => $v]);
                }
            }
        }
        foreach ($customConditions as $k => $cond) {
            if ($cond) {
                $dataProvider->query->andFilterWhere($cond);
            }
        }
        
        return $dataProvider;
    }
    
    /**
     * @return array conditions marked by attribute
     */
    public function getCustomConditions() {
        return [];
    }
    
    public function beforeSearch() {
        // 
    }
    
    public function getPagerInfo() {
        return $this->__pagerInfo;
    }
    
    public function setPagerInfo($config) {
        $this->__pagerInfo = $config;
        return $this->__pagerInfo;
    }
    
    public function getSorterInfo() {
        return $this->__sorterInfo;
    }
    
    public function setSorterInfo($config) {
        $this->__sorterInfo = $config;
        return $this->__sorterInfo;
    }
    
    public function genUrlParams($params = []) {
        if (!empty($params)) {
            $this->load($params);
        }
        
        $arrParams = [];
        $formName = $this->formName();
        foreach ($this->getAttributes() as $k => $v) {
            if ($k && $v) {
                $key = (empty($formName) ? $k : "{$formName}[{$k}]");
                $arrParams[$key] = $v;
            }
        }
        
        return $arrParams;
    }
    
    public function loadPagination($params) {
        $pageSize = \yii\helpers\ArrayHelper::getValue($params, 'per-page', null);
        if (!$pageSize) {
            $pageSize = \yii\helpers\ArrayHelper::getValue($params, 'rows', 20);
        }
        $page = intval(\yii\helpers\ArrayHelper::getValue($params, 'page', 1));
        if ($page < 1) {
            $page = 1;
        }
        $this->setPagerInfo(['pageSize'=>intval($pageSize), 'page'=>$page-1]);
    }
    
    public function loadSort($params) {
        $sort = trim(\yii\helpers\ArrayHelper::getValue($params, 'sort', null));
        if (!empty($sort)) {
            $sortDirection = \yii\helpers\ArrayHelper::getValue($params, 'order', null);
            if ($sortDirection && $sortDirection == 'desc') {
                if (strncmp($sort, '-', 1) != 0) {
                    $this->setSorterInfo(['attributes'=>[$sort], 'params'=>['sort'=>'-'.$sort]]);
                }
            }
        }
    }
    
}

<?php

namespace common\helpers;

/**
 * Description of ExtendActiveDataProvider
 *
 * @author kevin
 */
class ExtendActiveDataProvider extends \yii\data\ActiveDataProvider
{
    
    /**
     * related data marked by attribute
     * @var array 
     */
    public $formattingAttributes = [];
    
    /**
     * 
     * @var array
     */
    public $findAttributes = [];
    
    public $prepareDatas = null;
    
    private $_filterParams = [];
    
    public $originModelDatas = [];
    
    public function init() {
        parent::init();
        
        $this->formattingAttributes = $this->_prepareFormatterAttributesArray($this->formattingAttributes);
        $arr = $this->_prepareFormatterAttributesArray($this->findAttributes);
        foreach ($arr as $k => $v) {
            if (!isset($this->formattingAttributes[$k])) {
                $this->formattingAttributes[$k] = [];
            }
        }
    }
    
    public function setFilterParams($searcherModel) {
        $params = $searcherModel->getAttributes();
        $formName = $searcherModel->formName();
        foreach ($params as $k => $v) {
            if ($v !== '') {
                $key = empty($formName) ? $k : "{$formName}[{$k}]";
                $this->_filterParams[$key] = $v;
            }
        }
    }
    
    public function getFilterParams() {
        return $this->_filterParams;
    }
    
    public function setModels($models) {
        parent::setModels($models);
        $this->postPrepareModels($models);
    }
    
    protected function prepareModels() {
        $models = parent::prepareModels();
        $this->postPrepareModels($models);
        return $models;
    }
    
    protected function postPrepareModels($models) {
        $this->originModelDatas = [];
        foreach ($models as $k => $v) {
            $this->originModelDatas[$k] = array_merge([], (is_array($v)?$v:$v->getAttributes()));
        }
        
        if ($this->prepareDatas instanceof \Closure) {
            call_user_func($this->prepareDatas, $models, $this);
        }
        
        $this->processFindAttributes($models);
    }


    protected function processFindAttributes($models) {
        if (!empty($this->findAttributes)) {
            foreach ($this->findAttributes as $attr => $cfg) {
                $values = [];
                $attrs = $this->_explodeAttributes($attr);
                foreach ($models as $model) {
                    foreach ($attrs as $attrx) {
                        if (!isset($values[$model[$attrx]])) {
                            $values[$model[$attrx]] = 1;
                        }
                    }
                }

                if (empty($values)) {
                    continue;
                }
                $valueindexes = array_keys($values);
                $foundArray = [];
                // find
                if ($cfg instanceof \Closure) {
                    $foundArray = call_user_func($cfg, $valueindexes);
                }
                else {
                    $groupBy = false;
                    $findByPrimaryKeys = false;
                    $extend = false;
                    if (is_array($cfg)) {
                        $className = $cfg['class'];
                        $idField = \yii\helpers\ArrayHelper::getValue($cfg, 'idField', 'id');
                        $textField = \yii\helpers\ArrayHelper::getValue($cfg, 'textField', 'name');
                        $groupBy = \yii\helpers\ArrayHelper::getValue($cfg, 'groupBy', false);
                        $extend = \yii\helpers\ArrayHelper::getValue($cfg, 'extend', false);
                        if (isset($cfg['findByPrimaryKeys']) && $cfg['findByPrimaryKeys']) {
                            $findByPrimaryKeys = true;
                            $valueindexes = $this->prepareKeys($models);
                        }
                    }
                    else {
                        $className = $cfg;
                        $idField = 'id';
                        $textField = 'name';
                    }

                    $tmpQuery = $className::find(true);
                    $tmpQuery->select([$idField, $textField]);
                    $tmpQuery->where([$idField=>$valueindexes]);
                    if ($groupBy) {
                        $tmpQuery->addGroupBy($groupBy);
                    }
                    $arrRows = $tmpQuery->createCommand()->queryAll();
                    if (is_array($extend)) {
                        foreach ($extend as $_k => $_v) {
                            $foundArray[$_k] = $_v;
                        }
                    }
                    foreach ($arrRows as $row) {
                        $foundArray[$row[$idField]] = $row[$textField];
                    }
                    
                    if ($findByPrimaryKeys) {
                        $pk = $this->key;
                        if (!$pk) {
                            if ($this->query instanceof \yii\db\ActiveQueryInterface) {
                                $class = $this->query->modelClass;
                                $pks = $class::primaryKey();
                                if (count($pks) === 1) {
                                    $pk = $pks[0];
                                }
                            }
                        }
                        if ($pk) {
                            foreach ($models as $i => $model) {
                                if (isset($foundArray[$model[$pk]])) {
                                    foreach ($attrs as $attrx) {
                                        $models[$i][$attrx] = $foundArray[$model[$pk]];
                                    }
                                }
                            }
                        }
                        foreach ($attrs as $attrx) {
                            unset($this->formattingAttributes[$attrx]);
                        }
                        continue;
                    }
                }
                
                foreach ($attrs as $attrx) {
                    $this->formattingAttributes[$attrx] = $foundArray;
                }
            }
        }
    }


    private function _prepareFormatterAttributesArray($array) {
        $result = [];
        if (!empty($array)) {
            foreach ($array as $k => $v) {
                $attrs = $this->_explodeAttributes($k);
                foreach ($attrs as $_k) {
                    $result[$_k] = $v;
                }
            }
        }
        return $result;
    }
    
    private function _explodeAttributes($attributesString) {
        $attrs0 = explode(',', $attributesString);
        $attrs = [];
        foreach ($attrs0 as $k) {
            $k = trim($k);
            if ($k != '') {
                $attrs[] = $k;
            }
        }
        return $attrs;
    }

    public function getAttributeFormatter($attribute) {
        if (isset($this->formattingAttributes[$attribute])) {
            return function($model, $key, $index, $column) {
                $field = $column->attribute;
                if (!isset($model[$field])) {
                    return '';
                }
                $dataProvider = $column->grid->dataProvider;
                if ($dataProvider instanceof \common\helpers\ExtendActiveDataProvider) {
                    if (isset($dataProvider->formattingAttributes[$field])) {
                        return DataFormationHelper::formatModelAttributeValue($model, $field, $dataProvider->formattingAttributes[$field]);
                    }
                }
                return $model[$field];
            };
        }
        return null;
    }
    
    public function manualFormatModelValues($columns = []) {
        $models = $this->getModels();
        if (empty($columns)) {
            $columns = array_keys($this->formattingAttributes);
        }
        foreach ($columns as $column) {
            if (is_string($column)) {
                if (isset($this->formattingAttributes[$column])) {
                    foreach ($models as $j => $model) {
                        $models[$j][$column] = DataFormationHelper::formatModelAttributeValue($model, $column, $this->formattingAttributes[$column]);
                    }
                }
            }
            elseif (is_array($column)) {
                // TODO
            }
        }
    }
    
    public function setModelsAsArray($isAsArray = true) {
        if ($this->query instanceof \yii\db\ActiveQueryInterface) {
            $this->query->asArray($isAsArray);
        }
    }
    
}

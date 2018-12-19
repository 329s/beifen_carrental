<?php
namespace common\helpers;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ActiveRecordModel extends \yii\db\ActiveRecord
{
    public function getErrorDebugString() {
        $errors = $this->getErrors();
        $errTexts = [];
        foreach ($errors as $field => $fieldErrors) {
            $errTexts[] = \Yii::t('locale', 'There are errors on {field}: ', ['field'=>$field]) . implode(' ', $fieldErrors);
        }
        
        return implode("\n", $errTexts);
    }
    
    public function getErrorAsHtml() {
        $errors = $this->getErrors();
        $errTexts = [];
        foreach ($errors as $field => $fieldErrors) {
            //$errTexts[] = \Yii::t('locale', 'There are errors on {field}: ', ['field'=>$field]) . implode(' ', $fieldErrors);
            $errTexts[] = implode(' ', $fieldErrors);
        }
        
        return implode("<br />", $errTexts);
    }
    
    /**
     * @inheritdoc
     * @return \yii\db\ActiveQuery the newly created [[\yii\db\ActiveQuery]] instance.
     */
    public static function find($skipAuthorityLimit = false)
    {
        return \Yii::createObject(\yii\db\ActiveQuery::className(), [get_called_class()]);
    }
    
    /**
     * @inheritdoc
     * @param integer|string $id
     * @param string $idField
     * @return static ActiveRecord instance matching the id.
     */
    public static function findById($id, $idField = 'id') {
        $query = static::find(true);
        $query->where([$idField => $id]);
        return $query->one();
    }
    
    /**
     * get current table max auto_increament id
     * @return integer
     */
    public static function getAutoIncreamentId($field = 'id') {
        $id = 0;
        $cdb = static::find(true);
        $c = 0;
        do
        {
            $cdb->select("MAX(`{$field}`) as `id`");
            $r = $cdb->one();
            if ($r) {
                $id = $r['id'] + 1;
                break;
            }
            $c++;
        }while($c < 2);
        
        return $id;
    }
    
    /**
     * 
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public static function createDataProvider($config = []) {
        if (!isset($config['query'])) {
            $config['query'] = static::find();
        }
        return new \common\helpers\ExtendActiveDataProvider($config);
    }
    
    /**
     * 
     * @param array $config
     * @return array ['class'=>classname, 'idField'=>(optional) 'textField'=>(optional)]
     */
    public static function createFindIdNamesArrayConfig($config = []) {
        return array_merge(['class'=> static::className()], $config);
    }

}

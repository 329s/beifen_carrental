<?php

namespace backend\modules\rbac\models;

/**
 * Description of BaseModel
 *
 * @author kevin
 */
class BaseModel extends \common\helpers\ActiveRecordModel
{
    
    static $tablePrefix = 'rbac_';
    
    public static function tableName() {
        return '{{%' . static::$tablePrefix . \yii\helpers\Inflector::camel2id(\yii\helpers\StringHelper::basename(get_called_class()), '_') . '}}';
    }
    
}

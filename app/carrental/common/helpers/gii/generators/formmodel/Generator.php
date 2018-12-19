<?php

namespace common\helpers\gii\generators\formmodel;

/**
 * Description of Generator
 *
 * @author kevin
 */
class Generator extends \common\helpers\gii\generators\model\Generator
{
    /**
     * @var array a list of available code templates. The array keys are the template names,
     * and the array values are the corresponding template paths or path aliases.
     */
    public $templates = [
        'default' => '@common/helpers/gii/generators/formmodel/form',
    ];
    
    public $ns = 'backend\models';
    public $baseClass = 'common\helpers\ActiveFormModel';
    public $queryNs = 'backend\models';
    public $activeRecordClass;
    
    public $skipColumns = ['created_at', 'updated_at', 'created_by', 'updated_by', 'edit_user_id', 'app_id', 'application_id'];
    
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Form Model Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an ActiveFormModel class for the specified database table.';
    }
    
    public function rules() {
        return [
            [['db', 'ns', 'tableName', 'modelClass', 'baseClass', 'queryNs'], 'filter', 'filter' => 'trim'],
            [['ns', 'queryNs'], 'filter', 'filter' => function($value) { return trim($value, '\\'); }],

            [['db', 'ns', 'tableName', 'baseClass', 'queryNs', 'activeRecordClass'], 'required'],
            [['db', 'modelClass'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['ns', 'baseClass', 'queryNs', 'activeRecordClass'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['tableName'], 'match', 'pattern' => '/^([\w ]+\.)?([\w\* ]+)$/', 'message' => 'Only word characters, and optionally spaces, an asterisk and/or a dot are allowed.'],
            [['db'], 'validateDb'],
            [['ns', 'queryNs'], 'validateNamespace'],
            [['tableName'], 'validateTableName'],
            [['modelClass'], 'validateModelClass', 'skipOnEmpty' => false],
            [['baseClass'], 'validateClass', 'params' => ['extends' => \common\helpers\ActiveFormModel::className()]],
            [['activeRecordClass'], 'validateClass', 'params' => ['extends' => \yii\db\ActiveRecord::className()]],
            [['generateRelations'], 'in', 'range' => [self::RELATIONS_NONE, self::RELATIONS_ALL, self::RELATIONS_ALL_INVERSE]],
            [['generateLabelsFromComments', 'useTablePrefix', 'useSchemaName', 'generateQuery'], 'boolean'],
            [['enableI18N'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
        ];
    }

    public function generateRules($table) {
        $types = [];
        $lengths = [];
        $primaryKey = 'id';
        foreach ($table->columns as $column) {
            if ($column->isPrimaryKey) {
                $primaryKey = $column->name;
            }
            if ($column->autoIncrement) {
                continue;
            }
            elseif (in_array($column->name, $this->skipColumns)) {
                continue;
            }
            if (!$column->allowNull && $column->defaultValue === null) {
                $types['required'][] = $column->name;
            }
            switch ($column->type) {
                case \yii\db\Schema::TYPE_SMALLINT:
                case \yii\db\Schema::TYPE_INTEGER:
                case \yii\db\Schema::TYPE_BIGINT:
                    $types['integer'][] = $column->name;
                    break;
                case \yii\db\Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case \yii\db\Schema::TYPE_FLOAT:
                case 'double': // Schema::TYPE_DOUBLE, which is available since Yii 2.0.3
                case \yii\db\Schema::TYPE_DECIMAL:
                case \yii\db\Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case \yii\db\Schema::TYPE_DATE:
                case \yii\db\Schema::TYPE_TIME:
                case \yii\db\Schema::TYPE_DATETIME:
                case \yii\db\Schema::TYPE_TIMESTAMP:
                    $types['safe'][] = $column->name;
                    break;
                default: // strings
                    if ($column->size > 0) {
                        $lengths[$column->size][] = $column->name;
                    } else {
                        $types['string'][] = $column->name;
                    }
            }
        }
        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }

        $db = $this->getDbConnection();
        
        // Unique indexes rules
        try {
            $uniqueIndexes = $db->getSchema()->findUniqueIndexes($table);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);
                    $targetClassName = $this->activeRecordClass ? $this->activeRecordClass : "\\common\\models\\".ucfirst($this->tableName);
                    if ($attributesCount === 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique', 'targetClass' => '{$targetClassName}', 'filter'=>['<>', 'id', \$this->{$primaryKey}]]";
                    } elseif ($attributesCount > 1) {
                        //$labels = array_intersect_key($this->generateLabels($table), array_flip($uniqueColumns));
                        //$lastLabel = array_pop($labels);
                        $columnsList = implode("', '", $uniqueColumns);
                        $labels = [];
                        foreach ($uniqueColumns as $attr) {
                            $labels[] = "\$this->getAttributeLabel('{$attr}')";
                        }
                        $lastLabel = array_pop($labels);
                        $rules[] = "[['$columnsList'], 'unique', 'targetClass' => '{$targetClassName}', 'filter'=>['<>', 'id', \$this->{$primaryKey}], 'targetAttribute' => ['$columnsList'], 'message' => \Yii::t('{$this->messageCategory}', 'The combination of {labels} and {lastLabel} has already been taken.', ['labels'=>implode(',', [". implode(', ', $labels) . "]), 'lastLabel'=>{$lastLabel}])]";
                    }
                }
            }
        } catch (\yii\base\NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }

        // Exist rules for foreign keys
        foreach ($table->foreignKeys as $refs) {
            $refTable = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            $refClassName = $this->generateClassName($refTable);
            unset($refs[0]);
            $attributes = implode("', '", array_keys($refs));
            $targetAttributes = [];
            foreach ($refs as $key => $value) {
                $targetAttributes[] = "'$key' => '$value'";
            }
            $targetAttributes = implode(', ', $targetAttributes);
            $rules[] = "[['$attributes'], 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::className(), 'targetAttribute' => [$targetAttributes]]";
        }

        return $rules;
    }
    
}

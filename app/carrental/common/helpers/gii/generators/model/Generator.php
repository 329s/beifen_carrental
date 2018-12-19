<?php

namespace common\helpers\gii\generators\model;

/**
 * Description of Generator
 *
 * @author kevin
 */
class Generator extends \yii\gii\generators\model\Generator
{
    /**
     * @var array a list of available code templates. The array keys are the template names,
     * and the array values are the corresponding template paths or path aliases.
     */
    public $templates = [
        'activeRecord' => '@common/helpers/gii/generators/model/ar',
        'default' => '@vendor/yiisoft/yii2-gii/generators/model/default',
    ];
    
    public $ns = 'common\models';
    public $baseClass = 'common\helpers\ActiveRecordModel';
    public $queryNs = 'backend\models';
    
    public $skipColumns = ['created_at', 'updated_at', 'created_by', 'updated_by', 'edit_user_id', 'app_id', 'application_id'];
    
    public function generateString($string = '', $placeholders = array()) {
        if ($this->enableI18N) {
            // If there are placeholders, use them
            if (!empty($placeholders)) {
                $ph = ', ' . \yii\helpers\VarDumper::export($placeholders);
            } else {
                $ph = '';
            }
            return "\Yii::t('" . $this->messageCategory . "', '" . addslashes($string) . "'" . $ph . ")";
        }
        return parent::generateString($string, $placeholders);
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

                    if ($attributesCount === 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique', 'filter'=>['<>', 'id', \$this->{$primaryKey}]]";
                    } elseif ($attributesCount > 1) {
                        $labels = array_intersect_key($this->generateLabels($table), array_flip($uniqueColumns));
                        $lastLabel = array_pop($labels);
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[] = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList'], 'filter'=>['<>', 'id', \$this->{$primaryKey}], 'message' => 'The combination of " . implode(', ', $labels) . " and $lastLabel has already been taken.']";
                    }
                }
            }
        } catch (NotSupportedException $e) {
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

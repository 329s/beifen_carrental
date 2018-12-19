<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator common\helpers\gii\generators\formmodel\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

/**
 * This is the active form model class for table "<?= $generator->generateTableName($tableName) ?>".
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
<?php foreach ($tableSchema->columns as $column): ?>
<?php if (!in_array($column->name, $generator->skipColumns)): ?>
    public <?= "\${$column->name}".($column->isPrimaryKey ? ' = 0' : '').";\n" ?>
<?php endif; ?>
<?php endforeach; ?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . ",\n        " ?>];
    }

    public function getActiveRecordModel() {
        $model = new \<?php 
            if ($generator->activeRecordClass) {
                echo $generator->activeRecordClass;
            } else {
                echo 'common\\models\\';
                if (strtolower(substr($className, 0, 5)) == 'form_') {
                    echo ucfirst($className, 5);
                } else {
                    echo $className;
                }
            }
        ?>();
        return $model;
    }

}

<?php

namespace backend\modules\rbac\models;

/**
 * This is the active form model class for table "rbac_permission".
 */
class PermissionForm extends \common\helpers\ActiveFormModel
{
    public $name;
    public $category;
    public $parent;
    public $href;
    public $description;
    public $rule_name;
    public $icon;
    public $icon_traditional;
    public $c_order;
    public $status;
    public $target;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['c_order', 'status'], 'integer'],
            [['name', 'parent', 'rule_name'], 'string', 'max' => 64],
            [['category'], 'string', 'max' => 24],
            [['href', 'description'], 'string', 'max' => 256],
            [['icon', 'icon_traditional', 'target'], 'string', 'max' => 45],
        ];
    }

    public function getActiveRecordModel() {
        $model = new \backend\modules\rbac\models\Permission();
        return $model;
    }

}

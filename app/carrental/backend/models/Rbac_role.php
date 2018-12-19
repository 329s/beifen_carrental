<?php
namespace backend\models;

/**
 * 角色表 
 * @property integer $id
 * @property string $role_name
 * @property integer $status
 * @property integer $authority
 */
class Rbac_role extends \common\helpers\ActiveRecordModel
{
    
    const AUTHORITY_ADMINISTRATOR = 999999;
    const AUTHORITY_DOMAIN_MANAGER = 500;
    const AUTHORITY_OFFICE_MANAGER = 100;
    const AUTHORITY_OPERATOR = 1;
    
    /**
     * Returns the attribute labels.
     * Attribute labels are mainly used in error messages of validation.
     * By default an attribute label is generated using {@link generateAttributeLabel}.
     * This method allows you to explicitly specify attribute labels.
     *
     * Note, in order to inherit labels defined in the parent class, a child class needs to
     * merge the parent labels with child labels using functions like array_merge().
     *
     * @return array attribute labels (name=>label)
     * @see generateAttributeLabel
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'role_name' => \Yii::t('locale', 'Name'),
            'status' => \Yii::t('locale', 'Status'),
            'authority' => \Yii::t('locale', 'Authority'),
            'operation' => \Yii::t('locale', 'Operation'),
        ];
    }

    /**
     * Returns the attribute custom types.
     * Attribute custom types is used for display model data in datagrid specified display options.
     * @return array attribute custom types (name=>['width'=>null, 'data-options'=>null, 'editor'=>null,'formatter'=>null,'sortable'=>true)]
     */
    public function attributeCustomTypes()
    {
        $arrData = [
            'id' => ['data-options' => ['checkbox'=>'true'], 'key' => true],
            'role_name' => ['width' => 100, 'sortable' => 'true', 
                ],
            'authority' => ['width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getAuthoritiesArray(true))." }"
                ],
            'status' => ['width' => 60, 'sortable' => 'true', 'formatter' => <<<EOD
function(value,row){ 
    if (value == 1) {
        return '<font color=\'green\'>' + $.custom.lan.defaults.role.enabled + '</font>';
    } else {
        return '<font color=\'red\'>' + $.custom.lan.defaults.role.disabled + '</font>';
    }
}
EOD
                ],
            'operation' => ['width' => 160, 
                'buttons' => [
                    \Yii::$app->user->can('rbac2/role_edit') ? ['type' => 'dialog', 'url' => \yii\helpers\Url::to(['rbac2/role_edit', 'id'=>'']), 'name' => \Yii::t('locale', 'Edit'), 'title' => \Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit'] : null,
                    \Yii::$app->user->can('rbac2/role_delete') ? ['type' => 'ajax', 'url' => \yii\helpers\Url::to(['rbac2/role_delete', 'id'=>'']), 'name' => \Yii::t('locale', 'Delete'), 'title' => \Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete'] : null,
                    \Yii::$app->user->can('rbac2/role_column') ? ['type' => 'tab', 'url' => \yii\helpers\Url::to(['rbac2/role_column', 'id'=>'']), 'name' => '<font color=red>'. \Yii::t('locale', 'Manage authority') .'</font>', 'title' => \Yii::t('locale', 'Manage authority'), 'paramField' => 'id', 'icon' => ''] : null,
                ],
            ],
        ];
        
        return $arrData;
    }
    
    public static function getAuthoritiesArray($extainsSameAuthoration = false) {
        $arrAuthorations = [
            static::AUTHORITY_OPERATOR => \Yii::t('locale', 'Operator'),
            static::AUTHORITY_OFFICE_MANAGER => \Yii::t('locale', 'Office manager'),
            static::AUTHORITY_DOMAIN_MANAGER => \Yii::t('locale', 'Domain manager'),
            static::AUTHORITY_ADMINISTRATOR => \Yii::t('locale', 'System administrator'),
        ];
        $arrData = [];
        $authoration = \backend\components\AdminModule::getCurRoleAuthoration();
        foreach ($arrAuthorations as $auth => $desc) {
            if ($auth <= $authoration) {
                $arrData[$auth] = $desc;
            }
            elseif ($extainsSameAuthoration && $auth <= $authoration) {
                $arrData[$auth] = $desc;
            }
        }
        return $arrData;
    }
    
    public function genAuthRoleKey() {
        return 'role-'.$this->app_id.'-'.$this->id;
    }
    
    public function genAuthRoleData() {
        return (object)['authority'=> $this->authority, 'app_id'=>$this->app_id, 'role_id'=>$this->id, 'status'=>($this->status==1?0:-1)];
    }
    
}

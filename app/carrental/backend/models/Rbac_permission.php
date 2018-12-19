<?php
namespace backend\models;

/**
 * 角色表 
 * @property integer $id
 * @property string $role_name
 * @property integer $status
 * @property integer $authority
 */
class Rbac_permission extends \common\helpers\ActiveRecordModel
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
            'name' => \Yii::t('locale', 'Node name'),
            'category' => \Yii::t('locale', 'Node category'),
            'parent' => \Yii::t('locale', 'Node parent'),
            'href' => \Yii::t('locale', 'Node href'),
            'description' => \Yii::t('locale', 'Node description'),
            'icon' => \Yii::t('locale', 'Node icon'),
            'icon_traditional' => \Yii::t('locale', 'Node icon traditional'),
            'c_order' => \Yii::t('locale', 'C order'),
            'status' => \Yii::t('locale', 'Status'),
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
           
            'status' => ['width' => 60, 'sortable' => 'true', 'formatter' => <<<EOD
function(value,row){ 
    if (value == 0) {
        return '<font color=\'green\'>' + $.custom.lan.defaults.role.enabled + '</font>';
    } else {
        return '<font color=\'red\'>' + $.custom.lan.defaults.role.disabled + '</font>';
    }
}
EOD
                ],
            'operation' => ['width' => 160, 
                'buttons' => [
					
					array('type' => 'tab', 'url' => \yii\helpers\Url::to(['rbac2/node_index', 'parent_id'=>'']), 'name' => \Yii::t('locale', 'Node check'), 'title' => \Yii::t('locale', 'Node check'), 'paramField' => 'name', 'icon' => '', 'showText'=>true),
					
                    \Yii::$app->user->can('rbac2/node_edit') ? ['type' => 'dialog', 'url' => \yii\helpers\Url::to(['rbac2/node_edit', 'name'=>'']), 'name' => \Yii::t('locale', 'Edit'), 'title' => \Yii::t('locale', 'Edit'), 'paramField' => 'name', 'icon' => 'icon-edit'] : null,
                   \Yii::$app->user->can('rbac2/node_del') ? ['type' => 'ajax', 'url' => \yii\helpers\Url::to(['rbac2/node_del', 'name'=>'']), 'name' => \Yii::t('locale', 'Delete'), 'title' => \Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'name', 'icon' => 'icon-delete'] : null,
                ],
            ],
        ];
        
        return $arrData;
    }
    
    public function genAuthColumnKey() {
        return 'node-'.$this->name;
    }
    
    public function genAuthColumnData() {
        return (object)[
			'name'=>$this->name,
			'category'=>$this->category,
			'parent'=>$this->parent,
			'href'=>$this->href,
			'icon'=>$this->icon,
			'icon_traditional'=>$this->icon_traditional,
			'description'=>$this->description,
			'c_order'=>$this->c_order,
			'status'=>($this->status)
		];
    }
	
	public static function getIdentityTypesArray() {
        return [
            \common\components\Consts::ID_TYPE_MEMU => \Yii::t('locale', 'Node menu'),
            \common\components\Consts::ID_TYPE_ACTION => \Yii::t('locale', 'Node action'),
            \common\components\Consts::ID_TYPE_NODE => \Yii::t('locale', 'Node node'),
           
        ];
    }
    
}

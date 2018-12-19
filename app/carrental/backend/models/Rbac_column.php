<?php
namespace backend\models;

/**
 * 角色表 
 * @property integer $id
 * @property string $role_name
 * @property integer $status
 * @property integer $authority
 */
class Rbac_column extends \common\helpers\ActiveRecordModel
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
            'column_code' => \Yii::t('locale', 'Column code'),
            'column_name' => \Yii::t('locale', 'Column name'),
            'column_url' => \Yii::t('locale', 'Column url'),
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
                    \Yii::$app->user->can('rbac2/column_edit') ? ['type' => 'dialog', 'url' => \yii\helpers\Url::to(['rbac2/column_edit', 'id'=>'']), 'name' => \Yii::t('locale', 'Edit'), 'title' => \Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit'] : null,
                   \Yii::$app->user->can('rbac2/column_del') ? ['type' => 'ajax', 'url' => \yii\helpers\Url::to(['rbac2/column_del', 'id'=>'']), 'name' => \Yii::t('locale', 'Delete'), 'title' => \Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete'] : null,
                   
                ],
            ],
        ];
        
        return $arrData;
    }
    
    public function genAuthColumnKey() {
        return 'column-'.$this->id;
    }
    
    public function genAuthColumnData() {
        return (object)[ 
			'column_code'=>$this->column_code,
			'column_name'=>$this->column_name,
			'column_url'=>$this->column_url,
			'column_icon'=>$this->column_icon,
			'c_order'=>$this->c_order,
			'id'=>$this->id,
			'status'=>($this->status==1?0:-1)
		];
    }
    
}

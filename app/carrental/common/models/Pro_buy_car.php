<?php
namespace common\models;

/**
 * 角色表 
 * @property integer $id
 * @property string $role_name
 * @property integer $status
 * @property integer $authority
 */
class Pro_buy_car extends \common\helpers\ActiveRecordModel
{
	const STATUS_NORMAL = 0;
    const STATUS_PROCESSED = 1;
    const STATUS_Man = 0;
    const STATUS_Woman = 1;
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
            'name' => \Yii::t('locale', 'Buy car name'),
            'mobile' => \Yii::t('locale', 'Buy car mobile'),
            'sex' => \Yii::t('locale', 'Buy car sex'),
            'car_models' => \Yii::t('locale', 'Buy car models'),
            'buy_city' => \Yii::t('locale', 'Buy car city'),
            'add_time' => \Yii::t('locale', 'Buy car time'),
            'status' => \Yii::t('locale', 'Status'),
            'operation' => \Yii::t('locale', 'Operation'),
        ];
    }
	
	 /**
     * Returns the attribute custom types.
     * Attribute custom types is used for display model data in datagrid specified display options.
     * @return array attribute custom types (name=>array('width'=>null, 'data-options'=>null, 'editor'=>null,'formatter'=>null,'sortable'=>true))
     */
    public function attributeCustomTypes()
    {
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'name' => array('width' => 120),
            'mobile' => array('width' => 120),
            'sex' => array('width' => 100,'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getSexArray())." }"),
            'car_models' => array('width' => 100),
            'buy_city' => array('width' => 100),
            'add_time' => array('width' => 120, 'sortable' => 'true','formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'status' => array('width' => 60,'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"),
            'operation' => ['width' => 60, 
                'buttons' => [
                    \Yii::$app->user->can('user/buycar_look') ? ['type' => 'dialog', 'url' => \yii\helpers\Url::to(['user/buycar_look', 'id'=>'']), 'name' => \Yii::t('locale', 'Edit'), 'title' => \Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit'] : null,
                ],
            ],
        );
    }
	
	public static function getStatusArray() {
        return [
            static::STATUS_NORMAL => \Yii::t('locale', 'Normal'), 
            static::STATUS_PROCESSED => \Yii::t('locale', 'Processed'), 
        ];
    }
	
	public static function getSexArray() {
        return [
            static::STATUS_Man => \Yii::t('locale', 'Man'), 
            static::STATUS_Woman => \Yii::t('locale', 'Woman'), 
        ];
    }
    
    
    
}

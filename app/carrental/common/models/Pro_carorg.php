<?php
namespace common\models;

/**
 * 角色表 
 * @property integer $id
 * @property string $role_name
 * @property integer $status
 * @property integer $authority
 */
class Pro_carorg extends \common\helpers\ActiveRecordModel
{
 
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
        ];
    }
	
}

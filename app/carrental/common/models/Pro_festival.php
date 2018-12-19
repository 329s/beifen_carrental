<?php

namespace common\models;

use Yii;

/**
 * Festival model
 *
 * @property integer $id
 * @property string $name
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $alldays_required
 * @property integer $status
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_festival extends \common\helpers\ActiveRecordModel
{
    const STATUS_NORMAL = 0;
    const STATUS_CLOSED = -10;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            \common\helpers\behaviors\EditorBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_NORMAL],
            ['status', 'in', 'range' => [self::STATUS_NORMAL, self::STATUS_CLOSED]],
        ];
    }

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
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'name' => Yii::t('locale', 'Festival name'),
            'start_time' => Yii::t('locale', 'Festival begin time'),
            'end_time' => Yii::t('locale', 'Festival end time'),
            'alldays_required' => Yii::t('carrental', 'All festival days should rent at the time'),
            'status' => Yii::t('locale', 'Status'),
            'edit_user_id' => Yii::t('locale', 'Edit user'),
            'created_at' => Yii::t('locale', 'Create time'),
            'updated_at' => Yii::t('locale', 'Update time'),
            'operation' => Yii::t('locale', 'Operation'),
        );
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
            'name' => array('width' => 100, 'sortable' => 'true'),
            'start_time' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.formatTime('yyyy-MM-dd', value);}"),
            'end_time' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.formatTime('yyyy-MM-dd', value);}"),
            'alldays_required' => array('width'=>100,
                'formatter' => "function(value,row) { if (value>0) { return '<span style=\\'color:green\\'>√</span>'; } return ''; }"),
            'status' => array('width' => 60,
                'formatter' => "function(value,row){\n".
                "    if (value == ".self::STATUS_NORMAL.") { return '<span style=\\'color:green\\'>' + $.custom.lan.defaults.role.enabled + '</span>'; }\n".
                "    else if (value == ".self::STATUS_CLOSED.") { return '<span style=\\'color:red\\'>' + $.custom.lan.defaults.role.disabled + '</span>'; }\n".
                "    return '';\n".
                "}"),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                    \Yii::$app->user->can('options/festival_edit') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['options/festival_edit', 'id'=>'']), 'name' => Yii::t('locale', 'Edit'), 'title' => Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit') : null,
                ),
            ),
        );
    }
    
    public function isTimeMatch($now) {
        return ($now >= $this->start_time) && ($now <= $this->end_time);
    }
    
    public function isContainsTime($startTime, $endTime) {
        return ($startTime < $this->end_time) && ($endTime > $this->start_time);
    }
    
    public function isValidRentTime($startTime, $endTime) {
        if ($this->alldays_required > 0 && $this->isContainsTime($startTime, $endTime)) {
            $rentTimeData = Pri_renttime_data::create($this->start_time, $this->end_time);
            $rentTimeData2 = Pri_renttime_data::create($startTime, $endTime);
            if ($rentTimeData2->days < $rentTimeData->days) {
                \Yii::error("festival:{$this->name} check is rent time valid by start_time:{$startTime} end_time:{$endTime} failed, the calculated rent_days:{$rentTimeData2->days} less than festival days limit:{$rentTimeData->days}");
                return false;
            }
            $startLimit = strtotime(date('Y-m-d', $this->start_time).' 23:59:59');
            $endLimit = strtotime(date('Y-m-d', $this->end_time).' 00:00:01');
            
            if (/*$startTime > $startLimit ||*/ $endTime < $endLimit) {
                \Yii::error("festival:{$this->name} check is rent time valid by start_time:{$startTime} end_time:{$endTime} failed, end time earliear than festival end time limit:{$endLimit}");
                return false;
            }
            
            return true;
        }
        return true;
    }
    
}

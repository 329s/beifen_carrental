<?php

namespace common\helpers\behaviors;

/**
 * Description of EditorBehavior
 *
 * @author kevin
 */
class EditorBehavior extends \yii\behaviors\AttributeBehavior
{
    
    /**
     * @var string the attribute that will receive edit user id value
     * Set this property to false if you do not want to record the edit user id.
     */
    public $editorAttribute = 'edit_user_id';
    /**
     * @inheritdoc
     *
     * In case, when the value is `null`, the result of the PHP function [time()](http://php.net/manual/en/function.time.php)
     * will be used as value.
     */
    public $value;
    
    public function init() {
        parent::init();
        
        if (empty($this->attributes)) {
            $this->attributes = [
                \yii\db\BaseActiveRecord::EVENT_BEFORE_INSERT => $this->editorAttribute,
                \yii\db\BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->editorAttribute,
            ];
        }
    }
    
    /**
     * @inheritdoc
     *
     * In case, when the [[value]] is `null`, the result of the PHP function [time()](http://php.net/manual/en/function.time.php)
     * will be used as value.
     */
    protected function getValue($event)
    {
        if ($this->value === null) {
            if (\Yii::$app->user->isGuest) {
                return 0;
            }
            if (\Yii::$app->user->identityClass != \backend\models\Rbac_admin::className()) {
                return 0;
            }
            return \Yii::$app->user->id;
        }
        return parent::getValue($event);
    }

    /**
     * Updates a timestamp attribute to the current timestamp.
     *
     * ```php
     * $model->touch('lastVisit');
     * ```
     * @param string $attribute the name of the attribute to update.
     * @throws InvalidCallException if owner is a new record (since version 2.0.6).
     */
    public function touch($attribute)
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        //if ($owner->getIsNewRecord()) {
        //    throw new InvalidCallException('Updating the timestamp is not possible on a new record.');
        //}
        $owner->updateAttributes(array_fill_keys((array) $attribute, $this->getValue(null)));
    }
}

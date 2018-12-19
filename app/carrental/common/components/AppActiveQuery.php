<?php

namespace common\components;

/**
 * Description of AppActiveQuery
 *
 * @author kevin
 */
class AppActiveQuery extends \yii\db\ActiveQuery
{
    public $attribute = 'app_id';
    
    private $_hasVerifiedAppLimitation = false;
    
    /**
     * @inheritdoc
     */
    public function prepare($builder) {
        if (!$this->_hasVerifiedAppLimitation) {
            $this->_hasVerifiedAppLimitation = true;
            $this->verifyAppLimitation();
        }
        return parent::prepare($builder);
    }
    
    public function verifyAppLimitation() {
        $appId = 0;
        if (!\Yii::$app->user->isGuest) {
            $identity = \Yii::$app->user->getIdentity();
            if ($identity instanceof \backend\models\Rbac_admin) {
                $appId = $identity->app_id;
            }
        }
        
        $this->applyAppLimitation($appId);
    }

    public function applyAppLimitation($appId) {
        $tablePart = '';
        if (!empty($this->join)) {
            $modelClass = $this->modelClass;
            $tableName = $modelClass::tableName();
            $tablePart = $tableName.'.';
        }
        return [$tablePart.$this->attribute => $appId];
    }
}

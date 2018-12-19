<?php

namespace common\helpers;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ActiveFormModel extends \yii\base\Model
{
    
    private $__formName = false;
    private $__hasValidated = false;
    
    public function formName() {
        if (!$this->__formName) {
            $formName = parent::formName();
            if (substr($formName, 8, 1) == '_') {
                $formName = substr($formName, 8);
            }
            $this->__formName = Utils::underline2Hump($formName);
        }
        return $this->__formName;
    }
    
    public function fieldName($field) {
        $formName = $this->formName();
        if ($formName) {
            return "{$formName}[$field]";
        }
        return $field;
    }
    
    /**
     * 
     * @return \common\helpers\ActiveRecordModel
     */
    public function getActiveRecordModel() {
        return null;
    }

    /**
     * Returns the attribute labels.
     *
     * Attribute labels are mainly used for display purpose. For example, given an attribute
     * `firstName`, we can declare a label `First Name` which is more user-friendly and can
     * be displayed to end users.
     *
     * By default an attribute label is generated using [[generateAttributeLabel()]].
     * This method allows you to explicitly specify attribute labels.
     *
     * Note, in order to inherit labels defined in the parent class, a child class needs to
     * merge the parent labels with child labels using functions such as `array_merge()`.
     *
     * @return array attribute labels (name => label)
     * @see generateAttributeLabel()
     */
    public function attributeLabels()
    {
        $model = $this->getActiveRecordModel();
        if ($model) {
            return $model->attributeLabels();
        }
        return [];
    }
    
    /**
     * Returns the attribute hints.
     *
     * Attribute hints are mainly used for display purpose. For example, given an attribute
     * `isPublic`, we can declare a hint `Whether the post should be visible for not logged in users`,
     * which provides user-friendly description of the attribute meaning and can be displayed to end users.
     *
     * Unlike label hint will not be generated, if its explicit declaration is omitted.
     *
     * Note, in order to inherit hints defined in the parent class, a child class needs to
     * merge the parent hints with child hints using functions such as `array_merge()`.
     *
     * @return array attribute hints (name => hint)
     * @since 2.0.4
     */
    public function attributeHints() {
        $model = $this->getActiveRecordModel();
        if ($model) {
            return $model->attributeHints();
        }
        return [];
    }
    
    /**
     * Sets the attribute values in a massive way.
     * @param array $values attribute values (name => value) to be assigned to the model.
     * @param boolean $safeOnly whether the assignments should only be done to the safe attributes.
     * A safe attribute is one that is associated with a validation rule in the current [[scenario]].
     * @see safeAttributes()
     * @see attributes()
     */
    public function setAttributes($values, $safeOnly = false)
    {
        return parent::setAttributes($values, $safeOnly);
    }

    public function validate($attributeNames = null, $clearErrors = true) {
        if ($attributeNames === null) {
            if (!$this->__hasValidated) {
                $this->__hasValidated = parent::validate($attributeNames, $clearErrors);
            }
            return $this->__hasValidated;
        }
        return parent::validate($attributeNames, $clearErrors);
    }
    
    public function load($data, $formName = null)
    {
        $this->__hasValidated = false;
        $scope = $formName === null ? $this->formName() : $formName;
        $this->__formName = $scope;
        $formData = null;
        if ($scope === '' && !empty($data)) {
            $formData = $data;
        } elseif (isset($data[$scope])) {
            $formData = $data[$scope];
        }
        
        if ($formData) {
            $this->setAttributes($formData);
        }
        
        $extendFieldTypes = [];
        $extendFieldParams = [];
        $validators = $this->getActiveValidators();
        foreach ($validators as $validator) {
            if ($validator instanceof \yii\validators\FileValidator) {
                foreach ($validator->attributes as $attr) {
                    if ($validator->maxFiles == 1) {
                        $this->$attr = \common\helpers\UploadedFileExtends::getInstance($this, $attr);
                        $extendFieldTypes[$attr] = 'file';
                    }
                    else {
                        $this->$attr = \common\helpers\UploadedFileExtends::getInstances($this, $attr);
                        $extendFieldTypes[$attr] = 'files';
                    }
                }
            }
            elseif ($validator instanceof \yii\validators\DateValidator) {
                if ($validator->type == \yii\validators\DateValidator::TYPE_DATE
                    || $validator->type = \yii\validators\DateValidator::TYPE_DATETIME) {
                    foreach ($validator->attributes as $attr) {
                        $extendFieldTypes[$attr] = 'datetime';
                    }
                    if ($validator instanceof \common\helpers\validators\DateValidator) {
                        if (!empty($validator->defaultTimepart)) {
                            foreach ($validator->attributes as $attr) {
                                $extendFieldParams[$attr] = $validator->defaultTimepart;
                            }
                        }
                    }
                }
            }
            elseif ($validator instanceof \common\helpers\validators\BitFlagValidator) {
                foreach ($validator->attributes as $attr) {
                    $extendFieldTypes[$attr] = 'bitflag';
                }
            }
            elseif ($validator instanceof \yii\validators\EachValidator) {
                foreach ($validator->attributes as $attr) {
                    $extendFieldTypes[$attr] = 'pairs';
                }
            }
            elseif ($validator instanceof \common\helpers\validators\FloatValidator) {
                if ($validator->factor) {
                    foreach ($validator->attributes as $attr) {
                        $extendFieldTypes[$attr] = 'floatbase';
                        $extendFieldParams[$attr] = $validator->factor;
                    }
                }
            }
        }
        
        $this->afterLoad($formData);
        
        if ($this->validate()) {
            $this->populateAttributes($extendFieldTypes, $extendFieldParams);
            return $this->__hasValidated;
        }
        return false;
    }
    
    protected function afterLoad($data) {
        
    }
    
    public function savingFields() {
        return [];
    }
    
    public function save($model) {
        if (!$this->validate()) {
            return false;
        }
        $fields = $this->savingFields();
        
        if (empty($fields)) {
            $fields = [];
            foreach ($model->attributes() as $k) {
                if (!in_array($k, $model->primaryKey())) {
                    $fields[] = $k;
                }
            }
        }
        
        $this->beforeSaveToModel($model);
        
        foreach ($fields as $k => $k2) {
            $k1 = (is_integer($k) ? $k2 : $k);
            if (!$model->hasAttribute($k2) 
                || !$this->hasProperty($k1)
                || $this->$k1 === null) {
                continue;
            }
            
            $model->$k2 = $this->$k1;
        }
        
        $this->afterSaveToModel($model);
        
        return true;
    }
    
    private function populateAttributes($extendFieldTypes, $extendFieldParams)
    {
        $attributes = self::getAttributes();
        $counter = 0;
        foreach ($attributes as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (isset($extendFieldTypes[$key])) {
                $outValue = $value;
                switch ($extendFieldTypes[$key])
                {
                case 'file':
                    $outValue = $this->saveUploadeFile($value, $counter);
                    break;
                case 'files':
                    $outValue = $this->processFileArrayAttribute($this->_saveUploadedFilesMultiple($value, $counter), $key);
                    break;
                case 'datetime':
                    $outValue = \common\helpers\Utils::toTimestamp($value, (isset($extendFieldParams[$key]) ? $extendFieldParams[$key] : false));
                    break;
                case 'bitflag':
                    {
                        $flag = 0;
                        foreach ((array)$value as $bit) {
                            $flag |= $bit;
                        }
                        $outValue = $flag;
                    }break;
                case 'pairs':
                    {
                        $pieces = [];
                        foreach ((array)$value as $k => $v) {
                            $pieces[] = "{$k}:{$v}";
                        }
                        $outValue = implode(";", $pieces);
                    }break;
                case 'floatbase':
                    $outValue = intval($value * $extendFieldParams[$key]);
                    break;
                default:
                    break;
                }
                $this->$key = $outValue;
            }
        }
    }

    protected function beforeSaveToModel($model) {
    }
    
    protected function afterSaveToModel($model) {
    }
    
    protected function _saveUploadedFilesMultiple($files, &$counter) {
        $filePaths = [];
        foreach ($files as $i => $valv) {
            if ($valv instanceof \yii\web\UploadedFile) {
                $valk = $valv->key;
                $filePaths[$valk] = $this->saveUploadeFile($valv, $counter);
            }
        }
        return $filePaths;
    }


    public function getModelId() {
        if ($this->hasProperty('id') && !empty($this->id)) {
            return $this->id;
        }
        $model = $this->getActiveRecordModel();
        $id = $model::getAutoIncreamentId();
        return $id;
    }
    
    public function parseArrayAttributeIndex($key, $attribute = '') {
        $formName = $this->formName();
        $offset = 0;
        if (!empty($formName) && substr($key, 0, strlen($formName)+ strlen($attribute)+2) == "{$formName}[{$attribute}]") {
            $offset = strlen($formName)+ strlen($attribute)+2;
        }
        $idPos = strpos($key, '[', $offset);
        if ($idPos === false) {
            return false;
        }
        $_pos = strpos($key, "]", ++$idPos);
        if ($_pos <= $idPos) {
            return false;
        }
        return substr($key, $idPos, $_pos - $idPos);
    }

    /**
     * 
     * @param array $filepaths
     * @param string $attribute
     * @return string|number field value
     */
    public function processFileArrayAttribute($filepaths = [], $attribute = '') {
        throw new \yii\base\InvalidCallException('You should manually implement the method for processing uploaded file array');
    }
    
    /**
     * 
     * @param string $attribute
     * @param string $value
     * @return array file path array
     * @throws \yii\base\InvalidCallException
     */
    public function getFileArrayAttributePaths($attribute, $value = '') {
        throw new \yii\base\InvalidCallException('You should manually implement the method for get file attribute path array');
    }
    
    public function getRelativeUploadFileDir() {
        $reflector = new \ReflectionClass($this);
        return substr(md5($reflector->getName()), 0, 8);
    }
    
    protected function saveUploadeFile($file, &$counter = 0) {
        $basePath = \common\helpers\UploadFileHelper::getUploadRootDir();
        $relativeDir = $this->getRelativeUploadFileDir();
        $ext = $file->getExtension();
        $id = $this->getModelId();
        $curTime = time();
        $counter++;
        $fileName = "{$id}-{$curTime}-{$counter}.{$ext}";
        $relPath = '/public/upload/'.$relativeDir.'/'.$fileName;
        $fullPath = $basePath.$relPath;
        \common\helpers\UploadFileHelper::mkdirs($fullPath);
        if ($file->saveAs($fullPath)) {
            return $relPath;
        }
        else {
            throw new \yii\base\ErrorException("The file save path:{$relPath} was not writable.");
        }
        return false;
    }
    
    public function getErrorDebugString() {
        $errors = $this->getErrors();
        $errTexts = [];
        foreach ($errors as $field => $fieldErrors) {
            //$errTexts[] = \Yii::t('locale', 'There are errors on {field}: ', ['field'=>$field]) . implode(' ', $fieldErrors);
            $errTexts[] = implode(' ', $fieldErrors);
        }
        
        return implode("\n", $errTexts);
    }
    
    public function getErrorAsHtml() {
        $errors = $this->getErrors();
        $errTexts = [];
        foreach ($errors as $field => $fieldErrors) {
            //$errTexts[] = \Yii::t('locale', 'There are errors on {field}: ', ['field'=>$field]) . implode(' ', $fieldErrors);
            $errTexts[] = implode(' ', $fieldErrors);
        }
        
        return implode("<br />", $errTexts);
    }
    
}

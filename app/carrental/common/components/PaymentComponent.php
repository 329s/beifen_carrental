<?php

namespace common\components;

/**
 * Payment component 
 * the base class of each sdk payment processor
 */
class PaymentComponent extends \yii\base\Component
{
    const DATA_FORMAT_JSON = 'json';
    const DATA_FORMAT_XML = 'xml';
    const DATA_FORMAT_POST = 'post';
    
    public $appKey = '';
    
    public $dataFormat = self::DATA_FORMAT_JSON;    // json, xml, post
    
    private $_attributes = [];
    
    private $_errors = [];
    
    /**
     * @inheritdoc
     * @param type $config
     * @return \common\components\PaymentComponent the newly created [[\common\components\PaymentComponent]] instance.
     * @throws \Exception
     */
    public static function create($config)
    {
        try {
            $config['class'] = get_called_class();
            
            $data = null;
            if (isset($config['data'])) {
                $data = $config['data'];
                unset($config['data']);
            }
            
            $object = \Yii::createObject($config);
            $object->init();
            
            if ($data) {
                $object->parseData($data);
            }
        } catch (\Exception $e) {
            throw $e;
        }
        
        return $object;
    }
    
    public function init()
    {
    }
    
    public function setAttributes($array)
    {
        foreach ($array as $k => $v)
        {
            $this->_attributes[$k] = $v;
        }
    }
    
    public function setAttribute($key, $value)
    {
        $this->_attributes[$key] = $value;
    }
    
    /**
     * 
     * @param string $data
     */
    public function parseData($data)
    {
        $arrData = null;
        if ($this->dataFormat == static::DATA_FORMAT_JSON)
        {
            $arrData = json_decode($data, true);
        }
        elseif ($this->dataFormat == static::DATA_FORMAT_XML)
        {
            libxml_disable_entity_loader(true);
            $xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml) {
                $arrTmp = json_decode(json_encode($xml), true);
                $arrData = [];
                foreach ($arrTmp as $k => $v) {
                    $arrData[$k] = $v;
                }
            }
            
        }
        elseif ($this->dataFormat == static::DATA_FORMAT_POST)
        {
            $arrData = $_POST;
        }
        
        if (!$arrData)
        {
            $this->addError(\Yii::t('locale', 'Data does not fit the rule!'));
            return false;
        }
        
        $this->setAttributes($arrData);
    }
    
    /*public function responseData($data)
    {
        
    }*/
    
    public function validate()
    {
        $this->addError(\Yii::t('locale', 'Data does not fit the rule!'));
        return false;
    }
    
    public function getAttributes($keys = [])
    {
        $arrData = [];
        if (empty($keys)) {
            foreach ($this->_attributes as $k => $v) {
                $arrData[$k] = $v;
            }
        }
        else {
            foreach ($keys as $k) {
                if (isset($this->_attributes[$k])) {
                    $arrData[$k] = $this->_attributes[$k];
                }
            }
        }
        return $arrData;
    }
    
    public function getAttribute($name) {
        return $this->_attributes[$name];
    }
    
    public function hasAttribute($name) {
        return isset($this->_attributes[$name]);
    }
    
    public function generateSignment() {
        return false;
    }
    
    public function addError($errText)
    {
        $this->_errors[] = $errText;
    }
    
    public function getErrors()
    {
        return $this->_errors;
    }
    
    public function getErrorMessage()
    {
        return implode(";", $this->_errors);
    }
    
    public function hasError()
    {
        return !empty($this->_errors);
    }
    
}

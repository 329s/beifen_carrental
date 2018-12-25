<?php

namespace common\widgets;

/**
 * Description of JsonResultWidget
 *
 * @author kevin
 */
class JsonResultWidget extends \yii\base\Widget
{
    /**
     * @var integer 200: success, other failed 
     */
    public $code = 200;
    
    public $message = '';
    
    public $navTabId = '';
    
    public $rel = '';
    
    public $callbackType = '';
    
    public $forwardUrl = '';
    
    public $forwardTitle = '';
    
    /**
     * @var array
     */
    public $attributes;

    public function init() {
        parent::init();
        
        if ($this->code == null) {
            throw new \InvalidArgumentException("The parameter code should be specified!");
        }
        
    }
    
    public function run() {
        $arrData = array(
            'statusCode' => $this->code,
            'message' => $this->message,
            'navTabId' => $this->navTabId,
            'rel' => $this->rel,
            'callbackType' => $this->callbackType,
            'forwardUrl' => $this->forwardUrl,
            'forwardTitle' => $this->forwardTitle,
        );
        if ($this->attributes) {
            $arrData['attributes'] = $this->attributes;
        }
        echo json_encode($arrData, JSON_HEX_AMP|JSON_UNESCAPED_SLASHES);
        exit;
    }
    
}

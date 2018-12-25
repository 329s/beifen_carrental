<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

/**
 * Description of MapApiBase
 *
 * @author kevin
 */
class MapApiBase extends \yii\base\Component
{
    
    public $appKey;
    
    /**
     * @inheritdoc
     * @param type $config
     * @return \common\components\MapApiInterface the newly created [[\common\components\MapApiBase]] instance.
     * @throws \Exception
     */
    public static function create($config = [])
    {
        try {
            $config['class'] = get_called_class();
            
            $object = \Yii::createObject($config);
            $object->init();
            
        } catch (\Exception $e) {
            throw $e;
        }
        
        return $object;
    }
    
    public function init()
    {
        
    }
    
}

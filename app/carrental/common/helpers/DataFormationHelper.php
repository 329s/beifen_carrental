<?php

namespace common\helpers;

/**
 * Description of DataFormationHelper
 *
 * @author kevin
 */
class DataFormationHelper extends \yii\base\Component
{
    
    public static function formatModelAttributeValue($model, $attribute, $formatter)
    {
        $formationOptions = self::guessFormationType($formatter);
        $realFormatter = $formationOptions[1];
        $value = null;
        switch ($formationOptions[0]) {
            case 'dropdown':
                $value = isset($formatter[$model[$attribute]]) ? $formatter[$model[$attribute]] : '';
                break;
            case 'date':
                $value = (empty($model[$attribute]) ? '' : date(($realFormatter ? $realFormatter : 'Y-m-d'), Utils::toTimestamp($model[$attribute])));
                break;
            case 'datetime':
                $value = (empty($model[$attribute]) ? '' : date(($realFormatter ? $realFormatter : 'Y-m-d H:i:s'), Utils::toTimestamp($model[$attribute])));
                break;
            case 'time':
                $value = empty($model[$attribute]) ? '' : date(($realFormatter ? $realFormatter : 'H:i:s'), Utils::toTimestamp($model[$attribute]));
                break;
            case 'image':
                if ($realFormatter === 'link') {
                    $value = Utils::toFileUri($model[$attribute]);
                }
                elseif ($realFormatter === 'circle' || $realFormatter === 'rounded' || $realFormatter === 'thumbnail') {
                    $value = \yii\bootstrap\Html::img(Utils::toFileUri($model[$attribute]), ['class'=>"img-{$realFormatter}"]);
                }
                else {
                    $value = \yii\bootstrap\Html::img(Utils::toFileUri($model[$attribute]), ['class'=>'img-thumbnail']);
                }
                break;
            case 'file':
                if ($realFormatter === 'download') {
                    $value = \yii\bootstrap\Html::a('&nbsp;', Utils::toFileUri($model[$attribute]), ['class'=>'glyphicon glyphicon-save', 'download'=>'']);
                }
                else {
                    $value = Utils::toFileUri($model[$attribute]);
                }
                break;
            case 'function':
                $value = call_user_func($formatter, $model, $attribute);
                break;
            default :
                throw new \yii\base\InvalidParamException("The field:{$attribute} formatter:{$formatter} were not recognized.");
                break;
        }
        if ($value!==null) {
            return $value;
        }
        return isset($model[$attribute]) ? $model[$attribute] : null;
    }
    
    public static function guessFormationType($formatter) {
        $result = [null, null];
        if (is_array($formatter)) {
            $result[0] = 'dropdown';
            $result[1] = $formatter;
        }
        elseif (is_string($formatter)) {
            $seppos = strpos($formatter, ':');
            $format = false;
            if ($seppos && $seppos > 0) {
                $tmp = $formatter;
                $formatter = strtolower(trim(substr($tmp, 0, $seppos)));
                $format = trim(substr($tmp, $seppos+1));
            }
            
            $result[0] = $formatter;
            $result[1] = $format;
        }
        elseif ($formatter instanceof \Closure) {
            $result[0] = 'function';
            $result[1] = $formatter;
        }
        else {
            $result[1] = $formatter;
        }
        
        return $result;
    }
    
}

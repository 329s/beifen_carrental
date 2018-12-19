<?php

namespace common\widgets;

/**
 * Description of Sorter
 *
 * @author kevin
 */
class Sorter extends \yii\data\Sort
{
    public $containerSelector = false;
    
    public function link($attribute, $options = array()) {
        if (($direction = $this->getAttributeOrder($attribute)) !== null) {
            $class = $direction === SORT_DESC ? 'desc' : 'asc';
            if (isset($options['class'])) {
                $options['class'] .= ' ' . $class;
            } else {
                $options['class'] = $class;
            }
        }

        $url = $this->createUrl($attribute);
        $options['data-sort'] = $this->createSortParam($attribute);

        if (isset($options['label'])) {
            $label = $options['label'];
            unset($options['label']);
        } else {
            if (isset($this->attributes[$attribute]['label'])) {
                $label = $this->attributes[$attribute]['label'];
            } else {
                $label = \yii\helpers\Inflector::camel2words($attribute);
            }
        }
        
        if ($this->containerSelector) {
            $options['onclick'] = "$.custom.bootstrap.loadElement('{$this->containerSelector}', '{$url}')";
            $options['href'] = 'javascript:void(0);';
        }
        else {
            $options['href'] = $url;
        }

        return \yii\bootstrap\Html::a($label, null, $options);
    }
    
}

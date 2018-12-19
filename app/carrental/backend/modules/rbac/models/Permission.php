<?php

namespace backend\modules\rbac\models;

/**
 * This is the model class for table "rbac_permission".
 *
 * @property string $name
 * @property string $category
 * @property string $parent
 * @property string $href
 * @property string $description
 * @property string $icon
 * @property string $icon_traditional
 * @property integer $c_order
 * @property integer $status
 * @property string $target
 */
class Permission extends BaseModel
{
    
    const TYPE_MENU = 1001;
    const TYPE_ACTION = 1002;
    const TYPE_NODE = 1003;
    
    const STATUS_NORMAL = 0;
    const STATUS_DISABLED = 10;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['c_order', 'status'], 'integer'],
            [['name', 'parent'], 'string', 'max' => 64],
            [['category'], 'string', 'max' => 24],
            [['href', 'description'], 'string', 'max' => 256],
            [['icon', 'target'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => \Yii::t('locale', '权限标识'),
            'category' => \Yii::t('locale', '权限类型'),
            'parent' => \Yii::t('locale', '隶属节点'),
            'href' => \Yii::t('locale', '响应链接'),
            'description' => \Yii::t('locale', '描述'),
            'icon' => \Yii::t('locale', '图标'),
            'icon_traditional' => \Yii::t('locale', '传统图标'),
            'c_order' => \Yii::t('locale', '排序'),
            'status' => \Yii::t('locale', '状态'),
            'target' => \Yii::t('locale', '指定打开目标'),
            'operation' => \Yii::t('locale', 'Operation'),
        ];
    }
    
    public function getAuthPermissionKey() {
        if ($this->category != 'action') {
            return $this->category.'-'.$this->name;
        }
        return $this->name;
    }
    
    public function genAuthPermissionData() {
        return (object)[
            'type'=> $this->getPermissionType(), 
            'code'=> $this->name, 
            //'url'=>$this->href, 
            //'icon'=>$this->icon, 
            //'sort_num'=>$this->c_order, 
            //'status'=> $this->status, 
            //'target'=>$this->target
        ];
    }
    
    public function getPermissionType() {
        $arrTypes = [
            'menu' => static::TYPE_MENU,
            'action' => static::TYPE_ACTION,
            'node' => static::TYPE_NODE,
        ];
        return isset($arrTypes[$this->category]) ? $arrTypes[$this->category] : static::TYPE_NODE;
    }
    
    /**
     * @return static[] 
     */
    public static function getAll() {
        $q = static::find();
        $rows = $q->all();
        $arrData = [];
        foreach ($rows as $row) {
            $arrData[$row->name] = $row;
        }
        return $arrData;
    }
    
    /**
     * @return static[] 
     */
    public static function getMenus() {
        $q = static::find();
        $q->where(['category' => 'menu']);
        $rows = $q->all();
        $arrData = [];
        
    }
    
    public static function getTypesArray() {
        return [
            static::TYPE_MENU => \Yii::t('locale', 'Menu'),
            static::TYPE_ACTION => \Yii::t('locale', 'Action'),
            static::TYPE_NODE => \Yii::t('locale', 'Node'),
        ];
    }
    
    public static function getCategoriesArray() {
        return [
            'menu' => \Yii::t('locale', 'Menu'),
            'action' => \Yii::t('locale', 'Action'),
            'node' => \Yii::t('locale', 'Node'),
        ];
    }

    public static function getStatusArray() {
        return [
            static::STATUS_NORMAL => \Yii::t('locale', 'Normal'),
            static::STATUS_DISABLED => \Yii::t('locale', 'Disabled'),
        ];
    }
    
    /**
     * 
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public static function createDataProvider($config = []) {
        return parent::createDataProvider(\yii\helpers\ArrayHelper::merge([
            'formattingAttributes' => [
                'status' => static::getStatusArray(),
                'category' => static::getCategoriesArray(),
            ],
        ], $config));
    }

    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'name', 'textField'=>'description'];
    }

}

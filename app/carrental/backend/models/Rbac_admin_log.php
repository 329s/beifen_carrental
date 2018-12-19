<?php
namespace backend\models;

class Rbac_admin_log extends \common\helpers\ActiveRecordModel {
    
    public static function doLog() {
        $requestPathInfo = \Yii::$app->request->pathInfo;
        static $skipPaths = [
            'order/get_order_price' => 1,
            'order/get_order_paid_amount' => 1,
            'vehicle/getmodelnames' => 1,
            'options/preferential_combo_data' => 1,
            'user/searchuserslike' => 1,
            'site/reflush' => 1,
            'notification/check' => 1,
        ];
        $endspart = substr($requestPathInfo, -5);
        if (isset($skipPaths[$requestPathInfo])) {
            return;
        }
        elseif ($endspart == '_list' || $endspart == 'index') {
            return;
        }
        $userId = \Yii::$app->user->id;
        //$path_info = "/" . Yii::$app->request->pathInfo . "/";
        //$access_id = 0;

        $url = \Yii::$app->request->getUrl();
        $postData = var_export(\Yii::$app->request->post(), true);
        
        if (strlen($url) > 256) {
            $url = substr($url, 0, 256);
        }
        
        // 保存数据
        $log = new static();
        $log->user_id = $userId;
        $log->url = $url;
        $log->post_data = $postData;
        $log->time = date('Y-m-d H:i:s', time());
        $log->ip = trim(\common\helpers\MyFunction::funGetIP());
        $log->save();
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
                'user_id' => \Yii::t('locale', 'Operator'),
                'admin_name' => \Yii::t('locale', 'Operator'),
                'time' => \Yii::t('locale', 'Operating Time'),
                'post_data' => \Yii::t('locale', 'Sent data'),
                'ip' => \Yii::t('locale', 'Track IP'),
                'url' => \Yii::t('locale', 'Request URL'),
                'operation' => \Yii::t('locale', 'Operation'),
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
            'user_id' => array('width' => 100, 'sortable' => 'true', 
                'formatter' => "function(value,row){ return row.admin_name; }"
            ),
            'admin_name' => array('width' => 100, 'sortable' => 'true'),
            'time' => array('width' => 140, 'sortable' => 'true'),
            'post_data' => array('width' => 450),
            'ip' => array('width' => 60, 'sortable' => 'true'),
            'url' => array('width' => 550),
            'operation' => array('width' => 60, 
                'buttons' => array(
                    array('type' => 'ajax', 'url' => 'log/delete/ids/', 'name' => \Yii::t('locale', 'Delete'), 'title' => \Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-table_row_delete'),
                ),
            ),
        );
    }
    
}

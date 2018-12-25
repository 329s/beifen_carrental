<?php
namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
/**
 * Order model
 *
 * @property integer id
 * @property string  lsprefix
 * @property string  lsnum
 * @property string  carorg
 * @property integer usercarid
 * @property string  time
 * @property string  address
 * @property string  content
 * @property string  legalnum
 * @property integer price
 * @property integer score
 * @property integer illegalid
 * @property string  number
 * @property string  agency
 * @property string  province
 * @property string  city
 * @property string  town
 * @property string  lat
 * @property string  lng
 * @property string  count
 * @property string  totalprice
 * @property string  totalscore
 * @property string  canhandle
 * @property string  handlefee
 * @property integer status
 * @property integer add_time
 * @property integer date_time
 */
/**
 * 车辆违章查询表 
 * @property integer $id
 */
class Pro_violation_inquiry extends \common\helpers\ActiveRecordModel
{
	const STATUS_TRUE = 1;           // 已处理
    const STATUS_FALSE= 0;           // 未处理
    const STATUS_HANDLE= 2;           // 未处理
    const ALL_QUERY= 0;           // 未处理
    const SINGLE_QUERY= 1;           // 未处理
    const QUERY_NUM = 30;           // 查询条数
    const OVERTIME = 99;           // 超时时间
    const juheKey = '98402b8383a53b61fac34faf24ed487b';
	
	/**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
	
	public function rules()
    {
        return [
        ];
    }
	
    public function attributeLabels()
	{
        // return [//极速
            // 'id' => 'ID',
			// 'plate_number'	=> \Yii::t('locale', 'Plate number'),
			// 'lsprefix'		=> \Yii::t('locale', 'Lsprefix'),
			// 'lsnum'			=> \Yii::t('locale', 'Lsnum'),
			// 'carorg'		=> \Yii::t('locale', 'Carorg'),
			// 'usercarid'		=> \Yii::t('locale', 'Usercarid'),
			// 'time'			=> \Yii::t('locale', 'Illegal Time'),
			// 'address'		=> \Yii::t('locale', '{name} address', ['name'=>\Yii::t('locale', 'Illegal')]),
			// 'content'		=> \Yii::t('locale', '{name} content', ['name'=>\Yii::t('locale', 'Illegal')]),
			// 'legalnum'		=> \Yii::t('locale', 'Legalnum'),
			// 'price'			=> \Yii::t('locale', 'Deduction Price'),
			// 'score'			=> \Yii::t('locale', 'Deduction Score'),
			// 'illegalid'		=> \Yii::t('locale', 'Illegalid'),
			// 'agency'		=> \Yii::t('locale', 'Agency'),
			// 'province'		=> \Yii::t('locale', 'Province'),
			// 'city'			=> \Yii::t('locale', 'City'),
			// 'town'			=> \Yii::t('locale', 'Town'),
			// 'lat'			=> \Yii::t('locale', 'Lat'),
			// 'lng'			=> \Yii::t('locale', 'Lng'),
			// 'totalprice'	=> \Yii::t('locale', 'Totalprice'),
			// 'totalscore'	=> \Yii::t('locale', 'Totalscore'),
			// 'canhandle'		=> \Yii::t('locale', 'Canhandle'),
			// 'handlefee'		=> \Yii::t('locale', 'Handlefee'),
			// 'status'		=> \Yii::t('locale', 'Status'),
			// 'add_time'		=> \Yii::t('locale', 'Add Time'),
			// 'date_time'		=> \Yii::t('locale', 'Date Time'),
			// 'remarks'		=> \Yii::t('locale', 'Remarks'),
			// 'Operation'		=> \Yii::t('locale', 'Operation'),
			// 'Viewlog'		=> \Yii::t('locale', 'Operation'),
			// 'Viewlog'		=> \Yii::t('locale', 'Operation'),
			// 'fielname'		=> \Yii::t('locale', 'Fielname'),
			// 'errorcontent'	=> \Yii::t('locale', 'Error content'),
        // ];
		
		return [//聚合
            'id' => 'ID',
			'hphm'	=> \Yii::t('locale', 'Plate number'),
			'date'			=> \Yii::t('locale', 'Illegal Time'),
			'area'		=> \Yii::t('locale', '{name} address', ['name'=>\Yii::t('locale', 'Illegal')]),
			'act'		=> \Yii::t('locale', '{name} content', ['name'=>\Yii::t('locale', 'Illegal')]),
			'money'			=> \Yii::t('locale', 'Deduction Price'),
			'fen'			=> \Yii::t('locale', 'Deduction Score'),
			'status'		=> \Yii::t('locale', 'Status'),
			'add_time'		=> \Yii::t('locale', 'Add Time'),
			'date_time'		=> \Yii::t('locale', 'Date Time'),
			'remarks'		=> \Yii::t('locale', 'Remarks'),
			'Operation'		=> \Yii::t('locale', 'Operation'),
        ];
    }
	
	/**
     * Returns the attribute custom types.
     * Attribute custom types is used for display model data in datagrid specified display options.
     * @return array attribute custom types (name=>array('width'=>null, 'data-options'=>null, 'editor'=>null,'formatter'=>null,'sortable'=>true))
     */
    public function attributeCustomTypes()
    {
        return array(
            'id' => array('sortable' => 'true'),
            'date' => array('sortable' => 'true'),
            'add_time' => array('width' => 140, 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'status' => array('width' => 60, 'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(self::getStatusArray())." }"),
            'errorcontent' => array('width' => '96%'),
            'Operation' => ['width' => 140, 
				'buttons' => array(
                   \Yii::$app->user->can('inquiry/violation_info') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['inquiry/violation_info'])."?id=", 'name' => Yii::t('locale', 'Matching ordery'), 'title' => Yii::t('locale', 'Matching ordery'), 'paramField' => 'id', 'icon' => '', 'showText' => true) : null,
				   \Yii::$app->user->can('inquiry/violation_edit') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['inquiry/violation_edit'])."?id=", 'name' => Yii::t('locale', 'Illegal edit'), 'title' => Yii::t('locale', 'Illegal edit'), 'paramField' => 'id', 'icon' => '', 'showText' => true) : null,
                ),
            ],
			'Viewlog' =>[
				'buttons' => array(
                   \Yii::$app->user->can('inquiry/view_log') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['inquiry/view_content'])."?fielname=", 'name' => Yii::t('locale', 'View Log'), 'title' => Yii::t('locale', 'View Log'), 'paramField' => 'fielname', 'icon' => '', 'showText' => true) : null,
                ),
			],
        );
    }
	
	public static function getStatusArray() {
        return [
            self::STATUS_FALSE => \Yii::t('locale', 'Untreated'),
			self::STATUS_TRUE => \Yii::t('locale', 'Have entered'),
            self::STATUS_HANDLE => \Yii::t('locale', 'Already processed'),
        ];
    }
	
	public static function getQueryArray($queryCount = 0) {
		if($queryCount > 0){
			$arrData = [
				static::ALL_QUERY => \Yii::t('locale', 'All Query'),
				static::SINGLE_QUERY => \Yii::t('locale', 'Single_Query'),
			];
		}else{
			$arrData = [
				static::SINGLE_QUERY => \Yii::t('locale', 'Single_Query'),
			];
		}
        return $arrData;
    }
	
	public static function getInquiryStatusArray() {
        return [
            0 => '未处理',
            1 => '已录入',
            2 => '已处理',
        ];
    }
	
	public static function getInquiryQueryArray(){
		$vehicleArr = self::getRequeryVehicle(0,null);
		$vehicleCount = count($vehicleArr);
		$queryNum = static::QUERY_NUM;
		$queryPage = ceil($vehicleCount / $queryNum);
		for($i=1;$i <= $queryPage;$i++){
			$pageCount = $i * $queryNum;
			if($pageCount >= $vehicleCount){
				$pageCount = $vehicleCount;
			}
			$querydata[$i] = (($i - 1) * $queryNum )."->". $pageCount;
		}
		
		return $querydata;
	}
	
	public static function getRequeryVehicle($lookupMethod,$lookupArr=array()){
		if($lookupMethod == 1){//按照车牌号查询
			$lsnumArr = explode(',',$lookupArr['lsnum']);
			$where = ['and',['!=','model_id',50], ['<','status',5],['in','plate_number',$lsnumArr]];
			$requestVehicleArray = self::selectVehicle($where);
		}else{
			$requestVehicleArray = Yii::$app->cache->get('requestVehicleCache');
			$lastInfo = \common\models\Pro_violation_inquiry::find()->where(['status'=>0])->orderBy('add_time desc')->asArray()->one();
			$lastTime =  ($lastInfo['add_time'] - strtotime(date('Y-m-d 00:00:00'))) - 86400;
			
			if(empty($requestVehicleArray) && $lastTime >= 0){
				$where = ['and',['!=','model_id',50], ['<','status',5]];
				$requestArr = self::selectVehicle($where);
				//缓存时间
				$cacheTime = 86400 - (time() - strtotime(date('Y-m-d 00:00:00')));
				Yii::$app->cache->set('requestVehicleCache',$requestArr , $cacheTime); 
				$requestVehicleArray = Yii::$app->cache->get('requestVehicleCache');
			}
			if(intval($lookupArr['queryCount']) > 0){
				$requestVehicleArray = array_slice($requestVehicleArray,0,$lookupArr['queryCount']);
				
			}
		}
		
		return $requestVehicleArray;
	}
	
	public static function resetRequeryVehicle($resetCount){
		$arrRows = self::getRequeryVehicle(0,null);
		$newArr = array_splice($arrRows,$resetCount);
		
		//缓存时间
		$cacheTime = 86400 - (time() - strtotime(date('Y-m-d 00:00:00')));
		Yii::$app->cache->delete('requestVehicleCache');
		Yii::$app->cache->set('requestVehicleCache',$newArr , $cacheTime); 
	}
	
	public static function selectVehicle($where){
		
		$select = ['id','plate_number','model_id','engine_number','vehicle_number','certificate_number','status'];
		$cdb = \common\models\Pro_vehicle::find();
		$cdb2 = \common\models\Pro_carpre::find();//聚合
		// $cdb2 = \common\models\Pro_carorg::find();//极速
		$cdb->select($select);
		$cdb->where($where);
		$arrRows = $cdb->asArray()->all();
		
		foreach ($arrRows as $k => $row) {
			$lsprefix = "";
			$carorgArr = array();
			$abbr = self::cutStr($row['plate_number'],2,0);
			$cdb2->where(['abbr'=>$abbr]);
			$carorgArr = $cdb2->createCommand()->queryOne();
			if(!empty($carorgArr)){
				$requestArr[] = array(
					'dtype'		 => 'json',
					'callback'   => 'jsonp',
					'key'		 => static::juheKey,	
					'city' 		 => $carorgArr['city_code'],	
					'hphm' 		 => $row['plate_number'],	
					'hpzl' 		 => 02,	
					'engineno' 	 => self::Intercept($row['engine_number'],"-$carorgArr[engineno]",$carorgArr['engineno']),
					'classno' 	 => self::Intercept($row['vehicle_number'],"-$carorgArr[classno]",$carorgArr['classno']),
				);
			}
		}
		
		/*foreach ($arrRows as $k => $row) {
			$lsprefix = "";
			$carorgArr = array();
			$lsprefix = self::cutStr($row['plate_number'],1,0);
			$lsnum = self::cutStr($row['plate_number'],6,1);
			$cdb2->where(['lsprefix'=>$lsprefix]);
			$carorgArr = $cdb2->createCommand()->queryOne();
			
			$requestArr[] = array(
				'carorg'	  	=> 	$carorgArr['carorg'],
				'lsprefix' 		=> 	$lsprefix,
				'lsnum'	  		=> 	$lsnum,
				'lstype'	  	=>	02,
				'frameno' 		=>	self::Intercept($row['vehicle_number'],"-$carorgArr[frameno]",$carorgArr['frameno']),
				'engineno' 		=>	self::Intercept($row['engine_number'],"-$carorgArr[engineno]",$carorgArr['engineno']),
				'iscity'	  	=>	1,
			);
		}*/
		return isset($requestArr) ? $requestArr : null ;
	}
	
	public static function Intercept($str,$start,$len){
		if($len > 0){
			if($len == 100){
				$return = substr($str,0,$len);
			}else{
				$return = substr($str,$start,$len);
			}
		}else{
			$return = null;
		}
		return $return;
	}
	
	public static function cutStr($str, $len = 100, $start = 0, $suffix = 0) {
		$str = strip_tags(trim(strip_tags($str)));
		$str = str_replace(array("\n", "\t"), "", $str);
		$strlen = mb_strlen($str);
		while ($strlen) {
			$array[] = mb_substr($str, 0, 1, "utf8");
			$str = mb_substr($str, 1, $strlen, "utf8");
			$strlen = mb_strlen($str);
		}
		$end = $len + $start;
		$str = '';
		for ($i = $start; $i < $end; $i++) {
			$str.=$array[$i];
		}
		return count($array) > $len ? ($suffix == 1 ? $str . "&hellip;" : $str) : $str;
	}
	
}

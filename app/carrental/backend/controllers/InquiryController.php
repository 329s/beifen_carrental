<?php
namespace backend\controllers;

use Yii;
use common\helpers\MyFunction;

/**
 * Description of UserController
 *
 * @author kevin
 */
class InquiryController  extends \backend\components\AuthorityController
{
    private $pageSize = 20;
    private $jisuUrl = 'http://api.jisuapi.com/illegal/query?appkey=b7b5c1018bd6c51d';
    private $juheUrl = 'http://v.juhe.cn/sweizhang/query';
    private $recordReturn = '';
    private $writeLog = '';

    public function getView() {
        $prefix = \backend\components\AdminHtmlService::getViewPrefix();
        if ($prefix) {
            return \Yii::createObject([
                'class' => \common\components\ViewExtend::className(),
                'prefix' => $prefix,
            ]);
        }
        return parent::getView();
    }
    
    public function actionIndex() {
		$plate_number = strval(Yii::$app->request->getParam('plate_number'));
		$data['plate_number'] = $plate_number;
		if(isset($data)){
			return $this->renderPartial('index',$data);
		}else{
			return $this->renderPartial('index');
		}
    }
	
	public function actionAccording_vehicle(){
		
		return $this->renderPartial('according_vehicle');
	}
	
	public function actionAccording_vehicle_list(){
		$plate_number = strval(Yii::$app->request->getParam('plate_number'));
		
        if($plate_number){
			
			if(strlen($plate_number) == 9){
				$where = ['v.plate_number'=>$plate_number];
				
			}
		}else{
			$where = [];
		}
		
		$intPage = intval(Yii::$app->request->getParam('page'));
        if ($intPage == 0)
            $intPage = 1;
        $numPerPage = intval(Yii::$app->request->getParam('rows'));
        $numPerPage = intval($numPerPage);
        if (!$numPerPage){
            $numPerPage = $this->pageSize;
        }
		
        // get order
        $intSort = strval(Yii::$app->request->getParam('sort'));
        $intSortDirection = strval(Yii::$app->request->getParam('order'));
        if (!empty($intSort) && !empty($intSortDirection)) {
            $order = $intSort . " " . $intSortDirection;
        }
		
		$vehicleCount = \common\models\Pro_vehicle::find()->from('pro_vehicle AS v')->where($where)->count();
		$pages = new \yii\data\Pagination(['totalCount'=>$vehicleCount]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
		
		$vehicleList = \common\models\Pro_vehicle::find()
						->select('v.id,v.plate_number,v.engine_number,v.vehicle_number,(select count(*) from pro_violation_inquiry where hphm = v.plate_number and status = 0 and handled = 0) as inquiryCount')
						->from('pro_vehicle AS v')
						->where($where)
						->limit($pages->getLimit())
						->offset($pages->getOffset())
						->orderBy((isset($order) && !empty($order)) ? $order : "inquiryCount desc")
						->asArray()
						->all();
		
        $arrListData = [
            'total' => intval($vehicleCount),
            'rows' => $vehicleList
        ];
		echo json_encode($arrListData);
	}
	
    public function actionInquiry_list() {
		
		
		$plate_number = strval(Yii::$app->request->getParam('plate_number'));
		$serial = strval(Yii::$app->request->getParam('serial'));
		$status = intval(Yii::$app->request->getParam('status'));
		$where[] = 'and'; 
        if($plate_number){
			if(strlen($plate_number) == 9){
				$where[] = ['hphm'=>$plate_number];
			}
		}
		$where[] = ['status'=>$status];
		if(!empty($serial)){
			$orderInfo = \common\models\Pro_vehicle_order::find()->select(['start_time','end_time','new_end_time','vehicle_id'])->where(['serial'=>$serial])->asArray()->one();
			if($orderInfo['new_end_time'] > $orderInfo['end_time']){
				$end_time = $orderInfo['new_end_time'];
			}else{
				$end_time = $orderInfo['end_time'];
			}
			$vehicleInfo = \common\models\Pro_vehicle::find()->select(['plate_number'])->where(['id'=>$orderInfo['vehicle_id']])->asArray()->one();
			$where[] = ['and',['>=','date_time',$orderInfo['start_time']],['<=','date_time',$end_time],['=','hphm',$vehicleInfo['plate_number']]];
		}
		// print_r($where);exit;
		$intPage = intval(Yii::$app->request->getParam('page'));
        if ($intPage == 0)
            $intPage = 1;
        $numPerPage = intval(Yii::$app->request->getParam('rows'));
        $numPerPage = intval($numPerPage);
        if (!$numPerPage){
            $numPerPage = $this->pageSize;
        }
		
        // get order
        $intSort = strval(Yii::$app->request->getParam('sort'));
        $intSortDirection = strval(Yii::$app->request->getParam('order'));
        if (!empty($intSort) && !empty($intSortDirection)) {
            $order = $intSort . " " . $intSortDirection;
        }
		
		$inquiryCount = \common\models\Pro_violation_inquiry::find()->where($where)->count();
		$pages = new \yii\data\Pagination(['totalCount'=>$inquiryCount]);
        $pages->setPageSize($numPerPage);
        $pages->setPage($intPage - 1);
		
		$inquiryList = \common\models\Pro_violation_inquiry::find()
						->select('*')
						->where($where)
						->limit($pages->getLimit())
						->offset($pages->getOffset())
						->orderBy((isset($order) && !empty($order)) ? $order : "id desc")
						->asArray()
						->all();
        $arrListData = [
            'total' => intval($inquiryCount),
            'rows' => $inquiryList,
            'status' => $status,
        ];
        echo json_encode($arrListData);
    }
	
	public function actionViolation_info() {
		
        $objOrder = null;
        $objVehicle = null;
        $objInquiry = null;
   
		$inquiryId = intval(\Yii::$app->request->getParam('id'));
		if (!$inquiryId) {
			return Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Illegal')])]);
		}
		$objInquiry = \common\models\Pro_violation_inquiry::find()->where(['id'=>$inquiryId])->one();
		if (!$objInquiry) {
			return Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Illegal')]);
		}
		
		$plate_number = $objInquiry->hphm;
		$objVehicle = \common\models\Pro_vehicle::find()->where(['plate_number'=>$plate_number])->one();
        if (!$objVehicle) {
            return Yii::t('locale', '{name} should not be empty!', ['name'=>  Yii::t('locale', 'Vehicle')]);
        }
		
		$where = ['and',['>=','end_time',$objInquiry->date_time],['<=','start_time',$objInquiry->date_time],['vehicle_id'=>$objVehicle->id]];
		$objOrder = \common\models\Pro_vehicle_order::find()->where($where)->one();
		
		if (!$objOrder) {
			return  Yii::t('locale', '{name} not exists!', ['name'=>Yii::t('locale', 'Order')]);
		}
		
        $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle['model_id']);
        if (!$objVehicleModel) {
            return Yii::t('locale', '{name} should not be empty!', ['name'=>  Yii::t('locale', 'Vehicle model')]);
        }
        
        $arrData = [
            'objVehicle' => $objVehicle,
            'vehicleId' => $objVehicle['id'],
            'inquiryId' => $inquiryId,
            'orderId' => ($objOrder ? $objOrder->id : 0),
            'vehicleModelName' => $objVehicleModel->vehicle_model,
            'objOrder' => $objOrder,
            'objInquiry' => $objInquiry,
        ];
        
        return $this->renderPartial('violation_info', $arrData);
    }
	
	public function actionViolation_edit(){
		$inquiryId = intval(Yii::$app->request->getParam('id'));
		if (!$inquiryId) {
			MyFunction::funEchoJSON_Ajax(Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Illegal')])]), 300);
        }
		$objInquiry = \common\models\Pro_violation_inquiry::find()->where(['id'=>$inquiryId])->one();
		// print_r($objInquiry);exit;
		// 提交并入库
        if (Yii::$app->request->getParam('action') == 'update') {
			$status = intval(Yii::$app->request->getParam('status'));
			$remarks = trim(Yii::$app->request->getParam('remarks'));
			$result = \common\models\Pro_violation_inquiry::updateAll(['status' => $status,'remarks'=>$remarks],[ 'id'=>$inquiryId]); 
			if($result){
				MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Congratulations, successful operation!'), 200, '', '', 'refreshCurrentX', '');
			}else{
				MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'Operation failed!'), 300);
			}
		}
		$data['objInquiry'] = $objInquiry;
		return $this->renderPartial('violation_edit', $data);
	}
	
	public function actionView_log(){
		$path = $_SERVER['DOCUMENT_ROOT'].'/app/carrental/backend/runtime/carlog/';
		$files = scandir($path);
		foreach ($files as $file) {
			if ($file != '.' && $file != '..') {
				$fileInfo[] = array(
					'fielname'=> basename($file),
				);
			}
		}
		$data['fileInfo'] = $fileInfo;
		return $this->renderPartial('view_log',$data);
	}
	
	public function actionView_content(){
		$fielname = trim(Yii::$app->request->getParam('fielname'));
		$content = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/app/carrental/backend/runtime/carlog/'.$fielname);
		
		$logArr = explode("\n",$content);
		foreach($logArr as $key=>$val){
			if(!empty($val)){
				$data[] = array(
					'id' => $key+1,
					'errorcontent' => $val,
				);
			}
		}
		$data['logArr'] = $data;
		return $this->renderPartial('view_content',$data);
	}
	
	
	public function actionQuery_vehicle_violation(){
		
		if (Yii::$app->request->getParam('action') == 'query') {
			$lookupMethod = Yii::$app->request->getParam('lookupMethod');
			$lookupArr['lsnum'] = Yii::$app->request->getParam('lsnum');
			$lookupArr['queryCount'] = intval(Yii::$app->request->getParam('queryCount'));
			
			$arrRows = \common\models\Pro_violation_inquiry::getRequeryVehicle($lookupMethod,$lookupArr);
			if(empty($arrRows)){
				MyFunction::funEchoJSON_Ajax(Yii::t('locale', 'No vehicles were found to meet the conditions'), 300);
			}
			
			$starttime = time();//开始时间
			$startCount = 0;//开始计数
			
			foreach ($arrRows as $k => $row) {
				$return = array();
				$jsonarr = array();
				$result = array();
				
				$endtime = time() - $starttime;//结束时间
				
				if($endtime >= \common\models\Pro_violation_inquiry::OVERTIME){//如果时间太长 强制中断
					break;
				}else{
					$startCount++;
				}
				
				$return = $this->curlPost($this->juheUrl,$row);
				$jsonarr = json_decode($return,true);
				
				/* $this->returnResultDetail($jsonarr,$row['lsprefix'].$row['lsnum']);//极速
				if($jsonarr['status'] == 0){
					$result = $jsonarr['result'];
					if(!empty($result['list'])){
						foreach($result['list'] as $k=>$v){
							$havIllegalid = Yii::$app->db->createCommand('SELECT * FROM Pro_violation_inquiry WHERE lsnum=:lsnum AND illegalid=:illegalid')
												 ->bindValue(':lsnum',$result['lsnum'])
												 ->bindValue(':illegalid',$v['illegalid'])
												 ->queryOne();
							if(empty($havIllegalid)){
								$data[] =  array(
									'plate_number'	=>	$result['lsprefix'].$result['lsnum'],
									'lsprefix'		=>	$result['lsprefix'],
									'lsnum'			=>	$result['lsnum'],
									'carorg'		=>	$result['carorg'],
									'usercarid'		=>	$result['usercarid'],
									'time'			=>	$v['time'],
									'address'		=>	$v['address'],
									'content'		=>	$v['content'],
									'legalnum'		=>	$v['legalnum'],
									'price'			=>	$v['price'],
									'score'			=>	$v['score'],
									'illegalid'		=>	$v['illegalid'],
									'number'		=>	empty($v['number']) ? null : $v['number'],
									'agency'		=>	$v['agency'],
									'province'		=>	empty($v['province']) ? null : $v['province'],
									'city'			=>	empty($v['city']) ? null : $v['city'],
									'town'			=>	empty($v['town']) ? null : $v['town'],
									'lat'			=>	empty($v['lat']) ? 0 : $v['lat'],
									'lng'			=>	empty($v['lng']) ? 0 : $v['lng'],
									'count'			=>	$result['count'],
									'totalprice'	=>	$result['totalprice'],
									'totalscore'	=>	$result['totalscore'],
									'canhandle'		=>	$v['canhandle'],
									'handlefee'		=>	empty($v['handlefee']) ? 0 : $v['handlefee'],
									'status'		=>	0,
									'add_time'		=>	time(),
									'date_time'		=>	strtotime($v['time']),
								);
							}
						}
					}
				} */
				
				$this->returnResultDetail($jsonarr,$row['hphm']);
				if($jsonarr['error_code'] == 0){//聚合
					$result = $jsonarr['result'];
					if(!empty($result['lists'])){
						foreach($result['lists'] as $k=>$v){
							$date = strtotime($v['date']);
							$havIllegalid = Yii::$app->db->createCommand('SELECT * FROM Pro_violation_inquiry WHERE hphm=:hphm AND date_time=:date')
								->bindValue(':date',$date)
								->bindValue(':hphm',$result['hphm'])
								->queryOne();
							if(empty($havIllegalid)){
								$data[] =  array(
									'error_code'	=>	$jsonarr['error_code'],
									'province'		=>	$result['province'],
									'city'			=>	$result['city'],
									'hphm'			=>	$result['hphm'],
									'hpzl'			=>	$result['hpzl'],
									'date'			=>	$v['date'],
									'area'			=>	$v['area'],
									'archiveno'		=>	$v['archiveno'],
									'act'			=>	$v['act'],
									'code'			=>	$v['code'],
									'fen'			=>	$v['fen'],
									'money'			=>	$v['money'],
									'handled'		=>	$v['handled'],
									'wzcity'		=>	$v['wzcity'],
									'date_time'		=>	$date,
									'add_time'		=>	time(),
									'status'		=>	0,
								);
							}
						}
					}
				}
			}
			//写入log
			$this->writeLog();
			if($lookupMethod == 0){
				//从缓存中0开始的数组 删除已查询的有效车辆违章
				\common\models\Pro_violation_inquiry::resetRequeryVehicle($startCount);
			}
			if(!empty($data)){
				$field = ['error_code','province','city','hphm','hpzl','date','area','archiveno','act','code','fen','money','handled','wzcity','date_time','add_time','status'];//聚合
				// $field = ['plate_number','lsprefix','lsnum','carorg','usercarid','time','address','content','legalnum','price','score','illegalid','number','agency','province','city','town','lat','lng','count','totalprice','totalscore','canhandle','handlefee','status','add_time','date_time'];
				$objInquiry = \Yii::$app->db;
				$objInquiry->createCommand()->batchInsert('Pro_violation_inquiry',$field,$data)->execute(); 
				
				MyFunction::funEchoJSON_Ajax('查询时间：'.$endtime.'秒 查询成功车辆：'.$startCount.'辆 新增违章条数：'.count($data).'条', 200, 'page1', '', 'closeCurrent', '');
			}else{
				MyFunction::funEchoJSON_Ajax('查询时间：'.$endtime.'秒 查询成功车辆：'.$startCount.'辆 查询车辆无违章或者已存在违章', 200, 'page1', '', 'closeCurrent', '');
			}
		}else{
			$queryCount = \common\models\Pro_violation_inquiry::getRequeryVehicle(0,null);
			if($queryCount){
				$data['queryCount'] = count($queryCount);
				return $this->renderPartial('query_vehicle_violation',$data);
			}else{
				return $this->renderPartial('query_vehicle_violation');
			}
		}
	}
	
	
	
	
	function curlPost($url,$data){
		$ch = curl_init();
		$header = array("Accept-Charset: utf-8");
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 360);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$tempInfo = curl_exec($ch);
		$errorno = curl_errno($ch);
		curl_close($ch);
		if ( $errorno ) {
			return array('errorno' => $errorno);
		}
		return $tempInfo;
	}
	
	function returnResultDetail($jsonarr,$plate_number){
		
		/* $result = $jsonarr['result'];
		$msg = "车牌号：". $plate_number;
		$msg .= "->查询状态：".$jsonarr['status'];
		
		if($jsonarr['status'] == 0){
			if(!empty($result['list'])){
				$msg .= "->违章列表：".json_encode($result['list']);
			}else{
				$msg .= "->返回信息：".$jsonarr['msg'];
			}
			$this->recordReturn .= $msg."\n";
		}else{
			$msg .= "->返回信息：".$jsonarr['msg'];
			$this->writeLog .= $msg."\n";
		} */
		
		$result = $jsonarr['result'];
		$msg = "车牌号：".$plate_number;
		$msg .= "->查询状态：".$jsonarr['error_code'];
		if($jsonarr['error_code'] == 0){
			if(!empty($result['lists'])){
				$msg .= "->违章列表：".json_encode($result['lists']);
			}else{
				$msg .= "->返回信息：".$jsonarr['reason'];
			}
			$this->recordReturn .= $msg."\n";
		}else{
			$msg .= "->返回信息：".$jsonarr['reason'];
			$this->writeLog .= $msg."\n";
		}
	}
	
	function writeLog(){
		if($this->recordReturn){
			$path = $_SERVER['DOCUMENT_ROOT'].'/app/carrental/backend/runtime/return/';
			$logfile = fopen($path.'return_'.date('Ymd').'.txt', "a");
			fwrite($logfile, $this->recordReturn);
			fclose($logfile);
		}
		if($this->writeLog){
			$path = $_SERVER['DOCUMENT_ROOT'].'/app/carrental/backend/runtime/carlog/';
			$logfile = fopen($path.'log_'.date('Ymd').'.txt', "a");
			fwrite($logfile, $this->writeLog);
			fclose($logfile);
		}
	}
}

<?php
/**
 * Product配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Pack.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Product_Pack extends Base_Widget
{
	/**
	 * Product表名
	 * @var string
	 */
	protected $table = 'game_product_pack';
	protected $table_code = 'product_pack_code';
	protected $table_gen_log = 'product_pack_code_gen_log';
	protected $table_asign_schedule = 'product_pack_code_asign_schedule';

	/**
	 * 获取单条记录
	 * @param integer $ProductPackId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($ProductPackId,$field = '*')
	{
		$ProductPackId = intval($ProductPackId);
		$AppId = intval($AppId);
		return $this->db->selectRow($this->getDbTable($this->table), $field, '`ProductPackId` = ?', $ProductPackId,false);
	}
	
	/**
	 * 获取单个字段
	 * @param integer $ProductPackId
	 * @param string $field
	 * @return string
	 */
	public function getOne($ProductPackId,$field)
	{
		$ProductPackId = intval($ProductPackId);
		$AppId = intval($AppId);
		return $this->db->selectOne($this->getDbTable($this->table), $field, '`ProductPackId` = ?', $ProductPackId,false);
	}
	/**
	 * 获取单个字段
	 * @param integer $ProductPackId
	 * @param string $field
	 * @return string
	 */
	public function getOneByName($ProductName,$field)
	{
		$ProductName = trim($ProductName);
		return $this->db->selectOne($this->getDbTable($this->table), $field, '`name` = ?', $ProductName);
	}

	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		return $this->db->insert($this->getDbTable($this->table), $bind);
	}

	/**
	 * 删除
	 * @param integer $ProductPackId
	 * @return boolean
	 */
	public function delete($ProductPackId)
	{
		$ProductPackId = intval($ProductPackId);

		return $this->db->delete($this->getDbTable($this->table),'`ProductPackId` = ?', $ProductPackId);
	}

	/**
	 * 更新
	 * @param integer $ProductPackId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($ProductPackId, array $bind)
	{
		$ProductPackId = intval($ProductPackId);

		return $this->db->update($this->getDbTable($this->table), $bind, '`ProductPackId` = ?', $ProductPackId);
	}

	public function getAll($AppId,$fields = "*")
	{
		//初始化查询条件
		$whereApp = $AppId?" AppId = $AppId":"";

		$whereCondition = array($whereApp);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

		$sql = "SELECT $fields FROM " . $this->getDbTable($this->table) . " where 1 ".$where." ORDER BY AppId,ProductPackId ASC";
		$return = $this->db->getAll($sql,false);		
		$AllProductPack = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllProductPack[$value['AppId']][$value['ProductPackId']] = $value;	
			}	
		}
		return $AllProductPack;
	}
	public function InsertGenPackCodeLog($ProductPackId,$AppId,$PartnerId,$GenNum,$EndTime,$needBind,$ManagerId)
	{
		$DataArr['ProductPackId'] = $ProductPackId;
		$DataArr['AppId'] = $AppId;
		$DataArr['PartnerId'] = $PartnerId;
		$DataArr['ManagerId'] = $ManagerId;
		$DataArr['GenNum'] = $GenNum;
		$DataArr['GenTime'] = time();
		$DataArr['EndTime'] = $EndTime;
		$DataArr['needBind'] = $needBind;		
		$table_to_insert = Base_Widget::getDbTable($this->table_gen_log);
		return $this->db->insert($table_to_insert,$DataArr);
	}
	public function GetGenPackCodeLogById($GenId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_gen_log);
		$sql = "SELECT $fields FROM $table_to_process WHERE `GenId` = ?";
		return $this->db->getRow($sql,$GenId);
	}
	public function GenPackCode($GenInfo)
	{
		for($i = 1 ; $i <= $GenInfo['GenNum'] ; $i++)
		{
			$t1 = md5(sprintf("%03d",$GenInfo['AppId']).sprintf("%03d",$GenInfo['PartnerId']).$GenInfo['ManagerId'].time()."lm".rand(0,100000));
			$t2 = $this->parseMd5toNew($t1);
			$hex = dechex($t2['total']);
			$fix = $this->parseMd5toNew($hex);			
			$new_text = $t2['text'].$fix['text'];
			$PackCode[$i] = ($t2['text'].$fix['text']);   	
		}
		return $PackCode;
	}
	public function InsertPackCode($GenInfo,$PackCodeList)
	{
		$this->db->begin();
		$table_to_insert = Base_Widget::getDbTable($this->table_code);

		foreach($PackCodeList as $key => $value)
		{
			$DataArr['ProductPackId'] = $GenInfo['ProductPackId'];
			$DataArr['GenId'] = $GenInfo['GenId'];
			$DataArr['AppId'] = $GenInfo['AppId'];
			$DataArr['PartnerId'] = $GenInfo['PartnerId'];
			$DataArr['ProductPackCode'] = $value;
			$Gen = $this->db->insert($table_to_insert,$DataArr);
			if($Gen)
			{
				$GenedNum++;
			}	
		}
		$updateLog = $this->UpdatePackCodeGenLog($GenInfo,$GenedNum);
		if($updateLog&&$GenedNum)
		{
			$this->db->commit();
			return $GenedNum;			
		}
		else
		{
			$this->db->rollBack();
			return false;
		}	
	}
	public function UpdatePackCodeGenLog($GenInfo,$Gened)
	{
		$bind['GenedNum'] = $Gened;
		$table_to_update = Base_Widget::getDbTable($this->table_gen_log);
		return $this->db->update($table_to_update, $bind, '`GenId` = ?', $GenInfo['GenId']);
	}
	public function parseMd5toNew($md5)
	{
		$a = array(
		'0','1','2','3','4','5','6','7','8','9',
		'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
		'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','Y','U','V','W','X','Y','Z'
		);
		$array = array('0'=>array('value'=>0,'char'=>''),'1'=>array('value'=>0,'char'=>''),'2'=>array('value'=>0,'char'=>''),'3'=>array('value'=>0,'char'=>''),'4'=>array('value'=>0,'char'=>''),'5'=>array('value'=>0,'char'=>''),'6'=>array('value'=>0,'char'=>''),'7'=>array('value'=>0,'char'=>''));
		$i=0;
		foreach($a as $key => $value)
		{
			$arr[$value] = $i;
			$i++;	
		}
		$arr_shift = array_flip($arr);
		for($i=0;$i<strlen($md5);$i++)
		{
			$pos = intval(($i)/4);
			$array[$pos]['value'] += $arr[substr($md5,$i,1)];
			$array[$pos]['char'] = $arr_shift[$array[$pos]['value']];
		}
		$return = array('total'=>0,'text'=>'');
		foreach($array as $key => $value)
		{
			$return['total'] += $value['value'];
			$return['text'].= $value['char'];	
		}
		return $return;
	}
	public function getGenLog($StartDate,$EndDate,$ProductPackId,$needBind,$oWherePartnerPermission,$start,$pagesize)
	{
		$GenLogCount = $this->getGenLogNum($StartDate,$EndDate,$ProductPackId,$needBind,$oWherePartnerPermission);
		if($GenLogCount)
		{
			//查询列
			$select_fields = array("*");
			//分类统计列
	
			//初始化查询条件
			$whereStartDate = $StartDate?" GenTime >= '".strtotime($StartDate)."' ":"";
			$whereEndDate = $EndDate?" GenTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
			$wherePack = $ProductPackId?" ProductPackId = ".$ProductPackId." ":"";
			$whereBind = $needBind?" needBind = ".$needBind." ":"";
			$order = " order by GenTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
	
			$whereCondition = array($wherePack,$whereBind,$whereStartDate,$whereEndDate,$oWherePartnerPermission);
	
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
	
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
			$table_to_process = Base_Widget::getDbTable($this->table_gen_log);
	
			$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$GenLog = $this->db->getAll($sql,array(),false);
			foreach($GenLog as $key => $value)
			{
				$StatArr['GenLog'][$value['GenId']] = $value;
			}
		}
		$StatArr['GenLogCount'] = $GenLogCount;		
		return $StatArr;
	}
	public function getGenLogNum($StartDate,$EndDate,$ProductPackId,$needBind,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'GenLogCount'=>"count(*)");
		//分类统计列

		//初始化查询条件
		$whereStartDate = $StartDate?" GenTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" GenTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$wherePack = $ProductPackId?" ProductPackId = ".$ProductPackId." ":"";
		$whereBind = $needBind?" needBind = ".$needBind." ":"";

		$whereCondition = array($wherePack,$whereBind,$whereStartDate,$whereEndDate,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table_gen_log);

		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$GenLogNum = $this->db->getOne($sql,array(),false);
		return $GenLogNum;
	}
	public function GetPackCodeByGenId($GenId,$oWherePartnerPermission,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_code);
		$sql = "SELECT $fields FROM $table_to_process WHERE `GenId` = ? and ".$oWherePartnerPermission;
		return $this->db->getAll($sql,$GenId);
	}
	public function getProductPackCode($ProductPackCode,$field = '*')
	{		
		return $this->db->selectRow($this->getDbTable($this->table_code), $field, '`ProductPackCode` = ?', $ProductPackCode,false);
	}
	public function getUserProductPackCode($ProductPackCode,$UserId,$field = '*')
	{
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_to_process = Base_Common::getUserTable($this->table_code,$position);  		
		return $this->db->selectRow($table_to_process, $field, '`ProductPackCode` = ?', $ProductPackCode,false);
	}
	public function updatePackCode($ProductPackCode,$bind)
	{
		$this->db->begin();
		$CodeInfo = $this->getProductPackCode($ProductPackCode);
		foreach($bind as $key => $value)
		{
			$CodeInfo[$key] = $value;	
		}
		$update =  $this->db->update($this->getDbTable($this->table_code), $bind, '`ProductPackCode` = ?', $ProductPackCode);				
		if($update)
		{
			if($CodeInfo['AsignUser'])
			{
				$position = Base_Common::getUserDataPositionById($CodeInfo['AsignUser']);
				$table_user = Base_Common::getUserTable($this->table_code,$position);
			}
			elseif ($CodeInfo['UsedUser']) 
			{
				$position = Base_Common::getUserDataPositionById($CodeInfo['UsedUser']);
				$table_user = Base_Common::getUserTable($this->table_code,$position);			
			}
			if($table_user)
			{
				$user = $this->db->replace($table_user,$CodeInfo);
				if($user&&$update)
				{
					$this->db->commit();
					return true;	
				}
				else
				{
					$this->db->rollback();
					return false; 	
				}
			}
			else
			{
				if($update)
				{
					$this->db->commit();
					return true;	
				}
				else
				{
					$this->db->rollback();
					return false; 	
				}	
			}				
		}
		else
		{
			$this->db->rollback();
			return false; 	
		}
	}
	public function usePackCode($ProductPackCode,$ServerId,$UserId,$UsedTime)
	{
		$this->db->begin();
		$PackCode = $this->getProductPackCode($ProductPackCode);
		$Pack = $this->getRow($PackCode['ProductPackId']);
		$Comment = json_decode($Pack['Comment'],true);
		$bind = array('CodeStatus'=>1,'ServerId'=>$ServerId,'UsedUser'=>$UserId,'UsedTime'=>$UsedTime);
		$oProduct = new Config_Product_Product();	
		foreach($Comment as $Type => $TypeInfo)
		{
			foreach($TypeInfo as $ProductId => $ProductCount)
			{
				$Sent = $oProduct->insertIntoProductSendList($ProductPackCode,'ProductPack',$ProductId,$Type,$ProductCount,$UserId,$ServerId,time());
				if(!$Sent)
				{
					$this->db->rollback();
					return false; 
				}				
			}
		}
		$updatePackCode = $this->updatePackCode($ProductPackCode,$bind);
		if($updatePackCode&&$Sent)
		{
			$this->db->commit();
			return true;	
		}
		else
		{
			$this->db->rollback();
			return false; 	
		}
		
	}
	public function getUserPackUserLog($StartDate,$EndDate,$UserId,$ProductPackId,$GenId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('*');
		//分类统计列

		//初始化查询条件
		$whereStartDate = $StartDate?" GenTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" GenTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereUser = $UserId?" UsedUser = ".$UserId." ":"";
		$wherePack = $ProductPackId?" ProductPackId = ".$ProductPackId." ":"";
		$whereGen = $GenId?" GenId in (".$GenId.") ":"";

		$whereCondition = array($wherePack,$whereUser,$whereGen,$whereStartDate,$whereEndDate,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_to_process = Base_Common::getUserTable($this->table_code,$position); 

		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$UseLog = $this->db->getAll($sql);
		return $UseLog;
	}
 	public function getUserProductPackCodeList($UserId,$Used,$ProductPackId,$GenId,$start,$pagesize)
	{
		$UserProductPackCodeCount = $this->getUserProductPackCodeListCount($UserId,$Used,$ProductPackId,$GenId);
    	$StatArr = array('UserProductPackCodeList'=> array(),'UserProductPackCodeCount'=>$UserProductPackCodeCount);

		if($UserProductPackCodeCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			if($Used==0)
			{
				$whereUser = $UserId?" ((UsedUser = ".$UserId.") or (AsignUser = ".$UserId.")) ":"";
			}
			elseif($Used==1)
			{
				$whereUser = $UserId?" (UsedUser = ".$UserId.") ":"";		 	
			}
			elseif($Used==2)
			{
				$whereUser = $UserId?" ((UsedUser = 0) and (AsignUser = ".$UserId.")) ":"";		 	
			}
			$wherePack = $ProductPackId?" ProductPackId = ".$ProductId." ":"";
			$whereGen = $GenId?" GenId = ".$GenId." ":"";
			$whereCondition = array($whereUser,$wherePack,$whereGen);
			
			$order = " order by UsedTime,ProductPackId,GenId";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
			
			$position = Base_Common::getUserDataPositionById($UserId);
			$table_to_process = Base_Common::getUserTable($this->table_code,$position);    		

		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$UserProductPackCodeArr = $this->db->getAll($sql,false);
			if(isset($UserProductPackCodeArr))
		    {
				foreach ($UserProductPackCodeArr as $key => $value) 
				{
					$StatArr['UserProductPackCodeList'][$value['ProductPackCode']] = $value;
				}
		    }
	  	}
	 	$StatArr['UserProductPackCodeCount'] = $UserProductPackCodeCount; 
		return $StatArr;
	}
 	public function getUserProductPackCodeListCount($UserId,$Used,$ProductPackId,$GenId)
	{
		//查询列
		$select_fields = array('UserProductPackCodeCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		if($Used==0)
		{
			$whereUser = $UserId?" ((UsedUser = ".$UserId.") or (AsignUser = ".$UserId.")) ":"";
		}
		elseif($Used==1)
		{
			$whereUser = $UserId?" (UsedUser = ".$UserId.") ":"";		 	
		}
		elseif($Used==2)
		{
			$whereUser = $UserId?" ((UsedUser = 0) and (AsignUser = ".$UserId.")) ":"";		 	
		}
		$wherePack = $ProductPackId?" ProductPackId = ".$ProductId." ":"";
		$whereGen = $GenId?" GenId = ".$GenId." ":"";
		$whereCondition = array($whereUser,$wherePack,$whereGen);
				
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
	    
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_to_process = Base_Common::getUserTable($this->table_code,$position);
	    
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$UserProductPackCodeCount = $this->db->getOne($sql,false);
		if($UserProductPackCodeCount)
    	{
			return $UserProductPackCodeCount;    
		}
		else
		{
			return 0; 	
		}
	}
	public function asignProductPackCode($UserName,$unsignedCode)
	{
		$oUser = new Lm_User();
		$UserInfo  = $oUser->getUserByName($UserName);
		if($UserInfo['UserId'])
		{
			if($unsignedCode['ProductPackCode'])
			{
				$PackInfo = $this->getRow($unsignedCode['ProductPackId']);
				$CodeCount = $this->getUserProductPackCodeListCount($UserInfo['UserId'],0,0,$unsignedCode['GenId']);
				if($CodeCount<$PackInfo['AsignCountLimit'])
				{
					$this->db->begin();
					$unsignedCode['AsignTime'] = time();
					$unsignedCode['AsignUser'] = $UserInfo['UserId'];				
					$position = Base_Common::getUserDataPositionById($UserInfo['UserId']);
					$table_user = Base_Common::getUserTable($this->table_code,$position);
					$user = $this->db->replace($table_user, $unsignedCode);	
					$table_code = Base_Widget::getDbTable($this->table_code);
					$bind = $unsignedCode;
					unset($bind['ProductPackCode']);
					$code = $this->db->update($table_code, $bind, '`ProductPackCode` = ?', $unsignedCode['ProductPackCode']);
					if($code&&$user)
					{
						$this->db->commit();
						return true;	
					}
					else
					{
						$this->db->rollback();
						return false; 	
					}					
				}
				else
				{
					return false; 	
				}			
			}
			else
			{
				return false; 	
			}
		}
		else
		{
			return false; 	
		}		
	}
	public function getunSignedCode($GenId,$limit = 1)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_code);
		$sql = "SELECT * FROM $table_to_process as log where `GenId` = $GenId and UsedUser = 0 and AsignUser = 0 limit $limit";
		$code = $this->db->getAll($sql);
		$CodeList = array();
		if(count($code))
		{
			foreach($code as $key => $value)
			{
				$CodeList[$value['ProductPackCode']] = $value;	
			}	
		}
		return $CodeList;
	}
	public function getAllAsignSchedule($StartDate,$EndDate,$fields = '*')
	{
		//初始化查询条件
		$whereStart = $StartDate?" Date >= '".$StartDate."' ":"";
		$whereEnd = $EndDate?" Date <= '".$EndDate."' ":"";
		
		$whereCondition = array($whereStart,$whereEnd);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

		$sql = "SELECT $fields FROM " . $this->getDbTable($this->table_asign_schedule) . " where 1 ".$where." ORDER BY GenId ASC";
		$return = $this->db->getAll($sql,false);		
		$AllSchedule = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllSchedule[$value['ScheduleId']] = $value;	
			}	
		}
		return $AllSchedule;
	}
	public function insertSchedule(array $bind)
	{
		return $this->db->insert($this->getDbTable($this->table_asign_schedule), $bind);
	}
	public function deleteSchedule($ScheduleId)
	{
		return $this->db->delete($this->getDbTable($this->table_asign_schedule),'`ScheduleId` = ? and AsignedUserCount =0',$ScheduleId);
	}
	public function updateSchedule($ScheduleId, array $bind)
	{
		return $this->db->update($this->getDbTable($this->table_asign_schedule), $bind, '`ScheduleId` = ?', $ScheduleId);
	}

}


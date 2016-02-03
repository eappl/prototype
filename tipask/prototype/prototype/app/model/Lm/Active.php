<?php
/**
 * 用户激活相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Lm_Active extends Base_Widget
{
	//声明所用到的表
	protected $table = 'active_code';
	protected $table_gen_log = 'active_gen_log';
	protected $table_asign_log = 'active_asign_log';
       
	public function InsertGenActiveLog($AppId,$PartnerId,$GenNum,$ManagerId)
	{
		$DataArr['AppId'] = $AppId;
		$DataArr['PartnerId'] = $PartnerId;
		$DataArr['ManagerId'] = $ManagerId;
		$DataArr['GenNum'] = $GenNum;
		$DataArr['GenTime'] = time();	
		$table_to_insert = Base_Widget::getDbTable($this->table_gen_log);
		return $this->db->insert($table_to_insert,$DataArr);
	}
	public function GetGenActiveLogById($GenId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_gen_log);
		$sql = "SELECT $fields FROM $table_to_process WHERE `GenId` = ?";
		return $this->db->getRow($sql,$GenId);
	}
	public function GetAsignActiveLogById($AsignId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_asign_log);
		$sql = "SELECT $fields FROM $table_to_process WHERE `AsignId` = ?";
		return $this->db->getRow($sql,$AsignId);
	}
	public function GenActiveCode($GenInfo)
	{
		for($i = 1 ; $i <= $GenInfo['GenNum'] ; $i++)
		{
			//$ActiveCode[$i] = sprintf("%03d",$GenInfo['AppId']).sprintf("%03d",$GenInfo['PartnerId']).md5(sprintf("%03d",$GenInfo['AppId']).sprintf("%03d",$GenInfo['PartnerId']).$GenInfo['ManagerId'].time()."lm".rand(0,100000));	
			$t1 = md5(sprintf("%03d",$GenInfo['AppId']).sprintf("%03d",$GenInfo['PartnerId']).$GenInfo['ManagerId'].time()."lm".rand(0,100000));
			$t2 = $this->parseMd5toNew($t1);
			$hex = dechex($t2['total']);
			$fix = $this->parseMd5toNew($hex);			
			$new_text = $t2['text'].$fix['text'];
			$ActiveCode[$i] = ($t2['text'].$fix['text']);   	
		}
		return $ActiveCode;
	}
	public function InsertActiveCode($GenInfo,$ActiveCodeList)
	{
		$this->db->begin();
		$table_to_insert = Base_Widget::getDbTable($this->table);

		foreach($ActiveCodeList as $key => $value)
		{
			$DataArr['GenId'] = $GenInfo['GenId'];
			$DataArr['AppId'] = $GenInfo['AppId'];
			$DataArr['PartnerId'] = $GenInfo['PartnerId'];
			$DataArr['ActiveCode'] = $value;
			$Gen = $this->db->insert($table_to_insert,$DataArr);
			if($Gen)
			{
				$GenedNum++;
			}	
		}
		$updateLog = $this->UpdateActiveGenLog($GenInfo,$GenedNum);
		if($updateLog&&$GenedNum)
		{
			$this->db->commit();
			return true;			
		}
		else
		{
			$this->db->rollBack();
			return false;
		}	
	}
	public function UpdateActiveGenLog($GenInfo,$Gened)
	{
		$bind['GenedNum'] = $Gened;
		$table_to_update = Base_Widget::getDbTable($this->table_gen_log);
		return $this->db->update($table_to_update, $bind, '`GenId` = ?', $GenInfo['GenId']);
	}
	public function getGenLogNum($StartDate,$EndDate,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'GenLogCount'=>"count(*)");
		//分类统计列

		//初始化查询条件
		$whereStartDate = $StartDate?" GenTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" GenTime <= '".(strtotime($EndDate)+86400-1)."' ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table_gen_log);

		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$GenLogNum = $this->db->getOne($sql);
		return $GenLogNum;
	}
	public function getAsignLogNum($StartDate,$EndDate,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'GenLogCount'=>"count(*)");
		//分类统计列

		//初始化查询条件
		$whereStartDate = $StartDate?" AsignTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" AsignTime <= '".(strtotime($EndDate)+86400-1)."' ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table_asign_log);

		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$GenLogNum = $this->db->getOne($sql);
		return $GenLogNum;
	}
	public function getGenLog($StartDate,$EndDate,$oWherePartnerPermission,$start,$pagesize)
	{
		//查询列
		$select_fields = array("*");
		//分类统计列

		//初始化查询条件
		$whereStartDate = $StartDate?" GenTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" GenTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$order = " order by GenTime desc";
		$limit = $pagesize?" limit $start,$pagesize":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table_gen_log);

		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
		$GenLog = $this->db->getAll($sql);
		foreach($GenLog as $key => $value)
		{
			$StatArr[$value['GenId']] = $value;
		}
		return $StatArr;
	}
	public function getAsignLog($StartDate,$EndDate,$oWherePartnerPermission,$start,$pagesize)
	{
		//查询列
		$select_fields = array("*");
		//分类统计列

		//初始化查询条件
		$whereStartDate = $StartDate?" AsignTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" AsignTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$order = " order by AsignTime desc";
		$limit = $pagesize?" limit $start,$pagesize":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table_asign_log);

		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
		$GenLog = $this->db->getAll($sql);
		foreach($GenLog as $key => $value)
		{
			$StatArr[$value['AsignId']] = $value;
		}
		return $StatArr;
	}
 	public function GetActiveCodeInfo($ActiveCode,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM $table_to_process WHERE `ActiveCode` = ?";
		return $this->db->getRow($sql,$ActiveCode);
	}
	public function updateUserActiveCode($ActiveCode,$bind)
	{
		$table_to_update = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_update, $bind, '`ActiveCode` = ?', $ActiveCode);
	}
	public function Getactivecode($oWherePartnerPermission,$Start,$Num,$manager_id,$used = 0)
	{
		//查询列
		$select_fields = array("*");
		//分类统计列

		//初始化查询条件
		$whereUsed = $used?" ActiveUser = 0":"";
		$order = " order by GenId";
		$limit = $Num?" limit $Start,$Num":"";

		$whereCondition = array($whereUsed,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);

		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
		$Code = $this->db->getAll($sql);
		return $Code;
	}
	public function getUnUsedActiveCodeCount($oWherePartnerPermission)
	{
		//查询列
		$select_fields = array("UnUsedCount"=>"sum(if(ActiveUser=0,1,0))","UnsignedCount"=>"sum(if(AsignUser=0,1,0))");
		//分类统计列

		//初始化查询条件
		$whereUsed = " ActiveUser = 0";

		$whereCondition = array($whereUsed,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);

		$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$Active = $this->db->getRow($sql);
		return $Active;
	}
	public function InsertAsignActiveCodeLog($AppId,$PartnerId,$AsignNum,$ManagerId,$AsignTime,$AsignReason)
	{
		$DataArr['AppId'] = $AppId;
		$DataArr['PartnerId'] = $PartnerId;
		$DataArr['ManagerId'] = $ManagerId;
		$DataArr['AsignNum'] = $AsignNum;
		$DataArr['AsignTime'] = $AsignTime;
		$DataArr['AsignReason'] = $AsignReason;		
		$table_to_insert = Base_Widget::getDbTable($this->table_asign_log);
		return $this->db->insert($table_to_insert,$DataArr);
	}
	public function updateAsignActiveCodeLog($AsignId,$bind)
	{
		$table_to_update = Base_Widget::getDbTable($this->table_asign_log);
		return $this->db->update($table_to_update, $bind, '`AsignId` = ?', $AsignId);
	}
	public function AsignActiveCodeToId($AppId,$PartnerId,$AsignId,$AsignTime,$AsignNum,$ManagerId)
	{
		$table_to_update = Base_Widget::getDbTable($this->table);
		$sql = "update $table_to_update set AsignId = $AsignId,AsignUser=$ManagerId,AsignTime=$AsignTime where AppId=$AppId and PartnerId=$PartnerId and AsignUser=0 and ActiveUser = 0 limit $AsignNum";
		return $this->db->query($sql);
	}
	public function getAsignById($AsignId,$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM $table_to_process WHERE `AsignId` = ?";
		return $this->db->getAll($sql,$AsignId);
	}
	public function getUsedAsignById($AsignId)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT count(*) as used FROM $table_to_process WHERE `AsignId` = ? and ActiveUser > 0";
		return $this->db->getOne($sql,$AsignId);
	}
    public function getUsedAsignUserIdById($AsignId)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT ActiveUser as UserId FROM $table_to_process WHERE `AsignId` = ? and ActiveUser > 0";
		return $this->db->getAll($sql,$AsignId);
	}
    public function getAsignIdByReason($StartDate,$EndDate,$oWherePartnerPermission,$AsignReason)
	{
	   //初始化查询条件
		$whereStartDate = $StartDate?" AsignTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" AsignTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
        
		$whereCondition = array($whereStartDate,$whereEndDate,$oWherePartnerPermission); 

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
        
		$table_to_process = Base_Widget::getDbTable($this->table_asign_log);
		$sql = "SELECT * FROM $table_to_process WHERE `AsignReason` = '$AsignReason' $where order by AsignTime desc";
		return $this->db->getAll($sql);
	}
	public function AsignActiveCode($AppId,$PartnerId,$ManagerId,$AsignNum,$AsignReason)
	{
		$this->db->begin();
		$AsignTime = time();
		$insertAsignLog = $this->InsertAsignActiveCodeLog($AppId,$PartnerId,$AsignNum,$ManagerId,$AsignTime,$AsignReason);
		if($insertAsignLog)
		{
			$Asign = $this->AsignActiveCodeToId($AppId,$PartnerId,$insertAsignLog,$AsignTime,$AsignNum,$ManagerId);
			if($Asign)
			{
				$AsignedNum = count($this->getAsignById($insertAsignLog));
				$updateNum = $this->updateAsignActiveCodeLog($insertAsignLog,array('AsignedNum'=>$AsignedNum));
				if($updateNum)
				{
					$this->db->commit();
					return $insertAsignLog;
				}
				else
				{
				 	$this->db->rollback();
				 	return false;				 	
				}
			}	
			else 
			{
			 	$this->db->rollback();
			 	return false;
			}
		}
		else 
		{
		 	$this->db->rollback();
		 	return false;
		}			
	}
	public function getUnUsedActiveCode($AppId,$PartnerId,$AsignId,$AsignNum)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "select * from  $table_to_process where AsignId = $AsignId and  AppId=$AppId and PartnerId=$PartnerId and AsignUser=0 and ActiveUser =0 limit $AsignNum";
		return $this->db->getAll($sql);
	}    
    //获取激活码使用情况
    public function getActiveCodeLog($StartDate,$EndDate,$oWherePartnerPermission)
    {
        //查询列
		$select_fields = array(
		'ActiveCount'=>'count(*)',
        'ActiveDate'=>"from_unixtime(ActiveTime,'%Y-%m-%d')",
		);
        
		//初始化查询条件
		$whereStartDate = $StartDate?" ActiveTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" ActiveTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
        $whereActiveUser = " ActiveUser > 0 ";
        
        $group_fields = array('ActiveDate');
        $groups = Base_common::getGroupBy($group_fields);

		$whereCondition = array($whereStartDate,$whereEndDate,$whereActiveUser,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
        
        //初始化结果数组
        $Date = $StartDate;
        $StatArr = array('Active'=>array(),'TotalData'=>array('Total'=>0));
        do
		{
			$StatArr['Active'][$Date] = array('ActiveCount' => 0);
			$Date = date("Y-m-d",(strtotime($Date)+86400));
		}
		while(strtotime($Date) <= strtotime($EndDate));
        
        $table_name = Base_Widget::getDbTable($this->table);
        $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
		$CharacterDeadArr = $this->db->getAll($sql,false);
        
        foreach($CharacterDeadArr as $key=>$val){
            $StatArr['Active'][$val['ActiveDate']]['ActiveCount'] += $val['ActiveCount'];
            $StatArr['TotalData']['Total'] += $val['ActiveCount'];
        }
        	   
		return $StatArr;
    }
    
    //获取激活码申请情况
    public function getActiveAsignLog($StartDate,$EndDate,$oWherePartnerPermission)
    {
        //查询列
		$select_fields = array(
		'ActiveAsignCount'=>'sum(AsignedNum)',
        'AsignDate'=>"from_unixtime(AsignTime,'%Y-%m-%d')",
		);
        
		//初始化查询条件
		$whereStartDate = $StartDate?" AsignTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" AsignTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
        
        $group_fields = array('AsignDate');
        $groups = Base_common::getGroupBy($group_fields);

		$whereCondition = array($whereStartDate,$whereEndDate,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
        
        //初始化结果数组
        $Date = $StartDate;
        $StatArr = array('ActiveAsign'=>array(),'TotalData'=>array('Total'=>0));         
        do
		{
			$StatArr['ActiveAsign'][$Date] = array('ActiveAsignCount' => 0);
			$Date = date("Y-m-d",(strtotime($Date)+86400));
		}
		while(strtotime($Date) <= strtotime($EndDate));
        
        $table_name = Base_Widget::getDbTable($this->table_asign_log);
        $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
		$CharacterDeadArr = $this->db->getAll($sql,false);
        
        foreach($CharacterDeadArr as $key=>$val){
            $StatArr['ActiveAsign'][$val['AsignDate']]['ActiveAsignCount'] = $val['ActiveAsignCount'];
            $StatArr['TotalData']['Total'] += $val['ActiveAsignCount'];
        }
        	   
		return $StatArr;
    }
    
    //获取激活码生成情况
    public function getActiveGenLog($StartDate,$EndDate,$oWherePartnerPermission)
    {
        //查询列
		$select_fields = array(
		'ActiveGenCount'=>'sum(GenedNum)',
        'GenDate'=>"from_unixtime(GenTime,'%Y-%m-%d')",
		);
        
		//初始化查询条件
		$whereStartDate = $StartDate?" GenTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" GenTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
        
        $group_fields = array('GenDate');
        $groups = Base_common::getGroupBy($group_fields);

		$whereCondition = array($whereStartDate,$whereEndDate,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
        
        //初始化结果数组
        $Date = $StartDate;
        $StatArr = array('ActiveGen'=>array(),'TotalData'=>array('Total'=>0));         
        do
		{
			$StatArr['ActiveGen'][$Date] = array('ActiveGenCount' => 0);
			$Date = date("Y-m-d",(strtotime($Date)+86400));
		}
		while(strtotime($Date) <= strtotime($EndDate));
        
        $table_name = Base_Widget::getDbTable($this->table_gen_log);
        $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
		$CharacterDeadArr = $this->db->getAll($sql,false);
        
        foreach($CharacterDeadArr as $key=>$val){
            $StatArr['ActiveGen'][$val['GenDate']]['ActiveGenCount'] = $val['ActiveGenCount'];
            $StatArr['TotalData']['Total'] += $val['ActiveGenCount'];               
        }
        	   
		return $StatArr;
    }
 	public function getActiveDetail($StartDate,$EndDate,$UserId,$oWherePartnerPermission,$start,$pagesize)
	{
		$ActiveCount = $this->getActiveDetailCount($StartDate,$EndDate,$UserId,$oWherePartnerPermission);
	    $StatArr = array('ActiveDetail'=>array());
		if($ActiveCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartDate = $StartDate?" ActiveTime >= ".strtotime($StartDate)." ":"";
			$whereEndDate = $EndDate?" ActiveTime <= ".(strtotime($EndDate)+86400-1)." ":"";
			$whereUser = $UserId?" ActiveUser = ".$UserId." ":"";
	
			$whereCondition = array($whereUser,$whereStartDate,$whereEndDate,$oWherePartnerPermission);
			
			$order = " order by ActiveTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
		
			$table_to_process = Base_Widget::getDbTable($this->table);     	
	
	    	$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;

			$ActiveDetailArr = $this->db->getAll($sql,false);
			if(isset($ActiveDetailArr))
	    	{
	      		foreach ($ActiveDetailArr as $key => $value) 
				{
					$StatArr['ActiveDetail'][$value['ActiveCode']] = $value;
				}
	    	}
  	}
  	
	 	$StatArr['ActiveCount'] = $ActiveCount; 
		return $StatArr;
	}
 	public function getActiveDetailCount($StartDate,$EndDate,$UserId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('ActiveCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartDate = $StartDate?" ActiveTime >= ".strtotime($StartDate)." ":"";
		$whereEndDate = $EndDate?" ActiveTime <= ".(strtotime($EndDate)+86400-1)." ":"";
		$whereUser = $UserId?" ActiveUser = ".$UserId." ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$whereUser,$oWherePartnerPermission);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		
		$table_to_process = Base_Widget::getDbTable($this->table);     	
    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;

		$ActiveCount = $this->db->getOne($sql,false);
		if($ActiveCount)
    {
			return $ActiveCount;    
		}
		else
		{
			return 0; 	
		}
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

	public function check($text,$len)
	{
		if(strlen($text)<=$len)
		{
			return false;	
		}
		else
		{
			$text_to_check = substr($text,0,$len);
			$sign_to_check = substr($text,(-1)*(strlen($text)-$len));
			$a = array(
			'0','1','2','3','4','5','6','7','8','9',
			'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
			'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','Y','U','V','W','X','Y','Z'
			);
			$i=0;
			foreach($a as $key => $value)
			{
				$arr[$value] = $i;
				$i++;	
			}
			$v = 0;
			for($i=0;$i<strlen($text_to_check);$i++)
			{
				$v += $arr[substr($text_to_check,$i,1)];
			}
			$hex = dechex($v);
			$parse = $this->parseMd5toNew($hex);
			if($parse['text']==$sign_to_check)
			{
				return true;	
			}
			else
			{
				return false; 	
			}
		}	
	}
}

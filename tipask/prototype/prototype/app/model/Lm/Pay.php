<?php
/**
 * 支付处理
 * @author Chen <cxd032404@hotmail.com>
 * $Id: Pay.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Lm_Pay extends Base_Widget
{

	/**
	 * 支付表
	 * @var string
	 */
	protected $table = 'lm_pay';
	protected $table_date = 'lm_pay_date';
	protected $table_user = 'lm_pay_user';
	protected $table_first = 'first_pay';
	protected $table_ka91_order  = 'ka91_order';

	public function createUserPayTableDate($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table);

		$table_to_process = Base_Widget::getDbTable($this->table_date)."_".$Date;
		
		$exist = $this->db->checkTableExist($table_to_process);
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE " . $table_to_check;
			$row = $this->db->getRow($sql);
			$sql = $row['Create Table'];
			$sql = str_replace('`' . $this->table . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
			$create = $this->db->query($sql);
			if($create)
			{
				return $table_to_process;
			}
			else
			{
			 return false;	
			}		 	
		}
	}
	public function createUserPayTableUser($UserId)
	{
		$table_to_check = Base_Widget::getDbTable($this->table);

		$position = Base_Common::getUserDataPositionById($UserId);
		
		$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
		$exist = $this->db->checkTableExist($table_to_process);
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE " . $table_to_check;
			$row = $this->db->getRow($sql);
			$sql = $row['Create Table'];
			$sql = str_replace('`' . $this->table . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
			$create = $this->db->query($sql);
			if($create)
			{
				return $table_to_process;
			}
			else
			{
			 return false;	
			}		 	
		}
	}


	/**
	 * 获取单条记录
	 * @param string $recharge_id
	 * @param string $fields
	 * @return array
	 */
	public function getRow($PayId, $fields = '*')
	{
		$Date = substr($PayId,0,6);
		$table_to_process = Base_Widget::getDbTable($this->table);
		$table_to_process .= "_".$Date;
		return $this->db->selectRow($table_to_process, $fields, '`PayId` = ?', $PayId);
	}
	public function getKa91StageOrder($StageOrder, $fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_ka91_order);
		return $this->db->selectRow($table_to_process, $fields, '`StageOrder` = ?', $StageOrder);
	}
	public function insertKa91StageOrder($StageOrder)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_ka91_order);
		return $this->db->insert($table_to_process,$StageOrder);
	}

	/**
	 * 获取订单总数
	 * @author 潘建明
	 * @param $username
	 * @param $status
	 * @return unknown_type
	 */
	public function getRechargeCount($username, $status=2)
	{
		$table = $this->getUserTable($username);
		$orderCount = $this->db->selectOne($table, "count(*) as count", "`username` = ? and `status` = ? and `order_id` = '' ", array($username,$status));
		return $orderCount ? $orderCount : 0;
	}
	/*
	 * 根据帐号获取单个字段
	 * @author 潘建明
	 * @param array $params
	 * @param string $field 字段名
	 * @return string
	 */
	public function getOneByName($username,$params,$where, $field = 'count(*)'){
		$table = $this->getUserTable($username);
		return $this->db->selectOne($table, $field, $where, $params);
	}

	public function createPay($Pay)
	{
		$oOrder = new Lm_Order();
		$oUser = new Lm_User();
		$this->db->begin();
		$Pay['PayId'] = date("YmdHis",$Pay['PayedTime']).sprintf("%04d",rand(1,9999));
		$Date = date("Ym",$Pay['PayedTime']);
		$table_date = $this->createUserPayTableDate($Date);
		$table_user = $this->createUserPayTableUser($Pay['AcceptUserId']);
		$table_first = Base_Widget::getDbTable($this->table_first);
		$Date = $this->db->insert($table_date,$Pay);
		$User = $this->db->insert($table_user,$Pay);				
		$first = $this->db->insert($table_first,$Pay);
		$OrderUpdateArr = array('PayId'=>$Pay['PayId'],'OrderStatus'=>1);
		$OrderUpdate = $oOrder->updateOrder($Pay['OrderId'],$Pay['AcceptUserId'],$OrderUpdateArr);
		//给收款方加余额
		$UserCoinUpdate = $oUser->updateUserCoin($Pay['AcceptUserId'],$Pay['Coin']);
		//给支付方加积分，如无支付方帐号，则不加
		$UserCreditUpdate = $Pay['PayUserId']?$oUser->updateUserCredit($Pay['PayUserId'],$Pay['Coin']):1;
		
		if($Date&&$User&&$OrderUpdate&&$UserCoinUpdate&&$UserCreditUpdate)
		{
			$this->db->commit();
			return $Pay['OrderId'];			
		}
		else
		{
			$this->db->rollBack();
			return false;
		}
	}	
 	public function getPayDetail($StartTime,$EndTime,$UserId,$oWherePartnerPermission,$PassageId,$start,$pagesize)
	{
		$PayCount = $this->getPayDetailCount($StartTime,$EndTime,$UserId,$oWherePartnerPermission,$PassageId);
    	$StatArr = array('PayDetail'=> array(),'PayCount'=>$PayCount);

		if($PayCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartTime = $StartTime?" PayTime >= ".strtotime($StartTime)." ":"";
			$whereEndTime = $EndTime?" PayTime <= ".strtotime($EndTime)." ":"";
			$whereUser = $UserId?" AcceptUserId = ".$UserId." ":"";
			$wherePassage = $PassageId?" PassageId = ".$PassageId." ":"";
	
			$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$oWherePartnerPermission,$wherePassage);
			
			$order = " order by PayTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
		    if($UserId)
		    {
					$position = Base_Common::getUserDataPositionById($UserId);			
					$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];    		
		    }
		    else
		    {
					$Date = date("Ym",strtotime($StartTime));			
					$table_to_process = Base_Widget::getDbTable($this->table_date)."_".$Date;     	
		    }	
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$PayDetailArr = $this->db->getAll($sql,false);
			if(isset($PayDetailArr))
		    {
				foreach ($PayDetailArr as $key => $value) 
					{
						$StatArr['PayDetail'][$value['OrderId']] = $value;
					}
		    }
	  	}
	 	$StatArr['PayCount'] = $PayCount; 
		return $StatArr;
	}
    
    public function getUserPayAmountSum($UserId,$StartTime = 0,$EndTime = 0)
	{
	    //查询列
		$select_fields = array('TotalAmount'=>'sum(Amount)','AppId','PartnerId');
		//分类统计列
		$group_fields = array('AppId','PartnerId');

		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
       
	    //初始化查询条件
		$whereStartTime = $StartTime?" PayTime >= '".$StartTime."' ":"";
		$whereEndTime = $EndTime?" PayTime <= '".$EndTime."' ":"";
		$whereAcceptUserId = $UserId?" AcceptUserId = ".$UserId." ":"";        
        $whereCondition = array($whereAcceptUserId,$whereStartTime,$whereEndTime);
        
        //生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
        //生成查询列
		$fields = Base_common::getSqlFields($select_fields);
              	   
	    $position = Base_Common::getUserDataPositionById($UserId);			
		$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
        
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where.$groups;
		$Pay =  $this->db->getAll($sql,false);
		$ConvertedAmount = 0;
		if(is_array($Pay))
		{
			$oPartnerApp = new Config_Partner_App();
	    	$oArea = new Config_Area();	
			foreach($Pay as $key => $Stat)
			{
				if(!isset($PartnerAppList[$Stat['AppId']][$Stat['PartnerId']]))
				{
					$PartnerAppList[$Stat['AppId']][$Stat['PartnerId']] = $oPartnerApp->getRow(array($Stat['PartnerId'],$Stat['AppId']));
				}
				if(!isset($AreaList[$PartnerAppList[$Stat['AppId']][$Stat['PartnerId']]['AreaId']]))
				{
					$AreaList[$PartnerAppList[$Stat['AppId']][$Stat['PartnerId']]['AreaId']] = $oArea->getRow($PartnerAppList[$Stat['AppId']][$Stat['PartnerId']]['AreaId']);
				}
				$currency_rate = $AreaList[$PartnerAppList[$Stat['AppId']][$Stat['PartnerId']]['AreaId']]['currency_rate'];
				$ConvertedAmount += $Stat['TotalAmount']*$currency_rate;						
			}		
		}
		return $ConvertedAmount;
	}
    
    public function getUserPayCount($UserIdArr)
	{
	    //查询列
		$select_fields = array('AcceptCount'=>'count(distinct AcceptUserId)');
	    
        //生成查询列
		$fields = Base_common::getSqlFields($select_fields);
        
        //初始化返回值
        $UserPayCount = 0;
        
        foreach($UserIdArr as $k=>$UserId){
            //初始化查询条件
    		$whereAcceptUserId = $UserId?" AcceptUserId = ".$UserId." ":"";        
            $whereCondition = array($whereAcceptUserId);
            
            //生成条件列
    		$where = Base_common::getSqlWhere($whereCondition);
            
            $position = Base_Common::getUserDataPositionById($UserId);			
    		$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
            
            $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
            $UserPayCount += $this->db->getOne($sql,false);
        }      	   
	    
		return $UserPayCount;
	}
    
 	public function getPayDetailCount($StartTime,$EndTime,$UserId,$oWherePartnerPermission,$PassageId)
	{
		//查询列
		$select_fields = array('OrderCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartTime = $StartTime?" PayTime >= ".strtotime($StartTime)." ":"";
		$whereEndTime = $EndTime?" PayTime <= ".strtotime($EndTime)." ":"";
		$whereUser = $UserId?" AcceptUserId = ".$UserId." ":"";
		$wherePassage = $PassageId?" PassageId = ".$PassageId." ":"";

		$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$oWherePartnerPermission,$wherePassage);
				
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
	    if($UserId)
	    {
				$position = Base_Common::getUserDataPositionById($UserId);			
				$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];    		
	    }
	    else
	    {
				$Date = date("Ym",strtotime($StartTime));			
				$table_to_process = Base_Widget::getDbTable($this->table_date)."_".$Date;     	
	    }
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$PayCount = $this->db->getOne($sql,false);
		if($PayCount)
    	{
			return $PayCount;    
		}
		else
		{
			return 0; 	
		}
	}
 	public function getPayDay($StartDate,$EndDate,$PassageId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'PayUser'=>'count(distinct(AcceptUserId))',
		'PayCount'=>'count(*)',
		'TotalCoin'=>'sum(Coin)',
		'TotalAmount'=>'sum(Amount)',
		'Date'=>"from_unixtime(PayTime,'%Y-%m-%d')",
		'AppId','PartnerId');
		//分类统计列
		$group_fields = array('Date','AppId','PartnerId');

		//初始化查询条件
		$whereStartDate = $StartDate?" PayTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" PayTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$wherePassage = $PassageId?" PassageId = ".$PassageId." ":"";
		$whereCondition = array($whereStartDate,$whereEndDate,$oWherePartnerPermission,$wherePassage);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		$StatArr['TotalData'] = array('TotalCoin'=>0,'TotalAmount'=>0,'PayUser'=>0,'PayCount'=>0,'ConvertedAmount'=>0);
		do
		{
			$StatArr['PayDate'][$date] = array('Total'=>array('TotalCoin'=>0,'TotalAmount'=>0,'PayUser'=>0,'PayCount'=>0,'ConvertedAmount'=>0));
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));
	    $DateStart = date("Ym",strtotime($StartDate));
	    $DateEnd = date("Ym",strtotime($EndDate));
	    $DateList = array();
	    $Date = $StartDate;
	    do
	    {
	        $D = date("Ym",strtotime($Date));
	        $DateList[] = $D;
	        $Date = date("Y-m-d",strtotime("$Date +1 month"));
	    }
	    while($D!=$DateEnd);
	    $oPartnerApp = new Config_Partner_App();
	    $oArea = new Config_Area();
	    foreach($DateList as $key => $value)
	    {
			$table_name = Base_Widget::getDbTable($this->table_date)."_".$value;
			$sql = "SELECT  $fields FROM $table_name as log where 1 ".$where.$groups;
			$PayDateArr = $this->db->getAll($sql);
			if(is_array($PayDateArr))
			{
				foreach ($PayDateArr as $key => $Stat) 
				{
					if(!isset($PartnerAppList[$Stat['AppId']][$Stat['PartnerId']]))
					{
						$PartnerAppList[$Stat['AppId']][$Stat['PartnerId']] = $oPartnerApp->getRow(array($Stat['PartnerId'],$Stat['AppId']));
					}
					if(!isset($AreaList[$PartnerAppList[$Stat['AppId']][$Stat['PartnerId']]['AreaId']]))
					{
						$AreaList[$PartnerAppList[$Stat['AppId']][$Stat['PartnerId']]['AreaId']] = $oArea->getRow($PartnerAppList[$Stat['AppId']][$Stat['PartnerId']]['AreaId']);
					}
					$currency_rate = $AreaList[$PartnerAppList[$Stat['AppId']][$Stat['PartnerId']]['AreaId']]['currency_rate'];
					//累加数据
					if(isset($StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]))
					{
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['PayCount'] += $Stat['PayCount'];
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['PayUser'] += $Stat['PayUser'];
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['TotalCoin'] += $Stat['TotalCoin'];
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['TotalAmount'] += $Stat['TotalAmount'];
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['ConvertedAmount'] += $Stat['TotalAmount']*$currency_rate;
					}
					else
					{
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']] = array('TotalCoin'=>0,'TotalAmount'=>0,'PayCount'=>0,'PayUser'=>0,'ConvertedAmount'=>0);
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['PayCount'] += $Stat['PayCount'];
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['PayUser'] += $Stat['PayUser'];
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['TotalCoin'] += $Stat['TotalCoin'];
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['TotalAmount'] += $Stat['TotalAmount'];
						$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['ConvertedAmount'] += $Stat['TotalAmount']*$currency_rate;
					}			
					$StatArr['PayDate'][$Stat['Date']]['Total']['PayCount'] += $Stat['PayCount'];
					$StatArr['PayDate'][$Stat['Date']]['Total']['PayUser'] += $Stat['PayUser'];
					$StatArr['PayDate'][$Stat['Date']]['Total']['ConvertedAmount'] += $Stat['TotalAmount']*$currency_rate;			
				
					$StatArr['TotalData']['PayCount'] += $Stat['PayCount'];
					$StatArr['TotalData']['ConvertedAmount'] += $Stat['TotalAmount']*$currency_rate;
				
				}
				$StatArr['TotalData']['AmountPerPay'] = $StatArr['TotalData']['PayCount']?$StatArr['TotalData']['ConvertedAmount']/$StatArr['TotalData']['PayCount']:0;
			}
			
    	}
		return $StatArr;
	}
 	public function getFirstPayDayBySource($StartDate,$EndDate,$RegStartDate,$RegEndDate,$oWherePartnerPermission,$SourceProjectId,$SourceList,$SourceDetail)
	{
		//查询列
		$select_fields = array(
		'FirstPayUser'=>'count(distinct(AcceptUserId))',
		'TotalAmount'=>'sum(Amount)',
		'Date'=>"from_unixtime(PayTime,'%Y-%m-%d')",
		'UserSourceId','UserSourceDetail','UserSourceProjectId','PartnerId','AppId',);
		//分类统计列
		$group_fields = array('Date','PartnerId','AppId','UserSourceId','UserSourceDetail','UserSourceProjectId');

		//初始化查询条件
		$whereStartDate = $StartDate?" PayedTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" PayedTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
		$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";
		if($SourceList)
		{
			foreach($SourceList as $Key => $value)
			{
				$t[$Key] = $Key;	
			}
			$whereSource = " UserSourceId in (".implode(",",$t).")";	
		}
		else
		{
		 	$whereSource = "";
		}
		$WhereSourceDetail = $SourceDetail?" UserSourceDetail = ".$SourceDetail." ":"";
		$WhereSourceProject = $SourceProjectId?" UserSourceProjectId = ".$SourceProjectId." ":"";
		$whereCondition = array($whereStartDate,$whereEndDate,$whereRegStartDate,$whereRegEndDate,$whereServer,$oWherePartnerPermission,$whereSource,$WhereSourceDetail,$WhereSourceProject);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		do
		{
			$StatArr['PayDate'][$date] = array();
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));
	    $table_name = Base_Widget::getDbTable($this->table_first);
	    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
		$PayDateArr = $this->db->getAll($sql,false);
		if(is_array($PayDateArr))
	    {
	      	foreach ($PayDateArr as $key => $Stat) 
			{
				//累加数据
				if(isset($StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]))
				{
					$StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['FirstPayUser'] += $Stat['FirstPayUser'];
					$StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['TotalAmount'] += $Stat['TotalAmount'];
				}
				else
				{
					$StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['Detail'][$Stat['AppId']][$Stat['PartnerId']] = array('TotalAmount'=>0);
					$StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['FirstPayUser'] += $Stat['FirstPayUser'];
					$StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['TotalAmount'] += $Stat['TotalAmount'];
				}
				$StatArr['TotalData']['FirstPayUser'] += $Stat['FirstPayUser'];
			}
	    }
		return $StatArr;
	}
 	public function getPayDayBySource($StartDate,$EndDate,$RegStartDate,$RegEndDate,$oWherePartnerPermission,$SourceProjectId,$SourceList,$SourceDetail)
	{
		//查询列
		$select_fields = array(
		'PayUser'=>'count(distinct(AcceptUserId))',
		'TotalAmount'=>'sum(Amount)',
		'Date'=>"from_unixtime(PayTime,'%Y-%m-%d')",
		'UserSourceId','UserSourceDetail','UserSourceProjectId','PartnerId','AppId',);
		//分类统计列
		$group_fields = array('Date','PartnerId','AppId','UserSourceId','UserSourceDetail','UserSourceProjectId');

		//初始化查询条件
		$whereStartDate = $StartDate?" PayedTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" PayedTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
		$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";
		if($SourceList)
		{
			foreach($SourceList as $Key => $value)
			{
				$t[$Key] = $Key;	
			}
			$whereSource = " UserSourceId in (".implode(",",$t).")";	
		}
		else
		{
		 	$whereSource = "";
		}
		$WhereSourceDetail = $SourceDetail?" UserSourceDetail = ".$SourceDetail." ":"";
		$WhereSourceProject = $SourceProjectId?" UserSourceProjectId = ".$SourceProjectId." ":"";
		$whereCondition = array($whereStartDate,$whereEndDate,$whereRegStartDate,$whereRegEndDate,$whereServer,$oWherePartnerPermission,$whereSource,$WhereSourceDetail,$WhereSourceProject);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		do
		{
			$StatArr['PayDate'][$date] = array();
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));
	    $DateStart = date("Ym",strtotime($StartDate));
	    $DateEnd = date("Ym",strtotime($EndDate));
	    $DateList = array();
	    $Date = $StartDate;
	    do
	    {
	        $D = date("Ym",strtotime($Date));
	        $DateList[] = $D;
	        $Date = date("Y-m-d",strtotime("$Date +1 month"));
	    }
	    while($D!=$DateEnd);
	    $oPartnerApp = new Config_Partner_App();
	    $oArea = new Config_Area();
	    foreach($DateList as $key => $value)
	    {
	      	$table_name = Base_Widget::getDbTable($this->table_date)."_".$value;
		    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$PayDateArr = $this->db->getAll($sql,false);
			if(is_array($PayDateArr))
		    {
		      	foreach ($PayDateArr as $key => $Stat) 
				{
					//累加数据
					if(isset($StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]))
					{
						$StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['PayUser'] += $Stat['PayUser'];
						$StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['TotalAmount'] += $Stat['TotalAmount'];
					}
					else
					{
						$StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['Detail'][$Stat['AppId']][$Stat['PartnerId']] = array('TotalAmount'=>0);
						$StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['PayUser'] += $Stat['PayUser'];
						$StatArr['PayDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['TotalAmount'] += $Stat['TotalAmount'];
					}
					$StatArr['TotalData']['PayUser'] += $Stat['PayUser'];
				}
		    }
		}
	return $StatArr;
	}
 	public function getFirstPayDay($StartDate,$EndDate,$RegStartDate,$RegEndDate,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'FirstPayUser'=>'count(distinct(AcceptUserId))',
		'TotalAmount'=>'sum(Amount)',
		'Date'=>"from_unixtime(PayTime,'%Y-%m-%d')",'PartnerId','AppId',);
		//分类统计列
		$group_fields = array('Date','PartnerId','AppId');

		//初始化查询条件
		$whereStartDate = $StartDate?" PayedTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" PayedTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
		$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$whereRegStartDate,$whereRegEndDate,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		do
		{
			$StatArr['PayDate'][$date] = array('FirstPayUser'=>0,'Detail'=>array());
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));
	    $table_name = Base_Widget::getDbTable($this->table_first);
	    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
		$PayDateArr = $this->db->getAll($sql,false);
		if(is_array($PayDateArr))
	    {
	      	foreach ($PayDateArr as $key => $Stat) 
			{
				//累加数据
				if(isset($StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]))
				{
					$StatArr['PayDate'][$Stat['Date']]['FirstPayUser'] += $Stat['FirstPayUser'];
					$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['TotalAmount'] += $Stat['TotalAmount'];
				}
				else
				{
					$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']] = array('TotalAmount'=>0);
					$StatArr['PayDate'][$Stat['Date']]['FirstPayUser'] += $Stat['FirstPayUser'];
					$StatArr['PayDate'][$Stat['Date']]['Detail'][$Stat['AppId']][$Stat['PartnerId']]['TotalAmount'] += $Stat['TotalAmount'];
				}
			}
	    }
		return $StatArr;
	}
 	public function getTotalPayUser($Date,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('PayUser'=>'count(distinct(AcceptUserId))');
		//分类统计列

		//初始化查询条件
		$whereDate = $Date?" PayedTime < '".(strtotime($Date)+86400)."' ":"";

		$whereCondition = array($whereDate,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

	    $table_name = Base_Widget::getDbTable($this->table_first);
	    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where;

		$PayUser = $this->db->getOne($sql,false);

		return $PayUser;
	}
	public function createKa91Pay($Order,$Pay)
	{
		$oOrder = new Lm_Order();
		$oUser = new Lm_User();
		$this->db->begin();
		$Order['OrderId'] = date("YmdHis",$Order['OrderTime']).sprintf("%03d",$Order['AppId']).sprintf("%03d",$Order['PartnerId']).sprintf("%04d",rand(1,9999));
		
		//支付订单
		$Pay['OrderId'] = $Order['OrderId'];
		$Pay['PayId'] = date("YmdHis",$Pay['PayedTime']).sprintf("%04d",rand(1,9999));
		$Date = date("Ym",$Pay['PayedTime']);
		$table_date = $this->createUserPayTableDate($Date);
		$table_user = $this->createUserPayTableUser($Pay['AcceptUserId']);
		$table_first = Base_Widget::getDbTable($this->table_first);
		$Date = $this->db->insert($table_date,$Pay);
		$User = $this->db->insert($table_user,$Pay);				
		$first = $this->db->insert($table_first,$Pay);
		//合作方订单唯一保证
		$StageOrderArr = array('OrderId'=>$Pay['OrderId'],'StageOrder'=>$Pay['StageOrder']);
		$InsertStageOrder = $this->insertKa91StageOrder($StageOrderArr);
		//给收款方加余额
		$UserCoinUpdate = $oUser->updateUserCoin($Pay['AcceptUserId'],$Pay['Coin']);
		//给支付方加积分，如无支付方帐号，则不加
		$UserCreditUpdate = $Pay['PayUserId']?$oUser->updateUserCredit($Pay['PayUserId'],$Pay['Coin']):1;
		//生成订单
		$Order['PayId'] = $Pay['PayId'];
		$Date = date("Ym",$Order['OrderTime']);
		$table_date = $oOrder->createUserOrderTableDate($Date);
		$table_user = $oOrder->createUserOrderTableUser($Order['AcceptUserId']);
		$table_to_insert = $table_date;
		$order_date = $this->db->insert($table_to_insert,$Order);
		if(intval($order_date)&&intval($order_date)!=23000)
		{
			$table_to_insert = $table_user;
			$user = $this->db->insert($table_to_insert,$Order);
		}
		
		if($InsertStageOrder&&$Date&&$User&&$UserCoinUpdate&&$UserCreditUpdate&&$order_date&&$user)
		{
			$this->db->commit();
			return $Pay['OrderId'];			
		}
		else
		{
			$this->db->rollBack();
			return false;
		}
	}	
}

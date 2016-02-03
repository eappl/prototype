<?php
/**
 * 支付处理
 * @author Chen <cxd032404@hotmail.com>
 * $Id: Order.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Lm_Order extends Base_Widget
{

	/**
	 * 支付订单表
	 * @var string
	 */
	protected $table = 'lm_order';
	protected $table_user = 'lm_order_user';
	protected $table_date = 'lm_order_date';

	public function createUserOrderTableDate($Date)
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
	public function createUserOrderTableUser($UserId)
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
	 * @param string $order_id
	 * @param string $fields
	 * @return array
	 */
	public function getRow($OrderId, $fields = "*")
	{
		$Date = substr($OrderId,0,6);
		$table_to_process = Base_Widget::getDbTable($this->table_date);
		$table_to_process .= "_".$Date;
		$sql = "SELECT $fields FROM $table_to_process WHERE `OrderId` = ?";	
		return $this->db->getRow($sql, $OrderId);
	}
/**
	 * 支付下单
	 * @desc 所有兑换游戏币的都要入Order表     第三方 | 平台币  | 直充
	 * @param array $params username AppId ServerId coin ip
	 * @return mixed 订单号|false
	 */
	public function createOrder($Order)
	{
		$this->db->begin();
		$Order['OrderId'] = date("YmdHis",$Order['OrderTime']).sprintf("%03d",$Order['AppId']).sprintf("%03d",$Order['PartnerId']).sprintf("%04d",rand(1,9999));
		$Order['OrderStatus'] = 0;
		$Date = date("Ym",$Order['OrderTime']);
		$table_date = $this->createUserOrderTableDate($Date);
		$table_user = $this->createUserOrderTableUser($Order['AcceptUserId']);
		$table_to_insert = $table_date;
		$date = $this->db->insert($table_to_insert,$Order);
		if(intval($date)&&intval($date)!=23000)
		{
			$table_to_insert = $table_user;
			$user = $this->db->insert($table_to_insert,$Order);
			if($date&&$user)
			{
				$this->db->commit();
				return $Order['OrderId'];
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
	
	public function updateOrder($OrderId,$UserId,$bind)
	{
		$this->db->begin();
		$Date = substr($OrderId,0,6);
		$table_to_update = Base_Widget::getDbTable($this->table_date);
		$table_to_update .= "_".$Date;
		$Date = $this->db->update($table_to_update, $bind, '`OrderId` = ?', $OrderId);
		$position = Base_Common::getUserDataPositionById($UserId);		
		$table_to_update = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
		$User = $this->db->update($table_to_update, $bind, '`OrderId` = ?', $OrderId);
		if($Date&&$User)
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
 	public function getOrderDetail($StartTime,$EndTime,$UserId,$OrderId,$OrderStatus,$ServerId,$oWherePartnerPermission,$start,$pagesize)
	{
		if($OrderId)
		{
			$OrderInfo = $this->getRow($OrderId);
			if($OrderInfo['OrderId'])
			{
				$StatArr = array('OrderDetail'=>array($OrderInfo['OrderId']=>$OrderInfo),'OrderCount'=>1);
			}
			else
			{
			 	$StatArr = array('OrderDetail'=>array(),'OrderCount'=>0);
			}				
		}
		else 
		{
			$OrderCount = $this->getOrderDetailCount($StartTime,$EndTime,$UserId,$OrderStatus,$ServerId,$oWherePartnerPermission);
			if($OrderCount)
			{
					//查询列
				$select_fields = array('*');
				//分类统计列
		
				//初始化查询条件
				$whereStartTime = $StartTime?" OrderTime >= ".strtotime($StartTime)." ":"";
				$whereEndTime = $EndTime?" OrderTime <= ".strtotime($EndTime)." ":"";
				$whereUser = $UserId?" AcceptUserId = ".$UserId." ":"";
				$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
				$whereStatus = $OrderStatus!=5?" OrderStatus = ".$OrderStatus." ":"";
		
				$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereStatus,$whereServer,$oWherePartnerPermission);
				
				$order = " order by OrderTime desc";
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
			    $StatArr = array('OrderDetail'=>array());
			
			    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
				$OrderDetailArr = $this->db->getAll($sql,false);
				if(isset($OrderDetailArr))
			    {
		      		foreach ($OrderDetailArr as $key => $value) 
					{
						$StatArr['OrderDetail'][$value['OrderId']] = $value;
					}
			    }
	  		}	  	
		 	$StatArr['OrderCount'] = $OrderCount; 						
		}

		return $StatArr;
	}
 	public function getOrderDetailCount($StartTime,$EndTime,$UserId,$OrderStatus,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('OrderCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartTime = $StartTime?" OrderTime >= ".strtotime($StartTime)." ":"";
		$whereEndTime = $EndTime?" OrderTime <= ".strtotime($EndTime)." ":"";
		$whereUser = $UserId?" AcceptUserId = ".$UserId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
		$whereStatus = $OrderStatus!=5?" OrderStatus = ".$OrderStatus." ":"";

		$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereStatus,$whereServer,$oWherePartnerPermission);
		
		
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
		$OrderCount = $this->db->getOne($sql,false);
		if($OrderCount)
    	{
			return $OrderCount;    
		}
		else
		{
			return 0; 	
		}
	}
 	public function getUserOrderList($UserId,$AppId,$PartnerId,$ServerId,$StartDate,$EndDate,$PageSize,$start,$OrderStatus)
	{
		$OrderCount = $this->getUserOrderCount($UserId,$AppId,$PartnerId,$ServerId,$StartDate,$EndDate,$OrderStatus);
		if($OrderCount['OrderCount'])
		{
			//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartDate = $StartDate?" OrderTime >= ".strtotime($StartDate)." ":"";
			$whereEndDate = $EndDate?" OrderTime <= ".(strtotime($EndDate)+86400-1)." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
			$wherePartner = $PartnerId?" PartnerId = ".$PartnerId." ":"";
	
			$whereApp = $AppId?" AppId = ".$AppId." ":"";
	
			$whereStatus = $OrderStatus!=5?" OrderStatus = ".$OrderStatus." ":"";
			$whereUser = $UserId?" AcceptUserId = ".$UserId." ":"";
	
			$whereCondition = array($whereStartDate,$whereEndDate,$whereStatus,$whereServer,$wherePartner,$whereApp,$whereUser);
			
			$order = " order by OrderTime desc";
			$limit = $PageSize?" limit $start,$PageSize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);

	    
			$position = Base_Common::getUserDataPositionById($UserId);	
			$table_name = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
			$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$order.$limit;
			$OrderDetailArr = $this->db->getAll($sql,false);
			if(isset($OrderDetailArr))
			{
        		foreach ($OrderDetailArr as $key => $value) 
				{
					$StatArr['OrderDetail'][$value['OrderId']] = $value;
				}
			}
	    
		}
	 	$StatArr['OrderCount'] = $OrderCount['OrderCount']; 
	 	return $StatArr;   
	}	
 	public function getUserOrderCount($UserId,$AppId,$PartnerId,$ServerId,$StartDate,$EndDate,$OrderStatus)
	{
		//查询列
		$select_fields = array('OrderCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartDate = $StartDate?" OrderTime >= ".strtotime($StartDate)." ":"";
		$whereEndDate = $EndDate?" OrderTime <= ".(strtotime($EndDate)+86400-1)." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
		$wherePartner = $PartnerId?" PartnerId = ".$PartnerId." ":"";

		$whereApp = $AppId?" AppId = ".$AppId." ":"";

		$whereStatus = $OrderStatus!=5?" OrderStatus = ".$OrderStatus." ":"";
		$whereUser = $UserId?" AcceptUserId = ".$UserId." ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$whereStatus,$whereServer,$wherePartner,$whereApp,$whereUser);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

    	$StatArr = array('OrderCount'=>0);    
    	$position = Base_Common::getUserDataPositionById($UserId);	
		$table_name = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
		$sql = "SELECT $fields FROM $table_name as log where 1 ".$where;
		$OrderCount = $this->db->getRow($sql,false);
		if(isset($OrderCount))
    	{
			$StatArr['OrderCount'] += $OrderCount['OrderCount'];
    	}    
   		return $StatArr;
	}	
}

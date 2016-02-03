<?php
/**
 * 生成后台个人设置setting 中的图片
 */
!defined('IN_TIPASK') && exit('Access Denied');

class bind_logmodel {

    var $base;
	var $table_bindLog = 'bind_log';
	var $table_orderToPorcess = 'order_log';
	var $table_order = 'order_log_e';
	var $pdo = null;
	
    function bind_logmodel(&$base)
	{
        $this->base = $base;
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }

	function getOrderList($ConditionList,$page,$pagesize)
	{
		$whereStartTime = $ConditionList['StartDate']?" deal_time >= ".strtotime($ConditionList['StartDate'])." ":"";
		$whereOperator = $ConditionList['scopid']!=""?" scopid = '".$ConditionList['scopid']."' ":"";
		$whereBind = $ConditionList['bid']!=0?" bind_type = ".$ConditionList['bid']." ":"";
		$whereServiceType = $ConditionList['ServiceType']!=0?" order_type = ".$ConditionList['ServiceType']." ":"";
		if($ConditionList['author']!="")
		{
			$whereEndTime = $ConditionList['EndDate']?" deal_time < ".(strtotime($ConditionList['EndDate'])+86400)." ":"";
			$table_name = $this->base->getDbTable($this->table_order);
			if($ConditionList['bid']==1)
			{
				$whereAuthor = " author_buyer = '".$ConditionList['author']."' ";
				$Suffix = '_author'.$this->base->getSuffixTable($ConditionList['author']);
			}
			elseif($ConditionList['bid']==2)
			{
				$whereAuthor = " author_seller = '".$ConditionList['author']."' ";
				$Suffix = '_author'.$this->base->getSuffixTable($ConditionList['author']);
			}
			else
			{
				$whereAuthor = " ((author_buyer = '".$ConditionList['author']."' and bind_type = 1) or  (author_seller = '".$ConditionList['author']."' and bind_type = 2)) ";
				$Suffix = '_author'.$this->base->getSuffixTable($ConditionList['author']);			
			}			
			$table_name .= $Suffix;	
		}
		else
		{
			$whereEndTime = $ConditionList['EndDate']?" deal_time < ".(strtotime($ConditionList['EndDate'])+86400)." ":"";				
			$table_name = $this->base->getDbTable($this->table_order);
			$Suffix = '_date_'.date("Ym",strtotime($ConditionList['StartDate']));	
			$table_name.=$Suffix;
		}
		$whereCondition = array($whereStartTime,$whereEndTime,$whereBind,$whereAuthor,$whereServiceType,$whereOperator);

		foreach($whereCondition as $key => $value)
		{
			if(trim($value)=="")
			{
				unset($whereCondition[$key]);
			}
		}
		if(count($whereCondition)>0)
		{
			$where = "and ".implode(" and ",$whereCondition);
		}
		else
		{
			$where = "";
		}				
		$count_sql = "select count(1) as order_count,sum(amount) as total_amount,sum(commission) as total_commission from $table_name where 1 ".$where;
		$OrderCount = $this->db->fetch_first($count_sql);
		if($OrderCount['order_count']>0)
		{
			$sql = "select * from $table_name where 1  ".$where." order by deal_time desc";
			$limit = $page==0?"":" limit ".(($page-1)*$pagesize).",$pagesize";
			$sql.=$limit;
			$rs = $this->db->fetch_all($sql);
			$returnArr = array("OrderCount"=>$OrderCount['order_count'],"total_amount"=>$OrderCount['total_amount'],"total_commission"=>$OrderCount['total_commission'],"OrderList"=>$rs);
		}
		else
		{
			$returnArr = array("OrderCount"=>0,"total_amount"=>0,"total_commission"=>0,"OrderList"=>array());
		}
		return $returnArr;
	}
	function getOrderStatus($ConditionList)
	{
		$whereStartTime = $ConditionList['StartDate']?" deal_time >= ".strtotime($ConditionList['StartDate'])." ":"";
		$whereOperator = $ConditionList['scopid']!=""?" scopid = '".$ConditionList['scopid']."' ":"";
		$whereBind = $ConditionList['bid']!=0?" bind_type = ".$ConditionList['bid']." ":"";
		$whereServiceType = $ConditionList['ServiceType']!=0?" order_type = ".$ConditionList['ServiceType']." ":"";
		if($ConditionList['author']!="")
		{
			$whereEndTime = $ConditionList['EndDate']?" deal_time < ".(strtotime($ConditionList['EndDate'])+86400)." ":"";
			$table_name = $this->base->getDbTable($this->table_order);
			if($ConditionList['bid']==1)
			{
				$whereAuthor = " author_buyer = '".$ConditionList['author']."' ";
				$Suffix = '_author'.$this->base->getSuffixTable($ConditionList['author']);
			}
			elseif($ConditionList['bid']==2)
			{
				$whereAuthor = " author_seller = '".$ConditionList['author']."' ";
				$Suffix = '_author'.$this->base->getSuffixTable($ConditionList['author']);
			}
			else
			{
				$whereAuthor = " ((author_buyer = '".$ConditionList['author']."' and bind_type = 1) or  (author_seller = '".$ConditionList['author']."' and bind_type = 2)) ";
				$Suffix = '_author'.$this->base->getSuffixTable($ConditionList['author']);			
			}			
			$table_name .= $Suffix;	
		}
		else
		{
			$whereEndTime = $ConditionList['EndDate']?" deal_time < ".(strtotime($ConditionList['EndDate'])+86400)." ":"";				
			$table_name = $this->base->getDbTable($this->table_order);
			$Suffix = '_date_'.date("Ym",strtotime($ConditionList['StartDate']));	
			$table_name.=$Suffix;
		}
		$whereCondition = array($whereStartTime,$whereEndTime,$whereBind,$whereAuthor,$whereServiceType,$whereOperator);

		foreach($whereCondition as $key => $value)
		{
			if(trim($value)=="")
			{
				unset($whereCondition[$key]);
			}
		}
		if(count($whereCondition)>0)
		{
			$where = "and ".implode(" and ",$whereCondition);
		}
		else
		{
			$where = "";
		}				
		$sql = "select scopid,count(1) as order_count,sum(commission) as total_commission,sum(amount) as total_amount from $table_name where 1  ".$where." group by scopid";
		$rs = $this->db->fetch_all($sql);
		$returnArr = array('OrderStatus'=>$rs);
		return $returnArr;
	}

	function getBindLogList($ConditionList,$page,$pagesize)
	{
		$whereStartTime = $ConditionList['StartDate']?" time >= ".strtotime($ConditionList['StartDate'])." ":"";
		$whereOperator = $ConditionList['scopid']!=""?" scid = '".$ConditionList['scopid']."' ":"";
		$whereAuthor = $ConditionList['author']!=""?" author = '".$ConditionList['author']."' ":"";
		$whereBindType = $ConditionList['bid']!="all"?" bind_type = '".$ConditionList['bid']."' ":" bind_type in ('bind','unbind')";
		
			$whereEndTime = $ConditionList['EndDate']?" time < ".(strtotime($ConditionList['EndDate'])+86400)." ":"";				
			$table_name = $this->base->getDbTable($this->table_bindLog);
			$Suffix = '_date_'.date("Ym",strtotime($ConditionList['StartDate']));	
			$table_name.=$Suffix;
		
		$whereCondition = array($whereStartTime,$whereEndTime,$whereBindType,$whereAuthor,$whereOperator);

		foreach($whereCondition as $key => $value)
		{
			if(trim($value)=="")
			{
				unset($whereCondition[$key]);
			}
		}
		if(count($whereCondition)>0)
		{
			$where = "and ".implode(" and ",$whereCondition);
		}
		else
		{
			$where = "";
		}				
		$count_sql = "select count(*) from $table_name where 1 ".$where;
		$BindlogCount = $this->db->result_first($count_sql);
		if($BindlogCount>0)
		{
			$sql = "select * from $table_name where 1  ".$where." order by time desc";
			$limit = $page==0?"":" limit ".(($page-1)*$pagesize).",$pagesize";
			$sql.=$limit;
			$rs = $this->db->fetch_all($sql);
			$returnArr = array("BindLogCount"=>$BindlogCount,"BindLogList"=>$rs);
		}
		else
		{
			$returnArr = array("BindLogCount"=>0,"BindLogList"=>array());
		}
		return $returnArr;
	}	
}
?>

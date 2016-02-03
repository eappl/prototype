<?php
/**
 * 用户相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Lm_Purchase extends Base_Widget
{
	//声明所用到的表
	protected $table = 'lm_item_Purchase_log';
    protected $table_npc_item_purchase = 'lm_npc_item_Purchase_log';
    protected $table_money_type = 'game_money_type';
    protected $table_user_lastmoney = 'lm_user_lastmoney';

	public function createPurchaseTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table);
		$table_to_process = Base_Widget::getDbTable($this->table)."_".$Date;
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
			$sql = str_replace('`' . $this->table. '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
    
    public function createPurchaseTableByuser($User)
	{
		$table_to_check = Base_Widget::getDbTable($this->table);
		$table_to_process = Base_Widget::getDbTable($this->table)."_user_".$User;
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
			$sql = str_replace('`' . $this->table. '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
    
    public function createNpcPurchaseTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_npc_item_purchase);
		$table_to_process = Base_Widget::getDbTable($this->table_npc_item_purchase)."_".$Date;
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
			$sql = str_replace('`' . $this->table_npc_item_purchase. '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
    
    public function createNpcPurchaseTableByuser($User)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_npc_item_purchase);
		$table_to_process = Base_Widget::getDbTable($this->table_npc_item_purchase)."_user_".$User;
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
			$sql = str_replace('`' . $this->table_npc_item_purchase. '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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

	public function InsertPurchaseLog($DataArr)
	{
	    $this->db->begin();
		$Date = date("Ym",$DataArr['PurchaseTime']);
		$table_date = $this->createPurchaseTable($Date);
		$inserDate = $this->db->insert($table_date,$DataArr);
        
        $position = Base_Common::getUserDataPositionById($DataArr['UserId']);    
		$table_date = $this->createPurchaseTableByuser($position['db_fix']);		
		$inserUser = $this->db->insert($table_date,$DataArr);
        
        if($inserDate && $inserUser){
            $this->db->commit();
            return true;
        }else{
            $this->db->rollback();
            return false;
        }
	}
    
    public function InsertPurchaseLogByuser($DataArr)
	{
        $position = Base_Common::getUserDataPositionById($DataArr['UserId']);    
		$table_date = $this->createPurchaseTableByuser($position['db_fix']);		
		return $this->db->insert($table_date,$DataArr);
	}
    
    public function InsertNpcPurchaseLog($DataArr)
	{
	    $this->db->begin();
		$Date = date("Ym",$DataArr['NpcPurchaseTime']);
		$table_date = $this->createNpcPurchaseTable($Date);	
		$inserDate = $this->db->insert($table_date,$DataArr);
        
        $position = Base_Common::getUserDataPositionById($DataArr['UserId']);  
		$table_date = $this->createNpcPurchaseTableByuser($position['db_fix']);	
		$inserUser = $this->db->insert($table_date,$DataArr);
        
        if($inserDate && $inserUser){
            $this->db->commit();
            return true;
        }else{
            $this->db->rollback();
            return false;
        }
	}
    
    public function ReplaceUserLastmoney($DataArr)
	{
	    $table_name = Base_Widget::getDbTable($this->table_user_lastmoney);
		return $this->db->replace($table_name,$DataArr);
	}
	
	//道具购买
    public function ItemPurchase($StartDate,$EndDate,$ServerId,$oWherePartnerPermission,$ItemListText)
    {
        //查询列
        $select_fields = array(
        'ItemPurchaseCount'=>'count(*)',
        'ItemCount'=>'sum(ItemNum)',
        'UserCount'=>'count(distinct(UserId))',
        'TotalAppCoin'=> 'sum(AppCoin)',
        'PurchaseDate'=>"from_unixtime(PurchaseTime,'%Y-%m-%d')",
        );
    
        //初始化查询条件
        $whereStartDate = $StartDate?" PurchaseTime >= '".strtotime($StartDate)."' ":"";
        $whereEndDate = $EndDate?" PurchaseTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
        $whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
        $whereItemID = $ItemListText?" ItemID in ( ".$ItemListText." )":"";
             
        $group_fields = array('PurchaseDate');
        $groups = Base_common::getGroupBy($group_fields);
        
        $whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereItemID);
        
        //生成查询列
        $fields = Base_common::getSqlFields($select_fields);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
    
        //初始化结果数组
        $Date = $StartDate;
        $StatArr = array('ItemPurchase'=>array());         
        do
        {
            $StatArr['ItemPurchase'][$Date] = array('ItemPurchaseCount' => 0,'ItemCount' => 0,'UserCount'=> 0,'TotalAppCoin'=>0);
            $Date = date("Y-m-d",(strtotime($Date)+86400));
        }
        while(strtotime($Date) <= strtotime($EndDate));
    
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
    
        foreach($DateList as $k=>$v)
        {
            $table_name = Base_Widget::getDbTable($this->table)."_".$v;
            
            $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
            
        	$PurchaseArr = $this->db->getAll($sql,false);
        
            foreach($PurchaseArr as $key=>$val)
            {
              $StatArr['ItemPurchase'][$val['PurchaseDate']]['ItemPurchaseCount'] += $val['ItemPurchaseCount'];
              $StatArr['ItemPurchase'][$val['PurchaseDate']]['ItemCount'] += $val['ItemCount'];
              $StatArr['ItemPurchase'][$val['PurchaseDate']]['UserCount'] += $val['UserCount']; 
              $StatArr['ItemPurchase'][$val['PurchaseDate']]['TotalAppCoin'] += $val['TotalAppCoin'];               
            }
        }
        return $StatArr;
    }
	//道具购买
    public function getItemPurchaseDetail($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$ItemListText,$start,$pagesize)
    {
  		$PurchaseCount = $this->getItemPurchaseDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$ItemListText);
        //查询列
		if($PurchaseCount)
		{
			$select_fields = array('*');
	    
	        //初始化查询条件
	        $whereStartTime = $StartTime?" PurchaseTime >= '".strtotime($StartTime)."' ":"";
	        $whereEndTime = $EndTime?" PurchaseTime <= '".strtotime($EndTime)."' ":"";
	        $whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	        $whereItemID = $ItemListText?" ItemID in ( ".$ItemListText." )":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
	             	        
	        $whereCondition = array($whereStartTime,$whereEndTime,$whereUser,$whereServer,$oWherePartnerPermission,$whereItemID);
	        
	        //生成查询列
	        $fields = Base_common::getSqlFields($select_fields);
	        //生成条件列
	        $where = Base_common::getSqlWhere($whereCondition);
	        
			$order = " order by PurchaseTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
	    
		    if($UserId)
		    {
				$position = Base_Common::getUserDataPositionById($UserId);			
				$table_to_process = Base_Widget::getDbTable($this->table)."_user_".$position['db_fix'];    		
		    }
		    else
		    {
				$Date = date("Ym",strtotime($StartTime));			
				$table_to_process = Base_Widget::getDbTable($this->table)."_".$Date;     	
		    }
	    
		    $StatArr = array('PurchaseDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$PurchaseDetailArr = $this->db->getAll($sql,false);
			if(isset($PurchaseDetailArr))
		    {
				foreach ($PurchaseDetailArr as $key => $value) 
				{
					$StatArr['PurchaseDetail'][] = $value;
				}
		    }				
		}
	 	$StatArr['PurchaseCount'] = $PurchaseCount; 
		return $StatArr;
    }
    public function getItemPurchaseDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$ItemListText)
    {
        //查询列
		$select_fields = array('PurchaseCount'=>'count(*)');
    
        //初始化查询条件
        $whereStartTime = $StartTime?" PurchaseTime >= '".strtotime($StartTime)."' ":"";
        $whereEndTime = $EndTime?" PurchaseTime <= '".strtotime($EndTime)."' ":"";
        $whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
        $whereItemID = $ItemListText?" ItemID in ( ".$ItemListText." )":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";

             
        $group_fields = array('PurchaseDate');
        $groups = Base_common::getGroupBy($group_fields);
        
	    $whereCondition = array($whereStartTime,$whereEndTime,$whereUser,$whereServer,$oWherePartnerPermission,$whereItemID);
        
        //生成查询列
        $fields = Base_common::getSqlFields($select_fields);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
            
	    if($UserId)
	    {
			$position = Base_Common::getUserDataPositionById($UserId);			
			$table_to_process = Base_Widget::getDbTable($this->table)."_user_".$position['db_fix'];    		
	    }
	    else
	    {
			$Date = date("Ym",strtotime($StartTime));			
			$table_to_process = Base_Widget::getDbTable($this->table)."_".$Date;     	
	    }    
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$PurchaseCount = $this->db->getOne($sql,false);
		if($PurchaseCount)
    	{
			return $PurchaseCount;    
		}
		else
		{
			return 0; 	
		}
    }
	//NPC道具购买
    public function getCharacterMoney($StartDate,$EndDate,$ServerId,$oWherePartnerPermission)
    {
        //查询列
        $select_fields = array('MoneyType',
        'PurchaseCount'=>'count(*)',
        'UserCount'=>'count(distinct(UserId))',
        'TotalMoney'=> 'sum(Money)',
        'PurchaseDate'=>"from_unixtime(NpcPurchaseTime,'%Y-%m-%d')",
        );
    
        //初始化查询条件
        $whereStartDate = $StartDate?" NpcPurchaseTime >= '".strtotime($StartDate)."' ":"";
        $whereEndDate = $EndDate?" NpcPurchaseTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
        $whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
             
        $group_fields = array('PurchaseDate','MoneyType');
        $groups = Base_common::getGroupBy($group_fields);
        
        $whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereItemID);
        
        //生成查询列
        $fields = Base_common::getSqlFields($select_fields);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        
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
    
        foreach($DateList as $k=>$v)
        {
            $table_name = Base_Widget::getDbTable($this->table_npc_item_purchase)."_".$v;
            
            $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
        	$MoneyArr = $this->db->getAll($sql,false);
        
            foreach($MoneyArr as $key=>$val)
            {
              $StatArr['ItemPurchase'][$val['PurchaseDate']]['AppMoney'][$val['MoneyType']]['PurchaseCount'] += $val['PurchaseCount'];
              $StatArr['ItemPurchase'][$val['PurchaseDate']]['AppMoney'][$val['MoneyType']]['UserCount'] += $val['UserCount']; 
              $StatArr['ItemPurchase'][$val['PurchaseDate']]['AppMoney'][$val['MoneyType']]['TotalMoney'] += $val['TotalMoney'];               
            }
        }
        return $StatArr;
    }
	//NPC道具购买
    public function getCharacterAppCoin($StartDate,$EndDate,$ServerId,$oWherePartnerPermission)
    {
        //查询列
        $select_fields = array(
        'PurchaseCount'=>'count(*)',
        'UserCount'=>'count(distinct(UserId))',
        'TotalMoney'=> 'sum(AppCoin)',
        'PurchaseDate'=>"from_unixtime(PurchaseTime,'%Y-%m-%d')",
        );
    
        //初始化查询条件
        $whereStartDate = $StartDate?" PurchaseTime >= '".strtotime($StartDate)."' ":"";
        $whereEndDate = $EndDate?" PurchaseTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
        $whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
             
        $group_fields = array('PurchaseDate');
        $groups = Base_common::getGroupBy($group_fields);
        
        $whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereItemID);
        
        //生成查询列
        $fields = Base_common::getSqlFields($select_fields);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        
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
    
        foreach($DateList as $k=>$v)
        {
            $table_name = Base_Widget::getDbTable($this->table)."_".$v;
            
            $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
        	$AppCoinArr = $this->db->getAll($sql,false);
        
            foreach($AppCoinArr as $key=>$val)
            {
              $StatArr['ItemPurchase'][$val['PurchaseDate']]['PurchaseCount'] += $val['PurchaseCount'];
              $StatArr['ItemPurchase'][$val['PurchaseDate']]['UserCount'] += $val['UserCount']; 
              $StatArr['ItemPurchase'][$val['PurchaseDate']]['TotalMoney'] += $val['TotalMoney'];               
            }
        }
        return $StatArr;
    }
	//Npc道具购买
    public function getNpcItemPurchaseDetail($StartTime,$EndTime,$UserId,$ServerId,$MoneyType,$oWherePartnerPermission,$ItemListText,$start,$pagesize)
    {
  		$PurchaseCount = $this->getNpcItemPurchaseDetailCount($StartTime,$EndTime,$UserId,$ServerId,$MoneyType,$oWherePartnerPermission,$ItemListText);
        //查询列
		if($PurchaseCount)
		{
			$select_fields = array('*');
	    
	        //初始化查询条件
	        $whereStartTime = $StartTime?" NpcPurchaseTime >= '".strtotime($StartTime)."' ":"";
	        $whereEndTime = $EndTime?" NpcPurchaseTime <= '".strtotime($EndTime)."' ":"";
	        $whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	        $whereItemID = $ItemListText?" ItemID in ( ".$ItemListText." )":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereMoney = $MoneyType?" MoneyType = ".$MoneyType." ":"";		
	             	        
	        $whereCondition = array($whereStartTime,$whereEndTime,$whereUser,$whereMoney,$whereServer,$oWherePartnerPermission,$whereItemID);
	        
	        //生成查询列
	        $fields = Base_common::getSqlFields($select_fields);
	        //生成条件列
	        $where = Base_common::getSqlWhere($whereCondition);
	        
			$order = " order by NpcPurchaseTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
	    
		    if($UserId)
		    {
				$position = Base_Common::getUserDataPositionById($UserId);			
				$table_to_process = Base_Widget::getDbTable($this->table_npc_item_purchase)."_user_".$position['db_fix'];    		
		    }
		    else
		    {
				$Date = date("Ym",strtotime($StartTime));			
				$table_to_process = Base_Widget::getDbTable($this->table_npc_item_purchase)."_".$Date;     	
		    }
	    
		    $StatArr = array('PurchaseDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$PurchaseDetailArr = $this->db->getAll($sql,false);
			if(isset($PurchaseDetailArr))
		    {
				foreach ($PurchaseDetailArr as $key => $value) 
				{
					$StatArr['PurchaseDetail'][] = $value;
				}
		    }				
		}
	 	$StatArr['PurchaseCount'] = $PurchaseCount; 
		return $StatArr;
    }
    public function getNpcItemPurchaseDetailCount($StartTime,$EndTime,$UserId,$ServerId,$MoneyType,$oWherePartnerPermission,$ItemListText)
    {
        //查询列
		$select_fields = array('PurchaseCount'=>'count(*)');
    
        //初始化查询条件
        $whereStartTime = $StartTime?" NpcPurchaseTime >= '".strtotime($StartTime)."' ":"";
        $whereEndTime = $EndTime?" NpcPurchaseTime <= '".strtotime($EndTime)."' ":"";
        $whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
        $whereItemID = $ItemListText?" ItemID in ( ".$ItemListText." )":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereMoney = $MoneyType?" MoneyType = ".$MoneyType." ":"";		
             
        $groups = Base_common::getGroupBy($group_fields);
        
	    $whereCondition = array($whereStartTime,$whereEndTime,$whereUser,$whereMoney,$whereServer,$oWherePartnerPermission,$whereItemID);
        
        //生成查询列
        $fields = Base_common::getSqlFields($select_fields);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
            
	    if($UserId)
	    {
			$position = Base_Common::getUserDataPositionById($UserId);			
			$table_to_process = Base_Widget::getDbTable($this->table_npc_item_purchase)."_user_".$position['db_fix'];    		
	    }
	    else
	    {
			$Date = date("Ym",strtotime($StartTime));			
			$table_to_process = Base_Widget::getDbTable($this->table_npc_item_purchase)."_".$Date;     	
	    }    
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$PurchaseCount = $this->db->getOne($sql,false);
		if($PurchaseCount)
    	{
			return $PurchaseCount;    
		}
		else
		{
			return 0; 	
		}
    }
}

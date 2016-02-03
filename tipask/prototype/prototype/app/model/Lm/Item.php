<?php
/**
 * 游戏道具相关mod层
 * @author 张骥 <3445075721@qq.com>
 */


class Lm_Item extends Base_Widget
{
	//声明所用到的表
    protected $table = 'lm_item_pickup_log';
    protected $table_list = array('pickitem'=>'lm_item_pickup_log','pickitem_user'=>'lm_item_pickup_log_user'); //道具拾取日志
    protected $table_item_purchase_log = 'lm_item_Purchase_log';
    protected $table_item_seal = 'game_item_seal';
    protected $table_item_pickup_date = 'lm_item_pickup_date';
	
	public function createTable($Date,$table_list)
	{
		$table_to_check = Base_Widget::getDbTable($table_list);
		$table_to_process = $table_to_check."_".$Date;
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
			$sql = str_replace('`' . $table_list . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
    
    public function createTableById($table_list,$table_check)
	{
		$table_to_process = $table_check;
		$exist = $this->db->checkTableExist($table_to_process);
        
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE " . $table_list;
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
    
    public function compressItemByDay($StartTime){
         $table_name = $this->createTable(date("Ymd",$StartTime),$this->table_item_pickup_date);
         $value_table_name = $this->table."_".date("Ymd",$StartTime);
         
         $sql = "replace into $table_name(`UserId`,`ItemPickUpDate`,`Hour`,`AppId`,`PartnerId`,`ServerId`,`ItemId`,`ItemNum`)
                 SELECT `UserId`,from_unixtime(`ItemPickUpTime`,'%Y-%m-%d'),from_unixtime(`ItemPickUpTime`,'%H'),`AppId`,`PartnerId`,`ServerId`,`ItemId`,sum(`ItemNum`) as `ItemNum` 
                 FROM $value_table_name 
                 group by `UserId`,`AppId`,`PartnerId`,`ServerId`,`ItemId`,from_unixtime(`ItemPickUpTime`,'%H')";
         
         return $this->db->query($sql);
    }
	
    //道具拾取
	public function InsertItemPickUpLog($DataArr)
	{
	    $this->db->begin();
		$Date = date("Ymd",$DataArr['ItemPickUpTime']);
		$table_date = $this->createTable($Date,$this->table_list['pickitem']);
		$insertDate = $this->db->insert($table_date,$DataArr);
        
        $Date = date("Ymd",$DataArr['ItemPickUpTime']);
        $position = Base_Common::getUserDataPositionById($DataArr['UserId']);
        
        $table_list = Base_Widget::getDbTable($this->table);        
        $table_check = Base_Common::getUserTable($this->table_list['pickitem_user'],$position);
        
        $table_date = $this->createTableById($table_list,$table_check);
        
		$insertUser = $this->db->insert($table_date,$DataArr);
        
        if($insertDate && $insertUser){
            $this->db->commit();
            return true;
        }else{
            $this->db->rollback();
            return false;
        }
	}
    
    //获取用户道具拾取
    public function getItemSealList($AppId,$ItemId,$SealTime,$days,$dateType){
        $nowTime = time();
                
        //初始化查询条件
	    $whereAppId = $AppId?" AppId = '".$AppId."' ":"";
        $whereItemId = $ItemId?" ItemId in (".$ItemId.") ":"";
        $whereStartTime = $SealTime?" ItemPickUpDate = '".date("Y-m-d",$SealTime)."' ":"";
        $whereHour = $dateType=="hh"?" Hour = '".date("H",$SealTime)."' ":"";
        
        $whereCondition = array($whereAppId,$whereItemId,$whereStartTime,$whereHour);
        //生成条件列
	    $where = Base_common::getSqlWhere($whereCondition);
        
        $return = array();
        
        for($i=0;$i<$days;$i++){
            $table_to_process = Base_Widget::getDbTable($this->table_item_pickup_date)."_".date("Ymd",$SealTime);
            $sql = "SELECT `UserId`,`ItemId`,sum(`ItemNum`) as `ItemNum` FROM $table_to_process as log where 1 ".$where." GROUP BY `UserId` , `ItemId`";

            $ItemList = $this->db->getAll($sql);
            foreach($ItemList as $k=>$v){
                if(isset($return[$v['UserId']][$v['ItemId']])){
                    $return[$v['UserId']][$v['ItemId']] += $v['ItemNum'];                    
                }else{
                    $return[$v['UserId']][$v['ItemId']] = $v['ItemNum'];                                    
                }
            }
        }
                
        return $return;        
    }
    
	//道具购买
    public function getItemPickUpDetail($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$ItemListText,$start,$pagesize)
    {
  		$PickUpCount = $this->getItemPickUpCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$ItemListText);
        //查询列
		if($PickUpCount)
		{
			$select_fields = array('*');
	    
	        //初始化查询条件
		    $whereStartTime = $StartTime?" ItemPickUpTime >= '".strtotime($StartTime)."' ":"";
		    $whereEndTime = $EndTime?" ItemPickUpTime <= '".strtotime($EndTime)."' ":"";
	        $whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	        $whereItemID = $ItemListText?" ItemID in ( ".$ItemListText." )":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
	             	        
	        $whereCondition = array($whereStartTime,$whereEndTime,$whereUser,$whereServer,$oWherePartnerPermission,$whereItemID);
	        
	        //生成查询列
	        $fields = Base_common::getSqlFields($select_fields);
	        //生成条件列
	        $where = Base_common::getSqlWhere($whereCondition);
	        
			$order = " order by ItemPickUpTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
	    
		    if($UserId)
		    {
				$position = Base_Common::getUserDataPositionById($UserId);	
				
				$table_to_process = Base_Common::getUserTable($this->table_list['pickitem_user'],$position);		
		    }
		    else
		    {
				$Date = date("Ymd",strtotime($StartTime));			
				$table_to_process = Base_Widget::getDbTable($this->table_list['pickitem'])."_".$Date;     	
		    } 
	    
		    $StatArr = array('PickUpDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$PickUpDetailArr = $this->db->getAll($sql,false);
			if(isset($PickUpDetailArr))
		    {
				foreach ($PickUpDetailArr as $key => $value) 
				{
					$StatArr['PickUpDetail'][] = $value;
				}
		    }				
		}
	 	$StatArr['PickUpCount'] = $PickUpCount; 
		return $StatArr;
    }
    public function getItemPickUpCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$ItemListText)
    {
        //查询列
		$select_fields = array('PickUpCount'=>'count(*)');
    
        //初始化查询条件
        $whereStartTime = $StartTime?" ItemPickUpTime >= '".strtotime($StartTime)."' ":"";
        $whereEndTime = $EndTime?" ItemPickUpTime <= '".strtotime($EndTime)."' ":"";
        $whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
        $whereItemID = $ItemListText?" ItemID in ( ".$ItemListText." )":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
        
	    $whereCondition = array($whereStartTime,$whereEndTime,$whereUser,$whereServer,$oWherePartnerPermission,$whereItemID);
        
        //生成查询列
        $fields = Base_common::getSqlFields($select_fields);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
            
	    if($UserId)
	    {
			$position = Base_Common::getUserDataPositionById($UserId);	
			
			$table_to_process = Base_Common::getUserTable($this->table_list['pickitem_user'],$position);		
	    }
	    else
	    {
			$Date = date("Ymd",strtotime($StartTime));			
			$table_to_process = Base_Widget::getDbTable($this->table_list['pickitem'])."_".$Date;     	
	    }    
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$PickUpCount = $this->db->getOne($sql,false);
		if($PickUpCount)
    	{
			return $PickUpCount;    
		}
		else
		{
			return 0; 	
		}
    }
}

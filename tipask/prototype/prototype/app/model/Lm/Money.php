<?php
/**
 * 游戏金钱相关mod层
 * @author 张骥 <3445075721@qq.com>
 */


class Lm_Money extends Base_Widget
{
	//声明所用到的表
    protected $table = 'lm_moneylog';
    protected $table_moneylog_compress = 'lm_moneylog_compress';
	
	public function createMoneyLogTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table);
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
    
    public function createMoneyLogCompressTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_moneylog_compress);
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
			$sql = str_replace('`' . $this->table_moneylog_compress . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
            
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
    
    public function insertMoneyLog($DataArr)
    {
        $table_name = $this->createMoneyLogTable(date("Ymd",$DataArr['MoneyLogTime']));
        
        return $this->db->insert($table_name,$DataArr);
    }
    
    public function compressMoneyByDay($StartTime){
         $table_name = $this->createMoneyLogCompressTable(date("Ymd",$StartTime));
         $value_table_name = $this->table."_".date("Ymd",$StartTime);
         
         $sql = "replace into $table_name(`UserId`,`AppId`,`PartnerId`,`ServerId`,`MoneyLogDate`,`Hour`,`MoneyType`,`MoneyChanged`,`Reason`)
                SELECT `UserId` , `AppId` , `PartnerId` , `ServerId` ,from_unixtime(`MoneyLogTime`,'%Y-%m-%d') , from_unixtime(`MoneyLogTime`,'%H') , `MoneyType` , sum( `MoneyChanged` ) AS `MoneyChanged` , `Reason`
                FROM $value_table_name
                WHERE `MoneyChanged` > 0
                GROUP BY `UserId` , `AppId` , `PartnerId` , `ServerId` , `MoneyType` , `Reason`,from_unixtime(`MoneyLogTime`,'%H') ";
         
         return $this->db->query($sql);
    }
    
    //获取用户金币拾取
    public function getItemSealList($AppId,$ItemId,$SealTime,$days,$dateType){
        $nowTime = time();
                
        //初始化查询条件
	    $whereAppId = $AppId?" AppId = '".$AppId."' ":"";
        $whereItemId = $ItemId?" MoneyType in (".$ItemId.") ":"";
        $whereStartTime = $SealTime?" MoneyLogDate = '".date("Y-m-d",$SealTime)."' ":"";
        $whereHour = $dateType=="hh"?" Hour = '".date("H",$SealTime)."' ":"";        
        $whereReason = " `Reason` != 12 ";
        
        $whereCondition = array($whereAppId,$whereItemId,$whereStartTime,$whereHour,$whereReason);
        //生成条件列
	    $where = Base_common::getSqlWhere($whereCondition);
        
        $return = array();
        
        for($i=0;$i<$days;$i++){
            $table_to_process = Base_Widget::getDbTable($this->table_moneylog_compress)."_".date("Ymd",$SealTime);
            $sql = "SELECT `UserId`,`MoneyType` as ItemId,sum(`MoneyChanged`) as ItemNum FROM $table_to_process as log where 1 ".$where." GROUP BY `UserId` , `ItemId`";  
                      
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
}

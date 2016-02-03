<?php
/**
 * 用户相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Lm_Statlog extends Base_Widget
{
	//声明所用到的表
	protected $table = 'lm_statlog_online';


	public function createStatLogTable($Date)
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

	public function InsertStatLog($DataArr)
	{
		$Date = date("Ym",$DataArr['Time']);
		$table_date = $this->createStatLogTable($Date);		
		return $this->db->insert($table_date,$DataArr);
	}
 	public function getOnlineDay($lag,$Date,$ServerId,$oWherePartnerPermission)
	{
		$StartTime = strtotime($Date);
		$EndTime = $StartTime+86400;
		$Time = $StartTime;
		$OnlineDay = array();
		do
		{
			$OnlineDay[$Time] = $this->getOnline($Time-$lag,$Time+$lag,$ServerId,$oWherePartnerPermission);
			$Time+=$lag;
		}
		while($EndTime>$Time);
		return $OnlineDay;
	}
 	public function getOnline($StartTime,$EndTime,$ServerId,$oWherePartnerPermission)
	{
		if(($StartTime+$EndTime)/2 > time())
		{
			$Return['AvgOnline'] = 	0;
			$Return['LowOnline'] = 	0;
			$Return['HighOnline'] = 0;			
		}
		else
		{
			//查询列
			$select_fields = array('ServerId',
			'AvgOnline'=>'avg(CurOnline)',
			'LowOnline'=>'min(CurOnline)',
			'HighOnline'=>'max(HighestOnline)');
	
			//初始化查询条件
			$whereStartTime = " Time  >= '".$StartTime."' ";
			$whereEndTime = " Time  <= '".$EndTime."' ";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	
			$whereCondition = array($whereStartTime,$whereEndTime,$whereServer,$oWherePartnerPermission);
	
			$group_fields = array('ServerId');
			$groups = Base_common::getGroupBy($group_fields);
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成分类汇总列
			$where = Base_common::getSqlWhere($whereCondition);
			$Date = date("Ym",($StartTime+$EndTime)/2);
			$table_to_process = Base_Widget::getDbTable($this->table)."_".$Date;
			$Return = array('UserCount'=>0,'OnlineCount'=>0);
	    	$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$groups;
			$Online = $this->db->getAll($sql);
			foreach($Online as $key=>$val)
			{
				$Return['AvgOnline'] += $val['AvgOnline'];
				$Return['LowOnline'] += $val['LowOnline'];
				$Return['HighOnline'] += $val['HighOnline'];
			}	 	
		}
		return $Return;   	
  }
}

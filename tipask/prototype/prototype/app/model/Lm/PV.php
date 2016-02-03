<?php
/**
 * PV
 * @author Chen<cxd032404@hotmail.com>
 * $Id: PV.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Lm_PV extends Base_Widget
{
	/**
	 * Product表名
	 * @var string
	 */
	protected $table = 'pv_log';

	public function createPvLogDate($Date)
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
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertPvLog(array $bind)
	{
		$Date = date("Ymd",$bind['Time']);		
		$table_to_process = $this->createPvLogDate($Date);
		return $this->db->insert($table_to_process, $bind);
	}
}


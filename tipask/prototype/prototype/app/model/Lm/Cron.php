<?php
/**
 * 用户相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Lm_Cron extends Base_Widget
{
	//声明所用到的表
	protected $table = 'readlog_last_update';
	protected $table_error = 'error_log';

	public function InsertErrorLog($DataArr)
	{
      	$table_to_process = Base_Widget::getDbTable($this->table_error);
		return $this->db->replace($table_to_process,$DataArr);
	}
	public function UpdateLastUpdate($DataArr)
	{
      	$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->replace($table_to_process,$DataArr);
	}
	public function GetLastUpdate($ServerId,$FileType)
	{
      	$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "select * from $table_to_process where ServerId = ? and FileType = ?";
		return $this->db->getRow($sql,array($ServerId,$FileType),true);
	}
}

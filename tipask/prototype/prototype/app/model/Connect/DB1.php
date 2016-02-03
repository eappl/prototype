<?php
/**
 * Socket服务端函数
 * @author 刘增秀 <cxd032404@hotmail.com>
 */


class Connect_DB1 extends Base_Widget
{
	//声明所用到的表

	protected $table = 'check_1';
	/*
	*判断主从库的链接
	*/
	public function getLinkM1()
	{
		echo "<br/>getLinkM1<br/>";	
		$table_to_insert = Base_Widget::getDbTable($this->table);
		$DataArr = array('check_time'=>time());		
		$CheckId = $this->db->insert($table_to_insert,$DataArr);
		
		if($CheckId)
		{
			$sql = "select * from $table_to_insert where check_id = ?";
			$return = $this->db->getRow($sql,$CheckId,false);
			if($return['check_time'])
			{
				return true;					
			}
			else
			{
				return false; 	
			}
		}
		else
		{
			return false;	
		}
	}
}

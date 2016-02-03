<?php
/**
 * 用户mod层
 * $Id: BroadCastController.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Kubao_User extends Base_Widget
{
	//声明所用到的表
	protected $table = 'ask_gag';

	//根据用户名获取禁言记录
	public function getGag($UserName,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`login_name` = ?', $UserName);		
	}
	//根据cookie发帖记录验证用户
	public function authorUserByCookie($Cookie,$CookieList,$Id)
	{
		//拆解cookie
		$ask_type = unserialize(stripslashes($Cookie));
		$IdArrTotal = array();
		$IdArr = array();
		$t = explode(",",$CookieList);
		foreach($t as $k => $v)
		{
			$IdArrTotal[] = $ask_type[$v];			
		}
		foreach($IdArrTotal as $key => $value)
		{
			if(trim($value)=="")
			{
				unset($IdArrTotal[$key]);
			}
			else
			{
				$tt = explode(",",$value);
				foreach($tt as $kk => $vv)
				{
					$IdArr[] = $vv;
				}
			}

		}
		if(!in_array($Id,$IdArr))
		{
			return false;			
		}
		else
		{
			return true;
		}			
	}
	//根据用户名验证用户
	public function authorUserByName($UserName,$DetailName)
	{
		if($UserName != $this->config->UnLoggedUserName && strtolower($UserName)==strtolower($DetailName))
		{
			return true;
		}
		else
		{
			return false;
		}		
	}
}

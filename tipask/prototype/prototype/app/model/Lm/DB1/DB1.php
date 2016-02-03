<?php
/**
 * 用户相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Lm_DB1_DB1 extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_info_base';
	protected $table_active = 'user_info_active';
  protected $table_communication = 'user_info_communication';
  protected $table_mail = 'user_mail';
  protected $table_reg_log = 'user_reg_log';
  protected $table_password_reset_log = 'password_reset_log';
	protected $table_answer = 'user_security_answer';
	protected $table_del = 'user_to_del';

	protected $table_order = "lm_order_user";
	protected $table_pay = "lm_pay_user";
	protected $table_exchange = "lm_exchange";

    public function test($word)
    {
        echo $word;    
    }
	/*将指定用户注册ip，添加到 27 lm_user.user_to_del表中
	 *
	 */
	public function InsertToDelete($DataArr)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_del);
		return $this->db->insert($table_to_process,$DataArr);		
	}
	/*
	 *根据用户id和name获取用户的order,pay,exchange值放入 27 lm_user.user_to_del表中
	 *@author selena 2013/3/15
	 */
	public function UpdateDB1ToDelete($DataArr)
	{
		
		/*根据用户id,查询37 lm_order.lm_order_user中用户的订单记录数量
		 *查询37 lm_order.lm_pay_user中用户的付款记录数量
		 *查询37 lm_order.lm_exchange_user中用户的交换记录数量，更新 37.lm_user.user_to_del
		 */
		
		
		$UserId = $DataArr['UserId'];
		
		$table_to_process = Base_Widget::getDbTable($this->table_del);
		//Order表
		$OrderCount = $this->getUserOrder($DataArr);
		if($OrderCount!=0)
		{
			$OrderArr = array("Order"=>$OrderCount);		
			$this->db->update($table_to_process, $OrderArr, '`UserId` = ?', $UserId);
		}
		
		//Pay表
		$PayCount = $this->getUserPay($DataArr);
		if($PayCount!=0)
		{
			$PayArr = array("Pay"=>$PayCount);		
			$this->db->update($table_to_process, $PayArr, '`UserId` = ?', $UserId);
		}
		//Exchange表
		$ExchangeCount = $this->getUserExchange($DataArr);
		if($ExchangeCount!=0)
		{
			$ExchangeArr = array("Exchange"=>$ExchangeCount);		
			$this->db->update($table_to_process, $ExchangeArr, '`UserId` = ?', $UserId);
		}
		
	}
	public function getUserOrder($DataArr)
	{
		$UserId = $DataArr['UserId'];
		
		$FirstChar = $this->getMd5First($DataArr['UserName']);
		
		$table_to_process = Base_Widget::getDbTable($this->table_order)."_".$FirstChar;
		
		$sql = "SELECT count(OrderId) as count from $table_to_process where PayUserId = $UserId or AcceptUserId = $UserId";	
		
		$return = $this->db->getOne($sql);
		if($return)
		{
			return $return;
		}else{
			return 0;
		}
		
	}
	public function getUserPay($DataArr)
	{
		$UserId = $DataArr['UserId'];
		
		$FirstChar = $this->getMd5First($DataArr['UserName']);
		
		$table_to_process = Base_Widget::getDbTable($this->table_pay)."_".$FirstChar;
		
		$sql = "SELECT count(OrderId) as count from $table_to_process where PayUserId = $UserId or AcceptUserId = $UserId";	
		
		$return = $this->db->getOne($sql);
		if($return)
		{
			return $return;
		}else{
			return 0;
		}
		
	}
	public function getUserExchange($DataArr)
	{
		$UserId = $DataArr['UserId'];
		
		$FirstChar = $this->getMd5First($DataArr['UserName']);
		
		$table_to_process = Base_Widget::getDbTable($this->table_exchange)."_user_".$FirstChar;
		
		$sql = "SELECT count(OrderId) as count from $table_to_process where UserId = $UserId";	
		
		$return = $this->db->getOne($sql);

		if($return)
		{
			return $return;
		}else{
			return 0;
		}
		
	}
	public function getMd5First($str)
	{		
		$new_str = md5($str);		
		return substr($new_str,0,1);
	}
	public function GetUserByIP($UserRegIP)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_reg_log);
		$table_to_process .= "_201301";
		//初始化查询条件
		$whereStartDate = " UserRegTime >= ".strtotime('2013-01-05')." ";
		$whereEndDate = " UserRegTime <= ".(strtotime('2013-01-07')+86400-1)." ";
		$whereIP = " UserRegIP = '".$UserRegIP."' ";

		$whereCondition = array($whereStartDate,$whereEndDate,$whereIP);
		$where = Base_common::getSqlWhere($whereCondition);

		$sql = "select UserId,UserName from $table_to_process where 1 ".$where;
		$return = $this->db->getAll($sql,false);
		$UserList = array();
		foreach($return as $key => $value)
		{
			$UserList[$value['UserId']] = $value;
		}
		return $UserList;
	}
    	/*获取所有灌水用户(user_to_del表中Pay,order,exchange字段都为空的记录)的用户名和用户id
	 *
	 *@author:selena 2013/3/15
	 */
	/* 查询出所有灌水用户 (37 lm_user.user_to_del)根据用户名确定一下的库表
		 * 1.根据用户id删除 27 lm_active_code.active_code 同时删除 37 lm_user.user_info_active中的记录
		 * 2.根据用户id删除 37 lm_user.user_info_base中的记录 
		 * 3.根据 37 lm_user.user_info_communication中email字段的值的md5前2位确定 37 lm_user.user_mail表
		 *  根据用户id删除user_mail中的用户email记录
		 * 4.根据用户id删除 37 lm_user.user_info_communication中的记录  
		 *  @author selena 2013/3/15 整理
		 */
	public function getAllDelUser()
	{
		
		$table_to_process = Base_Widget::getDbTable($this->table_del);
		$sql = "SELECT UserId,UserName FROM $table_to_process WHERE `Pay`=0 AND `Order`=0 AND `Exchange`=0";
		
		$DelUserList = $this->db->getAll($sql,false);
		return $DelUserList;
		
	}
	/* 批量删除用户激活码，将 37 lm_user.user_info_active
	*$UserArr 二维数组 array("UserId"=>"","UserName"=>"")
	*/
	public function DelActiveCodeByUserID($UserArr)
	{
		$table = "user_info_active";
		$Count = 0;
		$table_to_update = Base_Widget::getDbTable($this->table_del);		
		foreach($UserArr as $k=>$v)
		{
			$position = Base_Common::getUserDataPositionByName($v["UserName"]);			
			$table_to_process = Base_Common::getUserTable($table,$position);
			$sql = "SELECT COUNT(*) FROM $table_to_process WHERE UserId = ".$v["UserId"];
			$ActiveInfoCount = $this->db->getOne($sql);
			if($this->db->delete($table_to_process, '`UserId` = ?', $v["UserId"]))
			{
				$ActiveInfoArr = array("ActiveInfo"=>$ActiveInfoCount);				
				if($this->db->update($table_to_update, $ActiveInfoArr, '`UserId` = ?', $v['UserId']))
				{
					$Count++;					
				}				
			}
		}
		return $Count;
	}
	/*
	 *批量删除灌水用户的基本信息 并更新基本信息列，根据用户id
	 *根据用户id删除 37 lm_user.user_info_base中的记录 
	*$UserArr 二维数组 array("UserId"=>"","UserName"=>"")
	 */
	public function DelUserBaseInfoByUserID($UserArr)
	{
		$user = new Lm_User();
		$table = "user_info_base";
		$Count = 0;
		
		$table_to_update = Base_Widget::getDbTable($this->table_del);		
		$BaseInfoArr = array("BaseInfo"=>1);			
		foreach($UserArr as $k=>$v)
		{
			$position = Base_Common::getUserDataPositionByName($v["UserName"]);			
			$table_to_process = Base_Common::getUserTable($table,$position);
			$Id = substr($v['UserId'],0,-3);
			if($this->db->delete($table_to_process, "`UserId` = ?", $Id))
			{				
				if($this->db->update($table_to_update, $BaseInfoArr, '`UserId` = ?', $v['UserId']))
				{
					$Count++;					
				}
			}
		}
		return $Count;
		
	}
	/*
	 *根据 37 lm_user.user_info_communication中email字段的值的md5前2位确定 37 lm_user.user_mail表
	 *  根据用户id删除user_mail中的用户email记录
	 * 4.根据用户id删除 37 lm_user.user_info_communication中的记录
	 * 更新lm_user.user_to_del Communication列，根据用户id
	 */
	public function DelUserMailAndCommunication($UserArr)
	{
		$table_communication = "user_info_communication";
		$table_mail = "user_mail";
		$delCommunicationCount = $delMailCount = 0;
		$UpComCount = $UpMailCount = 0;
		
		$table_to_update = Base_Widget::getDbTable($this->table_del);		
		$CommunicationArr = array("Communication"=>1);
		$MailArr = array("Mail"=>1);
		foreach($UserArr as $k=>$v)
		{
			$position = Base_Common::getUserDataPositionByName($v["UserName"]);
			$table_to_communication = Base_Common::getUserTable($table_communication,$position);
			$sql = "SELECT UserMail from $table_to_communication WHERE `UserId`=".$v["UserId"];
			$Usermail = $this->db->getOne($sql);
			if($Usermail)//只有用户Mail存在时才会删除Mail信息
			{
				//user_mail表是通过mail来定位的
				$position = Base_Common::getUserDataPositionByName($Usermail);
				$table_to_mail = Base_Common::getUserTable($table_mail,$position);
				if($this->db->delete($table_to_mail, '`UserId` = ?', $v["UserId"]))
				{
					if($this->db->update($table_to_update, $MailArr, '`UserId` = ?', $v["UserId"]))
					{
						$UpMailCount++;					
					}
					$delMailCount++;
				}
				
			}
			if($this->db->delete($table_to_communication, '`UserId` = ?', $v["UserId"]))
			{
				if($this->db->update($table_to_update, $CommunicationArr, '`UserId` = ?', $v["UserId"]))
				{
					$UpComCount++;					
				}
				$delCommunicationCount++;				
			}
		}
		return array("DelMailCount"=>$delMailCount,"DelCommunicationCount"=>$delCommunicationCount,
			     "UpMailCount"=>$UpMailCount,"UpComCount"=>$UpComCount);
		
	}
	/*
	 *向lm_user.user_to_del更新激活码列，根据用户id
	 *$user array("UserId"=>"","UserName"=>"")  $Activecount激活码的数量
	 */
	public function UpdateToDelActive($user,$Activecount)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_del);		
		$ActiveArr = array("ActiveCode"=>$Activecount);		
		return $this->db->update($table_to_process, $ActiveArr, '`UserId` = ?', $user['UserId']);				
	}
	/*
	 *更新lm_user.user_to_del中的LoginLog字段
	 */
	public function UpdateToDelLoginLog($user,$LoginCount)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_del);		
		$LoginArr = array("LoginLog"=>$LoginCount);		
		return $this->db->update($table_to_process, $LoginArr, '`UserId` = ?', $user['UserId']);	
	}
	/*
	 *更新lm_user.user_to_del中的OnlineLog字段
	 */
	public function UpdateToDelOnlineLog($user,$OnlineCount)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_del);		
		$OnlineArr = array("OnlineLog"=>$OnlineCount);		
		return $this->db->update($table_to_process, $OnlineArr, '`UserId` = ?', $user['UserId']);	
	}
}

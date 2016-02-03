<?php
/**
 * 用户登陆相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Lm_Login extends Base_Widget
{
	//声明所用到的表
	protected $table = 'login_log';
	protected $table_date = 'login_log_date';
	protected $table_user = 'login_log_user';
	protected $table_first = 'first_login';
	protected $table_last = 'last_logout';
	protected $table_online = 'online_log';

	public function UserLogin($User,$UserInfo)
	{
		$oUser = new Lm_User();		
		$AppList = (@include(__APP_ROOT_DIR__."/etc/App.php"));
		$PartnerAppList = (@include(__APP_ROOT_DIR__."/etc/PartnerApp.php"));
		$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
		
		if($UserInfo['UserId'])
		{
			if($UserInfo['UserPassWord']!="0")
			{
				if(($User['UserPassWord'])==$UserInfo['UserPassWord'])
				{
		 			unset($User['UserPassWord'],$User['ReturnType']);
					//判断用户所选服务器大区是否存在
					
					$ServerInfo = $ServerList[$User['ServerId']];
					if($ServerInfo['ServerId'])
					{
			 			$Comment = json_decode($ServerInfo['Comment'],true);
			 			if(isset($Comment['IpListBlack'][$User['UserLoginIP']]))
			 			{
			 				$result = array('return'=>0,'comment'=>"您的IP已经被限制登录");
			 			}
			 			else 
			 			{
				 			//判断当前时间是否在开服之前
				 			if((time() >= intval($ServerInfo['LoginStart']))||(isset($Comment['IpListWhite'][$User['UserLoginIP']])))
				 			{
								//判断当前时间是否处于停机维护
								if(((time()>$ServerInfo['NextEnd'])&&(time()<$ServerInfo['NextStart']))&&(!isset($Comment['IpListWhite'][$User['UserLoginIP']])))
								{
						 			$result = array('return'=>0,'comment'=>"您所选的服务器处于停机维护中");
								}
								else
								{
						 			//判断服务器信息附带的游戏－运营商信息是否合法
									$bind = array($ServerInfo['PartnerId'],$ServerInfo['AppId']);	
																		
									$PartnerInfo = $PartnerAppList[$ServerInfo['AppId']][$ServerInfo['PartnerId']];
						 			if($PartnerInfo['AppId']&&$PartnerInfo['PartnerId'])
						 			{
							 			$is_freeze = $oUser->getCharacterFreeze($UserInfo['UserId'],$ServerInfo['ServerId']);
							 			if(!$is_freeze['FreezeCount'])
							 			{
								 			$Active = 0;					 			
								 			if($PartnerInfo['IsActive']==1)
								 			{
									 			$UserActive = $oUser->getUserActive($UserInfo['UserId'],$ServerInfo['AppId'],$ServerInfo['PartnerId']);
									 			$Active = count($UserActive)?1:0;
								 			}
								 			else 
								 			{
								 			 	$Active = 1;
								 			}
								 			//检查用户是否激活
								 			if($Active)
								 			{
									 			$AppInfo = $AppList[$ServerInfo['AppId']];
									 			//检查游戏配置是否存在
									 			if($AppInfo['AppId'])
									 			{
										 			$Comment = json_decode($AppInfo['comment'],true);
										 			//检查是否由平台生成登陆ID
										 			if($Comment['create_loginid'])
										 			{
											 			$FirstLogin = $this->getFirstLogin($UserInfo['UserId'],$ServerInfo['AppId'],$ServerInfo['PartnerId'],$ServerInfo['ServerId']);
											 			$User['AppId'] = $ServerInfo['AppId'];
											 			$User['PartnerId'] = $ServerInfo['PartnerId'];
														$User['UserSourceId'] = $UserInfo['UserSourceId'];
														$User['UserSourceDetail'] = $UserInfo['UserSourceDetail'];
														$User['UserSourceActionId'] = $UserInfo['UserSourceActionId'];
														$User['UserSourceProjectId'] = $UserInfo['UserSourceProjectId'];
														$User['UserRegTime'] = $UserInfo['UserRegTime'];
														$User['FirstLoginTime'] = $FirstLogin?$FirstLogin:$User['LoginTime'];
											
											 			$AddLog = $this->InsertLoginLog($User,$UserInfo['UserName']);
											 			//获取用户生日信息
											 			$UserBirthday = $oUser->GetUserCommunication($User['UserId'],"UserBirthDay");
											 			//根据用户生日信息判断是否为成年（1成年，0未成年，2为空）
											 			$adult = base_common::checkAdult($UserBirthday['UserBirthDay']);
											 			//返回用户ID,不包含（23000）
											 			if($AddLog)
											 			{
												 			$result = array('return'=>1,'LoginId' => $AddLog,'UserId'=>$UserInfo['UserId'],'adult'=>$adult,'comment'=>"登录成功");
											 			}
											 			else
											 			{
												 			$result = array('return'=>2,'comment'=>"登录失败");
											 			}											 				
										 			}
										 			else
										 			{										 			 	
														$UserBirthday = $oUser->GetUserCommunication($User['UserId'],"UserBirthDay");
											 			//根据用户生日信息判断是否为成年（1成年，0未成年，2为空）
											 			$adult = base_common::checkAdult($UserBirthday['UserBirthDay']);
											 			$result = array('return'=>1,'LoginId' => 0,'UserId'=>$UserInfo['UserId'],'adult'=>$adult,'comment'=>"登录成功");
										 			}							 													 			
									 			}
									 			else
									 			{
									 			 	$result = array('return'=>2,'comment'=>"无此游戏");
									 			}	
								 			}
								 			else
								 			{
									 			$result = array('return'=>0,'comment'=>"您尚未激活");										 										 			 	
								 			}						 				
							 			}
							 			else
							 			{
	 								 		$result = array('return'=>2,'comment'=>"账号处于".$is_freeze['FreezeCount']."次封停中<br>".date("Y-m-d H:i:s",$is_freeze['MaxTime'])."前禁止登陆");										 	
							 			}	
									}
									else
									{
								 		$result = array('return'=>2,'comment'=>"服务器配置信息错误");										 	
									}									 	
								}
				 			}
				 			else
				 			{
					 			$result = array('return'=>0,'comment'=>"您所选的服务器尚未开启");
				 			}				 			 	
			 			}								
					}
					else
					{
			 			$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");							 	
					}
				}
				else
				{
		 			$result = array('return'=>2,'comment'=>"账号或密码错误");				 			 	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"用户已被封停");		 	
			}			
		}
		else
		{
			$result = array('return'=>2,'comment'=>"账号或密码错误");		 	
		}
		return $result;	
	}
	public function UserLogout($User,$UserInfo)
	{
		
		$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));	
		if($UserInfo['UserId'])
		{
 			unset($User['ReturnType']);
			//判断用户所选服务器大区是否存在
			$ServerInfo = $ServerList[$User['ServerId']];
			if($ServerInfo['ServerId'])
			{
				$LogOut = $this->LogoutById($User,$UserInfo['UserName']);
				//返回记录更新条数
				if($LogOut)
				{
					$result = array('return'=>1,'comment'=>"登出成功");
				}
				else
				{
					$result = array('return'=>2,'comment'=>"登出失败");
				}								 									
			}
			else
			{
	 			$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");							 	
			}
		}
		else
		{
 			$result = array('return'=>2,'comment'=>"无此用户");						 	
		}
		return $result;	
	}
	public function getLogOnlineDate($LoginLog)
	{
		$return = 0;
		if(($LoginLog['AppId']!=100)&&($LoginLog['LogoutTime']>0))
		{
			$StartTime = $LoginLog['LoginTime'];
			$EndTime = $LoginLog['LogoutTime'];
			$Start = $StartTime;
			do
			{
				$Date = date('Y-m-d',$Start);
				$OnlineTime = min((strtotime($Date)+86400),$EndTime)-$StartTime;
				$StartTime = strtotime($Date)+86400;
				$DataArr = array('LoginId'=>$LoginLog['LoginId'],'AppId'=>$LoginLog['AppId'],'PartnerId'=>$LoginLog['PartnerId'],'ServerId'=>$LoginLog['ServerId'],'UserId'=>$LoginLog['UserId'],'OnlineDate'=>$Date,'OnlineTime'=>$OnlineTime,'UserRegTime'=>$LoginLog['UserRegTime'],'FirstLoginTime'=>$LoginLog['FirstLoginTime']);
				$table_date = $this->createUserOnlineLogTableDate($Date);
				if($OnlineTime>0)
				{
					$return += $this->db->insert($table_date,$DataArr);
				}
				$Start += 86400;	
			}
			while($Start<$EndTime);
		}
		return $return;

	}
	public function getLoginLogById($LoginId, $field = "*")
	{
		$Date = substr($LoginId,-6);
		$Id = substr($LoginId,0,strlen($LoginId)-6);
		$table_to_process = Base_Widget::getDbTable($this->table_date);
		$table_to_process .= "_".$Date;
		$sql = "SELECT $field FROM $table_to_process WHERE `LoginId` = ?";
		return $this->db->getRow($sql, $Id);
	}
	public function getLoginLogByTime($UserId,$ServerId,$LoginTime,$field = "*")
	{
		$Date = date("Ym",$LoginTime);
		$table_to_process = Base_Widget::getDbTable($this->table_date);
		$table_to_process .= "_".$Date;
		$sql = "SELECT $field FROM $table_to_process WHERE `UserId` = ? and `ServerId` = ? and `LoginTime` = ?";
		return $this->db->getRow($sql, array($UserId,$ServerId,$LoginTime));
	}
	public function getunLogoutLoginLog($UserId,$AppId,$PartnerId,$ServerId,$table_to_process)
	{
		$sql = "SELECT max(LoginId) as max FROM $table_to_process WHERE `LogoutTime` = 0 and `UserId` = $UserId and `AppId` = $AppId and `PartnerId` = $PartnerId and `ServerId` = $ServerId";
		return $this->db->getOne($sql);
	}	
	public function getFirstLogin($UserId,$AppId,$ParnterId,$ServerId)
	{

		//初始化查询条件
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereApp = $AppId?" AppId = ".$AppId." ":"";
		$wherePartner = $ParnterId?" PartnerId = ".$ParnterId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereApp,$wherePartner,$whereServer,$whereUser);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$first_table = Base_Widget::getDbTable($this->table_first);
    	$sql = "SELECT min(LoginTime) as FirstLogin FROM $first_table as log where 1 ".$where;
		return $this->db->getOne($sql);
	}	
	public function createUserOnlineLogTableDate($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_online);
		$table_to_process = Base_Widget::getDbTable($this->table_online)."_".date('Ymd',strtotime($Date));
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
			$sql = str_replace('`' . $this->table_online . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
	public function createUserLoginLogTableDate($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table);
		$table_to_process = Base_Widget::getDbTable($this->table_date)."_".$Date;
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
	public function createUserLoginLogTableUser($UserName)
	{
		$table_to_check = Base_Widget::getDbTable($this->table);

		$position = Base_Common::getUserDataPositionByName($UserName);

		$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
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
	public function InsertLoginLog($DataArr,$UserName)
	{
		$this->db->begin();
		$Date = date("Ym",$DataArr['LoginTime']);
		$table_date = $this->createUserLoginLogTableDate($Date);
		$table_user = $this->createUserLoginLogTableUser($UserName);

//		$unLogoutLoginId = $this->getunLogoutLoginLog($DataArr['UserId'],$DataArr['AppId'],$DataArr['PartnerId'],$DataArr['ServerId'],$table_user);
//		if($unLogoutLoginId)
//		{
//			return $unLogoutLoginId;
//		}
//		else
		{			 	
			$table_to_insert = $table_date;
			$date = $this->db->insert($table_to_insert,$DataArr);
			if($date&&$date!=23000)
			{
				$table_to_insert = $table_user;
				$DataArr['LoginId'] = $date.$Date;
				$user = $this->db->insert($table_to_insert,$DataArr);
				if($date&&$user)
				{
					$this->db->commit();
					$first_table = Base_Widget::getDbTable($this->table_first);
					unset($DataArr['FirstLoginTime']);
					$first = $this->db->insert($first_table,$DataArr);				
					return $DataArr['LoginId'];
				}
				else
				{
					$this->db->rollback();
					return false;		 	
				}
			}
			else
			{
				$this->db->rollback();
				return false;		 	
			}
		}

	}	
	public function LogoutById($DataArr,$UserName)
	{
		$this->db->begin();
		$Date = substr($DataArr['LoginId'],-6);
		$Id = substr($DataArr['LoginId'],0,strlen($DataArr['LoginId'])-6);

		$bind = array('LogoutTime'=>$DataArr['LogoutTime']);

		$table_to_update = Base_Widget::getDbTable($this->table_date);
		$table_to_update .= "_".$Date;
		$param = array($Id,$DataArr['UserId']);
		$date = $this->db->update($table_to_update, $bind, '`LogoutTime` = 0 and `LoginId` = ? and `UserId` = ?', $param);
		
		$position = Base_Common::getUserDataPositionByName($UserName);
		$table_to_update = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
		$param = array($DataArr['LoginId'],$DataArr['UserId']);
		$user = $this->db->update($table_to_update, $bind, '`LogoutTime` = 0 and `LoginId` = ? and `UserId` = ?', $param);
		if($date&&$user)
		{
			$this->db->commit();
			$LoginLog = $this->getLoginLogById($DataArr['LoginId']);
			$LoginLog['LoginId'] = $DataArr['LoginId'];
			$last_table = Base_Widget::getDbTable($this->table_last);
			$last = $this->db->replace($last_table,$LoginLog);
			$Online = $this->getLogOnlineDate($LoginLog);		
			return true;
		}
		else
		{
			$this->db->rollback();
			return false;		 	
		}
	}
	public function LogoutByTime($DataArr,$UserName)
	{
		$this->db->begin();
		$Date = date("Ym",$DataArr['LoginTime']);

		$bind = array('LogoutTime'=>$DataArr['LogoutTime']);
		$LoginTime = $DataArr['LoginTime'];
		unset($DataArr['LoginTime']);
		$table_to_update = Base_Widget::getDbTable($this->table_date);
		$table_to_update .= "_".$Date;
		$param = array($DataArr['UserId'],$DataArr['ServerId'],$LoginTime);
		$date = $this->db->update($table_to_update, $bind, '`LogoutTime` = 0 and `UserId` = ? and `ServerId` = ? and `LoginTime` = ?', $param);
		
		$position = Base_Common::getUserDataPositionByName($UserName);
		$table_to_update = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
		$user = $this->db->update($table_to_update, $bind, '`LogoutTime` = 0 and `UserId` = ? and `ServerId` = ? and `LoginTime` = ?', $param);
		
		if($date&&$user)
		{
			$this->db->commit();
			$LoginLog = $this->getLoginLogByTime($DataArr['UserId'],$DataArr['ServerId'],$LoginTime);
			$LoginLog['LoginId'] = $LoginLog['LoginId'].$Date;
			$last_table = Base_Widget::getDbTable($this->table_last);
			$last = $this->db->replace($last_table,$LoginLog);
			$Online = $this->getLogOnlineDate($LoginLog);		
			return true;
		}
		else
		{
			$this->db->rollback();
			return false;		 	
		}
	}
	
 	public function getLoginDay($StartDate,$EndDate,$RegStartDate,$RegEndDate,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'LoginCount'=>'count(*)',
		'LoginUser'=>'count(distinct(UserId))',
		'Date'=>"from_unixtime(LoginTime,'%Y-%m-%d')");
		//分类统计列
		$group_fields = array('Date');

		//初始化查询条件
		$whereStartDate = $StartDate?" LoginTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" LoginTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
		$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$whereRegStartDate,$whereRegEndDate,$whereServer,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		$StatArr['TotalData'] = array('LoginCount'=>0,'LoginUser'=>0);

		do
		{
			$StatArr['LoginDate'][$date] = array('LoginCount'=>0,'LoginUser'=>0);
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));
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
    foreach($DateList as $key => $value)
    {
      $table_name = Base_Widget::getDbTable($this->table_date)."_".$value;
      $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
		$LoginDateArr = $this->db->getAll($sql,false);
		if(is_array($LoginDateArr))
      {
        foreach ($LoginDateArr as $key => $Stat) 
		{
		//累加数据
		if(isset($StatArr['LoginDate'][$Stat['Date']]))
		{
			$StatArr['LoginDate'][$Stat['Date']]['LoginCount'] += $Stat['LoginCount'];
			$StatArr['LoginDate'][$Stat['Date']]['LoginUser'] += $Stat['LoginUser'];
		}
		else
		{
			$StatArr['LoginDate'][$Stat['Date']] = array('LoginCount'=>0,'LoginUser'=>0);
			$StatArr['LoginDate'][$Stat['Date']]['LoginCount'] += $Stat['LoginCount'];
			$StatArr['LoginDate'][$Stat['Date']]['LoginUser'] += $Stat['LoginUser'];
		}
		$StatArr['TotalData']['LoginCount'] += $Stat['LoginCount'];
		}
      }
    }
		return $StatArr;
	}
 	public function getLostDayTrace($StartDate,$EndDate,$Lag,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'Lag'=>'floor((LogoutTime-UserRegTime)/86400)',
		'LostUser'=>'count(distinct(UserId))',
		'RegDate'=>'from_unixtime(UserRegTime,"%Y-%m-%d")'
		);
		//分类统计列
		$group_fields = array('Lag','RegDate');

		//初始化查询条件
		$whereLost = $Lag?" LogoutTime <= '".(time()-$Lag)."'":"";
		$whereStartDate = $StartDate?" UserRegTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" UserRegTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$whereLost,$whereServer,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

	    $table_name = Base_Widget::getDbTable($this->table_last);
	    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
		$LoginDateArr = $this->db->getAll($sql,false);
		$StatArr['TotalData'] = array('Max'=>0,'LostUser'=>0);
		if(is_array($LoginDateArr))
    {
      foreach ($LoginDateArr as $key => $Stat) 
			{
				//累加数据
				if(isset($StatArr['LostTrace']['lag'][$Stat['Lag']]))
				{
					$StatArr['LostTrace']['lag'][$Stat['Lag']]['LostUser'] += $Stat['LostUser'];
				}
				else
				{
					$StatArr['LostTrace']['lag'][$Stat['Lag']] = array('LostUser'=>0);
					$StatArr['LostTrace']['lag'][$Stat['Lag']]['LostUser'] += $Stat['LostUser'];
				}
				$StatArr['TotalData']['Max'] = max($StatArr['TotalData']['Max'],$Stat['Lag']);
				$StatArr['TotalData']['LostUser'] += $Stat['LostUser'];
			}
    }
		for($i =0;$i<=$StatArr['TotalData']['Max'];$i++)
		{
			if(!isset($StatArr['LostTrace']['lag'][$i]))
			{
				$StatArr['LostTrace']['lag'][$i] = array('LostUser'=>0);
			}
			$Date = $StartDate;
			do
			{
				$DateLag = intval((time()-strtotime($Date))/86400);
				if($i<=$DateLag)
				{
					if(!isset($StatArr['LostTrace']['Date'][$Date]['lag'][$i]))
					{
						$StatArr['LostTrace']['Date'][$Date]['lag'][$i] = 	array('LostUser'=>0);
					}
				}
				ksort($StatArr['LostTrace']['Date'][$Date]['lag']);   	

				$Date = date("Y-m-d",strtotime($Date)+86400);	
			}
			while(strtotime($Date) < strtotime($EndDate));
		}
		ksort($StatArr['LostTrace']['lag']);
		ksort($StatArr['LostTrace']['Date']);
		return $StatArr;
	}
 	public function getFirstLoginDay($StartDate,$EndDate,$RegStartDate,$RegEndDate,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'LoginUser'=>'count(distinct(UserId))',
		'Date'=>"from_unixtime(LoginTime,'%Y-%m-%d')",
		'RegDate'=>'from_unixtime(UserRegTime,"%Y-%m-%d")');
		//分类统计列
		$group_fields = array('Date','RegDate');

		//初始化查询条件
		$whereStartDate = $StartDate?" LoginTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" LoginTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
		$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$whereRegStartDate,$whereRegEndDate,$whereServer,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		$StatArr['TotalData'] = array('LoginUser'=>0);
		do
		{
			$StatArr['LoginDate'][$date] = array('LoginUser'=>0);
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));

    $table_name = Base_Widget::getDbTable($this->table_first);
    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
	$LoginDateArr = $this->db->getAll($sql,false);
	if(is_array($LoginDateArr))
    {
      foreach ($LoginDateArr as $key => $Stat) 
			{
				//累加数据
				if(isset($StatArr['LoginDate'][$Stat['Date']]))
				{
					$StatArr['LoginDate'][$Stat['Date']]['LoginUser'] += $Stat['LoginUser'];
				}
				else
				{
					$StatArr['LoginDate'][$Stat['Date']] = array('LoginUser'=>0);
					$StatArr['LoginDate'][$Stat['Date']]['LoginUser'] += $Stat['LoginUser'];
				}
				$StatArr['TotalData']['LoginUser'] += $Stat['LoginUser'];
			}
    }
    
		return $StatArr;
	}
 	public function getFirstLoginUser($StartDate,$EndDate,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'LoginUser'=>'count(distinct(UserId))',
		);
		//初始化查询条件
		$whereStartDate = $StartDate?" UserRegTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" UserRegTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

    	$table_name = Base_Widget::getDbTable($this->table_first);
    	$sql = "SELECT $fields FROM $table_name as log where 1 ".$where;//.$groups;
		$LoginDateArr = $this->db->getAll($sql,false);
		$StatArr = array('TotalData' => array('LoginUser' => 0)); 
    	foreach ($LoginDateArr as $key => $Stat) 
		{
			$StatArr['TotalData']['LoginUser'] += $Stat['LoginUser'];
		}	   
		return $StatArr;
	}
 	public function getLastLogoutDay($StartDate,$EndDate,$RegStartDate,$RegEndDate,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'LogoutUser'=>'count(distinct(UserId))',
		'Date'=>"from_unixtime(LogoutTime,'%Y-%m-%d')");
		//分类统计列
		$group_fields = array('Date');

		//初始化查询条件
		$whereStartDate = $StartDate?" LogoutTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" LogoutTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
		$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereRegStartDate,$whereRegEndDate,$whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		$StatArr['TotalData'] = array('LogoutUser'=>0);
		do
		{
			$StatArr['LogoutDate'][$date] = array('LogoutUser'=>0);
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));

    	$table_name = Base_Widget::getDbTable($this->table_last);
    	$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
		$LoginDateArr = $this->db->getAll($sql,false);
		if(is_array($LoginDateArr))
    	{
     		foreach ($LoginDateArr as $key => $Stat) 
			{
				//累加数据
				if(isset($StatArr['LogoutDate'][$Stat['Date']]))
				{
					$StatArr['LogoutDate'][$Stat['Date']]['LogoutUser'] += $Stat['LogoutUser'];
				}
				else
				{
					$StatArr['LogoutDate'][$Stat['Date']] = array('LogoutUser'=>0);
					$StatArr['LogoutDate'][$Stat['Date']]['LogoutUser'] += $Stat['LogoutUser'];
				}
				$StatArr['TotalData']['LogoutUser'] += $Stat['LogoutUser'];
			}
    }
    
		return $StatArr;
	}
 	public function getOnlineDay($RegStartDate,$RegEndDate,$lag,$Date,$ServerId,$oWherePartnerPermission)
	{
		$StartTime = strtotime($Date);
		$EndTime = $StartTime+86400;
		$Time = $StartTime;
		$OnlineDay = array();
		do
		{
			$OnlineDay[$Time] = $this->getOnline($RegStartDate,$RegEndDate,$Time,$ServerId,$oWherePartnerPermission);
			$Time+=$lag;
		}
		while($EndTime>=$Time);
		return $OnlineDay;
	}
 	public function getOnline($RegStartDate,$RegEndDate,$time,$ServerId,$oWherePartnerPermission)
	{
		if($time > time())
		{
				$Return['UserCount'] = 	0;
				$Return['OnlineCount'] = 	0;			
		}
		else
		{
			//查询列
			$select_fields = array(
			'UserCount'=>'count(distinct(UserId))',
			'OnlineCount'=>'count(*)');	
			//初始化查询条件
			$whereLogin = " LoginTime <= '".$time."' ";
			$whereLogout = " (LogOutTime  >= '".$time."' or LogoutTime = 0)";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
			$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
			$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";
	

			$whereCondition = array($whereRegStartDate,$whereRegEndDate,$whereLogin,$whereLogout,$whereServer,$oWherePartnerPermission);
	
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成分类汇总列
			$where = Base_common::getSqlWhere($whereCondition);
			$Date = date("Ym",$time);
			$table_to_process = Base_Widget::getDbTable($this->table_date)."_".$Date;
			$Return = array('UserCount'=>0,'OnlineCount'=>0);
	    	$sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
			$Online = $this->db->getRow($sql);
			if(isset($Online))
			{
				$Return['UserCount'] = 	$Online['UserCount'];
				$Return['OnlineCount'] = 	$Online['OnlineCount'];
			}		 	
		}

		return $Return;
   	
	}
 	public function getLoginDetail($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$start,$pagesize)
	{
		$LoginCount = $this->getLoginDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission);
		if($LoginCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartTime = $StartTime?" LoginTime >= ".strtotime($StartTime)." ":"";
			$whereEndTime = $EndTime?" LoginTime <= ".strtotime($EndTime)." ":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	
			$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereServer,$oWherePartnerPermission);
			
			$order = " order by LoginTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
		    if($UserId)
		    {
					$position = Base_Common::getUserDataPositionById($UserId);			
					$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];    		
		    }
		    else
		    {
					$Date = date("Ym",strtotime($StartTime));			
					$table_to_process = Base_Widget::getDbTable($this->table_date)."_".$Date;     	
		    }
		    $StatArr = array('LoginDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$LoginDetailArr = $this->db->getAll($sql,false);
			if(isset($LoginDetailArr))
		    {
		    	foreach ($LoginDetailArr as $key => $value) 
				{
					if($UserId)
					{
						$value['LoginId'] = $value['LoginId'];
					}
					else
					{
						$value['LoginId'] = $value['LoginId'].date("Ym",$value['LoginTime']);
					}
					$StatArr['LoginDetail'][$value['LoginId']] = $value;
				}
		    }
  	}
  	
	 	$StatArr['LoginCount'] = $LoginCount; 
		return $StatArr;
	}
 	public function getLoginDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('LoginCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartTime = $StartTime?" LoginTime >= ".strtotime($StartTime)." ":"";
		$whereEndTime = $EndTime?" LoginTime <= ".strtotime($EndTime)." ":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereStartTime,$whereEndTime,$whereUser,$whereServer,$oWherePartnerPermission);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
	    if($UserId)
	    {
				$position = Base_Common::getUserDataPositionById($UserId);			
				$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];    		
	    }
	    else
	    {
				$Date = date("Ym",strtotime($StartTime));			
				$table_to_process = Base_Widget::getDbTable($this->table_date)."_".$Date;     	
	    }
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$LoginCount = $this->db->getOne($sql,false);
		if($LoginCount)
    	{
			return $LoginCount;    
		}
		else
		{
			return 0; 	
		}
	}
 	public function getFirstLoginDetail($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$start,$pagesize)
	{
		$LoginCount = $this->getFirstLoginDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission);
		if($LoginCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartTime = $StartTime?" LoginTime >= ".strtotime($StartTime)." ":"";
			$whereEndTime = $EndTime?" LoginTime <= ".strtotime($EndTime)." ":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	
			$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereServer,$oWherePartnerPermission);
			
			$order = " order by LoginTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
			$table_to_process = Base_Widget::getDbTable($this->table_first);    		

		    $StatArr = array('LoginDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$LoginDetailArr = $this->db->getAll($sql,false);
			if(isset($LoginDetailArr))
		    {
		    	foreach ($LoginDetailArr as $key => $value) 
				{
					if($UserId)
					{
						$value['LoginId'] = $value['LoginId'];
					}
					else
					{
						$value['LoginId'] = $value['LoginId'].date("Ym",$value['LoginTime']);
					}
					$StatArr['LoginDetail'][$value['LoginId']] = $value;
				}
		    }
  	}
  	
	 	$StatArr['LoginCount'] = $LoginCount; 
		return $StatArr;
	}
 	public function getFirstLoginDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('LoginCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartTime = $StartTime?" LoginTime >= ".strtotime($StartTime)." ":"";
		$whereEndTime = $EndTime?" LoginTime <= ".strtotime($EndTime)." ":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereStartTime,$whereEndTime,$whereUser,$whereServer,$oWherePartnerPermission);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table_first);    		

	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;		
		$LoginCount = $this->db->getOne($sql,false);
		if($LoginCount)
    	{
			return $LoginCount;    
		}
		else
		{
			return 0; 	
		}
	}
 	public function getLastLogoutDetail($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$start,$pagesize)
	{
		$LoginCount = $this->getLastLogoutDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission);
		if($LoginCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartTime = $StartTime?" LogoutTime >= ".strtotime($StartTime)." ":"";
			$whereEndTime = $EndTime?" LogoutTime <= ".strtotime($EndTime)." ":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	
			$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereServer,$oWherePartnerPermission);
			
			$order = " order by LogoutTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
			$table_to_process = Base_Widget::getDbTable($this->table_last);    		

		    $StatArr = array('LoginDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$LoginDetailArr = $this->db->getAll($sql,false);
			if(isset($LoginDetailArr))
		    {
		    	foreach ($LoginDetailArr as $key => $value) 
				{
					if($UserId)
					{
						$value['LoginId'] = $value['LoginId'];
					}
					else
					{
						$value['LoginId'] = $value['LoginId'].date("Ym",$value['LoginTime']);
					}
					$StatArr['LoginDetail'][$value['LoginId']] = $value;
				}
		    }
  		}  	
	 	$StatArr['LoginCount'] = $LoginCount; 
		return $StatArr;
	}
 	public function getLastLogoutDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('LoginCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartTime = $StartTime?" LogoutTime >= ".strtotime($StartTime)." ":"";
		$whereEndTime = $EndTime?" LogoutTime <= ".strtotime($EndTime)." ":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereStartTime,$whereEndTime,$whereUser,$whereServer,$oWherePartnerPermission);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table_last);    		

	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;		
		$LoginCount = $this->db->getOne($sql,false);
		if($LoginCount)
    	{
			return $LoginCount;    
		}
		else
		{
			return 0; 	
		}
	}
 	public function getLastLogout($UserId,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('LastLogoutTime'=>'max(LogoutTime)','LastLoginTime'=>'max(LoginTime)');
		//分类统计列

		//初始化查询条件
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereUser,$whereServer,$oWherePartnerPermission);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$position = Base_Common::getUserDataPositionById($UserId);			
		$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix']; 

	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;		
		$Last = $this->db->getRow($sql,false);
		if($Last)
    	{
			return $Last;    
		}
		else
		{
			return 0; 	
		}
	}
 	public function getOnlineSum($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$start,$pagesize)
	{
		$UserCount = $this->getOnlineSumCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission);
		if($UserCount)
		{
				//查询列
			$select_fields = array('UserId','SumTime'=>"sum(if(LogOutTime>=".strtotime($EndTime).",".strtotime($EndTime).",LogOutTime)-if(LoginTime<= ".strtotime($StartTime).",".strtotime($StartTime).",LoginTime))");
			//分类统计列
	
			//初始化查询条件
			$whereStartTime = $StartTime?" LogoutTime >= ".strtotime($StartTime)." ":"";
			$whereEndTime = $EndTime?" LoginTime <= ".strtotime($EndTime)." ":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	
			$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereServer,$oWherePartnerPermission);
			
       		$group_fields = array('UserId');
			$groups = Base_common::getGroupBy($group_fields);
			
			$order = " order by UserId desc";
			//$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
		    $StatArr = array('OnlineUser'=>array());


		    if($UserId)
		    {
					$position = Base_Common::getUserDataPositionById($UserId);			
					$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix']; 
				    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$groups.$order;//.$limit;
					$OnlineDetailArr = $this->db->getAll($sql,false);
					foreach($OnlineDetailArr as $key => $value)
					{
						$StatArr['OnlineUser'][$value['UserId']]['OnlineTime']=$value['SumTime'];
					}   		
		    }
		    else
		    {
					for($t=0;$t<16;$t++)
					{
						$table_to_process = Base_Widget::getDbTable($this->table_user)."_".dechex($t); 
					    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$groups.$order;//.$limit;
						$OnlineDetailArr = $this->db->getAll($sql,false);
						foreach($OnlineDetailArr as $key => $value)
						{
							$StatArr['OnlineUser'][$value['UserId']]['OnlineTime'] = $value['SumTime'];
						}    						
					}
		    }
  	}
  	
	 	$StatArr['UserCount'] = $UserCount;
		return $StatArr;
	}
 	public function getOnlineSumCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('UserCount'=>'count(distinct(UserId))');
		//分类统计列

		//初始化查询条件
		$whereStartTime = $StartTime?" LogoutTime >= ".strtotime($StartTime)." ":"";
		$whereEndTime = $EndTime?" LoginTime <= ".strtotime($EndTime)." ":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereStartTime,$whereEndTime,$whereUser,$whereServer,$oWherePartnerPermission);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
	    if($UserId)
	    {
			$position = Base_Common::getUserDataPositionById($UserId);			
			$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
			$UserCount = $this->db->getOne($sql,false);    		
	    }
	    else
	    {
			$UserCount = 0;
			for($t=0;$t<16;$t++)
			{
				$table_to_process = Base_Widget::getDbTable($this->table_user)."_".dechex($t); 
			    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
				$Count = $this->db->getOne($sql,false);
				$UserCount+=$Count;     						
			}
	    }
		if($UserCount)
    	{
			return $UserCount;    
		}
		else
		{
			return 0; 	
		}
	}
 	public function getLoginDayBySource($StartDate,$EndDate,$RegStartDate,$RegEndDate,$ServerId,$oWherePartnerPermission,$SourceProjectId,$SourceList,$SourceDetail)
	{
		//查询列
		$select_fields = array(
		'LoginCount'=>'count(*)',
		'LoginUser'=>'count(distinct(UserId))',
		'Date'=>"from_unixtime(LoginTime,'%Y-%m-%d')",
		'UserSourceId','UserSourceDetail','UserSourceProjectId');
		//分类统计列
		$group_fields = array('Date','UserSourceId','UserSourceDetail','UserSourceProjectId');

		//初始化查询条件
		$whereStartDate = $StartDate?" LoginTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" LoginTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
		$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
		if($SourceList)
		{
			foreach($SourceList as $Key => $value)
			{
				$t[$Key] = $Key;	
			}
			$whereSource = " UserSourceId in (".implode(",",$t).")";	
		}
		else
		{
		 	$whereSource = "";
		}
		$WhereSourceDetail = $SourceDetail?" UserSourceDetail = ".$SourceDetail." ":"";
		$WhereSourceProject = $SourceProjectId?" UserSourceProjectId = ".$SourceProjectId." ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$whereRegStartDate,$whereRegEndDate,$whereServer,$oWherePartnerPermission,$whereSource,$WhereSourceDetail,$WhereSourceProject);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		$StatArr['TotalData'] = array('LoginCount'=>0,'LoginUser'=>0);

		do
		{
			$StatArr['LoginDate'][$date] = array();
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));
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
    foreach($DateList as $key => $value)
    {
      $table_name = Base_Widget::getDbTable($this->table_date)."_".$value;
      $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$LoginDateArr = $this->db->getAll($sql,false);
			if(is_array($LoginDateArr))
      {
        foreach ($LoginDateArr as $key => $Stat) 
				{
  				//累加数据
  				if(isset($StatArr['LoginDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]))
  				{
  					$StatArr['LoginDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['LoginCount'] += $Stat['LoginCount'];
  					$StatArr['LoginDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['LoginUser'] += $Stat['LoginUser'];
  				}
  				else
  				{
  					$StatArr['LoginDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']] = array('LoginCount'=>0,'LoginUser'=>0);
  					$StatArr['LoginDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['LoginCount'] += $Stat['LoginCount'];
  					$StatArr['LoginDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['LoginUser'] += $Stat['LoginUser'];
  				}
  				$StatArr['TotalData']['LoginCount'] += $Stat['LoginCount'];
				}
      }
    }
		return $StatArr;
	}
 	public function getFirstLoginDayBySource($StartDate,$EndDate,$RegStartDate,$RegEndDate,$ServerId,$oWherePartnerPermission,$SourceProjectId,$SourceList,$SourceDetail)
	{
		//查询列
		$select_fields = array(
		'FirstLoginUser'=>'count(distinct(UserId))',
		'Date'=>"from_unixtime(LoginTime,'%Y-%m-%d')",
		'UserSourceId','UserSourceDetail','UserSourceProjectId');
		//分类统计列
		$group_fields = array('Date','UserSourceId','UserSourceDetail','UserSourceProjectId');

		//初始化查询条件
		$whereStartDate = $StartDate?" LoginTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" LoginTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
		$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
		if($SourceList)
		{
			foreach($SourceList as $Key => $value)
			{
				$t[$Key] = $Key;	
			}
			$whereSource = " UserSourceId in (".implode(",",$t).")";	
		}
		else
		{
		 	$whereSource = "";
		}
		$WhereSourceDetail = $SourceDetail?" UserSourceDetail = ".$SourceDetail." ":"";
		$WhereSourceProject = $SourceProjectId?" UserSourceProjectId = ".$SourceProjectId." ":"";
		$whereCondition = array($whereStartDate,$whereEndDate,$whereRegStartDate,$whereRegEndDate,$whereServer,$oWherePartnerPermission,$whereSource,$WhereSourceDetail,$WhereSourceProject);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		$StatArr['TotalData'] = array('FirstLoginUser'=>0);
		do
		{
			$StatArr['LoginDate'][$date] = array();
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));

    $table_name = Base_Widget::getDbTable($this->table_first);
    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
		$LoginDateArr = $this->db->getAll($sql,false);
		if(is_array($LoginDateArr))
    {
      foreach ($LoginDateArr as $key => $Stat) 
			{
				//累加数据
				if(isset($StatArr['LoginDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]))
				{
					$StatArr['LoginDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['FirstLoginUser'] += $Stat['FirstLoginUser'];
				}
				else
				{
					$StatArr['LoginDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']] = array('FirstLoginUser'=>0);
					$StatArr['LoginDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['FirstLoginUser'] += $Stat['FirstLoginUser'];
				}
				$StatArr['TotalData']['FirstLoginUser'] += $Stat['FirstLoginUser'];
			}
    }
		return $StatArr;
	}
 	public function getFirstLoginUserBySource($FirstStartDate,$FirstEndDate,$RegStartDate,$RegEndDate,$ServerId,$oWherePartnerPermission,$SourceProjectId,$SourceList,$SourceDetail)
	{
		//查询列
		$select_fields = array(
		'LoginUser'=>'count(distinct(UserId))',
		'Date'=>"from_unixtime(FirstLoginTime,'%Y-%m-%d')",
		'DayLag'=>"floor((LoginTime-FirstLoginTime)/86400)");
		//分类统计列
		$group_fields = array('Date','DayLag');

		//初始化查询条件
		$whereStartDate = $FirstStartDate?" FirstLoginTime >= '".strtotime($FirstStartDate)."' ":"";
		$whereEndDate = $FirstEndDate?" FirstLoginTime <= '".(strtotime($FirstEndDate)+86400-1)."' ":"";
		$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
		$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
		if($SourceList)
		{
			foreach($SourceList as $Key => $value)
			{
				$t[$Key] = $Key;	
			}
			$whereSource = " UserSourceId in (".implode(",",$t).")";	
		}
		else
		{
		 	$whereSource = "";
		}
		$WhereSourceDetail = $SourceDetail?" UserSourceDetail = ".$SourceDetail." ":"";
		$WhereSourceProject = $SourceProjectId?" UserSourceProjectId = ".$SourceProjectId." ":"";
		$whereCondition = array($whereStartDate,$whereEndDate,$whereRegStartDate,$whereRegEndDate,$whereServer,$oWherePartnerPermission,$whereSource,$WhereSourceDetail,$WhereSourceProject);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $FirstStartDate;
		//初始化结果数组
		do
		{
			$StatArr['LoginDate'][$date] = array();
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($FirstEndDate));
	    $DateStart = date("Ym",strtotime($FirstStartDate));
	    $DateEnd = date("Ym",strtotime($FirstEndDate));
	    $DateList = array();
	    $Date = $FirstStartDate;
	    do
	    {
	      $D = date("Ym",strtotime($Date));
	      $DateList[] = $D;
	      $Date = date("Y-m-d",strtotime("$Date +1 month"));
	    }
		while($D!=$DateEnd);
	    foreach($DateList as $key => $value)
	    {
	    	$table_name = Base_Widget::getDbTable($this->table_date)."_".$value;
			$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$LoginDateArr = $this->db->getAll($sql,false);
			if(is_array($LoginDateArr))
		    {
				foreach ($LoginDateArr as $key => $Stat) 
				{
					//累加数据
					if(isset($StatArr['LoginDate'][$Stat['Date']][$Stat['DayLag']]))
					{
						$StatArr['LoginDate'][$Stat['Date']][$Stat['DayLag']]['LoginUser'] += $Stat['LoginUser'];
					}
					else
					{
						$StatArr['LoginDate'][$Stat['Date']][$Stat['DayLag']] = array('LoginUser'=>0);
						$StatArr['LoginDate'][$Stat['Date']][$Stat['DayLag']]['LoginUser'] += $Stat['LoginUser'];
					}
				}
	    	}
	  	}
		return $StatArr;	
	}
 	public function getOnlineUser($StartDate,$EndDate,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('UserId','OnlineDate','OnlineHour'=>'floor(sum(Onlinetime)/3600)');
		//分类统计列
		$group_fields = array('OnlineDate','UserId');
		//初始化查询条件
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereServer,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		do
		{
			$StatArr['OnlineDate'][$date] = array();
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));
	    $DateStart = date("Ymd",strtotime($StartDate));
	    $DateEnd = date("Ymd",strtotime($EndDate));
	    $DateList = array();
	    $Date = $StartDate;
	    do
	    {
	      $D = date("Ymd",strtotime($Date));
	      $DateList[] = $D;
	      $Date = date("Ymd",strtotime("$Date +1 day"));
	    }
	    while($D!=$DateEnd);
	    foreach($DateList as $key => $value)
	    {
			$table_name = Base_Widget::getDbTable($this->table_online)."_".$value;
			$sql = "select OnlineDate,OnlineHour,count(*) as LoginUser from (SELECT $fields FROM $table_name as log where 1 ".$where.$groups.") as log group by OnlineDate,OnlineHour";
			$OnlineDateArr = $this->db->getAll($sql,false);
			if(is_array($OnlineDateArr))
			{
				foreach ($OnlineDateArr as $key => $Stat) 
				{
					if($Stat['OnlineHour']>=24)
					{
						$Stat['OnlineHour'] = 23;	
					}
					//累加数据
					if(isset($StatArr['OnlineDate'][$Stat['OnlineDate']][$Stat['OnlineHour']]))
					{
						$StatArr['OnlineDate'][$Stat['OnlineDate']][$Stat['OnlineHour']]['LoginUser'] += $Stat['LoginUser'];
					}
					else
					{
						$StatArr['OnlineDate'][$Stat['OnlineDate']][$Stat['OnlineHour']] = array('LoginUser'=>0);
						$StatArr['OnlineDate'][$Stat['OnlineDate']][$Stat['OnlineHour']]['LoginUser'] += $Stat['LoginUser'];
					}
				}
			}
	    }
		return $StatArr;
	}
 	public function getUserOnline($StartDate,$EndDate,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('UserId','OnlineDate');
		//分类统计列
		$group_fields = array('UserId','OnlineDate');
		//初始化查询条件
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereServer,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

	    $DateStart = date("Ymd",strtotime($StartDate));
	    $DateEnd = date("Ymd",strtotime($EndDate));
	    $DateList = array();
	    $Date = $StartDate;
	    do
	    {
	      $D = date("Ymd",strtotime($Date));
	      $DateList[] = $D;
	      $Date = date("Ymd",strtotime("$Date +1 day"));
	    }
	    while($D!=$DateEnd);
	    $StatArr['OnlineUser'] = array();
	    foreach($DateList as $key => $value)
	    {
			$table_name = Base_Widget::getDbTable($this->table_online)."_".$value;
			$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$OnlineUserArr = $this->db->getAll($sql,false);
			if(is_array($OnlineUserArr))
			{
				foreach ($OnlineUserArr as $key => $Stat) 
				{
					$StatArr['OnlineUser'][$Stat['UserId']] ++;					
				}
			}
	    }
	    foreach($StatArr['OnlineUser'] as $User => $Day)
	    {
	    	$StatArr['OnlineDay'][$Day]++;	
	    }
	    unset($StatArr['OnlineUser']);
		return $StatArr;
	}
 	public function getFirstLoginUserByAll($FirstStartDate,$FirstEndDate,$RegStartDate,$RegEndDate,$ServerId,$oWherePartnerPermission,$type)
	{
		//查询列
		if($type==1)
		{
			$select_fields = array(
			'LoginUser'=>'count(distinct(UserId))',
			'Date'=>"from_unixtime(FirstLoginTime,'%Y-%m-%d')",
			'DayLag'=>"floor((LoginTime-FirstLoginTime)/86400)");
		}
		else
		{
			$select_fields = array(
			'LoginUser'=>'count(distinct(UserId))',
			'Date'=>"from_unixtime(FirstLoginTime,'%Y-%m-%d')",
			'DayLag'=>"datediff(from_unixtime(LoginTime,'%Y-%m-%d'),from_unixtime(FirstLoginTime,'%Y-%m-%d'))");		 	
		}
		//分类统计列
		$group_fields = array('Date','DayLag');

		//初始化查询条件
		$whereStartDate = $FirstStartDate?" FirstLoginTime >= '".strtotime($FirstStartDate)."' ":"";
		$whereEndDate = $FirstEndDate?" FirstLoginTime <= '".(strtotime($FirstEndDate)+86400-1)."' ":"";
		$whereRegStartDate = $RegStartDate?" UserRegTime >= '".strtotime($RegStartDate)."' ":"";
		$whereRegEndDate = $RegEndDate?" UserRegTime <= '".(strtotime($RegEndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$whereRegStartDate,$whereRegEndDate,$whereServer,$oWherePartnerPermission);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $FirstStartDate;
		//初始化结果数组
		do
		{
			$StatArr['LoginDate'][$date] = array();
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($FirstEndDate));
	    $DateStart = date("Ym",strtotime($FirstStartDate));
	    $DateEnd = date("Ym",strtotime($FirstEndDate));
	    $DateList = array();
	    $Date = $FirstStartDate;
	    do
	    {
	      $D = date("Ym",strtotime($Date));
	      $DateList[] = $D;
	      $Date = date("Y-m-d",strtotime("$Date +1 month"));
	    }
		while($D!=$DateEnd);
	    foreach($DateList as $key => $value)
	    {
	    	$table_name = Base_Widget::getDbTable($this->table_date)."_".$value;
			$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$LoginDateArr = $this->db->getAll($sql,false);
			if(is_array($LoginDateArr))
		    {
				foreach ($LoginDateArr as $key => $Stat) 
				{
					//累加数据
					if(isset($StatArr['LoginDate'][$Stat['Date']][$Stat['DayLag']]))
					{
						$StatArr['LoginDate'][$Stat['Date']][$Stat['DayLag']]['LoginUser'] += $Stat['LoginUser'];
					}
					else
					{
						$StatArr['LoginDate'][$Stat['Date']][$Stat['DayLag']] = array('LoginUser'=>0);
						$StatArr['LoginDate'][$Stat['Date']][$Stat['DayLag']]['LoginUser'] += $Stat['LoginUser'];
					}
				}
	    	}
	  	}
		return $StatArr;	
	}
    
    public function UserLoginSumDate($UserId,$oWherePartnerPermission)
    {
        //初始化查询条件
		$whereCondition = array($oWherePartnerPermission);
        
        $where = Base_common::getSqlWhere($whereCondition);
        
        $position = Base_Common::getUserDataPositionById($UserId);		
		$table_to_process = Base_Widget::getDbTable($this->table_user)."_".$position['db_fix'];
        
        $sql = "SELECT sum(`LogoutTime`-`LoginTime`) as `LoingTime` FROM $table_to_process WHERE `UserId` = $UserId and `LogoutTime` > 0 and `LogoutTime` > `LoginTime` ".$where;
        return $this->db->getOne($sql,false);
    }
    
    public function getUserLoginCountByDate($UserList,$StartDate,$EndDate,$oWherePartnerPermission)
    {
		$whereCondition = array($oWherePartnerPermission);
        
        $where = Base_common::getSqlWhere($whereCondition);
        
        //初始化结果数组
        $Date = $StartDate;
        $CountList = array('Count'=>array());         
        do
        {
            $CountList['Count'][$Date] = array('UserCount'=> 0);
            $Date = date("Y-m-d",(strtotime($Date)+86400));
        }
        while(strtotime($Date) <= strtotime($EndDate));
        
        foreach($CountList['Count'] as $date=>$arr){
            $table_to_process = Base_Widget::getDbTable($this->table_date)."_".date("Ym",strtotime($date));
        
            $sql = "SELECT count(distinct UserId) as `LoginCount` FROM $table_to_process WHERE `UserId` in (".implode(",",$UserList).") and LoginTime > '".strtotime($date)."' and LoginTime < '".(strtotime($date)+86400)."' ".$where;
            $CountList['Count'][$date]['UserCount'] = $this->db->getOne($sql,false);
        }
        return $CountList;
    }
}

<?php
/**
 * 用户相关mod层
 * @author 张骥 <344505721@qq.com>
 */


class Lm_DB2_DB2 extends Base_Widget
{
	//声明所用到的表
    protected $table = 'login_log';
    protected $table_login_log = 'login_log_user';
    protected $table_first_login = 'first_login';
    protected $table_last_logout = 'last_logout';
    protected $login_log_date = 'login_log_date';
    protected $table_online = 'online_log';
    protected $table_to_del = 'user_to_del';
    
	public function GetEmptyFirstLoginTimeUserId($table_suffix)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_login_log);
        $table_to_process .= "_".$table_suffix;
        
        $group_fields = array('UserId','AppId','PartnerId','ServerId');
        $groups = Base_common::getGroupBy($group_fields);
           
        $whereFirstLoginTime = " FirstLoginTime = '0' ";        
        $whereCondition = array($whereFirstLoginTime);
        $where = Base_common::getSqlWhere($whereCondition);
        
        $sql = "select UserId,AppId,PartnerId,ServerId from $table_to_process where 1 ".$where.$groups." order by UserId limit 0,100";
        $UserList = $this->db->getAll($sql);
                
        return $UserList;
	}     
    
    public function UpdateUserFirstLoginTime($table_suffix,$bind,$param)
	{
        $table_to_process = Base_Widget::getDbTable($this->table_login_log);
        $table_to_process .= "_".$table_suffix;
        
        if(intval($bind['FirstLoginTime']) != 0){
            return $this->db->update($table_to_process, $bind, '`UserId` = ? and `AppId` = ? and `PartnerId` = ? and `ServerId` = ?', array_values($param));
        }
    }
    
    public function UpdateUserFirstLoginTimeByDate($table_suffix,$bind,$param)
	{
        $table_to_process = Base_Widget::getDbTable($this->login_log_date);
        $table_to_process .= "_".$table_suffix;
        
        return $this->db->update($table_to_process, $bind, '`UserId` = ?', $param);
    }
    
    public function GetInsertFirstLoginUser($table_suffix)
	{
        $table_to_process = Base_Widget::getDbTable($this->table_login_log);
        $table_to_process .= "_".$table_suffix;
        
        $group_fields = array('UserId','AppId','PartnerId','ServerId');
        $groups = Base_common::getGroupBy($group_fields);
        
        $sql = "select 
                t.UserId,t.AppId,t.PartnerId,t.ServerId,t.LoginTime,t.UserLoginIp,t.LogoutTime,t.LoginId,t.UserSourceId,t.UserSourceDetail,t.UserSourceProjectId,t.UserSourceActionId,t.UserRegTime 
                from (select UserId,AppId,PartnerId,ServerId,min(LoginTime) as first
                from $table_to_process 
                where 1 $where$groups) as log , $table_to_process as t 
                where t.UserId = log.UserId and t.AppId = log.AppId and t.PartnerId = log.PartnerId and t.ServerId = log.ServerId and t.LoginTime = log.first";
        
        return $sql;
    }
    
    public function GetInsertLastLogoutUser($table_suffix)
	{
        $table_to_process = Base_Widget::getDbTable($this->table_login_log);
        $table_to_process .= "_".$table_suffix;
        
        $group_fields = array('UserId','AppId','PartnerId','ServerId');
        $groups = Base_common::getGroupBy($group_fields);

		$where = "and LogoutTime > 0 ";
        
        $sql = "select 
                t.UserId,t.AppId,t.PartnerId,t.ServerId,t.LoginTime,t.UserLoginIp,t.LogoutTime,t.LoginId,t.UserSourceId,t.UserSourceDetail,t.UserSourceProjectId,t.UserSourceActionId,t.UserRegTime,t.FirstLoginTime 
                from (select UserId,AppId,PartnerId,ServerId,max(LogoutTime) as last
                from $table_to_process 
                where 1 $where$groups) as log , $table_to_process as t 
                where t.UserId = log.UserId and t.AppId = log.AppId and t.PartnerId = log.PartnerId and t.ServerId = log.ServerId and t.LogoutTime = log.last";
        
        return $sql;
    }
    
    public function InsertFirstLogin($UserIdListSql)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_first_login);
        $sql = "replace into $table_to_process $UserIdListSql";
        
        $return = $this->db->query($sql);
        
        if($return){
            $this->writeTxt("InsertFirstLogin.log",$sql."\n");
        }
                
		return $return;		
	}
    
    public function InsertLastLogout($UserIdListSql)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_last_logout);
		$sql = "replace into $table_to_process $UserIdListSql";
        
        $return = $this->db->query($sql);
        
        if($return){
            $this->writeTxt("InsertLastLogout.log",$sql."\n");
        }
        
		return $return;			
	}
    
    public function GetLoginLogDate($table_suffix,$page)
	{
        $table_to_process = Base_Widget::getDbTable($this->login_log_date);
        $table_to_process .= "_".$table_suffix;
		$page *= 1000;
        
        $sql = "select * from $table_to_process order by LoginTime limit $page,1000 ";
        $UserList = $this->db->getAll($sql);
        
        $this->writeTxt("GetLoginLogDate.log",$sql."\n");
        
        return $UserList;
    }

	public function GetLoginLogConut($table_suffix){
		$table_to_process = Base_Widget::getDbTable($this->login_log_date);
        $table_to_process .= "_".$table_suffix;

		$sql = "select count(*) from $table_to_process";
		$count = $this->db->getOne($sql);
        
        $this->writeTxt("GetLoginLogConut.log",$sql."\n");

		return $count;
	}
    
    public function GetLoginLogUserTable($UserId)
	{
	    $UserId = substr($UserId,-3);
        $UserId = sprintf("%02x",dechex($UserId));
        $UserId = substr($UserId,1);
        
		$table_to_process = Base_Widget::getDbTable($this->table_login_log)."_".$UserId;
        
		return $table_to_process;
	}
    
    public function GetLoginLogDateUserId($table_suffix)
	{
        $table_to_process = Base_Widget::getDbTable($this->login_log_date);
        $table_to_process .= "_".$table_suffix;
        
        $sql = "select distinct(UserId) from $table_to_process where 1 ";
        $UserId = $this->db->getAll($sql);
        
        $this->writeTxt("GetLoginLogDateUserId.log",$sql."\n");
        
        return $UserId;
    }
    
    public function InsertLoginLogUser($table_to_process,$DataArr)
	{
		return $this->db->insert($table_to_process,$DataArr);		
	}
    
    public function DeleteLoginLogUser($table_suffix)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_login_log);
        $table_to_process .= "_".$table_suffix;
        
        $sql = "delete from $table_to_process";
        
        $this->writeTxt("DeleteLoginLogUser.log",$sql."\n");
        
        return $this->db->query($sql);	
	}
    
    public function writeTxt($filename,$content)
    {
    	$logpath = "/www/web_usercenter/LoginLog/";
        //$logpath = "d:\\wamp\\www\\web_usercenter\\LoginLog\\";
        
    	$filename = $logpath.$filename;
    	$fp = fopen($filename,'a+');
    	fwrite($fp,$content);
    	fclose($fp);
    }
    public function getOnlineLog()
    {
    	$DateArr = array('201210','201211','201212','201301','201302');
    	foreach($DateArr as $key => $value)
    	{
    		$table_name = Base_Widget::getDbTable($this->login_log_date)."_".$value;
    		$sql = "select * from $table_name where LogoutTime >0 and AppId = 101";
    		$Log[$value] = $this->db->getAll($sql);
    	}
    	return $Log;	
    }
    public function deleteOldOnlineLog()
    {
    	$StartDate = "2012-09-01";
    	$EndDate = "2013-03-01";
    	$Start = $StartDate;
    	do{
	    	$Date = date("Ymd",strtotime($Start));
	    	$DateList[$Date]=1;
	    	$Start = date('Y-m-d',strtotime($Start)+86400);
    	}
    	while(strtotime($Start)<=strtotime($EndDate));
    	
    	
    	foreach($DateList as $key => $value)
    	{
    		$table_name = Base_Widget::getDbTable($this->table_online)."_".$key;
    		$sql = "drop table $table_name";
    		echo $sql.":";
    		echo ($this->db->query($sql));
    		echo "\n";
    	}
    }
	/*
	*释放激活码，将lm_active_code.active_code中的ActiveTime和ActiveUser制空
	*$User 一维数组 array("UserId"=>"","UserName"=>"")
	*/
	public function ReleaseActiveCodeByUserID($User)
	{
	       $table = "active_code";
	       $table_to_process = Base_Widget::getDbTable($table);
	      
	       $sql = "SELECT COUNT(*) FROM $table_to_process WHERE `ActiveUser`=".$User["UserId"];
	       $Count = $this->db->getOne($sql);
	       $bind = array("ActiveTime"=>0,"ActiveUser"=>0);	       	       
	       if($this->db->update($table_to_process, $bind, '`ActiveUser` = ?', $User["UserId"]))
	       {
			return $Count;
	       }
	}
	/*
	 *删除用户登录(删除根据用户名md5的表和时间的表)，删除首次登陆和最后一次登出记录
	 *并更新lm_user.user_to_del中的LoginLog和OnlineLog字段
	 * 2013/3/18 
	 */
	/*public function DelLoginLogByUserID($User)
	{
		//删除首次登陆
		$table_first_login = "first_login";
		$del_first_login = Base_Widget::getDbTable($table_first_login);
		//echo $User["UserId"]."<br/>";
		$this->db->delete($del_first_login,"`UserId` = ?", $User["UserId"]);
		
		//最后一次登出记录
		$table_last_logout = "last_logout";
		$del_last_logout = Base_Widget::getDbTable($table_last_logout);		
		$this->db->delete($table_last_logout,"`UserId` = ?", $User["UserId"]);
		
		//删除用户登录(删除时间的表,
		$table_login_log_date = "login_log_date";
		$DateArr = $this->getEveryMonth("201210","201303");
		foreach($DateArr as $k=>$v)
		{
			$del_login_log_date = Base_Widget::getDbTable($table_login_log_date)."_".$v;
			$this->db->delete($del_login_log_date,"`UserId` = ?", $User["UserId"]);			
		}
		
		//删除用户登录(根据用户名md5的表)
		$table_login_log_user = "login_log_user";		
		$FirstChar = $this->getMd5First($User['UserName']);
		$del_login_log_user = Base_Widget::getDbTable($table_login_log_user)."_".$FirstChar;
		$sql = "SELECT COUNT(*) FROM $del_login_log_user WHERE UserId = ".$User["UserId"];
		$LoginCount = $this->db->getOne($sql);
		if($this->db->delete($del_login_log_user, "`UserId` = ?", $User["UserId"]))
		{				
			return $LoginCount;
		}
				
	}*/
	/*
	 *删除在线日志
	 */
	public function DelOnlineLogByUserId($User)
	{
		set_time_limit(0);
		$table_online_log = "online_log";
		$dateArr = $this->getEveryDay("20130104","20130310");		
		$OnlineCount = 0;
		foreach($dateArr as $k=>$v)
		{
			//在线状态可能每个月都有,每天都有，要计算在线的总和，要先查出来，然后返回，删除			
			$del_online_log = Base_Widget::getDbTable($table_online_log)."_".$v;
			//echo $v."<br/>";
			$sql = "SELECT COUNT(*) FROM $del_online_log WHERE UserId = ".$User["UserId"];
			$OnlineCount += intval($this->db->getOne($sql));
			$this->db->delete($del_online_log,"`UserId` = ?", $User["UserId"]);
		}
		return $OnlineCount;
		
	}
	//日期格式 ；201210
	public function getEveryMonth($startMonth,$endMonth)
	{
		$curtime = $starttime = strtotime($startMonth."01");		
		$endtime = strtotime($endMonth."01");
		$DateArr = array();
		while($curtime < $endtime)
		{
			$DateArr[] = date("Ym",$curtime);
			
			$curtime =strtotime("next month",$curtime);						
		}
		$DateArr[] = $endMonth;	
		return $DateArr;
	}
	//$startDay $endDay 格式：20130104
	public function getEveryDay($startDay,$endDay)
	{		
		$curtime = $starttime = strtotime($startDay);
		$endtime = strtotime($endDay);
		$DateArr = array();
		while($curtime < $endtime)
		{
			$DateArr[] = date("Ymd",$curtime);
			$curtime += 86400;						
		}
		$DateArr[] = $endDay;
		return $DateArr;
	}
	public function getMd5First($str)
	{		
		$new_str = md5($str);		
		return substr($new_str,0,1);
	}

}

<?php
/**
 * 用户相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Lm_User extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_info_base';
	protected $table_active = 'user_info_active';
	protected $table_communication = 'user_info_communication';
	protected $table_mail = 'user_mail';
	protected $table_reg_log = 'user_reg_log';
	protected $table_password_reset_log = 'password_reset_log';
	protected $table_answer = 'user_security_answer';
	
	protected $character_freeze = 'character_freeze';
	protected $character_freeze_log = 'character_freeze_log';
	protected $character_kick_off = 'character_kick_off';
	protected $character_kick_off_log = 'character_kick_off_log';
	


	public function createUserRegLogTable($Date)
	{	
		$table_to_check = Base_Widget::getDbTable($this->table);
		$table_to_process = Base_Widget::getDbTable($this->table_reg_log)."_".$Date;
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
	public function InsertUser($DataArr)
	{
		$this->db->begin();
		$position = Base_Common::getUserDataPositionByName($DataArr['UserName']);
		$DataArr['UserPosionFix'] = $position['fix'];
		$DataArr['UserPassWord'] = md5($DataArr['UserPassWord']);
		$UserMail = $DataArr['UserMail'];
		unset($DataArr['UserMail']);
		$table_to_insert = Base_Common::getUserTable($this->table,$position);
		$UserIdPre = $this->db->insert($table_to_insert,$DataArr);
		$DataArr['UserId'] = $UserIdPre.sprintf("%03d",$DataArr['UserPosionFix']);
		unset($DataArr['UserPosionFix']);
		$CommunicationArr = array('UserId'=>$DataArr['UserId'],/*'UserMail'=>$UserMail,*/'UserBirthDay'=>'1970-01-01');
		//$UserMailAuthApplyTime = time();
		//$insertMail = $this->insertUserMail(array('UserId'=>$DataArr['UserId'],'UserMail'=>$UserMail,'UserMailAuthApplyTime'=>$UserMailAuthApplyTime));
		//同时加入用户联系信息
		$Communication  = $this->InsertUserCommunication($DataArr['UserId'],$CommunicationArr);
		if($UserIdPre&&$Communication/*&&$insertMail*/)
		{
			//同时加入注册日志
			$RegLog = $this->InsertUserRegLog($DataArr);
			if($RegLog)
			{
				$this->db->commit();
				//$this->sendAuthMail($DataArr['UserId'],$UserMail,$UserMailAuthApplyTime);				
				return $DataArr['UserId'];
			}
			else
			{
					$this->db->rollBack();
					return false;
			}			
		}
		else
		{
			$this->db->rollBack();
			return false;
		}

	}
	public function InsertUserRegLog($DataArr)
	{
		$Date = date("Ym",$DataArr['UserRegTime']);
		$table_to_insert = $this->createUserRegLogTable($Date);
		$DataArr['UserPassWord'] = md5($DataArr['UserPassWord']);
		return $this->db->insert($table_to_insert,$DataArr);
	}
	public function GetUserByName($UserName,$fields="*")
	{
		$position = Base_Common::getUserDataPositionByName($UserName);
		$table_to_process = Base_Common::getUserTable($this->table,$position);
		$sql = "select $fields from $table_to_process where UserName = ?";
		$return = $this->db->getRow($sql,$UserName,false);
		if($return['UserId'])
		{
			$return['UserId'] = $return['UserId'].sprintf("%03d",$return['UserPosionFix']);	
		}
		return $return;
	}
	public function GetUserById($UserId,$fields="*")
	{
		$position = Base_Common::getUserDataPositionById($UserId);
		$Id = substr($UserId,0,-3);
		$table_to_process = Base_Common::getUserTable($this->table,$position);
		$sql = "select $fields from $table_to_process where UserId = ?";
		$return = $this->db->getRow($sql,$Id,false);
		if($return['UserId'])
		{
			$return['UserId'] = $return['UserId'].sprintf("%03d",$return['UserPosionFix']);	
		}
		
		return $return;
	}
	public function InsertUserCommunication($UserId,$DataArr)
	{
    	$position = Base_Common::getUserDataPositionById($UserId);
		$Id = substr($UserId,0,-3);
		$table_to_insert = Base_Common::getUserTable($this->table_communication,$position);
		return $this->db->insert($table_to_insert,$DataArr);
	}
	public function GetUserCommunication($UserId,$fields="*")
	{
    	$position = Base_Common::getUserDataPositionById($UserId);
		$Id = substr($UserId,0,-3);
		$table_to_process = Base_Common::getUserTable($this->table_communication,$position);
		$sql = "select $fields from $table_to_process where UserId = ?";
		return $this->db->getRow($sql,$UserId,false);
	}
	public function InsertUserActive($DataArr)
	{
		$oActive= new Lm_Active();
		$this->db->begin();
		$position = Base_Common::getUserDataPositionById($DataArr['UserId']);
		$Id = substr($DataArr['UserId'],0,-3);
		$table_to_insert = Base_Common::getUserTable($this->table_active,$position);
		$active =  $this->db->insert($table_to_insert,$DataArr);
		if($active)
		{
			$updateActiveCode = $oActive->updateUserActiveCode($DataArr['ActiveCode'],array('ActiveUser'=>$DataArr['UserId'],'ActiveTime'=>$DataArr['ActiveTime']));
			if($updateActiveCode)
			{
				$this->db->commit();
				return true;
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
	public function GetUserActive($UserId,$AppId,$PartnerId,$fields="*")
	{
		$position = Base_Common::getUserDataPositionById($UserId);
		$Id = substr($UserId,0,-3);
		$table_to_process = Base_Common::getUserTable($this->table_active,$position);
		$param = array($UserId,$AppId,$PartnerId);
		$sql = "select $fields from $table_to_process where `UserId` = ? and `AppId` = ? and `PartnerId` = ?";
		return $this->db->getAll($sql,$param,false);
	}
	  function idcard_verify($IdCard)
	  {
	      $len = strlen($IdCard);
	      if($len==15)
	      {
	          $t = $this->idcard_15to18($IdCard);
	          if($t['return']==1)
	          {
	            $IdCard = $t['idcard'];  
	          }
	          else
	          {
	            $return = $t;
	          }
	      }
	      $check =  $this->idcard_checksum18($IdCard);
	      return $check;
	  }
	  function idcard_verify_number($idcard_base)
	  {
		   if (strlen($idcard_base) != 17) 
		   {
		        $return = array('return'=>0,'showMsg' => "您输入的身份证位数不对，请您重新填写！！！");
		   }
		   else
		   {
		        //加权因子
		        $factor = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
		        //校验码对应值
		        $verify_number_list = array('1','0','X','9','8','7','6','5','4','3','2');
		        $checksum = 0;
		        for ($i = 0; $i < strlen($idcard_base); $i++) {
		            //echo intval(substr($idcard_base, $i, 1))."<br>";
		            $checksum += intval(substr($idcard_base, $i, 1)) * $factor[$i];
		        }
		        $mod = strtoupper($checksum % 11);
		        $verify_number = $verify_number_list[$mod];
		        $return = array('return'=>1,'verify_number' => $verify_number);
		    }
		    
		    return $return;
	  }
	  //将15位身份证升级到18位
	  function idcard_15to18($idcard)
	  {
	    if (strlen($idcard) != 15)
	    {
	        $return = array('return'=>0,'showMsg' => "您输入的身份证位数不对，请您重新填写！！！");
	    }
	    else
	    {
	        //如果身份证顺序码是996 997 998 999,这些是为百岁以上老人的特殊编码
	        if (array_search(substr($idcard, 12, 3), array(
	            '996',
	            '997',
	            '998',
	            '999')) != false) {
	            $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
	        } else {
	            $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
	        }
	        $idcard = $idcard . $this->idcard_verify_number($idcard);
	        $return = array('return'=>1,'idcard' => $idcard);
	    }
	     return $return;
	  }
	  //18位身份证校验码有效性检查
	  function idcard_checksum18($idcard)
	  {
	    if (strlen($idcard) != 18) 
	    {
	        $return = array('return'=>0,'showMsg' => "您输入的身份证位数不对，请您重新填写！！！");
	    }
	    $aCity = array(
	        11 => "北京",
	        12 => "天津",
	        13 => "河北",
	        14 => "山西",
	        15 => "内蒙古",
	        21 => "辽宁",
	        22 => "吉林",
	        23 => "黑龙江",
	        31 => "上海",
	        32 => "江苏",
	        33 => "浙江",
	        34 => "安徽",
	        35 => "福建",
	        36 => "江西",
	        37 => "山东",
	        41 => "河南",
	        42 => "湖北",
	        43 => "湖南",
	        44 => "广东",
	        45 => "广西",
	        46 => "海南",
	        50 => "重庆",
	        51 => "四川",
	        52 => "贵州",
	        53 => "云南",
	        54 => "西藏",
	        61 => "陕西",
	        62 => "甘肃",
	        63 => "青海",
	        64 => "宁夏",
	        65 => "新疆",
	        71 => "台湾",
	        81 => "香港",
	        82 => "澳门",
	        91 => "国外");
	    //非法地区
	    if (!array_key_exists(intval(substr($idcard, 0, 2)), $aCity)) {
	        $return = array('return'=>0,'showMsg' => "您输入的身份证地区非法，请您重新填写！！！");
	    }
	    //验证生日
	    if (!checkdate(substr($idcard, 10, 2), substr($idcard, 12, 2), substr($idcard, 6,
	        4))) {
	        $return = array('return'=>0,'showMsg' => "您输入的身份证生日不对，请您重新填写！！！");
	    }
	    $idcard_base = substr($idcard, 0, 17);
	    $verify = $this->idcard_verify_number($idcard_base);
	    if ($verify['verify_number'] != strtoupper(substr($idcard, 17, 1))) 
	    {
	        $return = array('return'=>0,'showMsg' => "您输入的身份证不对，请您重新填写！！！");
	    } else {
	        $return = array('return'=>1,'showMsg' => "身份证合法");
	    }
	    return $return;
	  }
	public function updateUserCommunication($UserId,$bind)
	{
    	$position = Base_Common::getUserDataPositionById($UserId);
		$table_to_update = Base_Common::getUserTable($this->table_communication,$position);
   	return $this->db->update($table_to_update,$bind, '`UserId` = ?', $UserId); 	
	}
 	 public function updateUser($UserId,$bind)
	{
    	$position = Base_Common::getUserDataPositionById($UserId);
		$Id = substr($UserId,0,-3);
		$table_to_update = Base_Common::getUserTable($this->table,$position);
   	return $this->db->update($table_to_update,$bind, '`UserId` = ?', $Id); 	
	}
	public function updateUserCoin($UserId,$Coin)
	{
        $position = Base_Common::getUserDataPositionById($UserId);
		$Id = substr($UserId,0,-3);
		$table_to_update = Base_Common::getUserTable($this->table,$position);
		$sql = "UPDATE ".$table_to_update." SET `UserCoin` = `UserCoin` + ($Coin)  WHERE `UserId` = ? ";
		return $this->db->query($sql, $Id);	
	}
	public function updateUserCredit($UserId,$Credit)
	{
    $position = Base_Common::getUserDataPositionById($UserId);
		$Id = substr($UserId,0,-3);
		$table_to_update = Base_Common::getUserTable($this->table,$position);
		$sql = "UPDATE ".$table_to_update." SET `UserCredit` = `UserCredit` + $Credit WHERE `UserId` = ? ";
		return $this->db->query($sql, $Id);	
	}
  	public function getUserMail($UserMail,$fields="*")
	{
		$position = Base_Common::getUserDataPositionByName($UserMail);
		$table_to_process = Base_Common::getUserTable($this->table_mail,$position);
		$sql = "select $fields from $table_to_process where UserMail = ?";
		return $this->db->getRow($sql,$UserMail,false);
	}
  	public function insertUserMail($DataArr)
	{
		$position = Base_Common::getUserDataPositionByName($DataArr['UserMail']);
		$table_to_insert = Base_Common::getUserTable($this->table_mail,$position);
		return $this->db->insert($table_to_insert,$DataArr);
	}
 	public function updateUserMail($UserId,$UserMail,$OldUserMail = 0)
 	{
		$this->db->begin(); 
    $position = Base_Common::getUserDataPositionById($UserId);
    $updateArr = array('UserMail'=>$UserMail);
 		$table_to_update = Base_Common::getUserTable($this->table_communication,$position);
    $update = $this->db->update($table_to_update,$updateArr, '`UserId` = ?', $UserId);
    if($OldUserMail)
    {
	    $positionMail = Base_Common::getUserDataPositionByName($OldUserMail);
			$table_to_del = Base_Common::getUserTable($this->table_mail,$positionMail);
			$delArr = array($UserId,$OldUserMail);
	    $del = $this->db->delete($table_to_del, '`UserId` = ? and `UserMail` = ?', $delArr);
  	}
  	else
  	{
  		$del = true; 	
  	}
  	$insertArr = array('UserId'=>$UserId,'UserMail'=>$UserMail,'UserMailAuthApplyTime'=>time());
  	$insert = $this->insertUserMail($insertArr);
  	if($update&&$del&&insert)
		{
			$this->db->commit();
			$this->sendAuthMail($insertArr['UserId'],$insertArr['UserMail'],$insertArr['UserMailAuthApplyTime']);
			return true;			
		}
		else
		{
			$this->db->rollBack();
			return false;
		} 			
 	}
 	public function sendAuthMail($UserId,$UserMail,$AuthApplyTime)
 	{
 		$UserInfo = $this->GetUserById($UserId);
 		$User = array('UserId'=>$UserId,'EndTime'=>$AuthApplyTime+86400,'UserMail'=>$UserMail);
 		$User['sign'] = base_common::check_sign($User,'authmail');
 		$AuthUrl = $this->config->passporturl.(Base_Common::getUrl('','','',$User))."&c=email_verification&m=check_mail&d=usercenter";
 		$UrlList = array('AuthUrl'=>array('href'=>$AuthUrl,'txt'=>$AuthUrl));
 		$MailContent = array('UserMail'=>$UserMail,'UserName'=>$UserInfo['UserName'],'UrlList'=>$UrlList,'title'=>"《无尽英雄》绑定邮箱验证");
 		$MailType = "AuthMail";
 		$oMail = new Lm_Mail();
 		$mail = $oMail->createMail($MailType,$UserId,$MailContent);
 		return $mail;
 	}
 	public function authUserMail($UserId,$UserMail)
 	{
		$position = Base_Common::getUserDataPositionByName($UserMail);
    $bindArr = array($UserId,$UserMail,0);
    $updateArr = array('UserMailAuth'=>1,'UserMailAuthTime'=>time());
 		$table_to_update = Base_Common::getUserTable($this->table_mail,$position);
    return $this->db->update($table_to_update,$updateArr, '`UserId` = ? and `UserMail` = ? and `UserMailAuth` = ?', $bindArr); 			
 	}
 	public function getRegDay($StartDate,$EndDate,$PartnerId)
	{
		//查询列
		$select_fields = array(
		'RegUser'=>'count(distinct(UserId))',
		'RegIp'=>'count(distinct(UserRegIp))',
		'Date'=>"from_unixtime(UserRegTime,'%Y-%m-%d')");
		//分类统计列
		$group_fields = array('Date');

		//初始化查询条件
		$whereStartDate = $StartDate?" UserRegTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" UserRegTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$wherePartner = $PartnerId?" PartnerId = ".$PartnerId." ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$wherePartner);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		$StatArr['TotalData'] = array('RegUser'=>0,'RegIp'=>0);
		do
		{
			$StatArr['RegDate'][$date] = array('RegUser'=>0,'RegIp'=>0);
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));
    	$DateStart = date("Ym",strtotime($StartDate));
	    $DateEnd = date("Ym",strtotime($EndDate));
	    $DateList = array();
	    $Date = $StartDate;
	    do{
	        $D = date("Ym",strtotime($Date));
	        $DateList[] = $D;
	        $Date = date("Y-m-d",strtotime("$Date +1 month"));
	    }
	    while($D!=$DateEnd);
	    foreach($DateList as $key => $value)
	    {
			$table_name = Base_Widget::getDbTable($this->table_reg_log)."_".$value;
			$sql = "SELECT  $fields FROM $table_name as log where 1 ".$where.$groups;
			
			$RegDateArr = $this->db->getAll($sql);
			if(is_array($RegDateArr))
			{
				foreach ($RegDateArr as $key => $Stat) 
				{
					//累加数据
					if(isset($StatArr['RegDate'][$Stat['Date']]))
					{
						$StatArr['RegDate'][$Stat['Date']]['RegIp'] += $Stat['RegIp'];
						$StatArr['RegDate'][$Stat['Date']]['RegUser'] += $Stat['RegUser'];
					}
					else
					{
						$StatArr['RegDate'][$Stat['Date']] = array('RegUser'=>0,'RegIp'=>0);
						$StatArr['RegDate'][$Stat['Date']]['RegIp'] += $Stat['RegIp'];
						$StatArr['RegDate'][$Stat['Date']]['RegUser'] += $Stat['RegUser'];
					}
					$StatArr['TotalData']['RegUser'] += $Stat['RegUser'];
				}
			}
	    }
		return $StatArr;
	}
 	public function getRegHour($Date,$PartnerId)
	{
		//查询列
		$select_fields = array(
		'RegUser'=>'count(distinct(UserId))',
		'RegIp'=>'count(distinct(UserRegIp))',
		'Hour'=>"from_unixtime(UserRegTime,'%H')");
		//分类统计列
		$group_fields = array('Hour');

		//初始化查询条件
		$whereStartTime = $Date?" UserRegTime >= '".strtotime($Date)."' ":"";
		$whereEndTime = $Date?" UserRegTime <= '".(strtotime($Date)+86400-1)."' ":"";
		$wherePartner = $PartnerId?" PartnerId = ".$PartnerId." ":"";

		$whereCondition = array($whereStartTime,$whereEndTime,$wherePartner);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		$StatArr['TotalData'] = array('RegUser'=>0,'RegIp'=>0);
		for($hour=0;$hour<=23;$hour++)
		{
			$StatArr['RegHour'][sprintf("%02d",$hour)] = array('RegUser'=>0,'RegIp'=>0);	
		}
		
		$table_name = Base_Widget::getDbTable($this->table_reg_log)."_".date("Ym",strtotime($Date));
		$sql = "SELECT  $fields FROM $table_name as log where 1 ".$where.$groups;
		$RegHourArr = $this->db->getAll($sql);
		if(is_array($RegHourArr))
		{
			foreach ($RegHourArr as $key => $Stat) 
			{
				//累加数据
				if(isset($StatArr['RegHour'][$Stat['Hour']]))
				{
					$StatArr['RegHour'][$Stat['Hour']]['RegIp'] += $Stat['RegIp'];
					$StatArr['RegHour'][$Stat['Hour']]['RegUser'] += $Stat['RegUser'];
				}
				else
				{
					$StatArr['RegHour'][$Stat['Hour']] = array('RegUser'=>0,'RegIp'=>0);
					$StatArr['RegHour'][$Stat['Hour']]['RegIp'] += $Stat['RegIp'];
					$StatArr['RegHour'][$Stat['Hour']]['RegUser'] += $Stat['RegUser'];
				}
				$StatArr['TotalData']['RegUser'] += $Stat['RegUser'];
			}
		}
		return $StatArr;
	}
	public function resetUserMail($UserId)
	{
		$UserCommunication = $this->GetUserCommunication($UserId);
		$UserMail = $UserCommunication['UserMail'];
		$AuthApplyTime = time();
		$position = Base_Common::getUserDataPositionByName($UserMail);
		$table_to_update = Base_Common::getUserTable($this->table_mail,$position);
		$update = $this->db->update($table_to_update,array("UserMailAuthApplyTime"=>$AuthApplyTime),'`UserId`=? and `UserMail`=? and `UserMailAuth`=0',array($UserId,$UserMail));
		if($update)
		{
			$this->sendAuthMail($UserId,$UserMail,$AuthApplyTime);
		}
		return $update;	
	}
	public function resetMail($UserId)
	{
		$this->db->begin();
		$UserCommunication = $this->GetUserCommunication($UserId);
		$UserMail = $UserCommunication['UserMail'];
		$updateMail = $this->updateUserCommunication($UserId,array('UserMail'=>''));
		
		$position = Base_Common::getUserDataPositionByName($UserMail);
		$table_to_delete = Base_Common::getUserTable($this->table_mail,$position);
		$deleteMail = $this->db->delete($table_to_delete,'`UserId`=? and `UserMail`=?',array($UserId,$UserMail));
		
		if($updateMail&&$deleteMail)
		{
			$this->db->commit();
			return true;
		}
		else
		{
			$this->db->rollback();
			return false; 	
		}
	}
	
	
	public function createResetPassword($DataArr)
	{
		$table_to_insert = Base_Widget::getDbTable($this->table_password_reset_log);
		return $this->db->insert($table_to_insert,$DataArr);
	}
	public function getLastResetPasswordTime($UserId)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_password_reset_log);
		$sql = "select max(AsignResetTime) as LastTime from $table_to_process where UserId = ?";
		return  $this->db->getOne($sql,$UserId,false);
	}
	public function getResetPassword($ResetId,$UserId,$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table_password_reset_log);
		$sql = "select $fields from $table_to_process where `ResetId` = ? and `UserId` = ?";
		return $this->db->getRow($sql,array($ResetId,$UserId),false);
	}
	public function updateResetPassword($ResetId,$UserId,$bind)
	{
		$table_to_update = Base_Widget::getDbTable($this->table_password_reset_log);
		return $this->db->update($table_to_update,$bind, '`ResetId` = ? and `UserId` = ?',array($ResetId,$UserId));
	}
	public function ResetUserPassword($ResetInfo,$ResetStatus,$UserPassWord,$comment)
	{
		$this->db->begin();
		if($ResetStatus==1)
		{
			$updatePassword = $this->updateUser($ResetInfo['UserId'],array('UserPassWord'=>md5($UserPassWord)));
		}
		elseif($ResetStatus==2)
		{
		 	$updatePassword = true;
		}

		if($updatePassword)
		{
			$updateBind = array('Comment'=>json_encode($comment),'ResetStatus'=>$ResetStatus,'ResetTime'=>time());
			$update = $this->updateResetPassword($ResetInfo['ResetId'],$ResetInfo['UserId'],$updateBind);
			if($update)
			{
				$this->db->commit();
				return true;
			}
			else
			{
				$this->db->rollBack();
				return false;
			} 			
		}
		else
		{
			$this->db->rollBack();
			return false;
		} 			
	}
 	public function sendUserResetPasswordMail($UserId,$UserMail)
 	{
 		$this->db->begin();
 		$UserInfo = $this->GetUserById($UserId);
 		$AsignResetTime = time();
 		$ResetPasswordLog = array('UserId'=>$UserId,'PartnerId'=>$UserInfo['PartnerId'],'AsignResetTime'=>$AsignResetTime,'ResetStatus'=>0,'ResetType'=>1);
 		$ResetLog = $this->createResetPassword($ResetPasswordLog);
 		if($ResetLog)
 		{
	 		$User = array('UserId'=>$UserId,'PartnerId'=>$UserInfo['PartnerId'],'EndTime'=>($AsignResetTime + 86400),'UserMail'=>$UserMail,'ResetId'=>$ResetLog);
	 		$User['sign'] = base_common::check_sign($User,"resetpassword");
	 			 		
	 		$ResetUrl = $this->config->passporturl.(Base_Common::getUrl('','','',$User))."&c=forgot_password&m=reset_by_email";
 			$UrlList = array('ResetUrl'=>array('href'=>$ResetUrl,'txt'=>$ResetUrl));
 			$MailContent = array('UserMail'=>$UserMail,'UserName'=>$UserInfo['UserName'],'UrlList'=>$UrlList,'title'=>"用户密码找回");
 			$MailType = "ResetPassWord";
 			$oMail = new Lm_Mail();
 			$mail = $oMail->createMail($MailType,$UserId,$MailContent);
 		
 		
	 		if($mail)
			{
				$this->db->commit();
				return $ResetLog;			
			}
			else
			{
				$this->db->rollBack();
				return false;
			}
		}
		else
		{
			$this->db->rollBack();
			return false;
		} 
 	}
	public function insertAnswer(array $bind)
	{
		$position = Base_Common::getUserDataPositionById($bind['UserId']);
		$table_to_insert = Base_Common::getUserTable($this->table_answer,$position);		
		return $this->db->insert($table_to_insert, $bind);
	}
	public function deleteAnswer($UserId)
	{
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_to_delete = Base_Common::getUserTable($this->table_answer,$position);		
		return $this->db->delete($table_to_delete, '`UserId`= ?',$UserId);
	}
	public function resetPassWord($UserId)
	{
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_to_update = Base_Common::getUserTable($this->table,$position);		
		$bindArray = array('UserPassWord'=>md5('limaogame123'));
		return $this->db->update($table_to_update,$bindArray, '`UserId`= ?',$Id = substr($UserId,0,-3));
	}
	public function getUserQuestionAnswer($UserId,$QuestionId, $fields = '*')
	{
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_to_process = Base_Common::getUserTable($this->table_answer,$position);	
		$sql = "select $fields from $table_to_process where `UserId` = ? and `QuestionId` = ?";
		$param = array($UserId,$QuestionId);
		return $this->db->getRow($sql,$param);
	}
	public function getUserAnswer($UserId,$fields = '*')
	{
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_to_process = Base_Common::getUserTable($this->table_answer,$position);	
		$sql = "select $fields from $table_to_process where `UserId` = ?";
		return $this->db->getAll($sql,$UserId);
	}
 	public function getRegDetail($StartDate,$EndDate,$PartnerId,$start,$pagesize)
	{
		$RegCount = $this->getRegDetailCount($StartDate,$EndDate,$PartnerId);
		if($RegCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartDate = $StartDate?" UserRegTime >= ".strtotime($StartDate)." ":"";
			$whereEndDate = $EndDate?" UserRegTime <= ".(strtotime($EndDate)+86400-1)." ":"";
			$wherePartner = $PartnerId?" PartnerId = ".$PartnerId." ":"";
	
			$whereCondition = array($whereStartDate,$whereEndDate,$wherePartner);
			
			$order = " order by UserRegTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);

			$Date = date("Ym",strtotime($StartDate));			
			$table_to_process = Base_Widget::getDbTable($this->table_reg_log)."_".$Date;     	
	    
	    $StatArr = array('RegDetail'=>array());
	
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$RegDetailArr = $this->db->getAll($sql,false);
			if(isset($RegDetailArr))
	    {
	      foreach ($RegDetailArr as $key => $value) 
				{
					$StatArr['RegDetail'][$value['UserId']] = $value;
				}
	    }
  	}
  	
	 	$StatArr['RegCount'] = $RegCount; 
		return $StatArr;
	}
 	public function getRegDetailCount($StartDate,$EndDate,$PartnerId)
	{
		//查询列
		$select_fields = array('RegCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartDate = $StartDate?" UserRegTime >= ".strtotime($StartDate)." ":"";
		$whereEndDate = $EndDate?" UserRegTime <= ".(strtotime($EndDate)+86400-1)." ":"";
		$wherePartner = $PartnerId?" PartnerId = ".$PartnerId." ":"";

		$whereCondition = array($whereStartDate,$whereEndDate,$wherePartner);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

		$Date = date("Ym",strtotime($StartDate));			
		$table_to_process = Base_Widget::getDbTable($this->table_reg_log)."_".$Date; 
			 
    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$RegCount = $this->db->getOne($sql,false);
		if($RegCount)
    {
			return $RegCount;    
		}
		else
		{
			return 0; 	
		}
	}
 	public function getRegDayBySource($StartDate,$EndDate,$SourceProjectId,$SourceList,$SourceDetail)
	{
		//查询列
		$select_fields = array(
		'RegUser'=>'count(distinct(UserId))',
		'RegIp'=>'count(distinct(UserRegIp))',
		'Date'=>"from_unixtime(UserRegTime,'%Y-%m-%d')",
		'UserSourceId','UserSourceDetail','UserSourceProjectId');
		//分类统计列
		$group_fields = array('Date','UserSourceId','UserSourceDetail','UserSourceProjectId');

		//初始化查询条件
		$whereStartDate = $StartDate?" UserRegTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" UserRegTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
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


		$whereCondition = array($whereStartDate,$whereEndDate,$whereSource,$WhereSourceDetail,$WhereSourceProject);

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$date = $StartDate;
		//初始化结果数组
		$StatArr['TotalData'] = array('RegUser'=>0,'RegIp'=>0);
		do
		{
			$StatArr['RegDate'][$date] = array();
			$date = date("Y-m-d",(strtotime($date)+86400));
		}
		while(strtotime($date) <= strtotime($EndDate));
    $DateStart = date("Ym",strtotime($StartDate));
    $DateEnd = date("Ym",strtotime($EndDate));
    $DateList = array();
    $Date = $StartDate;
    do{
        $D = date("Ym",strtotime($Date));
        $DateList[] = $D;
        $Date = date("Y-m-d",strtotime("$Date +1 month"));
    }
    while($D!=$DateEnd);
    foreach($DateList as $key => $value)
    {
      $table_name = Base_Widget::getDbTable($this->table_reg_log)."_".$value;
      $sql = "SELECT  $fields FROM $table_name as log where PartnerId = 1 ".$where.$groups;

			$RegDateArr = $this->db->getAll($sql,false);
			if(is_array($RegDateArr))
      {
        foreach ($RegDateArr as $key => $Stat) 
				{
  				//累加数据
  				if(isset($StatArr['RegDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]))
  				{
  					$StatArr['RegDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['RegIp'] += $Stat['RegIp'];
  					$StatArr['RegDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['RegUser'] += $Stat['RegUser'];
  				}
  				else
  				{
  					$StatArr['RegDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']] = array('RegUser'=>0,'RegIp'=>0);
  					$StatArr['RegDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['RegIp'] += $Stat['RegIp'];
  					$StatArr['RegDate'][$Stat['Date']][$Stat['UserSourceProjectId']][$Stat['UserSourceId']][$Stat['UserSourceDetail']]['RegUser'] += $Stat['RegUser'];
  				}
  				$StatArr['TotalData']['RegUser'] += $Stat['RegUser'];
				}
      }
    }
		return $StatArr;
	}
 	public function getAuthMailbyMail()
	{
		//查询列
		$select_fields = array(
		'UserMail','UserMailAuthTime');

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		$StatArr = array('UserAuth'=>array(),'TotalData'=>array('UserCount'=>0,'AuthUserCount'=>0));
	    for($i = 0;$i<=15;$i++)
	    {
	    	for($j=0;$j<=15;$j++)
	    	{
		    	$position = array('db_fix'=>dechex($i),'tb_fix'=>dechex($j));
				$table_name = Base_Common::getUserTable($this->table_mail,$position);
				$sql = "SELECT  $fields FROM $table_name as log ";
	
				$AuthMailArr = $this->db->getAll($sql,false);
				if(is_array($AuthMailArr))
				{
					foreach ($AuthMailArr as $key => $Stat) 
					{
						$t = explode("@",$Stat['UserMail']);
						$MailFix = "@".$t['1'];
						
						//累加数据
						if(isset($StatArr['UserAuth'][$MailFix]))
						{
							$StatArr['UserAuth'][$MailFix]['UserCount'] ++;
							if($Stat['UserMailAuthTime']>0)
							{
								$StatArr['UserAuth'][$MailFix]['AuthUserCount'] ++;
							}
						}
						else
						{
							$StatArr['UserAuth'][$MailFix] = array('UserCount'=>0,'AuthUserCount'=>0);
							$StatArr['UserAuth'][$MailFix]['UserCount'] ++;
							if($Stat['UserMailAuthTime']>0)
							{
								$StatArr['UserAuth'][$MailFix]['AuthUserCount'] ++;
							}
						}
						$StatArr['TotalData']['UserCount']++;
						if($Stat['UserMailAuthTime']>0)
						{
							$StatArr['TotalData']['AuthUserCount']++;
						}
					}
				}    			
	    	}	
	    }
		return $StatArr;
	}
 	public function getUserBySex()
	{
		//查询列
		$select_fields = array('UserSex','UserCount'=>'count(*)');
		
		$group_fields = array('UserSex');
		

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		

		$StatArr = array('UserSex'=>array('1'=>array('name'=>'男','UserCount'=>0),'2'=>array('name'=>'女','UserCount'=>0),'0'=>array('name'=>'未指定','UserCount'=>0)),'TotalData'=>array('UserCount'=>0));
	    for($i = 0;$i<=15;$i++)
	    {
	    	for($j=0;$j<=15;$j++)
	    	{
		    	$position = array('db_fix'=>dechex($i),'tb_fix'=>dechex($j));
				$table_name = Base_Common::getUserTable($this->table_communication,$position);
				$sql = "SELECT  $fields FROM $table_name as log where 1 ".$groups;
				$UserSexArr = $this->db->getAll($sql,false);
				if(is_array($UserSexArr))
				{
					foreach ($UserSexArr as $key => $Stat) 
					{						
						//累加数据
						$StatArr['UserSex'][$Stat['UserSex']]['UserCount'] += $Stat['UserCount'];											
						$StatArr['TotalData']['UserCount'] += $Stat['UserCount'];						
					}
				}    			
	    	}	
	    }
		return $StatArr;
	}
 	public function getUserByAge()
	{
		//查询列
		$select_fields = array('UserAge'=>'floor(datediff(now(),UserBirthDay)/365)','UserCount'=>'count(*)');
		
		$group_fields = array('UserAge');
		

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		

		$StatArr = array('UserAge'=>array(),'UnDefined'=>0);
	    for($i = 0;$i<=15;$i++)
	    {
	    	for($j=0;$j<=15;$j++)
	    	{
		    	$position = array('db_fix'=>dechex($i),'tb_fix'=>dechex($j));
				$table_name = Base_Common::getUserTable($this->table_communication,$position);
				$sql = "SELECT  $fields FROM $table_name as log where UserIdCard !='' ".$groups;
				$UserSexArr = $this->db->getAll($sql,false);
				if(is_array($UserSexArr))
				{
					foreach ($UserSexArr as $key => $Stat) 
					{						
						if(isset($StatArr['UserAge'][$Stat['UserAge']]))
						{
							//累加数据
							$StatArr['UserAge'][$Stat['UserAge']]['UserCount'] += $Stat['UserCount'];	
						}
						else
						{
							$StatArr['UserAge'][$Stat['UserAge']] = array('UserCount'=>0);	
							$StatArr['UserAge'][$Stat['UserAge']]['UserCount'] += $Stat['UserCount'];	
						}										
						$StatArr['TotalData']['UserCount'] += $Stat['UserCount'];						
					}
				}
				$sql = "SELECT count(*) as UserCount FROM $table_name as log where UserIdCard =''";
				$UserCount = $this->db->getOne($sql,false);
				$StatArr['UnDefined'] += $UserCount;
				$StatArr['TotalData']['UserCount'] += $UserCount;						
	    	}	
	    }
		return $StatArr;
	}
	public function InsertCharacterFreeze($DataArr)
	{
		$this->db->begin();
		$position = Base_Common::getUserDataPositionById($DataArr['UserId']);
		$table_user = Base_Common::getUserTable($this->character_freeze,$position);
		
		$user = $this->db->insert($table_user,$DataArr);
		$Date = date("Ym",$DataArr['StartTime']);
		$table_date = $this->CreateCharacterFreezeLog($Date);
		$log = $this->db->insert($table_date,$DataArr);
		if($user&&$log)
		{
			$this->db->commit();
			return true;
		}
		else
		{
			$this->db->rollBack();
			return false;
		}			
	}
	public function GetCharacterFreezeInfo($UserId,$ServerId,$StartTime,$fields='*')
	{
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_user = Base_Common::getUserTable($this->character_freeze,$position);
		$sql = "SELECT $fields FROM " . $table_user . " where UserId = ? and ServerId = ? and StartTime = ?";
		return $this->db->getRow($sql,array($UserId,$ServerId,$StartTime));							
	}
	public function updateCharacterFreezeInfo($UserId,$ServerId,$StartTime,$manager,$bind)
	{
		$FreezeInfo = $this->GetCharacterFreezeInfo($UserId,$ServerId,$StartTime);
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_user = Base_Common::getUserTable($this->character_freeze,$position);
		$Comment = json_decode($FreezeInfo['Comment'],true);
		if(!is_array($Comment['update_log']))
		{
			$Comment['update_log'] = array();	
		}
		$Comment['update_log'][count($Comment['update_log'])+1] = array(
		'before'=>array('StartTime'=>$FreezeInfo['StartTime'],'EndTime'=>$FreezeInfo['EndTime'],'FreezeReason'=>$Comment['FreezeReason']),
		'after' => array('StartTime'=>$bind['StartTime'],'EndTime'=>$bind['EndTime'],'FreezeReason'=>$bind['FreezeReason'],'manager'=>$manager,'update_time'=>time()));
		$Comment['FreezeReason'] = $bind['FreezeReason'];

		$Comment['manager'] = $manager;
		$bind['Comment'] = json_encode($Comment);
		unset($bind['FreezeReason']);
		return $this->db->update($table_user, $bind ,'`UserId` = ? and `ServerId` = ? and `StartTime` = ?',array($UserId,$ServerId,$StartTime));						
	}
  	public function CreateCharacterFreezeLog($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->character_freeze_log);
		$table_to_process = Base_Widget::getDbTable($this->character_freeze_log)."_".$Date;
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
			$sql = str_replace('`' . $this->character_freeze_log . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
 	public function getCharacterFreezeDetail($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$start,$pagesize)
	{
		$FreezeCount = $this->getCharacterFreezeDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission);
		if($FreezeCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartTime = $StartTime?" StartTime >= ".strtotime($StartTime)." ":"";
			$whereEndTime = $EndTime?" StartTime <= ".strtotime($EndTime)." ":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	
			$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereServer,$oWherePartnerPermission);
			
			$order = " order by StartTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
		    if($UserId)
		    {
				$position = Base_Common::getUserDataPositionById($UserId);
				$table_to_process = Base_Common::getUserTable($this->character_freeze,$position);	
		    }
		    else
		    {
				$Date = date("Ym",strtotime($StartTime));			
				$table_to_process = Base_Widget::getDbTable($this->character_freeze_log)."_".$Date;   	
		    }
		    $StatArr = array('FreezeDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$FreezeDetailArr = $this->db->getAll($sql,false);
			if(isset($FreezeDetailArr))
		    {
		    	foreach ($FreezeDetailArr as $key => $value) 
				{
					$StatArr['FreezeDetail'][] = $value;
				}
		    }
  		}
	 	$StatArr['FreezeCount'] = $FreezeCount; 
		return $StatArr;
	}
 	public function getCharacterFreezeDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('FreezeCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartTime = $StartTime?" StartTime >= ".strtotime($StartTime)." ":"";
		$whereEndTime = $EndTime?" StartTime <= ".strtotime($EndTime)." ":"";
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
			$table_to_process = Base_Common::getUserTable($this->character_freeze,$position);	
	    }
	    else
	    {
			$Date = date("Ym",strtotime($StartTime));			
			$table_to_process = Base_Widget::getDbTable($this->character_freeze_log)."_".$Date;   	
	    }
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$FreezeCount = $this->db->getOne($sql,false);
		if($FreezeCount)
    	{
			return $FreezeCount;    
		}
		else
		{
			return 0; 	
		}
	}
 	public function getCharacterFreeze($UserId,$ServerId)
	{	
		//查询列
		$select_fields = array('FreezeCount'=>'count(*)','MaxTime'=>'max(EndTime)');
		//分类统计列
		$time = time();
		//初始化查询条件
		$whereStartTime = " StartTime <= ".$time." ";
		$whereEndTime = " EndTime >= ".$time." ";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereServer);
					
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
	    if($UserId)
	    {
			$position = Base_Common::getUserDataPositionById($UserId);
			$table_to_process = Base_Common::getUserTable($this->character_freeze,$position);	
	    }
	
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$Freeze = $this->db->getRow($sql,false);
		if($Freeze['FreezeCount'])
    	{
			return $Freeze;    
		}
		else
		{
			return array('FreezeCount'=>0); 	
		}
  	}
  	public function InsertCharacterKickOff($KickOffArr)
	{
		$this->db->begin();
		$position = Base_Common::getUserDataPositionById($KickOffArr['UserId']);
		$table_user = Base_Common::getUserTable($this->character_kick_off,$position);
		
		$user = $this->db->insert($table_user,$KickOffArr);
		$Date = date("Ym",$KickOffArr['KickOffTime']);
		$table_date = $this->CreateCharacterKickOffLog($Date);
		$oSocketType = (@include(__APP_ROOT_DIR__."/etc/SocketType.php"));
		$oSocketQueue = new Config_SocketQueue();
		
		$uType=60221;
		$TypeInfo = $oSocketType[$uType];
		if($TypeInfo['Type'])
		{
			$DataArr = array('PackFormat'=>$TypeInfo['PackFormat'],
			'Length' => $TypeInfo['Length'],
            'Length2' => 0,
			'uType'=>$uType,
			'MsgLevel'=>0,
			'Line'=>0,
			'UserID'=>$KickOffArr['UserId'],
			'KickOffReason'=>$KickOffArr['KickOffReason'],
			'Serial'=>$KickOffArr['KickOffId']);	
		}
		$DataArr = array('ServerId'=>$KickOffArr['ServerId'],'uType'=>$uType,'UserId'=>$DataArr['UserID'],'MessegeContent'=>serialize($DataArr),'QueueTime'=>time(),'SendTime'=>0);
		$addQueue = $oSocketQueue->insert($DataArr);			
		
		$log = $this->db->insert($table_date,$KickOffArr);
		if($user&&$log&&$addQueue)
		{
			$this->db->commit();
			return true;
		}
		else
		{
			$this->db->rollBack();
			return false;
		}			
	}
  	public function CreateCharacterKickOffLog($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->character_kick_off_log);
		$table_to_process = Base_Widget::getDbTable($this->character_kick_off_log)."_".$Date;
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
			$sql = str_replace('`' . $this->character_kick_off_log. '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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

}

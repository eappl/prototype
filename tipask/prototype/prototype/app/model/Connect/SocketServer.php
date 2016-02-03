<?php
/**
 * Socket服务端函数
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Connect_SocketServer extends Base_Widget
{
	//声明所用到的表

	public function SocketLogin($buff)
	{
		$oUser = new Lm_User();
		$oLogin = new Lm_Login();
		
		$format="V2Length/vuType/C2uMsgLevel/VcharID/A33usr/A33Pwd/Vltime/Vzoneid/Vip";
		$unpackArr = @unpack($format,$buff);
		$User['UserName'] = trim($unpackArr['usr']);
		$User['LoginTime'] = trim($unpackArr['ltime']);
		$User['UserPassWord'] = (trim($unpackArr['Pwd']));
		$User['ServerId'] = intval($unpackArr['zoneid']);
		$User['UserLoginIp'] = trim($unpackArr['ip']);
		//验证用户名有效性
		if($User['UserName'])
		{
			if($User['ServerId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['LoginTime']-time())<=600)
				{
		 			//查询用户
					$UserInfo = $oUser->GetUserByName($User['UserName']);
					unset($User['UserName']);
					$User['UserId'] = $UserInfo['UserId'];
					$result = $oLogin->UserLogin($User,$UserInfo);
				}
				else
				{
					$result = array('return'=>0,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>2,'comment'=>"请选择服务器");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"请输入用户名");	
		}
		//pack header
		$resArr['uLength'] = 85;
		$resArr['uID'] = $unpackArr['Length2'];
		$resArr['uType'] = 60202;
		$resArr['uMsgLevel'] = $unpackArr['uMsgLevel1'];
		$resArr['uLine'] = $unpackArr['uMsgLevel2'];
		//pack body
		$resArr['m_ucResultID'] = $result['return'];
		$resArr['m_uiChallID'] = $unpackArr['charID'];
		$resArr['m_uiLoginID'] = $result['LoginId'];
		$resArr['m_uiUserID'] = $result['UserId'];
		$resArr['m_ucAdult'] = $result['adult'];
		$resArr['m_szComment'] = iconv("UTF-8","GB2312",$result['comment'])."\0";
		
		//重新封包	
		$format = "V2vC2CV2Ca13A50";
		$resMsg = pack($format,
		$resArr['uLength'],
		$resArr['uID'],
		$resArr['uType'],
		$resArr['uMsgLevel'],
		$resArr['uLine'],
		$resArr['m_ucResultID'],
		$resArr['m_uiChallID'],
		$resArr['m_uiUserID'],
		$resArr['m_ucAdult'],
		$resArr['m_uiLoginID'],
		$resArr['m_szComment']);
		return $resMsg;	
	}
	public function SocketLogout($buff)
	{
		$oUser = new Lm_User();
		$oLogin = new Lm_Login();
		
		$format="V2Length/vuType/C2uMsgLevel/V3/A13loginid";
		$unpackArr = @unpack($format,$buff);
		$User['UserId']= trim($unpackArr[1]);
		$User['LogoutTime'] = trim($unpackArr[2]);
		$User['LoginId'] = intval($unpackArr['loginid']);
		$User['ServerId'] = $unpackArr[3];
		//验证用户有效性
		if($User['UserId'])
		{
			if($User['ServerId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['LogoutTime']-time())<=600)
				{
		 			//查询用户
					$UserInfo = $oUser->GetUserById($User['UserId']);
					$result = $oLogin->UserLogout($User,$UserInfo);
				}
				else
				{
					$result = array('return'=>0,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>2,'comment'=>"请选择服务器");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"请输入用户ID");	
		}
	}
	public function SocketGetServerInfo($buff)
	{
		$oUser = new Lm_User();
		$oLogin = new Lm_Login();
		$oServer = new Config_Server();
		
		$arrIP = "";
		$format = "V1uLength/V1uID/v1Type/C1MsgLevel/C1Line/C*";
		$unpackArr = @unpack($format,$buff);
		foreach($unpackArr as $key=>$value){
			if(is_int($key)){
				$arrIP .= chr($value);
			}
		}
		if($arrIP)
		{
			$ServerInfo = $oServer->getByIp(trim($arrIP));
			if($ServerInfo['ServerId'])
			{
				$result = array('return'=>1,'ServerInfo'=>$ServerInfo,'comment'=>'找到服务器');	
			}
			else
			{
				$result = array('return'=>2,'comment'=>'你所查询的IP不属于任何服务器');						 	
			}
				
		}
		else
		{
			$result = array('return'=>0,'comment'=>"请输入服务器IP");					 	
		}

		$tmpArr = explode('|',$allMsg);
		
		$resArr['uLength'] = 17;
		$resArr['uID'] = $unpackArr['uID'];
		$resArr['uType'] = 60207;
		$resArr['uMsgLevel'] = $unpackArr['MsgLevel'];
		$resArr['uLine'] = $unpackArr['Line'];
		
		$resArr['m_ucResultID'] = $result['return'];
		$resArr['m_uiZoneID'] = $result['ServerInfo']['ServerId'];
		
		//重新封包	
		$format = "V2vC3V";		
		$resMsg = pack($format,
		$resArr['uLength'],
		$resArr['uID'],
		$resArr['uType'],
		$resArr['uMsgLevel'],
		$resArr['uLine'],
		$resArr['m_ucResultID'],
		$resArr['m_uiZoneID']);
		return $resMsg;	
	}
	public function SocketCreateCharacter($buff)
	{
		$oCharacter = new Lm_Character();
		$oUser = new Lm_User();
		$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
		$format = "V1uLength/V1uID/v1Type/C1MsgLevel/C1Line/V1UserID/V1ZoneID/C*";
		$unpackArr = @unpack($format,$buff);
		$arrName = "";
		foreach($unpackArr as $key=>$value){
			if(is_int($key)){
				$arrName .= chr($value);
			}
		}		
		$Character['UserId'] = $unpackArr['UserID'];
		$Character['CharacterName'] = str_replace("\0","",iconv('GBK','UTF-8//IGNORE',$arrName));
		$Character['ServerId'] = $unpackArr['ZoneID'];
		$Character['CharacterLevel'] = 1;
		$Character['CharacterCreateTime'] = time();
		if($Character['UserId'])
		{
			//验证用户名有效性
			if((strlen($Character['CharacterName'])>=4)&&(strlen($Character['CharacterName'])<=20))
			{
			 	//查询用户
				$UserInfo = $oUser->GetUserById($Character['UserId']);
				if($UserInfo['UserId'])
				{
					//判断用户所选服务器大区是否存在
					$ServerInfo = $ServerList[$Character['ServerId']];
					if($ServerInfo['ServerId'])
					{
			 			$Character['AppId'] = $ServerInfo['AppId'];
			 			$Character['PartnerId'] = $ServerInfo['PartnerId'];
						$CharacterInfo = $oCharacter->getCharacterInfoByUser($Character['UserId'],$Character['ServerId']);
			 			if(count($CharacterInfo)==0)
			 			{
				 			$AddLog = $oCharacter->CreateCharacter($Character);
				 			if($AddLog)
				 			{
				 				$result = array('return'=>1,'comment'=>"角色创建成功");
				 			}
				 			else
				 			{
				 			 	$result = array('return'=>0,'comment'=>"角色创建失败");
				 			}
			 			}
			 			else
			 			{
			 			 	$result = array('return'=>0,'comment'=>"您已经在此服务器有角色，无法重复创建");
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
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请输入合法的用户名");	
			}
		}
		else
		{
		 	$result = array('return'=>0,'comment'=>"请选择用户");	
		}
		return $result;
	}
}

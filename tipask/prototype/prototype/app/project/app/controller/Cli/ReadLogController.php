<?php
class Cli_ReadLogController extends Base_Controller_Action{	
	public function init()
	{
		parent::init();
        $this->oCron = new Lm_Cron();
	}
    
    //判断是否外网正式环境
    private function getEnvironment()
    {
        if(isset($_SERVER['SERVER_NAME']) && strstr($_SERVER['SERVER_NAME'],"limaogame.com")){
            return true;
        }elseif(isset($_SERVER["SSH_CONNECTION"]) && strstr($_SERVER["SSH_CONNECTION"],"183.136.134.82")){
            return true;
        }elseif(isset($_SERVER['HOSTNAME']) && $_SERVER['HOSTNAME'] == "lnbudb01"){
            return true;
        }else{
            return false;
        }
    }
	
	function getLogAction()
	{
        $ServerId = $this->request->ServerId;
		$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
		$ServerInfo = $ServerList[$ServerId];
		$filename = "formatlog";
		$Date = $this->request->Date?trim($this->request->Date):date("Y-m-d",time()-3600);
		$Hour = intval($this->request->Hour)!=-1?intval($this->request->Hour):date("H",time()-3600);
        echo (date("Y-m-d H:i:s",time()))."\n";
        echo $Date."-".$Hour;       
  //      if($this->getEnvironment())
  //      {                    
            $ServerRoot = $ServerInfo['AppId']."/".$ServerInfo['PartnerId']."/".$ServerInfo['ServerId'];
            $logurl = '/gamelog';
            $dir = $logurl."/".$ServerRoot."/".$Date."/";
//        }
//        else
//        {
//            $logurl = "d:\\wamp\\www\\web_usercenter\\log";
//            $ServerRoot = $ServerInfo['AppId']."\\".$ServerInfo['PartnerId']."\\".$ServerInfo['ServerId'];
//            $dir = $logurl."\\".$ServerRoot."\\".$Date."\\";
//        }
        
		echo $dir."\n";
		$fileArr = $this->get_file($dir,$filename,"log");	                
		ksort($fileArr);
        print_R($fileArr);
		foreach($fileArr as $key => $FileName)
		{
			echo $FileName."\n";
			$t = explode(".",$FileName);
			$t2 = explode("-",$t[0]);
			if($t2[1]==sprintf("%02d",$Hour))
			{
			    $FileName = $dir.$FileName;
                echo "FileName:".$FileName."\n";	
				
				$LastUpdate = $this->readLine($FileName,$ServerInfo);
			}
		}

	}
	function getCharacterCreateLogAction()
	{
        $ServerId = $this->request->ServerId;
		$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
		$ServerInfo = $ServerList[$ServerId];
		$filename = "pvelog";
		$Date = $this->request->Date?trim($this->request->Date):date("Y-m-d",time()-3600);
		$Hour = intval($this->request->Hour)!=-1?intval($this->request->Hour):date("H",time()-3600);
        echo (date("Y-m-d H:i:s",time()))."\n";
        echo $Date."-".$Hour;       
  //      if($this->getEnvironment())
  //      {                    
            $ServerRoot = $ServerInfo['AppId']."/".$ServerInfo['PartnerId']."/".$ServerInfo['ServerId'];
            $logurl = '/gamelog';
            $dir = $logurl."/".$ServerRoot."/".$Date."/";
//        }
//        else
//        {
//            $logurl = "d:\\wamp\\www\\web_usercenter\\log";
//            $ServerRoot = $ServerInfo['AppId']."\\".$ServerInfo['PartnerId']."\\".$ServerInfo['ServerId'];
//            $dir = $logurl."\\".$ServerRoot."\\".$Date."\\";
//        }
        
		echo $dir."\n";
		$fileArr = $this->get_file($dir,$filename,"log");	                
		ksort($fileArr);
        print_R($fileArr);
		foreach($fileArr as $key => $FileName)
		{
			echo $FileName."\n";
			$t = explode(".",$FileName);
			$t2 = explode("-",$t[0]);
			if($t2[1]==sprintf("%02d",$Hour))
			{
			    $FileName = $dir.$FileName;
                echo "FileName:".$FileName."\n";	
				
				$LastUpdate = $this->readLine($FileName,$ServerInfo);
			}
		}

	}
	
	function get_file($dir,$prefix,$subfix)
	{
		$array = array();
		if (is_dir($dir)){
			if ($dh = opendir($dir)){
				while (($file = readdir($dh)) !== false){
					$p = substr($file,0,strlen($prefix));
					$s = substr($file,-1*strlen($subfix));
					if(($p==$prefix)&&($s==$subfix)){
						$array[] = $file;
					}
				}
				closedir($dh);
			}
		}
		return $array;
	}

    function readLine($file,$ServerInfo)
    {
        if(file_exists($file))
        {
            $fd = fopen($file, "r");
                        
            while ($buffer = fgets($fd)) 
            {                              
    			$data = explode(":",substr($buffer,20,strlen($buffer)));
                
                if(strstr($buffer,"addcash")){
                    $function = explode(";",$data[0]);
                }else{
                    if(strstr($buffer,"pickitem")){
                        $function = explode(";",$data[1]);
                    }else{
                        $function = explode(";",$data[2]);
                    }                    
                }
                
    			$log = array();
                
				$log['time'] = substr($buffer,0,19);
				$log['type'] = $function[0];
				$log['function'] = $function[1];
				$log['text'] = $buffer;
				unset($function[0],$function[1]);
				foreach($function as $k=>$v)
				{
					$newdata = explode("=",$v);
					$log['data'][$newdata[0]] = $newdata[1];
				}
                
    			$result = array();
                
//                if($log['function'] != 'logout')
//                {
                    //continue;
//                    print_R($log['data']);
//                }
                
                //echo "\n".$log['function'];                
                switch ($log['function'])
                {    				
                    //登陆
                    case "login":
	                    $oUser = new Lm_User();
	                    $oLogin = new Lm_Login();
	                    $UserInfo = $oUser->GetUserById($log['data']['AccountID']);
	                    if($UserInfo['UserId'])
	                    {
				 			$FirstLogin = $oLogin->getFirstLogin($UserInfo['UserId'],$ServerInfo['AppId'],$ServerInfo['PartnerId'],$ServerInfo['ServerId']);	
	                        $DataArr = array(
	                        'UserId'=>$log['data']['AccountID'],
	                        'UserLoginIP'=>$log['data']['IP'],                        
	                        'LoginTime'=>strtotime($log['time']),
	                        'AppId'=>$ServerInfo['AppId'],
	                        'PartnerId'=>$ServerInfo['PartnerId'],
	                        'ServerId'=>$ServerInfo['ServerId'],
							'UserSourceId'=>$UserInfo['UserSourceId'],
							'UserSourceDetail'=>$UserInfo['UserSourceDetail'],
							'UserSourceActionId'=>$UserInfo['UserSourceActionId'],
							'UserSourceProjectId'=>$UserInfo['UserSourceProjectId'],
							'UserRegTime'=>$UserInfo['UserRegTime'],
							'FirstLoginTime'=>$FirstLogin?$FirstLogin:strtotime($log['time'])
	                        );
	                        $insertLog = $oLogin->InsertLoginLog($DataArr,$UserInfo['UserName']);
	                    }
	                    else
	                    {
	                    	break; 	
	                    }
                        break;
                    //创建角色
                    case "newname":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'CharacterLevel'=>1,
                        'CharacterName'=>iconv('GBK','UTF-8//IGNORE',$log['data']['Name']),                        
                        'CharacterCreateTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                        $oCharacter = new Lm_Character();
                        $insertLog = $oCharacter->CreateCharacter($DataArr);
                        break;
                    //获得永久英雄
                    case "addhero":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'CharacterLevel'=>$log['data']['Level'],
                        'HeroId'=>$log['data']['HeroID'],
                        'AddReason'=>$log['data']['Reason'],
                        'HeroNum'=>$log['data']['HeroNum'],
                        'TimeLimit'=>0,
                        
                        'HeroAddTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                        $oHerolog = new Lm_Hero();
                        //$insertLog = $oHerolog->InsertHeroAddLog($DataArr);
                        break;
					
                    //获得时效英雄
                    case "addtimehero":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'CharacterLevel'=>$log['data']['Level'],
                        'HeroId'=>$log['data']['HeroID'],
                        'AddReason'=>$log['data']['Reason'],
                        'HeroNum'=>$log['data']['HeroNum'],
                        'TimeLimit'=>$log['data']['validtime'],
                        
                        'HeroAddTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                        $oHerolog = new Lm_Hero();
                        //$insertLog = $oHerolog->InsertHeroAddLog($DataArr);
                        break;
					
                    //切换英雄
                    case "changehero":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'CharacterLevel'=>$log['data']['Level'],
                        'CurHeroId'=>$log['data']['CurHeroID'],
                        'NewHeroId'=>$log['data']['NewHeroID'],
                        
                        'HeroChangeTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                        $oHerolog = new Lm_Hero();
                        //$insertLog = $oHerolog->InsertHeroChangeLog($DataArr);
                        break;
				
                    //接取任务
                    case "accepttask":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'CharacterLevel'=>$log['data']['Level'],
                        'TaskId'=>$log['data']['TaskID'],
                        'TaskType'=>$log['data']['TaskType'],
                        
                        'HeroAcceptTaskTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                        $oTask = new Lm_Task();
                        $insertLog = $oTask->InsertCharacterAcceptTaskLog($DataArr);
                        break;
				
                    //完成任务
                    case "taskcomplete":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'TaskId'=>$log['data']['TaskID'],
                        'TaskType'=>$log['data']['TaskType'],
                        
                        'HeroTaskCompleteTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                        $oTask = new Lm_Task();
                        $insertLog = $oTask->InsertCharacterTaskCompleteLog($DataArr);
                        break;
				
                    //商城购买
                    case "shopitem":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'CharacterLevel'=>$log['data']['Level'],
                        'ItemId'=>$log['data']['ItemID'],
                        'ItemNum'=>$log['data']['ItemNum'],
                        'AppCoin'=>$log['data']['Gold'],
                        'AppCoinLast'=>$log['data']['LastGold'],
                        'ItemPrice'=>intval(($log['data']['Gold'])/$log['data']['ItemNum']),
                        
                        'PurchaseTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                        $oPurchase = new Lm_Purchase();
                        $insertLog = $oPurchase->InsertPurchaseLog($DataArr);
                        
                        $replaceArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        'LastMoney'=>$log['data']['LastGold'],
                        'MoneyType'=>1,
                        );
                        
                        $replace = $oPurchase->ReplaceUserLastmoney($replaceArr);                        
                        $insertLog = $insertLog*$replace;
                        break;
                    
                    //npc购买
                    case "npcitem":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'CharacterLevel'=>$log['data']['Level'],
                        'NpcID'=>$log['data']['NpcID'],
                        'ItemId'=>$log['data']['ItemID'],
                        'ItemNum'=>$log['data']['ItemNum'],
                        'Money'=>$log['data']['Money'],
                        'MoneyType'=>$log['data']['MoneyType'],
                        'LastMoney'=>$log['data']['LastMoney'],    						
                        'NpcPurchaseTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                        $oPurchase = new Lm_Purchase();                        
                        
                        $insertLog = $oPurchase->InsertNpcPurchaseLog($DataArr);
                        
                        $replaceArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        'LastMoney'=>$log['data']['LastMoney'],
                        'MoneyType'=>$log['data']['MoneyType'],
                        );
                        
                        $replace = $oPurchase->ReplaceUserLastmoney($replaceArr);
                        $insertLog = $insertLog*$replace;
    					break;
					
                    //角色死亡日志
                    case "dead":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'CharacterLevel'=>$log['data']['Level'],
                        'MonsterId'=>$log['data']['MonsterSlkID'],
                        'HeroId'=>$log['data']['HeroID'],
                        'SlkId'=>$log['data']['SlkID'],
                        'CharacterDeadTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                        
                        $oCharacter = new Lm_Character();
                        //$insertLog = $oCharacter->InsertCharacterDeadLog($DataArr);
                        break;
				
                    //角色进入副本	
                    case "enterectype":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'CharacterLevel'=>$log['data']['Level'],
                        'HeroId'=>$log['data']['HeroID'],
                        'TeamNum'=>$log['data']['TeamNum'],
                        'SlkId'=>$log['data']['SlkID'],
                        'EctypeId'=>$log['data']['EctypeID'],
                        'CharacterSlkEnterTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                        
                        $oTask = new Lm_Task();
                        $insertLog = $oTask->InsertCharacterSlkLog($DataArr);
                        break;
				
                    //角色离开副本
                    case "leaveectype":
                        $bindArr = array(
                        $log['data']['AccountID'],
                        $log['data']['SlkID'],
                        $log['data']['EctypeID'],
                        $ServerInfo['AppId'],
                        $ServerInfo['PartnerId'],
                        $ServerInfo['ServerId'],
                        );
                        $DataArr = array(
                        'CharacterSlkLeaveTime'=>strtotime($log['time']),
                        'CharacterLeaveType'=>intval($log['data']['Reason']),
                        );
                        
                        $oTask = new Lm_Task();								
                        $CharacterSlkEnterTime = $oTask->GetSlkIdMapLog($bindArr,'CharacterSlkEnterTime');
                        if($CharacterSlkEnterTime)
                        {
                            $updateLog = $oTask->LeaveSlk($CharacterSlkEnterTime,$DataArr,$bindArr,$log['data']['AccountID']);                            
                            $insertLog = $updateLog; 
                        }
                        break;
                    
                    //登出
                    case "logout":
	                    $oUser = new Lm_User();
	                    $oLogin = new Lm_Login();
	                    $UserInfo = $oUser->GetUserById($log['data']['AccountID']); 
	                    if($UserInfo['UserId'])
	                    {
	                       $DataArr = array(
	                        'UserId'=>$log['data']['AccountID'],
	                        'LogoutTime'=>strtotime($log['time']),
	                        'LoginTime'=>$log['data']['TimeStamp'],
	                        'ServerId'=>$ServerInfo['ServerId'],
	                        );
	                        $insertLog = $oLogin->LogoutByTime($DataArr,$UserInfo['UserName']);
	                    }
	                    else
	                    {
	                    	break; 	
	                    }                        
                        break;
//                        $DataArr = array(
//                        'UserId'=>$log['data']['AccountID'],
//                        'LogoutTime'=>strtotime($log['time']),
//                        'AppId'=>$ServerInfo['AppId'],
//                        'PartnerId'=>$ServerInfo['PartnerId'],
//                        'ServerId'=>$ServerInfo['ServerId'],
//                        'LoginId'=>$log['data']['LoginID'],
//                        'LogoutLevel'=>$log['data']['Level'],
//                        'LogoutReason'=>$log['data']['Reason'],
//                        'MaxPing'=>$log['data']['MaxPing'],
//                        'MinPing'=>$log['data']['MinPing'],
//                        );
//                        
//                        $oCharacter = new Lm_Character();
//                        $insertLog = $oCharacter->InsertCharacterLogoutLog($DataArr);
//                        break;
                
                    case "levelup":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'CharacterLevel'=>$log['data']['Level'],
                        'CharacterLevelUpTime'=>strtotime($log['time']),
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        );
                    
                        $oCharacter = new Lm_Character();
                        $insertLog = $oCharacter->InsertcharacterLevelUpLog($DataArr);
                        $updateLog = $oCharacter->updateCharacterInfo($log['data']['AccountID'],$ServerInfo['ServerId'],array('CharacterLevel'=>$log['data']['Level']));
                        echo "\tupdate:".$updateLog."\n";
                        break;
                        
                    case "tower":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'AppId'=>$ServerInfo['AppId'],
                        'PartnerId'=>$ServerInfo['PartnerId'],
                        'ServerId'=>$ServerInfo['ServerId'],
                        'EctypeID'=>$log['data']['EctypeID'],
                        'CreateTowerTime'=>(strtotime($log['time'])-($log['data']['RunTime']/1000)),
                        'EndTowerTime'=>strtotime($log['time']),                        
                        'CharacterLevel'=>$log['data']['Level'],                        
                        'HeroID'=>$log['data']['HeroID'],
                        'SlkID'=>$log['data']['SlkID'],                        
                        'TowerIndex'=>$log['data']['Index'],
                        'PlayerIndex'=>$log['data']['PlayerIndex'],
                        'PlayerNum'=>$log['data']['PlayerNum'],
                        'RunTime'=>$log['data']['RunTime'],
                        );
                    
                        $oTask = new Lm_Task();								
                        $insertLog = $oTask->insertTowerLog($DataArr);
                        break;
                }
//                if($insertLog)
//                {
//                    echo "\tinsert:".$insertLog."\n";	
//                }                
            }            
            fclose($fd);
        }
        else
        {
            echo iconv("utf-8","gbk","文件".$file."不存在\n");
        }
    }
    
	function getRankLogAction()
	{
		$ServerId = trim($this->request->ServerId);
		$oCharacter = new Lm_Character();

		$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
		$ServerInfo = $ServerList[$ServerId];
        if($ServerInfo['ServerId'])
		{
			$filename = $ServerInfo['AppId'].'/'.$ServerInfo['PartnerId'].'/'.$ServerInfo['ServerId'].'/'.date('Y-m-d',time()).'/accbrief58.log';
			$logurl = '/gamelog/';
			$filename = $logurl.$filename;
			echo $filename."\n";
            $file = fopen($filename,'r');
			if($file)
			{
				$succeed = 0;
				$fail = 0;
				$count = 0;
				
				$oCharacter->truncateUserCharacterRankList();
				while($content = fgets($file,filesize($filename)))
				{
					$count++;
					$arr = explode(",", $content);
                    
                    if(is_numeric($arr[0])){
                        $CharacterInfoUser = $oCharacter->getCharacterInfoByUser($arr[0],$ServerInfo['ServerId']);
                        $CharacterInfoName = $oCharacter->getCharacterInfoByCharacter(iconv('GBK','UTF-8',htmlspecialchars(trim($arr[1]))),$ServerInfo['ServerId']);
                        
                        if(count($CharacterInfoUser) < 1 || count($CharacterInfoName) < 1){
                            $Character['UserId'] = trim($arr[0]);
                    		$Character['CharacterName'] = iconv('GBK','UTF-8',htmlspecialchars(trim($arr[1])));
                    		$Character['ServerId'] = $ServerInfo['ServerId'];
                            $Character['AppId'] = $ServerInfo['AppId'];
			 			    $Character['PartnerId'] = $ServerInfo['PartnerId'];
                    		$Character['CharacterLevel'] = 1;
                    		$Character['CharacterCreateTime'] = trim($arr[10]);
                            
                            $insertLog = $oCharacter->CreateCharacter($Character);
                            
                            echo "insert:".$insertLog."\n";
                        }else{
                            $updateLog = $oCharacter->updateCharacterInfo($arr[0],$ServerInfo['ServerId'],array(
                            "CharacterLevel"=>$arr[4],
                                "Comment"=>json_encode(array(
                                    'FightingCapacity'=>$arr[2],'Capacity'=>$arr[3],'PKPoint'=>$arr[9]
                                )),
                            "CharacterName"=>iconv('GBK','UTF-8',htmlspecialchars(trim($arr[1]))),
                            ));
                            echo "update:".$updateLog."\n";
                        }
                        
                        $Uarr = array('UserId' => $arr[0],'ServerId'=>$ServerId,'CharacterName'=>iconv('GBK','UTF-8',htmlspecialchars(trim($arr[1]))),'FightingCapacity'=>$arr[2],'Capacity'=>$arr[3],'CharacterLevel'=>$arr[4],'PKPoint'=>$arr[9]);
    					$insert = $oCharacter->insertUserCharacterRankList($Uarr);
                        echo "insertRank:".$insert."\n";
    					if($insert)
    					{
    						$succeed++;
    					}else{
    						$fail++;
    					}
                    }else{
                        continue;
                    }
				}								 	 
			}
            
			echo $succeed."-".$fail;
			fclose($file);
			//根据战斗力和生命值 将用户排名写入配置文件 
			$oCharacter->getUserByFightRank();
			$oCharacter->getUserByLiveRank();
			$oCharacter->getUserByPKPoint();				
		}
	}
}
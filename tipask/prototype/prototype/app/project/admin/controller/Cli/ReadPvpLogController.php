<?php
class Cli_ReadPvpLogController extends Base_Controller_Action{	
	public function init()
	{
		parent::init();
		$this->oServer = new Config_Server();
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
		$ServerData = $this->oServer->getRow($ServerId);
		$filename = "pvplog";
		$lag = 30;
        
        $matchesc = array();
        $matchsingle = array();
        $matchtotal = array();
        		
		do
		{			
			$LastUpdateLog = $this->oCron->GetLastUpdate($ServerId,$filename);
			$Time = array('EndTime'=> time(),'StartTime'=>$LastUpdateLog['LastUpdateTime']-$lag);
			$LastUpdate = array('LastUpdateTime'=>$LastUpdateLog['LastUpdateTime'],'ToUpdateTime'=>$LastUpdateLog['LastUpdateTime'] + $lag);			
			
			$time = $Time['StartTime'];	
			do
			{
				$DateArr[date('Y-m-d',$time)] = 1;
				$time+=86400;
			}
			while(strtotime(date("Y-m-d",$time)) <= strtotime(date("Y-m-d",$Time['EndTime'])));
            print_r($DateArr);
			foreach($DateArr as $Date => $value)
			{
			    $S = max(strtotime($Date),$Time['StartTime']);
			    $E = min(strtotime($Date)+86400-1,$Time['EndTime']);
                
                if($this->getEnvironment())
                {                    
                    $ServerRoot = $ServerData['AppId']."/".$ServerData['PartnerId']."/".$ServerData['ServerId'];
                    $logurl = '/gamelog';
                    $dir = $logurl."/".$ServerRoot."/".$Date."/";
                }
                else
                {
                    $logurl = "d:\\wamp\\www\\web_usercenter\\log";
                    $ServerRoot = $ServerData['AppId']."\\".$ServerData['PartnerId']."\\".$ServerData['ServerId'];
                    $dir = $logurl."\\".$ServerRoot."\\".$Date."\\";
                }
                
				$fileArr = $this->get_file($dir,$filename,"log");
				ksort($fileArr);
                
				foreach($fileArr as $key => $FileName)
				{
					$t = explode(".",$FileName);
					$t2 = explode("-",$t[0]);
					if(($t2[1]>=date("H",$S))&&($t2[1]<=date("H",$E)))
					{
					    $FileName = $dir.$FileName;
                        echo "\n".$FileName."\n";
                        echo "StartTime:".date("Y-m-d H:i:s",$Time['StartTime'])."\tEndTime:".date("Y-m-d H:i:s",$Time['EndTime'])."\n";					    
	    				$return = $this->readLine($FileName,$filename,$ServerData,$Time['StartTime'],$Time['EndTime'],$LastUpdate,$lag,$matchesc,$matchsingle,$matchtotal);
					    $LastUpdate = $return['LastUpdate'];
                        $matchesc = $return['matchesc'];
                        $matchsingle = $return['matchsingle'];
                        $matchtotal = $return['matchtotal'];
                    }
				}
			}							
			sleep(10);
		}
		while(true);        
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

    function readLine($file,$filename,$ServerData,$StartTime,$EndTime,$LastUpdate,$lag,$matchesc,$matchsingle,$matchtotal)
    {
        if(file_exists($file))
        {
            $fd = fopen($file, "r");
            
            while ($buffer = fgets($fd)) 
            {                              
    			$data = explode(":",substr($buffer,20,strlen($buffer)));
                
                $function = explode(";",$data[2]);
                
    			$log = array();
                
    			if(($StartTime <= (strtotime(substr($buffer,0,19))) || $StartTime == 0 ) && ($EndTime >= (strtotime(substr($buffer,0,19))) || $EndTime == 0 ))
				{
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
				}
				else
				{
					continue; 	
				}
                
    			$result = array();
                
                if(count($matchtotal) > 1000){
                    unset($matchtotal);
                    $matchtotal = array();
                }
                
                if(count($matchesc) > 1000){
                    unset($matchesc);
                    $matchesc = array();
                }
                
                if(count($matchsingle) > 1000){
                    unset($matchsingle);
                    $matchsingle = array();
                }
                
                echo "\n".$log['function'];
    			switch ($log['function'])
    			{
    				//PVP单人记录
                    case "matchsingle":                        
    					$DataArr = array(
                            'SlkID'=>$log['data']['SlkID'],
                            'EctypeID'=>$log['data']['EctypeID'],
    						'UserId'=>$log['data']['AccountID'],
                            'HeroID'=>$log['data']['HeroID'],                            
                            'PvpLevel'=>$log['data']['PvpLevel'],
                            'KillNum'=>$log['data']['KillNum'],
                            'DeadNum'=>$log['data']['DeadNum'],
                            'AssistNum'=>$log['data']['AssistNum'],
                            'EquipList'=>json_encode(
                                array(
                                    '1'=>$log['data']['Equip1'],
                                    '2'=>$log['data']['Equip2'],
                                    '3'=>$log['data']['Equip3'],
                                    '4'=>$log['data']['Equip4'],
                                    '5'=>$log['data']['Equip5'],
                                    '6'=>$log['data']['Equip6'],
                                )
                            ),
                            'Won'=>$log['data']['Result'],
                            'AppId'=>$ServerData['AppId'],
    						'PartnerId'=>$ServerData['PartnerId'],
    						'ServerId'=>$ServerData['ServerId'],
                            'Comment'=>json_encode(
                                array(
                                    "Double"=>$log['data']['Double'],
                                    "Triple"=>$log['data']['Triple'],
                                    "Four"=>$log['data']['Four'],
                                    "Five"=>$log['data']['Five'],
                                    "God"=>$log['data']['God'],
                                    "Mvp"=>$log['data']['Mvp'],
                                    "KillKing"=>$log['data']['KillKing'],
                                    "AssistKing"=>$log['data']['AssistKing'],
                                    "DestroyKing"=>$log['data']['DestroyKing'],
                                    "Grade"=>$log['data']['Grade'],
                                    "KillMonNum"=>$log['data']['KillMonNum'],
                                    "PvpMoney"=>$log['data']['PvpMoney'],
                                    "Faction"=>trim($log['data']['Faction']),
                                )
                            ),
    					);
                        
                        $oTask = new Lm_Task();                        
                        
                        if(isset($matchtotal[$DataArr['EctypeID']])){
                            $DataArr['PvpEnterTime'] = $matchtotal[$DataArr['EctypeID']]['PvpEnterTime'];
                            $DataArr['PvpLeaveTime'] = $matchtotal[$DataArr['EctypeID']]['PvpEndTime'];
                            
                            if(isset($matchesc[$DataArr['EctypeID']])){
                                foreach($matchesc[$DataArr['EctypeID']] as $k=>$v){
                                    $DataArrEsc = array(
                                        'SlkID'=>$v['SlkID'],
                                        'EctypeID'=>$v['EctypeID'],
                						'UserId'=>$v['UserId'],
                                        'HeroID'=>$v['HeroID'],
                                        'PvpEnterTime'=>$DataArr['PvpEnterTime'],
                                        'PvpLeaveTime'=>$v['PvpLeaveTime'],
                                        'PvpLevel'=>0,
                                        'KillNum'=>0,
                                        'DeadNum'=>0,
                                        'AssistNum'=>0,
                                        'EquipList'=>json_encode(array()),
                                        'Won'=>3,
                                        'AppId'=>$ServerData['AppId'],
                						'PartnerId'=>$ServerData['PartnerId'],
                						'ServerId'=>$ServerData['ServerId'],
                                        'Comment'=>json_encode(array(
                                        'Faction'=>trim($v['Faction']),
                                        )),
                					);
                                    
                                    $insertLog = $oTask->InsertPvpLog($DataArrEsc);
                                    
                                    if($insertLog){
                                        unset($matchesc[$DataArr['EctypeID'][$k]]);
                                    }
                                }
                            }
                            
                            $insertLog = $oTask->InsertPvpLog($DataArr);
                        }else{
                            $matchsingle[$DataArr['EctypeID']][] = $DataArr;
                        }
                        
    					break;
    					
    				case "matchtotal": 
                        $DataArr = array(
                            'SlkID'=>$log['data']['SlkID'],
                            'EctypeID'=>$log['data']['EctypeID'],
                            'PvpEnterTime'=>($log['data']['EndTimeStamp']-$log['data']['RunTime']),
                            'RunTime'=>$log['data']['RunTime'],
                            'PvpEndTime'=>$log['data']['EndTimeStamp'],
                            'WinCamp'=>$log['data']['WinCamp'],
                            'AppId'=>$ServerData['AppId'],
    						'PartnerId'=>$ServerData['PartnerId'],
    						'ServerId'=>$ServerData['ServerId'],
    					);
                        
                        $oTask = new Lm_Task();
    					$insertLog = $oTask->InsertPvpTotalLog($DataArr);
                        
                        if(isset($matchesc[$DataArr['EctypeID']])){
                            foreach($matchesc[$DataArr['EctypeID']] as $k=>$v){
                                $DataArrEsc = array(
                                    'SlkID'=>$v['SlkID'],
                                    'EctypeID'=>$v['EctypeID'],
            						'UserId'=>$v['UserId'],
                                    'HeroID'=>$v['HeroID'],
                                    'PvpEnterTime'=>$DataArr['PvpEnterTime'],
                                    'PvpLeaveTime'=>$v['PvpLeaveTime'],
                                    'PvpLevel'=>0,
                                    'KillNum'=>0,
                                    'DeadNum'=>0,
                                    'AssistNum'=>0,
                                    'EquipList'=>json_encode(array()),
                                    'Won'=>3,
                                    'AppId'=>$ServerData['AppId'],
            						'PartnerId'=>$ServerData['PartnerId'],
            						'ServerId'=>$ServerData['ServerId'],
                                    'Comment'=>json_encode(array(
                                    'Faction'=>$v['Faction'],
                                    )),
            					);
                                
                                $insertLog = $oTask->InsertPvpLog($DataArrEsc);
                                
                                if($insertLog){
                                    unset($matchesc[$DataArr['EctypeID']][$k]);
                                }
                            }
                        }
                        
                        if(isset($matchsingle[$DataArr['EctypeID']])){
                            foreach($matchsingle[$DataArr['EctypeID']] as $k=>$v){
                                $v['PvpEnterTime'] = $DataArr['PvpEnterTime'];
                                $v['PvpLeaveTime'] = $DataArr['PvpEndTime'];
                                
                                $insertLog = $oTask->InsertPvpLog($v);
                                if($insertLog){
                                    unset($matchsingle[$DataArr['EctypeID']][$k]);
                                }
                            }
                        }
                        
                        $matchtotal[$DataArr['EctypeID']] = $DataArr;
                        break;  
                        
                    case "matchesc":
                        $DataArr = array(
                            'SlkID'=>$log['data']['SlkID'],
                            'EctypeID'=>$log['data']['EctypeID'],
                            'PvpLeaveTime'=>strtotime($log['time']),
                            'UserId'=>$log['data']['AccountID'],
                            'HeroID'=>$log['data']['HeroID'],
                            'Faction'=>trim($log['data']['Faction']),
                            'AppId'=>$ServerData['AppId'],
    						'PartnerId'=>$ServerData['PartnerId'],
    						'ServerId'=>$ServerData['ServerId'],
    					);
                        
                        $matchesc[$DataArr['EctypeID']][] = $DataArr;
                        break;                       
                }
                echo "LogTime:".$log['time']."\n";
                if($insertLog)
                {
                    echo "\tinsert:".$insertLog."\n";
                    
                    if(strtotime($log['time']) >= $LastUpdate['LastUpdateTime'])
                    {
                        $LastUpdate['LastUpdateTime'] = strtotime($log['time']);
                    }
                    echo "LastUpdateTime:".date("Y-m-d H:i:s",$LastUpdate['LastUpdateTime'])."\tToUpdateTime:".date("Y-m-d H:i:s",$LastUpdate['ToUpdateTime'])."\n";
					if($LastUpdate['LastUpdateTime']>=$LastUpdate['ToUpdateTime'])
					{
						$this->oCron->UpdateLastUpdate(array('ServerId'=>$ServerData['ServerId'],'LastUpdateTime'=>$LastUpdate['LastUpdateTime'],'FileType'=>$filename));
						$LastUpdate['ToUpdateTime'] = $LastUpdate['LastUpdateTime'] + $lag;
					}	
                }
    		}
			$this->oCron->UpdateLastUpdate(array('ServerId'=>$ServerData['ServerId'],'LastUpdateTime'=>$LastUpdate['LastUpdateTime'],'FileType'=>$filename));	
			$LastUpdate['ToUpdateTime'] = $LastUpdate['LastUpdateTime'] + $lag;
			
            $return['LastUpdate'] = $LastUpdate;
            $return['matchsingle'] = $matchsingle;
            $return['matchesc'] = $matchesc;
            $return['matchtotal'] = $matchtotal;
            fclose($fd);            
            return $return;          	
        }
        else
        {
            echo iconv("utf-8","gbk","文件".$file."不存在\n");
        }
    }
}
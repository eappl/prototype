<?php
class Cli_ReadPickLogController extends Base_Controller_Action{	
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
		$filename = "picklog";
		$lag = 30;        
        		
		do
		{			
			$LastUpdateLog = $this->oCron->GetLastUpdate($ServerId,$filename);
			$Time = array('EndTime'=> time(),'StartTime'=>$LastUpdateLog['LastUpdateTime']);
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
	    				$LastUpdate = $this->readLine($FileName,$filename,$ServerData,$Time['StartTime'],$Time['EndTime'],$LastUpdate,$lag);
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

    function readLine($file,$filename,$ServerData,$StartTime,$EndTime,$LastUpdate,$lag)
    {
        if(file_exists($file))
        {
            $fd = fopen($file, "r");
        
            while ($buffer = fgets($fd)) 
            {                              
    			$data = explode(":",substr($buffer,20,strlen($buffer)));
                
                if(strstr($buffer,"pickitem")){
                    $function = explode(";",$data[1]);
                }
                
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
                
    			$result = array();
                
                echo "\n".$log['function'];                
    			switch ($log['function'])
    			{
                    //道具拾取
                    case "pickitem":
                        $DataArr = array(
                        'UserId'=>$log['data']['AccountID'],
                        'ItemPickUpTime'=>strtotime($log['time']),
                        'AppId'=>$ServerData['AppId'],
                        'PartnerId'=>$ServerData['PartnerId'],
                        'ServerId'=>$ServerData['ServerId'],
                        'CharacterLevel'=>$log['data']['Level'],
                        'ItemId'=>$log['data']['ItemID'],
                        'PreFixId'=>$log['data']['PreFixID'],
                        'SufFixId'=>$log['data']['SufFixID'],
                        'ItemNum'=>intval($log['data']['ItemNum'])?intval($log['data']['ItemNum']):1,
                        );
                        
                        $oItemLog = new Lm_Item();
                        $insertLog = $oItemLog->InsertItemPickUpLog($DataArr);
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
            
            fclose($fd);
            return $LastUpdate;          	
        }
        else
        {
            echo iconv("utf-8","gbk","文件".$file."不存在\n");
        }
    }
}
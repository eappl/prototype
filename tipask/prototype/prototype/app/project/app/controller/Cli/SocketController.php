<?php

class Cli_SocketController extends Base_Controller_Action
{
	public $oSocket;
    public $oSocketCli;
    public $oSocketQueue;
	
	public function init()
	{
		parent::init();
		$this->socketPath =  "/www/opt";
		$this->oSocketServer = new Connect_SocketServer();
        $this->oSocketClient = new Connect_SocketClient();
        $this->oSocketQueue = new Config_SocketQueue();
	}
	
	function socketServerAction()
	{
		//D:\wamp\bin\php\php5.2.6>php.exe d:\wamp\www\web_usercenter\app\admin\html\cli.php "ctl=socket&ac=socket.server"
		set_time_limit(0);
		
		$ServerId = trim($this->request->ServerId);
		$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
		$ServerInfo = $ServerList[$ServerId];
		if($ServerInfo['ServerId'])
		{
			$ipserver = '183.136.134.91';
			//$ipserver = '192.168.30.37';

			$SocketPort = $ServerInfo['SocketPort'];
			$errno = 1;
			$timeout = 1;
			$buff = 1024;	//缓存大小
			echo "Server:".$ipserver.",Port:".$ServerInfo['SocketPort']."\n";
			$socket=stream_socket_server('tcp://'.$ipserver.':'.$SocketPort, $errno, $errstr);
			echo "socket:".$socket."\n";
			stream_set_blocking($socket,0);
			//如果socket已经连接,接受socket连接并获取信息
			while(true)
			{
				$conn = @stream_socket_accept($socket,-1);
				$Buff_to_process = "";
				while($conn)
				{		
					echo "conn:".$conn."\n";
					$buff = fread($conn,1024*16);
					$length = strlen($buff);
					if($length === 0)
					{
						unset($conn);
						break;
					}
					else
					{
						//在单次长连接中进行数据读取
						$format="V2Length/vuType";
						if(!isset($Buff_to_process))
						{
							$Buff_to_process = "";
						}
						$Buff_to_process .= $buff;
					
						do
						{
							$unpack_buff =  @unpack($format,$Buff_to_process);
							$text = substr($Buff_to_process,0,$unpack_buff['Length1']);
							$unpackArry =  @unpack($format,$text);
                            echo $unpackArry['uType']."\n";
							switch ($unpackArry['uType'])
							{
								case "60201":									
									$resMsg = $this->oSocketServer->SocketLogin($text);
									fwrite($conn,$resMsg);
									break;
								case "60204":
									$resMsg = $this->oSocketServer->SocketLogout($text);
									break;
								case "60206":
									$resMsg = $this->oSocketServer->SocketGetServerInfo($text);
									fwrite($conn,$resMsg);
									break;
								case "60208":
									$resMsg = $this->oSocketServer->SocketCreateCharacter($text);
									break;
							}	
							$Buff_to_process = substr($Buff_to_process,strlen($text),strlen($Buff_to_process)-strlen($text));
							echo "last:".strlen($Buff_to_process)."\n";
							$unpack2_buff = @unpack($format,$Buff_to_process);
						}
						while(($unpack2_buff['Length1'] <= strlen($Buff_to_process))&&($unpack2_buff['Length1']>0));		
					}
					
				}
			}
		}
	}
    
    function socketClientAction()
    {
		$ServerId = trim($this->request->ServerId);
		$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
		$ServerInfo = $ServerList[$ServerId];
		if($ServerInfo['ServerId'])
		{
			echo date("Y-m-d H:i:s",time())."write connecting:\n";
			$connect = @fsockopen(long2ip($ServerInfo['ServerIp']), $ServerInfo['ServerSocketPort'], $errno, $errstr, 1);
			echo long2ip($ServerInfo['ServerIp'])."-".$ServerInfo['ServerSocketPort']."\n";
			$Buff_to_process = "";
			// stream_set_blocking($sock,TRUE);
			stream_set_timeout($connect,0);
			echo "connected:".$connect."\n"; 
			while(true)
			{                     
				$sendQueue = $this->oSocketQueue->getSendSocket($ServerInfo['ServerId'],'60215,60219,60217',1000);

				echo date("Y-m-d H:i:s",time())." got queue:".count($sendQueue)."\n";
				if(is_array($sendQueue))
				{
					foreach($sendQueue as $k=>$v)
					{
						echo $v['uType']."\n";
						switch($v['uType'])
						{
							case "60219":
								$SendContent = $this->oSocketClient->PackMsg($v);
								break;
							case "60215":
								$SendContent = $this->oSocketClient->PackExchangeNoSN($v);
								break;
							case "60217";
								$SendContent = $this->oSocketClient->PackExchange($v);						
								break;
						}
						echo "connect:".$connect."\n";
						if($connect)
						{
							fwrite($connect,$SendContent);
							$this->oSocketQueue->MoveSocketQueue($v);                            		
						}
						else
						{
							fclose($connect);
							echo date("Y-m-d H:i:s",time()). "write connecting:";
							$connect = @fsockopen(long2ip($ServerInfo['ServerIp']), $ServerInfo['ServerSocketPort'], $errno, $errstr, 1);
							$Buff_to_process = "";
							// stream_set_blocking($sock,TRUE);
							stream_set_timeout($connect,0);
							echo "connected:".$connect."\n";                             	
						}										
					}
				}
                else
                {
                    sleep(1);
                }
				echo $connect?"connected\n":"unconnected\n"; 
				if(!feof($connect))
				{
					if($connect)
					{
						$buff = fread($connect,1024);
						$length = strlen($buff);
						echo date("Y-m-d H:i:s",time())." read buff:".$length."\n";
						$format="V2Length/vuType";
						if(!isset($Buff_to_process))
						{
							$Buff_to_process = "";
						}
						$Buff_to_process .= $buff;							
						do
						{
							sleep(1);
							$unpack_buff =  @unpack($format,$Buff_to_process);
							$text = substr($Buff_to_process,0,$unpack_buff['Length1']);
							$unpackArry =  @unpack($format,$text);							
							print_R($unpackArry);
							switch ($unpackArry['uType'])
							{
								case "60216":
									$resMsg = $this->oSocketClient->UnPackSN($text,$unpackArry['uType']);
									break;
								case "60218":
									$resMsg = $this->oSocketClient->UnPackExchangeResult($text,$unpackArry['uType']);
									break;
								case "60224":
									$resMsg = $this->oSocketClient->UnPackAddMoney($text,$unpackArry['uType']);
									break;
								case "60228":
									$resMsg = $this->oSocketClient->UnPackAddHero($text,$unpackArry['uType']);
									break;
								case "60230":
									$resMsg = $this->oSocketClient->UnPackAddSkin($text,$unpackArry['uType']);
									break;
							}							
							$Buff_to_process = substr($Buff_to_process,strlen($text),strlen($Buff_to_process)-strlen($text));
							echo date("Y-m-d H:i:s",time())." last:".strlen($Buff_to_process)."\n";
							$unpack2_buff = @unpack($format,$Buff_to_process);
						}
						while(($unpack2_buff['Length1']<=strlen($Buff_to_process))&&($unpack2_buff['Length1']>0)&&(strlen($Buff_to_process)>=0));						
					}
					                  		
					
					else
					{
						echo "close connect";
						fclose($connect);
						sleep(1);
						echo "重新连接"."\n";
						echo date("Y-m-d H:i:s",time())." read connecting:\n";
						$connect = @fsockopen(long2ip($ServerInfo['ServerIp']), $ServerInfo['ServerSocketPort'], $errno, $errstr, 1);
						$Buff_to_process = "";
						// stream_set_blocking($sock,TRUE);
						stream_set_timeout($connect,0);
						echo "connected:".$connect."\n";                             	
					}
				}
				else
				{
					echo "close connect";
					fclose($connect);
					sleep(1);
					echo "重新连接"."\n";
					echo date("Y-m-d H:i:s",time())." read connecting:\n";
					$connect = @fsockopen(long2ip($ServerInfo['ServerIp']), $ServerInfo['ServerSocketPort'], $errno, $errstr, 1);
					$Buff_to_process = "";
					// stream_set_blocking($sock,TRUE);
					stream_set_timeout($connect,0);
					echo "connected:".$connect."\n";                             	
				}
				                        			                                  						
			}           
		}        
    }
    function socketClientGmAction()
    {
		$ServerId = trim($this->request->ServerId);
		$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
		$ServerInfo = $ServerList[$ServerId];
		if($ServerInfo['ServerId'])
		{
			echo date("Y-m-d H:i:s",time())."write connecting:\n";
			$connect = @fsockopen(long2ip($ServerInfo['GMIp']), $ServerInfo['GMSocketPort'], $errno, $errstr, 1);
			echo long2ip($ServerInfo['GMIp'])."-".$ServerInfo['GMSocketPort']."\n";
			$Buff_to_process = "";
			// stream_set_blocking($sock,TRUE);
			stream_set_timeout($connect,0);
			echo "connected:".$connect."\n"; 
			while(true)
			{                     
				$sendQueue = $this->oSocketQueue->getSendSocket($ServerInfo['ServerId'],'60221,60223,60227,60229',1000);
				echo date("Y-m-d H:i:s",time())." got queue:".count($sendQueue)."\n";
				if(is_array($sendQueue))
				{
					foreach($sendQueue as $k=>$v)
					{
						echo $v['uType']."\n";
						switch($v['uType'])
						{
							case "60221":
								$SendContent = $this->oSocketClient->PackKickOff($v);
								break;
							case "60223":
								$SendContent = $this->oSocketClient->PackAddMoney($v);
								break;
							case "60227":
								$SendContent = $this->oSocketClient->PackAddHero($v);
								break;
							case "60229";
								$SendContent = $this->oSocketClient->PackAddSkin($v);						
								break;
						}
						echo "connect:".$connect."\n";
						if($connect)
						{
							fwrite($connect,$SendContent);
							//sleep(1);
                            $this->oSocketQueue->MoveSocketQueue($v);                            		
						}
						else
						{
							fclose($connect);
							echo date("Y-m-d H:i:s",time()). "write connecting:";
							$connect = @fsockopen(long2ip($ServerInfo['ServerIp']), $ServerInfo['ServerSocketPort'], $errno, $errstr, 1);
							$Buff_to_process = "";
							// stream_set_blocking($sock,TRUE);
							stream_set_timeout($connect,0);
							echo "connected:".$connect."\n";                             	
						}										
					}
                    sleep(1);
				}
                else
                {
                    sleep(1);
                }
				echo $connect?"connected\n":"unconnected\n"; 
				if(!feof($connect))
				{
					if($connect)
					{
						$buff = fread($connect,1024);
						$length = strlen($buff);
						echo date("Y-m-d H:i:s",time())." read buff:".$length."\n";
						$format="V2Length/vuType";
						if(!isset($Buff_to_process))
						{
							$Buff_to_process = "";
						}
						$Buff_to_process .= $buff;							
						do
						{
							$unpack_buff =  @unpack($format,$Buff_to_process);
							$text = substr($Buff_to_process,0,$unpack_buff['Length1']);
							$unpackArry =  @unpack($format,$text);							
							print_R($unpackArry);
							
							switch ($unpackArry['uType'])
							{
								case "60216":
									$resMsg = $this->oSocketClient->UnPackSN($text,$unpackArry['uType']);
 							        $content = date('Y-m-d H:i:s',time());							
        							$filename = $ServerId.".log";
        							$this->writeTxt($filename,$content);
									break;
								case "60218":
									$resMsg = $this->oSocketClient->UnPackExchangeResult($text,$unpackArry['uType']);
                                    $content = date('Y-m-d H:i:s',time());							
        							$filename = $ServerId.".log";
        							$this->writeTxt($filename,$content);
									break;
								case "60224":
									$resMsg = $this->oSocketClient->UnPackAddMoney($text,$unpackArry['uType']);
                                    $content = date('Y-m-d H:i:s',time());							
        							$filename = $ServerId.".log";
        							$this->writeTxt($filename,$content);
									break;
								case "60228":
									$resMsg = $this->oSocketClient->UnPackAddHero($text,$unpackArry['uType']);
                                    $content = date('Y-m-d H:i:s',time());							
        							$filename = $ServerId.".log";
        							$this->writeTxt($filename,$content);
									break;
								case "60230":
									$resMsg = $this->oSocketClient->UnPackAddSkin($text,$unpackArry['uType']);
                                    $content = date('Y-m-d H:i:s',time());							
        							$filename = $ServerId.".log";
        							$this->writeTxt($filename,$content);
									break;
							}
							

							
							$Buff_to_process = substr($Buff_to_process,strlen($text),strlen($Buff_to_process)-strlen($text));
							echo date("Y-m-d H:i:s",time())." last:".strlen($Buff_to_process)."\n";
							$unpack2_buff = @unpack($format,$Buff_to_process);
						}
						while(($unpack2_buff['Length1']<=strlen($Buff_to_process))&&($unpack2_buff['Length1']>0)&&(strlen($Buff_to_process)>=0));						
					}
					                  		
					
					else
					{
						echo "close connect";
						fclose($connect);
						sleep(1);
						echo "重新连接"."\n";
						echo date("Y-m-d H:i:s",time())." read connecting:\n";
						$connect = @fsockopen(long2ip($ServerInfo['ServerIp']), $ServerInfo['ServerSocketPort'], $errno, $errstr, 1);
						$Buff_to_process = "";
						// stream_set_blocking($sock,TRUE);
						stream_set_timeout($connect,0);
						echo "connected:".$connect."\n";                             	
					}
				}
				else
				{
					echo "close connect";
					fclose($connect);
					sleep(1);
					echo "重新连接"."\n";
					echo date("Y-m-d H:i:s",time())." read connecting:\n";
					$connect = @fsockopen(long2ip($ServerInfo['ServerIp']), $ServerInfo['ServerSocketPort'], $errno, $errstr, 1);
					$Buff_to_process = "";
					// stream_set_blocking($sock,TRUE);
					stream_set_timeout($connect,0);
					echo "connected:".$connect."\n";                             	
				}
				                        			                                  						
			}           
		}        
    }
    
	function writeTxt($filename,$content)
	{
		$logpath = "/www/opt/sock/log/";
		$filename = $logpath.$filename;
		$fp = fopen($filename,'w');
		fwrite($fp,$content);
		fclose($fp);
	}
}
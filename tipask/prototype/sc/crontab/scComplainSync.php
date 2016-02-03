<?php 
error_reporting(0);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(dirname(__FILE__)));
ini_set('include_path', TIPASK_ROOT);
date_default_timezone_set('Etc/GMT-8');
defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

require 'config.php';
require 'lib/config.class.php';
require 'lib/global.func.php';
require 'lib/CacheMemcache.class.php';
require 'lib/db.class.php';
require 'model/base.class.php';
require 'lib/pdo/Mysql/pdo.class.php';
require 'lib/pdo/Mysql/hash.class.php';
class ScComplainSync extends base 
{
	private $complainKey = "%YOJNCWQRIWA:OE YV)ENVRMQOWV {)RWCJNWQBCVCE WQMEJC WROL VR"; // 投诉同步key
	private $complainBackUrl = 'http://complain.5173esb.com/sc/AsycPostInfo.ashx'; // sc投诉问题同步complain站点返回值接口
	private $complainSyncUrl = 'http://complain.5173esb.com/sc/AsycGetInfo.ashx'; // 获取complain站点同步数据接口
	private $backComplainUrl = 'http://complain.5173esb.com/sc/AsycUpdateSc.ashx'; // 返回complain站点接口
	private $complainAnswerUrl = 'http://complain.5173esb.com/sc/AsycGetAnswer.ashx';	// complain站点 同步回答表接口
	function __construct()
	{
		parent::__construct();
		$argv = $this->getCmdArgv(); // 获取命令行参数
		
		// sc投诉问题同步到coplain
		if($argv['operation'] == 'ScSyncComplain') 
		{
			$num = intval($argv['syncNum']);
			$syncNum = $num >=0 ? $num : 20; // 同步条数
			$this->ScSyncComplain($syncNum);
		}
		else if($argv['operation'] == 'ComplainSyncSc')
		{
			$this->ComplainSyncSc();
		}
		else if($argv['operation'] == 'ComplainAnswerToSc')
		{
			$num = intval($argv['syncNum']);
			$syncNum = $num >=0 ? $num : 20;
			$this->ComplainAnswerToSc($syncNum);
		}
		else if($argv['operation'] == 'ScSyncComplainMore')
		{
			$num = intval($argv['syncNum']);
			$syncNum = $num >=0 ? $num : 20;
			
			$minute = intval($argv['limtMinute']);
			$limtMinute = $minute>=0 ? $minute*60 : 1140;
			
			$this->ScSyncComplainMore($syncNum,$limtMinute);
		}
		else if($argv['operation'] == 'Revoke')
		{
			$this->Revoke();
		}
		else if($argv['operation'] == 'test')
		{
			$this->test();
		}
			
	}
	/**
	 * sc投诉问题同步到coplain
	 * @param $syncNum 同步条数
	 */
	private function ScSyncComplain($syncNum)
	{
		$this->load('complain');
		$complainWarnNum = isset($this->setting['ts_warn_num']) ? intval($this->setting['ts_warn_num']) : 3; // 投诉同步阈值 默认3
		for($i=0;$i<=9;$i++)
		{
			$syncData = $_ENV['complain']->ScSyncComplainData($complainWarnNum,$syncNum);
			
			if(!empty($syncData))
			{
				$resultData = $_ENV['complain']->ComplainBackData($syncData,$this->complainKey,$this->complainBackUrl); // 获取投诉站点返回值
				$unpackData = $_ENV['complain']->unpackComplainData($resultData,$this->complainKey); // 解包投诉站点返回数据
				$failureIdArr = array();
				foreach($syncData as $value)
				{
					if(isset($unpackData[$value['id']]))
					{
						// sync表插入一条新数据, 更新complain表
						$result = $_ENV['complain']->scSyncComplainOperation($unpackData[$value['id']],$value['id']);
						if(!$result)
						{
							// 更新失败id状态
							$failureIdArr[] = $value['id'];
							$_ENV['complain']->Update($value['id'],array('sync'=>'_sync-1'));
						}
					}
					else
					{
						// 更新失败id状态
						$failureIdArr[] = $value['id'];
						$_ENV['complain']->Update($value['id'],array('sync'=>'_sync-1'));
					}
				}
				$ids = implode(',',$failureIdArr);
				$failureIdStr = empty($ids) ? "失败id: no\r\n" : "失败id: $ids\r\n";
			}
			sleep(5);
		}
	}
	/**
	 * complain站点投诉问题同步sc
	 */
	private function ComplainSyncSc()
	{
		$this->load('complain');
		$resultData = $_ENV['complain']->ComplainSyncData($this->complainSyncUrl);
		// 该投诉问题没有同步过,则插入一条新的数据,否更新该投诉问题
		$backComplainStr = $_ENV['complain']->ComplainSyncScOperation($resultData);
		// 返回值post给complain站点
		$_ENV['complain']->backComplainData($backComplainStr,$this->backComplainUrl,$this->complainKey);
	}
	// 同步complain回答问题到sc
	private function ComplainAnswerToSc($syncNum)
	{
		$this->load('complain');
		$postData = $_ENV['complain']->PostComplainData($syncNum); // post给complain站点的数据
		if(!empty($postData))
		{
			// 投诉站点返回的回答内容
			$resultData = $_ENV['complain']->complainAnswerBackData($postData,$this->complainAnswerUrl,$this->complainKey);
			if(!empty($resultData))
			{
				$_ENV['complain']->insertComplainAnswer($resultData); // 插入一条新的投诉回答
			}
		}
	}
	/**
	 * sc投诉问题同步到coplain
	 * 
	 * @param $syncNum 同步条数        	
	 * @param $limtMinute 超时退出
	 */
	private function ScSyncComplainMore($syncNum,$limtMinute) 
	{
		$startTime = time();
		$this->load ( 'complain' );
		$complainWarnNum = isset ( $this->setting ['ts_warn_num']) ? intval ( $this->setting ['ts_warn_num'] ) : 6; // 投诉同步阈值
		$doubleComplainWarnNum = 2*$complainWarnNum;    
		$complainWarnNum = -($complainWarnNum+1);
		$syncData = $_ENV ['complain']->ScSyncComplainData ( $doubleComplainWarnNum, $syncNum, $complainWarnNum );
		if(!empty($syncData))
		{
			// 超过 19分钟退出
			while(count($syncData)>0)
			{
				$failureListArr = array();
				foreach($syncData as $key=>$value)
				{
					// 超过 19分钟退出
					if(time()-$startTime<=$limtMinute)
					{
						// 每次请求一次
						$resultData = $_ENV['complain']->ComplainBackData(array($value),$this->complainKey,$this->complainBackUrl); // 获取投诉站点返回值
						$unpackData = $_ENV['complain']->unpackComplainData($resultData,$this->complainKey); // 解包投诉站点返回数据
						
						if(isset($unpackData[$value['id']]))
						{
							// sync表插入一条新数据, 更新complain表
							$result = $_ENV['complain']->scSyncComplainOperation($unpackData[$value['id']],$value['id']);
							if(!$result)
							{
								// 更新失败id状态
								$_ENV['complain']->Update($value['id'],array('sync'=>'_sync-1'));
							}
							else
							{
								unset($syncData[$key]);
							}
						}
						else
						{
							// 更新失败id状态
							$_ENV['complain']->Update($value['id'],array('sync'=>'_sync-1'));
						}
					}
					else
					{
						foreach($syncData as $v)
						{
							$failureListArr[] = $v['id'];
						}
						$failureIdStr = implode(',',$failureListArr);
						
						exit('quit');
					}
					sleep(1);
				}
				
				foreach($syncData as $v)
				{
					$failureListArr[] = $v['id'];
				}
				$failureIdStr = implode(',',$failureListArr);
			}
		}
	}	/**
	 * 连接测试
	 */
	private function test($syncNum)
	{
		$t1 = microtime(true);
		$return = file_get_contents("http://complain.5173esb.com/Sc/GetIPInfo.ashx");
		$t2 = microtime(true);
		$lag = $t2-$t1;
		$text = date("Y-m-d H:i:s")." return:".$return.",time:".$lag."\r\n";
	}	
	/**
	 * complain站点投诉问题同步sc
	 */
	private function Revoke()
	{
		$this->load('complain');
		$RevokeQueue = $_ENV['complain']->getRevokeQueue();
		foreach($RevokeQueue as $key => $value)
		{
			$id = $value['scid'];
			$ComplainInfo = $_ENV['complain']->Get($id);
			$startDate = date("Y-m-01",strtotime("-3 month",time()));
			if($ComplainInfo['time']<strtotime($startDate))
			{
				$_ENV['complain']->delRevokeQueue($id);
			}
			else
			{
				$t = array();
				foreach($value as $k => $y)
				{
					if($k!='id')
					{
						$t[] = $k."=".urlencode($y);
					}
					else
					{
						$id = $y;					
					}
				}
				$data = implode("&",$t);
				$url = "http://complain.5173.com/Sc/PostCancel.aspx";
				$data = $data."&sign=".config::TS_SIGN;
				echo "id:".$id."\n";
				$result = do_post($url,$data);
				$result_arr = json_decode($result,true);
				echo "return:".$result_arr['return']."\n";
				if($result_arr['return']==1)
				{					
					$_ENV['complain']->delRevokeQueue($id);
				}				
			}
		}		
	}		
}

new ScComplainSync();
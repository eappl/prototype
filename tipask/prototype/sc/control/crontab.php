<?php
error_reporting(0);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(dirname(__FILE__)));
ini_set('include_path', TIPASK_ROOT);

require 'config.php';
require 'lib/db.class.php';
require 'lib/global.func.php';
require 'lib/CacheMemcache.class.php';
//require 'lib/CacheRedis.class.php';
require 'lib/config.class.php';
require 'model/base.class.php';

require 'lib/pdo/Mysql/pdo.class.php';
require 'lib/pdo/Mysql/hash.class.php';
ini_set('date.timezone','Asia/Shanghai');

class crontab extends base {

    function crontab() 
    {
    	parent::__construct();
    	
        $this->load('bind_log');
		$this->load('question');
        $argv = $this->getCmdArgv(); // 获取命令行参数
        
        //根据传入的第一个变量确定执行的方法
        if($argv['operation']=="order_process")
        {
			$this->order_process();
        } 
        else if($argv['operation']=="default_assess")
        {
        	$num = intval($argv['num']);
			$limitNum = $num >0 ? $num : 100;
        	$this->default_assess($limitNum);
        }
        else if($argv['operation']=="view_update")
        {
        	$num = intval($argv['num']);
			$limitNum = $num >0 ? $num : 100;
        	$this->view_update($limitNum);
        }
        else if($argv['operation']=="history_map")
        {
        	$year = intval($argv['start']);
        	$this->history_map($year);
        }
    }
	function order_process()
	{
		$n = 1;
		while($n>0)
		{
			$orderToProcess = $_ENV['bind_log']->getOrderLog(1000);
			$n = count($orderToProcess);
			foreach($orderToProcess as $key => $value)
			{
				if($value['bind_type']==1)
				{
					$bindStatus = $_ENV['bind_log']->getBindStatus($value['author_buyer'],$value['scopid'],$value['deal_time']);
				}
				else
				{
					$bindStatus = $_ENV['bind_log']->getBindStatus($value['author_seller'],$value['scopid'],$value['deal_time']);
				}
				if(isset($bindStatus['id']))
				{
					$value['bind_time'] = $bindStatus['bind_time'];
					$value['unbind_time'] = $bindStatus['unbind_time'];
				}
				else
				{
					$value['bind_time'] = 0;
					$value['unbind_time'] = 0;				
				}
				$_ENV['bind_log']->insertOrderLog($value);				
			}
		}		
	}

	// 投诉问题默认3天好评
	function default_assess($num)
	{
		$n = 1;
		$this->load('complain');
		 while($n>0)
		 { 
			$NoAssessData = $_ENV['complain']->getAssessData($num,"id,assess,asnum,atime");
			$n = count($NoAssessData);
			
			$logData = "";
			$backInfo = array(1=>'success',2=>'failure');
			
			foreach($NoAssessData as $value)
			{
				$now = time();
				$threeDayAfter = $value['atime']+259200-$now;
				if($threeDayAfter<=0 && $value['assess']==0)
				{
					$result = $_ENV['complain']->updateAssess($value['id'],1);
					if($result)
					{
						$url  = "http://complain.5173esb.com/Sc/PostEvaluate.aspx";
						$data = "scid={$value['id']}&iJudgeInt=1&userid='defaultAssess'&sign=".config::TS_SIGN;
						do_post($url,$data);
						$logData .= $data."\r\n";
					}					
					
					$logData .= "id={$value['id']} {$backInfo[$result]} \r\n";
				}
			}
			if($logData)
			{

			}
		 } 
	}
	function view_update()
	{
		$key = "view";
		$n = 1;
		while($n>0)
		{
			for($i=0;$i<=10000;$i++)
			{
				$qid = $this->redis->RPOP($key);
				if($qid)
				{
					$qid_list[$qid]++;
				}
				else
				{
					break;
				}
			}
			break;
		}
		foreach($qid_list as $q => $n)
		{
			echo $q."-".$n."\n";
			$_ENV['question']->Update($q,array('views'=>"_views+$n"));
		}
	}
	function history_map($year)
	{
		$y = date("Y",time());
		do{
			$update = $_ENV['question']->UpdateHistoryMap($year);
			echo $year."-".intval($update)."\n";
			$year++;
		}
		while($y >= $year);
		$_ENV['question']->UpdateCurrentMap();
	}
}
$crontab = new crontab();
?>
<?php
//error_reporting(0);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(dirname(__FILE__)));
ini_set('include_path', dirname(dirname(__FILE__)));
error_reporting(0);
require 'config.php';
require 'lib/db.class.php';
require 'lib/global.func.php';
require 'lib/CacheMemcache.class.php';
require 'lib/config.class.php';
require 'model/base.class.php';

//require 'lib/pdo/Mysql/pdo.class.php';
//require 'lib/pdo/Mysql/hash.class.php';

class crontab extends base {

    function crontab() 
    {
		parent::__construct();
		$this->init_db();
        $this->load('question');
        $this->load('operator');
        $this->load('category');
		$this->load('post');
        //根据传入的第一个变量确定执行的方法
        $ac = trim($_SERVER["argv"][1]);
		if($ac=="update_operator")
        {
            $this->update_operator();
        }
        elseif($ac=="work_log")
        {
            $Date = trim($_SERVER["argv"][2]);
			if(strtotime($Date)==0)
			{
				$Date = date("Y-m-d",time()-3600);
			}
			$Hour = intval($_SERVER["argv"][3]);
			if($Hour <0 || $Hour >23 || !isset($_SERVER["argv"][3]))
			{
				$Hour = date("H",time()-3600);
			}
			$this->work_log($Date,$Hour);
        } 
    }

    //同步客服联系信息
    function work_log($Date,$Hour)
    {                
		date_default_timezone_set('Asia/Shanghai'); 
		$T = $Date." ".$Hour.":00:00";
		$StartTime = strtotime($T);
		$EndTime = $StartTime+3600;
		$return = $_ENV['operator'] -> getOnlineOperator($StartTime,$EndTime,array());
		if(count($return['operatorList'])>0)
		{
			
			$_ENV['operator'] -> DeleteWorkLog(array('Date'=>$Date,'Hour'=>$Hour));
			foreach($return['operatorList'] as $Key => $OperatorInfo)
			{
				$_ENV['operator'] -> InsertWorkLog(array('OperatorName'=>$OperatorInfo['userID'],'Date'=>$Date,'Hour'=>$Hour));
			}
		}
    }
}
$crontab = new crontab();
?>
<?php

!defined('IN_TIPASK') && exit('Access Denied');

class worktimemodel {

    var $db;
    var $base;

    function worktimemodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }   
    
    //当天是否在班
    function isToday($user,$date){
    	$info = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "worktime WHERE login_name='".$user."' AND login_time='".$date."'");
    	if(!empty($info)) return $info;
    	return false;	
    	
    }
    
    //查询操作客服的前一天的在班记录，用于统计
    function lastToday($users){
    	$list = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "worktime WHERE login_name='$users' ORDER BY login_time DESC LIMIT 1");
    	if(!empty($list)) return $list;
    	return false;
    }
    
    function get_list($where=''){	
    	$list = $this->db->fetch_all("SELECT login_name,SUM(busy_time) AS total_busy,SUM(onjob_time) AS total_job FROM " . DB_TABLEPRE . "worktime $where  GROUP BY login_name");
    	if(empty($list)) return false;
    	return $list;
    }
    
    function get_where($start_time='',$end_time='',$user_name=''){
    	$where = ' WHERE 1 ';
    	if($start_time != ''){
    		$where.= " AND login_time>='".date('Y-m-d',$start_time)."'";
    	}
    	if($end_time != ''){
    		$where.= " AND login_time<='".date('Y-m-d',$end_time)."'";
    	}
    	if($user_name != ''){
    		$where.= " AND login_name='".$user_name."'";
    	}
    	return $where;
    }
    
    function getHour($seconds){
    	if(intval($seconds)<=0){
    		return '';
    	}else{
    		$h = floor($seconds/3600);
    		$m = floor($seconds%3600/60);
    		$s = $seconds%3600%60;
    		$m<10 && $m='0'.$m;
    		$s<10 && $s='0'.$s;
    		return $h.': '.$m.': '.$s;
    	}   
    }
	function insertWorkTime($workInfo)
    {
    	foreach($workInfo as $key => $value)
    	{
    		$array_key[$key] = $key;
    		$array_value[$key] = "'".$value."'";
    	}
    	$sql = "insert into " .DB_TABLEPRE."worktime (".implode($array_key,",").") values (".implode($array_value,",").")";
    	return $this->db->query($sql);
    }
    function updateWorkTime($id,$workInfo)
    {
    	foreach($workInfo as $key => $value)
    	{
    		$txt[$key] = "`".$key."`='".$value."'";
    	}
    	$sql = "update ".DB_TABLEPRE."worktime set ".implode($txt,",")." where id = ".intval($id);
    	return $this->db->query($sql);
    }
	function worktimeModify($login_name,$onjob,$busy)
	{
		//在班
		if($onjob==1)
		{
			//转为忙碌
			if($busy==1)
			{
				$this->busyStatusRemoteModify($login_name,$busy);
				$busy_start = time();//记录点忙碌状态为是的时间
				$istoday = $this->isToday($login_name,date('Y-m-d'));
				if(false === $istoday)
				{
					$lasttoday = $this->lastToday($login_name);
					if(false !== $lasttoday)
					{
						$last_job_time = strtotime($lasttoday['login_time']) + 24*3600 - $lasttoday['onjob_start'];//统计前一天在班的在班时间
						$job_time = $busy_start - strtotime(date('Y-m-d'));//统计当天在班的在班时间
						$updateArr = array('onjob_time'=>$last_job_time+$lasttoday['onjob_time'],'onjob_start'=>strtotime(date('Y-m-d')));
						$update = $this->updateWorkTime($lasttoday['id'],$updateArr);//更新前一天在班的忙碌时间以及在班时间
						$date = $lasttoday['login_time'];
						do
						{
							$insertArr = array('login_name'=>$login_name,'login_time'=>$date,'onjob_time'=>86400,'onjob_start'=>strtotime($date),'busy_start'=>strtotime($date));
							$Insert = $this->insertWorkTime($insertArr);//插入今天和起始天之间每天的工作数据，全天在班不忙碌
							$date = date("Y-m-d",strtotime($date)+86400);
						}
						while($date<date('Y-m-d'));
						$insertArr = array('login_name'=>$login_name,'login_time'=>date('Y-m-d'),'onjob_time'=>$lasttoday['onjob_time']+$job_time,'onjob_start'=>$busy_start,'busy_start'=>$busy_start);
						$Insert = $this->insertWorkTime($insertArr);//插入当天在班时间，并统计当天的忙碌时间以及在班时间
					}
				}
				else
				{
					$updateArr = array('busy_start'=>$busy_start);
					$update = $this->updateWorkTime($istoday['id'],$updateArr);
				}
			}
			else//转为空闲
			{
				$this->busyStatusRemoteModify($login_name,$busy);
				$busy_end = time();//记录点忙碌状态为否的时间
				$istoday = $this->isToday($login_name,date('Y-m-d'));
				if(false === $istoday)
				{//点忙碌与非忙碌不是同一天
					$lasttoday = $this->lastToday($login_name);
					if(false !== $lasttoday)
					{
						$last_busy_time = strtotime($lasttoday['login_time']) + 24*3600 - $lasttoday['busy_start'];//统计前一天在班的忙碌时间
						$last_job_time = strtotime($lasttoday['login_time']) + 24*3600 - $lasttoday['onjob_start'];//统计前一天在班的在班时间
						$busy_time = $busy_end - strtotime(date('Y-m-d'));//统计当天在班的忙碌时间
						$updateArr = array('busy_time'=>$lasttoday['busy_time']+$last_busy_time,'onjob_time'=>$lasttoday['onjob_time']+$last_job_time,'onjob_start'=>strtotime(date('Y-m-d')),'busy_start'=>strtotime(date('Y-m-d')));
						$update = $this->updateWorkTime($lasttoday['id'],$updateArr);//更新前一天在班的忙碌时间
						$date = $lasttoday['login_time'];
						do
						{
							$insertArr = array('login_name'=>$login_name,'login_time'=>$date,'onjob_time'=>86400,'onjob_start'=>strtotime($date),'busy_start'=>strtotime($date)+86400-1);
							$Insert = $this->insertWorkTime($insertArr);//插入今天和起始天之间每天的工作数据，全天在班忙碌
							$date = date("Y-m-d",strtotime($date)+86400);
						}
						while($date<date('Y-m-d'));
						$insertArr = array('login_name'=>$login_name,'login_time'=>date('Y-m-d'),'onjob_time'=>$lasttoday['onjob_time']+$busy_time,'busy_time'=>$lasttoday['busy_time']+$busy_time,'onjob_start'=>$busy_end,'busy_start'=>$busy_end);
						$Insert = $this->insertWorkTime($insertArr);//插入当天在班时间，并统计当天的忙碌时间
					}
				}
				else
				{//同一天处理
					$busy_time = $busy_end - $istoday['busy_start'];
					$job_time = $busy_end - $istoday['onjob_start'];
					$updateArr = array('busy_time'=>$istoday['busy_time']+$busy_time,'onjob_time'=>$istoday['onjob_time']+$job_time,'onjob_start'=>$busy_end,'busy_start'=>$busy_end);
					$update = $this->updateWorkTime($istoday['id'],$updateArr);//更新前一天在班的忙碌时间
				}			
			}
		}
		else//不在班
		{
			
		}
	}
	function busyStatusRemoteModify($login_name,$busy)
	{
		$url = "http://complain.5173.com/sc/AsycWorkStatus.ashx?userID=".urlencode($login_name)."&isBusy=".intval($busy);
		return file_get_contents($url);
	}

}

?>

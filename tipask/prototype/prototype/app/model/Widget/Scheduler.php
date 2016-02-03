<?php
/**
 * 调度数据操作
 * 
 * $Id: 
 * @category   Cron_Scheduler
 * @Description
 * @author   陈晓东
 * @version
 */
class Widget_Scheduler extends Base_Widget
{
	protected $table = 'config_scheduler_task';

	//取得所有任务
	public function GetAllTask()
	{
		$table=$this->getDbTable();
		$sql =  " SELECT * from $table ";
		return $this->db->getAll($sql);
	}

	//取得任务
	public function GetTaskByType($type)
	{
		$table=$this->getDbTable();
		$sql =  " SELECT * from $table where type=:type";
		return $this->db->getAll($sql,array(':type'=>$type));
	}
	
	//任务开始
	public function SetTaskStart($task)
	{
		$table=$this->getDbTable();
		$sql =  "update $table set first_time=now(),last_time=now(),cnt=cnt+1,status='runing' where name=:task";
		return $this->db->query($sql,array(':task'=>$task));
	}
	
	//任务结束
	public function SetTaskEnd($param)
	{
		$table=$this->getDbTable();
		$sql =  " select count(1)>0 as runing from $table ".
				" where status='runing' and name=:name";
		$row=$this->db->getOne($sql,$param);
		if(!$row['runing'])
			return false;
			
		$sql =  "update $table set status='idel' where status='runing' and name=:name";
		$this->db->query($sql,$param);
		
		$this->PutLog($param[':name'],"{$param[':name']} end.\n");
		
	}
	
	//记录日志
	public function PutLog($task,$context)
	{
		$table=$this->getDbTable();
		$sql =  "insert into {$table}_log(task,context,time)values(:task,:context,now())";

		return $this->db->query($sql,array(':task'=>$task,':context'=>$context));
	}
	
	//记录任务日志
	public function PutTaskLog($param)
	{
		$table=$this->getDbTable();
		$sql =  "insert into {$table}_log(task,context,time)values(:task,:context,now())";
		return $this->db->query($sql,$param);
	}
	
	
	//更新进程运行信息
	public function UpdateRuningTask($param)
	{
		$table=$this->getDbTable();
		$sql =  "update $table set status=:status,last_time=now(),pid=:pid where name=:name";
		return $this->db->query($sql,$param);
	}

	//取得运行时间超时任务
	public function GetTimeoutTask()
	{
		$table=$this->getDbTable();
		$sql =  "SELECT * ".
				"from $table ".
				"where status='runing' and limit_second>0 ".
				"and DATE_ADD(first_time, INTERVAL limit_second SECOND)<now()";
		return $this->db->getAll($sql);
	}
	
	//进程执行时间是否已经超过
	public function IsTimeout($name)
	{
		$table=$this->getDbTable();
		$sql =  " SELECT count(1)>0 as IsTimeout ".
				" from $table ".
				" where status='runing' and limit_second>0 ".
				" and DATE_ADD(first_time, INTERVAL limit_second SECOND)<now() ".
				" and name=:name";
		$row=$this->db->getOne($sql,array(':name'=>$name));
		return $row['IsTimeout'];
	}
	

	//任务是否到期需要执行
	public function TasksIsMature($name)
	{
		$table=$this->getDbTable();
		//固定间隔
		$sql =  " SELECT count(1)>0 as TasksIsMature ".
				" from $table ". 
				" where type='timer' and `interval`>0 ".
				" and DATE_ADD(first_time, INTERVAL `interval` SECOND)<now() and name=:name ";
		$row=$this->db->getOne($sql,array(':name'=>$name));
		if($row['TasksIsMature'])
			return true;
			
		//周期
		$sql =  " SELECT count(1)>0 as TasksIsMature ".
				" from $table ". 
				" where type='cron' ".
				" and (minute=MINUTE(now()) or minute='*') ".
				" and (hour=hour(now()) or hour='*') ".
				" and (day=day(now()) or day='*') ".
				" and (month=MONTH(now()) or month='*') ".
				" and (week=WEEKDAY(now())+1 or week='*') ".
				" and not(	MINUTE(first_time)=MINUTE(NOW()) and ".
				" 			HOUR(first_time)=HOUR(NOW()) and ".
				" 			DAY(first_time)=DAY(NOW()) and ".
				" 			MONTH(first_time)=MONTH(NOW()) and ".
				" 			WEEKDAY(first_time)+1=WEEKDAY(NOW())+1 ) ".
				" and name=:name ";
		$row=$this->db->getOne($sql,array(':name'=>$name));
		if($row['TasksIsMature'])
			return true;
			
		//定时一次性
		$sql =  " SELECT count(1)>0 as TasksIsMature ".
				" from $table ". 
				" where type='once' ".
				" and cnt<1 and start_time<=now() ".
				" and name=:name ";
		$row=$this->db->getOne($sql,array(':name'=>$name));
		if($row['TasksIsMature'])
			return true;
			
		return false;
	}
	
	//插入
	public function insert($bind)
	{
		$table=$this->getDbTable();
		return $this->db->insert($table, $bind);
	}
	
	//取出
	public function getRow($name, $fields = '*')
	{
		$table=$this->getDbTable();
		$sql = "SELECT $fields FROM {$table} WHERE `name` = ?";
		$task = $this->db->getRow($sql, $name);

		return $task;
	}
	//更新
	public function update($name, $bind)
	{
		$table=$this->getDbTable();
		return $this->db->update($table, $bind, '`name` = ?', $name);
	}
	//删除
	public function delete($name)
	{
		$table=$this->getDbTable();
		return $this->db->delete($table, '`name` = ?', $name);
	}
}

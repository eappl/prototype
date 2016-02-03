<?php
/**
 * 服务器数据
 * @author Chen<cxd032404@hotmail.com>
 * $Id: SocketQueue.php 15195 2014-07-23 07:18:26Z 334746 $
 */
class Config_SocketQueue extends Base_Widget
{
	/**
	 * server表
	 * @var string
	 */
	protected $table = "socket_queue";
	protected $table_date = "socket_queue_date";
	/**
	 * 获取单条记录
	 * @param integer $QueueId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($QueueId, $fields = '*')
	{
		return $this->db->selectRow($this->getDbTable($this->table), $fields, '`QueueId` = ?', $QueueId);
	}
	
	/**
	 * 获取单个字段
	 * @param integer $QueueId
	 * @param string $field
	 * @return string
	 */
	public function getOne($QueueId, $field)
	{
		return $this->db->selectOne($this->getDbTable($this->table), $field, '`QueueId` = ?', $QueueId);
	}
	
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		return $this->db->insert($this->getDbTable($this->table), $bind);
	}

	/**
	 * 删除
	 * @param integer $QueueId
	 * @return boolean
	 */
	public function delete($QueueId,$uType = 0)
	{
	    if($uType)//在删除公告的时候 需要两个条件 保证正确性
        {
            return $this->db->delete($this->getDbTable($this->table), '`QueueId` = ? AND `uType` = ?', array($QueueId,$uType));
        }else
        {
            return $this->db->delete($this->getDbTable($this->table), '`QueueId` = ? ', $QueueId);
        }
		
	}

	
	public function getAll($fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable($this->table) . " ORDER BY QueueId DESC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllQueue[$value['QueueId']] = $value;	
			}	
		}
		return $AllQueue;
	}
    /**
	 * 根据socket类型获取所有还未发送的socket队列
	 * @param string $fields
	 * @return array
	 */
	public function getAllByuType($uType,$fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable($this->table) . " WHERE uType = '$uType' ORDER BY QueueId ASC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllQueue[$value['QueueId']] = $value;	
			}	
		}
		return $AllQueue;
	}
    public function createSocketQueueTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_date);
		$table_to_process = Base_Widget::getDbTable($this->table_date)."_".$Date;
		$exist = $this->db->checkTableExist($table_to_process);
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE " . $table_to_check;
			$row = $this->db->getRow($sql);
			$sql = $row['Create Table'];
			$sql = str_replace('`' . $this->table_date. '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
			$create = $this->db->query($sql);
			if($create)
			{
				return $table_to_process;
			}
			else
			{
			 	return false;	
			}		 	
		}
	}
    
    //获取发送socket
    public function getSendSocket($ServerId,$uType,$limit,$fields = "*")
    {
        $nowtime = time();
        $whereuType = $uType?' and uType in ('.$uType.') ':'';

        $sql = "SELECT $fields FROM " . $this->getDbTable($this->table) . " where ServerId = $ServerId $whereuType and SendTime = 0 and QueueId > 0 and QueueTime <= '$nowtime' ORDER BY QueueTime LIMIT 0,$limit";
        $return = $this->db->getAll($sql,array(),true);
        $AllQueue = array();
        if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllQueue[$value['QueueId']] = $value;	
			}	
		}
		return $AllQueue;
    }
    
    //更新发送时间
    public function updateSendTime($QueueId)
    {
        $bind['SendTime'] = time();
        $param = array($QueueId);
        return $this->db->update($this->getDbTable($this->table), $bind, '`QueueId` = ?', $param);
    }
    
    public function MoveSocketQueue($bind)
    {
        $bind["SendTime"] = time();
        $Date = date("Ymd",$bind["SendTime"]);
        $this->db->begin();
        
        $tablename = $this->createSocketQueueTable($Date);
        $insert = $this->db->insert($tablename,$bind);
        $delete = $this->delete($bind['QueueId']);
        
        if($insert && $delete)
        {
            $this->db->commit();
            return true;
        }
        else
        {
            $this->db->rollBack();
            return false;
        }
    }
    public function getSendStatus($Date,$ServerId,$uType)
    {
		$select_fields = array('SendCount'=>'count(*)','H'=>'from_unixtime(Sendtime,"%H")','m'=>'from_unixtime(SendTime,"%i")');

        $whereuType = $uType?' uType = '.$uType.' ':'';
        $whereServer = $ServerId?' ServerId = '.$ServerId.' ':'';    		
		$whereCondition = array($whereuType,$whereServer);
		$group_fields = array('H','m');
		
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//分类统计列

		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
				
		$table_to_process = $this->getDbTable($this->table_date)."_".date("Ymd",strtotime($Date));
		
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$groups;
	   	$return = $this->db->getAll($sql);
	   	$SendStatus = array();
	   	if(count($return))
	   	{
	   		foreach($return as $key => $value)
	   		{
	   			$SendStatus[$value['H']*60+$value['m']]['SendCount'] = $value['SendCount'];	
	   		}	
	   	}
	   	return $SendStatus;
	   	


    }
}

<?php
/**
 * 机柜机体数据
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Machine.php 15195 2014-07-23 07:18:26Z 334746 $
 */
class Config_Machine extends Base_Widget
{
	/**
	 * server表
	 * @var string
	 */
	protected $table = "machine";

	/**
	 * 获取单条记录
	 * @param integer $MachineCode
	 * @param string $fields
	 * @return array
	 */
	public function getRow($MachineId, $fields = '*')
	{
		return $this->db->selectRow($this->getDbTable(), $fields, '`MachineId` = ?', $MachineId);
	}
	
	/**
	 * 获取单个字段
	 * @param integer $MachineCode
	 * @param string $field
	 * @return string
	 */
	public function getOne($MachineId, $field)
	{
		return $this->db->selectOne($this->getDbTable(), $field, '`MachineId` = ?', $MachineId);
	}
	
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
	
		return $this->db->insert($this->getDbTable(), $bind);
	}

	/**
	 * 删除
	 * @param integer $MachineCode
	 * @return boolean
	 */
	public function delete($MachineId)
	{
		return $this->db->delete($this->getDbTable(), '`MachineId` = ?', $MachineId);
	}

	/**
	 * 获取所有区服
	 * @param string $fields
	 * @return array 
	 */
	public function getAll($fields = '*')
	{		
		return $this->db->select($this->getDbTable(), $fields);
	}

	/**
	 * 按游戏ID查询
	 * @param $AppId
	 * @param $fields
	 * @return array
	 */
	public function getByApp($AppId, $fields = "*")
	{
		$sql = "SELECT $fields FROM {$this->table} WHERE `AppId` = ? ORDER BY MachineId ASC";
		return $this->db->getAll($sql, $AppId);
	}
	
	/**
	 * 慎用
	 * @param string $fields
	 * @param array $param
	 * @return array
	 */
	public function getByParam($param , $fields = '*')
	{
		$table_to_process = $this->getDbTable();
		$condition="where 1=1 ";
		foreach($param as $k => $v)
			if(!empty($v))
				$condition.=" and $k='$v' ";

		
		$sql = "SELECT $fields FROM $table_to_process  $condition ";
		return  $this->db->getAll($sql);
	}
	
	/**
	 * 更新单条区服数据
	 * @param integer $MachineCode
	 * @param array $bind
	 * @return boolean
	 */
	public function update($MachineId, array $bind)
	{
		return $this->db->update($this->getDbTable(), $bind, '`MachineId` = ?', $MachineId);
	}
	public function getMachinePosition($CageId,$Position)
	{
		$sql = "SELECT Position FROM {$this->table} WHERE `CageId` = ".$CageId." AND Position > ".$Position." ORDER BY Position ASC limit 1";
		return $this->db->getOne($sql);
	}
	//
	public function getCurrentByCageId($CageId)
	{
		$sql = "SELECT sum(Current) as count FROM {$this->table} WHERE `CageId` in (".$CageId.")";
		return $this->db->getOne($sql);		
	}
	public function getMachineCountByCageId($CageId)
	{
		$sql = "SELECT count(MachineCode) as count FROM {$this->table} WHERE `CageId` = ".$CageId;
		return $this->db->getOne($sql);		
	}
	
	public function getMachineByCageId($CageId,$fields = '*')
	{
		$sql = "SELECT $fields FROM {$this->table} WHERE `CageId` = ".$CageId." ORDER BY Position ASC";
		return $this->db->getAll($sql);		
	}
	
	//获取列表页搜索数据
  public function getMachineParams($MachineCode,$EstateCode,$MachineName,$Version,$CageId,$AppId,$ServerId,$LocalIP,$WebIP,$User,$field,$order,$Flag,$Owner,$start,$pageSize)
  {
		//初始化查询条件
		$whereMachineCode = $MachineCode? "MachineCode LIKE '%".$MachineCode."%'":"";
		$whereEstateCode = $EstateCode? "EstateCode LIKE '%".$EstateCode."%'":"";
		$whereMachineName = $MachineName? "MachineName LIKE '%".$MachineName."%'":"";
		$whereVersion = $Version? "Version LIKE '%".$Version."%'":"";
		$whereOwner = $Owner? "Owner = '".$Owner."'":"";
		$whereCageId = "";
		if($CageId)
		{
			if(strchr($CageId,","))
			{				
				$whereCageId = "CageId in ( $CageId )";
			}else
			{
				$whereCageId = "CageId = $CageId";		
			}		
		}

		$whereServerId = "";
		if($AppId || $ServerId)  
		{
			if(strchr($ServerId,","))
			{				
				$whereServerId = "ServerId in ( $ServerId )";
			}else
			{			
				$whereServerId = $ServerId ?"ServerId = $ServerId":"ServerId =''";		//存在有运营商但没有服务器的情况
			}		
		}
		$whereUser = $User? "User LIKE '%".$User."%'":"";
		$limit = $pageSize?" limit $start,$pageSize":"";
		if($Flag)
		{
			if($Flag==8)//8表示网络设备
			{
				$whereFlag = "Flag in ('2','3','4') ";								
			}else
			{			
				$whereFlag = "Flag = ".$Flag;		
			}
						
		}
		if($field)
		{
			$order = " ORDER BY $field $order";
		}else
		{
			$order = " ORDER BY Udate DESC";
		}
		 
		$whereCondition = array($whereMachineCode,$whereEstateCode,$whereMachineName,$whereVersion,$whereCageId,$whereServerId,$whereLocalIP,$whereUser,$whereFlag,$whereOwner);		
				
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		if($LocalIP!="")
		{
			if($LocalIP=='zero')
			{
				$LocalIP = '0';
			}
			$where .= " AND LocalIP = ".$LocalIP;
		}
		if($WebIP!="")
		{
			if($WebIP=='zero')
			{
				$WebIP = '0';
			}
			$where .= " AND WebIP = ".$WebIP;
		}
		
		//查询列 查询总记录数
   	$select_fields_count = array('MachineCount'=>'count(*)');
		$fields = Base_common::getSqlFields($select_fields_count);
		$sql = "SELECT $fields FROM {$this->table} where 1 ".$where; 		
		
		$MachineCount = $this->db->getOne($sql,false);
		
		if($MachineCount)
		{
			$select_fields = array('*');
	   	//生成查询列
			$fields = Base_common::getSqlFields($select_fields);	
	    $sql = "SELECT $fields FROM {$this->table} where 1 ".$where.$order.$limit;
	    //echo $sql."<br/>";
	    $MachineArr = $this->db->getAll($sql);    				
		}
		$MachineList['MachineDetail'] = $MachineArr;
    $MachineList['MachineCount'] = $MachineCount;
    return  $MachineList;
  }
  
  public function getIpList($CageIdList,$start,$pageSize)
  {
  	if($CageIdList)
		{			
			$whereCageId = "CageId in ( $CageIdList )";	
		}
		$limit = $pageSize?" limit $start,$pageSize":"";
  	//生成条件列
		$where = Base_common::getSqlWhere(array($whereCageId));
		$sql = "SELECT count(*) as MachineCount FROM {$this->table} where 1 ".$where; 		
		$MachineCount = $this->db->getOne($sql,false);
		if($MachineCount)
		{
			$sql = "SELECT MachineId,MachineCode,EstateCode,LocalIP,WebIP,Purpose,ServerId FROM  {$this->table} where 1 ".$where." ORDER BY Udate DESC ".$limit;
		
			$MachineArr = $this->db->getAll($sql); 
		}		
		$MachineList['MachineDetail'] = $MachineArr;
    $MachineList['MachineCount'] = $MachineCount;
    return  $MachineList;
  }
  
  //根据序列号或资产编码来查出一条数据 MachineCode EstateCode
  public function getRowByKey($key, $val)
	{
		if($key!="" && $val!="")
		{
			return $this->db->selectRow($this->getDbTable(), '*', "`$key` = ?", $val);			
		}
	}
}

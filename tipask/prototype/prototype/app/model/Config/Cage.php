<?php
/**
 * Cage配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Cage.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Cage extends Base_Widget
{
	/**
	 * Cage表名
	 * @var string
	 */
	protected $table = 'cage';

	/**
	 * 获取单条记录
	 * @param integer $CageId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($CageId,$field = '*')
	{
		$CageId = intval($CageId);
		return $this->db->selectRow($this->getDbTable(), $field, '`CageId` = ?', array($CageId));
	}

	/**
	 * 获取单个字段
	 * @param integer $CageId
	 * @param string $field
	 * @return string
	 */
	public function getOne($CageId,$field)
	{
		$CageId = intval($CageId);
		return $this->db->selectOne($this->getDbTable(), $field, '`CageId` = ?', array($CageId));
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
	 * @param integer $CageId
	 * @return boolean
	 */
	public function delete($CageId)
	{
		$CageId = intval($CageId);

		return $this->db->delete($this->getDbTable(),'`CageId` = ?', array($CageId));
	}

	/**
	 * 更新
	 * @param integer $CageId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($CageId, array $bind)
	{
		$CageId = intval($CageId);

		return $this->db->update($this->getDbTable(), $bind, '`CageId` = ?', array($CageId));
	}

	public function getAll($DepotId = 0)
	{
		if($DepotId)
		{
			$sql = "SELECT * FROM " . $this->getDbTable() . " where DepotId = ? ORDER BY Udate DESC,Cageid DESC";
			$return = $this->db->getAll($sql,$DepotId);
		}
		else
		{
			$sql = "SELECT * FROM " . $this->getDbTable() . " ORDER BY Udate DESC,Cageid DESC";
			$return = $this->db->getAll($sql);		
		}
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllCage[$value['DepotId']][$value['CageId']] = $value;	
			}	
		}
		return $AllCage;
	}
	public function getUsedLine($DepotId)
	{
		//查询列
		$select_fields = array(
		'Y','X');

		//初始化查询条件
		$whereDepot = $DepotId?" DepotId = ".$DepotId." ":"";
		$whereCondition = array($whereDepot);
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		
    $table_name = Base_Widget::getDbTable($this->table);
    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where;
		$UsedArr = $this->db->getAll($sql,false);
		foreach($UsedArr as $key => $Stat)
		{
			$StatArr[$Stat['X']] = $Stat;	
		}
		return $StatArr;    
	}
	public function getAllCountDepot()
	{
		$sql = "SELECT DepotId, count(*) AS count FROM " . $this->getDbTable() . " GROUP BY DepotId";		
		$return = $this->db->getAll($sql);	
		$arr = array();	
		foreach($return as $k=> $v)
		{
			$arr[$v['DepotId']] = $v["count"];		
		}
		return $arr;
	}
	public function getCageListParams($DepotId,$X,$fields = "*")
	{
		$whereDepot = $DepotId?" DepotId = ".$DepotId." ":"";
		$whereX = $X?" X = '".$X."' ":"";
		$whereCondition = array($whereDepot,$whereX);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM ".$this->getDbTable()." where 1 ".$where;
		$CageList = $this->db->getAll($sql);		
		$returnArr = array();
		foreach($CageList as $k=> $v)
		{
			$returnArr[$v["CageId"]] = $v;			
		}
		return $returnArr;
	}
	//
	public function getRowByCageCode($CageCode,$field = '*')
	{
		$CageCode = trim($CageCode);
		return $this->db->selectRow($this->getDbTable(), $field, '`CageCode` = ?', array($CageCode));
	}
}

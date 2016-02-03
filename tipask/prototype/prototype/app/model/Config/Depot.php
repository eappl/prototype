<?php
/**
 * Depot配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Depot.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Depot extends Base_Widget
{
	/**
	 * Depot表名
	 * @var string
	 */
	protected $table = 'depot';

	/**
	 * 获取单条记录
	 * @param integer $DepotId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($DepotId,$field = '*')
	{
		$DepotId = intval($DepotId);
		return $this->db->selectRow($this->getDbTable(), $field, '`DepotId` = ?', array($DepotId));
	}

	/**
	 * 获取单个字段
	 * @param integer $DepotId
	 * @param string $field
	 * @return string
	 */
	public function getOne($DepotId,$field)
	{
		$DepotId = intval($DepotId);
		return $this->db->selectOne($this->getDbTable(), $field, '`DepotId` = ?', array($DepotId));
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
	 * @param integer $DepotId
	 * @return boolean
	 */
	public function delete($DepotId)
	{
		$DepotId = intval($DepotId);

		return $this->db->delete($this->getDbTable(),'`DepotId` = ?', array($DepotId));
	}

	/**
	 * 更新
	 * @param integer $DepotId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($DepotId, array $bind)
	{
		$DepotId = intval($DepotId);

		return $this->db->update($this->getDbTable(), $bind, '`DepotId` = ?', array($DepotId));
	}

	public function getAll()
	{
		$sql = "SELECT * FROM " . $this->getDbTable() . " ORDER BY Udate DESC,DepotId DESC";
		$return = $this->db->getAll($sql);		
		foreach($return as $key => $value)
		{
			$AllDepot[$value['DepotId']] = $value;	
		}	
		return $AllDepot;
	}
	public function getRowByName($name,$field = '*')
	{
		$name = trim($name);
		return $this->db->selectRow($this->getDbTable(), $field, '`name` = ?', array($name));
	}
}

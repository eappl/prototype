<?php
/**
 * Map配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Map.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Map extends Base_Widget
{
	/**
	 * map表名
	 * @var string
	 */
	protected $table = 'config_map';

	/**
	 * 获取单条记录
	 * @param integer $MapId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($MapId, $fields = '*')
	{
		$MapId = intval($MapId);

		return $this->db->selectRow($this->getDbTable(), $fields, '`MapId` = ?', $MapId);
	}

	/**
	 * 获取单个字段
	 * @param integer $MapId
	 * @param string $field
	 * @return string
	 */
	public function getOne($MapId, $field)
	{
		$MapId = intval($MapId);

		return $this->db->selectOne($this->getDbTable(), $field, '`MapId` = ?', $MapId);
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
	 * @param integer $MapId
	 * @return boolean
	 */
	public function delete($MapId)
	{
		$MapId = intval($MapId);

		return $this->db->delete($this->getDbTable(), '`MapId` = ?', $MapId);
	}

	/**
	 * 更新
	 * @param integer $MapId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($MapId, array $bind)
	{
		$MapId = intval($MapId);

		return $this->db->update($this->getDbTable(), $bind, '`MapId` = ?', $MapId);
	}

	public function getAll($fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY MapId ASC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllMap[$value['MapId']] = $value;	
			}	
		}
		return $AllMap;
	}

}

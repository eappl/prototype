<?php
/**
 * InstMap配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Instmap.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_InstMap extends Base_Widget
{
	/**
	 * InstMap表名
	 * @var string
	 */
	protected $table = 'game_instmap';

	/**
	 * 获取单条记录
	 * @param integer $InstMapId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($InstMapId,$AppId,$field = '*')
	{
		$InstMapId = intval($InstMapId);
		$AppId = intval($AppId);

		return $this->db->selectRow($this->getDbTable(), $field, '`InstMapId` = ? and `AppId` = ?', array($InstMapId,$AppId));
	}

	/**
	 * 获取单个字段
	 * @param integer $InstMapId
	 * @param string $field
	 * @return string
	 */
	public function getOne($InstMapId,$AppId,$field)
	{
		$InstMapId = intval($InstMapId);
		$AppId = intval($AppId);

		return $this->db->selectOne($this->getDbTable(), $field, '`InstMapId` = ? and `AppId` = ?', array($InstMapId,$AppId));
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
	 * @param integer $InstMapId
	 * @return boolean
	 */
	public function delete($InstMapId,$AppId)
	{
		$InstMapId = intval($InstMapId);
		$AppId = intval($AppId);

		return $this->db->delete($this->getDbTable(),'`InstMapId` = ? and `AppId` = ?', array($InstMapId,$AppId));
	}

	/**
	 * 更新
	 * @param integer $InstMapId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($InstMapId,$AppId, array $bind)
	{
		$InstMapId = intval($InstMapId);
		$AppId = intval($AppId);

		return $this->db->update($this->getDbTable(), $bind, '`InstMapId` = ? and `AppId` = ?', array($InstMapId,$AppId));
	}

	public function getAll($AppId,$fields = "*")
	{
		if($AppId)
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " where AppId = ? ORDER BY AppId,InstMapId ASC";
			$return = $this->db->getAll($sql,$AppId);
		}
		else
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY AppId,InstMapId ASC";
			$return = $this->db->getAll($sql);		
		}
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllInstMap[$value['AppId']][$value['InstMapId']] = $value;	
			}	
		}
		return $AllInstMap;
	}

}

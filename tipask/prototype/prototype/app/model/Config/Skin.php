<?php
/**
 * Skin配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Skin.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Skin extends Base_Widget
{
	/**
	 * Skin表名
	 * @var string
	 */
	protected $table = 'game_skin';

	/**
	 * 获取单条记录
	 * @param integer $SkinId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($SkinId,$AppId,$field = '*')
	{
		$SkinId = intval($SkinId);
		$AppId = intval($AppId);
		return $this->db->selectRow($this->getDbTable(), $field, '`SkinId` = ? and `AppId` = ?', array($SkinId,$AppId));
	}

	/**
	 * 获取单个字段
	 * @param integer $SkinId
	 * @param string $field
	 * @return string
	 */
	public function getOne($SkinId,$AppId,$field)
	{
		$SkinId = intval($SkinId);
		$AppId = intval($AppId);

		return $this->db->selectOne($this->getDbTable(), $field, '`SkinId` = ? and `AppId` = ?', array($SkinId,$AppId));
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
	 * @param integer $SkinId
	 * @return boolean
	 */
	public function delete($SkinId,$AppId)
	{
		$SkinId = intval($SkinId);
		$AppId = intval($AppId);

		return $this->db->delete($this->getDbTable(),'`SkinId` = ? and `AppId` = ?', array($SkinId,$AppId));
	}

	/**
	 * 更新
	 * @param integer $SkinId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($SkinId,$AppId, array $bind)
	{
		$SkinId = intval($SkinId);
		$AppId = intval($AppId);

		return $this->db->update($this->getDbTable(), $bind, '`SkinId` = ? and `AppId` = ?', array($SkinId,$AppId));
	}

	public function getAll($AppId,$fields = "*")
	{
		if($AppId)
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " where AppId = ? ORDER BY AppId,SkinId ASC";
			$return = $this->db->getAll($sql,$AppId);
		}
		else
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY AppId,SkinId ASC";
			$return = $this->db->getAll($sql);		
		}
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllSkin[$value['AppId']][$value['SkinId']] = $value;	
			}	
		}
		return $AllSkin;
	}

}

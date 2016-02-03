<?php
/**
 * 道具阀值配置管理
 */

class Config_ItemSeal extends Base_Widget
{
	/**
	 * Item表名
	 * @var string
	 */
	protected $table = 'game_item_seal';

	/**
	 * 获取单条记录
	 * @param integer $ItemId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($ItemId,$AppId,$field = '*')
	{
		$ItemId = intval($ItemId);
		$AppId = intval($AppId);

		return $this->db->selectRow($this->getDbTable(), $field, '`ItemId` = ? and `AppId` = ?', array($ItemId,$AppId));
	}

	/**
	 * 获取单个字段
	 * @param integer $ItemId
	 * @param string $field
	 * @return string
	 */
	public function getOne($ItemId,$AppId,$field)
	{
		$ItemId = intval($ItemId);
		$AppId = intval($AppId);

		return $this->db->selectOne($this->getDbTable(), $field, '`ItemId` = ? and `AppId` = ?', array($ItemId,$AppId));
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
	 * 替换
	 * @param array $bind
	 * @return boolean
	 */
	public function replace(array $bind)
	{
		return $this->db->replace($this->getDbTable(), $bind);
	}

	/**
	 * 删除
	 * @param integer $ItemId
	 * @return boolean
	 */
	public function delete($ItemId,$AppId)
	{
		$ItemId = intval($ItemId);
		$AppId = intval($AppId);

		return $this->db->delete($this->getDbTable(),'`ItemId` = ? and `AppId` = ?', array($ItemId,$AppId));
	}

	/**
	 * 更新
	 * @param integer $ItemId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($ItemId,$AppId, array $bind)
	{
		$ItemId = intval($ItemId);
		$AppId = intval($AppId);

		return $this->db->update($this->getDbTable(), $bind, '`ItemId` = ? and `AppId` = ?', array($ItemId,$AppId));
	}

	public function getAll($AppId,$fields = "*")
	{
		if($AppId)
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " where AppId = ? ORDER BY AppId,ItemId ASC";
			$return = $this->db->getAll($sql,$AppId);
		}
		else
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY AppId,ItemId ASC";
			$return = $this->db->getAll($sql);		
		}
        
		return $return;
	}

}

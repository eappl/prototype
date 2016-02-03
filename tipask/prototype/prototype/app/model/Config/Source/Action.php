<?php
/**
 * SourceAction配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Action.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Source_Action extends Base_Widget
{
	/**
	 * SourceAction表名
	 * @var string
	 */
	protected $table = 'user_source_action';

	/**
	 * 获取单条记录
	 * @param integer $SourceActionId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($SourceActionId,$field = '*')
	{
		$SourceActionId = intval($SourceActionId);
		return $this->db->selectRow($this->getDbTable(), $field, '`SourceActionId` = ?', array($SourceActionId));
	}

	/**
	 * 获取单个字段
	 * @param integer $SourceActionId
	 * @param string $field
	 * @return string
	 */
	public function getOne($SourceActionId,$field)
	{
		$SourceActionId = intval($SourceActionId);
		return $this->db->selectOne($this->getDbTable(), $field, '`SourceActionId` = ?', array($SourceActionId));
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
	 * @param integer $SourceActionId
	 * @return boolean
	 */
	public function delete($SourceActionId)
	{
		$SourceActionId = intval($SourceActionId);

		return $this->db->delete($this->getDbTable(),'`SourceActionId` = ?', array($SourceActionId));
	}

	/**
	 * 更新
	 * @param integer $SourceActionId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($SourceActionId, array $bind)
	{
		$SourceActionId = intval($SourceActionId);

		return $this->db->update($this->getDbTable(), $bind, '`SourceActionId` = ?', array($SourceActionId));
	}

	public function getAll()
	{
		$sql = "SELECT * FROM " . $this->getDbTable() . " where 1 ORDER BY SourceActionId ASC";
		$return = $this->db->getAll($sql);
		if($return)
		{
			foreach($return as $key => $value)
			{
				$AllSourceActionArr[$value['SourceActionId']] = $value;	
			}	
		}
		return $AllSourceActionArr;
	}

}

<?php
/**
 * Quest配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Quest.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Quest extends Base_Widget
{
	/**
	 * Quest表名
	 * @var string
	 */
	protected $table = 'game_quest';

	/**
	 * 获取单条记录
	 * @param integer $QuestId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($QuestId,$AppId,$field = '*')
	{
		$QuestId = intval($QuestId);
		$AppId = intval($AppId);

		return $this->db->selectRow($this->getDbTable(), $field, '`QuestId` = ? and `AppId` = ?', array($QuestId,$AppId));
	}

	/**
	 * 获取单个字段
	 * @param integer $QuestId
	 * @param string $field
	 * @return string
	 */
	public function getOne($QuestId,$AppId,$field)
	{
		$QuestId = intval($QuestId);
		$AppId = intval($AppId);

		return $this->db->selectOne($this->getDbTable(), $field, '`QuestId` = ? and `AppId` = ?', array($QuestId,$AppId));
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
	 * @param integer $QuestId
	 * @return boolean
	 */
	public function delete($QuestId,$AppId)
	{
		$QuestId = intval($QuestId);
		$AppId = intval($AppId);

		return $this->db->delete($this->getDbTable(),'`QuestId` = ? and `AppId` = ?', array($QuestId,$AppId));
	}

	/**
	 * 更新
	 * @param integer $QuestId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($QuestId,$AppId, array $bind)
	{
		$QuestId = intval($QuestId);
		$AppId = intval($AppId);

		return $this->db->update($this->getDbTable(), $bind, '`QuestId` = ? and `AppId` = ?', array($QuestId,$AppId));
	}

	public function getAll($AppId,$fields = "*")
	{
		if($AppId)
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " where AppId = ? ORDER BY AppId,QuestId ASC";
			$return = $this->db->getAll($sql,$AppId);
		}
		else
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY AppId,QuestId ASC";
			$return = $this->db->getAll($sql);		
		}
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllQuest[$value['AppId']][$value['QuestId']] = $value;	
			}	
		}
		return $AllQuest;
	}

}

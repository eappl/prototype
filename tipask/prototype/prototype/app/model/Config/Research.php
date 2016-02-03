<?php
/**
 * 地域数据
 * @author 陈晓东
 * $Id: Research.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Research extends Base_Widget
{
	/**
	 * 表名
	 * @var string
	 */
	protected $table = "research";

	/**
	 * 初始化表名
	 * @return string
	 */
	public function init()
	{
		parent::init();
		$this->table = Base_Widget::getDbTable($this->table);
	}

	/**
	 * 插入数据
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		return $this->db->insert(Base_Widget::getDbTable($this->table), $bind);
	}

	/**
	 * 删除数据
	 * @param string$ResearchId
	 * @return boolean
	 */
	public function delete($ResearchId)
	{
		return $this->db->delete(Base_Widget::getDbTable($this->table), '`ResearchId` = ?',$ResearchId);
	}

	/**
	 * 修改数据
	 * @param array $bind
	 * @param string$ResearchId
	 * @return boolean
	 */
	public function update($ResearchId, array $bind)
	{
		return $this->db->update(Base_Widget::getDbTable($this->table), $bind, '`ResearchId` = ?',$ResearchId);
	}

	/**
	 * 查询单个字段
	 * @param$ResearchId
	 * @param $fields
	 * @return array
	 */
	public function getOne($ResearchId, $field='*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $field FROM $table_to_process WHERE `ResearchId` = ?";
		return $this->db->getRow($sql,$ResearchId);
	}
	/**
	 * 获取单行数据
	 * @param array $param
	 * @param $field
	 * @return array
	 */
	public function getRow($ResearchId, $field = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $field FROM $table_to_process WHERE `ResearchId` = ?";
		return $this->db->getRow($sql,$ResearchId);	
	}
	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAll($fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM $table_to_process ORDER BY ResearchId ASC";
		$totalResearch = $this->db->getAll($sql);
		foreach($totalResearch as $Key => $value)
		{		
			$ResearchList[$value['ResearchId']] = $value;
		}
		return $ResearchList;
	}
}
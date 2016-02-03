<?php
/**
 * 用户激活相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Sports extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_sports_type';

	protected $maxParams = 5;

	public function getMaxParmas()
	{
		return $this->maxParams;
	}
	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAllSportsTypeList($fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " ORDER BY SportsTypeId ASC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllSportsType[$value['SportsTypeId']] = $value;
			}
		}
		return $AllSportsType;
	}
	/**
	 * 获取单条记录
	 * @param integer $AppId
	 * @param string $fields
	 * @return array
	 */
	public function getSportsType($sportsTypeId, $fields = '*')
	{
		$sportsTypeId = intval($sportsTypeId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`SportsTypeId` = ?', $sportsTypeId);
	}
	/**
	 * 更新
	 * @param integer $AppId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateSportsType($sportsTypeId, array $bind)
	{
		$sportsTypeId = intval($sportsTypeId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`SportsTypeId` = ?', $sportsTypeId);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertSportsType(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $AppId
	 * @return boolean
	 */
	public function deleteSportsType($sportsTypeId)
	{
		$sportsTypeId = intval($sportsTypeId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`SportsTypeId` = ?', $sportsTypeId);
	}

}

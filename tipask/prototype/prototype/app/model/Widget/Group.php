<?php
/**
 * 用户组
 * @author     陈晓东
 */


class Widget_Group extends Base_Widget
{
	protected $table = 'config_group';

	public function getAll($fields = '*')
	{
		$sql = "SELECT $fields FROM {$this->table} ";
		return $this->db->getAll($sql);
	}
	
	public function getClass($Class, $fields = '*')
	{
		$sql = "SELECT $fields FROM {$this->table} WHERE ClassId = $Class";
		$res = $this->db->getAll($sql);
		$return = array();
		foreach($res as $key => $value)
		{
			$return[$value['group_id']] = $value;
		}
		return $return;		
	}

	public function get($group_id, $fields = '*')
	{
		$sql = "SELECT $fields FROM {$this->table} WHERE `group_id` = ?";
		return $this->db->getRow($sql, $group_id);
	}

	public function insert(array $bind)
	{
		return $this->db->insert($this->table, $bind);
	}

	public function nameExists($name)
	{
		$sql = "SELECT `group_id` FROM `{$this->table}` WHERE `name` = ?";
		$groupId = $this->db->getOne($sql, $name);
		return $groupId > 0;
	}


	public function update($group_id,array $bind)
	{
		$group_id = intval($group_id);
		return $this->db->update($this->table, $bind, '`group_id` = ?', $group_id);
	}

	public function delete($group_id)
	{
		return $this->db->delete($this->table, '`group_id` = ?', $group_id);
	}
}

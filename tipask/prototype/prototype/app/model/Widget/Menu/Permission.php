<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: Purview.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Widget_Menu_Permission extends Base_Widget
{

	protected $table = 'config_menu_purview';
	protected $table_permission = 'config_menu_permission';

	public function insert(array $bind)
	{
		return $this->db->insert($this->table, $bind);
	}
	public function insertPermission(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_permission);
		return $this->db->insert($table_to_process, $bind);
	}
	public function update($menu_id, $group_id, array $bind)
	{
		return $this->db->update($this->table, $bind, '`menu_id` = ? AND `group_id` = ?', array($menu_id, $group_id));
	}
	public function delete($menu_id, $group_id)
	{

	}
	public function deleteByGroup($group_id)
	{
		return $this->db->delete($this->table, '`group_id` = ?', $group_id);
	}
	public function deleteByMenu($menu_id)
	{
		return $this->db->delete($this->table, 'menu_id = ?', $menu_id);
	}
	public function deletePermissionByMenu($menu_id)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_permission);
		return $this->db->delete($table_to_process, 'menu_id = ?', $menu_id);
	}
	public function deletePermissionByGroup($group_id)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_permission);
		return $this->db->delete($table_to_process, 'group_id = ?', $group_id);
	}
	public function get($menu_id, $group_id, $fields = '*')
	{
		$sql = "SELECT $fields FROM {$this->table} WHERE `menu_id` = ? AND `group_id` = ?";
		return $this->db->getRow($sql, array($menu_id, $group_id));
	}
	public function getOne($menu_id, $group_id, $field)
	{
		return $this->db->selectOne($this->getDbTable(), $field, 'group_id = ? AND menu_id = ?', array($group_id, $menu_id));
	}
	public function getPermission($menu_id, $group_id, $field = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_permission);
		return $this->db->select($table_to_process, $field, 'group_id = ? AND menu_id = ?', array($group_id, $menu_id));
	}
	public function getPermissionByMenu($menu_id, $fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_permission);
		return $this->db->select($table_to_process, '*', 'menu_id = ?', $menu_id);
	}
	public function getPermissionByGroup($group_id, $fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_permission);
		return $this->db->select($table_to_process, '*', 'group_id = ?', $group_id);
	}
	public function getPermissionByGruop($group_id, $fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_permission);
		return $this->db->select($table_to_process, '*', 'group_id = ?', $group_id);
	}
	public function getTopPermissionByGroup($group_id,$top_menu_id)
	{
		$permission_list = $this->getPermissionByGruop($group_id);
		$top_permission = array();
		$permission_to_process = array();
		foreach($permission_list as $key => $value)
		{
			//待归档的权限列表
			$permission_to_process[$value['menu_id']] = 1;
		}
		$Menu = new Widget_Menu();
		while(count($permission_to_process))
		{
			foreach($permission_to_process as $menu_id => $value)
			{
				$menu = $Menu->get($menu_id);
				//如果是当前顶层目录
				if($menu['parent']==$top_menu_id)
				{
					$top_permission[$menu_id] = $menu;
				}
				//将其上级加入待处理队列
				else
				{
					$permission_to_process[$menu['parent']] = 1;
				}
				//从待处理队列中移除
				unset($permission_to_process[$menu_id]);
			}
		}
		return $top_permission;
	}	
	public function updateByMenuID($menu_id, array $bind)
	{
		return $this->db->update($this->table, $bind, '`menu_id` = ?', $menu_id);
	}
	public function updatePermissionByMenu($menu_id,array $permission = array())
	{
		$this->db->begin();
		//删除当前页面所有权限,待重构
		$this->deletePermissionByMenu($menu_id);
		//如果需要重新写入数组
		if(count($permission))
		{
			foreach($permission as $group_id => $p_list)
			{
				foreach($p_list as $p => $value)
				{
					$permissionArr = array('group_id'=>$group_id,'permission'=>$p,'menu_id'=>$menu_id);
					//插入权限记录
					$insert = $this->insertPermission($permissionArr);
					//如果任意一条插入失败则回滚
					if(!$insert)
					{
						$this->db->rollback();
						return false;
					}
				}
			}
		}
		//全部成功,提交
		$this->db->commit();
		return true;
	}
	public function updatePermissionByGroup($group_id,array $permission)
	{
		$this->db->begin();
		//删除当前页面所有权限,待重构
		$this->deletePermissionByGroup($group_id);
		foreach($permission as $menu_id => $p_list)
		{
			foreach($p_list as $p => $value)
			{
				$permissionArr = array('group_id'=>$group_id,'permission'=>$p,'menu_id'=>$menu_id);
				//插入权限记录
				$insert = $this->insertPermission($permissionArr);
				//如果任意一条插入失败则回滚
				if(!$insert)
				{
					$this->db->rollback();
					return false;
				}				
			}
		}
		//全部成功,提交
		$this->db->commit();
		return true;
	}
}

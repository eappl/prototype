<?php
/**
 * @author 陈晓东
 * $Id: Menu.php 15287 2014-08-25 09:24:09Z 334746 $
 */

class Widget_Menu extends Base_Widget
{

	protected $table = 'config_menu';

	/**
	 * 
	 * 插入菜单
	 * @author 陈晓东
	 * @param $bind
	 */
	public function insert(array $bind)
	{
		$insertStruct = array(
			'name' => empty($bind['name']) ? '' : $bind['name'],
			'link' => empty($bind['link']) ? '' : $bind['link'],
			'parent' => empty($bind['parent']) ? 0 : intval($bind['parent']),
			'sort' => empty($bind['sort']) ? 80 : intval($bind['sort']),
			'sign' => empty($bind['sign']) ? '' : $bind['sign'],
		);

		if (isset($bind['menu_id'])) {
			$insertStruct['menu_id']=$bind['menu_id'];
		}

		return $this->db->insert($this->getDbTable(), $insertStruct);
	}

	/**
	 * 
	 * 删除菜单
	 * @author 陈晓东
	 * @param unknown_type $menu_id
	 */
	public function delete($menu_id)
	{
		return $this->db->delete($this->getDbTable(), '`menu_id` = ?', $menu_id);
	}

	/**
	 * 
	 * 修改更新菜单
	 * @author 陈晓东
	 * @param unknown_type $menu_id
	 * @param array $bind
	 */
	public function update($menu_id, array $bind)
	{
		$this->db->begin();
		
		//菜单修改
		$ret=$this->db->update($this->table, $bind, '`menu_id` = ?', $menu_id);
		if(!$ret)
		{
			$this->db->rollBack();
			return false;
		}

		if(!empty($bind['menu_id']))
		{
			//子菜单修改
			$ret=$this->db->update($this->table, array('parent'=>$bind['menu_id']), '`parent` = ?', $menu_id);
			if(!$ret)
			{
				$this->db->rollBack();
				return false;
			}
			
			//菜单权限修改
			$Widget_Menu_Permission=new Widget_Menu_Permission();
			$Widget_Menu_Permission->updateByMenuID($menu_id, array('menu_id'=>$bind['menu_id']));
		}
		$this->db->commit();
		return true;
	}

	/**
	 * 
	 * 按id取得菜单指定例
	 * @author 陈晓东
	 * @param unknown_type $menu_id
	 * @param unknown_type $fields
	 */
	public function get($menu_id, $fields = '*')
	{
		$sql = "SELECT $fields FROM {$this->table} WHERE `menu_id` = ?";
		return $this->db->getRow($sql, $menu_id);
	}

	/**
	 * 
	 * 按路径取得指定菜单
	 * @author 陈晓东
	 * @param unknown_type $sign
	 * @param unknown_type $field
	 */
	public function getOneBylink($link, $fields = '*')
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable() . " WHERE link = ?";
		return $this->db->getRow($sql, $link);
	}

	/**
	 * 
	 * 取得全部菜单
	 * @author 陈晓东
	 * @return 全部菜单
	 */
	public function getAll()
	{
		$sql = "SELECT * FROM {$this->table} ORDER BY sort ASC";
		return $this->db->getAll($sql);
	}

	/**
	 * 
	 * 按菜单名取得菜单
	 * @author 陈晓东
	 * @param unknown_type $name
	 * @param unknown_type $fields
	 */
	public function getByName($name, $fields = '*')
	{
		$sql = "SELECT $fields FROM `{$this->table}` WHERE `name` = ?";
		return $this->db->getRow($sql, $name);
	}
	
	/**
	 * 
	 * 取得所有根菜单
	 * @author 陈晓东
	 */
	public function getRootAll()
	{
		$sql = "SELECT `menu_id`, `name`, `link`, `parent`, `sort` FROM {$this->table} WHERE `parent` = 0 AND `sort` >= 0 ORDER BY `sort` ASC";
		return $this->db->getAll($sql);
	}
    
    /**
	 * 
	 * 取得权限管理所有子菜单
	 * @author 张骥
	 */
	public function getPermissionChildMenu($parentId)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT menu_id,name FROM $table_to_process WHERE `parent` = $parentId AND `sort` >= 0 ORDER BY `sort` ASC";
		$res = $this->db->getAll($sql);
		$return = array();
		foreach($res as $key => $menu_info)
		{
			$return['menu_list'][$menu_info['menu_id']] = $menu_info;

		}
		
		return $return;		
	}
    
    /**
	 * 
	 * 取得所有子菜单
	 * @author 张骥
	 */
	public function getChildMenu($parentId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE `parent` = $parentId AND `sort` > 0 ORDER BY `sort` ASC";
		return $this->db->getAll($sql);
	}
    
    /**
	 * 
	 * 获取父菜单
	 * @author 张骥
	 */
	public function getParentMenu($menu_id, $fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM $table_to_process WHERE `menu_id` = ?";        
        $parent = $this->db->getAll($sql, $menu_id);       
        foreach($parent as $k=>$v)
		{
            $parents[$k] = $v;
        }        
		return $parents;
	}
}

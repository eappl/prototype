<?php
/**
 * app配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: App.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_App extends Base_Widget
{
	/**
	 * app表名
	 * @var string
	 */
	protected $table = 'config_app';
	protected $levelup_table = 'role';



	/**
	 * 获取单条记录
	 * @param integer $AppId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($AppId, $fields = '*')
	{
		$AppId = intval($AppId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`AppId` = ?', $AppId);
	}

	/**
	 * 获取单个字段
	 * @param integer $AppId
	 * @param string $field
	 * @return string
	 */
	public function getOne($AppId, $field)
	{
		$AppId = intval($AppId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectOne($table_to_process, $field, '`AppId` = ?', $AppId);
	}

	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $AppId
	 * @return boolean
	 */
	public function delete($AppId)
	{
		$AppId = intval($AppId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`AppId` = ?', $AppId);
	}

	/**
	 * 更新
	 * @param integer $AppId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($AppId, array $bind)
	{
		$AppId = intval($AppId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`AppId` = ?', $AppId);
	}

	/**
	 * 查询类型游戏
	 * @param $app_class
	 * @param $fields
	 * @return array
	 */
	public function getByClass($ClassId, $fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " WHERE `ClassId` = ? ORDER BY AppId ASC";
		return $this->db->getAll($sql, $ClassId);
	}

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAll($fields = "*",$is_show = 0)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		if(!$is_show)
		{
			$sql = "SELECT $fields FROM " . $table_to_process . " ORDER BY AppId ASC";
		}
		else
		{		 			
			$sql = "SELECT $fields FROM " . $table_to_process . " where is_show = $is_show ORDER BY AppId ASC";
		}
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllApp[$value['AppId']] = $value;	
			}	
		}
		return $AllApp;
	}

	/**
	 * 筛选是否本公司开发游戏
	 * @param unknown_type $app_type  0:全部/1:本公司开发开发/2:代理游戏
	 * @param array $AppList 游戏ID数组
	 */
	public function getApp($app_type, array $AppList)
	{
		$app_type = intval($app_type);
		if(count($AppList))
		{
			foreach($AppList as $AppId => $app_data)
			{
				if($app_type>0)
				{
					if(intval($AppId/100)!=$app_type)
					{
						unset($AppList[$AppId]);
					}
				}
			}
		}
		return $AppList;
	}
	public function reBuildAppConfig($fields = "*")
	{
		$AllApp = $this->getAll($fields);
		$file_path = "../../etc/";
		$file_name = "App.php";
		$var = var_export($AllApp,true);
		$text ='<?php $AllApp='.$var.'; return $AllApp;?>';		
		file_put_contents($file_path.$file_name,$text);
	}

}

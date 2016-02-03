<?php
/**
 * 游戏类别
 * @author chen<cxd032404@hotmail.com>
 * $Id: Class.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Class extends Base_Widget
{
	/**
	 * 游戏类别表名
	 * @var string
	 */
	protected $table = 'config_class';

	/**
	 * 游戏类别查询
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		return $this->db->insert($this->getDbTable(),$bind);
	}

	/**
	 * 游戏类别删除
	 * @param $param
	 * @return boolean
	 */
	public function delete($gameClassId)
	{
		return $this->db->delete($this->getDbTable(), '`ClassId` = ?', $gameClassId);
	}

	/**
	 * 游戏类别更新
	 * @param array $param
	 * @param $bind
	 * @return boolean
	 */
	public function update($gameClassId, array $bind)
	{
		return $this->db->update($this->getDbTable(), $bind, '`ClassId` = ?',$gameClassId );
	}

	/**
	 * 获取单条游戏类别内容
	 * @param $gameClassId
	 * @return array
	 */
	public function getRow($gameClassId, $filed = "*")
	{
		$sql = "SELECT $filed FROM {$this->getDbTable()} WHERE `ClassId` = ?";
		return $this->db->getRow($sql, $gameClassId);
	}

	/**
	 * 全部游戏类别记录查询
	 * @param $filed
	 * @return array
	 */
	public function getAll($filed = "*")
	{
		$AllClass = array();
		$sql = "SELECT $filed FROM {$this->getDbTable()} ORDER BY ClassId ASC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllClass[$value['ClassId']] = $value;	
			}	
		}
		return $AllClass;
	}

	/**
	 * 查找游戏类别名称
	 * @param $name
	 * @return boolean
	 */
	public function nameExists($name)
	{
		$sql = "SELECT `ClassId` FROM `{$this->getDbTable()}` WHERE `name` = ?";
		$id = $this->db->getOne($sql, $name);
		return $id;
	}
}
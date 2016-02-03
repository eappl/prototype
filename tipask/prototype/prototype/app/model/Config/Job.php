<?php
/**
 * Job配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Job.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Job extends Base_Widget
{
	/**
	 * Job表名
	 * @var string
	 */
	protected $table = 'game_job';

	/**
	 * 获取单条记录
	 * @param integer $JobId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($JobId,$AppId,$field = '*')
	{
		$JobId = intval($JobId);
		$AppId = intval($AppId);

		return $this->db->selectRow($this->getDbTable(), $field, '`JobId` = ? and `AppId` = ?', array($JobId,$AppId));
	}

	/**
	 * 获取单个字段
	 * @param integer $JobId
	 * @param string $field
	 * @return string
	 */
	public function getOne($JobId,$AppId,$field)
	{
		$JobId = intval($JobId);
		$AppId = intval($AppId);

		return $this->db->selectOne($this->getDbTable(), $field, '`JobId` = ? and `AppId` = ?', array($JobId,$AppId));
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
	 * @param integer $JobId
	 * @return boolean
	 */
	public function delete($JobId,$AppId)
	{
		$JobId = intval($JobId);
		$AppId = intval($AppId);

		return $this->db->delete($this->getDbTable(),'`JobId` = ? and `AppId` = ?', array($JobId,$AppId));
	}

	/**
	 * 更新
	 * @param integer $JobId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($JobId,$AppId, array $bind)
	{
		$JobId = intval($JobId);
		$AppId = intval($AppId);

		return $this->db->update($this->getDbTable(), $bind, '`JobId` = ? and `AppId` = ?', array($JobId,$AppId));
	}

	public function getAll($AppId,$fields = "*")
	{
		if($AppId)
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " where AppId = ? ORDER BY AppId,JobId ASC";
			$return = $this->db->getAll($sql,$AppId);
		}
		else
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY AppId,JobId ASC";
			$return = $this->db->getAll($sql);		
		}
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllJob[$value['AppId']][$value['JobId']] = $value;	
			}	
		}
		return $AllJob;
	}

}

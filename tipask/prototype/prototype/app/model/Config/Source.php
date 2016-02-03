<?php
/**
 * Source配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Source.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Source extends Base_Widget
{
	/**
	 * Source表名
	 * @var string
	 */
	protected $table = 'user_source';

	/**
	 * 获取单条记录
	 * @param integer $SourceId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($SourceId,$field = '*')
	{
		$SourceId = intval($SourceId);
		return $this->db->selectRow($this->getDbTable(), $field, '`SourceId` = ?', array($SourceId));
	}

	/**
	 * 获取单个字段
	 * @param integer $SourceId
	 * @param string $field
	 * @return string
	 */
	public function getOne($SourceId,$field)
	{
		$SourceId = intval($SourceId);
		return $this->db->selectOne($this->getDbTable(), $field, '`SourceId` = ?', array($SourceId));
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
	 * @param integer $SourceId
	 * @return boolean
	 */
	public function delete($SourceId)
	{
		$SourceId = intval($SourceId);

		return $this->db->delete($this->getDbTable(),'`SourceId` = ?', array($SourceId));
	}

	/**
	 * 更新
	 * @param integer $SourceId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($SourceId, array $bind)
	{
		$SourceId = intval($SourceId);

		return $this->db->update($this->getDbTable(), $bind, '`SourceId` = ?', array($SourceId));
	}

	public function getAll($SourceTypeId = 0)
	{
		if($SourceTypeId)
		{
			$sql = "SELECT * FROM " . $this->getDbTable() . " where SourceTypeId = ? ORDER BY SourceTypeId,SourceId ASC";
			$return = $this->db->getAll($sql,$SourceTypeId);
		}
		else
		{
			$sql = "SELECT * FROM " . $this->getDbTable() . " ORDER BY SourceTypeId,SourceId ASC";
			$return = $this->db->getAll($sql);		
		}
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllSource[$value['SourceId']] = $value;	
			}	
		}
		return $AllSource;
	}

}

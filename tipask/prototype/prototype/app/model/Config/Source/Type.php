<?php
/**
 * SourceType配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Type.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Source_Type extends Base_Widget
{
	/**
	 * SourceType表名
	 * @var string
	 */
	protected $table = 'user_source_type';

	/**
	 * 获取单条记录
	 * @param integer $SourceTypeId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($SourceTypeId,$field = '*')
	{
		$SourceTypeId = trim($SourceTypeId);

		return $this->db->selectRow($this->getDbTable(), $field, '`SourceTypeId` = ?', $SourceTypeId);
	}

	/**
	 * 获取单个字段
	 * @param integer $SourceTypeId
	 * @param string $field
	 * @return string
	 */
	public function getOne($SourceTypeId,$field)
	{
		$SourceTypeId = trim($SourceTypeId);

		return $this->db->selectOne($this->getDbTable(), $field, '`SourceTypeId` = ?', $SourceTypeId);
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
	 * @param integer $SourceTypeId
	 * @return boolean
	 */
	public function delete($SourceTypeId)
	{
		$SourceTypeId = trim($SourceTypeId);

		return $this->db->delete($this->getDbTable(),'`SourceTypeId` = ?', $SourceTypeId);
	}

	/**
	 * 更新
	 * @param integer $SourceTypeId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($SourceTypeId, array $bind)
	{
		$SourceTypeId = trim($SourceTypeId);

		return $this->db->update($this->getDbTable(), $bind, '`SourceTypeId` = ? ', $SourceTypeId);
	}

	public function getAll($fields = "*")
	{

			$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY SourceTypeId ASC";
			$return = $this->db->getAll($sql);		

		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllSourceType[trim($value['SourceTypeId'])] = $value;	
			}	
		}
		return $AllSourceType;
	}

}

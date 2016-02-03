<?php
/**
 * SourceType配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Type.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Video_Type extends Base_Widget
{
	/**
	 * SourceType表名
	 * @var string
	 */
	protected $table = 'video_type_list';

	/**
	 * 获取单条记录
	 * @param integer $VideoTypeId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($VideoTypeId,$field = '*')
	{
		$VideoTypeId = trim($VideoTypeId);

		return $this->db->selectRow($this->getDbTable(), $field, '`VideoTypeId` = ?', $VideoTypeId);
	}

	/**
	 * 获取单个字段
	 * @param integer $VideoTypeId
	 * @param string $field
	 * @return string
	 */
	public function getOne($VideoTypeId,$field)
	{
		$VideoTypeId = trim($VideoTypeId);

		return $this->db->selectOne($this->getDbTable(), $field, '`VideoTypeId` = ?', $VideoTypeId);
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
	 * @param integer $VideoTypeId
	 * @return boolean
	 */
	public function delete($VideoTypeId)
	{
		$VideoTypeId = trim($VideoTypeId);

		return $this->db->delete($this->getDbTable(),'`VideoTypeId` = ?', $VideoTypeId);
	}

	/**
	 * 更新
	 * @param integer $VideoTypeId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($VideoTypeId, array $bind)
	{
		$VideoTypeId = trim($VideoTypeId);

		return $this->db->update($this->getDbTable(), $bind, '`VideoTypeId` = ? ', $VideoTypeId);
	}

	public function getAll($fields = "*")
	{

		$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY VideoTypeId ASC";
		$return = $this->db->getAll($sql);		

		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllVideoType[trim($value['VideoTypeId'])] = $value;	
			}	
		}
		return $AllVideoType;
	}

}

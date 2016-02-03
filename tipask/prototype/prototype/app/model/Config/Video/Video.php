<?php
/**
 * SourceType配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Video.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Video_Video extends Base_Widget
{
	/**
	 * SourceType表名
	 * @var string
	 */
	protected $table = 'video_list';

	/**
	 * 获取单条记录
	 * @param integer $VideoTypeId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($VideoId,$field = '*')
	{
		$VideoId = trim($VideoId);
		return $this->db->selectRow($this->getDbTable(), $field, '`VideoId` = ?', $VideoId);
	}

	/**
	 * 获取单个字段
	 * @param integer $VideoTypeId
	 * @param string $field
	 * @return string
	 */
	public function getOne($VideoId,$field)
	{
		$VideoTypeId = trim($VideoId);

		return $this->db->selectOne($this->getDbTable(), $field, '`VideoId` = ?', $VideoId);
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
	public function delete($VideoId)
	{
		$VideoId = trim($VideoId);

		return $this->db->delete($this->getDbTable(),'`VideoId` = ?', $VideoId);
	}

	/**
	 * 更新
	 * @param integer $VideoTypeId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($VideoId, array $bind)
	{
		$VideoId = trim($VideoId);

		return $this->db->update($this->getDbTable(), $bind, '`VideoId` = ? ', $VideoId);
	}

	public function getAll($VideoTypeId,$fields = "*")
	{
		if($VideoTypeId)
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " where VideoTypeId = $VideoTypeId ORDER BY LastUpdateTime DESC";
		}
		else
		{
		 	$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY LastUpdateTime DESC";
		}
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllVideo[($value['VideoId'])] = $value;	
			}	
		}
		return $AllVideo;
	}

}

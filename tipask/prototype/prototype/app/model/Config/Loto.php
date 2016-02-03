<?php
/**
 * Loto配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Loto.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Loto extends Base_Widget
{
	/**
	 * map表名
	 * @var string
	 */
	protected $table = 'loto_list';

	/**
	 * 获取单条记录
	 * @param integer $LotoId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($LotoId, $fields = '*')
	{
		$LotoId = intval($LotoId);

		return $this->db->selectRow($this->getDbTable(), $fields, '`LotoId` = ?', $LotoId);
	}

	/**
	 * 获取单个字段
	 * @param integer $LotoId
	 * @param string $field
	 * @return string
	 */
	public function getOne($LotoId, $field)
	{
		$LotoId = intval($LotoId);

		return $this->db->selectOne($this->getDbTable(), $field, '`LotoId` = ?', $LotoId);
	}

	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		print_r($bind);
		return $this->db->insert($this->getDbTable(), $bind);
	}

	/**
	 * 删除
	 * @param integer $LotoId
	 * @return boolean
	 */
	public function delete($LotoId)
	{
		$LotoId = intval($LotoId);

		return $this->db->delete($this->getDbTable(), '`LotoId` = ?', $LotoId);
	}

	/**
	 * 更新
	 * @param integer $LotoId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($LotoId, array $bind)
	{
		$LotoId = intval($LotoId);

		return $this->db->update($this->getDbTable(), $bind, '`LotoId` = ?', $LotoId);
	}

	public function getAll($fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY LotoId ASC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllLoto[$value['LotoId']] = $value;	
			}	
		}
		return $AllLoto;
	}

}

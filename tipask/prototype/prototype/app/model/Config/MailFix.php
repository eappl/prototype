<?php
/**
 * app配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: MailFix.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_MailFix extends Base_Widget
{
	/**
	 * app表名
	 * @var string
	 */
	protected $table = 'mail_sub_fix';


	/**
	 * 获取单条记录
	 * @param integer FixId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($FixId, $fields = '*')
	{
		$FixId = intval($FixId);

		return $this->db->selectRow($this->getDbTable(), $fields, '`FixId` = ?', $FixId);
	}

	/**
	 * 获取单个字段
	 * @param integer FixId
	 * @param string $field
	 * @return string
	 */
	public function getOne($FixId, $field)
	{
		$FixId = intval($FixId);

		return $this->db->selectOne($this->getDbTable(), $field, '`FixId` = ?', $FixId);
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
	 * @param integer FixId
	 * @return boolean
	 */
	public function delete($FixId)
	{
		$FixId = intval($FixId);
		return $this->db->delete($this->getDbTable(), '`FixId` = ?', $FixId);
	}

	/**
	 * 更新
	 * @param integer FixId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($FixId, array $bind)
	{
		$FixId = intval($FixId);

		return $this->db->update($this->getDbTable(), $bind, '`FixId` = ?', $FixId);
	}

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAll($fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY FixId ASC";
		$MailFixArr = $this->db->getAll($sql);
		foreach($MailFixArr as $key => $value)
		{
			$return[$value['SubFix']] = $value;	
		}
		return $return;
	}
}

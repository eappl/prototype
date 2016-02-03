<?php
/**
 * app配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: SecurityAnswer.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_SecurityAnswer extends Base_Widget
{
	/**
	 * app表名
	 * @var string
	 */
	protected $table = 'security_answer';
//	protected $table_answer = 'user_security_answer';


	/**
	 * 获取单条记录
	 * @param integer QuestionId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($QuestionId, $fields = '*')
	{
		$QuestionId = intval($QuestionId);

		return $this->db->selectRow($this->getDbTable(), $fields, '`QuestionId` = ?', $QuestionId);
	}

	/**
	 * 获取单个字段
	 * @param integer QuestionId
	 * @param string $field
	 * @return string
	 */
	public function getOne($QuestionId, $field)
	{
		$QuestionId = intval($QuestionId);

		return $this->db->selectOne($this->getDbTable(), $field, '`QuestionId` = ?', $QuestionId);
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
	
//	public function insertAnswer(array $bind)
//	{
//		$position = Base_Common::getUserDataPositionById($bind['UserId']);
//		$table_to_insert = Base_Common::getUserTable($this->table_answer,$position);		
//		return $this->db->insert($table_to_insert, $bind);
//	}
//	public function getUserQuestionAnswer($UserId,$QuestionId, $fields = '*')
//	{
//		$position = Base_Common::getUserDataPositionById($UserId);
//		$table_to_process = Base_Common::getUserTable($this->table_answer,$position);	
//		$sql = "select $fields from $table_to_process where `UserId` = ? and `QuestionId` = ?";
//		$param = array($UserId,$QuestionId);
//		return $this->db->getRow($sql,$param);
//	}
//	public function getUserAnswer($UserId,$fields = '*')
//	{
//		$position = Base_Common::getUserDataPositionById($UserId);
//		$table_to_process = Base_Common::getUserTable($this->table_answer,$position);	
//		$sql = "select $fields from $table_to_process where `UserId` = ?";
//		return $this->db->getAll($sql,$UserId);
//	}

	/**
	 * 删除
	 * @param integer QuestionId
	 * @return boolean
	 */
	public function delete($QuestionId)
	{
		$QuestionId = intval($QuestionId);
		return $this->db->delete($this->getDbTable(), '`QuestionId` = ?', $QuestionId);
	}

	/**
	 * 更新
	 * @param integer QuestionId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($QuestionId, array $bind)
	{
		$QuestionId = intval($QuestionId);

		return $this->db->update($this->getDbTable(), $bind, '`QuestionId` = ?', $QuestionId);
	}

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAll($fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY QuestionId ASC";
		return $this->db->getAll($sql);
	}
}

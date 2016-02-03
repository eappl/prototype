<?php
/**
 * Faq配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Faq.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Faq extends Base_Widget
{
	/**
	 * Faq表名
	 * @var string
	 */
	protected $table = 'faq';

	/**
	 * 获取单条记录
	 * @param integer $FaqId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($FaqId,$field = '*')
	{
		$FaqId = intval($FaqId);
		return $this->db->selectRow($this->getDbTable(), $field, '`FaqId` = ?', array($FaqId));
	}

	/**
	 * 获取单个字段
	 * @param integer $FaqId
	 * @param string $field
	 * @return string
	 */
	public function getOne($FaqId,$field)
	{
		$FaqId = intval($FaqId);
		return $this->db->selectOne($this->getDbTable(), $field, '`FaqId` = ?', array($FaqId));
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
	 * @param integer $FaqId
	 * @return boolean
	 */
	public function delete($FaqId)
	{
		$FaqId = intval($FaqId);

		return $this->db->delete($this->getDbTable(),'`FaqId` = ?', array($FaqId));
	}

	/**
	 * 更新
	 * @param integer $FaqId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($FaqId, array $bind)
	{
		$FaqId = intval($FaqId);

		return $this->db->update($this->getDbTable(), $bind, '`FaqId` = ?', array($FaqId));
	}

	public function getAll($FaqTypeId = 0,$KeyWord = "",$Start = 0,$Count = 0)
	{
		$whereType = $FaqTypeId?" FaqTypeId = ".$FaqTypeId." ":"";
		$whereKeyWord = $KeyWord?" (name like '%".$KeyWord."%' or Answer like '%".$KeyWord."%') ":""; 

		$whereCondition = array($whereType,$whereKeyWord);
		$where = Base_common::getSqlWhere($whereCondition);

		//生成条件列
		$limit  = $Count?" limit $Start,$Count":"";
		$sql = "SELECT * FROM " . $this->getDbTable() . " where 1 ".$where." ORDER BY FaqTypeId,FaqId ASC".$limit;
		$return = $this->db->getAll($sql);
		
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllFaq[$value['FaqId']] = $value;	
			}	
		}
		return $AllFaq;
	}
	public function getFAQCount($FaqTypeId = 0,$KeyWord = "")
	{
		$whereType = $FaqTypeId?" FaqTypeId = ".$FaqTypeId." ":"";
		$whereKeyWord = $KeyWord?" (name like '%".$KeyWord."%' or Answer like '%".$KeyWord."%') ":""; 

		$whereCondition = array($whereType,$whereKeyWord);
		$where = Base_common::getSqlWhere($whereCondition);

		//生成条件列
		$limit  = $Count?" limit $Start,$Count":"";
		$sql = "SELECT count(*) as FaqCount FROM " . $this->getDbTable() . " where 1 ".$where." ORDER BY FaqTypeId,FaqId ASC".$limit;
		$FaqCount = $this->db->getOne($sql);
		return $FaqCount;
	}

}

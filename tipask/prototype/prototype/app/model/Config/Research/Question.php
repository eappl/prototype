<?php
/**
 * Question配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Question.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Research_Question extends Base_Widget
{
	/**
	 * Question表名
	 * @var string
	 */
	protected $table = 'question';
	protected $table_answer = 'research_answer';

	/**
	 * 获取单条记录
	 * @param integer $QuestionId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($QuestionId,$field = '*')
	{
		$QuestionId = intval($QuestionId);
		return $this->db->selectRow($this->getDbTable(), $field, '`QuestionId` = ?', array($QuestionId));
	}

	/**
	 * 获取单个字段
	 * @param integer $QuestionId
	 * @param string $field
	 * @return string
	 */
	public function getOne($QuestionId,$field)
	{
		$QuestionId = intval($QuestionId);
		return $this->db->selectOne($this->getDbTable(), $field, '`QuestionId` = ?', array($QuestionId));
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
	 * @param integer $QuestionId
	 * @return boolean
	 */
	public function delete($QuestionId)
	{
		$QuestionId = intval($QuestionId);

		return $this->db->delete($this->getDbTable(),'`QuestionId` = ?', array($QuestionId));
	}

	/**
	 * 更新
	 * @param integer $QuestionId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($QuestionId, array $bind)
	{
		$QuestionId = intval($QuestionId);

		return $this->db->update($this->getDbTable(), $bind, '`QuestionId` = ?', array($QuestionId));
	}

	public function getAll($ResearchId = 0)
	{
		$whereResearch = $ResearchId?" ResearchId = ".$ResearchId." ":"";

		$whereCondition = array($whereResearch);
		$where = Base_common::getSqlWhere($whereCondition);

		//生成条件列
		$sql = "SELECT * FROM " . $this->getDbTable() . " where 1 ".$where." ORDER BY ResearchId,QuestionId ASC";
		$return = $this->db->getAll($sql);
		$AllQuestion = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllQuestion[$value['QuestionId']] = $value;	
			}	
		}
		return $AllQuestion;
	}
	public function getFAQCount($ResearchId = 0,$KeyWord = "")
	{
		$whereType = $ResearchId?" ResearchId = ".$ResearchId." ":"";
		$whereKeyWord = $KeyWord?" (name like '%".$KeyWord."%' or Answer like '%".$KeyWord."%') ":""; 

		$whereCondition = array($whereType,$whereKeyWord);
		$where = Base_common::getSqlWhere($whereCondition);

		//生成条件列
		$limit  = $Count?" limit $Start,$Count":"";
		$sql = "SELECT count(*) as QuestionCount FROM " . $this->getDbTable() . " where 1 ".$where." ORDER BY ResearchId,QuestionId ASC".$limit;
		$QuestionCount = $this->db->getOne($sql);
		return $QuestionCount;
	}
	public function createAnswerLogTableDate($ResearchId)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_answer);
		$table_to_process = Base_Widget::getDbTable($this->table_answer)."_".$ResearchId;
		$exist = $this->db->checkTableExist($table_to_process);
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE " . $table_to_check;
			$row = $this->db->getRow($sql);
			$sql = $row['Create Table'];
			$sql = str_replace('`' . $this->table_answer . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
			$create = $this->db->query($sql);
			if($create)
			{
				return $table_to_process;
			}
			else
			{
			 return false;	
			}		 	
		}
	}
	public function InsertAnswerLog($DataArr,$ResearchId)
	{
		$this->db->begin();
		$table = $this->createAnswerLogTableDate($ResearchId);

		$log = $this->db->insert($table,$DataArr);
		
		if($log)
		{
			$this->db->commit();
			return true;
		}
		else
		{
			$this->db->rollback();
			return false;		 	
		}

	}
 	public function getResearchDetail($StartDate,$EndDate,$UserId,$ResearchId,$QuestionId,$start,$pagesize)
	{
		$ResearchCount = $this->getResearchDetailCount($StartDate,$EndDate,$UserId,$ResearchId,$QuestionId);
	  $StatArr = array('ResearchDetail'=>array());
		if($ResearchCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartDate = $StartDate?" AnswerTime >= ".strtotime($StartDate)." ":"";
			$whereEndDate = $EndDate?" AnswerTime <= ".(strtotime($EndDate)+86400-1)." ":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereQuestion = $QuestionId?" QuestionId = ".$QuestionId." ":"";
	
			$whereCondition = array($whereUser,$whereStartDate,$whereEndDate,$whereQuestion);
			
			$order = " order by AnswerTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
		
			$table_to_process = Base_Widget::getDbTable($this->table_answer)."_".$ResearchId;     	
	
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;

			$ResearchDetailArr = $this->db->getAll($sql,false);
			if(isset($ResearchDetailArr))
	    {
	      $i = 0;
	      foreach ($ResearchDetailArr as $key => $value) 
				{
					$StatArr['ResearchDetail'][$i++] = $value;
				}
	    }
  	}
  	
	 	$StatArr['ResearchCount'] = $ResearchCount; 
		return $StatArr;
	}
	public function getResearchDetailCount($StartDate,$EndDate,$UserId,$ResearchId,$QuestionId)
	{
		//查询列
		$select_fields = array('ResearchCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartDate = $StartDate?" AnswerTime >= ".strtotime($StartDate)." ":"";
		$whereEndDate = $EndDate?" AnswerTime <= ".(strtotime($EndDate)+86400-1)." ":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereQuestion = $QuestionId?" QuestionId = ".$QuestionId." ":"";

		$whereCondition = array($whereUser,$whereStartDate,$whereEndDate,$whereQuestion);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		
		$table_to_process = Base_Widget::getDbTable($this->table_answer)."_".$ResearchId;     	
    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;
		$ResearchCount = $this->db->getOne($sql,false);
		if($ResearchCount)
    {
			return $ResearchCount;    
		}
		else
		{
			return 0; 	
		}
	}

}

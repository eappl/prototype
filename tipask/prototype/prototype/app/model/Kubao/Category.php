<?php
/**
 * 基础mod层
 * $Id: BroadCastController.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Kubao_Category extends Base_Widget
{
	//声明所用到的表
	protected $table = 'ask_category';
	
	//根据问题分类获取分类信息
	public function getCategoryByQuestionType($QuestionType)
	{	
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$M = $oMenCache -> get('Category_Type_'.$QuestionType);
		if($M)
		{
			$Category = json_decode($M,true);
		}
		else
		{
			$table_to_process = Base_Widget::getDbTable($this->table);
			$Category = $this->db->selectRow($table_to_process,"*",'`question_type`=?',array($QuestionType));
			$oMenCache -> set('Category_Type_'.$QuestionType,json_encode($Category),3600);
		}
		return $Category;
	}
	public function getCategory($CategoryId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`id` = ?', $CategoryId);		
	}
}

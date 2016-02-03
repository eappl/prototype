<?php
/**
 * 公告mod层
 * $Id: BroadCast.php 15305 2014-08-28 03:19:13Z 334746 $
 */


class Kubao_BroadCast extends Base_Widget
{
	//声明所用到的表
	protected $table = 'broadcast';
	protected $table_common = 'ask_common_question';

	//获取当前生效的公告列表
	public function getCurrentBroadCast($ConditionList,$fields = '*',$include_all)
	{	
		$table_to_process = Base_Widget::getDbTable($this->table);		
		//查询列
		$select_fields = array($fields);
		//初始化查询条件
		$time = time();
		if($include_all==1)
		{
			$whereBroadCastZone = $ConditionList['BroadCastZone']?" (BroadCastZone = ".$ConditionList['BroadCastZone']." or BroadCastZone = 0)":"";
		}
		else
		{
			$whereBroadCastZone = $ConditionList['BroadCastZone']?" BroadCastZone = ".$ConditionList['BroadCastZone']." ":"";
		}		
		$whereAvailable = " BroadCastStatus != 3 ";
		$whereTime = " StartTime <= $time and EndTime >= $time ";
		$whereCondition = array($whereBroadCastZone,$whereTime,$whereAvailable);
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		
		$Limit = " limit ".$ConditionList['Count'];
		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where.$groups." order by StartTime DESC ".$Limit;
		$BroadCast = $this->db->getAll($sql);
		return $BroadCast;
	}
	//获取当前生效的常见问题列表
	public function getCurrentCommonQustion($ConditionList,$fields = '*')
	{	
		$table_to_process = Base_Widget::getDbTable($this->table_common);		
		//查询列
		$select_fields = array($fields);
		//初始化查询条件
		$time = time();
		$whereDisplay = " display = 1";
		$whereCondition = array($whereDisplay);
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		
		$Limit = " limit ".$ConditionList['Count'];
		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where.$groups." order by id DESC ".$Limit;
		$CommonQuestion = $this->db->getAll($sql);
		return $CommonQuestion;
	}
}

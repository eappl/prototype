<?php
/**
 * 通用
 * $Id: Common.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Kubao_Common extends Base_Widget
{
	//声明所用到的表
	protected $table = 'ask_log';
		
	//添加新系统日志
	public function addSystemLog($QuestionId,$AuthorName,$OperatorName,$LogTypeId,$LogText)
	{
		$LogType = $this->config->sys_log_arr;
		$LogTypeId = intval($LogTypeId);
		if(isset($LogType[$LogTypeId]))
		{
			$tip = $LogType[$LogTypeId];
		}
		else
		{
			return false;
		}
    	$QuestionId = intval($QuestionId);
    	//if($QuestionId == 0)
		//{
		//	return false;
		//}
    	$log_id = Base_Common::get_log_sn();
    	$message = $tip.$LogText;
		$time = time();
		$date = date("Ym",$time);
		$table_to_process = Base_Widget::getDbTable($this->table)."_".$date;
		$dataArr = array('id'=>$log_id,'AuthorName'=>$AuthorName,'qid'=>$QuestionId,'user'=>$OperatorName,'message'=>$message,'time'=>$time);
		return $this->db->insert($table_to_process,$dataArr);
	}
}

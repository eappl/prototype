<?php
/**
 * 邮件相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Lm_Mail extends Base_Widget
{
	//声明所用到的表
	protected $table = 'mail_queue';

	public function createMail($MailType,$UserId,$MailContent)
	{
		$table_to_insert = Base_Widget::getDbTable($table);
		$MailArr = array('UserId'=>$UserId,'MailType'=>$MailType,'MailContent'=>serialize($MailContent),'MailType'=>$MailType);
		return $this->db->insert($table_to_insert,$MailArr);
	}
	//获取角色升级数据
	public function getMailSentUpByMailAddress($StartDate,$EndDate,$MailType)
	{

	      //查询列
		$select_fields = array(
		'MailSent'=>'count(*)',
	  	'UserMail',
		);
	      
		//初始化查询条件
//		$whereStart = $StartDate?" end_date >= '".strtotime($StartDate)."' ":"";
//		$whereEnd = $EndDate?" end_date <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereType = $MailType?" MailType ='".$MailType."' ":"";
	      
		$group_fields = array('UserMail');
		$groups = Base_common::getGroupBy($group_fields);
	
		$whereCondition = array($whereStart,$whereEnd,$whereType);
	
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

	  	$StatArr['MailSent'] = array();	      
//		foreach($MailFixList as $key => $value)
//		{
//			$StatArr['MailSent'][$value['SubFix']] = array('MailSent'=>0,'Detail'=>array());	
//		}
		//初始化结果数组
		$Date = $StartDate;
		
		$DateStart = date("Ymd",strtotime($StartDate));
		$DateEnd = date("Ymd",strtotime($EndDate));
		$DateList = array();  
		$Date = $StartDate;      
		do
		{
		$D = date("Ymd",strtotime($Date));
		$DateList[] = $D;
		$Date = date("Y-m-d",strtotime("$Date +1 day"));
		}
		while($D!=$DateEnd);
		
		foreach($DateList as $k=>$v)
		{
			$table_name = Base_Widget::getDbTable($this->table)."_log_".$v;
			$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$MailSentArr = $this->db->getAll($sql,false);
		    
			foreach($MailSentArr as $key=>$val)
			{
				$t = explode("@",$val['UserMail']);
				$MailFix = "@".$t['1'];
			  	if(isset($StatArr['MailSent'][$MailFix]))
			  	{
			  		$StatArr['MailSent'][$MailFix]['MailSent'] += $val['MailSent'];	
			  	}
			  	else
			  	{
			  		$StatArr['MailSent'][$MailFix] = array('MailSent'=>0,'Detail'=>array());
			  		$StatArr['MailSent'][$MailFix]['MailSent'] += $val['MailSent'];	
			  	}
			  	if(isset($StatArr['MailSent'][$MailFix]['Detail'][$val['UserMail']]))
			  	{
			  		$StatArr['MailSent'][$MailFix]['Detail'][$val['UserMail']] += $val['MailSent'];	
			  	}
			  	else
			  	{
			  		$StatArr['MailSent'][$MailFix]['Detail'][$val['UserMail']] = 0;
			  		$StatArr['MailSent'][$MailFix]['Detail'][$val['UserMail']] += $val['MailSent'];	
			  	}
			}
		}
		return $StatArr;
	}
}

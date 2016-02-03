<?php
/**
 * 浏览mod层
 * $Id: ViewController.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Kubao_View extends Base_Widget
{
	//声明所用到的表
	protected $table = 'page_view_log';
	protected $table_page_config = 'page_view_config';
	
	//添加新浏览记录
	public function addViewLog($ViewInfo)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		//根据记录的时间戳确定所在的分表
		$table_to_process.= "_".date("Ym",$ViewInfo['Time']);
		return $this->db->insert($table_to_process,$ViewInfo);
	}

}

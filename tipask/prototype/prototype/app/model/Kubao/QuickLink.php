<?php
/**
 * 快捷链接mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Kubao_QuickLink extends Base_Widget
{
	//声明所用到的表
	protected $table = 'quicklink';

	//根据类型获取快速链接顶层信息
	public function getQuickLinkByType($LinkType,$fields = '*')
	{	
		$table_to_process = Base_Widget::getDbTable($this->table);
		$QuickLink = $this->db->selectRow($table_to_process,$fields,'`LinkType`=?',array($LinkType));
		return $QuickLink;
	}
	//根据顶层Id获取下级快速链接
	public function getQuickLinkByParent($Parent,$fields = '*')
	{	
		$table_to_process = Base_Widget::getDbTable($this->table);
		$QuickLink = $this->db->select($table_to_process,$fields,'`Parent`=?',array($Parent));
		return $QuickLink;
	}
}

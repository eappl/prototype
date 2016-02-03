<?php
/**
 * Source配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Detail.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Source_Detail extends Base_Widget
{
	/**
	 * Source表名
	 * @var string
	 */
	protected $table = 'user_source_detail';
	protected $table_source = 'user_source';
	protected $table_user_login = 'login_user';

	/**
	 * 获取单条记录
	 * @param integer $SourceDetail
	 * @param string $fields
	 * @return array
	 */
	public function getRow($SourceDetail,$field = '*')
	{
		$SourceDetail = trim($SourceDetail);
		return $this->db->selectRow($this->getDbTable(), $field, '`SourceDetail` = ?', array($SourceDetail));
	}

	/**
	 * 获取单个字段
	 * @param integer $SourceDetail
	 * @param string $field
	 * @return string
	 */
	public function getOne($SourceDetail,$field)
	{
		$SourceDetail = trim($SourceDetail);
		return $this->db->selectOne($this->getDbTable(), $field, '`SourceId` = ?', array($SourceDetail));
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
    /*
    *
    */
    public function replace(array $bind)
	{
		return $this->db->replace($this->getDbTable(), $bind);
	}
	/**
	 * 删除
	 * @param integer $SourceDetail
	 * @return boolean
	 */
	public function delete($SourceDetail)
	{
		$SourceDetail = trim($SourceDetail);

		return $this->db->delete($this->getDbTable(),'`SourceDetail` = ?', array($SourceDetail));
	}

	/**
	 * 更新
	 * @param integer $SourceDetail
	 * @param array $bind
	 * @return boolean
	 */
	public function update($SourceDetail, array $bind)
	{
		$SourceDetail = trim($SourceDetail);
		return $this->db->update($this->getDbTable(), $bind, '`SourceDetail` = ?', array($SourceDetail));
	}

	public function getAll($SourceList)
	{
		if($SourceList)
		{
			foreach($SourceList as $Key => $value)
			{
				$t[$Key] = $Key;	
			}
			$whereSource = " SourceId in (".implode(",",$t).")";	
		}
		else
		{
		 	$whereSource = "";
		}
		$whereCondition = array($whereSource);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT * FROM " . $this->getDbTable() . " where 1 ".$where." ORDER BY SourceId ASC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllSourceDetail[$value['SourceDetail']] = $value;	
			}	
		}
		return $AllSourceDetail;
	}
}

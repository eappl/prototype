<?php
class Config_Weixin extends Base_Widget
{
	/**
	 * app表名
	 * @var string
	 */
	protected $table = 'weixin';

	/**
	 * 获取单条记录
	 * @param integer $Id
	 * @param string $fields
	 * @return array
	 */
	public function getRow($Id, $fields = '*')
	{
		$Id = intval($Id);

		return $this->db->selectRow($this->getDbTable(), $fields, '`Id` = ?', $Id);
	}

	/**
	 * 获取单个字段
	 * @param integer $Id
	 * @param string $field
	 * @return string
	 */
	public function getOne($Id, $field)
	{
		$Id = intval($Id);

		return $this->db->selectOne($this->getDbTable(), $field, '`Id` = ?', $Id);
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
	 * @param integer $Id
	 * @return boolean
	 */
	public function delete($Id)
	{
		$Id = intval($Id);
		return $this->db->delete($this->getDbTable(), '`Id` = ?', $Id);
	}

	/**
	 * 更新
	 * @param integer $Id
	 * @param array $bind
	 * @return boolean
	 */
	public function update($Id, array $bind)
	{
		$Id = intval($Id);

		return $this->db->update($this->getDbTable(), $bind, '`Id` = ?', $Id);
	}

	/**
	 * 查询类型游戏
	 * @param $app_class
	 * @param $fields
	 * @return array
	 */
	public function getByClass($ClassId, $fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable() . " WHERE `ClassId` = ? ORDER BY Id ASC";
		return $this->db->getAll($sql, $ClassId);
	}

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAll($fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY Id ASC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllApp[$value['Id']] = $value;	
			}	
		}
		return $AllApp;
	}
    
	public function getUserNamebyDate($StartDate,$EndDate,$act)
	{
	    $whereStartDate = $StartDate?" `time` >= '".strtotime($StartDate)."' ":"";
	    $whereEndDate = $EndDate?" `time` <= '".strtotime($EndDate)."' ":"";
        $whereCondition = array($whereStartDate,$whereEndDate);
        $where = Base_common::getSqlWhere($whereCondition);
        
		$sql = "SELECT * FROM " . $this->getDbTable().$act . " where 1 $where ";
        
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllApp[] = $value['txt'];	
			}	
		}
		return $AllApp;
	}

}

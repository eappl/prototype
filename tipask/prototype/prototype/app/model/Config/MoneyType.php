<?php
/**
 * Quest配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: MoneyType.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_MoneyType extends Base_Widget
{
	/**
	 * Quest表名
	 * @var string
	 */
	protected $table = 'game_money_type';

	/**
	 * 获取单条记录
	 * @param integer $MoneyTypeId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($MoneyTypeId,$AppId,$field = '*')
	{
		$MoneyTypeId = intval($MoneyTypeId);
		$AppId = intval($AppId);

		return $this->db->selectRow(Base_Widget::getDbTable($this->table), $field, '`MoneyTypeId` = ? and `AppId` = ?', array($MoneyTypeId,$AppId));
	}

	/**
	 * 获取单个字段
	 * @param integer $MoneyTypeId
	 * @param string $field
	 * @return string
	 */
	public function getOne($MoneyTypeId,$AppId,$field)
	{
		$MoneyTypeId = intval($MoneyTypeId);
		$AppId = intval($AppId);

		return $this->db->selectOne(Base_Widget::getDbTable($this->table), $field, '`MoneyTypeId` = ? and `AppId` = ?', array($MoneyTypeId,$AppId));
	}

	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		return $this->db->insert(Base_Widget::getDbTable($this->table), $bind);
	}

	/**
	 * 删除
	 * @param integer $MoneyTypeId
	 * @return boolean
	 */
	public function delete($MoneyTypeId,$AppId)
	{
		$MoneyTypeId = intval($MoneyTypeId);
		$AppId = intval($AppId);

		return $this->db->delete(Base_Widget::getDbTable($this->table),'`MoneyTypeId` = ? and `AppId` = ?', array($MoneyTypeId,$AppId));
	}

	/**
	 * 更新
	 * @param integer $MoneyTypeId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($MoneyTypeId,$AppId, array $bind)
	{
		$MoneyTypeId = intval($MoneyTypeId);
		$AppId = intval($AppId);

		return $this->db->update(Base_Widget::getDbTable($this->table), $bind, '`MoneyTypeId` = ? and `AppId` = ?', array($MoneyTypeId,$AppId));
	}

	public function getAll($AppId,$fields = "*")
	{
		if($AppId)
		{
			$sql = "SELECT $fields FROM " . Base_Widget::getDbTable($this->table) . " where AppId = ? ORDER BY AppId,MoneyTypeId ASC";
			$return = $this->db->getAll($sql,$AppId);
		}
		else
		{
			$sql = "SELECT $fields FROM " . Base_Widget::getDbTable($this->table) . " ORDER BY AppId,MoneyTypeId ASC";
			$return = $this->db->getAll($sql);		
		}
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllMoneyType[$value['AppId']][$value['MoneyTypeId']] = $value;	
			}	
		}
		return $AllMoneyType;
	}

}

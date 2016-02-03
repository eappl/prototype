<?php
/**
 * Quest配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Money.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Money extends Base_Widget
{
	/**
	 * Quest表名
	 * @var string
	 */
	protected $table = 'game_money';

	/**
	 * 获取单条记录
	 * @param integer $MoneyId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($MoneyId,$AppId,$field = '*')
	{
		$MoneyId = intval($MoneyId);
		$AppId = intval($AppId);

		return $this->db->selectRow(Base_Widget::getDbTable($this->table), $field, '`MoneyId` = ? and `AppId` = ?', array($MoneyId,$AppId));
	}

	/**
	 * 获取单个字段
	 * @param integer $MoneyId
	 * @param string $field
	 * @return string
	 */
	public function getOne($MoneyId,$AppId,$field)
	{
		$MoneyId = intval($MoneyId);
		$AppId = intval($AppId);

		return $this->db->selectOne(Base_Widget::getDbTable($this->table), $field, '`MoneyId` = ? and `AppId` = ?', array($MoneyId,$AppId));
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
	 * @param integer $MoneyId
	 * @return boolean
	 */
	public function delete($MoneyId,$AppId)
	{
		$MoneyId = intval($MoneyId);
		$AppId = intval($AppId);

		return $this->db->delete(Base_Widget::getDbTable($this->table),'`MoneyId` = ? and `AppId` = ?', array($MoneyId,$AppId));
	}

	/**
	 * 更新
	 * @param integer $MoneyId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($MoneyId,$AppId, array $bind)
	{
		$MoneyId = intval($MoneyId);
		$AppId = intval($AppId);

		return $this->db->update(Base_Widget::getDbTable($this->table), $bind, '`MoneyId` = ? and `AppId` = ?', array($MoneyId,$AppId));
	}

	public function getAll($AppId,$fields = "*")
	{
		if($AppId)
		{
			$sql = "SELECT $fields FROM " . Base_Widget::getDbTable($this->table) . " where AppId = ? ORDER BY AppId,MoneyId ASC";
			$return = $this->db->getAll($sql,$AppId);
		}
		else
		{
			$sql = "SELECT $fields FROM " . Base_Widget::getDbTable($this->table) . " ORDER BY AppId,MoneyId ASC";
			$return = $this->db->getAll($sql);		
		}
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllMoney[$value['AppId']][$value['MoneyId']] = $value;	
			}	
		}
		return $AllMoney;
	}

}

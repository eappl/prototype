<?php
/**
 * Quest配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Type.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Product_Type extends Base_Widget
{
	/**
	 * Quest表名
	 * @var string
	 */
	protected $table = 'game_product_type';

	/**
	 * 获取单条记录
	 * @param integer $ProductTypeId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($ProductTypeId,$AppId,$field = '*')
	{
		$ProductTypeId = intval($ProductTypeId);
		$AppId = intval($AppId);

		return $this->db->selectRow($this->getDbTable(), $field, '`ProductTypeId` = ? and `AppId` = ?', array($ProductTypeId,$AppId));
	}

	/**
	 * 获取单个字段
	 * @param integer $ProductTypeId
	 * @param string $field
	 * @return string
	 */
	public function getOne($ProductTypeId,$AppId,$field)
	{
		$ProductTypeId = intval($ProductTypeId);
		$AppId = intval($AppId);

		return $this->db->selectOne($this->getDbTable(), $field, '`ProductTypeId` = ? and `AppId` = ?', array($ProductTypeId,$AppId));
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
	 * @param integer $ProductTypeId
	 * @return boolean
	 */
	public function delete($ProductTypeId,$AppId)
	{
		$ProductTypeId = intval($ProductTypeId);
		$AppId = intval($AppId);

		return $this->db->delete($this->getDbTable(),'`ProductTypeId` = ? and `AppId` = ?', array($ProductTypeId,$AppId));
	}

	/**
	 * 更新
	 * @param integer $ProductTypeId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($ProductTypeId,$AppId, array $bind)
	{
		$ProductTypeId = intval($ProductTypeId);
		$AppId = intval($AppId);

		return $this->db->update($this->getDbTable(), $bind, '`ProductTypeId` = ? and `AppId` = ?', array($ProductTypeId,$AppId));
	}

	public function getAll($AppId,$fields = "*")
	{
		if($AppId)
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " where AppId = ? ORDER BY AppId,ProductTypeId ASC";
			$return = $this->db->getAll($sql,$AppId);
		}
		else
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY AppId,ProductTypeId ASC";
			$return = $this->db->getAll($sql);		
		}
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllQuest[$value['AppId']][$value['ProductTypeId']] = $value;	
			}	
		}
		return $AllQuest;
	}

}

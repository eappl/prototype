<?php
/**
 * 地域数据
 * @author 陈晓东
 * $Id: Area.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Area extends Base_Widget
{
	/**
	 * 表名
	 * @var string
	 */
	protected $table = 'config_area';

	/**
	 * 初始化表名
	 * @return string
	 */
	public function init()
	{
		$this->table = Base_Widget::getDbTable($this->table);
	}

	/**
	 * 插入数据
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		return $this->db->insert($this->table, $bind);
	}

	/**
	 * 删除数据
	 * @param string $AreaId
	 * @return boolean
	 */
	public function delete($AreaId)
	{
		return $this->db->delete($this->table, '`AreaId` = ?', $AreaId);
	}

	/**
	 * 修改数据
	 * @param array $bind
	 * @param string $AreaId
	 * @return boolean
	 */
	public function update($AreaId, array $bind)
	{
		return $this->db->update($this->table, $bind, '`AreaId` = ?', $AreaId);
	}

	/**
	 * 查询单个字段
	 * @param $AreaId
	 * @param $fields
	 * @return array
	 */
	public function getOne($AreaId, $field='*')
	{
		$sql = "SELECT $field FROM {$this->table} WHERE `AreaId` = ?";
		return $this->db->getRow($sql, $AreaId);
	}
	/**
	 * 获取单行数据
	 * @param array $param
	 * @param $field
	 * @return array
	 */
	public function getRow($AreaId, $field = "*")
	{
		$sql = "SELECT $field FROM {$this->table} WHERE `AreaId` = ?";
		return $this->db->getRow($sql, $AreaId);	
	}
	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAll($fields = "*")
	{
		$sql = "SELECT $fields FROM $this->table ORDER BY AreaId ASC";
		$totalArea = $this->db->getAll($sql);
		foreach($totalArea as $Key => $value)
		{		
			$AreaList[$value['AreaId']] = $value;
		}
		return $AreaList;
	}
	/**
	 * 筛选符合所在地的地区列表
	 * @params is_abroad 所在地区 0:全部/1:国内/2:海外
	 * @params AreaList 地区列表数组
	 * @return array
	 */
	public function getAbroad($is_abroad,$AreaList)
	{
		$is_abroad = intval($is_abroad);
		if($is_abroad)
		{
			foreach($AreaList as $AreaId => $area_data)
			{
				if(($is_abroad!=$area_data['is_abroad'])&&($is_abroad!=0))
				{
					unset($AreaList[$AreaId]);
				}	
			}
		}
		return $AreaList;
	}
	/**
	 * 筛选符合所在地的地区列表
	 * @params AreaId 所在地区 0:全部/>0指定地区
	 * @params AreaList 地区列表数组
	 * @return array
	 */
	public function getArea($AreaId,$AreaList)
	{
		$AreaId = intval($AreaId);
		if($AreaId)
		{
			foreach($AreaList as $a => $area_data)
			{
				if(($AreaId!=$a)&&($AreaId!=0))
				{
					unset($AreaList[$a]);
				}	
			}
		}
		return $AreaList;
	}
}
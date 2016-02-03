<?php
/**
 * Hero配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Hero.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Hero extends Base_Widget
{
	/**
	 * Hero表名
	 * @var string
	 */
	protected $table = 'game_hero';

	/**
	 * 获取单条记录
	 * @param integer $HeroId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($HeroId,$AppId,$field = '*')
	{
		$HeroId = intval($HeroId);
		$AppId = intval($AppId);

		return $this->db->selectRow($this->getDbTable(), $field, '`HeroId` = ? and `AppId` = ?', array($HeroId,$AppId));
	}

	/**
	 * 获取单个字段
	 * @param integer $HeroId
	 * @param string $field
	 * @return string
	 */
	public function getOne($HeroId,$AppId,$field)
	{
		$HeroId = intval($HeroId);
		$AppId = intval($AppId);

		return $this->db->selectOne($this->getDbTable(), $field, '`HeroId` = ? and `AppId` = ?', array($HeroId,$AppId));
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
	 * @param integer $HeroId
	 * @return boolean
	 */
	public function delete($HeroId,$AppId)
	{
		$HeroId = intval($HeroId);
		$AppId = intval($AppId);

		return $this->db->delete($this->getDbTable(),'`HeroId` = ? and `AppId` = ?', array($HeroId,$AppId));
	}

	/**
	 * 更新
	 * @param integer $HeroId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($HeroId,$AppId, array $bind)
	{
		$HeroId = intval($HeroId);
		$AppId = intval($AppId);

		return $this->db->update($this->getDbTable(), $bind, '`HeroId` = ? and `AppId` = ?', array($HeroId,$AppId));
	}

	public function getAll($AppId,$fields = "*")
	{
		if($AppId)
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " where AppId = ? ORDER BY AppId,HeroId ASC";
			$return = $this->db->getAll($sql,$AppId);
		}
		else
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY AppId,HeroId ASC";
			$return = $this->db->getAll($sql);		
		}
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllHero[$value['AppId']][$value['HeroId']] = $value;	
			}	
		}
		return $AllHero;
	}

}

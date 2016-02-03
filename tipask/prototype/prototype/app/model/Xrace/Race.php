<?php
/**
 * 用户激活相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Race extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_race_catalog';
	protected $table_type = 'config_race_type';
	protected $table_group = 'config_race_group';
	protected $table_stage = 'config_race_stage';
	protected $maxRaceDetail = 5;

	protected $raceTimingType = array('chip'=>'芯片计时','gps'=>'gps定位');

	public function getTimingType()
	{
		return $this->raceTimingType;
	}
	public function getMaxRaceDetail()
	{
		return $this->maxRaceDetail;
	}
	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAllRaceCatalogList($fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " ORDER BY RaceCatalogId ASC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllRaceCatalog[$value['RaceCatalogId']] = $value;
				$AllRaceCatalog[$value['RaceCatalogId']]['comment'] = json_decode($AllRaceCatalog[$value['RaceCatalogId']]['comment'],true);
			}
		}
		return $AllRaceCatalog;
	}
	/**
	 * 获取单条记录
	 * @param integer $AppId
	 * @param string $fields
	 * @return array
	 */
	public function getRaceCatalog($RaceCatalogId, $fields = '*')
	{
		$RaceCatalogId = intval($RaceCatalogId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`RaceCatalogId` = ?', $RaceCatalogId);
	}
	/**
	 * 更新
	 * @param integer $AppId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateRaceCatalog($RaceCatalogId, array $bind)
	{
		$RaceCatalogId = intval($RaceCatalogId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`RaceCatalogId` = ?', $RaceCatalogId);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertRaceCatalog(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $AppId
	 * @return boolean
	 */
	public function deleteRaceCatalog($RaceCatalogId)
	{
		$RaceCatalogId = intval($RaceCatalogId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`RaceCatalogId` = ?', $RaceCatalogId);
	}
	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAllRaceGroupList($RaceCatalogId,$fields = "*")
	{
		$RaceCatalogId = intval($RaceCatalogId);
		//初始化查询条件
		$whereCatalog = ($RaceCatalogId != 0)?" RaceCatalogId = $RaceCatalogId":"";
		$whereCondition = array($whereCatalog);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

		$table_to_process = Base_Widget::getDbTable($this->table_group);
		$sql = "SELECT $fields FROM " . $table_to_process . "  where 1 ".$where." ORDER BY RaceCatalogId desc,RaceGroupId asc";
		$return = $this->db->getAll($sql);
		$AllRaceGroup = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllRaceGroup[$value['RaceGroupId']] = $value;
			}
		}
		return $AllRaceGroup;
	}
	/**
	 * 获取单条记录
	 * @param integer $AppId
	 * @param string $fields
	 * @return array
	 */
	public function getRaceGroup($RaceGroupId, $fields = '*')
	{
		$RaceGroupId = intval($RaceGroupId);
		$table_to_process = Base_Widget::getDbTable($this->table_group);
		return $this->db->selectRow($table_to_process, $fields, '`RaceGroupId` = ?', $RaceGroupId);
	}
	/**
	 * 更新
	 * @param integer $AppId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateRaceGroup($RaceGroupId, array $bind)
	{
		$RaceGroupId = intval($RaceGroupId);
		$table_to_process = Base_Widget::getDbTable($this->table_group);
		return $this->db->update($table_to_process, $bind, '`RaceGroupId` = ?', $RaceGroupId);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertRaceGroup(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_group);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $AppId
	 * @return boolean
	 */
	public function deleteRaceGroup($RaceGroupId)
	{
		$RaceGroupId = intval($RaceGroupId);
		$table_to_process = Base_Widget::getDbTable($this->table_group);
		return $this->db->delete($table_to_process, '`RaceGroupId` = ?', $RaceGroupId);
	}
	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAllRaceStageList($RaceCatalogId,$fields = "*")
	{
		$RaceCatalogId = trim($RaceCatalogId);
		//初始化查询条件
		$whereCatalog = ($RaceCatalogId != 0)?" RaceCatalogId = $RaceCatalogId":"";
		$whereCondition = array($whereCatalog);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

		$table_to_process = Base_Widget::getDbTable($this->table_stage);
		$sql = "SELECT $fields FROM " . $table_to_process . "  where 1 ".$where." ORDER BY RaceCatalogId,RaceStageId ASC";
		$return = $this->db->getAll($sql);
		$AllRaceGroup = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllRaceStage[$value['RaceStageId']] = $value;
			}
		}
		return $AllRaceStage;
	}
	/**
	 * 获取单条记录
	 * @param integer $AppId
	 * @param string $fields
	 * @return array
	 */
	public function getRaceStage($RaceStageId, $fields = '*')
	{
		$RaceStageId = intval($RaceStageId);
		$table_to_process = Base_Widget::getDbTable($this->table_stage);
		return $this->db->selectRow($table_to_process, $fields, '`RaceStageId` = ?', $RaceStageId);
	}
	/**
	 * 更新
	 * @param integer $AppId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateRaceStage($RaceStageId, array $bind)
	{
		$RaceStageId = intval($RaceStageId);
		$table_to_process = Base_Widget::getDbTable($this->table_stage);
		return $this->db->update($table_to_process, $bind, '`RaceStageId` = ?', $RaceStageId);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertRaceStage(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_stage);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $AppId
	 * @return boolean
	 */
	public function deleteRaceStage($RaceStageId)
	{
		$RaceStageId = intval($RaceStageId);
		$table_to_process = Base_Widget::getDbTable($this->table_stage);
		return $this->db->delete($table_to_process, '`RaceStageId` = ?', $RaceStageId);
	}
	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAllRaceTypeList($fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table_type);
		$sql = "SELECT $fields FROM " . $table_to_process . " ORDER BY RaceTypeId ASC";
		$return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllRaceType[$value['RaceTypeId']] = $value;
				$AllRaceType[$value['RaceTypeId']]['comment'] = json_decode($AllRaceType[$value['RaceTypeId']]['comment'],true);
			}
		}
		return $AllRaceType;
	}
	/**
	 * 获取单条记录
	 * @param integer $AppId
	 * @param string $fields
	 * @return array
	 */
	public function getRaceType($RaceTypeId, $fields = '*')
	{
		$RaceTypeId = intval($RaceTypeId);
		$table_to_process = Base_Widget::getDbTable($this->table_type);
		return $this->db->selectRow($table_to_process, $fields, '`RaceTypeId` = ?', $RaceTypeId);
	}
	/**
	 * 更新
	 * @param integer $AppId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateRaceType($RaceTypeId, array $bind)
	{
		$RaceTypeId = intval($RaceTypeId);
		$table_to_process = Base_Widget::getDbTable($this->table_type);
		return $this->db->update($table_to_process, $bind, '`RaceTypeId` = ?', $RaceTypeId);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertRaceType(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_type);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $AppId
	 * @return boolean
	 */
	public function deleteRaceType($RaceTypeId)
	{
		$RaceTypeId = intval($RaceTypeId);
		$table_to_process = Base_Widget::getDbTable($this->table_type);
		return $this->db->delete($table_to_process, '`RaceTypeId` = ?', $RaceTypeId);
	}
}

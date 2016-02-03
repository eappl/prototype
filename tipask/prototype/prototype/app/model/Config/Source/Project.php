<?php
/**
 * SourceProject配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Project.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Source_Project extends Base_Widget
{
	/**
	 * SourceProject表名
	 * @var string
	 */
	protected $table = 'user_source_project';
	protected $table_detail = 'user_source_project_detail';

	/**
	 * 获取单条记录
	 * @param integer $SourceProjectId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($SourceProjectId,$field = '*')
	{
		$SourceProjectId = trim($SourceProjectId);

		return $this->db->selectRow($this->getDbTable(), $field, '`SourceProjectId` = ?', $SourceProjectId);
	}
	
	public function getDetail($SourceProjectId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_detail);
		$sql = "SELECT $fields FROM $table_to_process where `SourceProjectId` = ? ORDER BY `StartDate` DESC";
		return $this->db->getAll($sql,$SourceProjectId);		
	}
	
	public function getSingleDetail($SourceProjectId,$SourceProjectDetailId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_detail);
		$sql = "SELECT $fields FROM $table_to_process where `SourceProjectId` = ? and `SourceProjectDetailId` = ? ORDER BY `StartDate` DESC";
		return $this->db->getRow($sql,array($SourceProjectId,$SourceProjectDetailId));		
	}

	/**
	 * 获取单个字段
	 * @param integer $SourceProjectId
	 * @param string $field
	 * @return string
	 */
	public function getOne($SourceProjectId,$field)
	{
		$SourceProjectId = trim($SourceProjectId);

		return $this->db->selectOne($this->getDbTable(), $field, '`SourceProjectId` = ?', $SourceProjectId);
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
	 * @param integer $SourceProjectId
	 * @return boolean
	 */
	public function delete($SourceProjectId)
	{
		$SourceProjectId = trim($SourceProjectId);

		return $this->db->delete($this->getDbTable(),'`SourceProjectId` = ?', $SourceProjectId);
	}

	/**
	 * 更新
	 * @param integer $SourceProjectId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($SourceProjectId, array $bind)
	{
		$SourceProjectId = trim($SourceProjectId);

		return $this->db->update($this->getDbTable(), $bind, '`SourceProjectId` = ? ', $SourceProjectId);
	}

	public function getAll($fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " ORDER BY SourceProjectId ASC";
		$return = $this->db->getAll($sql);		

		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllSourceProject[trim($value['SourceProjectId'])] = $value;	
			}	
		}
		return $AllSourceProject;
	}
	public function updateDetail($SourceProjectId,$SourceProjectDetailId, array $bind)
	{
		$SourceProjectId = trim($SourceProjectId);
		$SourceProjectDetailId = trim($SourceProjectDetailId);
		$table_to_process = Base_Widget::getDbTable($this->table_detail);
		return $this->db->update($table_to_process, $bind, '`SourceProjectId` = ? and `SourceProjectDetailId` = ? ', array($SourceProjectId,$SourceProjectDetailId));
	}
	
	public function deleteDetail($SourceProjectId,$SourceProjectDetailId)
	{
		$SourceProjectId = trim($SourceProjectId);
		$SourceProjectDetailId = trim($SourceProjectDetailId);
		$table_to_del = Base_Widget::getDbTable($this->table_detail);
		return $this->db->delete($table_to_del, '`SourceProjectId` = ? and `SourceProjectDetailId` = ? ', array($SourceProjectId,$SourceProjectDetailId));
	}
	public function insertDetail(array $bind)
	{
		$table_to_insert = Base_Widget::getDbTable($this->table_detail);		
		return $this->db->insert($table_to_insert, $bind);
	}
	public function getUserProject($SourceId,$SourceDetail,$RegDate)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_detail);
		$sql = "SELECT min(SourceProjectId) as m FROM $table_to_process where `SourceId` = ? and (`SourceDetail` = 0 or `SourceDetail` = ?) and `StartDate` <= ? and `EndDate` >= ? ";
		return $this->db->getOne($sql,array($SourceId,$SourceDetail,$RegDate,$RegDate));	
	}
	public function getDetailCost($SourceProjectId,$SourceId,$SourceDetail,$Date)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_detail);
		$sql = "SELECT sum(cost) as TotalCost FROM $table_to_process where `SourceProjectId` = $SourceProjectId and `SourceId` = $SourceId and `SourceDetail` = $SourceDetail and `StartDate` <= '$Date' and `EndDate` >= '$Date'";
		return $this->db->getOne($sql);		
	}
    public function replaceDetail(array $bind)
	{
		/*$SourceDetail = $bind['SourceDetail'];
        $table_to_process = Base_Widget::getDbTable($this->table_detail);        
		$project_detail_row = $this->db->selectRow($table_to_process, '*', '`SourceDetail` = ?', $SourceDetail);
        //selectRow 返回false 或者数组
        if(!empty($project_detail_row))
        {
            return $this->db->update($table_to_process, $bind, '`SourceDetail` = ? ', $SourceDetail);
        }else{
            return $this->db->insert($table_to_process, $bind);
        }*/
        $this->db->replace(Base_Widget::getDbTable($this->table_detail),$bind);
		
	}
}

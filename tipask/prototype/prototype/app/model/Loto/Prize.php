<?php
/**
 * Prize配置管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Prize.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Loto_Prize extends Base_Widget
{
	/**
	 * Prize表名
	 * @var string
	 */
	protected $table = 'loto_prize_list';
	protected $table_detail = 'loto_prize_detail_list';
	protected $table_log = 'loto_log';

	/**
	 * 获取单条记录
	 * @param integer $LotoPrizeId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($LotoPrizeId,$field = '*')
	{
		$LotoPrizeId = intval($LotoPrizeId);

		return $this->db->selectRow($this->getDbTable(), $field, '`LotoPrizeId` = ?', $LotoPrizeId);
	}
	public function getDetail($LotoPrizeDetailId,$field = '*')
	{
		$LotoPrizeDetailId = intval($LotoPrizeDetailId);
		return $this->db->selectRow($this->getDbTable($this->table_detail), $field, '`LotoPrizeDetailId` = ?', $LotoPrizeDetailId);
	}

	/**
	 * 获取单个字段
	 * @param integer $LotoPrizeId
	 * @param string $field
	 * @return string
	 */
	public function getOne($LotoPrizeId,$field = '*')
	{
		$LotoPrizeId = intval($LotoPrizeId);

		return $this->db->selectOne($this->getDbTable(), $field, '`LotoPrizeId` = ?', $LotoPrizeId);
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
	 * @param integer $LotoPrizeId
	 * @return boolean
	 */
	public function delete($LotoPrizeId,$LotoId)
	{
		$LotoPrizeId = intval($LotoPrizeId);
		$LotoId = intval($LotoId);

		return $this->db->delete($this->getDbTable(),'`LotoPrizeId` = ?',$LotoPrizeId);
	}

	/**
	 * 更新
	 * @param integer $LotoPrizeId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($LotoPrizeId, array $bind)
	{
		$LotoPrizeId = intval($LotoPrizeId);

		return $this->db->update($this->getDbTable(), $bind, '`LotoPrizeId` = ?',$LotoPrizeId);
	}

	public function getAll($LotoId,$fields = "*")
	{
		if($LotoId)
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " where LotoId = ? ORDER BY LotoId,LotoPrizeId ASC";
			$return = $this->db->getAll($sql,$LotoId);
		}
		else
		{
			$sql = "SELECT $fields FROM " . $this->getDbTable() . " ORDER BY LotoId,LotoPrizeId ASC";
			$return = $this->db->getAll($sql);		
		}
		$AllPrize = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AllPrize[$value['LotoId']][$value['LotoPrizeId']] = $value;	
			}	
		}
		return $AllPrize;
	}
	public function getAllPrizeDetail($LotoPrizeId,$fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->getDbTable($this->table_detail) . " where LotoPrizeId = ? ORDER BY LotoPrizeId ASC";
		return $this->db->getAll($sql,$LotoPrizeId);
	}
	public function updateDetail($LotoPrizeDetailId, array $bind)
	{
		$LotoPrizeDetailId = intval($LotoPrizeDetailId);
		return $this->db->update($this->getDbTable($this->table_detail), $bind, '`LotoPrizeDetailId` = ?',$LotoPrizeDetailId);
	}
	public function insertDetail(array $bind)
	{
		return $this->db->insert($this->getDbTable($this->table_detail), $bind);
	}
	public function deleteDetail($LotoPrizeDetailId)
	{
		$LotoPrizeDetailId  = intval($LotoPrizeDetailId );

		return $this->db->delete($this->getDbTable($this->table_detail),'`LotoPrizeDetailId` = ?',$LotoPrizeDetailId);
	}
	public function getCurrentRate($LotoId,$Time)
	{
		$select_fields = array(
		'*');

			
		$whereTime = $Time?" (StartTime <= ".$Time." and EndTime >= ".$Time.")":"";
		$whereLoto = $LotoId?" LotoId = ".$LotoId:"";
		$whereCount = " LotoPrizeCountUsed < LotoPrizeCount ";  

		$whereCondition = array($whereLoto,$whereTime,$whereCount);
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		//生成条件列
    $table_name = Base_Widget::getDbTable($this->table_detail);

    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where;
		$PrizeArr = $this->db->getAll($sql,false);
		$StatArr = array('PrizeList'=>array());
		if(is_array($PrizeArr))
		{
      foreach ($PrizeArr as $key => $Stat) 
			{
				$StatArr['PrizeDetailList'][$Stat['LotoPrizeDetailId']] = $Stat;
			}				
		}
		return $StatArr;
	}
	public function updatePrizeCount($LotoPrizeDetailId,$Count)
	{
		$table_to_update = Base_Widget::getDbTable($this->table_detail);
		$sql = "UPDATE ".$table_to_update." SET `LotoPrizeCountUsed` = `LotoPrizeCountUsed` + $Count WHERE `LotoPrizeDetailId` = ? and `LotoPrizeCountUsed` < `LotoPrizeCount`";
		return $this->db->query($sql, $LotoPrizeDetailId);	

	}
	public function insertLotoLog(array $bind)
	{
		$this->db->begin();
		$log_table = $this->createLotoLog($bind['LotoId']);
		$log = $this->db->insert($log_table, $bind);
		$update = $bind['LotoPrizeId']?$this->updatePrizeCount($bind['LotoPrizeDetailId'],1):ture;
		if($log&&$update)
		{
			$this->db->commit();
			return $log;	
		}
		else
		{
			$this->db->rollback();
			return false;			 	
		}
	}
	public function createLotoLog($LotoId)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_log);
		$table_to_process = Base_Widget::getDbTable($this->table_log)."_".$LotoId;
		$exist = $this->db->checkTableExist($table_to_process);
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE " . $table_to_check;
			$row = $this->db->getRow($sql);
			$sql = $row['Create Table'];
			$sql = str_replace('`' . $this->table_log . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
			$create = $this->db->query($sql);
			if($create)
			{
				return $table_to_process;
			}
			else
			{
			 return false;	
			}		 	
		}
	}
	public function getUserLotoLog($UserId,$LotoId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_log)."_".$LotoId;
		$sql = "select $fields from $table_to_process where `LotoId` = ? and `UserId` = ?";
		$StatArr = array();
		$return = $this->db->getAll($sql, array($LotoId,$UserId));
		if(is_array($return))
		{
			foreach($return as $key => $value)
			{
				$StatArr[$value['LotoLogId']] = $value;	
			}
		}
		return $StatArr;
		
	}
	public function updateLotoLog($LotoId,$LotoLogId,$bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_log)."_".$LotoId;
		return $this->db->update($table_to_process, $bind, '`LotoLogId` = ? and `LotoId` = ?',array($LotoLogId,$LotoId));
	}

}

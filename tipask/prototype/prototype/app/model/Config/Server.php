<?php
/**
 * 服务器数据
 * @author Chen<cxd032404@hotmail.com>
 * $Id: Server.php 15195 2014-07-23 07:18:26Z 334746 $
 */
class Config_Server extends Base_Widget
{
	/**
	 * server表
	 * @var string
	 */
	protected $table = 'config_server';

	/**
	 * 获取单条记录
	 * @param integer $ServerId
	 * @param string $fields
	 * @return array
	 */
	public function getRow($ServerId, $fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`ServerId` = ?', $ServerId);
	}
	
	/**
	 * 以游戏与合作商查询
	 * @param $AppId
	 * @param $PartnerId
	 * @param $fields
	 * @return array
	 */
	public function getAppPartnerRow($AppId, $PartnerId, $fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->select($table_to_process, $fields, ' `PartnerId` = ? and `AppId` = ? ORDER BY ServerId DESC', array($PartnerId, $AppId));
	}

	/**
	 * 获取单个字段
	 * @param integer $ServerId
	 * @param string $field
	 * @return string
	 */
	public function getOne($ServerId, $field)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectOne($table_to_process, $field, '`ServerId` = ?', $ServerId);
	}
	
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insert(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $ServerId
	 * @return boolean
	 */
	public function delete($ServerId)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`ServerId` = ?', $ServerId);
	}

	/**
	 * 获取所有区服
	 * @param string $fields
	 * @return array
	 */
	public function getAll($fields = '*',$is_show = 0)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		if(!$is_show)
		{
			$sql = "SELECT $fields FROM " . $table_to_process . " ORDER BY ServerId ASC";
		}
		else
		{		 			
			$sql = "SELECT $fields FROM " . $table_to_process . " where is_show = $is_show ORDER BY ServerId ASC";
		}
		$return = $this->db->getAll($sql);
		foreach($return as $key => $value)
		{
			$ServerList[$value['ServerId']] = $value;	
		}
		return $ServerList;
	}

	/**
	 * 按游戏ID查询
	 * @param $AppId
	 * @param $fields
	 * @return array
	 */
	public function getByApp($AppId, $fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM $table_to_process WHERE `AppId` = ? ORDER BY ServerId ASC";
		return $this->db->getAll($sql, $AppId);
	}
	/**	
	 * 按服务器IP查询
	 * @param $ServerIp
	 * @param $fields
	 * @return array
	 */
	public function getByIp($ServerIp)
	{
		$Server = array();		
		$ServerList = (@include(__APP_ROOT_DIR__."/etc/Server.php"));
		foreach ($ServerList as $value) {
		 	 $value['ServerIp'] = long2ip($value['ServerIp']);
		 	 if($ServerIp == $value['ServerIp'])
		 	 {
		 	 	return $Server = $value;
		 	 }
		} 
		return $Server;		
	}

	/**
	 * 按合作商查询
	 * @param $PartnerId
	 * @param $fields
	 * @return array
	 */
	public function getByPartner($PartnerId, $fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM $table_to_process WHERE `PartnerId` = ? ORDER BY ServerId ASC";
		return $this->db->getAll($sql, $PartnerId);
	}

	/**
	 * 获取指定合作商的app区服
	 * @param integer $AppId
	 * @param integer $PartnerId
	 * @param string $fields
	 * @return array
	 */
	public function getByAppPartner($AppId,$PartnerId,$fields = '*',$is_show = 0)
	{
		$whereApp = $AppId?" AppId = $AppId":"";
		$wherePartner = $PartnerId?" PartnerId = $PartnerId":"";

		$whereCondition = array($whereApp,$wherePartner);

		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);

		if(!$is_show)
		{
			$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where;
		}
		else
		{		 			
			$sql = "SELECT $fields FROM " . $table_to_process . " where is_show = $is_show ".$where;
		}
		$return = $this->db->getAll($sql);
		if($return)
		{
			foreach($return as $key => $value)
			{
				$AllServer[$value['ServerId']] = $value;	
			}	
		}
		
		return $AllServer;
	}
	
	/**
	 * 以游戏和合作商获取区服列表
	 * @param $AppId
	 * @param $PartnerId
	 * @param $fields
	 * @return array
	 */
	public function getByServer($AppId, $PartnerId = 1, $fields = 'ServerId,name,start_time,next_start,next_stop')
	{
		$serverArr = $this->getByAppPartner($AppId, $PartnerId, $fields);
		$start_time = time();
		foreach ($serverArr as $k => $v)
		{
			if($start_time <= $v['start_time'])
				unset($serverArr[$k]);
			if ($start_time > $v['next_stop'] && $start_time < $v['next_start'])
				unset($serverArr[$k]);
		}
		return $serverArr;
	}
	
	/**
	 * 慎用
	 * @param string $fields
	 * @param array $param
	 * @return array
	 */
	public function getByParam($param , $fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$condition="where 1=1 ";
		foreach($param as $k => $v)
			if(!empty($v))
				$condition.=" and $k='$v' ";
		
		$sql = "SELECT $fields FROM $table_to_process $condition ";

		return  $this->db->getAll($sql);
	}
	
	/**
	 * 更新单条区服数据
	 * @param integer $ServerId
	 * @param array $bind
	 * @return boolean
	 */
	public function update($ServerId, array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`ServerId` = ?', $ServerId);
	}
	
	/**
	 * 更新批量合作商区服数据
	 * @param integer $parnter_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updatePartner($parnter_id, array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return  $this->db->update($table_to_process, $bind, '`PartnerId` = ?', $parnter_id);
	}

	/*
	*
	*/
	public function reBuildServerConfig($fields = "*")
	{
		$AllServer = $this->getAll($fields);
		$file_path = "../../etc/";
		$file_name = "Server.php";
		$var = var_export($AllServer,true);
		$text ='<?php $AllServer='.$var.'; return $AllServer;?>';		
		file_put_contents($file_path.$file_name,$text);			
	}
}

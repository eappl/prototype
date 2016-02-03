<?php
/**
 * 登录日志
 * @author <cxd032404@hotmail.com>
 * $Id: LogsManager.php 15195 2014-07-23 07:18:26Z 334746 $
 */
class Log_LogsManager extends Base_Widget
{
	protected $table = 'config_logs_manager';
	
	protected $logArr = array();

	/**
	 * 初始化表名
	 * @return string
	 */
	public function init()
	{
		parent::init();
		$this->table = $this->getDbTable($this->table);
	}
	
	public function insert()
	{
		return true;
//		return $this->db->insert($this->table, $this->logArr);
	}
	
	public function push($key, $value)
	{
		$this->logArr[$key] = $value;
	}

	/**
	 * 查询类型游戏
	 * @param $app_class
	 * @param $fields
	 * @return array
	 */
	public function getNameAll($name, $fields = "*")
	{
		$sql = "SELECT $fields FROM {$this->table} WHERE `name` = ? ORDER BY addtime DESC";
		return $this->db->getAll($sql, $name);
	}
	
	/**
	 * 单条数据
	 * @param string $name
	 * @return int
	 */
	public function getCount($name, $fields = "count(*)")
	{
		$sql = "SELECT $fields FROM {$this->table}";
		if(!empty($name))
		{
			$sql .= " WHERE `name` in ($name)";
		}
		return $this->db->getOne($sql);
	}
	
	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAll($name, $number = 20, $offset = 0, $fields = "*")
	{
		$where = "";
		if(!empty($name))
		{
			$where = " WHERE `name` in ($name)";
		}
		$sql = "SELECT $fields FROM {$this->table} $where  ORDER BY id DESC";
		return $this->db->limitQuery($sql, array(), $offset, $number);
	}
	/*selena
	*服务器修改日志
	*/
	public function getLogsManagerParams($fields,$LogInfo,$StartDate,$EndDate)
	{
		//$whereId = $ManageId ? "manager_id=".$ManageId:"";
		$whereLog = $LogInfo? "log LIKE '%".$LogInfo."%'":"";
		
		$whereStartDate = $StartDate?" addtime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" addtime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereCondition = array($whereLog,$whereStartDate,$whereEndDate);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM {$this->table} where 1 ".$where." ORDER BY addtime ASC";
		//echo $sql."<br/>";
		$MachineLog = $this->db->getAll($sql);
		$MachineLogList = array();
		
		foreach($MachineLog as $key=> $LogInfo)
		{
			$MachineList[$key]['Udate'] = $LogInfo['addtime'];
			$LogArr = explode("\n\n",$LogInfo['log']);
			$MachineIdStr = explode(":",$LogArr[2]);
			$MachineId = $MachineIdStr[1];
			$Tip = mb_substr($LogArr[0],0,2,"utf-8");
			$BeforeMachineInfo = json_decode($LogArr[3],true);
			
			if($LogArr[4]) //有5个值的是修改，没有的是 添加和删除
			{
				$NextMachineInfo = json_decode($LogArr[4],true);
			
				foreach($NextMachineInfo as $key => $val)
				{
					if($key=="Comment")
					{
						$NextComment = json_decode($val,true);
						$NextMachineInfo["Comment"] = $NextComment;
						$BeforeComment = json_decode($BeforeMachineInfo["Comment"],true);
						foreach($NextComment as $ckey=> $cval)
						{
							if($cval!=$BeforeComment[$ckey])
							{
								if($ckey=="Status")
								{
									$NextMachineInfo["Comment"][$ckey."_span"] = $cval;
								}else{									
									$NextMachineInfo["Comment"][$ckey] = "<span style='color:red;'>".$cval."</span>";		
								}
								
							}
						}
						$NextMachineInfo["Comment"] = json_encode($NextMachineInfo["Comment"]);
					}elseif($val != $BeforeMachineInfo[$key])
					{
						if($key=="CageId"||$key=="ServerId"||$key=="LocalIP"||$key=="WebIP"||$key=="IntellectProperty"||$key=="Flag")
						{
							$NextMachineInfo[$key."_span"] = $val;
						}else{									
							$NextMachineInfo[$key] = "<span style='color:red;'>".$val."</span>";	
						}
												
					}
					
				}
				$NextMachineInfo['LogDate'] = date("Y-m-d H:i:s",$LogInfo['addtime']);
				$NextMachineInfo['Name'] = $LogInfo['name'];	
				unset($NextMachineInfo['Udate']);
				$NextMachineInfo['Tip'] = $Tip;
				$MachineLogList[$MachineId][] = $NextMachineInfo;
				
			}else{
				$BeforeMachineInfo['LogDate'] = date("Y-m-d H:i:s",$LogInfo['addtime']);
				$BeforeMachineInfo['Name'] = $LogInfo['name'];	
				$BeforeMachineInfo['Tip'] = $Tip;
				$MachineLogList[$MachineId][] = $BeforeMachineInfo;				
			}

		}
		/*foreach($MachineLog as $key=> $LogInfo)
		{
			$MachineList[$key]['Udate'] = $LogInfo['addtime'];
			//echo $LogInfo['log'];
			$LogArr = explode("\n\n",$LogInfo['log']);
			//echo "<pre>";
			//	print_r($LogArr);
			$MachineIdStr = explode(":",$LogArr[2]);
			$MachineId = $MachineIdStr[1];
			$Tip = mb_substr($LogArr[0],0,2,"utf-8");
			$BeforeMachineInfo = json_decode($LogArr[3],true);
				
			if($LogArr[4]) //有5个值的是修改，没有的是 添加和删除
			{
				$NextMachineInfo = json_decode($LogArr[4],true);
			
				$NextMachineInfo['LogDate'] = date("Y-m-d H:i:s",$LogInfo['addtime']);
				$NextMachineInfo['Name'] = $LogInfo['name'];	
				unset($NextMachineInfo['Udate']);
				$NextMachineInfo['Tip'] = $Tip;
				$MachineLogList[$MachineId][] = $NextMachineInfo;
				
			}else{
			
				$BeforeMachineInfo['LogDate'] = date("Y-m-d H:i:s",$LogInfo['addtime']);
				$BeforeMachineInfo['Name'] = $LogInfo['name'];	
				$BeforeMachineInfo['Tip'] = $Tip;
				$MachineLogList[$MachineId][] = $BeforeMachineInfo;				
			}

		}*/
		
		return $MachineLogList;
	}
	
}
<?php
/**
 * 数据权限控制
 * @author Chen <cxd032404@hotmail.com>
 * $Id: Permission.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Permission extends Base_Widget
{

	protected $table = 'config_partner_permission';
	protected $table2 = 'config_partner_permission2';
	protected $table_date = 'date_permission';
	protected $oAppData;
	/**
	 * 初始化表名
	 * @return string
	 */
	public function init()
	{
		parent::init();
		$this->table = $this->getDbTable($this->table);
		$this->table_date = $this->getDbTable($this->table_date);
	}
	/**
	 * 获取指定用户组允许的AppId
	 * @param integer $data_groups
	 * @return array
	 */
	public function getApp($data_groups,$fields='*')
	{
		$returnArr = array();
		//声明需要调取的app类
		$oAppData = new Config_App();
		
		$data_groups = trim($data_groups);
		$table_to_process = $this->getDbTable($this->table);
		//查找指定data_groups的数据
		$sql = "SELECT distinct(AppId) FROM $table_to_process where group_id in (".$data_groups.")";
		$level_app = $this->db->getAll($sql);
		if(($level_app))
		{
			foreach($level_app as $key => $value)
			{
				//默认所有游戏
				if($value['AppId']==0)
				{
					$app_data = $oAppData->getAll($fields);
					if(isset($app_data))
					{
						foreach($app_data as $k => $v)
						{
							if(!isset($returnArr[$v['AppId']]))
							$returnArr[$v['AppId']] = $v;
						}	
					}
				}
				else
				{
					$app_data = $oAppData->getRow($value['AppId'], $fields);
					if(isset($app_data))
					{
						if(!isset($returnArr[$app_data['AppId']]))
						$returnArr[$app_data['AppId']] = $app_data;
					}						
				}
			}
		}
		return $returnArr;

	}
		/**
	 * 获取指定用户组允许查看游戏的合作商
	 * @param integer $data_groups
	 * @param integer $AppId
	 * @return array
	 */
	public function getPartner($data_groups,$AppId,$fields)
	{
		$returnArr = array();
		//声明需要调取的partner_app类
		$oPartnerAppData = new Config_Partner_App();
		$AppId = abs(intval($AppId));
		$data_groups = trim($data_groups);
		//查找指定data_groups的数据
$table_to_process = $this->getDbTable($this->table);
		$sql = "SELECT * from $table_to_process where group_id in ($data_groups) and (AppId = ? or AppId = 0) group by PartnerId,partner_type,AreaId,partner_type";
		$level_partner = $this->db->getAll($sql,$AppId);
		if(($level_partner))
		{
			foreach($level_partner as $key => $value)
			{
				
				$whereType = $oPartnerAppData->getWherePartnerType($value['partner_type']);
				$partner_data = $oPartnerAppData->getPartnerAppbyType($fields,$AppId,$value['PartnerId'],$value['AreaId'],$whereType);
				if(isset($partner_data))
				{
					foreach($partner_data as $k => $v)
					{
						if(!isset($returnArr[$v['PartnerId']]))
						$returnArr[$v['PartnerId']] = $v;
					}	
				}
			}
		}
		return $returnArr;		
	}
	/**
	 * 获取指定用户组允许查看游戏的合作商
	 * @param integer $data_groups
	 * @param integer $AppId
	 * @return array
	 */
	public function getServer($data_groups,$AppId,$PartnerId,$fields)
	{
		$returnArr = array();
		$oServerData = new Config_Server();
		
		$AppId = abs(intval($AppId));
		$PartnerId = abs(intval($PartnerId));
		$data_groups = trim($data_groups);
		$app_list = $this->getApp($data_groups,"AppId");
		if(isset($app_list[$AppId]))
		{
			$partner_list = $this->getPartner($data_groups,$AppId,'PartnerId');
			if(isset($partner_list[$PartnerId]))//判断运营商是否在这个数据组的合作商的列表内
			{
//				if($partner_list[$PartnerId]['mixed_sign']!='')
//				{
//					$PartnerId = 1;
//				}
				$server_list = $oServerData->getByAppPartner($AppId,$PartnerId,$fields);
				if(isset($server_list))
				{
					foreach($server_list as $key => $value)
					{
							$returnArr[$value['ServerId']] = $value;
					}	
				}
			}
		}
		return $returnArr;		
	}
	/**
	 * 获得权限总列表
	 * @return array
	 *
	**/
	public function listTotalParterPermission($fields)
	{
		$oPartnerApp = new Config_Partner_App();
		$oApp = new Config_App();
		$oArea = new Config_Area();
		$TotalArea = $oArea->getAll('AreaId,name');
		if(isset($TotalArea))
		{
			foreach($TotalArea as $AreaId => $value)
			{
				$TotalArea[$AreaId]['partner_type'] = array('1'=>array('name'=>'官服','permission'=>0),'2'=>array('name'=>'专区','permission'=>0));
			}
		}

		$PartnerAppList = $oPartnerApp->getAll($fields); 
		$totalPartner = array();
		$totalPartner['total'] = $TotalArea;
		if(is_array($PartnerAppList))
		{
			foreach($PartnerAppList as $key => $value)
			{
				$value['permission'] = 0;
				if(!isset($totalPartner['list'][$value['AppId']]))
				{
					$name = $oApp->getOne($value['AppId'], 'name');
					$totalPartner['list'][$value['AppId']] = array('name'=>$name,'default'=>$TotalArea,'partner'=>array());
				}
				if(!isset($totalPartner['list'][$value['AppId']]['partner'][$value['PartnerId']]))
				{
					$totalPartner['list'][$value['AppId']]['partner'][$value['PartnerId']] = $value;
				}
			}
		}
		return $totalPartner;
	}
	/**
	 * 根据权限数组获得可查询的合作商配置页面
	 * @param $data_groups
	 * @return array
	 *
	**/
	public function listParterPermission($group_id)
	{
		$group_id = intval($group_id);
		$default_permission = $this->listParterDefaultPermission($group_id);
		$permission_array = array();

		$permission_array = $this->getApp($group_id,'AppId');
		$totalPartner = $this->listTotalParterPermission('PartnerId,AppId,name');
		if(count($permission_array))
		{
			foreach($permission_array as $app => $app_value)
			{
				$partner_list = $this->getPartner($group_id,$app,'PartnerId');
				if(count($partner_list))
				{
					$p_list = array();
					foreach($partner_list as $partner => $partner_value)
					{
						if(isset($totalPartner['list'][$app]['partner'][$partner]))
						{
							$totalPartner['list'][$app]['partner'][$partner]['permission'] = 1;
						}
					}	
				}
			}
			if(isset($default_permission['partner']))
			{
				foreach($default_permission['partner'] as $AppId => $partner_data)
				{
					foreach($partner_data as $AreaId => $partner_type_data)
					{
						foreach($partner_type_data as $partner_type => $value)
						{
							$totalPartner['list'][$AppId]['default'][$AreaId]['partner_type'][$partner_type]['permission']=1;
						}
					}			
				}
			}
			if(isset($default_permission['app']))
			{
				foreach($default_permission['app'] as $AreaId => $partner_type_data)
				{
					foreach($partner_type_data as $partner_type => $value)
					{
						$totalPartner['total'][$AreaId]['partner_type'][$partner_type]['permission']=1;
					}
				}
			}			
			
		}
		return $totalPartner;
	}
	/**
	 * 根据权限数组获得可查询的合作商默认权限
	 * @param $data_groups
	 * @return array
	 *
	**/
	public function listParterDefaultPermission($group_id)
	{
		$group_id = intval($group_id);
		$default_permission_array = array();

		$sql = "select * from $this->table where group_id = $group_id and PartnerId = 0 order by AppId,PartnerId";
		$default = $this->db->getAll($sql,$group_id);
		if(isset($default))
		{
			foreach($default as $key => $value)
			{
				//默认游戏权限
				if($value['AppId']==0)
				{
					$default_permission_array['app'][$value['AreaId']][$value['partner_type']]=1;
				}
				//默认合作商权限
				else
				{
					$default_permission_array['partner'][$value['AppId']][$value['AreaId']][$value['partner_type']]['permission']=1;
				}
			}
			
		}
		return $default_permission_array;
	}
	/**
	 * 根据权限数组拼接出数据库查询用的语句
	 * @param $AppId
	 * @param $parnter_id
	 * @param $数据库前缀用以拼在条件中
	 * @return array
	 *
	**/
	public function getWherePermittedPartner($data_groups,$AppId,$PartnerId,$app_type,$partner_type,$AreaList,$AreaId,$is_abroad)
	{
		$oPartnerApp = new Config_Partner_App();
		$oApp = new Config_App();
		$oArea = new Config_Area();

		$data_groups = trim($data_groups);
		$AppId = intval($AppId);
		$PartnerId = intval($PartnerId);
		$AreaId = intval($AreaId);
		//根据所在区域筛选
		if($AreaId)
		{
			$AreaList = $oArea->getArea($AreaId,$AreaList);
		}

		$is_abroad = intval($is_abroad);

		$partner_type = intval($partner_type);
		$app_list = $this->getApp($data_groups,'AppId');
		//根据产品类型筛选
		$app_list = $oApp->getApp($app_type,$app_list);

		$permission_array = array();
		if(count($app_list))
		{
			foreach($app_list as $app => $app_value)
			{
				if(($AppId == $app)||($AppId==0))
				{
					$partner_list = $this->getPartner($data_groups,$app,'PartnerId,name,AreaId');
					//根据合作方式筛选
					$partner_list = $oPartnerApp->getPermittedPartnerByPartnerType($partner_type,$partner_list);
					//根据所在地区筛选
					$partner_list = $oPartnerApp->getPermittedPartnerByPartnerArea($AreaList,$partner_list);
					//将允许的合作商列表分组
					if(count($partner_list))
					{
						$p_list = array();
						foreach($partner_list as $partner => $partner_value)
						{
							if(($PartnerId == 0)||($PartnerId == $partner))
							{
								$p_list[] = $partner;
							}
						}
						$permission_array[$app]['partner'] = $p_list;
					}
				}
			}
		}
		if(isset($permission_array))
		{
			//根据游戏拼接成数组
			foreach($permission_array as $app => $app_data)
			{
					$app_arr[$app] = "(AppId = $app and PartnerId in (".implode(",",$app_data['partner']).")) ";
			}
			//组合数组
			if(isset($app_arr))
			{
				$wherePermittedPartner = "(".implode(" or ",$app_arr).")";
			}
			//无权限需指定
			else
			{
				$wherePermittedPartner = "(0)";
			}
		}
		//无权限需指定
		else
		{
			$wherePermittedPartner = "(0)";
		}
		return $wherePermittedPartner;
	}
	/**
	 * 更新权限
	 * 
	 *
	**/
	public function modifyParterPermission($group_id,$total_default_permission,$default_permission,$PartnerIds)
	{
		echo "<pre>";
		$oPartnerApp = new Config_Partner_App();
		$oApp = new Config_App();
		$permission_arr = array('default'=>array(),'app_default'=>array(),'list'=>array());
		//留下全局默认权限数组
		if(is_array($total_default_permission))
		{
			foreach($total_default_permission as $key => $value)
			{
				$t_d = explode("_",$value);
				if(count($t_d)==2)
				{
					$permission_arr['default'][$t_d[0]][$t_d[1]]=1;
				}		
			}
		}
		//留下各游戏默认权限数组
		if(is_array($default_permission))
		{
			foreach($default_permission as $key => $value)
			{
				$t_a = explode("_",$value);
				if(count($t_a)==3)
				{
					//如果全局已经默认，则无需再记录
					if(!isset($permission_arr['default'][$t_a[1]][$t_a[2]]))
					{
						$permission_arr['app_default'][$t_a[0]][$t_a[1]][$t_a[2]]=1;
					}
				}	
			}
		}
		$partner_info = array();
		//留下零散合作商权限数组
		if(isset($PartnerIds))
		{			foreach($PartnerIds as $key => $value)
			{
				$t_p = explode("_",$value);
				if(count($t_p)==2)
				{
					$arr = array($t_p[1],$t_p[0]);
					//获取合作商基本信息
					$p = $oPartnerApp->getRow($arr,'AreaId');
					//判断合作商类型
					if($t_p[1]==1)
					{
						$partner_type=1;	
					}
					else
					{
						$partner_type=2;	
					}
					$partner_info[$t_p[0]][$t_p[1]] = array('partner_type'=>$partner_type,'AreaId'=>$p['AreaId']);
					//如果全局已经默认，则无需再记录
					if(!isset($permission_arr['default'][$p['AreaId']][$partner_type]))
					{
						//如果指定游戏已经默认，则无需再记录
						if(!isset($permission_arr['app_default'][$t_p[0]][$p['AreaId']][$partner_type]))
						{
							$permission_arr['list'][$t_p[0]][$t_p[1]]=1;
						}
					
					}
				}
			}
		}
		//获取原有的权限
		$sql = "select PartnerId,AppId,partner_type,AreaId from $this->table where group_id = ?";
		$permission_now = $this->db->getAll($sql,$group_id);
		if($permission_now)
		{
			foreach($permission_now as $key => $value)
			{
				if($value['AppId']==0)
				{
					//如果原来有现在没有则标记删除
					if(!isset($permission_arr['default'][$value['AreaId']][$value['partner_type']]))
					{
						$permission_arr['default'][$value['AreaId']][$value['partner_type']]=0;
						//删除权限明细中的相关
						foreach($permission_arr['list'] as $AppId => $partner_data)
						{
							foreach($partner_data as $PartnerId => $data)
							{							
								$arr = array($PartnerId,$AppId);
								$p = $partner_info[$AppId][$PartnerId];
								if(($value['AreaId']==$p['AreaId'])&&($value['partner_type']==$p['partner_type']))
								{
									unset($permission_arr['list'][$AppId][$PartnerId]);
								}
							}
						}
					}
					//如果原来有现在也有则不做变化
					else
					{
						unset($permission_arr['default'][$value['AreaId']][$value['partner_type']]);	
					}	
				}
				else
				{
					if($value['PartnerId']==0)
					{
						//如果原来有现在没有则标记删除
						if(!isset($permission_arr['app_default'][$value['AppId']][$value['AreaId']][$value['partner_type']]))
						{
							$permission_arr['app_default'][$value['AppId']][$value['AreaId']][$value['partner_type']]=0;
							//删除权限明细中的相关
							if(isset($permission_arr['list'][$value['AppId']]))
							{
								foreach($permission_arr['list'][$value['AppId']] as $PartnerId => $data)
								{							
									$arr = array($PartnerId,$value['AppId']);
									$p = $partner_info[$value['AppId']][$PartnerId];
									if(($value['AreaId']==$p['AreaId'])&&($value['partner_type']==$p['partner_type']))
									{
										if(isset($permission_arr['list'][$value['AppId']][$PartnerId]))
										{
											unset($permission_arr['list'][$value['AppId']][$PartnerId]);	
										}
									}
								}
							}
							
						}
						//如果原来有现在也有则不做变化
						else
						{
							unset($permission_arr['app_default'][$value['AppId']][$value['AreaId']][$value['partner_type']]);	
						}	
					}
					else
					{
						//如果原来有现在没有则标记删除
						if(!isset($permission_arr['list'][$value['AppId']][$value['PartnerId']]))
						{
							$permission_arr['list'][$value['AppId']][$value['PartnerId']]=0;
						}
						//如果原来有现在也有则不做变化
						else
						{
							unset($permission_arr['list'][$value['AppId']][$value['PartnerId']]);
						}								
					}						
				}	
			}	
		}
		//写入数据库
		if(isset($permission_arr['default']))
		{
			foreach($permission_arr['default'] as $AreaId => $partner)
			{
				if(is_array($partner))
				{
					foreach($partner as $partner_type => $to_do)
					{
						if($to_do==0)
						{
							$this->DelDefaultPermission($group_id,$AreaId,$partner_type);	
						}
						else
						{
							$this->InsDefaultPermission($group_id,$AreaId,$partner_type);	
						}
					}	
				}
			}
		}
		//写入数据库
		if(isset($permission_arr['app_default']))
		{
			foreach($permission_arr['app_default'] as $AppId => $app)
			{
				foreach($app as $AreaId => $partner)
				{
					if(is_array($partner))
					{
						foreach($partner as $partner_type => $to_do)
						{
							if($to_do==0)
							{
								$this->DelAppDefaultPermission($group_id,$AppId,$AreaId,$partner_type);	
							}
							else
							{
								$this->InsAppDefaultPermission($group_id,$AppId,$AreaId,$partner_type);
							}
						}	
					}
				}
			}
		}
		//写入数据库
		if(isset($permission_arr['list']))
		{
			foreach($permission_arr['list'] as $AppId => $app)
			{
				foreach($app as $PartnerId => $to_do)
				{
					if($to_do==0)
					{
						$this->DelPermission($group_id,$AppId,$PartnerId);	
					}
					else
					{
						$this->InsPermission($group_id,$AppId,$PartnerId,$partner_info[$AppId][$PartnerId]['AreaId'],$partner_info[$AppId][$PartnerId]['partner_type']);
					}
				}
			}
		}		
	}
	/**
	 * 加入全局默认权限
	 * @params $group_id	权限组
	 * @params $AreaId 所在地区
	 * @params $$partner_type  合作商类型
	 */
	function InsDefaultPermission($group_id,$AreaId,$partner_type)
	{
			$InsertParmas = array('AppId'=>0,'PartnerId'=>0,'AreaId'=>$AreaId,'partner_type'=>$partner_type,'group_id'=>$group_id,'permission'=>1);
			echo "add:group_id:$group_id    AreaId:$AreaId	 partner_type:$partner_type";
			echo "<br>";
			return $this->db->insert($this->table,  $InsertParmas);
	}
	/**
	 * 删除全局默认权限
	 * @params $group_id	权限组
	 * @params $AreaId 所在地区
	 * @params $$partner_type  合作商类型
	 */
	function DelDefaultPermission($group_id,$AreaId,$partner_type)
	{
			$deleteParmas = array($AreaId,$partner_type,$group_id);
			echo "del:group_id:$group_id    AreaId:$AreaId	 partner_type:$partner_type";
			echo "<br>";
			return $this->db->delete($this->table, 'AppId = 0 and PartnerId = 0 and `AreaId` = ? and `partner_type` = ? and `group_id` = ?', $deleteParmas);
	}
	/**
	 * 加入游戏默认权限
	 * @params $group_id	权限组
	 * @params $AppId	游戏
	 * @params $AreaId 所在地区
	 * @params $$partner_type  合作商类型
	 */
	function InsAppDefaultPermission($group_id,$AppId,$AreaId,$partner_type)
	{
			$InsertParmas = array('AppId'=>$AppId,'PartnerId'=>0,'AreaId'=>$AreaId,'partner_type'=>$partner_type,'group_id'=>$group_id,'permission'=>1);
			echo "add:group_id:$group_id    AreaId:$AreaId	 partner_type:$partner_type";
			echo "<br>";
			return $this->db->insert($this->table,  $InsertParmas);
	}
	/**
	 * 删除游戏默认权限
	 * @params $group_id	权限组
	 * @params $AppId	游戏
	 * @params $AreaId 所在地区
	 * @params $$partner_type  合作商类型
	 */	
		function DelAppDefaultPermission($group_id,$AppId,$AreaId,$partner_type)
		{
			$deleteParmas = array($AppId,$AreaId,$partner_type,$group_id);
			echo "del:group_id:$group_id   AppId:$AppId   AreaId:$AreaId	 partner_type:$partner_type";
			echo "<br>";
			return $this->db->delete($this->table, '`AppId` = ? and PartnerId = 0 and `AreaId` = ? and `partner_type` = ? and `group_id` = ?', $deleteParmas);
		}
	/**
	 * 加入基本权限
	 * @params $group_id	权限组
	 * @params $AppId	游戏
	 * @params $PartnerId	合作商
	 * @params $AreaId 所在地区
	 * @params $$partner_type  合作商类型
	 */
	function InsPermission($group_id,$AppId,$PartnerId,$AreaId,$partner_type)
	{
			$InsertParmas = array('AppId'=>$AppId,'PartnerId'=>$PartnerId,'group_id'=>$group_id,'AreaId'=>$AreaId,'partner_type'=>$partner_type,'permission'=>1);
			echo "add:group_id:$group_id   AppId:$AppId   PartnerId:$PartnerId";
			echo "<br>";
			return $this->db->insert($this->table, $InsertParmas);
	}
	/**
	 * 删除基本权限
	 * @params $group_id	权限组
	 * @params $AppId	游戏
	 * @params $PartnerId	合作商
	 * @params $AreaId 所在地区
	 * @params $$partner_type  合作商类型
	 */
	function DelPermission($group_id,$AppId,$PartnerId)
	{
			$deleteParmas = array($AppId,$PartnerId,$group_id);
			echo "del:group_id:$group_id   AppId:$AppId   PartnerId:$PartnerId";
			echo "<br>";
			return $this->db->delete($this->table, '`AppId` = ? and `PartnerId` = ? and `group_id` = ?', $deleteParmas);
	}
	/**
	 * 更新数据权限组的日期限制
	 * @params group_id	权限组
	 * @params start_time 起始时间
	 * @params end_time 结束时间
	 */
	function ModifyDatePermission($group_id,$start_time,$end_time)
	{
		$group_id = abs(intval($group_id));
		$start = date("Y-m-d",min(strtotime($start_time),strtotime($end_time)));
		$end = date("Y-m-d",max(strtotime($start_time),strtotime($end_time)));
		$updateStruct = array('start_time'=>$start,'end_time'=>$end);
		return $this->db->update($this->table_date, $updateStruct, '`group_id` = ?', $group_id);
	}
	/**
	 * 获取数据权限组的日期限制
	 * @params group_id	权限组
	 */
	public function getDatePermission($group_id)
	{
		$group_id = trim($group_id);
		$sql = "SELECT min(start_time) as start_time,max(end_time) as end_time FROM {$this->table_date} WHERE `group_id` in ($group_id)";
		return $this->db->getRow($sql);
	}
	/**
	 * 删除数据权限组的日期限制
	 * @params group_id	权限组
	 */
	public function delDatePermission($group_id)
	{
		$group_id = abs(intval($group_id));
		$sql = "SELECT min(start_time) as start_time,max(end_time) as end_time FROM {$this->table_date} WHERE `group_id` in ($group_id)";
		return $this->db->delete($this->table_date, '`group_id` = ?', $group_id);
	}
	
	/*
	 *@author selena  2013/3/14
	 */
	/**
	 * 获得权限总列表
	 * @return array
	 *
	**/
	public function AllParterPermissionList($fields,$group_id)
	{
		$oPartnerApp = new Config_Partner_App();
		$oApp = new Config_App();
		$oArea = new Config_Area();
		//所有游戏信息，游戏id为键值
		$AppList = $oApp->getAll("AppId,name");
		
		//所有区域 二维数组 区域id为键值
		$TotalArea = $oArea->getAll('AreaId,name');
		
		//每个游戏所在的区域
		$AreaList = $oPartnerApp->getAreaList();
		
		//构造新数组，每个区域新添官服和专区
		if(isset($TotalArea))
		{
			foreach($TotalArea as $AreaId => $value)
			{
				$TotalArea[$AreaId]['partner_type'] = array('1'=>array('name'=>'官服','permission'=>0),'2'=>array('name'=>'专区','permission'=>0));
			}
		}
		
		//获取所有运营商 二维数组 键值默认排序		
		$PartnerAppList = $oPartnerApp->getAll($fields);
		
		//构建数组 五维数组 键值依次是 游戏应用id(Appid) 区域id(AreaId) 合作商分类(partner_type) 服务商id(PartnerId) 服务商一维数组		
		//目的：把构建好的服务商数组赋给下面大循环
		$PartnerAppAreaList=array();
		foreach($PartnerAppList as $k=>$v)
		{
			$v['permission']=0;
			if($v["PartnerId"]==1)
			{				
				$PartnerAppAreaList[$v["AppId"]][$v["AreaId"]][1][]=$v;
			}else
			{
				$PartnerAppAreaList[$v["AppId"]][$v["AreaId"]][2][]=$v;
			}
			//$PartnerAppAreaList[$v["AppId"]][$v["AreaId"]][$v["PartnerId"]][]=$v;						
		}						
		
		
		$totalPartner = array();
		$totalPartner['total'] = $TotalArea;
		
		$partner = array();
				
		if(is_array($PartnerAppList))
		{
			foreach($PartnerAppList as $key => $value)
			{
				
				//if(!isset($totalPartner['list'][$value['AppId']]))
				//{
					$name = $AppList[$value['AppId']]["name"];
					$AppAreaList =array();					
					$AppAreaList = $TotalArea;//为了不破坏$TotalArea数组
					foreach($AppAreaList as $AreaId=>$Area)
					{
						if(in_array($AreaId,$AreaList[$value["AppId"]]))//判断这个区域是否在游戏区域内
						{
							foreach($Area["partner_type"] as $k=>$v)
							{
								//判断游戏在某个区域内是否有运营商以及这个运营商是官服还是专区
								//$ParnterType = $oPartnerApp->getPartnerId($value['AppId'],$AreaId);								
								if($value["PartnerId"]==1)//1表示官服
								{
									$partnerList = $PartnerAppAreaList[$value["AppId"]][$AreaId][1];
									$AppAreaList[$AreaId]["partner_type"][1]["partner"] = $partnerList;	
								}else
								{
									$partnerList = $PartnerAppAreaList[$value["AppId"]][$AreaId][2];
									$AppAreaList[$AreaId]["partner_type"][2]["partner"]= $partnerList;									
								}								
															
							}						
						}
						else
						{
							unset($AppAreaList[$AreaId]);
						}						
						
					}				
					$totalPartner['list'][$value['AppId']] = array('name'=>$name,'default'=>$AppAreaList);

					
					
				//}
				/*if(!isset($totalPartner['list'][$value['AppId']]['partner'][$value['PartnerId']]))
				{
					$totalPartner['list'][$value['AppId']]['partner'][$value['PartnerId']] = $value;
				}*/
			}
		}

		//目的是删除没有运营商的官服和专区  和判断用户是否有权限
		foreach($totalPartner['list'] as $AppId => &$area_data)
		{
			foreach($area_data['default'] as $AreaId => &$area_data)
			{
				foreach($area_data['partner_type'] as $partner_type => &$partner_type_data)
				{
					if($partner_type_data["partner"]==null)
					{
						unset($totalPartner['list'][$AppId]['default'][$AreaId]['partner_type'][$partner_type]);
					}
					else
					{
						foreach($partner_type_data['partner'] as $key => &$partner_data)
						{
							//($AppId,$AreaId,$PartnerId,$partner_type,$group_id)_id"
							$permission = $this->getPermission($AppId,$AreaId,$partner_data['PartnerId'],$partner_type,$group_id);													
							$partner_data['permission'] = $permission['permission'];
						}
					}					
				}
			}
			
		}
		return $totalPartner;
	}
	/**
	 * 根据权限数组获得可查询的合作商默认权限
	 * @param $data_groups
	 * @return array
	 * @author selena 2013/3/14 
	*/
	public function getPermission($AppId,$AreaId,$PartnerId,$partner_type,$group_id)
	{
		//$group_id = intval($group_id);
		$sql = "select permission from $this->table2 where AppId = $AppId and AreaId = $AreaId and PartnerId = $PartnerId and partner_type = $partner_type and group_id = $group_id ";
		//echo $sql."<br/>";
		$default = $this->db->getRow($sql);
		return $default;
	}
	/**
	 * 删除游戏默认权限
	 * @author selena 2013/3/14
	 */	
	function DelPermissionByGroup($group_id)
	{
		$deleteParmas = array($group_id);
		//echo "del:group_id:$group_id   AppId:$AppId   AreaId:$AreaId	 partner_type:$partner_type";
		//echo "<br>";
		return $this->db->delete($this->table2, '`group_id` = ?', $deleteParmas);
	}
		/**
	 * 加入全局默认权限
	 * @params $group_id	权限组
	 * @params $AreaId 所在地区
	 * @params $$partner_type  合作商类型
	 * @author selena 2013/3/14
	 */
	public function InsArrPermission($AppId,$PartnerId,$AreaId,$partner_type,$group_id)
	{
		$InsertParmas = array('AppId'=>$AppId,'PartnerId'=>$PartnerId,'AreaId'=>$AreaId,'partner_type'=>$partner_type,'group_id'=>$group_id,'permission'=>1);
		return $this->db->insert($this->table2,  $InsertParmas);
	}
	

}

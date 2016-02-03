<?php
/**
 * 机柜管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: MachineController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_MachineController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/machine';
	/**
	 * Machine对象
	 * @var object
	 */
	protected $oCage;
	protected $oDepot;
	protected $oMachine;
	protected $oServer;
	protected $oApp;
	protected $oPartner;
	protected $Status = array("1"=>"已贴","2"=>"未贴");
	protected $IntellectProperty = array("1"=>"自购","2"=>"租用");
	protected $FlagList = array("2"=>"交换机","3"=>"防火墙","4"=>"路由器");
	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oMachine = new Config_Machine();
		$this->oDepot = new Config_Depot();
		$this->oCage = new Config_Cage();
		
		
	  $this->oPermission = new Config_Permission();//新添
	  $this->oArea = new Config_Area();//新添
		$this->oApp = new Config_App();
		$this->oPartner = new Config_Partner();
		$this->oPartnerApp = new Config_Partner_App();
		$this->oServer = new Config_Server();
		
		$this->DepotList = $this->oDepot->getAll();
		$this->CageList = $this->oCage->getAll();
		$this->AppList = $this->oApp->getAll();
		$this->PartnerList = $this->oPartner->getAll();
		$this->ServerList = $this->oServer->getAll();
		
		//新添 
		//获取用户可以查看的游戏列表
		$this->permitted_app = $this->oPermission->getApp($this->manager->data_groups,'AppId,name');
		//预处理地区信息
		$this->AreaList = $this->oArea->getAll();
		
	}
	//机柜配置列表页面
	public function indexAction()
	{
		$Flag = $this->request->Flag? $this->request->Flag:1;//判断设备是什么类型的 默认是服务器的 8表示是网络设备旗下有标识234 5表示其他设备
		if($Flag == 1)
		{
			//检查权限
		  $this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		}elseif($Flag == 5)
		{
			$sign = "?ctl=config/machine&ac=index&Flag=5";
			$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);	
		}else
		{
			$sign = "?ctl=config/machine&ac=index&Flag=8";
			$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);	
		}
		
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		//只显示所选择的列 
		$managerInfo = $this->manager->getRow($this->manager->id,"machine_show_list");
		$ShowList = json_decode($managerInfo['machine_show_list'],true);
		$Show = $ShowList["Show"];
		
		$bDepotList = $this->DepotList;
		$bCageList = $this->CageList;
		$FlagList = $this->FlagList;
		$StatusList = $this->Status;
		$IntellectPropertyList = $this->IntellectProperty;
		
		//是否导出当前页面表格
		$page = intval(max($this->request->page,1));
		$export = $this->request->export?intval($this->request->export):0;
		$pagesize = $export?0:10;
			
		
		$MachineCode = trim($this->request->MachineCode);
		$EstateCode = trim($this->request->EstateCode);
		$MachineName = trim($this->request->MachineName);
		$Version = trim($this->request->Version);
		$Owner = trim($this->request->Owner);		
		
		$DepotId = abs(intval($this->request->DepotId));
		$CageId = abs(intval($this->request->CageId));
		
		//游戏 合作商 服务器
		$AppId = intval($this->request->AppId);
		$PartnerId = intval($this->request->PartnerId);
		$ServerId = intval($this->request->ServerId);
		$AreaId = intval($this->request->AreaId)?intval($this->request->AreaId):0;
		$app_type = intval($this->request->app_type);
		$partner_type = intval($this->request->partner_type);
		$is_abroad = intval($this->request->is_abroad)?intval($this->request->is_abroad):0;
		
		$LocalIP = trim($this->request->LocalIP);
		$WebIP = trim($this->request->WebIP);
		$ProjectId = abs($this->request->ProjectId);
		$User = trim($this->request->User);
		//排序
		$field = trim($this->request->field);
		$order = trim($this->request->order);
		
		$param=array();//页面参数
		//$param["export"] = $export;
		$param["Flag"] = $Flag;

		if($MachineCode!="")
		{
			$param['MachineCode']=$MachineCode;
		}
		if($EstateCode!="")
		{
			$param['EstateCode']=$EstateCode;
		}
		if($MachineName!="")
		{
			$param['MachineName']=$MachineName;
		}
		if($Version!="")
		{
			$param['Version']=$Version;
		}
		if($DepotId)//机房机柜
		{
			$param['DepotId'] = $DepotId;
			$CageIdList = $bCageList[$DepotId];
			if(!$CageId)
			{
				$param['CageId'] = $CageId;
				$CageIdList = Base_Common::getArrList($bCageList[$DepotId]);
			}
			else
			{
				$CageIdList = $CageId;	
			}
		}
		else // 防止出现删除了机柜 但机器列表还是出现了这个CageId
		{
			foreach($bDepotList as $Depot => $DepotInfo)
			{
				$a = Base_Common::getArrList($bCageList[$Depot]);				
				if($a != 0)
				{
					$CageidTextTmp[] = $a;
					
				}
			}
			$CageIdList = implode(",",$CageidTextTmp);	
		}

		if($AppId)//服务器平台
		{
			$param['AppId']=$AppId;
			if($PartnerId)
			{
				$param['PartnerId']=$PartnerId;
				$ServerIdList = "";
				if($ServerId)
				{
					$param['ServerId']=$ServerId;
					$ServerIdList = $ServerId;
				}
				else
				{									
					$ServerList = $this->oServer->getAppPartnerRow($AppId,$PartnerId);//存在有运营商但没有服务器的情况
					if($ServerList)
					{
						foreach($ServerList as $key => $val)
						{
							$ServerIdList.=	$val['ServerId']." , ";				
						}
						$ServerIdList = substr($ServerIdList,0,strlen($ServerIdList)-2);						
					}else
					{				
						$ServerIdList = "";		
					}
										
				}	
			}else//选择了游戏App，但没选择平台
			{
				$ServerList = $this->oServer->getByApp($AppId);
				foreach($ServerList as $key => $val)
				{
					$ServerIdList.=	$val['ServerId']." , ";				
				}
				$ServerIdList = substr($ServerIdList,0,strlen($ServerIdList)-2);
			}	
						
		}else//没有选择服务器  防止出现删除了服务器ServerId但机器列表还是出现了这个ServerId
		{
			if($Flag==1)//网络设备没有服务区 即 ServerId
			{
				$ServerList = $this->oServer->getAll("ServerId");
				foreach($ServerList as $key => $val)
				{
					$ServerIdList.=	$val['ServerId']." , ";				
				}
				$ServerIdList = substr($ServerIdList,0,strlen($ServerIdList)-2);				
			}
						
		}
		
		if($LocalIP!="")
		{
			$param['LocalIP']=$LocalIP;
			$LocalIP = Base_Common::ip2long($LocalIP);
			
			if($LocalIP==0) //用户想查询0.0.0.0 的公网IP,赋值zero，是为了在后面不被过滤掉
			{
				$LocalIP = 'zero';
			}
		}		
		if($WebIP!="")
		{
			$param['WebIP']=$WebIP;
			$WebIP = Base_Common::ip2long($WebIP);
			if($WebIP==0) //用户想查询0.0.0.0 的公网IP,赋值zero，是为了在后面不被过滤掉
			{
				$WebIP = 'zero';
			}
			
		}	
		if($User!="")
		{
			$param['User']=$User;
		}
		if($Flag!="")
		{
			$param['Flag']=$Flag;
		}
		if($field!="")
		{
			$param['field']=$field;
		}
		if($order!="")
		{
			$param['order']=$order;
		}
		if($Owner!="")//针对于其他设备 ，所属机器序列号
		{
			$param['Owner']=$Owner;
		}
		$DepotList = $this->DepotList;//机房列表
		$CageList = $this->oCage->getAll($param['DepotId']); //机柜列表
				
	  /*$AppList = $this->AppList;//游戏列表
		$PartnerList = $this->oPartnerApp->getAppAll($param['AppId'],"PartnerId,name");//平台列表
		$ServerList = $this->oServer->getByAppPartner($param['AppId'],$param['PartnerId'],"ServerId,name");//服务器列表		*/
		//初始化游戏列表
		$permitted_app = $this->permitted_app;
		//初始化合作商列表
		$permitted_partner = array();
		//初始化服务器列表
		$permitted_server= array();
		
		$AreaList = $this->AreaList;
		//获取当前地区列表
		$AreaList = $this->oArea->getAbroad($is_abroad,$AreaList);
		//生成允许的地区id数组
		if($app_type>0)
		{
			//筛选是否平台产品
			$permitted_app = $this->oApp->getApp($app_type,$permitted_app);
		}
		if($AppId>0)
		{
		 	//获取可查看的权限总表
			$permitted_partner = $this->oPermission->getPartner($this->manager->data_groups,$AppId,'PartnerId,name,AreaId');
			//根据合作方式筛选
			$permitted_partner = $this->oPartnerApp->getPermittedPartnerByPartnerType($partner_type,$permitted_partner);
			//根据所在地区筛选
			$permitted_partner = $this->oPartnerApp->getPermittedPartnerByPartnerArea($AreaList,$permitted_partner);

			if($PartnerId>0)
			{
				//获取服务器列表
				$permitted_server = $this->oPermission->getServer($this->manager->data_groups,$AppId,$PartnerId,'ServerId,name');					
			}
		}
		
			
		$MachineList = $this->oMachine->getMachineParams($MachineCode,$EstateCode,$MachineName,$Version,$CageIdList,$AppId,$ServerIdList,$LocalIP,$WebIP,$User,$field,$order,$Flag,$Owner,($page-1)*$pagesize,$pagesize);
		$MachineArr = $MachineList['MachineDetail'];
		$MachineArr = $this->recombinationArr($MachineArr);

		//数据总数
		$count = $MachineList['MachineCount']?$MachineList['MachineCount']:0;
		
		$execlParam = $param+array("export"=>1);
		$export_var = "<a href =".(Base_Common::getUrl('','config/machine','index',$execlParam))."><导出表格></a>";
		//分页
		$pageParam = $param+array("export"=>0);
		$page_url = Base_Common::getUrl('','config/machine','index',$pageParam)."&page=~page~";
		$page_content =  base_common::multi($MachineList['MachineCount'], $page_url, $page, $pagesize, 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');		

			
		if($export==1)
		{			
			$oExcel = new Third_Excel();
			if($Flag==1)
			{
				$FileName='服务器配置表格';
				//标题栏
				$title = array("序列号","资产编号","固定资产","资产型号","所属机房","所属机柜","位置","机器高度","内网IP","公网IP","实物状态",
				"实物标签","所属游戏","所属平台","所属服务器","额定电流","CPU","CPU数量","内存","内存数量","硬盘","硬盘数量","硬盘模式","网卡","网卡数量","知识产权","使用人","备注");
			
			}elseif($Flag==5)
			{
				$FileName='其他设备配置表格';
				//标题栏
				$title = array("序列号","资产编号","固定资产","资产型号","所属机房","所属机柜","位置","机器高度","内网IP","公网IP","实物状态",
				"所属机器序列号","金额","使用人","备注");
				
			}elseif(in_array($Flag,array(2,3,4,8))){
				$FileName='网络设备配置表格';
				//标题栏
				$title = array("序列号","资产编号","固定资产","资产型号","所属机房","所属机柜","位置","机器高度","内网IP","公网IP","实物状态",
				"金额","使用人","备注");
				
			}
			$oExcel->download($FileName)->addSheet('配置表');
			$oExcel->addRows(array($title));
			
		 	foreach($MachineArr as $MachineId => $MachineInfo)
			{
				//生成单行数据

				$t['MachineCode'] = $MachineInfo['MachineCode'];
				$t['EstateCode'] = $MachineInfo['EstateCode'];
				$t['MachineName'] = $MachineInfo['MachineName'];
				$t['Version'] = $MachineInfo['Version'];
				$t['DepotName'] = $MachineInfo['DepotName'];
				$t['CageCode'] = $MachineInfo['CageCode'];
				
				$t['Position'] = $MachineInfo['Position'];
				$t['Size'] = $MachineInfo['Size']."U";
								
				$t['LocalIP'] = $MachineInfo['LocalIP'];
				$t['WebIP'] = $MachineInfo['WebIP'];
				
				$t['MachineStatus'] = $MachineInfo['MachineStatus'];
				
				
				if($Flag==1)
				{
					$t['Status']= $MachineInfo['Comment']['Status'];
					$t['AppName'] = $MachineInfo['AppName'];
					$t['PartnerName'] = $MachineInfo['PartnerName'];
					$t['ServerName'] = $MachineInfo['ServerName'];
					$t['Current'] = $MachineInfo['Current']."A";
					
					$t['Cpu'] = $MachineInfo['Comment']['Cpu'];
					$t['CpuCount'] = $MachineInfo['Comment']['CpuCount'];
					$t['Memory'] = $MachineInfo['Comment']['Memory'];				
					$t['MemoryCount'] = $MachineInfo['Comment']['MemoryCount'];				
					$t['Hd'] = $MachineInfo['Comment']['Hd'];
					$t['HdCount'] = $MachineInfo['Comment']['HdCount'];
					$t['HdMode'] = $MachineInfo['Comment']['HdMode'];
					$t['Netcard'] = $MachineInfo['Comment']['Netcard'];
					$t['NetcardCount'] = $MachineInfo['Comment']['NetcardCount'];						
					$t['IntellectProperty'] =$MachineInfo['IntellectProperty'];
				}
				if($Flag==5)
				{
					$t['OwnerCode'] = $MachineInfo['OwnerCode'];
				}
				$t['Money'] = $MachineInfo['Comment']['Money']."元";
		  	$t['User'] = $MachineInfo['User'];
		  	$t['Remark'] = $MachineInfo['Comment']['Remark'];
				$oExcel->addRows(array($t));	
				unset($t);					
			}

			//结束excel
			$oExcel->closeSheet()->close();	
						
		}
		if($Flag == 1)
		{
			include $this->tpl('Config_Machine_list');
		}elseif($Flag == 5)
		{
			include $this->tpl('Config_Machine_otherList');			
		}else
		{
			include $this->tpl('Config_Machine_networkList');			
		}
		
	}
	//数组重组
	public function recombinationArr($MachineArr)
	{
		//$bDepotList = $this->DepotList;
		//$bCageList = $this->CageList;
		
		$AppList = $this->AppList;
		$PartnerList = $this->PartnerList;
		$ServerList = $this->ServerList;
		
		$StatusList = $this->Status;
		$IntellectPropertyList = $this->IntellectProperty;
		$FlagList = $this->FlagList;
		$MachineCodeList = array();
		foreach($MachineArr as $MachineId => &$MachineInfo)
		{
			if($MachineInfo["CageId"])
			{
				if(!isset($CageInfoList["CageId"]) && !isset($DepotInfoList["CageId"]))
				{
					$CageInfo = $this->oCage->getRow($MachineInfo["CageId"]);
					$CageInfoList[$MachineInfo["CageId"]] = $CageInfo;
					$DepotInfo = $this->oDepot->getRow($CageInfo["DepotId"]);
					$DepotInfoList[$MachineInfo["CageId"]] = $DepotInfo;					
				}
			}
			$ServerIdArr = explode(",",$MachineInfo["ServerId"]);
			if(count($ServerIdArr)>1)
			{
				$MachineInfo['ServerName'] = $MachineInfo["ServerId"];
				$MachineInfo['AppName'] = "其它";
			}else
			{
				$MachineInfo['ServerName'] = $ServerList[$MachineInfo['ServerId']]['name'];
				$MachineInfo['AppName'] = $AppList[$ServerList[$MachineInfo['ServerId']]['AppId']]['name'];
				$MachineInfo['PartnerName'] = $PartnerList[$ServerList[$MachineInfo['ServerId']]['PartnerId']]['name'];			
			}
			
			
			$MachineInfo['CageCode'] = $CageInfoList[$MachineInfo['CageId']]['CageCode'];
			$MachineInfo['DepotName'] = $DepotInfoList[$MachineInfo['CageId']]['name'];
			$MachineInfo['CageX'] = $CageInfoList[$MachineInfo['CageId']]['X'];
						
			$MachineInfo['LocalIP'] = long2ip($MachineInfo['LocalIP']);		
			$MachineInfo['WebIP'] = long2ip($MachineInfo['WebIP']);
			$MachineInfo['Udate'] =  date("Y-m-d H:i:s",$MachineInfo['Udate']);
			$MachineInfo['Comment'] = json_decode($MachineInfo['Comment'],true);
					
		
			$MachineInfo['Comment']['Status'] = $StatusList[$MachineInfo['Comment']['Status']];
			$MachineInfo['IntellectProperty'] = $IntellectPropertyList[$MachineInfo['IntellectProperty']];

			if($MachineInfo['Flag']==1)
			{
				$MachineInfo['Flag'] = "服务器";
			}elseif($MachineInfo['Flag']==5)
			{
				$MachineInfo['Flag'] = "其他设备";		
				if($MachineInfo["Owner"])
				{
					if(!$MachineCodeList[$MachineInfo["Owner"]])
					{
						$MachineCodeList[$MachineInfo["Owner"]] = $this->oMachine->getOne($MachineInfo["Owner"],"MachineCode");
					}
					$MachineInfo['OwnerCode'] = $MachineCodeList[$MachineInfo["Owner"]];				
				}else
				{
					$MachineInfo['OwnerCode'] = "";
				}
			}else 
			{
				$MachineInfo['Flag'] = $FlagList[$MachineInfo['Flag']];				
			}
		}
		return $MachineArr;
	}
	//添加机柜填写配置页面
	public function addAction()
	{
		//检查权限	
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);	
		
		$DepotList = $this->DepotList;//机房列表
		
		//初始化游戏列表
		$permitted_app = $this->permitted_app;
		//初始化合作商列表
		$permitted_partner = array();
		//初始化服务器列表
		$permitted_server= array();
		
		$AreaList = $this->AreaList;
		//获取当前地区列表
		$AreaList = $this->oArea->getAbroad($is_abroad,$AreaList);
		//生成允许的地区id数组
		$app_type = 0;
		if($app_type>0)
		{
			//筛选是否平台产品
			$permitted_app = $this->oApp->getApp($app_type,$permitted_app);
		}
		
		//$AppList = $this->AppList;//游戏列表
		//$ProjectList = $this->ProjectList;//项目列表 
		$StatusList = $this->Status;
		$IntellectPropertyList = $this->IntellectProperty;
		include $this->tpl('Config_Machine_add');
	}
	
	//添加新机柜
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('MachineCode','Position','Current','Size','LocalIP','WebIP',"Comment","ServerId","CageId",
		"MachineName","Version","MachineStatus","IntellectProperty","EstateCode","User","Purpose","Flag","Owner");

		$bind['MachineCode'] = trim($bind['MachineCode']);
		$bind['Position'] = intval($bind['Position']);
		$bind['Current'] = sprintf("%.2f",$bind['Current']);
		$bind['Size'] = intval($bind['Size']);		
		$bind['LocalIP'] = Base_Common::ip2long($bind['LocalIP']);
		$bind['WebIP'] = Base_Common::ip2long($bind['WebIP']);		
		$bind['ServerId'] = trim($bind['ServerId']);
		$bind['CageId'] = abs($bind['CageId']);				
		$bind['MachineName'] = trim($bind['MachineName']);
		$bind['Version'] = trim($bind['Version']);
		$bind['MachineStatus'] = trim($bind['MachineStatus']);
		$bind["User"] = trim($bind['User']);
		$bind["Purpose"] = trim($bind['Purpose']);
		$bind["EstateCode"] = trim($bind['EstateCode']);//资产编号
		$bind["Flag"] = trim($bind['Flag']);
		$bind["Owner"] = trim($bind['Owner']);	//所属机器
		$bind['Udate'] = time();
		if($bind["Flag"] == '1')
		{
			$bind["Owner"] = "";
			$bind["IntellectProperty"] = abs($bind['IntellectProperty']);
			$Comment["Cpu"] = trim($bind['Comment']['Cpu']);
			$Comment["CpuCount"] = abs($bind['Comment']['CpuCount']);
			$Comment["Memory"] = trim($bind['Comment']['Memory']);
			$Comment["MemoryCount"] = abs($bind['Comment']['MemoryCount']);
			$Comment["Hd"] = trim($bind['Comment']['Hd']);
			$Comment["HdCount"] = abs($bind['Comment']['HdCount']);
			$Comment["HdMode"] = trim($bind['Comment']['HdMode']);
			$Comment["Netcard"] = trim($bind['Comment']['Netcard']);
			$Comment["NetcardCount"] = abs($bind['Comment']['NetcardCount']);
		}else
		{
			$bind["Owner"] = trim($bind['Owner']);
			$bind["IntellectProperty"] = '0';
			
		}
		$Comment["Money"] = sprintf("%.2f",$bind['Comment']['Money']);	
		$Comment["Status"] = abs($bind['Comment']['Status']);		
		$Comment["Remark"] = trim($bind['Comment']['Remark']);
		$bind['Comment'] = json_encode($Comment);

		$CageData =  $this->oCage->getRow($bind['CageId']);
		
    if(!empty($bind['MachineCode']))//验证机器序列号
		{
			$CheckMachine = $this->oMachine->getRowByKey('MachineCode',$bind['MachineCode']);
			if($CheckMachine)
			{
				$response = array('errno' => 3);
			}
				
		}
		if(count($response)>0)
		{
			exit(json_encode($response));
		}elseif(!empty($bind['EstateCode']))//验证资产编码 
		{
			$CheckEstate = $this->oMachine->getRowByKey('EstateCode',$bind['EstateCode']);
			if($CheckEstate)
			{
				$response = array('errno' => 16);
			}
		}
		
		if(count($response)>0)
		{
			exit(json_encode($response));
		}elseif($bind['CageId']==0)
		{
	  	$response = array('errno' => 4);	
	  }elseif($bind['Position']==0)
		{
			$response  = array('errno' => 8);	
		}elseif($bind['Size']==0)
		{
			$response  = array('errno' => 7);	
		}else
		{			
			$Position = $this->oMachine->getMachinePosition($bind['CageId'],$bind['Position']);			
			$long=0;
			if($Position)
			{
				$long = $Position-$bind['Position'];
				
			}else
			{
				$long = $CageData["Size"]-$bind['Position'];
				$long++;
			}
		
			if($bind['Size'] > $long)
			{	
				$response  = array('errno' => 9);	
			}else{
				$response = array();
			}
		}
		
		if($bind["Flag"] == '1')//这个是在服务器的时候判断的
		{			
			if(count($response)>0)
			{
				exit(json_encode($response));
			}elseif(!empty($bind['Current']))//额定电流可以为空
			{
		  	$currentCount = $CageData["Current"];
			  $currentSum = $this->oMachine->getCurrentByCageId($bind['CageId']);
			  if($bind['Current']>($currentCount-$currentSum))
			  {		  	
			  	$response  = array('errno' => 6);	
			  }else{
					$response = array();
				}
		  }	
		  
			if(count($response)>0)
			{
				exit(json_encode($response));
			}elseif(empty($bind['ServerId']))
			{
					//echo "ServerId".$bind['ServerId']."<br/>";
				$response  = array('errno' => 12);					
			}	
		}
		
		if($bind["Flag"]=='1'|| $bind["Flag"]=="2" ||$bind["Flag"]=='3' || $bind["Flag"]=="4" )
		{
			if(count($response)>0)
			{
				exit(json_encode($response));
			}elseif(!empty($bind['LocalIP']))
			{
				$CheckLocalIP = $this->oMachine->getRowByKey('LocalIP',$bind['LocalIP']);
				if($CheckLocalIP)
				{
					$response = array('errno' => 10);
				} 
			}
			
			if(count($response)>0)
			{
				exit(json_encode($response));
			}elseif(!empty($bind['WebIP']))
			{
				$CheckWebIP = $this->oMachine->getRowByKey('WebIP',$bind['WebIP']);
				if($CheckWebIP)
				{
					$response = array('errno' => 11);
				} 
			}
		}
		
		if(count($response)>0)
		{
			exit(json_encode($response));
		}elseif(empty($bind['MachineName']))
		{			
			$response  = array('errno' => 13);	
		}elseif(empty($bind['Version']))
		{			
			$response  = array('errno' => 14);	
		}elseif(empty($bind['MachineStatus']))
		{			
			$response  = array('errno' => 15);	
		}
		else
		{			
				$res = $this->oMachine->insert($bind);
			  if($res)
			  {
			  	$response = array('errno' => 0);
			  	//记录日志					 
					$log = "添加服务器信息\n\nServerIp:" . $this->request->getServer('SERVER_ADDR') . "\n\nMachineId:".$res."\n\n" . json_encode($bind);
					$this->oLogManager->push('log', $log);
			  }else
			  {
			  	$response = array('errno' => 17);			  	
			  }
		}
	
		/*echo "<pre>";
		print_r($response);*/
		exit(json_encode($response));
		
	}
	
	//修改机柜信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
				
		$MachineId = $this->request->MachineId;
		$MachineInfo = $this->oMachine->getRow($MachineId);	
		$MachineInfo['LocalIP'] = long2ip($MachineInfo['LocalIP']);
		$MachineInfo['WebIP'] = long2ip($MachineInfo['WebIP']);
		$MachineInfo['Comment'] = json_decode($MachineInfo['Comment'],true);

		$CageInfo = $this->oCage->getRow($MachineInfo['CageId']);		
		$MachineInfo['DepotId'] = $CageInfo['DepotId'];
		
	

		$AreaId = 0;
		$app_type = 0;
		$partner_type = 0;
		$is_abroad = 0;
		//初始化游戏列表
		$permitted_app = $this->permitted_app;
		//初始化合作商列表
		$permitted_partner = array();
		//初始化服务器列表
		$permitted_server= array();
		//获取当前地区列表
		$AreaList = $this->oArea->getAbroad($is_abroad,$AreaList);
		//生成允许的地区id数组
		if($app_type>0)
		{
			//筛选是否平台产品
			$permitted_app = $this->oApp->getApp($app_type,$permitted_app);
		}
		
		//判断ServerId是否是多个
		$ServerIdArr = explode(',',$MachineInfo['ServerId']);
		if(count($ServerIdArr)>1)//添加时选择的游戏是其他
		{
			$MachineInfo['AppId'] = "other";
		}else{
		 	//选择了一项具体的游戏
					
			$ServerInfo = $this->oServer->getRow($MachineInfo['ServerId']);		
			
			$MachineInfo['AppId'] = $ServerInfo['AppId'];
			$MachineInfo['PartnerId'] = $ServerInfo['PartnerId'];
			
			//游戏 合作商 服务器 新添
			$AppId = $MachineInfo['AppId'];
			$PartnerId = $MachineInfo['PartnerId'];
			$ServerId = $MachineInfo['ServerId'];
			
			//初始化游戏列表
			$permitted_app = $this->permitted_app;
			//初始化合作商列表
			$permitted_partner = array();
			//初始化服务器列表
			$permitted_server= array();
			
			$AreaList = $this->AreaList;
			//获取当前地区列表
			$AreaList = $this->oArea->getAbroad($is_abroad,$AreaList);
			//echo $AppId."<br/>";
			if($AppId>0)
			{
			 	//获取可查看的权限总表
				$permitted_partner = $this->oPermission->getPartner($this->manager->data_groups,$AppId,'PartnerId,name,AreaId');
				
				//根据合作方式筛选
				$permitted_partner = $this->oPartnerApp->getPermittedPartnerByPartnerType($partner_type,$permitted_partner);
				
				//根据所在地区筛选
				$permitted_partner = $this->oPartnerApp->getPermittedPartnerByPartnerArea($AreaList,$permitted_partner);
				
				if($PartnerId>0)
				{
					//获取服务器列表
					$permitted_server = $this->oPermission->getServer($this->manager->data_groups,$AppId,$PartnerId,'ServerId,name');					
				}
			}
		}
		
		$DepotList = $this->DepotList;//机房列表
		$CageList = $this->oCage->getAll($MachineInfo['DepotId']); //机柜列表
		$PositionList = $this->getPositionList($MachineInfo['CageId']);//机柜位置列表
		foreach($PositionList as $k=> $v)
		{
			if($v!=0 && $k!=$MachineInfo['Position'])
			{
				unset($PositionList[$k]);
			}					
		}
		
		$SizeLong = $this->getSizeList($MachineInfo['CageId'],$MachineInfo['Position']);

		//可用电流
		if($MachineInfo['Flag']==1)
		{
			$currentCount = $CageInfo["Current"];// 这台机柜的额定电流
			//这台机柜的实际用掉的电流必须减去本台机器的电流，因为在数据库中是计算这台机器的电流的			  
			$UseCurrent = $this->oMachine->getCurrentByCageId($MachineInfo['CageId'])-$MachineInfo['Current'];
			$MachineInfo['UseCurrent'] = $UseCurrent;
		}
		$SizeList = array();//机柜此位置可用空间列表
		for($i=1;$i<=$SizeLong;$i++)
		{
			$SizeList[$i] = $i;
		}
				
		
		$StatusList = $this->Status;
	  $IntellectPropertyList = $this->IntellectProperty;
		
		
		include $this->tpl('Config_Machine_modify');
	}
	
	//更新机柜信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$bind=$this->request->from('MachineId','MachineCode','Position','Current','Size','LocalIP','WebIP',"Comment","ServerId","CageId",
		"MachineName","Version","MachineStatus","IntellectProperty","EstateCode","User","Purpose","Flag","Owner");

		
		$bind['MachineCode'] = trim($bind['MachineCode']);
		$bind['Position'] = intval($bind['Position']);
		$bind['Current'] = sprintf("%.2f",$bind['Current']);
		$bind['Size'] = intval($bind['Size']);		
		$bind['LocalIP'] = Base_Common::ip2long($bind['LocalIP']);
		$bind['WebIP'] = Base_Common::ip2long($bind['WebIP']);
		//$bind['Comment'] = $bind['Comment']);不能加trim
		$bind['ServerId'] = trim($bind['ServerId']);
		$bind['CageId'] = abs($bind['CageId']);	
		$bind['MachineName'] = trim($bind['MachineName']);
		$bind['Version'] = trim($bind['Version']);
		$bind['MachineStatus'] = trim($bind['MachineStatus']);
		$bind["User"] = trim($bind['User']);
		$bind["Purpose"] = trim($bind['Purpose']);
		$bind["EstateCode"] = trim($bind['EstateCode']);
		$bind["Flag"] = abs($bind['Flag']);		
		$bind["Owner"] = trim($bind['Owner']);		
		$bind['Udate'] = time();
		
		if($bind["Flag"] == '1')
		{
			$bind["Owner"] = "";
			$bind["IntellectProperty"] = abs($bind['IntellectProperty']);
			$Comment["Cpu"] = trim($bind['Comment']['Cpu']);
			$Comment["CpuCount"] = abs($bind['Comment']['CpuCount']);
			$Comment["Memory"] = trim($bind['Comment']['Memory']);
			$Comment["MemoryCount"] = abs($bind['Comment']['MemoryCount']);
			$Comment["Hd"] = trim($bind['Comment']['Hd']);
			$Comment["HdCount"] = abs($bind['Comment']['HdCount']);
			$Comment["HdMode"] = trim($bind['Comment']['HdMode']);
			$Comment["Netcard"] = trim($bind['Comment']['Netcard']);
			$Comment["NetcardCount"] = abs($bind['Comment']['NetcardCount']);
		}else
		{
			$bind["Owner"] = trim($bind['Owner']);
			$bind["IntellectProperty"] = '0';
			
		}
		
		$Comment["Money"] = sprintf("%.2f",$bind['Comment']['Money']);	
		$Comment["Status"] = abs($bind['Comment']['Status']);		
		$Comment["Remark"] = trim($bind['Comment']['Remark']);
		$bind['Comment'] = json_encode($Comment);
			
			
		$CageData =  $this->oCage->getRow($bind['CageId']);
		$MachineData =  $this->oMachine->getRow($bind['MachineId']);
	
		if(!empty($bind['MachineCode']))
		{
			$array = array('MachineCode'=>$bind['MachineCode']);
			$CheckMachineCodeList = $this->oMachine->getByParam($array,"MachineId,MachineCode");
			if(count($CheckMachineCodeList)==0||(count($CheckMachineCodeList)==1 && $CheckMachineCodeList[0]['MachineId']==$bind['MachineId']))
			{
				$response = array();
			}else
			{
				$response = array('errno' => 3);
			}
				
		}
		
		if(count($response)>0)
		{
			exit(json_encode($response));
		}elseif(!empty($bind['EstateCode']))
		{
			$array = array('EstateCode'=>$bind['EstateCode']);
			$CheckEstateCodeList = $this->oMachine->getByParam($array,"MachineId,EstateCode");
			if(count($CheckEstateCodeList)==0||(count($CheckEstateCodeList)==1 && $CheckEstateCodeList[0]['MachineId']==$bind['MachineId']))
			{
				$response = array();
			}else{
				$response = array('errno' => 16);
			}
				
		}
		
		if(count($response)>0)
		{
			exit(json_encode($response));
		}elseif(empty($bind['CageId']))
		{
	  	$response = array('errno' => 4);	
	  }elseif(empty($bind['Position']))
		{
			$response  = array('errno' => 8);	
		}elseif(empty($bind['Size']))
		{
			$response  = array('errno' => 7);	
		}else
		{			
			$Position = $this->oMachine->getMachinePosition($bind['CageId'],$bind['Position']);			
			$long=0;
			if($Position)
			{
				$long = $Position-$bind['Position'];
				
			}else
			{
				$long = $CageData["Size"]-$bind['Position'];
				$long++;
			}
		
			if($bind['Size'] > $long)
			{	
				$response  = array('errno' => 9);	
			}else{
				$response = array();
			}
		}
		
		if($bind['Flag']==1)//这个是在服务器的时候判断的
		{
			if(count($response)>0)
			{
				exit(json_encode($response));
			}elseif($bind['Current']!=0)
			{
		  	$currentCount = $CageData["Current"];// 这台机柜的额定电流
				//这台机柜的实际用掉的电流必须减去本台机器的电流，因为在数据库中是计算这台机器的电流的
			  $currentSum = $this->oMachine->getCurrentByCageId($bind['CageId'])-$MachineData['Current'];
			  if($bind['Current']>($currentCount-$currentSum))
			  {		  	
			  	$response  = array('errno' => 6);	
			  }else{
					$response = array();
				}
		  }	
			if(count($response)>0)
			{
				exit(json_encode($response));
			}elseif(empty($bind['ServerId']))
			{
				$response  = array('errno' => 12);					
			}			
		}
		
		if($bind["Flag"]=='1'|| $bind["Flag"]=="2" ||$bind["Flag"]=='3' || $bind["Flag"]=="4" )
		{
			if(count($response)>0)
			{
				exit(json_encode($response));
			}elseif(!empty($bind['LocalIP']))
			{
				$array = array('LocalIP'=>$bind['LocalIP']);
				$CheckLocalIPList = $this->oMachine->getByParam($array,"MachineId,LocalIP");
				if(count($CheckLocalIPList)==0||(count($CheckLocalIPList)==1 && $CheckLocalIPList[0]['MachineId']==$bind['MachineId']))
				{
					$response = array();
				}else{
					$response = array('errno' => 10);
				}					
			}
			if(count($response)>0)
			{
				exit(json_encode($response));
			}elseif(!empty($bind['WebIP']))
			{
				$array = array('WebIP'=>$bind['WebIP']);
				$CheckWebIPList = $this->oMachine->getByParam($array,"MachineId,WebIP");
				if(count($CheckWebIPList)==0||(count($CheckWebIPList)==1 && $CheckWebIPList[0]['MachineId']==$bind['MachineId']))
				{
					$response = array();
				}else{
					$response = array('errno' => 11);
				}					
			}
		}
		
		
		if(count($response)>0)
		{
			exit(json_encode($response));
		}elseif(empty($bind['MachineName']))
		{			
			$response  = array('errno' => 13);	
		}elseif(empty($bind['Version']))
		{			
			$response  = array('errno' => 14);	
		}elseif(empty($bind['MachineStatus']))
		{			
			$response  = array('errno' => 15);	
		}else
		{			
			    
					
				$res = $this->oMachine->update($bind['MachineId'], $bind);
				if($res)//修改成功，写入管理员日志
				{
					$response = array('errno' => 0);
				  /**
					 * 记录日志
					 */
					$log = "修改服务器信息\n\nServerIp:" . $this->request->getServer('SERVER_ADDR') . "\n\nMachineId:".$bind['MachineId']."\n\n" . json_encode($MachineData)."\n\n".json_encode($bind);
					$this->oLogManager->push('log', $log);
					
					//当修改的为服务器时，需要判断是否该了机房、机柜、位置，这3个属性，若改了，则需要批量修改其下的其他设备（CPU、HD）的位置
					if($bind["Flag"]=='1'&& ($MachineData['CageId']!=$bind['CageId'] || $MachineData['Position']!=$bind['Position']))
					{
						$fields = "MachineId";//,CageId,Position,Size
						$params = array("Flag"=>5,"Owner"=>$bind['MachineId']);// 其他设备
						$MachineList = $this->oMachine->getByParam($params,$fields);
						foreach($MachineList as $k => $v)
						{
							$otherArr = array('CageId'=> $bind['CageId'],'Position'=> $bind['Position'],'Size'=> $bind['Size']);
							$this->oMachine->update($v['MachineId'], $otherArr);
						}
					}
				}else
				{
					$response = array('errno' => 17);					
				}
		}
	
		exit(json_encode($response));
	}
	
	//删除机柜
	public function deleteAction()
	{
		$MachineId = trim($this->request->MachineId);
		$Flag = abs($this->request->Flag);
		
		//检查权限
		if($Flag == 1)
		{			
			$sign = "?ctl=config/machine";
		}elseif($Flag == 5)//5是其他设备
		{
			$sign = "?ctl=config/machine&ac=index&Flag=5";
		}else//8是网络设备
		{
			$sign = "?ctl=config/machine&ac=index&Flag=8";			
		}
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_DELETE);	
		$MachineInfo = $this->oMachine->getRow($MachineId);
		$res = $this->oMachine->delete($MachineId);//成功返回true否则false
		if($res)
		{
			/**
			 * 记录日志
			 */
			$log = "删除服务器信息\n\nServerIp:" . $this->request->getServer('SERVER_ADDR') . "\n\nMachineId:".$MachineId."\n\n" .json_encode($MachineInfo);
			$this->oLogManager->push('log', $log);
		}
		
		$this->response->goBack();
	}
	/**
	 * 获取机柜列表
	 * @return 下拉列表
	 */
	/*public function getMachineAction()
	{
		$MachineArr = $this->oMachine->getAll();
		echo "<option value=0>全部</option>";

		if(count($MachineArr))
		{
			foreach($MachineArr as  $MachineId => $MachineData)
			{
				echo "<option value='{$MachineId}'>{$MachineData['name']}</option>";
			}
		}
	}*/
	
	
	/*
	* 根据机房id获取机柜列表信息  selena  2013/3/19
	**/
	public function getCageListAction()
	{
		$DepotId = $this->request->DepotId;
		$CageArr = $this->oCage->getAll($DepotId);	
		
		$str = "";	
		$CageArr = $CageArr[$DepotId];
		foreach($CageArr as $key => $val)
		{ 						
				$str .= "<option value='".$val['CageId']."' >".$val['CageCode']."</option>";			
		}
		echo  $str;
	}
	public function getPositionList($CageId)
	{
		$CageData =  $this->oCage->getRow($CageId);
		$params = array("CageId"=>$CageId);	
		$fields = "CageId,Size,Position";
		$MachineArr = $this->oMachine->getByParam($params,$fields);	
		$CageMap = array();
		for($i=1;$i<=$CageData['Size'];$i++)
		{
			$CageMap[$i]=0;
		}
		foreach($MachineArr as $key => $val)
		{
			 
			 if(array_key_exists($val['Position'],$CageMap))
			 {
			 		$CageMap[$val['Position']]=1;
			 		$long = $val["Size"]-1;
			 		
			 		if($long!=0)
			 		{
			 			for($j=1;$j<=$long;$j++)
			 			{
			 				$CageMap[$val['Position']+$j] = 1;
			 			}
			 		}
			 }
		}
		/*foreach($CageMap as $k=> $v)
		{
			if($v!=0)
			{
				unset($CageMap[$k]);
			}			
		}*/
		return $CageMap;
	}
	public function getCagePositionListAction()
	{
		$CageId = $this->request->CageId;	
		$CageData =  $this->oCage->getRow($CageId);
		$CageMap = $this->getPositionList($CageId);
		$str = "";
		foreach($CageMap as $k=> $v)
		{
			if($v==0)
			{
				$str .="<option value='$k'>行{$k}</option>";	
			}					
		}
		
		$currentCount = $CageData["Current"];
		$currentSum = $this->oMachine->getCurrentByCageId($CageId);
		$current = $currentCount-$currentSum;
		echo json_encode(array("option"=>$str,"current"=>$current));
	}
	public function getSizeList($CageId,$PositionId)
	{
		$CageData =  $this->oCage->getRow($CageId);
		$Position = $this->oMachine->getMachinePosition($CageId,$PositionId);
		$long=0;
		if($CageData)
		{
			if($Position)
			{
				$long = $Position-$PositionId;
				
			}else
			{
				$long = $CageData["Size"]-$PositionId;
				$long++;
			}		
	  }
			
		return $long;
	} 
	public function getMachineSizeAction()
	{
		$CageId = intval($this->request->CageId);		
		$PositionId = intval($this->request->PositionId);	
		$long = $this->getSizeList($CageId,$PositionId);
		$str = "";
		for($i=1;$i<=$long;$i++)
		{
			$str .= "<option value='$i'>{$i}个空间</option>";
		}
		echo $str;
	}
	/*public function getPartnerListAction()
	{
		$AppId = $this->request->AppId;
		$PartnerList = $this->oPartnerApp->getAppAll($AppId,"PartnerId,name");
		
		$str = "";
		foreach($PartnerList as $k=> $v)
		{
			$str.="<option value='$k'>".$v["name"]."</option>";
		}
		echo $str;
	}
	public function getServerListAction()
	{
		$AppId = $this->request->AppId;
		$PartnerId = $this->request->PartnerId;
		$AppPartnerList = $this->oServer->getByAppPartner($AppId,$PartnerId,"ServerId,name");
		$str = "";
		foreach($AppPartnerList as $k=> $v)
		{
			$str.="<option value='$k'>".$v["name"]."</option>";
		}
		echo $str;
	}*/
	//check machineCode
	public function checkMachineCodeAction()
	{
		$MachineCode = $this->request->MachineCode;
		$MachineId = $this->request->MachineId ? abs($this->request->MachineId):0;
		if(!empty($MachineCode))
		{		
			if($MachineId){//有MachineId 表示是修改页面的 
				$array = array('MachineCode'=>$MachineCode);
				$checkMachineCodeList = $this->oMachine->getByParam($array,"MachineId,MachineCode");
				if(count($checkMachineCodeList)==0 || (count($checkMachineCodeList)==1 && $checkMachineCodeList[0]["MachineId"]==$MachineId))
				{
					echo "yes";
				}else
				{
					echo "no";	
				}
			}else{			
				$return = $this->oMachine->getRowByKey('MachineCode',$MachineCode);
				if($return)
				{
					echo "no";
					
				}else{
					
					echo "yes";
				}
			}
		}
	}
	
	//check EstateCode
	public function checkEstateCodeAction()
	{
		$EstateCode = $this->request->EstateCode;
		$MachineId = $this->request->MachineId ? abs($this->request->MachineId):0;
		if(!empty($EstateCode))
		{			
			if($MachineId){//有MachineId 表示是修改页面的 
				$array = array('EstateCode'=>$EstateCode);
				$checkEstateCodeList = $this->oMachine->getByParam($array,"MachineId,EstateCode");
				if(count($checkEstateCodeList)==0 || (count($checkEstateCodeList)==1 && $checkEstateCodeList[0]["MachineId"]==$MachineId))
				{
					echo "yes";
				}else
				{
					echo "no";	
				}
			}else
			{			
				$return = $this->oMachine->getRowByKey('EstateCode',$EstateCode);
				if($return)
				{
					echo "no";
					
				}else{
					
					echo "yes";
				}
			}
		}
		
	}
	public function getMachineInfoAction()
	{
		$MachineId = $this->request->MachineId;
		$MachineInfo = $this->oMachine->getRow($MachineId);
		
		$DepotList = $this->DepotList;
		$CageList = $this->CageList;
		/*$AppList = $this->AppList;
		$PartnerList = $this->PartnerList;
		$ServerList = $this->ServerList;*/
		
		$MachineInfo['LocalIP'] = long2ip($MachineInfo['LocalIP']);
		$MachineInfo['WebIP'] = long2ip($MachineInfo['WebIP']);
		$MachineInfo['Comment'] = json_decode($MachineInfo['Comment'],true);
	
		//判断ServerId是否是多个
		$ServerIdArr = explode(',',$MachineInfo['ServerId']);
		if(count($ServerIdArr)>1)//添加时选择的游戏是其他
		{
			//$MachineInfo['AppId'] = "other";
			$MachineInfo['AppName'] = "其它";
			$MachineInfo['ServerName'] = $MachineInfo['ServerId'];
		}else{
		 	//选择了一项具体的游戏						
			$ServerInfo = $this->oServer->getRow($MachineInfo['ServerId']);	
			$MachineInfo['ServerName'] = $ServerInfo['name'];
			$MachineInfo['AppName'] = $this->oApp->getOne($ServerInfo['AppId'],"name");
			$MachineInfo['PartnerName'] = $this->oPartner->getOne($ServerInfo['PartnerId'],"name");
	  }
		$CageInfo = $this->oCage->getRow($MachineInfo['CageId']);
		$MachineInfo['CageCode'] = $CageInfo['CageCode'];
		$MachineInfo['DepotName'] = $this->oDepot->getOne($CageInfo['DepotId'],"name");					

	  $StatusList = $this->Status;
		$IntellectPropertyList = $this->IntellectProperty;
		$MachineInfo['IntellectProperty'] = $IntellectPropertyList[$MachineInfo['IntellectProperty']];

		$MachineInfo['Comment']['Status']=$StatusList[$MachineInfo['Comment']['Status']];
		include $this->tpl('Config_Machine_MachineInfo');
	}
	
	//添加网络设备界面  机器和网络设备和其他设备可以写到的一个Action里，然后根据Flag判断 ，修改同理（这里为了维护的简单就给分开写了），
	//只在入库的时候，用了同一个方法
	public function networkAddAction()
	{ 
		//检查当前页面权限
		$sign = '?ctl=config/machine&ac=index&Flag=8';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$FlagList = $this->FlagList;
		$DepotList = $this->DepotList;//机房列表
		$StatusList = $this->Status;
		include $this->tpl('Config_Machine_networkAdd');
	}
	//修改网络设备界面
	public function networkModifyAction()
	{
		//检查当前页面权限
		$sign = '?ctl=config/machine&ac=index&Flag=8';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$MachineId = $this->request->MachineId;
		$MachineInfo = $this->oMachine->getRow($MachineId);
		$MachineInfo['LocalIP'] = long2ip($MachineInfo['LocalIP']);
		$MachineInfo['WebIP'] = long2ip($MachineInfo['WebIP']);
		
		$MachineInfo['Comment'] = json_decode($MachineInfo['Comment'],true);

		$CageInfo = $this->oCage->getRow($MachineInfo['CageId']);		
		$MachineInfo['DepotId'] = $CageInfo['DepotId'];

		$DepotList = $this->DepotList;//机房列表
		$CageList = $this->oCage->getAll($MachineInfo['DepotId']); //机柜列表
		$PositionList = $this->getPositionList($MachineInfo['CageId']);//机柜位置列表				
		foreach($PositionList as $k=> $v)
		{
			if($v!=0 && $k!=$MachineInfo['Position'])
			{
				unset($PositionList[$k]);
			}					
		}
		
		$SizeLong = $this->getSizeList($MachineInfo['CageId'],$MachineInfo['Position']);
		$SizeList = array();//机柜此位置可用空间列表
		for($i=1;$i<=$SizeLong;$i++)
		{
			$SizeList[$i] = $i;
		}
		$FlagList = $this->FlagList;//类型列表
		$StatusList = $this->Status;//实物标签列表
		
		include $this->tpl('Config_Machine_networkModify');
	}
	//添加其他设备界面
	public function otherAddAction()
	{
		//检查当前页面权限
		$sign = '?ctl=config/machine&ac=index&Flag=5';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$FlagList = $this->FlagList;
		$DepotList = $this->DepotList;//机房列表
		$StatusList = $this->Status;
		include $this->tpl('Config_Machine_otherAdd');
	}
	//修改其他设备界面
	public function otherModifyAction()
	{
		//检查当前页面权限
		$sign = '?ctl=config/machine&ac=index&Flag=5';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$MachineId = $this->request->MachineId;
		$MachineInfo = $this->oMachine->getRow($MachineId);
		
		$MachineInfo['Comment'] = json_decode($MachineInfo['Comment'],true);

		$CageInfo = $this->oCage->getRow($MachineInfo['CageId']);		
		$MachineInfo['DepotId'] = $CageInfo['DepotId'];

		$DepotList = $this->DepotList;//机房列表
		$CageList = $this->oCage->getAll($MachineInfo['DepotId']); //机柜列表
		$PositionList = $this->getPositionList($MachineInfo['CageId']);//机柜位置列表		
		foreach($PositionList as $k=> $v)
		{
			if($v!=0 && $k!=$MachineInfo['Position'])
			{
				unset($PositionList[$k]);
			}					
		}
				
		$SizeLong = $this->getSizeList($MachineInfo['CageId'],$MachineInfo['Position']);
		$SizeList = array();//机柜此位置可用空间列表
		for($i=1;$i<=$SizeLong;$i++)
		{
			$SizeList[$i] = $i;
		}
		$FlagList = $this->FlagList;//类型列表
		$StatusList = $this->Status;//实物标签列表
		
		if($MachineInfo['Owner']!=0)// $MachineInfo['Flag']==5 &&  若是其他设备，并且所属机器不为空，需要显示所属机器的序列号 MachineCode 
		{		
			 $MachineInfo['OwnerCode'] = $this->oMachine->getOne($MachineInfo['Owner'],'MachineCode');			 
		}
		
		include $this->tpl('Config_Machine_otherModify');
	}	
	
	
	public function checkOwnerCodeAction()
	{
		
		$MachineCode = $this->request->OwnerCode;
		$MachineInfo = $this->oMachine->getRowByKey("MachineCode",$MachineCode);	
		if($MachineInfo)
		{
			$returnArr = array("MachineId"=> $MachineInfo['MachineId'],"CageId" => $MachineInfo['CageId'],"Position" => $MachineInfo['Position'],"Size" => $MachineInfo['Size']);
			echo json_encode($returnArr);		
		}else{			
			echo "no";
		}		
	}
	public function ipListAction()
	{
		//检查当前页面权限
		$sign = '?ctl=config/machine&ac=ip.list';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		$CageList = $this->CageList;
		$DepotId = $this->request->DepotId;
		$export = $this->request->export? intval($this->request->export):0;
		$page = $this->request->page?intval($this->request->page):1;
		$pageSize = $export?0:20;

		$DepotList = $this->DepotList;
		$ServerList = $this->ServerList;
		$PartnerList = $this->PartnerList;
		
		$param = array();
		if($DepotId)//机房
		{
			$param['DepotId'] = $DepotId;
			$CageIdList = Base_Common::getArrList($CageList[$DepotId]);
		}
		$MachineList = $this->oMachine->getIpList($CageIdList,($page-1)*$pageSize,$pageSize);
		$MachineArr = $MachineList['MachineDetail'];

		foreach($MachineArr as $MachineId=> &$MachineInfo)
		{			
			$MachineInfo['PartnerName'] = $PartnerList[$ServerList[$MachineInfo['ServerId']]['PartnerId']]['name'];
			$MachineInfo['LocalIP'] = long2ip($MachineInfo['LocalIP']);			
			$MachineInfo['WebIP'] = long2ip($MachineInfo['WebIP']);
			$MachineInfo['Purpose'] = $MachineInfo['Purpose'];
			
		}

		//翻页
		$pageParam = $param + array("export"=>0);
		$page_url = Base_Common::getUrl('','config/machine','ip.list',$pageParam)."&page=~page~";
		$page_content =  base_common::multi($MachineList['MachineCount'], $page_url, $page, $pagesize, 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
		//表格导出
		$execlParam = $param+array("export"=>1);
		$export_var = "<a href =".(Base_Common::getUrl('','config/machine','ip.list',$execlParam))."><导出表格></a>";
		if($export == 1)
		{
			$oExcel = new Third_Excel();
			$FileName='IP地址信息';
			$oExcel->download($FileName)->addSheet('IP地址信息');
			//标题栏			
			$title = array("序列号","资产编号","内网IP","外网IP","项目","用途");
			$oExcel->addRows(array($title));
			
			foreach($MachineArr as $MachineCode=> $MachineInfot)
			{
				//生成单行数据
				$t = array();
				$t['MachineCode'] = $MachineInfot['MachineCode'];
				$t['EstateCode'] = $MachineInfot['EstateCode'];			
				$t['LocalIP'] = $MachineInfot['LocalIP'];
				$t['WebIP'] = $MachineInfot['WebIP'];						  
			  $t['PartnerName'] = $MachineInfot['PartnerName'];
			  $t['Purpose'] = $MachineInfot['Purpose'];			
				$oExcel->addRows(array($t));	
				unset($t);					
			}
			$oExcel->closeSheet()->close();	
		}
		include $this->tpl('Config_Machine_ipList');			
	}
	public function showListAction()
	{
		//检查当前页面权限
		$sign = '?ctl=config/machine&ac=show.list';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		$managerInfo = $this->manager->getRow($this->manager->id,"machine_show_list");
		$ShowList = json_decode($managerInfo['machine_show_list'],true);
		$Show = $ShowList["Show"];
		include $this->tpl('Config_Machine_showList');		
	}
	public function showAddAction()
	{
		//检查当前页面权限
		$sign = '?ctl=config/machine&ac=show.list';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$bind=$this->request->from('Show');
		$bind = json_encode($bind);
		$res = $this->manager->update($this->manager->id,array("machine_show_list"=> $bind));
	  
		$response = $res ? array('errno' => 0) : array('errno' => 2);		
		exit(json_encode($response));		
	}
	public function machineLogAction()
	{		
		//检查当前页面权限
		$sign = '?ctl=config/machine&ac=machine.log';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		$manager_id = $this->manager->id;
		$StartDate = $this->request->StartDate;

		if($StartDate=="" || strtotime($StartDate) > time())
		{
			$StartDate = date("Y-m-01",time());
		}
		
		$EndDate = $this->request->EndDate ? $this->request->EndDate:date("Y-m-d");

		include $this->tpl('Config_Machine_MachineLog');		
	}
	public function machineLogIframeAction()
	{		
		//检查当前页面权限
		$sign = '?ctl=config/machine&ac=machine.log';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		set_time_limit(0);
		$StartDate = $this->request->StartDate;

		if($StartDate=="" || strtotime($StartDate) > time())
		{
			$StartDate = date("Y-m-01",time());
		}
		
		$EndDate = $this->request->EndDate ? $this->request->EndDate:date("Y-m-d");

		$fields = "name,addtime,log";
		$LogInfo = "服务器信息";
		$MachineLogList = $this->oLogManager->getLogsManagerParams($fields,$LogInfo,$StartDate,$EndDate);
		
		/*foreach($MachineLogList as $MachineId=> $MachineList)
		{
			//为数组里的元素赋值 
			$MachineRecom = $this->recombinationArr($MachineList);
			echo "<pre>";
		  print_r($MachineRecom);
		  //对比在此机器下的修改日志
			foreach($MachineRecom as $key=> $val)
			{
				if($val['Tip']=='修改')
				{
					
				}
				
			}
		}*/
		$MachineLogArr = array();
		foreach($MachineLogList as $MachineCode=> $MachineList)
		{
			foreach($MachineList as $key=> $MachineInfo)
			{				
				$MachineLogArr[] = $MachineInfo;	
			}				
		}	
		$MachineLogArr = $this->recombinationArrLog($MachineLogArr);
		include $this->tpl('Config_Machine_MachineLogIframe');		
	}	
	public function recombinationArrLog($MachineArr)
	{
		//$DepotList = $this->DepotList;
		//$CageList = $this->CageList;
		$span = "<span style='color:red;'>";
		
		$AppList = $this->AppList;
		$PartnerList = $this->PartnerList;
		$ServerList = $this->ServerList;
		
		$StatusList = $this->Status;
		$IntellectPropertyList = $this->IntellectProperty;
		$FlagList = $this->FlagList;
		foreach($MachineArr as $MachineId => &$MachineInfo)
		{			
			if($MachineInfo["CageId_span"])
				$MachineInfo["CageId"] = $MachineInfo["CageId_span"];
			if($MachineInfo["CageId"])
			{
				if(!isset($CageInfoList["CageId"]) && !isset($DepotInfoList["CageId"]))
				{
					$CageInfo = $this->oCage->getRow($MachineInfo["CageId"]);
					$CageInfoList[$MachineInfo["CageId"]] = $CageInfo;
					$DepotInfo = $this->oDepot->getRow($CageInfo["DepotId"]);
					$DepotInfoList[$MachineInfo["CageId"]] = $DepotInfo;					
				}
			}
			
			if($MachineInfo["CageId_span"])
			{
				$MachineInfo['CageCode'] = $span.$CageInfoList[$MachineInfo['CageId']]['CageCode']."</span>";
				$MachineInfo['DepotName'] = $span.$DepotInfoList[$MachineInfo['CageId']]['name']."</span>";
				$MachineInfo['CageX'] = $span.$CageInfoList[$MachineInfo['CageId']]['X']."</span>";
			}else
			{
				$MachineInfo['CageCode'] = $CageInfoList[$MachineInfo['CageId']]['CageCode'];
				$MachineInfo['DepotName'] = $DepotInfoList[$MachineInfo['CageId']]['name'];
				$MachineInfo['CageX'] = $CageInfoList[$MachineInfo['CageId']]['X'];				
			}
			if($MachineInfo['ServerId_span'])
				$MachineInfo['ServerId'] = $MachineInfo['ServerId_span'];
				
			$ServerIdArr = explode(",",$MachineInfo['ServerId']);
			if(count($ServerIdArr)>1)
			{
				$MachineInfo['ServerName'] = "其它";
				$MachineInfo['AppName'] = $MachineInfo['ServerId'];
			}else{
				$MachineInfo['ServerName'] = $ServerList[$MachineInfo['ServerId']]['name'];
				$MachineInfo['AppName'] = $AppList[$ServerList[$MachineInfo['ServerId']]['AppId']]['name'];
				$MachineInfo['PartnerName'] = $PartnerList[$ServerList[$MachineInfo['ServerId']]['PartnerId']]['name'];				
			}
			
			if($MachineInfo['ServerId_span'])
			{
				$MachineInfo['ServerName'] = $span.$MachineInfo['ServerName']."</span>";
				$MachineInfo['AppName'] = $span.$MachineInfo['AppName']."</span>";
				$MachineInfo['PartnerName'] = $span.$MachineInfo['PartnerName']."</span>";
			}
			
			if($MachineInfo['LocalIP_span'])
				$MachineInfo['LocalIP'] = $MachineInfo['LocalIP_span'];		
				
			$MachineInfo['LocalIP'] = long2ip($MachineInfo['LocalIP']);		
			
			if($MachineInfo['LocalIP_span'])
				$MachineInfo['LocalIP'] = $span.$MachineInfo['LocalIP']."</span>";
				
			if($MachineInfo['WebIP_span'])
				$MachineInfo['WebIP'] = $MachineInfo['WebIP_span'];		
				
			$MachineInfo['WebIP'] = long2ip($MachineInfo['WebIP']);		
			
			if($MachineInfo['WebIP_span'])
				$MachineInfo['WebIP'] = $span.$MachineInfo['WebIP']."</span>";
			
			if($MachineInfo['IntellectProperty_span'])
			{
				$MachineInfo['IntellectProperty'] = $MachineInfo['IntellectProperty_span']; 
			}
												
			$MachineInfo['IntellectProperty'] = $IntellectPropertyList[$MachineInfo['IntellectProperty']];
			
			if($MachineInfo['IntellectProperty_span'])
			{
				$MachineInfo['IntellectProperty'] = $span.$MachineInfo['IntellectProperty']."</span>";
			}				
				
			$MachineInfo['Comment'] = json_decode($MachineInfo['Comment'],true);
			
			if($MachineInfo['Comment']['Status_span'])
				$MachineInfo['Comment']['Status'] = $MachineInfo['Comment']['Status_span'];		
								
			$MachineInfo['Comment']['Status'] = $StatusList[$MachineInfo['Comment']['Status']];
			if($MachineInfo['Comment']['Status_span'])
			   $MachineInfo['Comment']['Status'] = $span.$MachineInfo['Comment']['Status']."</span>";
			   
			if($MachineInfo['Flag_span'])
				$MachineInfo['Flag'] = 	$MachineInfo['Flag_span'];
			if($MachineInfo['Flag']==1)
			{
				$MachineInfo['Flag'] = "服务器";
			}elseif($MachineInfo['Flag']==5)
			{
				$MachineInfo['Flag'] = "其他设备";				
			}else 
			{
				$MachineInfo['Flag'] = $FlagList[$MachineInfo['Flag']];				
			}
			if($MachineInfo['Flag_span'])
				$MachineInfo['Flag'] = $span.$MachineInfo['Flag']."</span>";
		}
		return $MachineArr;
	}
	
	//检查IP是否存在
	public function checkIpAction()
	{
		$type = trim($this->request->type); //LocalIP 或者 WebIP
		$ip = Base_Common::ip2long($this->request->ip);
		$MachineId = $this->request->MachineId ? abs($this->request->MachineId):0;
		if(!empty($ip))
		{			
			if($MachineId){//有MachineId 表示是修改页面的 
				$array = array($type=>$ip);
				$checkIPList = $this->oMachine->getByParam($array,"MachineId,".$type);
				if(count($checkIPList)==0 || (count($checkIPList)==1 && $checkIPList[0]["MachineId"]==$MachineId))
				{
					echo "yes";
				}else
				{
					echo "no";	
				}
			}else
			{			
				$return = $this->oMachine->getRowByKey($type,$ip);
				if($return)
				{
					echo "no";
					
				}else{
					
					echo "yes";
				}
			}
		}
		
	}
}

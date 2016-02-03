<?php
/**
 * 产品管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: PackController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Product_PackController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/product/pack';
	/**
	 * Product对象
	 * @var object
	 */
	protected $oProduct;
	protected $oProductPack;
	protected $oProductType;

	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oHero = new Config_Hero();
		$this->oSkin = new Config_Skin();
		$this->oMoney = new Config_Money();

		$this->oProduct = new Config_Product_Product();
		$this->oProductType = new Config_Product_Type();
		$this->oProductPack = new Config_Product_Pack();

		$this->oPermission = new Config_Permission();
		
		
		$this->oApp = new Config_App();
		$this->oPartner = new Config_Partner();
		$this->oPartnerApp = new Config_Partner_App();
		$this->oArea = new Config_Area();

		$this->oUser = new Lm_User();

		//获取用户可以查看的游戏列表
		$this->permitted_app = $this->oPermission->getApp($this->manager->data_groups,'AppId,name');
		//预处理地区信息
		$this->AreaList = $this->oArea->getAll();
		$this->AppList = $this->oApp->getAll();


	}
	//产品配置列表页面
	public function indexAction()
	{
		$AppList = $this->AppList;
		$AppId = $this->request->AppId;

		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$ProductPackArr = $this->oProductPack->getAll($AppId);
		if($ProductPackArr)
		{
			foreach($ProductPackArr as $AppId => $AppData)
			{
				foreach($AppData as $ProductPackId => $ProductPackData)
				{
					$ProductPackArr[$AppId][$ProductPackId]['AppName'] = $AppList[$ProductPackData['AppId']]['name'];
					$Comment = json_decode($ProductPackData['Comment'],true);
					if(is_array($Comment))
					{
						unset($ProductList);
						unset($TypeList);
						foreach($Comment as $Type => $TypeInfo)
						{
							foreach($TypeInfo as $ProductId => $Count)
							{
								if(!isset($ProductInfo[$Type][$AppId][$ProductId]))
								{
									if($Type=="hero")
									{
										$ProductInfo[$Type][$AppId][$ProductId] = $this->oHero->getRow($ProductId,$AppId,'*');
									}
									elseif($Type=="skin")
									{
										$ProductInfo[$Type][$AppId][$ProductId] = $this->oSkin->getRow($ProductId,$AppId,'*');
									}
									elseif($Type=="product")
									{
										$ProductInfo[$Type][$AppId][$ProductId] = $this->oProduct->getRow($ProductId,$AppId,'*');
									}
									elseif($Type=="money")
									{
										$ProductInfo[$Type][$AppId][$ProductId] = $this->oMoney->getRow($ProductId,$AppId,'*');
									}
									elseif($Type=="appcoin")
									{
										$AppInfo = $this->oApp->getRow($AppId);
										$Comment = json_decode($AppInfo['comment'],true);
										$ProductInfo[$Type][$AppId][$ProductId]['name'] = $Comment['coin_name'];
									}
								}
								$ProductList[$Type]['detail'][$ProductId] = $ProductInfo[$Type][$AppId][$ProductId]['name']."*".$Count."个";
							}
							$TypeList[$Type] = implode(",",$ProductList[$Type]['detail']);							
						}
						$ProductPackArr[$AppId][$ProductPackId]['ProductListText'] = implode(",",$TypeList);		
					}
					else
					{
						$ProductPackArr[$AppId][$ProductPackId]['ProductListText'] = "无道具";		
					}
				}
			}
		}
		include $this->tpl('Config_Product_Pack_list');
	}
	//添加产品填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Product_Pack_add');
	}
	
	//添加新产品
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','AppId','ProductPrice','ProductId','Type','number','UseCountLimit','AsignCountLimit','UseTimeLag');

		if($bind['AppId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}
		elseif($bind['ProductPrice']<0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['UseCountLimit']<0)
		{
			$response = array('errno' => 5);
		}
		elseif($bind['AsignCountLimit']<0)
		{
			$response = array('errno' => 6);
		}
		elseif($bind['UseTimeLag']<0)
		{
			$response = array('errno' => 7);
		}
		else
		{	
			foreach($bind['ProductId'] as $key => $product)
			{
				if($bind['number'][$key]>0)
				{
					$bind['Comment'][$bind['Type'][$key]][$product] += $bind['number'][$key];
				}	
			}
			unset($bind['ProductId'],$bind['number'],$bind['Type']);
			ksort($bind['Comment']);
			$bind['Comment'] = json_encode($bind['Comment']);
			$res = $this->oProductPack->insert($bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改产品信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$ProductPackId = $this->request->ProductPackId;
		$ProductPack = $this->oProductPack->getRow($ProductPackId,'*');
		$AppList = $this->AppList;
		$AppName = $AppList[$ProductPack['AppId']]['name'];
		$ProductPack['Comment'] = json_decode($ProductPack['Comment'],true);
		$ProductTypeList = $this->oProductType->getAll($ProductPack['AppId']);
		$ProductTypeListShow = $ProductTypeList[$ProductPack['AppId']];
		foreach($ProductPack['Comment'] as $product => $productinfo)
		{
			$ProductPack['productinfo'][$product]=$this->oProduct->getRow($product,$ProductPack['AppId']);
			$ProductPack['productinfo'][$product]['count']=	$productinfo;	
		}
		
		include $this->tpl('Config_Product_Pack_modify');
	}
	
	//更新产品信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('ProductPackId','AppId','name','ProductPrice','ProductId','Type','number','UseCountLimit','AsignCountLimit','UseTimeLag');

		if($bind['ProductPackId']==0)
		{
			$response = array('errno' => 4);
		}
		elseif($bind['AppId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}
		elseif($bind['ProductPrice']<0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['UseCountLimit']<0)
		{
			$response = array('errno' => 5);
		}
		elseif($bind['AsignCountLimit']<0)
		{
			$response = array('errno' => 6);
		}
		elseif($bind['UseTimeLag']<0)
		{
			$response = array('errno' => 7);
		}
		else
		{	
			foreach($bind['ProductId'] as $key => $product)
			{
				if($bind['number'][$key]>0)
				{
					$bind['Comment'][$bind['Type'][$key]][$product] += $bind['number'][$key];
				}	
			}
			unset($bind['ProductId'],$bind['number'],$bind['Type']);
			ksort($bind['Comment']);
			$bind['Comment'] = json_encode($bind['Comment']);
			$res = $this->oProductPack->update($bind['ProductPackId'],$bind);;
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除产品
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$ProductPackId = intval($this->request->ProductPackId);
		$this->oProductPack->delete($ProductPackId);
		$this->response->goBack();
	}    
    public function getProductPackAction()
	{
		$AppId = intval($this->request->AppId)?intval($this->request->AppId):0;
		$ProductPackArr = $this->oProductPack->getAll($AppId);

		echo "<option value=''>全部</option>";
		if(is_array($ProductPackArr[$AppId]))
		{
			foreach ($ProductPackArr[$AppId] as $ProductPackId => $data)
			{
					echo "<option value='{$ProductPackId}'>{$data['name']}</option>";
			}
		}
	}
	public function genPackCodeAction()
	{
		set_time_limit(0);
		//检查当前页面权限
		$sign = '?ctl=config/product/pack&ac=gen.pack.code.log';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_UPDATE);

		//页面输入变量
		$AppId = intval($this->request->AppId);
		$PartnerId = intval($this->request->PartnerId);
		$ProductPackId = intval($this->request->ProductPackId);
		$GenNum = intval($this->request->GenNum)?intval($this->request->GenNum):100;
		//时间范围初始化
		$EndTime= $this->request->EndTime?strtotime($this->request->EndTime):(time()+30*86400);
		$needBind = $this->request->needBind? intval($this->request->needBind):0;
		
		if($GenNum)
		{
			if($AppId)
			{
				if($PartnerId)
				{
					if($ProductPackId)
					{
						//检查大区配置
						$PartnerAppInfo = $this->oPartnerApp->GetRow(array($PartnerId,$AppId));
						if($PartnerAppInfo['AppId']&&$PartnerAppInfo['PartnerId'])
						{
							//先生成礼包码生成日志
							$GenId = $this->oProductPack->InsertGenPackCodeLog($ProductPackId,$AppId,$PartnerId,$GenNum,$EndTime,$needBind,$this->manager->id);
							if($GenId)
							{
								//验证日志
								$GenInfo = $this->oProductPack->GetGenPackCodeLogById($GenId);
								if($GenInfo['GenId'])
								{
									//根据日志生成礼包码
									$PackCodeList = $this->oProductPack->GenPackCode($GenInfo);
									if(count($PackCodeList))
									{
										//将礼包码插入数据库
										$Gened = $this->oProductPack->InsertPackCode($GenInfo,$PackCodeList);
										$response = $Gened ? array('errno' => 0,'AppId'=>$AppId,'PartnerId'=>$PartnerId,'ProductPackId'=>$ProductPackId,'Gened'=>$Gened,'sign'=>$sign) : array('errno' => 9);
									}
								}
							}
						}
						else
						{
							$response = array('errno' => 4);					 	
						}
					}
					else
					{
						$response = array('errno' => 3);					 	
					}
				}
				else
				{
					$response = array('errno' => 2);					 	
				}
			}
			else
			{
				$response = array('errno' => 1);					 	
			}
		}
		else
		{
			$response = array('errno' => 5);					 	
		}
		echo json_encode($response);
		return true;		
	}
	public function genPackCodeLogAction()
	{
		set_time_limit(0);
		$pagesize = 10;
		//检查当前页面权限
		$sign = '?ctl=config/product/pack&ac=gen.pack.code.log';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);

		//页面输入变量
		$AppId = intval($this->request->AppId);
		$PartnerId = intval($this->request->PartnerId);
		$GenNum = intval($this->request->GenNum)?intval($this->request->GenNum):100;
		$AreaId = $this->request->AreaId?intval($this->request->AreaId):0;
		$app_type = $this->request->app_type?intval($this->request->app_type):0;
		$partner_type = $this->request->partner_type?intval($this->request->partner_type):0;
		$is_abroad = $this->request->is_abroad?intval($this->request->is_abroad):0;
		$page = intval(max($this->request->page,1));
		//时间范围初始化
		$StartDate= $this->request->StartDate?($this->request->StartDate):date("Y-m-01",time());
		$EndDate = $this->request->EndDate?($this->request->EndDate):date("Y-m-d",time());
		$ProductPackArr = $this->oProductPack->getAll($AppId);
		$ProductPackId = $this->request->ProductPackId?intval($this->request->ProductPackId):0;

		//初始化图表配置
		$Input = array('AppId'=>$AppId,
		'PartnerId'=>$PartnerId,
		'is_abroad'=>$is_abroad,
		'AreaId'=>$AreaId,
		'app_type'=>$app_type,
		'partner_type'=>$partner_type,
		'ProductPackId'=>$ProductPackId,
		'export'=>1,);		
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

		}
		//获取用于查询的权限sql语句
		$oWherePartnerPermission = $this->oPermission->getWherePermittedPartner($this->manager->data_groups,$AppId,$PartnerId,$app_type,$partner_type,$AreaList,$AreaId,$is_abroad,'');
		$GenLog = $this->oProductPack->getGenLog(0,0,$ProductPackId,0,$oWherePartnerPermission,($page-1)*$pagesize,$pagesize);
		$page_url = Base_Common::getUrl('','config/product/pack','gen.pack.code.log',$Input)."&page=~page~";	
		$page_content =  base_common::multi($GenLog['GenLogCount'], $page_url, $page, $pagesize, 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
		if(count($GenLog['GenLog']))
		{
			foreach($GenLog['GenLog'] as $GenId => $GenInfo)
			{
				$GenLog['GenLog'][$GenId]['AppName'] = $permitted_app[$GenInfo['AppId']]['name'];
			  	if(!isset($PartnerInfo[$GenInfo['PartnerId']]))
			  	{
			  		$PartnerInfo[$GenInfo['PartnerId']] = $this->oPartner->getRow($GenInfo['PartnerId']);	
			  	}
				if(!isset($ManagerInfo[$GenInfo['ManagerId']]))
				{
					$ManagerInfo[$GenInfo['ManagerId']] = $this->manager->getRow($GenInfo['ManagerId']);	
				}
				$GenLog['GenLog'][$GenId]['PartnerName'] = $PartnerInfo[$GenInfo['PartnerId']]['name'];
				$GenLog['GenLog'][$GenId]['ManagerName'] = $ManagerInfo[$GenInfo['ManagerId']]['name'];
				if(!isset($ProductPackList[$GenInfo['ProductPackId']]))
				{
					$ProductPackList[$GenInfo['ProductPackId']] = $this->oProductPack->getRow($GenInfo['ProductPackId']);
					$Comment = json_decode($ProductPackList[$GenInfo['ProductPackId']]['Comment'],true);
					if(is_array($Comment))
					{
				        unset($ProductList);
			             unset($TypeList);
                        unset($ProductInfo);
						foreach($Comment as $Type => $TypeInfo)
						{
							foreach($TypeInfo as $ProductId => $Count)
							{
								if(!isset($ProductInfo[$Type][$AppId][$ProductId]))
								{
									if($Type=="hero")
									{
										$ProductInfo[$Type][$AppId][$ProductId] = $this->oHero->getRow($ProductId,$AppId,'*');
									}
									elseif($Type=="skin")
									{
										$ProductInfo[$Type][$AppId][$ProductId] = $this->oSkin->getRow($ProductId,$AppId,'*');
									}
									elseif($Type=="product")
									{
										$ProductInfo[$Type][$AppId][$ProductId] = $this->oProduct->getRow($ProductId,$AppId,'*');
									}
									elseif($Type=="money")
									{
										$ProductInfo[$Type][$AppId][$ProductId] = $this->oMoney->getRow($ProductId,$AppId,'*');
									}
									elseif($Type=="appcoin")
									{
										$AppInfo = $this->oApp->getRow($AppId);
										$Comment = json_decode($AppInfo['comment'],true);
										$ProductInfo[$Type][$AppId][$ProductId]['name'] = $Comment['coin_name'];
									}
								}
								$ProductList[$Type]['detail'][$ProductId] = $ProductInfo[$Type][$AppId][$ProductId]['name']."*".$Count."个";
							}
							$TypeList[$Type] = implode(",",$ProductList[$Type]['detail']);							
						}

						$ProductPackArr[$AppId][$GenInfo['ProductPackId']]['ProductListText'] = implode(",",$TypeList);		
					}
					else
					{
						$ProductPackArr[$AppId][$GenInfo['ProductPackId']]['ProductListText'] = "无道具";		
					}
				}
				$GenLog['GenLog'][$GenId]['PackName'] = $ProductPackList[$GenInfo['ProductPackId']]['name'];
				$GenLog['GenLog'][$GenId]['ProductListText'] = $ProductPackArr[$GenInfo['AppId']][$GenInfo['ProductPackId']]['ProductListText'];
				$GenLog['GenLog'][$GenId]['ExportUrl'] = "<a href =".(Base_Common::getUrl('','config/product/pack','download.pack.code',array('export'=>1,'GenId'=>$GenId)))."><导出礼包码></a>";			}
		}
		$page_title = "礼包码生成记录";
		$page_form_action = $sign;
	 	//调取模板
		include $this->tpl('Config_Product_Pack_GenLog');
	}
	public function genAction()
	{
		//检查当前页面权限
		$sign = '?ctl=config/product/pack&ac=gen.pack.code.log';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_UPDATE);

		//页面输入变量
		$AppId = intval($this->request->AppId);
		$PartnerId = intval($this->request->PartnerId);
		$AreaId = $this->request->AreaId?intval($this->request->AreaId):0;
		$app_type = intval($this->request->app_type);
		$partner_type = intval($this->request->partner_type);
		$is_abroad = $this->request->is_abroad?intval($this->request->is_abroad):0;
		$page = intval(max($this->request->page,1));
		//时间范围初始化
		$EndTime= $this->request->EndTime?($this->request->EndTime):date("Y-m-d H:i:s",time()+30*86400);
		$ProductPackArr = $this->oProductPack->getAll($AppId);

		
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

		}
		$page_title = "礼包码生成";
		$page_form_action = $sign;
	 	//调取模板
		include $this->tpl('Config_Product_Pack_Gen');
	}
	public function downloadPackCodeAction()
	{
		//检查当前页面权限
		$sign = '?ctl=config/product/pack&ac=gen.pack.code.log';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);

		//页面输入变量
		$GenId = intval($this->request->GenId);
		$permitted_app = $this->permitted_app;
		//初始化合作商列表
		$permitted_partner = array();
		//初始化服务器列表
		$permitted_server= array();
		
		$AreaList = $this->AreaList;
		//获取当前地区列表
		$AreaList = $this->oArea->getAbroad(0,$AreaList);
		//生成允许的地区id数组
		if($app_type>0)
		{
			//筛选是否平台产品
			$permitted_app = $this->oApp->getApp(0,$permitted_app);
		}
		if($AppId>0)
		{
		 	//获取可查看的权限总表
			$permitted_partner = $this->oPermission->getPartner($this->manager->data_groups,0,'PartnerId,name,AreaId');
			//根据合作方式筛选
			$permitted_partner = $this->oPartnerApp->getPermittedPartnerByPartnerType(0,$permitted_partner);
			//根据所在地区筛选
			$permitted_partner = $this->oPartnerApp->getPermittedPartnerByPartnerArea($AreaList,$permitted_partner);

		}
		//获取用于查询的权限sql语句
		$oWherePartnerPermission = $this->oPermission->getWherePermittedPartner($this->manager->data_groups,$AppId,$PartnerId,$app_type,$partner_type,$AreaList,$AreaId,$is_abroad,'');
	 	$PackCodeList = $this->oProductPack->GetPackCodeByGenId($GenId,$oWherePartnerPermission);
 		if(is_array($PackCodeList))
 		{
			$GenInfo = $this->oProductPack->GetGenPackCodeLogById($GenId);

  			$AppName = $permitted_app[$GenInfo['AppId']]['name'];
	  		$ManagerName = $this->manager->name;

				$FileName=$GenId."-".$AppName."-".$ManagerName."-".date("YmdHis",time());
				$oExcel = new Third_Excel();
				$oExcel->download($FileName)->addSheet('礼包码');
	 			foreach($PackCodeList as $key => $value)
				{
					$t = array();
					//生成单行数据
					$t['Code'] = $value['ProductPackCode'];
					if($value['UsedUser'])
					{
						$UserInfo = $this->oUser->getUserById($value['UsedUser']);
						$t['Used'] = "已使用";	
						$t['UserName'] = $UserInfo['UserName'];
                        $Used++;	                           
					}
                    else
                    {
                        $t['Used'] = "未使用";	
						$t['UserName'] = "";	 
                    }
                    if($value['AsignUser'])
					{
						$UserInfo = $this->oUser->getUserById($value['AsignUser']);
						$t['Asigned'] = "已分配";	
						$t['AsignUserName'] = $UserInfo['UserName'];
                        $Asigned++;	                           
					}
                    else
                    {
                        $t['Used'] = "未分配";	
						$t['AsignUserName'] = "";	 
                    }
					$oExcel->addRows(array($t));						
				}
                unset($t);
                $t['Used'] = "已使用：".$Used;
                $t['Asigned'] = "已分配：".$Asigned;
                $oExcel->addRows(array($t));				
	
					//结束excel
				$oExcel->closeSheet()->close();				
 		}
	}
	public function asignProductPackCodeAction()
	{
		//检查当前页面权限
		$sign = '?ctl=config/product/pack&ac=gen.pack.code.log';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_UPDATE);

		//页面输入变量
		$GenId = intval($this->request->GenId);
		$permitted_app = $this->permitted_app;
		//初始化合作商列表
		$permitted_partner = array();
		//初始化服务器列表
		$permitted_server= array();
		
		$AreaList = $this->AreaList;
		//获取当前地区列表
		$AreaList = $this->oArea->getAbroad(0,$AreaList);
		//生成允许的地区id数组
		if($app_type>0)
		{
			//筛选是否平台产品
			$permitted_app = $this->oApp->getApp(0,$permitted_app);
		}
		if($AppId>0)
		{
		 	//获取可查看的权限总表
			$permitted_partner = $this->oPermission->getPartner($this->manager->data_groups,0,'PartnerId,name,AreaId');
			//根据合作方式筛选
			$permitted_partner = $this->oPartnerApp->getPermittedPartnerByPartnerType(0,$permitted_partner);
			//根据所在地区筛选
			$permitted_partner = $this->oPartnerApp->getPermittedPartnerByPartnerArea($AreaList,$permitted_partner);

		}
		//获取用于查询的权限sql语句
		$oWherePartnerPermission = $this->oPermission->getWherePermittedPartner($this->manager->data_groups,$AppId,$PartnerId,$app_type,$partner_type,$AreaList,$AreaId,$is_abroad,'');
	 	$PackCodeList = $this->oProductPack->GetPackCodeByGenId($GenId,$oWherePartnerPermission);

		$GenInfo = $this->oProductPack->GetGenPackCodeLogById($GenId);
		foreach($PackCodeList as $key => $value)
		{
			$GenInfo['CodeCount']++;
			if($value['AsignUser'])
			{
				$GenInfo['AsingedCodeCount']++;
			}
			if($value['UsedUser'])
			{
				$GenInfo['UsedCodeCount']++;
			}		
		}
		$ProductPackInfo = $this->oProductPack->getRow($GenInfo['ProductPackId']);
		$Comment = json_decode($ProductPackInfo['Comment'],true);
		if(is_array($Comment))
		{
			unset($ProductList);
			unset($TypeList);
            unset($ProductInfo);
            foreach($Comment as $Type => $TypeInfo)
			{
				foreach($TypeInfo as $ProductId => $Count)
				{
					if(!isset($ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId]))
					{
						if($Type=="hero")
						{
							$ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId] = $this->oHero->getRow($ProductId,$ProductPackInfo['AppId'],'*');
						}
						elseif($Type=="skin")
						{
							$ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId] = $this->oSkin->getRow($ProductId,$ProductPackInfo['AppId'],'*');
						}
						elseif($Type=="product")
						{
							$ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId] = $this->oProduct->getRow($ProductId,$ProductPackInfo['AppId'],'*');
						}
						elseif($Type=="money")
						{
							$ProductInfo[$Type][$AppId][$ProductId] = $this->oMoney->getRow($ProductId,$AppId,'*');
						}
						elseif($Type=="appcoin")
						{
							$AppInfo = $this->oApp->getRow($ProductPackInfo['AppId']);
							$Comment = json_decode($AppInfo['comment'],true);
							$ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId]['name'] = $Comment['coin_name'];
						}
					}
					$ProductList[$Type]['detail'][$ProductId] = $ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId]['name']."*".$Count."个";
				}
				$TypeList[$Type] = implode(",",$ProductList[$Type]['detail']);							
			}
			$ProductPackInfo['ProductListText'] = implode(",",$TypeList);		
		}
		else
		{
			$ProductPackInfo['ProductListText'] = "无道具";		
		}
		$AppName = $permitted_app[$ProductPackInfo['AppId']]['name'];
		$page_form_action = $sign;
	 	//调取模板
		include $this->tpl('Config_Product_Pack_Asign');
	}
	public function asignCodeAction()
	{
		set_time_limit(0);
        //检查当前页面权限
		$sign = '?ctl=config/product/pack&ac=gen.pack.code.log';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_UPDATE);

		//页面输入变量
		$bind=$this->request->from('GenId','UserName');


		//用户名是否为空
		if (!$bind['GenId'])
		{
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		} 
		//用户名是否为空
		if (empty($bind['UserName']))
		{
			$response = array('errno' => 3);
			echo json_encode($response);
			return false;
		}
		$GenInfo = $this->oProductPack->GetGenPackCodeLogById($bind['GenId']);
		if($GenInfo['needBind']!=1)
		{
			$response = array('errno' => 4);
			echo json_encode($response);
			return false;
		}				                       
        $total_log = 0;
        
        $List = explode(",",$bind['UserName']);
        foreach($List as $k=>$UserName)
        {		
            if(count($CodeList)==0)
            {
            	$CodeList = $this->oProductPack->getunSignedCode($bind['GenId'],1000);
            	if(count($CodeList)==0)
            	{
				    $response = array('errno' => 0,'success'=>$total_log);
					echo json_encode($response);
					return; 	
            	}	
            }
            $Code = current($CodeList);
            $AsignCode = $this->oProductPack->asignProductPackCode($UserName,$Code);	        
    		if($AsignCode)
            {
                $total_log++;
                unset($CodeList[$Code['ProductPackCode']]);
           	}        	
        }       
        $response = array('errno' => 0,'success'=>$total_log);
		echo json_encode($response);
		return;
	}
	//产品配置列表页面
	public function asignScheduleAction()
	{
		$sign = '?ctl=config/product/pack&ac=asign.schedule';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		//时间范围初始化
		$StartDate= $this->request->StartDate?($this->request->StartDate):date("Y-m-01",time());
		$EndDate = $this->request->EndDate?($this->request->EndDate):date("Y-m-d",time());

		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$AsignScheduleArr = $this->oProductPack->getAllAsignSchedule($StartDate,$EndDate);
		if($AsignScheduleArr)
		{
			foreach($AsignScheduleArr as $key => $AsignSchedule)
			{
				$GenInfo = $this->oProductPack->GetGenPackCodeLogById($AsignSchedule['GenId']);
				if(!isset($ProductPackArr[$GenInfo['ProductPackId']]))
				{
					$ProductPackArr[$GenInfo['ProductPackId']] = $this->oProductPack->getRow($GenInfo['ProductPackId']);	
				}
				$AsignScheduleArr[$key]['PackName'] = $ProductPackArr[$GenInfo['ProductPackId']]['name'];
				if(!isset($ManagerArr[$GenInfo['ManagerId']]))
				{
					$ManagerArr[$GenInfo['ManagerId']] = $this->manager->getRow($GenInfo['ManagerId']);	
				}
				$AsignScheduleArr[$key]['ManagerName'] = $ManagerArr[$GenInfo['ManagerId']]['name'];
			}
		}
		include $this->tpl('Config_Product_Pack_Schedule');
	}
	public function addScheduleAction()
	{
		$sign = '?ctl=config/product/pack&ac=asign.schedule';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_INSERT);			
		include $this->tpl('Config_Product_Pack_AddSchedule');
	}
	public function insertScheduleAction()
	{
		$sign = '?ctl=config/product/pack&ac=asign.schedule';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_UPDATE);

		//检查当前页面权限
		$oUpload = new Base_Upload("user_list");	
		$upload = $oUpload->upload('asign');
		$res[1] = $upload->resultArr;
		$path = $res[1][1];
		
		$bind = $this->request->from('GenId','Date');
		$bind['FileName'] = $path['path'];
		$bind['ManagerId'] = $this->manager->id;
		
		if(empty($bind['FileName']))
		{
			echo json_encode(array('errno' => 1));
			return false;
		}
		$GenInfo = $this->oProductPack->GetGenPackCodeLogById($bind['GenId']);
		if(!$GenInfo['GenId'])
		{
			echo json_encode(array('errno' => 2));
			return false;
		}
		if($bind['Date']<date('Y-m-d',time()))
		{
			echo json_encode(array('errno' => 3));
			return false;
		}
		$res = $this->oProductPack->insertSchedule($bind);
		$response = $res ? array('errno' => 0) : array('errno' => 9);				
		echo json_encode($response);
	}
	public function deleteScheduleAction()
	{
		//检查权限
		$sign = '?ctl=config/product/pack&ac=asign.schedule';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$ScheduleId = intval($this->request->ScheduleId);
		$this->oProductPack->deleteSchedule($ScheduleId);
		$this->response->goBack();
	} 
}

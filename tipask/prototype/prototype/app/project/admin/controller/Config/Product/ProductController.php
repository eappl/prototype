<?php
/**
 * 产品管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: ProductController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Product_ProductController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/product/product';
	/**
	 * Product对象
	 * @var object
	 */
	protected $oProduct;
	protected $oProductType;
	protected $oApp;
	protected $oArea;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oProduct = new Config_Product_Product();
		$this->oProductType = new Config_Product_Type();
		$this->oApp = new Config_App();

		
		$this->AppList = $this->oApp->getAll();

	}
	//产品配置列表页面
	public function indexAction()
	{
		$AppList = $this->AppList;
		$AppId = $this->request->AppId;
		$ProductTypeId = $this->request->ProductTypeId;

		$ProductTypeList = $this->oProductType->getAll($AppId );
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$ProductArr = $this->oProduct->getAll($AppId,$ProductTypeId);
		if($ProductArr)
		{
			foreach($ProductArr as $AppId => $AppData)
			{
				foreach($AppData as $ProductId => $ProductData)
				{
					$ProductArr[$AppId][$ProductId]['AppName'] = $AppList[$ProductData['AppId']]['name'];	
					$ProductArr[$AppId][$ProductId]['ProductTypeName'] = $ProductTypeList[$ProductData['AppId']][$ProductData['ProductTypeId']]['name'];	
				}
			}
		}
		include $this->tpl('Config_Product_list');
	}
	//添加产品填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Product_add');
	}
	
	//添加新产品
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('ProductId','name','AppId','ProductPrice','ProductTypeId');

		if($bind['ProductId']<=0)
		{
			$response = array('errno' => 3);
		}
		if($bind['ProductTypeId']<0)
		{
			$response = array('errno' => 5);
		}
		elseif($bind['AppId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}
		elseif($bind['ProductPrice']==0)
		{
			$response = array('errno' => 4);
		}
		else
		{	
			$res = $this->oProduct->insert($bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId'],'ProductTypeId'=>$bind['ProductTypeId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改产品信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$ProductId = $this->request->ProductId;
		$AppId = $this->request->AppId;
		$Product = $this->oProduct->getRow($ProductId,$AppId,'*');
		$AppList = $this->AppList;
		$ProductTypeList = $this->oProductType->getAll($Product['AppId']);
		include $this->tpl('Config_Product_modify');
	}
	
	//更新产品信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('ProductId','name','AppId','ProductPrice','ProductTypeId');

		if($bind['ProductId']<=0)
		{
			$response = array('errno' => 3);
		}
		if($bind['ProductTypeId']<0)
		{
			$response = array('errno' => 5);
		}
		elseif($bind['AppId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}
		elseif($bind['ProductPrice']==0)
		{
			$response = array('errno' => 4);
		}		
		else
		{	
			$res = $this->oProduct->update($bind['ProductId'],$this->request->oldAppId, $bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId'],'ProductTypeId'=>$bind['ProductTypeId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除产品
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$ProductId = intval($this->request->ProductId);
		$AppId = intval($this->request->AppId);
		$this->oProduct->delete($ProductId,$AppId);
		$this->response->goBack();
	}
    
    public function getProductAction()
	{
		$AppId = intval($this->request->AppId)?intval($this->request->AppId):0;
        $ProductTypeId = intval($this->request->ProductTypeId)?intval($this->request->ProductTypeId):0;
		$ProductArr = $this->oProduct->getAll($AppId,$ProductTypeId);
        $selected = intval($this->request->selected)?intval($this->request->selected):0;

		echo "<option value=''>全部</option>";
		if(is_array($ProductArr[$AppId]))
		{
			foreach ($ProductArr[$AppId] as $ProductId => $data)
			{					
                    if($ProductId == $selected){
					   echo "<option selected value='{$ProductId}'>{$data['name']}</option>";
                    }else{
                       echo "<option value='{$ProductId}'>{$data['name']}</option>";
                    }

			}
		}
	}
	public function productQueueSendDetailAction()
	{
		$oArea = new Config_Area();
		$oPartnerApp = new Config_Partner_App();
		$oServer = new Config_Server();
		$oPermission = new Config_Permission();
		$oUser = new Lm_User();

		$ProductSendTypeArr = $this->config->ProductSendTypeArr;
		$ProductTypeArr = $this->config->ProductTypeArr;

		//获取用户可以查看的游戏列表
		$permitted_app = $oPermission->getApp($this->manager->data_groups,'AppId,name');
		$AreaList = $oArea->getAll();

		set_time_limit(0);
		$page = intval(max($this->request->page,1));

		//检查当前页面权限
		$sign = '?ctl=config/product/product&ac=product.queue.send.detail';
		$this->manager->checkMenuPermission($sign, Widget_Manager::MENU_PURVIEW_SELECT);

		//页面输入变量
		$AppId = intval($this->request->AppId);
		$PartnerId = intval($this->request->PartnerId);
		$PassageId = intval($this->request->PassageId);
		$AreaId = intval($this->request->AreaId)?intval($this->request->AreaId):0;

		$UserName = $this->request->UserName;
		$app_type = intval($this->request->app_type);
		$partner_type = intval($this->request->partner_type);
		$is_abroad = intval($this->request->is_abroad)?intval($this->request->is_abroad):0;		//页面输入变量
		
		$ProductSendType = $this->request->ProductSendType;	
		$ProductType = $this->request->ProductType;	
		
		//是否导出当前页面表格
		$export = $this->request->export?intval($this->request->export):0;
		$pagesize = $export?0:20;

		//初始化图表配置
		$Input = array(
		'UserName'=>urlencode($UserName),
		'AppId'=>$AppId,
		'PartnerId'=>$PartnerId,
		'ServerId'=>$ServerId,
		'ProductSendType'=>$ProductSendType,
		'export'=>1,);
    	$export_var = "<a href =".(Base_Common::getUrl('','config/product/product','product.queue.send.detail',$Input))."><导出表格></a>";
    
		//初始化合作商列表
		$permitted_partner = array();
		//获取当前地区列表
		$AreaList = $oArea->getAbroad($is_abroad,$AreaList);
		//生成允许的地区id数组
		if($app_type>0)
		{
			//筛选是否平台产品
			$permitted_app = $this->oApp->getApp($app_type,$permitted_app);
		}
		if($AppId>0)
		{
		 	//获取可查看的权限总表
			$permitted_partner = $oPermission->getPartner($this->manager->data_groups,$AppId,'PartnerId,name,AreaId');
			//根据合作方式筛选
			$permitted_partner = $oPartnerApp->getPermittedPartnerByPartnerType($partner_type,$permitted_partner);
			//根据所在地区筛选
			$permitted_partner = $oPartnerApp->getPermittedPartnerByPartnerArea($AreaList,$permitted_partner);

			if($PartnerId>0)
			{
				//获取服务器列表
				$permitted_server = $oPermission->getServer($this->manager->data_groups,$AppId,$PartnerId,'ServerId,name');					
			}
		}
		
		if($UserName)
		{
			$UserInfo = $oUser->getUserByName($UserName);
			if($UserInfo['UserId'])
			{
				$UserId = $UserInfo['UserId'];
			}
			else
			{
			 	$UserId = -1;
			}
		}
		else
		{
		 	$UserId = 0;
		}
		$ProductSendQueueDetailArr = $this->oProduct->getProductSendQueueDetail($UserId,$ProductSendType,$ProductType,$ServerId,($page-1)*$pagesize,$pagesize);
	
		$UserInfoList = array();
		if(is_array($ProductSendQueueDetailArr['ProductSendQueueDetail']))
		{
			foreach($ProductSendQueueDetailArr['ProductSendQueueDetail'] as $key => $value)
			{
				if($value['UserId'])
				{
					if(!isset($UserInfoList[$value['UserId']]))
					{
						$UserInfo = $oUser->getUserById($value['UserId']);
						$UserInfoList[$value['UserId']] = $UserInfo;	
					}
				}
				if(!isset($ServerInfo[$value['ServerId']]))
			  	{
			  		$ServerInfo[$value['ServerId']] = $oServer->getRow($value['ServerId']);	
			  	}
			  	$Comment = json_decode($value['Comment'],true);
				$ProductSendQueueDetailArr['ProductSendQueueDetail'][$key]['UserName'] = $value['UserId']?$UserInfoList[$value['UserId']]['UserName']:"未指定";	
				$ProductSendQueueDetailArr['ProductSendQueueDetail'][$key]['ProductSendTypeName'] = $ProductSendTypeArr[$value['SendType']];
				$ProductSendQueueDetailArr['ProductSendQueueDetail'][$key]['ProductTypeName'] = $ProductTypeArr[$value['ProductType']];
		  		$ProductSendQueueDetailArr['ProductSendQueueDetail'][$key]['ServerName'] = $ServerInfo[$value['ServerId']]['name'];			
			}
		}
    	$page_title = "道具发送队列详情";
		$page_form_action = $sign;
	 	//调取模板 
		include $this->tpl('Config_Product_ProductSendQueueDetail');
	}
}

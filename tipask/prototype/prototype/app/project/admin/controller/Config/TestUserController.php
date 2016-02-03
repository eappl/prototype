<?php
/**
 * 测试用户管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: TestUserController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_TestUserController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/test.user';
	/**
	 * TestUser对象
	 * @var object
	 */
	protected $oTestUser;
	protected $oPartner;
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
		//声明所需调用的类库
		$this->oTestUser = new Config_TestUser();
		$this->oPermission = new Config_Permission();
		$this->oPartnerApp = new Config_Partner_App();
		$this->oArea = new Config_Area();
		$this->oApp = new Config_App();

		//获取用户可以查看的游戏列表
		$this->permitted_app = $this->oPermission->getApp($this->manager->data_groups,'AppId,name');
		//预处理测试用户信息
		$this->AreaList = $this->oArea->getAll();

	}
	//测试用户配置列表页面
	public function indexAction()
	{
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		//页面输入变量
		$AppId = intval($this->request->AppId);
		$PartnerId = intval($this->request->PartnerId);
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
		
		$AreaList = $this->AreaList;
		//获取当前测试用户列表
		//生成允许的测试用户id数组
		if($AppId>0)
		{
		 	//获取可查看的权限总表
			$permitted_partner = $this->oPermission->getPartner($this->manager->data_groups,$AppId,'PartnerId,name,AreaId');



			if($PartnerId>0)
			{
				//获取服务器列表
				$permitted_server = $this->oPermission->getServer($this->manager->data_groups,$AppId,$PartnerId,'ServerId,name');					
			}
		}
		//获取用于查询的权限sql语句
		$oWherePartnerPermission = $this->oPermission->getWherePermittedPartner($this->manager->data_groups,$AppId,$PartnerId,$app_type,$partner_type,$AreaList,$AreaId,$is_abroad,'');
		$totalUser = $this->oTestUser->getTestUser($oWherePartnerPermission,$AppId);
		foreach($totalUser as $key => $detail)
		{
				if($detail['AppId']==0)
				{
						$totalUser[$key]['app_name'] = "全部";
				}
				else
				{
					$totalUser[$key]['app_name'] = $permitted_app[intval($detail['AppId'])]['name'];
				}
				if($detail['PartnerId']==0)
				{
						$totalUser[$key]['partner_name'] = "全部";
				}
				else
				{
					//如果配置数组不存在当前合作商则重新获取上层游戏的合作商列表
					if(!isset($permission_array[$detail['AppId']][$detail['PartnerId']]))
					{
						$p = $this->oPermission->getPartner($this->manager->data_groups,$detail['AppId'],'PartnerId,name,AreaId');
						$permission_array[$detail['AppId']] = $this->oPartnerApp->getPermittedPartnerByPartnerArea($AreaList,$p);
					}
					//如果配置数组不存在当前服务器则重新获取上层合作商的服务器列表
					$totalUser[$key]['partner_name'] = $permission_array[$detail['AppId']][$detail['PartnerId']]['name'];						
				}

		}
		$page_title = "测试用户管理";
		$page_form_action = $this->sign;
		
		include $this->tpl('Config_TestUser_list');
	}
	//添加测试用户填写配置页面
	public function addAction()
	{
		$AppId = intval($this->request->AppId);
		$PartnerId = intval($this->request->PartnerId);
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
		
		$AreaList = $this->AreaList;
		//获取当前测试用户列表
		//生成允许的测试用户id数组
		if($AppId>0)
		{
		 	//获取可查看的权限总表
			$permitted_partner = $this->oPermission->getPartner($this->manager->data_groups,$AppId,'PartnerId,name,AreaId');



			if($PartnerId>0)
			{
				//获取服务器列表
				$permitted_server = $this->oPermission->getServer($this->manager->data_groups,$AppId,$PartnerId,'ServerId,name');					
			}
		}
		include $this->tpl('Config_TestUser_add');
	}
	
	//添加新测试用户
	public function insertAction()
	{
		$username = trim($this->request->username);
		$AppId = trim($this->request->app);
		$PartnerId = trim($this->request->partner);	
			
		$bind = array('username'=>$username,'AppId'=>$AppId ,'PartnerId'=>$PartnerId);
		$res = $this->oTestUser->insert($bind);
		$response = $res ? array('errno' => 0) : array('errno' => 9);
		echo json_encode($response);
		return true;
	}
	
	
	//删除测试用户
	public function deleteAction()
	{
		$username = trim($this->request->username);
		$AppId = trim($this->request->AppId);
		$PartnerId = trim($this->request->PartnerId);
		$bind= array($username,$AppId,$PartnerId);
		$this->oTestUser->delete($bind);
		$this->response->goBack();
	}
}

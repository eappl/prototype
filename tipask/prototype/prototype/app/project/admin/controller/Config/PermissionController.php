<?php
/**
 * 数据权限控制
 * @author Chen <cxd032404@hotmail.com>
 * $Id: PermissionController.php 15195 2014-07-23 07:18:26Z 334746 $
 */
class Config_PermissionController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/permission';
	/**
	 * App对象
	 * @var object
	 */
	protected $oApp;
	protected $oPartnerApp;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oApp = new Config_App();
		$this->oPartnerApp = new Config_Partner_App();
		$this->oArea = new Config_Area();
		$this->oPermission = new Config_Permission();

	}

	/**
	 * 获取当前用户的允许游戏列表
	 * @params partner_type  0:全部/1:平台开发/2:代理游戏	 
	 * @return 下拉列表
	 */
	public function getAppAction()
	{
		$app_type= intval($this->request->app_type);
		$fields = 'AppId,name';
		$permitted_app = $this->oPermission->getApp($this->manager->data_groups,$fields);
		$permitted_app = $this->oApp->getApp($app_type,$permitted_app);
		echo "<option value=0>全部</option>";
		if(count($permitted_app))
		{
			foreach($permitted_app as  $AppId => $app_data)
			{
				echo "<option value='{$AppId}'>{$app_data['name']}</option>";
			}
		}
	}
	/**
	 * 获取当前用户的允许合作商列表
	 * @params partner_type 合作方式 1:官服/2:专区
	 * @params AppId 游戏
	 * @params PartnerId 合作商
	 * @params AreaId 所在地区	 
	 * @return 下拉列表
	 */
	public function getPartnerAction()
	{
		$AreaList = array();

		$partner_type = intval($this->request->partner_type);
		$AreaId = intval($this->request->AreaId);
		$AppId = intval($this->request->AppId);
		$is_abroad = intval($this->request->is_abroad);
		$AreaId = intval($this->request->AreaId);
		$AreaList = $this->oArea->getAll();
		$AreaList = $this->oArea->getAbroad($is_abroad,$AreaList);
		$AreaList = $this->oArea->getArea($AreaId,$AreaList);
				
		$fields = 'PartnerId,name,AreaId';
		$permitted_partner = $this->oPermission->getPartner($this->manager->data_groups,$AppId,$fields);
		$permitted_partner = $this->oPartnerApp->getPermittedPartnerByPartnerType($partner_type,$permitted_partner);
		$permitted_partner = $this->oPartnerApp->getPermittedPartnerByPartnerArea($AreaList,$permitted_partner);
		echo "<option value=0>全部</option>";
		if(count($permitted_partner))
		{
			foreach($permitted_partner as  $PartnerId => $partner_data)
			{
				echo "<option value='{$PartnerId}'>{$partner_data['name']}</option>";
			}
		}
	}
	/**
	 * 获取当前用户的允许的服务器列表
	 * @params partner_type 合作方式 1:官服/2:专区
	 * @params AppId 游戏
	 * @params PartnerId 合作商
	 * @return 下拉列表
	 */
	public function getServerAction()
	{
		$partner_type = intval($this->request->partner_type);
		$AppId = intval($this->request->AppId);
		$PartnerId = intval($this->request->PartnerId);
		$fields = 'ServerId,name';
		$permitted_server = $this->oPermission->getServer($this->manager->data_groups,$AppId,$PartnerId,$fields);
		echo "<option value=0>全部</option>";
		if(count($permitted_server))
		{
			foreach($permitted_server as  $ServerId => $server_data)
			{
				echo "<option value='{$ServerId}'>{$server_data['name']}</option>";
			}
		}
	}
	/**
	 * 获取当前用户组的权限配置页面
	 * @params data_groups 用户组id
	 * @return 下拉列表
	 */
	public function listPartnerPermissionAction()
	{
		$group_id = intval($this->request->group_id);
		//echo $group_id."<>";
		$totalPermission = $this->oPermission->listParterPermission($group_id);
		$totalDefaultPermission = $this->oPermission->listPartertotalDefaultPermission($group_id);
		
		include $this->tpl('Config_Permission_modify');
	}
	/**
	 * 获取当前用户组的权限配置页面2
	 * @params data_groups 用户组id
	 * @return 下拉列表
	 */
	public function listPartnerPermission2Action()
	{
		$group_id = intval($this->request->group_id)?intval($this->request->group_id):3;
		//echo $group_id."<br/>";
		$totalPermission = $this->oPermission->AllParterPermissionList('PartnerId,AppId,name,AreaId',$group_id);		
		include $this->tpl('Config_Permission_modify2');
	}	
	/**
	 * 更新用户组的数据权限
	 * @params group_id	权限组
	 * @params $total_default_permission 全局默认权限
	 * @params $default_permission  游戏默认权限
	 * @params $PartnerIds  基本权限
	 * @return 回前一页面
	 */
	public function permissionModifyAction()
	{
		$group_id = abs(intval($this->request->group_id));
		$PartnerIds = $this->request->PartnerIds;
		$default_permission = $this->request->default_permission;
		$total_default_permission = $this->request->total_default_permission;
		
		$this->oPermission->modifyParterPermission($group_id,$total_default_permission,$default_permission,$PartnerIds);
		$this->response->goBack();
	}
	
	/*
	 *selena 数据权限更新
	 */
	public function permissionModify2Action()
	{				
		$group_id = abs(intval($this->request->group_id));
		$this->oPermission->DelPermissionByGroup($group_id);
		/*$PartnerIds = $this->request->PartnerIds;
		$default_permission = $this->request->default_permission;
		$total_default_permission = $this->request->total_default_permission;*/
		$PartnerIds = $this->request->PartnerIds;		
		foreach($PartnerIds as $k=>$v)
		{
			$listarr = $this->arr_process($v);
			$res = $this->oPermission->InsArrPermission($listarr["AppId"],$listarr["PartnerId"],$listarr["AreaId"],$listarr["partner_type"],$group_id);			
			
		}
		$this->response->goBack();
	}
	
	/*格式化数组
	 *@author selena
	 */
	public function arr_process($str)
	{
		$arr = array();
		$text_arr = explode("_",$str);		
		foreach($text_arr as $k=>$v)
		{
			$text_arr_2 = explode("|",$v);
			//print_r($text_arr_2);
			if($text_arr_2[0]=="App")
			{
				$text_arr_2[0] = "AppId";
				$arr[$text_arr_2[0]] = $text_arr_2[1];				
			}
			if($text_arr_2[0]=="Area")
			{
				$text_arr_2[0] = "AreaId";
				$arr[$text_arr_2[0]] = $text_arr_2[1];	
			}
			if($text_arr_2[0]=="Partner")
			{
				$text_arr_2[0] = "PartnerId";
				$arr[$text_arr_2[0]] = $text_arr_2[1];	
			}
			if($text_arr_2[0]=="Type")
			{
				$text_arr_2[0] = "partner_type";
				$arr[$text_arr_2[0]] = $text_arr_2[1];	
			}
		}
		return $arr;
	}
}

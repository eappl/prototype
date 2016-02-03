<?php
/**
 * 广告商管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: TypeController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Video_TypeController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/video/type';
	/**
	 * VideoType对象
	 * @var object
	 */
	protected $oVideoType;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oVideoType = new Config_Video_Type();

	}
	//广告商配置列表页面
	public function indexAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$VideoTypeArr = $this->oVideoType->getAll();
		include $this->tpl('Config_Video_Type_list');
	}
	//添加广告商填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		include $this->tpl('Config_Video_Type_add');
	}
	
	//添加新广告商
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('VideoTypeName','VideoTypeId');

		if($bind['VideoTypeName']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oVideoType->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改广告商信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$VideoTypeId = intval($this->request->VideoTypeId);
		$VideoType = $this->oVideoType->getRow($VideoTypeId,'*');
		include $this->tpl('Config_Video_Type_modify');
	}	
	//更新广告商信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('VideoTypeId','VideoTypeName');

		
		if($bind['VideoTypeId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['VideoTypeName']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oVideoType->update($bind['VideoTypeId'], $bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}	
	//删除广告商
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$VideoTypeId = intval($this->request->VideoTypeId);
		$this->oVideoType->delete($VideoTypeId);
		$this->response->goBack();
	}
}

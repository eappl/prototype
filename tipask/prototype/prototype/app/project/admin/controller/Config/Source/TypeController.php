<?php
/**
 * 广告商管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: TypeController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Source_TypeController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/source/type';
	/**
	 * SourceType对象
	 * @var object
	 */
	protected $oSourceType;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oSourceType = new Config_Source_Type();

	}
	//广告商配置列表页面
	public function indexAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$SourceTypeArr = $this->oSourceType->getAll();
		include $this->tpl('Config_Source_Type_list');
	}
	//添加广告商填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		include $this->tpl('Config_Source_Type_add');
	}
	
	//添加新广告商
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','SourceTypeId');


		if($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oSourceType->insert($bind);
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
		$SourceTypeId = intval($this->request->SourceTypeId);
		$SourceType = $this->oSourceType->getRow($SourceTypeId,'*');
		include $this->tpl('Config_Source_Type_modify');
	}
	
	//更新广告商信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('SourceTypeId','name');

		
		if($bind['SourceTypeId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oSourceType->update($bind['SourceTypeId'], $bind);
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
		$SourceTypeId = intval($this->request->SourceTypeId);
		$this->oSourceType->delete($SourceTypeId);
		$this->response->goBack();
	}
}

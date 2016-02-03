<?php
/**
 * FAQ分类管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: TypeController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Faq_TypeController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/faq/type';
	/**
	 * FaqType对象
	 * @var object
	 */
	protected $oFaqType;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oFaqType = new Config_Faq_Type();

	}
	//FAQ分类配置列表页面
	public function indexAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$FaqTypeArr = $this->oFaqType->getAll();
		include $this->tpl('Config_Faq_Type_list');
	}
	//添加FAQ分类填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		include $this->tpl('Config_Faq_Type_add');
	}
	
	//添加新FAQ分类
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','FaqTypeId');


		if($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oFaqType->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改FAQ分类信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$FaqTypeId = intval($this->request->FaqTypeId);
		$FaqType = $this->oFaqType->getRow($FaqTypeId,'*');
		include $this->tpl('Config_Faq_Type_modify');
	}
	
	//更新FAQ分类信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('FaqTypeId','name');

		
		if($bind['FaqTypeId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oFaqType->update($bind['FaqTypeId'], $bind);

			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除FAQ分类
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$FaqTypeId = intval($this->request->FaqTypeId);
		$this->oFaqType->delete($FaqTypeId);
		$this->response->goBack();
	}
}

<?php
/**
 * 活动管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: ActionController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Source_ActionController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/source/action';
	/**
	 * Source对象
	 * @var object
	 */
	protected $oSourceAction;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oSourceAction = new Config_Source_Action();
	}
	//活动配置列表页面
	public function indexAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$SourceActionArr = $this->oSourceAction->getAll();
		include $this->tpl('Config_Source_Action_list');
	}
	//添加活动填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		include $this->tpl('Config_Source_Action_add');
	}
	
	//添加新活动
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','SourceActionId');

		if($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oSourceAction->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改活动信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$SourceActionId = $this->request->SourceActionId;
		$SourceAction = $this->oSourceAction->getRow($SourceActionId,'*');
		include $this->tpl('Config_Source_Action_modify');
	}
	
	//更新活动信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('SourceActionId','name');

		
		if($bind['SourceActionId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oSourceAction->update($bind['SourceActionId'], $bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除活动
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$SourceActionId = intval($this->request->SourceActionId);
		$this->oSourceAction->delete($SourceActionId);
		$this->response->goBack();
	}
/**
	 * 获取广告商列表
	 * @params SourceTypeId 广告商分类列表 
	 * @return 下拉列表
	 */
	public function getSourceByTypeAction()
	{
		$SourceTypeId = $this->request->SourceTypeId?abs(intval($this->request->SourceTypeId)):0;
		$SourceArr = $this->oSource->getAll($SourceTypeId);
		echo "<option value=0>全部</option>";
		if($SourceTypeId > 0)
		{
			if(count($SourceArr))
			{
				foreach($SourceArr[$SourceTypeId] as  $SourceId => $SourceData)
				{
					echo "<option value='{$SourceId}'>{$SourceData['name']}</option>";
				}
			}
		}
		else
		{
			
		}
	}
}

<?php
/**
 * 调研管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: ResearchController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_ResearchController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/research';
	/**
	 * Research对象
	 * @var object
	 */
	protected $oResearch;
	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oResearch = new Config_Research();


	}
	//调研配置列表页面
	public function indexAction()
	{
		$ResearchArr = $this->oResearch->getAll();
		include $this->tpl('Config_Research_list');
	}
	//添加调研填写配置页面
	public function addAction()
	{
		include $this->tpl('Config_Research_add');
	}
	
	//添加新调研
	public function insertAction()
	{
		$bind=$this->request->from('ResearchName','ResearchContent');


		if($bind['ResearchName']=='')
		{
			$response = array('errno' => 3);
		}	
		else
		{	
			$res = $this->oResearch->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改调研信息页面
	public function modifyAction()
	{
		$ResearchId = $this->request->ResearchId;
		$Research = $this->oResearch->getRow($ResearchId);
		include $this->tpl('Config_Research_modify');
	}
	
	//更新调研信息
	public function updateAction()
	{
		$bind=$this->request->from('ResearchId','ResearchName','ResearchContent');


		if($bind['ResearchId']==0)
		{
			$response = array('errno' => 2);
		}
		elseif($bind['ResearchName']=='')
		{
			$response = array('errno' => 3);
		}	
		else
		{	
			$res = $this->oResearch->update($bind['ResearchId'], $bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除调研
	public function deleteAction()
	{
		$ResearchId = intval($this->request->ResearchId);
		$this->oResearch->delete($ResearchId);
		$this->response->goBack();
	}
}

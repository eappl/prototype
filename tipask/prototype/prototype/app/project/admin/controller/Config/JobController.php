<?php
/**
 * 职业管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: JobController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_JobController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/job';
	/**
	 * Job对象
	 * @var object
	 */
	protected $oJob;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oJob = new Config_Job();
		$this->oApp = new Config_App();
	}
	//职业配置列表页面
	public function indexAction()
	{
		$AppList = $this->oApp->getAll('name,AppId');
		$AppId = $this->request->AppId?abs(intval($this->request->AppId)):254;
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$JobArr = $this->oJob->getAll($AppId);
		if($JobArr)
		{
			foreach($JobArr as $AppId => $AppData)
			{
				foreach($AppData as $JobId => $JobData)
				$JobArr[$AppId][$JobId]['AppName'] = $this->oApp->getOne($AppId,'name');	
			}
		}
		include $this->tpl('Config_Job_list');
	}
	//添加职业填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Job_add');
	}
	
	//添加新职业
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','AppId','JobId');


		if($bind['JobId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['AppId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oJob->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改职业信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$JobId = $this->request->JobId;
		$AppId = $this->request->AppId;
		$Job = $this->oJob->getRow($JobId,$AppId,'*');
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Job_modify');
	}
	
	//更新职业信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('JobId','name','AppId');

		
		if($bind['JobId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['AppId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oJob->update($bind['JobId'],$this->request->oldAppId, $bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除职业
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$JobId = intval($this->request->JobId);
		$AppId = intval($this->request->AppId);
		$this->oJob->delete($JobId,$AppId);
		$this->response->goBack();
	}
	
	public function getJobAction()
	{
		$AppId = intval($this->request->AppId)?intval($this->request->AppId):0;
		$JobArr = $this->oJob->getAll($AppId);

		echo "<option value=''>--全部--</option>";
		if(is_array($JobArr[$AppId]))
		{
			foreach ($JobArr[$AppId] as $job_id => $job)
			{
					echo "<option value='{$job_id}'>{$job['name']}</option>";

			}
		}
	}
}

<?php
/**
 * 任务管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: QuestController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_QuestController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/quest';
	/**
	 * Quest对象
	 * @var object
	 */
	protected $oQuest;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oQuest = new Config_Quest();
		$this->oApp = new Config_App();

	}
	//任务配置列表页面
	public function indexAction()
	{
		$AppList = $this->oApp->getAll('name,AppId');
		$AppId = $this->request->AppId?abs(intval($this->request->AppId)):101;
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$QuestArr = $this->oQuest->getAll($AppId);
		if($QuestArr)
		{
			foreach($QuestArr as $App => $AppData)
			{
				foreach($AppData as $QuestId => $QuestData)
				$QuestArr[$App][$QuestId]['AppName'] = $AppList[$App]['name'];	
			}
		}
		include $this->tpl('Config_Quest_list');
	}
	//添加任务填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Quest_add');
	}
	
	//添加新任务
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','AppId','QuestId');


		if($bind['QuestId']==0)
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
			$res = $this->oQuest->insert($bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改任务信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$QuestId = $this->request->QuestId;
		$AppId = $this->request->AppId;
		$Quest = $this->oQuest->getRow($QuestId,$AppId,'*');
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Quest_modify');
	}
	
	//更新任务信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('QuestId','name','AppId');

		
		if($bind['QuestId']==0)
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
			$res = $this->oQuest->update($bind['QuestId'],$this->request->oldAppId, $bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除任务
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$QuestId = intval($this->request->QuestId);
		$AppId = intval($this->request->AppId);
		$this->oQuest->delete($QuestId,$AppId);
		$this->response->goBack();
	}
	public function getQuestAction()
	{
		$AppId = intval($this->request->AppId)?intval($this->request->AppId):0;
		$QuestArr = $this->oQuest->getAll($AppId);

		echo "<option value=''>全部</option>";
		if(is_array($QuestArr[$AppId]))
		{
			foreach ($QuestArr[$AppId] as $quest_id => $quest)
			{
					echo "<option value='{$quest_id}'>{$quest['name']}</option>";

			}
		}
	}
}

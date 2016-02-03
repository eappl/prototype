<?php
/**
 * 副本管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: InstmapController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_InstmapController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/instmap';
	/**
	 * InstMap对象
	 * @var object
	 */
	protected $oInstMap;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oInstMap = new Config_Instmap();
		$this->oApp = new Config_App();

	}
	//副本配置列表页面
	public function indexAction()
	{
		$AppList = $this->oApp->getAll('name,AppId');
		$AppId = $this->request->AppId?abs(intval($this->request->AppId)):101;
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);		
		$InstMapArr = $this->oInstMap->getAll($AppId);
		if($InstMapArr)
		{
			foreach($InstMapArr as $App => $AppData)
			{
				foreach($AppData as $InstMapId => $InstMapData)
				$InstMapArr[$App][$InstMapId]['AppName'] = $AppList[$App]['name'];	
			}
		}
		include $this->tpl('Config_InstMap_list');
	}
	//添加副本填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_InstMap_add');
	}
	
	//添加新副本
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','AppId','InstMapId');


		if($bind['InstMapId']==0)
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
			$res = $this->oInstMap->insert($bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改副本信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$InstMapId = $this->request->InstMapId;
		$AppId = $this->request->AppId;
		$InstMap = $this->oInstMap->getRow($InstMapId,$AppId);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_InstMap_modify');
	}
	
	//更新副本信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('InstMapId','name','AppId');

		
		if($bind['InstMapId']==0)
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
			$res = $this->oInstMap->update($bind['InstMapId'],$this->request->oldAppId, $bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除副本
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$InstMapId = intval($this->request->InstMapId);
		$AppId = intval($this->request->AppId);
		$this->oInstMap->delete($InstMapId,$AppId);
		$this->response->goBack();
	}
    
    public function getInstmapAction()
	{
		$AppId = intval($this->request->AppId);
		$Instmap = new Config_Instmap;
        $InstmapList = $Instmap->getAll(empty($AppId)?0:$AppId);
		echo "<option value=0>全部</option>";
       
		if(count($InstmapList))
		{
			foreach($InstmapList[$AppId] as $key => $val)
			{
				echo "<option value='{$val['InstMapId']}'>{$val['name']}</option>";
			}
		}
	}
}

<?php
/**
 * 游戏内货币类型管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: MoneyTypeController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_MoneyTypeController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/money.type';
	/**
	 * MoneyType对象
	 * @var object
	 */
	protected $oMoneyType;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oMoneyType = new Config_MoneyType();
		$this->oApp = new Config_App();

	}
	//游戏内货币类型配置列表页面
	public function indexAction()
	{
		$AppList = $this->oApp->getAll('name,AppId');
		$AppId = $this->request->AppId;
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$MoneyTypeArr = $this->oMoneyType->getAll($AppId);
		if($MoneyTypeArr)
		{
			foreach($MoneyTypeArr as $AppId => $AppData)
			{
				foreach($AppData as $MoneyTypeId => $MoneyTypeData)
				$MoneyTypeArr[$AppId][$MoneyTypeId]['AppName'] = $this->oApp->getOne($AppId,'name');	
			}
		}
		include $this->tpl('Config_MoneyType_list');
	}
	//添加游戏内货币类型填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_MoneyType_add');
	}
	
	//添加新游戏内货币类型
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','AppId','MoneyTypeId');


		if($bind['MoneyTypeId']==0)
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
			$res = $this->oMoneyType->insert($bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改游戏内货币类型信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$MoneyTypeId = $this->request->MoneyTypeId;
		$AppId = $this->request->AppId;
		$MoneyType = $this->oMoneyType->getRow($MoneyTypeId,$AppId,'*');
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_MoneyType_modify');
	}
	
	//更新游戏内货币类型信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('MoneyTypeId','name','AppId');

		if($bind['MoneyTypeId']==0)
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
			$res = $this->oMoneyType->update($bind['MoneyTypeId'],$this->request->oldAppId, $bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除游戏内货币类型
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$MoneyTypeId = intval($this->request->MoneyTypeId);
		$AppId = intval($this->request->AppId);
		$this->oMoneyType->delete($MoneyTypeId,$AppId);
		$this->response->goBack();
	}
    
    public function getMoneyTypeAction()
	{
		$AppId = intval($this->request->AppId)?intval($this->request->AppId):0;
		$MoneyTypeArr = $this->oMoneyType->getAll($AppId);

		echo "<option value=''>全部</option>";
		if(is_array($MoneyTypeArr[$AppId]))
		{
			foreach ($MoneyTypeArr[$AppId] as $MoneyTypeId => $data)
			{
					echo "<option value='{$MoneyTypeId}'>{$data['name']}</option>";

			}
		}
	}
}

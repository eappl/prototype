<?php
/**
 * 游戏内货币类型管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: MoneyController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_MoneyController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/money';
	/**
	 * Money对象
	 * @var object
	 */
	protected $oMoney;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oMoney = new Config_Money();
		$this->oApp = new Config_App();

	}
	//游戏内货币类型配置列表页面
	public function indexAction()
	{
		$AppList = $this->oApp->getAll('name,AppId');
		$AppId = $this->request->AppId;
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$MoneyArr = $this->oMoney->getAll($AppId);
		if($MoneyArr)
		{
			foreach($MoneyArr as $AppId => $AppData)
			{
				foreach($AppData as $MoneyId => $MoneyData)
				$MoneyArr[$AppId][$MoneyId]['AppName'] = $this->oApp->getOne($AppId,'name');	
			}
		}
		include $this->tpl('Config_Money_list');
	}
	//添加游戏内货币类型填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Money_add');
	}
	
	//添加新游戏内货币类型
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','AppId','MoneyId');


		if($bind['MoneyId']==0)
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
			$res = $this->oMoney->insert($bind);
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
		$MoneyId = $this->request->MoneyId;
		$AppId = $this->request->AppId;
		$Money = $this->oMoney->getRow($MoneyId,$AppId,'*');
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Money_modify');
	}
	
	//更新游戏内货币类型信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('MoneyId','name','AppId');

		if($bind['MoneyId']==0)
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
			$res = $this->oMoney->update($bind['MoneyId'],$this->request->oldAppId, $bind);
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
		$MoneyId = intval($this->request->MoneyId);
		$AppId = intval($this->request->AppId);
		$this->oMoney->delete($MoneyId,$AppId);
		$this->response->goBack();
	}
    
    public function getMoneyAction()
	{
		$AppId = intval($this->request->AppId)?intval($this->request->AppId):0;
		$MoneyArr = $this->oMoney->getAll($AppId);
        $selected = intval($this->request->selected)?intval($this->request->selected):0;

		echo "<option value=''>全部</option>";
		if(is_array($MoneyArr[$AppId]))
		{
			foreach ($MoneyArr[$AppId] as $MoneyId => $data)
			{					
                    if($MoneyId == $selected){
					   echo "<option selected value='{$MoneyId}'>{$data['name']}</option>";
                    }else{
                       echo "<option value='{$MoneyId}'>{$data['name']}</option>";
                    }
			}
		}
	}
}

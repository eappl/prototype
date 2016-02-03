<?php
/**
 * 皮肤管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: SkinController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_SkinController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/skin';
	/**
	 * Skin对象
	 * @var object
	 */
	protected $oSkin;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oSkin = new Config_Skin();
		$this->oHero = new Config_Hero();
		$this->oApp = new Config_App();
	}
	//皮肤配置列表页面
	public function indexAction()
	{
		$AppList = $this->oApp->getAll('name,AppId');
		$AppId = $this->request->AppId?abs(intval($this->request->AppId)):101;
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$SkinArr = $this->oSkin->getAll($AppId);
		if($SkinArr)
		{
			foreach($SkinArr as $AppId => $AppData)
			{
				foreach($AppData as $SkinId => $SkinData)
				{
					$SkinArr[$AppId][$SkinId]['AppName'] = $this->oApp->getOne($AppId,'name');
					$SkinArr[$AppId][$SkinId]['HeroName'] = $this->oHero->getOne($SkinData['HeroId'],$AppId,'name');
				}
	
			}
		}
		include $this->tpl('Config_Skin_list');
	}
	//添加皮肤填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Skin_add');
	}
	
	//添加新皮肤
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','AppId','SkinId','HeroId');


		if($bind['SkinId']<0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['HeorId']<0)
		{
			$response = array('errno' => 4);
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
			$res = $this->oSkin->insert($bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改皮肤信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$SkinId = $this->request->SkinId;
		$AppId = $this->request->AppId;
		$Skin = $this->oSkin->getRow($SkinId,$AppId,'*');
		$AppList = $this->oApp->getAll('name,AppId');
		$HeroArr = $this->oHero->getAll($AppId);
		include $this->tpl('Config_Skin_modify');
	}
	
	//更新皮肤信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('SkinId','name','AppId','HeroId');

		
		if($bind['SkinId']<0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['HeorId']<0)
		{
			$response = array('errno' => 4);
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
			$res = $this->oSkin->update($bind['SkinId'],$this->request->oldAppId, $bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除皮肤
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$SkinId = intval($this->request->SkinId);
		$AppId = intval($this->request->AppId);
		$this->oSkin->delete($SkinId,$AppId);
		$this->response->goBack();
	}
	
	public function getSkinAction()
	{
		$AppId = intval($this->request->AppId)?intval($this->request->AppId):0;
		$SkinArr = $this->oSkin->getAll($AppId);
        $selected = intval($this->request->selected)?intval($this->request->selected):0;

		echo "<option value='-1'>全部</option>";
		if(is_array($SkinArr[$AppId]))
		{
			foreach ($SkinArr[$AppId] as $skin_id => $skin)
			{
			        if($skin_id == $selected){
			             echo "<option selected value='{$skin_id}'>{$skin['name']}</option>";
			        }else{
			             echo "<option value='{$skin_id}'>{$skin['name']}</option>";
			        } 
					

			}
		}
	}
}

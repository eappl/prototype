<?php
/**
 * 英雄管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: HeroController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_HeroController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/hero';
	/**
	 * Hero对象
	 * @var object
	 */
	protected $oHero;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oHero = new Config_Hero();
		$this->oApp = new Config_App();
	}
	//英雄配置列表页面
	public function indexAction()
	{
		$AppList = $this->oApp->getAll('name,AppId');
		$AppId = $this->request->AppId?abs(intval($this->request->AppId)):101;
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$HeroArr = $this->oHero->getAll($AppId);
		if($HeroArr)
		{
			foreach($HeroArr as $AppId => $AppData)
			{
				foreach($AppData as $HeroId => $HeroData)
				{
					$HeroArr[$AppId][$HeroId]['AppName'] = $this->oApp->getOne($AppId,'name');	
				}
			}
		}
		include $this->tpl('Config_Hero_list');
	}
	//添加英雄填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Hero_add');
	}
	
	//添加新英雄
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','AppId','HeroId');


		if($bind['HeroId']<0)
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
			$res = $this->oHero->insert($bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改英雄信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$HeroId = $this->request->HeroId;
		$AppId = $this->request->AppId;
		$Hero = $this->oHero->getRow($HeroId,$AppId,'*');
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Hero_modify');
	}
	
	//更新英雄信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('HeroId','name','AppId');

		
		if($bind['HeroId']<0)
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
			$res = $this->oHero->update($bind['HeroId'],$this->request->oldAppId, $bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除英雄
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$HeroId = intval($this->request->HeroId);
		$AppId = intval($this->request->AppId);
		$this->oHero->delete($HeroId,$AppId);
		$this->response->goBack();
	}
	
	public function getHeroAction()
	{
		$AppId = intval($this->request->AppId)?intval($this->request->AppId):0;
		$HeroArr = $this->oHero->getAll($AppId);
        $selected = intval($this->request->selected)?intval($this->request->selected):0;

		echo "<option value='-1'>全部</option>";
		if(is_array($HeroArr[$AppId]))
		{
			foreach ($HeroArr[$AppId] as $hero_id => $hero)
			{
			        if($hero_id == $selected){
					   echo "<option selected value='{$hero_id}'>{$hero['name']}</option>";
                    }else{
                       echo "<option value='{$hero_id}'>{$hero['name']}</option>";
                    }

			}
		}
	}
}

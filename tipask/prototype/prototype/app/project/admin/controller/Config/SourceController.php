<?php
/**
 * 广告商管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: SourceController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_SourceController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/source';
	/**
	 * Source对象
	 * @var object
	 */
	protected $oSource;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oSource = new Config_Source();
		$this->oSourceType = new Config_Source_Type();
	}
	//广告商配置列表页面
	public function indexAction()
	{
		$SourceTypeList = $this->oSourceType->getAll('name,SourceTypeId');
		$SourceTypeId = $this->request->SourceTypeId?abs(intval($this->request->SourceTypeId)):0;

		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$SourceArr = $this->oSource->getAll($SourceTypeId);
		if($SourceArr)
		{
			foreach($SourceArr as $SourceId => $SourceData)
			{
				$SourceArr[$SourceId]['SourceTypeName'] = $SourceTypeList[$SourceData['SourceTypeId']]['name'];	
			}
		}
		include $this->tpl('Config_Source_list');
	}
	//添加广告商填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$SourceTypeList = $this->oSourceType->getAll('name,SourceTypeId');
		include $this->tpl('Config_Source_add');
	}
	
	//添加新广告商
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','SourceTypeId');


		if($bind['SourceTypeId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oSource->insert($bind);
			$response = $res ? array('errno' => 0,'SourceTypeId'=>$bind['SourceTypeId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改广告商信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$SourceId = $this->request->SourceId;
		$SourceTypeId = $this->request->SourceTypeId;
		$Source = $this->oSource->getRow($SourceId,'*');
		$SourceTypeList = $this->oSourceType->getAll('name,SourceTypeId');
		include $this->tpl('Config_Source_modify');
	}
	
	//更新广告商信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('SourceId','name','SourceTypeId');

		
		if($bind['SourceId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['SourceTypeId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oSource->update($bind['SourceId'], $bind);
			$response = $res ? array('errno' => 0,'SourceTypeId'=>$bind['SourceTypeId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除广告商
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$SourceId = intval($this->request->SourceId);
		$this->oSource->delete($SourceId);
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
				foreach($SourceArr as  $SourceId => $SourceData)
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

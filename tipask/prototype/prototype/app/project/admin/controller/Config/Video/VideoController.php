<?php
/**
 * 广告商管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: VideoController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Video_VideoController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/video/video';
	/**
	 * VideoType对象
	 * @var object
	 */
	protected $oVideoType;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oVideoType = new Config_Video_Type();
		$this->oVideo = new Config_Video_Video();


	}
	//广告商配置列表页面
	public function indexAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$VideoTypeArr = $this->oVideoType->getAll();
		$VideoTypeId = abs(intval($this->request->VideoTypeId));

		$VideoArr = $this->oVideo->getAll($VideoTypeId);
		foreach($VideoArr as $VideoId => $Video)
		{
			$VideoArr[$VideoId]['VideoTypeName'] = $VideoTypeArr[$Video['VideoTypeId']]['VideoTypeName'];	
		}
		include $this->tpl('Config_Video_Video_list');
	}
	//添加广告商填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$VideoTypeArr = $this->oVideoType->getAll();
		include $this->tpl('Config_Video_Video_add');
	}
	
	//添加新广告商
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('VideoContent','VideoUrl','VideoTypeId');

		if(!$bind['VideoTypeId'])
		{
			$response = array('errno' => 1);
		}	
		if($bind['VideoUrl']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$bind['LastUpdateTime'] = time();
			$res = $this->oVideo->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改广告商信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$VideoId = intval($this->request->VideoId);
		$Video = $this->oVideo->getRow($VideoId,'*');
		$VideoTypeArr = $this->oVideoType->getAll();
		include $this->tpl('Config_Video_Video_modify');
	}	
	//更新广告商信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('VideoId','VideoContent','VideoUrl','VideoTypeId');

		if(!$bind['VideoId'])
		{
			$response = array('errno' => 3);
		}			
		elseif(!$bind['VideoTypeId'])
		{
			$response = array('errno' => 1);
		}	
		if($bind['VideoUrl']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oVideo->update($bind['VideoId'], $bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}	
	//删除广告商
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$VideoId = intval($this->request->VideoId);
		$this->oVideo->delete($VideoId);
		$this->response->goBack();
	}
}

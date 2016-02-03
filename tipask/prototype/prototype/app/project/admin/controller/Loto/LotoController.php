<?php
/**
 * 任务管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: LotoController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Loto_LotoController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=loto/loto';
	/**
	 * Loto对象
	 * @var object
	 */
	protected $oLoto;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oLoto = new Loto_Loto();

	}
	//任务配置列表页面
	public function indexAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$LotoArr = $this->oLoto->getAll();
		include $this->tpl('Loto_list');
	}
	//添加任务填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		include $this->tpl('Loto_add');
	}
	
	//添加新任务
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('LotoName','StartTime','EndTime','Comment','UserLotoLimit');

		$bind['StartTime'] = strtotime($bind['StartTime']);
		$bind['EndTime'] = strtotime($bind['EndTime']);
		if(!$bind['StartTime'])
		{
			$response = array('errno' => 2);
		}
		elseif(!$bind['EndTime'])
		{
			$response = array('errno' => 1);
		}
		elseif(!$bind['UserLotoLimit'])
		{
			$response = array('errno' => 5);
		}
		elseif($bind['LotoName']=='')
		{
			$response = array('errno' => 3);
		}	
		else
		{	
			$res = $this->oLoto->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改任务信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$LotoId = $this->request->LotoId;
		$Loto = $this->oLoto->getRow($LotoId,'*');
		$Loto['StartTime'] = date('Y-m-d H:i:s',$Loto['StartTime']);
		$Loto['EndTime'] = date('Y-m-d H:i:s',$Loto['EndTime']);
		include $this->tpl('Loto_modify');
	}
	
	//更新任务信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('LotoId','LotoName','StartTime','EndTime','Comment','UserLotoLimit');

		$bind['StartTime'] = strtotime($bind['StartTime']);
		$bind['EndTime'] = strtotime($bind['EndTime']);
		
		if($bind['LotoId']==0)
		{
			$response = array('errno' => 4);
		}
		elseif(!$bind['StartTime'])
		{
			$response = array('errno' => 2);
		}
		elseif(!$bind['EndTime'])
		{
			$response = array('errno' => 1);
		}
		elseif(!$bind['UserLotoLimit'])
		{
			$response = array('errno' => 5);
		}
		elseif($bind['LotoName']=='')
		{
			$response = array('errno' => 3);
		}	
		else
		{	
			$res = $this->oLoto->update($bind['LotoId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除任务
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$LotoId = intval($this->request->LotoId);
		$this->oLoto->delete($LotoId);
		$this->response->goBack();
	}
}

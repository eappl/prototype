<?php
/**
 * 任务管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: LotoController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Xrace_RaceTypeController extends AbstractController
{
	/**运动类型列表:RaceTypeList
	 * 权限限制  ?ctl=xrace/sports&ac=sports.type
	 * @var string
	 */
	protected $sign = '?ctl=xrace/race.type';
	/**
	 * race对象
	 * @var object
	 */
	protected $oRaceType;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oRaceType = new Xrace_Race();

	}
	//任务配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			$RaceTypeArr  = $this->oRaceType->getAllRaceTypeList();
			include $this->tpl('Xrace_Race_RaceTypeList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加任务填写配置页面
	public function raceTypeAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceTypeInsert");
		if($PermissionCheck['return'])
		{
			include $this->tpl('Xrace_Race_RaceTypeAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//添加新任务
	public function raceTypeInsertAction()
	{
		//检查权限
		$bind=$this->request->from('RaceTypeName');
		if(trim($bind['RaceTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			$res = $this->oRaceType->insertRaceType($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改任务信息页面
	public function raceTypeModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceTypeModify");
		if($PermissionCheck['return'])
		{
			$RaceTypeId = trim($this->request->RaceTypeId);
			$oRaceType = $this->oRaceType->getRaceType($RaceTypeId,'*');
			include $this->tpl('Xrace_Race_RaceTypeModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新任务信息
	public function raceTypeUpdateAction()
	{
		$bind=$this->request->from('RaceTypeId','RaceTypeName');
		if(trim($bind['RaceTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		elseif(intval($bind['RaceTypeId'])<=0)
		{
			$response = array('errno' => 2);
		}
		else
		{
			//获取原有数据
			$oRaceType = $this->oRaceType->getRaceType($bind['RaceTypeId'],'*');
			$bind['comment'] = json_decode($oRaceType['comment'],true);
			//文件上传
			$oUpload = new Base_Upload('RaceTypeIcon');
			$upload = $oUpload->upload('RaceTypeIcon');
			$res[1] = $upload->resultArr;
			$path = $res[1][1];
			//如果正确上传，就保存文件路径
			if(strlen($path['path'])>2)
			{
				$bind['comment']['RaceTypeIcon'] = $path['path'];
				$bind['comment']['RaceTypeIcon_root'] = $path['path_root'];
			}
			$bind['comment'] = json_encode($bind['comment']);
			$res = $this->oRaceType->updateRaceType($bind['RaceTypeId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}

	//删除任务
	public function raceTypeDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceTypeDelete");
		if($PermissionCheck['return'])
		{
			$RaceTypeId = intval($this->request->RaceTypeId);
			$this->oRaceType->deleteRaceType($RaceTypeId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}

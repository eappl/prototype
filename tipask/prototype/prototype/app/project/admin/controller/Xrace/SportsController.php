<?php
/**
 * 任务管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: LotoController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Xrace_SportsController extends AbstractController
{
	/**运动类型列表:SportsTypeList
	 * 权限限制  ?ctl=xrace/sports&ac=sports.type
	 * @var string
	 */
	protected $sign = '?ctl=xrace/sports';
	/**
	 * game对象
	 * @var object
	 */
	protected $oSportsType;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oSportsType = new Xrace_Sports();

	}
	//任务配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			$SportTypeArr = $this->oSportsType->getAllSportsTypeList();
			include $this->tpl('Xrace_Sports_SportsTypeList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加任务填写配置页面
	public function sportsTypeAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("SportsTypeInsert");
		if($PermissionCheck['return'])
		{
			$maxParams = $this->oSportsType->getMaxParmas();
			for($i = 1;$i<=$maxParams;$i++)
			{
				$oSportsType['comment']['params'][$i] = array('param'=>'','paramName'=>'');
			}
			include $this->tpl('Xrace_Sports_SportsTypeAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//添加新任务
	public function sportsTypeInsertAction()
	{
		//检查权限
		$bind=$this->request->from('SportsTypeName','ParamsInfo');
		if(trim($bind['SportsTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			$maxParams = $this->oSportsType->getMaxParmas();
			for($i = 1;$i<=$maxParams;$i++)
			{
				if(!isset($bind['ParamsInfo'][$i]))
				{
					$bind['ParamsInfo'][$i] = array('param'=>'','paramName'=>'');
				}
			}
			$bind['comment']['params'] = $bind['ParamsInfo'];
			unset($bind['ParamsInfo']);
			$bind['comment'] = json_encode($bind['comment']);
			$res = $this->oSportsType->insertSportsType($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改任务信息页面
	public function sportsTypeModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("SportsTypeModify");
		if($PermissionCheck['return'])
		{
			$sportsTypeId = intval($this->request->sportsTypeId);
			$oSportsType = $this->oSportsType->getSportsType($sportsTypeId,'*');
			$oSportsType['comment'] = json_decode($oSportsType['comment'],true);
			$maxParams = $this->oSportsType->getMaxParmas();
			if(isset($oSportsType['comment']['params']) && is_array($oSportsType['comment']['params']))
			{
				for($i = 1;$i<=$maxParams;$i++)
				{
					if(!isset($oSportsType['comment']['params'][$i]))
					{
						$oSportsType['comment']['params'][$i] = array('param'=>'','paramName'=>'');
					}
				}
			}
			else
			{
				for($i = 1;$i<=$maxParams;$i++)
				{
					$oSportsType['comment']['params'][$i] = array('param'=>'','paramName'=>'');
				}
			}
			include $this->tpl('Xrace_Sports_SportsTypeModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新任务信息
	public function sportsTypeUpdateAction()
	{
		$bind=$this->request->from('SportsTypeId','SportsTypeName','ParamsInfo');
		if(trim($bind['SportsTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			$oSportsType = $this->oSportsType->getSportsType($bind['SportsTypeId'],'*');
			$bind['comment'] = json_decode($oSportsType['comment'],true);
			$maxParams = $this->oSportsType->getMaxParmas();
			for($i = 1;$i<=$maxParams;$i++)
			{
				if(!isset($bind['ParamsInfo'][$i]))
				{
					$bind['ParamsInfo'][$i] = array('param'=>'','paramName'=>'');
				}
				else
				{
					//$bind['ParamsInfo'][$i] =
				}
			}
			$bind['comment']['params'] = $bind['ParamsInfo'];
			unset($bind['ParamsInfo']);
			$bind['comment'] = json_encode($bind['comment']);
			$res = $this->oSportsType->updateSportsType($bind['SportsTypeId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除任务
	public function sportsTypeDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("SportsTypeDelete");
		if($PermissionCheck['return'])
		{
			$sportsTypeId = trim($this->request->sportsTypeId);
			$this->oSportsType->deleteSportsType($sportsTypeId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}

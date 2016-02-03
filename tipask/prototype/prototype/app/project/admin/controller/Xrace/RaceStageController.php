<?php
/**
 * 任务管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: LotoController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Xrace_RaceStageController extends AbstractController
{
	/**运动类型列表:RaceStageList
	 * 权限限制  ?ctl=xrace/sports&ac=sports.type
	 * @var string
	 */
	protected $sign = '?ctl=xrace/race.stage';
	/**
	 * race对象
	 * @var object
	 */
	protected $oRaceStage;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oRaceStage = new Xrace_Race();

	}
	//任务配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			$RaceCatalogId = isset($this->request->RaceCatalogId)?intval($this->request->RaceCatalogId):0;
			$RaceCatalogArr  = $this->oRaceStage->getAllRaceCatalogList();
			$RaceStageArr = $this->oRaceStage->getAllRaceStageList($RaceCatalogId);
			$RaceGroupArr = $this->oRaceStage->getAllRaceGroupList($RaceCatalogId,'RaceGroupId,RaceGroupName');
			$RaceStageList = array();
			foreach($RaceStageArr as $key => $value)
			{
				$RaceStageList[$value['RaceCatalogId']]['RaceStageList'][$key] = $value;
				$RaceStageList[$value['RaceCatalogId']]['RaceStageCount'] = isset($RaceStageList[$value['RaceCatalogId']]['RaceStageCount'])?$RaceStageList[$value['RaceCatalogId']]['RaceStageCount']+1:1;
				$RaceStageList[$value['RaceCatalogId']]['RowCount'] = $RaceStageList[$value['RaceCatalogId']]['RaceStageCount']+1;

				if(isset($RaceCatalogArr[$value['RaceCatalogId']]))
				{
					$RaceStageList[$value['RaceCatalogId']]['RaceCatalogName'] = isset($RaceStageList[$value['RaceCatalogId']]['RaceCatalogName'])?$RaceStageList[$value['RaceCatalogId']]['RaceCatalogName']:$RaceCatalogArr[$value['RaceCatalogId']]['RaceCatalogName'];

				}
				else
				{
					$RaceStageList[$value['RaceCatalogId']]['RaceCatalogName'] = 	"未定义";
				}

				if(isset($RaceCatalogArr[$value['RaceCatalogId']]))
				{
					$value['comment'] = json_decode($value['comment'],true);
					$t = array();
					if(isset($value['comment']['SelectedRaceGroup']) && is_array($value['comment']['SelectedRaceGroup']))
					{
						foreach($value['comment']['SelectedRaceGroup'] as $k => $v)
						{
							if(isset($RaceGroupArr[$v]))
							{
								$t[$k] = $RaceGroupArr[$v]['RaceGroupName'];
							}
						}
					}
					if(count($t))
					{
						$RaceStageList[$value['RaceCatalogId']]['RaceStageList'][$key]['SelectedGroupList'] = implode("/",$t);
						$RaceStageList[$value['RaceCatalogId']]['RaceStageList'][$key]['RaceDetail'] = 1;
						$RaceStageList[$value['RaceCatalogId']]['RaceStageList'][$key]['GroupCount'] = count($t);
						$RaceStageList[$value['RaceCatalogId']]['RaceStageList'][$key]['RowCount'] = $RaceStageList[$value['RaceCatalogId']]['RaceStageList'][$key]['GroupCount']+1;
					}
					else
					{
						$RaceStageList[$value['RaceCatalogId']]['RaceStageList'][$key]['SelectedGroupList'] = "尚未配置";
						$RaceStageList[$value['RaceCatalogId']]['RaceStageList'][$key]['RaceDetail'] = 0;
						$RaceStageList[$value['RaceCatalogId']]['RaceStageList'][$key]['GroupCount'] = 0;
						$RaceStageList[$value['RaceCatalogId']]['RaceStageList'][$key]['RowCount'] = 1;
					}
				}
				else
				{
					$RaceStageArr[$key]['RaceCatalogName'] = 	"未定义";
				}
			}
			include $this->tpl('Xrace_Race_RaceStageList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加任务填写配置页面
	public function raceStageAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceStageInsert");
		if($PermissionCheck['return'])
		{
			include('Third/ckeditor/ckeditor.php');

			$editor =  new CKEditor();
			$editor->BasePath = '/js/ckeditor/';
			$editor->config['height'] = "50%";
			$editor->config['width'] ="80%";

			$RaceCatalogArr  = $this->oRaceStage->getAllRaceCatalogList();
			include $this->tpl('Xrace_Race_RaceStageAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//添加新任务
	public function raceStageInsertAction()
	{
		//检查权限
		$bind=$this->request->from('RaceStageName','RaceCatalogId');
		$SelectedRaceGroup = $this->request->from('SelectedRaceGroup');
		$RaceCatalogArr  = $this->oRaceStage->getAllRaceCatalogList();
		if(trim($bind['RaceStageName'])=="")
		{
			$response = array('errno' => 1);
		}
		elseif(!isset($RaceCatalogArr[$bind['RaceCatalogId']]))
		{
			$response = array('errno' => 3);
		}
		elseif(count($SelectedRaceGroup['SelectedRaceGroup'])==0)
		{
			$response = array('errno' => 4);
		}
		else
		{
			$bind['comment']['SelectedRaceGroup'] = $SelectedRaceGroup['SelectedRaceGroup'];
			$bind['comment'] = json_encode($bind['comment']);
			$res = $this->oRaceStage->insertRaceStage($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改任务信息页面
	public function raceStageModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceStageModify");
		if($PermissionCheck['return'])
		{
			$RaceStageId = trim($this->request->RaceStageId);
			$RaceCatalogArr  = $this->oRaceStage->getAllRaceCatalogList();
			$oRaceStage = $this->oRaceStage->getRaceStage($RaceStageId,'*');
			$RaceGroupArr = $this->oRaceStage->getAllRaceGroupList($oRaceStage['RaceCatalogId'],'RaceGroupId,RaceGroupName');
			$oRaceStage['comment'] = json_decode($oRaceStage['comment'],true);
			foreach($RaceGroupArr as $RaceGroupId => $value)
			{
				if(in_array($RaceGroupId,$oRaceStage['comment']['SelectedRaceGroup']))
				{
					$RaceGroupArr[$RaceGroupId]['selected'] = 1;
				}
				else
				{
					$RaceGroupArr[$RaceGroupId]['selected'] = 0;
				}
			}
			include $this->tpl('Xrace_Race_RaceStageModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新任务信息
	public function raceStageUpdateAction()
	{
		$bind=$this->request->from('RaceStageId','RaceStageName','RaceCatalogId','StageStartDate','StageEndDate');
		$SelectedRaceGroup = $this->request->from('SelectedRaceGroup');
		$RaceCatalogArr  = $this->oRaceStage->getAllRaceCatalogList();
		if(trim($bind['RaceStageName'])=="")
		{
			$response = array('errno' => 1);
		}
		elseif(intval($bind['RaceStageId'])<=0)
		{
			$response = array('errno' => 2);
		}
		elseif(!isset($RaceCatalogArr[$bind['RaceCatalogId']]))
		{
			$response = array('errno' => 3);
		}
		elseif(count($SelectedRaceGroup['SelectedRaceGroup'])==0)
		{
			$response = array('errno' => 4);
		}
		else
		{
			$bind['comment']['SelectedRaceGroup'] = $SelectedRaceGroup['SelectedRaceGroup'];
			$bind['comment'] = json_encode($bind['comment']);
			$res = $this->oRaceStage->updateRaceStage($bind['RaceStageId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//更新任务信息
	public function raceDetailListAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceStageDelete");
		$PermissionCheck['return'] = "1";
		if($PermissionCheck['return'])
		{
			$menuArr = array('aRaceDetailName'=>'赛段详情名称','RaceDatailLenthList'=>"分段计时点长度",'Price'=>'报名价格','MaxPeople'=>"最大人数限制",'MinPeople'=>"最小人数限制");
			krsort($menuArr);
			$MaxRaceDetail = $this->oRaceStage->getMaxRaceDetail()+1;
			$RaceStageId = intval($this->request->RaceStageId);
			$oRaceStage = $this->oRaceStage->getRaceStage($RaceStageId,'*');
			$oRaceStage['comment'] = json_decode($oRaceStage['comment'],true);
			$RaceGroupArr = $this->oRaceStage->getAllRaceGroupList($oRaceStage['RaceCatalogId'],'RaceGroupId,RaceGroupName');
			foreach($oRaceStage['comment']['SelectedRaceGroup'] as $key => $value)
			{
				$oRaceStage['comment']['SelectedGroupDetail'][$value]['RaceGroupName'] = $RaceGroupArr[$value]['RaceGroupName'];
				for($i=1;$i<$MaxRaceDetail;$i++)
				{
					if(!isset($oRaceStage['comment']['SelectedGroupDetail'][$value]['DetailList'][$i]))
					{
						$oRaceStage['comment']['SelectedGroupDetail'][$value]['DetailList'][$i] = array('aRaceDetailName'=>'','RaceDatailLenthList'=>"",'Price'=>'','MaxPeople'=>1,'MinPeople'=>1);
					}
					krsort($oRaceStage['comment']['SelectedGroupDetail'][$value]['DetailList'][$i]);
				}
			}
			include $this->tpl('Xrace_Race_RaceDetailList');

		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//更新任务信息
	public function raceDetailUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceStageDelete");
		$PermissionCheck['return'] = "1";
		if($PermissionCheck['return'])
		{
			$SelectedGroupDetail = $this->request->from('SelectedGroupDetail');
			$SelectedGroupDetail = $SelectedGroupDetail['SelectedGroupDetail'];
			$MaxRaceDetail = $this->oRaceStage->getMaxRaceDetail()+1;
			$RaceStageId = intval($this->request->RaceStageId);
			$oRaceStage = $this->oRaceStage->getRaceStage($RaceStageId,'*');
			$oRaceStage['comment'] = json_decode($oRaceStage['comment'],true);
			foreach($oRaceStage['comment']['SelectedRaceGroup'] as $key => $value)
			{
				for($i=1;$i<$MaxRaceDetail;$i++)
				{
					if(isset($SelectedGroupDetail[$value][$i]) && ($SelectedGroupDetail[$value][$i]['aRaceDetailName']) != "")
					{
						$oRaceStage['comment']['SelectedGroupDetail'][$value]['DetailList'][$i] = array('aRaceDetailName'=>$SelectedGroupDetail[$value][$i]['aRaceDetailName'],
							'RaceDatailLenthList'=>$SelectedGroupDetail[$value][$i]['RaceDatailLenthList'],
							'MaxPeople'=>$SelectedGroupDetail[$value][$i]['MaxPeople']>0?intval($SelectedGroupDetail[$value][$i]['MaxPeople']):1,
							'MinPeople'=>$SelectedGroupDetail[$value][$i]['MinPeople']>0?intval($SelectedGroupDetail[$value][$i]['MinPeople']):1,
							'Price'=>$SelectedGroupDetail[$value][$i]['Price'],);
					}
				}
			}
			$bind['comment'] = json_encode($oRaceStage['comment']);
			$res = $this->oRaceStage->updateRaceStage($RaceStageId,$bind);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//删除任务
	public function raceStageDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceStageDelete");
		if($PermissionCheck['return'])
		{
			$RaceStageId = intval($this->request->RaceStageId);
			$this->oRaceStage->deleteRaceStage($RaceStageId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//删除任务
	public function getSelectedGroupAction()
	{
		$RaceCatalogId = intval($this->request->RaceCatalogId);
		$RaceStageId = intval($this->request->RaceStageId);
		$RaceGroupArr = $this->oRaceStage->getAllRaceGroupList($RaceCatalogId);
		if($RaceStageId)
		{
			$oRaceStage = $this->oRaceStage->getRaceStage($RaceStageId,'*');
			$oRaceStage['comment'] = json_decode($oRaceStage['comment'],true);
		}
		else
		{
			$oRaceStage['comment']['SelectedRaceGroup'] = array();
		}
		foreach($RaceGroupArr as $RaceGroupId => $RaceGroupInfo)
		{
			if(in_array($RaceGroupId,$oRaceStage['comment']['SelectedRaceGroup']))
			{
				$t[$RaceGroupId] = '<input type="checkbox"  name="SelectedRaceGroup[]" value='.$RaceGroupId.' checked>'.$RaceGroupInfo['RaceGroupName'];
			}
			else
			{
				$t[$RaceGroupId] = '<input type="checkbox"  name="SelectedRaceGroup[]" value='.$RaceGroupId.'>'.$RaceGroupInfo['RaceGroupName'];
			}
		}
		$text = implode("  ",$t);
		$text = (trim($text!=""))?$text:"暂无分类";
		echo $text;
		die();
	}
}

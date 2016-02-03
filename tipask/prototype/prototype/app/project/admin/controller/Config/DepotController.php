<?php
/**
 * 机房管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: DepotController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_DepotController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/depot';
	/**
	 * Depot对象
	 * @var object
	 */
	protected $oDepot;
	protected $oCage;	

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oDepot = new Config_Depot();
		$this->oCage = new Config_Cage();
		$this->oMachine = new Config_Machine();
		
		$this->CageList = $this->oCage->getAll();
	}
	//机房配置列表页面
	public function indexAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$DepotArr = $this->oDepot->getAll();
		$DepotListCount = $this->oCage->getAllCountDepot();
		foreach($DepotArr as $DepotId=> $DepotInfo)
		{
			$DepotArr[$DepotId]["count"] = $DepotListCount[$DepotId]? $DepotListCount[$DepotId]: 0;
			$DepotArr[$DepotId]['FirstX'] = substr($DepotInfo['X'],0,strpos($DepotInfo['X'],","));
			$DepotArr[$DepotId]['Udate'] = date("Y-m-d H:i:s",$DepotInfo['Udate']);
		}
		include $this->tpl('Config_Depot_list');
	}
	//添加机房填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		include $this->tpl('Config_Depot_add');
	}
	
	//添加新机房
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','Comment','X');
		$bind['name'] = trim($bind['name']);
		$bind['Comment'] = trim($bind['Comment']);
		$bind['X'] = trim($bind['X']);
		$bind['Y']=0;
		$bind['Udate'] = time();
		if($bind['name']=='')
		{
			$response = array('errno' => 2);
		}elseif($this->oDepot->getRowByName($bind['name']))
		{
			$response = array('errno' => 3);		
		}		
		else
		{	
			$res = $this->oDepot->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改机房信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$DepotId = $this->request->DepotId;
		$Depot = $this->oDepot->getRow($DepotId,'*');
		include $this->tpl('Config_Depot_modify');
	}
	
	//更新机房信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);

		$bind=$this->request->from('DepotId','name','Comment','X');
		$bind['name'] = trim($bind['name']);
		$bind['Comment'] = trim($bind['Comment']);
		$bind['X'] = trim($bind['X']);
		$bind['Y'] = 0;
		$bind['Udate'] = time();
		
		if($bind['DepotId']==0)
		{
			$response = array('errno' => 5);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}else
		{	
			$res = $this->oDepot->update($bind['DepotId'], $bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	public function getDepotDelmesAction()
	{
		$DepotId = intval($this->request->DepotId);
		$CageList = $this->oCage->getAll($DepotId);
		if(count($CageList))
		{
			echo "1";		//1表示有数据，不能删除机房	
		}else{
			echo "0";			//0表示有数据，可以删除机房	
		}
	
	}
	//删除机房
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$DepotId = intval($this->request->DepotId);
		$this->oDepot->delete($DepotId);
		$this->response->goBack();
	}
	/**
	 * 获取机房列表
	 * @return 下拉列表
	 */
	public function getDepotAction()
	{
		$DepotArr = $this->oDepot->getAll();
		echo "<option value=0>全部</option>";

		if(count($DepotArr))
		{
			foreach($DepotArr as  $DepotId => $DepotData)
			{
				echo "<option value='{$DepotId}'>{$DepotData['name']}</option>";
			}
		}
	}
	public function cagePositionAction()
	{
		$DepotId = $this->request->DepotId;
		$DepotInfo = $this->oDepot->getRow($DepotId);
		$CageArr = $this->oCage->getAll($DepotId);
		for($i = 1;$i <= $DepotInfo['X'];$i++)
		{
			$CageMap['total']['X'][$i] = 1;
			for($j = 1;$j <= $DepotInfo['Y'];$j++)
			{
				$CageMap['total']['Y'][$j] = 1;
				$CageMap['detail'][$i][$j] = 0;	
			}	
		}
		if(is_array($CageArr))
		{
			foreach($CageArr as $Cage => $CageData)
			{
				$CageMap['detail'][$CageData['X']][$CageData['Y']] = $CageData['CageCode'];						
			}
		}
		include $this->tpl('Config_Depot_CagePosition');
	}
	//机器地图分布
	public function machineMapAction()
	{
		$sign = "?ctl=config/depot&ac=machine.map";
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		
		$export = $this->request->export? intval($this->request->export):0;
		$DepotParame['export']= 1;
		$DepotList = $this->oDepot->getAll();
		//用于设置默认的页面参数
		$DepotIdStr = Base_Common::getArrList($DepotList);
		$DepotArr = explode(",",$DepotIdStr);

		//页面参数
		$DepotId = $this->request->DepotId? $this->request->DepotId:$DepotArr[0];
		$DepotX = $this->request->X;	
		
		$DepotXList = $this->getDepotX($DepotId);

		$DepotName = $DepotList[$DepotId]['name'];
		
		if($DepotId && $DepotX)
		{
			$DepotParame['DepotId'] = $DepotId;
			$DepotParame['X'] = $DepotX;
			$CageList = $this->oCage->getCageListParams($DepotId,$DepotX);
			
			$MachineFields = "MachineId,MachineCode,EstateCode,Position,Size,LocalIP,WebIP,Purpose,Flag";
			foreach($CageList as $CageId=> $CageInfo)
			{
				$MachineList = $this->oMachine->getMachineByCageId($CageId,$MachineFields);
				
				$SizeList = array();
				for($i=1;$i<=$CageInfo["Size"];$i++)
				{
					$SizeList[$i] = 0;			
				}
				foreach($MachineList as $key=> $MachineInfo)
				{
					$MachineInfo['LocalIP'] = long2ip($MachineInfo['LocalIP']);
					$MachineInfo['WebIP'] = long2ip($MachineInfo['WebIP']);
										
					$SizeList[$MachineInfo['Position']] = $MachineInfo;
					for($i=$MachineInfo['Position'];$i<=$MachineInfo['Position']+$MachineInfo['Size']-1;$i++)
					{
						if($i>$MachineInfo['Position'])
						{
							unset($SizeList[$i]);
						}
					}
					ksort($SizeList);
					$CageList[$CageId]['SizeList'] = $SizeList;
				}
			}

		}
    $imgPath = __APP_ROOT_DIR__."admin/html/img/machine/";
		//导出表格
		$export_var = "<a href =".(Base_Common::getUrl('','config/depot','machine.map',$DepotParame))."><导出表格></a>";
		if($export ==1)
		{
			/*header("Content-type:application/vnd.ms-excel;charset=UTF-8");
			header("Content-Disposition:attachment;filename=test_data.xls");
			
			foreach($CageList as $CageId=> $CageInfo)
			{
				echo "<table>";
				echo "<tr><td>编号：{$CageInfo['CageCode']}</td></tr>";
				echo "<tr><td>电量：{$CageInfo['Current']}A</td></tr>";
				echo "<tr><td>实际电量：{$CageInfo['ActualCurrent']}A</td></tr>";
				foreach($CageInfo['SizeList'] as $k=> $v)
				{
					if($v == 0)
					{
						echo "<tr><td></td></tr>";			
				  }else{
				  	if($v['Flag']==1)
				  	{
				  		echo "<tr><td><img title='内网IP：".$v['LocalIP']." 外网IP：".$v['WebIP']."' src='{$imgPath}server/server".$v['Size'].".png' style='height:".($v['Size']*15)."px' /></td></tr>";		  	
				  	}elseif($v['Flag']==2)//交换机
				  	{
				  		echo "<tr><td><img title='内网IP：".$v['MachineCode']."外网IP：".$v['WebIP']."' src='{$imgPath}exchange/exchange".$v['Size'].".png' style='height:".($v['Size']*15)."px' /></td></tr>";			  	
				  	}elseif($v['Flag']==3)//防火墙
				  	{
				  		echo "<tr><td><img  title='内网IP：".$v['MachineCode']."外网IP：".$v['WebIP']."' src='{$imgPath}router/router".$v['Size'].".png' style='height:".($v['Size']*15)."px' /></td></tr>";			  	
				  	}elseif($v['Flag']==4)//路由器
				  	{
				  		echo "<tr><td><img  title='内网IP：".$v['MachineCode']."外网IP：".$v['WebIP']."' src='{$imgPath}router/router".$v['Size'].".png' style='height:".($v['Size']*15)."px' /></td></tr>";			  	
				  	}
		
		
				  }
				
				}
				 
				echo "</table>";
				
			}*/

			/*$FileName='机器列表图';
			$oExcel = new Third_Excel();
			$oExcel->download($FileName)->addSheet('机器列表图');
			include $this->tpl('Config_Depot_MapList3');lll
			$oExcel->closeSheet()->close();	*/
			header("Content-type:application/vnd.ms-excel;charset=UTF-8");
			header("Content-Disposition:attachment;filename=MachineMap.xls");
			include $this->tpl('Config_Depot_MapExecl');
		}else{
			include $this->tpl('Config_Depot_MapList');
		}
		
	}
	
	public function getDepotX($DepotId)
	{
		$DepotX = $this->oDepot->getOne($DepotId,"X");
		$DepotXList = explode(",",$DepotX);
		return $DepotXList;		
	}
	//检察机房名称是否存在
	public function checkDepotNameAction()
	{
		$name = $this->request->name;
		$return = $this->oDepot->getRowByName($name);
		if($return)
		{
			echo "no";
			
		}else{
			
			echo "yes";
		}
		
	}
	
}

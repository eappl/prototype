<?php
/**
 * 机柜管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: CageController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_CageController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/cage';
	/**
	 * Cage对象
	 * @var object
	 */
	protected $oCage;
	protected $oDepot;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oCage = new Config_Cage();
		$this->oDepot = new Config_Depot();
		$this->oMachine = new Config_Machine();
		$this->DepotList = $this->oDepot->getAll();
	}
	//机柜配置列表页面
	public function indexAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$DepotList = $this->DepotList;
		$DepotId = abs(intval($this->request->DepotId));
		$CageArr = $this->oCage->getAll($DepotId);

		$CageListAll = array();
		foreach($CageArr as $DeoptId => $CageList)
		{
				foreach($CageList as $CageId=> $CageInfo)
				{				
					$CageListAll[$CageId] = $CageInfo;
				}
		}

		foreach($CageListAll as $CageId=> $CageInfo)
		{
			if(!isset($DeoptList[$CageInfo['DepotId']]))
			{
				$DeoptInfo = $this->oDepot->getRow($CageInfo['DepotId']);
				$DeoptList[$CageInfo['DepotId']] = $DeoptInfo;				
			}
			$CageListAll[$CageId]["DepotName"] = $DeoptList[$CageInfo['DepotId']]["name"];
			$CageListAll[$CageId]["MachineCount"] = $this->oMachine->getMachineCountByCageId($CageId);
			$CageListAll[$CageId]["Udate"] = date("Y-m-d H:i:s",$CageInfo['Udate']);
		} 
		
		include $this->tpl('Config_Cage_list');
	}
	//添加机柜填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$DepotList = $this->DepotList;
	
		/*$first = reset($DepotList);
		for($i = 1;$i<=$first['X'];$i++)
		{
			$position['X'][$i] = 0;
		}
		for($i = 1;$i<=$first['Y'];$i++)
		{
			$position['Y'][$i] = 0;
		}*/
		
		include $this->tpl('Config_Cage_add');
	}
	
	//添加新机柜
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('CageCode','Comment','Depot','Current','Size','X');
		//echo "<pre>";
		//print_r($bind);
		$bind['DepotId'] = $bind['Depot'];
		unset($bind['Depot']);	
		$bind['CageCode'] = trim($bind['CageCode']);
		$bind['Comment'] = trim($bind['Comment']);
		
		$bind['Current'] = sprintf("%.2f",$bind['Current']);
		$bind['ActualCurrent'] = 0;
		$bind['Size'] = intval($bind['Size']);
		$bind['X'] = trim($bind['X']);
		$bind['Y'] = 0;
		$bind['Udate'] = time();
		
		if($bind['CageCode']=='')
		{
			$response = array('errno' => 2);
		}elseif($this->oCage->getRowByCageCode($bind['CageCode']))
		{
			$response = array('errno' => 6);
		}
		elseif(!$bind['DepotId'])
		{
			$response = array('errno' => 3);
		}	
		elseif(!$bind['Current'])
		{
			$response = array('errno' => 4);
		}	
		elseif(!$bind['Size'])
		{
			$response = array('errno' => 5);
		}
		else
		{	
			$res = $this->oCage->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改机柜信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);		
		$CageId = $this->request->CageId;

		$Cage = $this->oCage->getRow($CageId,'*');
		
		$DepotList = $this->DepotList;
		$DepotXList = $this->getDepotX($Cage['DepotId']);
		
		/*$position  = array('X'=>array(),'Y'=>array());
		for($i = 1;$i<=$DepotList[$Cage['DepotId']]['X'];$i++)
		{
			$position['X'][$i] = 0;
		}
		for($i = 1;$i<=$DepotList[$Cage['DepotId']]['Y'];$i++)
		{
			$position['Y'][$i] = 0;
		}*/
		include $this->tpl('Config_Cage_modify');
	}
	
	//更新机柜信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('CageId','CageCode','Depot','Current','ActualCurrent','Size','Comment',"X");
		$bind['DepotId'] = $bind['Depot'];
		unset($bind['Depot']);	
		$bind['CageCode'] = trim($bind['CageCode']);
		$bind['Comment'] = trim($bind['Comment']);
		
		$bind['Current'] = sprintf("%.2f",$bind['Current']);
		$bind['ActualCurrent'] = sprintf("%.2f",$bind['ActualCurrent']);
		$bind['Size'] = intval($bind['Size']);
		$bind['X'] = trim($bind['X']);
		$bind['Y'] = 0;
		$bind['Udate'] = time();
		
		if($bind['CageCode']=='')
		{
			$response = array('errno' => 2);
		}
		elseif(!$bind['DepotId'])
		{
			$response = array('errno' => 3);
		}	
		elseif(!$bind['Current'])
		{
			$response = array('errno' => 4);
		}	
		elseif(!$bind['Size'])
		{
			$response = array('errno' => 5);
		}
		else
		{	
			$res = $this->oCage->update($bind['CageId'], $bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}

	public function getCageDelmesAction()
	{
		$CageId = intval($this->request->CageId);
		$MachineCount = $this->oMachine->getMachineCountByCageId($CageId);
		echo $MachineCount."<br/>";
		if($MachineCount!=0)
		{
			echo "1";		//1表示有数据，不能删除机房	
		}else{
			echo "0";			//0表示没有数据，可以删除机房	
		}
	
	}
	//删除机柜
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$CageId = intval($this->request->CageId);
		$this->oCage->delete($CageId);
		$this->response->goBack();
	}
	/**
	 * 获取机柜列表
	 * @return 下拉列表
	 */
	public function getCageByDepotction()
	{
		$DepotId = abs(intval($this->request->DepotId));
		$CageArr = $this->oCage->getAll($DepotId);
		if(count($CageArr))
		{
			foreach($CageArr as  $CageCode => $CageData)
			{
				echo "<option value='{$CageCode}'>{CageCode}</option>";
			}
		}
	}
	/**
	 * 获取指定机房有空余的位置行列表
	 * @return 下拉列表
	 */
	 
	/*public function getAvailableXAction()
	{
		$self = $this->request->self;
		$CageId = $this->request->CageId;
		$DepotId = $this->request->DepotId;
		$CageArr = $this->oCage->getAll($DepotId);
		$DepotInfo = $this->oDepot->getRow($DepotId);
		$CageInfo = $this->oCage->getRow($CageId);
		for($i = 1;$i <= $DepotInfo['X'];$i++)
		{
			for($j = 1;$j <= $DepotInfo['Y'];$j++)
			{
				$CageMap[$i][$j] = 0;	
			}	
		}
		if(is_array($CageArr))
		{
			foreach($CageArr as $Cage => $CageData)
			{
				if($self)
				{
					if($Cage != $CageId)
					{
						$CageMap[$CageData['X']][$CageData['Y']] = 1;	
					}	
				}
				else
				{
					$CageMap[$CageData['X']][$CageData['Y']] = 1;	
				}	
			}
		}
		foreach($CageMap as $key => $value)
		{
			if(count($value) > array_sum($value))
			{
				if(array_sum($value))
				{
					echo "<option value='{$key}'>行{$key}：已占用".array_sum($value)."</option>";	
				}
				else
				{
					echo "<option value='{$key}'>行{$key}</option>";	
				}
			}									
		}	
	}*/
	/**
	 * 获取指定机房指定行有空余的位置列列表
	 * @return 下拉列表
	 */
	 
	/*public function getAvailableYAction()
	{
		$self = $this->request->self;
		$X = $this->request->X;
		$CageId = $this->request->CageId;
		$DepotId = $this->request->DepotId;
		$CageArr = $this->oCage->getAll($DepotId);
		$DepotInfo = $this->oDepot->getRow($DepotId);
		$CageInfo = $this->oCage->getRow($CageId);
		for($i = 1;$i <= $DepotInfo['X'];$i++)
		{
			for($j = 1;$j <= $DepotInfo['Y'];$j++)
			{
				$CageMap[$i][$j] = 0;	
			}	
		}
		if(is_array($CageArr))
		{
			foreach($CageArr as $Cage => $CageData)
			{
				if($self)
				{
					if($Cage != $CageId)
					{
						$CageMap[$CageData['X']][$CageData['Y']] = 1;	
					}	
				}
				else
				{
					$CageMap[$CageData['X']][$CageData['Y']] = 1;	
				}	
			}
		}
		foreach($CageMap[$X] as $key => $value)
		{
			if(!$value)
			{
				echo "<option value='{$key}'>列{$key}</option>";	
			}									
		}
	}*/

	public function machinePositionAction()
	{
		$CageId = $this->request->CageId;
		$CageInfo = $this->oCage->getRow($CageId );
		$MachineList = $this->oMachine->getMachineByCageId($CageId,"MachineCode,Position,Size,LocalIP,WebIP,Comment");
		$CageMap = array();
	
		for($i=1;$i<=$CageInfo["Size"];$i++)
		{
			$CageMap[$i] = 0;			
		}
		foreach($MachineList as $k=> $val)
		{
			$val['LocalIP'] = long2ip($val['LocalIP']);
			$val['WebIP'] = long2ip($val['WebIP']);
			$val['Comment'] = json_decode($val['Comment'],true);			
			$CageMap[$val['Position']] = $val;
			for($i=$val['Position'];$i<=$val['Position']+$val['Size']-1;$i++)
			{
				if($i>$val['Position'])
				{
					unset($CageMap[$i]);
				}	
			}
		
		}

		$i = 1;
		foreach($CageMap as $row => $row_info)
		{
			if($row_info==0)
			{
				$trstr.= "<tr><td>$i</td><td>empty</td></tr>";				
			}
			elseif(is_array($row_info))
			{
				for($j=$i;$j<=$row_info['Size']+$i-1;$j++)
				{
					if($j==$i)
					{
						 $trstr.="<tr ><td>$j</td><td rowspan = ".$row_info['Size'].">".$row_info['MachineCode']."</td></tr>";
					}
					else
					{
						$trstr.="<tr rowspan = ".$row_info['Size']."><td>$j</td></tr>";
					}
				}
			}
			$i++;	
		}
		//print_r($CageMap);
		include $this->tpl('Config_Cage_MachinePosition');
	}
	
	public function getDepotX($DepotId)
	{
		$DepotX = $this->oDepot->getOne($DepotId,"X");
		$DepotXList = explode(",",$DepotX);
		return $DepotXList;		
	}
	
	//根据机房id获取机房编号 getDepotXList
	public function getDepotXAction()
	{
		$DepotId = $this->request->DepotId;
		$DepotXList = $this->getDepotX($DepotId);
		$str = "";
		foreach($DepotXList as $k=> $v)
		{
			$str .= "<option value='".$v."'>".$v."</option>";
			
		}
		echo $str;
	}
	
	//检查机柜编码是否存在
	public function checkCageCodeAction()
	{
		$CageCode = $this->request->CageCode;
		$return = $this->oCage->getRowByCageCode($CageCode);
		if($return)
		{
			echo "no";
			
		}else{
			
			echo "yes";
		}
		
	}
	
}

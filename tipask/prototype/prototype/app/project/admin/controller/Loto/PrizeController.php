<?php
/**
 * 奖品管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: PrizeController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Loto_PrizeController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=loto/prize';
	/**
	 * Prize对象
	 * @var object
	 */
	protected $oPrize;
	protected $oLoto;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oPrize = new Loto_Prize();
		$this->oLoto = new Loto_Loto();
		
		
	}
	//奖品配置列表页面
	public function indexAction()
	{
		$LotoList = $this->oLoto->getAll();
		$LotoId = $this->request->LotoId?abs(intval($this->request->LotoId)):0;
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$PrizeArr = $this->oPrize->getAll($LotoId);
		if($PrizeArr)
		{
			foreach($PrizeArr as $LotoId => $LotoData)
			{
				foreach($LotoData as $LotoPrizeId => $PrizeData)
				{
					$PrizeArr[$LotoId][$LotoPrizeId]['LotoName'] = $LotoList[$PrizeData['LotoId']]['LotoName'];	
					$PrizeDetail = $this->oPrize->getAllPrizeDetail($LotoPrizeId);
					if(is_array($PrizeDetail))
					{
						  $PrizeArr[$LotoId][$LotoPrizeId]['LotoPrizeCount'] = 0;
							$PrizeArr[$LotoId][$LotoPrizeId]['LotoPrizeCountUsed'] = 0;

						foreach($PrizeDetail as $key => $value)
						{
							$PrizeArr[$LotoId][$LotoPrizeId]['LotoPrizeCount'] += $value['LotoPrizeCount'];
							$PrizeArr[$LotoId][$LotoPrizeId]['LotoPrizeCountUsed'] +=  $value['LotoPrizeCountUsed'];
						}	
					}		
				}
			}
		}
		include $this->tpl('Loto_Prize_list');
	}
	//添加奖品填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$LotoList = $this->oLoto->getAll();
		include $this->tpl('Loto_Prize_add');
	}
	
	//添加新奖品
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('LotoPrizeName','LotoId');


		if($bind['LotoId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['LotoPrizeName']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oPrize->insert($bind);
			$response = $res ? array('errno' => 0,'LotoId'=>$bind['LotoId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改奖品信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$LotoPrizeId = $this->request->LotoPrizeId;
		$LotoPrize = $this->oPrize->getRow($LotoPrizeId,'*');
		$LotoList = $this->oLoto->getAll();
		include $this->tpl('Loto_Prize_modify');
	}
	
	//更新奖品信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('LotoPrizeId','LotoPrizeName','LotoId');

		
		if($bind['LotoPrizeId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['LotoId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['LotoPrizeName']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oPrize->update($bind['LotoPrizeId'],$bind);
			$response = $res ? array('errno' => 0,'LotoId'=>$bind['LotoId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除奖品
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$LotoPrizeId = intval($this->request->LotoPrizeId);
		$LotoId = intval($this->request->LotoId);
		$this->oPrize->delete($LotoPrizeId,$LotoId);
		$this->response->goBack();
	}
	
	public function getPrizeAction()
	{
		$LotoId = intval($this->request->LotoId)?intval($this->request->LotoId):0;
		$PrizeArr = $this->oPrize->getAll($LotoId);

		echo "<option value=''>--全部--</option>";
		if(is_array($PrizeArr[$LotoId]))
		{
			foreach ($PrizeArr[$LotoId] as $job_id => $job)
			{
					echo "<option value='{$job_id}'>{$job['LotoPrizeName']}</option>";

			}
		}
	}
	//奖品配置列表页面
	public function detailAction()
	{
		$LotoPrizeId = abs(intval($this->request->LotoPrizeId));
		$LotoPrizeInfo = $this->oPrize->getRow($LotoPrizeId);

		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$PrizeDetailList = $this->oPrize->getAllPrizeDetail($LotoPrizeId);
		include $this->tpl('Loto_Prize_Detail_list');
	}
	//修改奖品概率信息页面
	public function modifyDetailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$LotoPrizeDetailId = $this->request->LotoPrizeDetailId;
		$LotoPrizeDetail = $this->oPrize->getDetail($LotoPrizeDetailId);
		include $this->tpl('Loto_Prize_Detail_modify');
	}
	//更新概率信息
	public function updateDetailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('StartTime','EndTime','PrizeRate','LotoPrizeDetailId','LotoPrizeCount');

		$bind['StartTime'] = strtotime($bind['StartTime']);
		$bind['EndTime'] = strtotime($bind['EndTime']);
		$res = $this->oPrize->updateDetail($bind['LotoPrizeDetailId'],$bind);
		
		if($bind['LotoPrizeDetailId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['PrizeRate']<0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['StartTime']<0)
		{
			$response = array('errno' => 4);
		}
		elseif($bind['EndTime']=='')
		{
			$response = array('errno' => 2);
		}	
		elseif($bind['LotoPrizeCount']<0)
		{
			$response = array('errno' => 5);
		}	
		else
		{	
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//添加奖品填写配置页面
	public function addDetailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$LotoPrizeId = abs(intval($this->request->LotoPrizeId));
		include $this->tpl('Loto_Prize_Detail_add');
	}
	//添加新奖品
	public function insertDetailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('StartTime','EndTime','PrizeRate','LotoPrizeId','LotoPrizeCount');
		$bind['StartTime'] = strtotime($bind['StartTime']);
		$bind['EndTime'] = strtotime($bind['EndTime']);
		$LotoInfo = $this->oPrize->getRow($bind['LotoPrizeId']);
		$bind['LotoId'] = $LotoInfo['LotoId'];
		$res = $this->oPrize->insertDetail($bind);

		if($bind['LotoPrizeId']==0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['PrizeRate']<0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['StartTime']<0)
		{
			$response = array('errno' => 4);
		}
		elseif($bind['EndTime']=='')
		{
			$response = array('errno' => 2);
		}
		elseif($bind['LotoPrizeCount']<0)
		{
			$response = array('errno' => 5);
		}		
		else
		{	
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//删除奖品
	public function deleteDetailAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$LotoPrizeDetailId = intval($this->request->LotoPrizeDetailId);
		$this->oPrize->deleteDetail($LotoPrizeDetailId);
		$this->response->goBack();
	}
}

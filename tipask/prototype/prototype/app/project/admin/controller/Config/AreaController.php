<?php
/**
 * 地区管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: AreaController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_AreaController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/area';
	/**
	 * Area对象
	 * @var object
	 */
	protected $oArea;
	protected $Abroad;
	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oArea = new Config_Area();

		$this->Abroad = array(1=>'国内',2=>'国外');

		$totalArea = $this->oArea->getAll();

		foreach($totalArea as $AreaId => $value)
		{		
			$value['abroad']=$this->Abroad[$value['is_abroad']];	
			
			$this->AreaList[$AreaId] = $value;
		}
	}
	//地区配置列表页面
	public function indexAction()
	{
		$areaArr = $this->AreaList;
		include $this->tpl('Config_Area_list');
	}
	//添加地区填写配置页面
	public function addAction()
	{
		include $this->tpl('Config_Area_add');
	}
	
	//添加新地区
	public function insertAction()
	{
		$bind=$this->request->from('name','currency_rate','is_abroad');
		$bind['is_abroad'] = $bind['is_abroad']?$bind['is_abroad']:1;

		$res = $this->oArea->insert($bind);

		$response = $res ? array('errno' => 0) : array('errno' => 9);
		echo json_encode($response);
		return true;
	}
	
	//修改地区信息页面
	public function modifyAction()
	{
		$AreaId = $this->request->AreaId;
		$area = $this->oArea->getOne($AreaId);
		include $this->tpl('Config_Area_modify');
	}
	
	//更新地区信息
	public function updateAction()
	{
		$bind=$this->request->from('AreaId','name','currency_rate','is_abroad','currency_rate');

		$res = $this->oArea->update($bind['AreaId'], $bind);

		$response = $res ? array('errno' => 0) : array('errno' => 9);
		echo json_encode($response);
		return true;
	}
	
	//删除地区
	public function deleteAction()
	{
		$AreaId = intval($this->request->AreaId);
		$this->oArea->delete($AreaId);
		$this->response->goBack();
	}
	//根据选择国内/海外获取相应列表
	public function getAreaAction()
	{
		$is_abroad= intval($this->request->is_abroad);
		$AreaList = $this->AreaList;
		$AreaList = $this->oArea->getAbroad($is_abroad,$AreaList);
		if(count($AreaList))
		{
			echo "<option value=0>全部</option>";
			foreach($AreaList as $AreaId => $area_data)
			{
				echo "<option value='{$AreaId}'>{$area_data['name']}</option>";
			}
		}
	}
}

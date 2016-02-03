<?php
/**
 * 产品类型管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: TypeController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Product_TypeController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/product/type';
	/**
	 * ProductType对象
	 * @var object
	 */
	protected $oProductType;
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oProductType = new Config_Product_Type();
		$this->oApp = new Config_App();

	}
	//产品类型配置列表页面
	public function indexAction()
	{
		$AppList = $this->oApp->getAll('name,AppId');
		$AppId = $this->request->AppId;
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		$ProductTypeArr = $this->oProductType->getAll($AppId);
		if($ProductTypeArr)
		{
			foreach($ProductTypeArr as $App => $AppData)
			{
				foreach($AppData as $ProductTypeId => $ProductTypeData)
				$ProductTypeArr[$App][$ProductTypeId]['AppName'] = $AppList[$App]['name'];
			}
		}
		include $this->tpl('Config_Product_Type_list');
	}
	//添加产品类型填写配置页面
	public function addAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Product_Type_add');
	}
	
	//添加新产品类型
	public function insertAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		$bind=$this->request->from('name','AppId','ProductTypeId');


		if($bind['ProductTypeId']<0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['AppId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oProductType->insert($bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改产品类型信息页面
	public function modifyAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$ProductTypeId = $this->request->ProductTypeId;
		$AppId = $this->request->AppId;
		$ProductType = $this->oProductType->getRow($ProductTypeId,$AppId,'*');
		$AppList = $this->oApp->getAll('name,AppId');
		include $this->tpl('Config_Product_Type_modify');
	}
	
	//更新产品类型信息
	public function updateAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$bind=$this->request->from('ProductTypeId','name','AppId');

		if($bind['ProductTypeId']<0)
		{
			$response = array('errno' => 3);
		}
		elseif($bind['AppId']==0)
		{
			$response = array('errno' => 1);
		}
		elseif($bind['name']=='')
		{
			$response = array('errno' => 2);
		}	
		else
		{	
			$res = $this->oProductType->update($bind['ProductTypeId'],$this->request->oldAppId, $bind);
			$response = $res ? array('errno' => 0,'AppId'=>$bind['AppId']) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除产品类型
	public function deleteAction()
	{
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		$ProductTypeId = intval($this->request->ProductTypeId);
		$AppId = intval($this->request->AppId);
		$this->oProductType->delete($ProductTypeId,$AppId);
		$this->response->goBack();
	}
    
    public function getProductTypeAction()
	{
		$AppId = intval($this->request->AppId)?intval($this->request->AppId):0;
		$ProductTypeArr = $this->oProductType->getAll($AppId);

		echo "<option value=''>全部</option>";
		if(is_array($ProductTypeArr[$AppId]))
		{
			foreach ($ProductTypeArr[$AppId] as $ProductTypeId => $data)
			{
					echo "<option value='{$ProductTypeId}'>{$data['name']}</option>";

			}
		}
	}
}

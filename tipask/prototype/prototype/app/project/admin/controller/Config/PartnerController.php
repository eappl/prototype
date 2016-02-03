<?php
/**
 * 合作商管理
 * @author chen<cxd032404@hotmail.com>
 * $Id: PartnerController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_PartnerController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/partner';

	/**
	 * App对象
	 * @var object
	 */
	protected $oPartner;
	protected $oApp;
	protected $oPartnerApp;
	protected $oServer;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oApp = new Config_App();
		$this->oPartner = new Config_Partner();
		$this->oServer = new Config_Server();
		$this->oPartnerApp = new Config_Partner_App();

	}

	/**
	 * 合作商列表
	 * @return unknown_type
	 */
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "合作商管理\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		$partner = intval($this->request->partner_type);
		$oPartnerArr = $this->oPartner->getAll();
		$oPartnerArr = $this->oPartner->getPartner($partner,$oPartnerArr);

		foreach ($oPartnerArr as $k=>$v) {
			$oPartnerArr[$k]['notes'] = json_decode($v['notes'],true);
		}
		include $this->tpl();
	}

	/**
	 * 添加合作商
	 * @return unknown_type
	 */
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "合作商添加\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		include $this->tpl();
	}

	/**
	 * 将数据入库
	 * @return unknown_type
	 */
	public function insertAction()
	{
		/**
		 * 记录日志
		 */
		$log = "合作商添加入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$bind = $this->request->from('name',  'notes');

		if(!empty($this->request->newid))
			$bind['PartnerId']=$this->request->newid;

		if (!empty($bind['notes'])) {
			$bind['notes'] = json_encode($bind['notes']);
		}

		if (empty($bind['name'])) {
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		}

		$res = $this->oPartner->insert($bind);

		if($res)
		{
			$response=array('errno' => 0);
			$this->oPartner->reBuildPartnerConfig();
		}
		else
		{
		 	$response=array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}

	/**
	 * 删除数据
	 * @return unknown_type
	 */
	public function deleteAction()
	{
		/**
		 * 记录日志
		 */
		$log = "合作商删除\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		
		$PartnerId = intVal($this->request->PartnerId);
		if(!$PartnerId)
		{
			$this->response->goBack();
		}
		$this->oPartner->delete($PartnerId);
		$this->oPartner->reBuildPartnerConfig();
		$this->response->goBack();
	}

	/**
	 * 修改页面
	 * @return unknown_type
	 */
	public function modifyAction()
	{
		/**
		 * 记录日志
		 */
		$log = "合作商修改\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$PartnerId = $this->request->PartnerId;
		$partner = $this->oPartner->getRow($PartnerId);

		if (!empty($partner['notes'])) {
			$partner['notes'] = json_decode($partner['notes'],true);
		}

		include $this->tpl();
	}

	/**
	 * 更新数据
	 * @return boolean
	 */
	public function updateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "合作商修改入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$bind = $this->request->from('name','notes');
		$PartnerId = $this->request->PartnerId;

		$bind['notes'] = json_encode($bind['notes']);

		if (empty($bind['name'])) {
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		}
		
		$res = $this->oPartner->update($PartnerId, $bind);
		if($res)
		{
			$response=array('errno' => 0);
			$this->oPartner->reBuildPartnerConfig();
		}
		else
		{
		 	$response=array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
}

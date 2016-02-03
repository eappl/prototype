<?php
/**
 * 合作商运营游戏
 * @author Chen <cxd032404@hotmail.com>
 * $Id: AppController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Partner_AppController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = "?ctl=config/partner/app";

	/**
	 * 对象变量
	 * @var object
	 */
	protected $oArea;
	protected $oApp;
	protected $oPartner;
	protected $oPartnerApp;
	protected $oServer;

	/**
	 * 初始化对象
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oArea = new Config_Area();
		$this->oApp = new Config_App();
		$this->oPartner = new Config_Partner();
		$this->oPartnerApp = new Config_Partner_App();
		$this->oServer = new Config_Server();
	}

	/**
	 * 运营游戏列表
	 * @return unknown_type
	 */
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "运营游戏管理\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		$AppId= $this->request->AppId;
		$PartnerId= $this->request->PartnerId;
		$appArr = $this->oApp->getAll('AppId,name');
		if (!empty($AppId)) 
			$oPartnerAppArr = $this->oPartnerApp->getAppAll($AppId);
		else if(!empty($PartnerId))
			$oPartnerAppArr = $this->oPartnerApp->getPartnerAll($PartnerId);
		else 
			$oPartnerAppArr = $this->oPartnerApp->getAll();

		if (!empty($oPartnerAppArr)) {
			foreach ($oPartnerAppArr as $k =>$v )
			{
				$oPartnerAppArr[$k]['product_name'] = $appArr[$v['AppId']]['name'];
			}
		}
		include $this->tpl();
	}

	/**
	 * 添加运营游戏
	 * @return unknown_type
	 */
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "运营游戏添加\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$oAppArr = $this->oApp->getAll('AppId,name');
		$oPratnerArr = $this->oPartner->getAll('PartnerId,name');
		$A = $this->oArea->getAll();
		foreach($A as $Key => $value)
		{
			$AreaList[$value['AreaId']] = $value['name'];
		}
		$pay_key = md5(rand(10000,99999));
		$AppId = $this->request->AppId;
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
		$log = "运营游戏添加入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$bind = $this->request->from('PartnerId', 'AppId','AreaId', 'income_type', 'income_rate',  'game_site', 'coin_rate','IsActive');
		//名称验证
		if (empty($bind['PartnerId']) || $bind['PartnerId'] == 'partner'){
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}else{
			$PartnerInfo = $this->oPartner->getRow($bind['PartnerId']);
			$bind['LoginStart'] = strtotime($this->request->LoginStart);
			$bind['NextEnd'] = strtotime($this->request->NextEnd);
			$bind['NextStart'] = strtotime($this->request->NextStart);
			
			$bind['comment'] = $PartnerInfo['notes'];
			$bind['name'] = $PartnerInfo['name'];
		}

		//收入分成比例
		if (empty($bind['income_rate'])){
			$response = array('errno' => 5);
			echo json_encode($response);
			return false;
		}

		if ($bind['income_type']==2 || $bind['income_type']==3){
			$tmp_rate = explode(",", $bind['income_rate']);
			$tmp_num = count($tmp_rate);
			if ($tmp_num < 2){
				$response = array('errno' => 6);
				echo json_encode($response);
				return false;
			}
		}
		$res = $this->oPartnerApp->insert($bind);
		if($res)
		{
			$response = array('errno' => 0, 'app' => $bind['AppId']);
			$this->oPartnerApp->reBuildPartnerAppConfig();
		}
		else
		{
		 	$response = array('errno' => 9);
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
		$log = "运营游戏删除\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		
		$PartnerId = $this->request->PartnerId;
		$AppId = $this->request->AppId;
		if (empty($PartnerId) || empty($AppId))
		{
			$this->response->goBack();
		}
		$bind = array($PartnerId,$AppId);
		$this->oPartnerApp->delete($bind);
		$this->oPartnerApp->reBuildPartnerAppConfig();
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
		$log = "运营游戏修改\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$productArr = $this->oApp->getAll('AppId,name');
		$PartnerId = $this->request->PartnerId;
		$AppId = $this->request->AppId;
		$bind = array($PartnerId,$AppId);
		$partner = $this->oPartnerApp->getRow($bind);
		$partner['LoginStart'] = date('Y-m-d H:i:s',$partner['LoginStart']);
		$partner['NextEnd'] = date('Y-m-d H:i:s',$partner['NextEnd']);
		$partner['NextStart'] = date('Y-m-d H:i:s',$partner['NextStart']);
		$partnerArr = $this->oPartner->getAll('PartnerId,name');
		$A = $this->oArea->getAll();
		foreach($A as $Key => $value)
		{
			$AreaList[$value['AreaId']] = $value['name'];
		}
		include $this->tpl();
	}

	/**
	 * 修改数据
	 * @return unknown_type
	 */
	public function updateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "运营游戏修改入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$bind = $this->request->from('PartnerId', 'AppId', 'AreaId', 'income_type', 'income_rate',  'game_site', 'comment', 'coin_rate','IsActive');

		$PartnerId = $this->request->PartnerId;
		$AppId = $this->request->old_AppId;
		if (empty($PartnerId) || empty($AppId))
		{
				echo json_encode(array('errno' => 6));
				return false;
		}

		$partnerArr = $this->oPartner->getRow($PartnerId);

		$bind['name'] = $partnerArr['name'];
		$bind['LoginStart'] = strtotime($this->request->LoginStart);
		$bind['NextEnd'] = strtotime($this->request->NextEnd);
		$bind['NextStart'] = strtotime($this->request->NextStart);

		//收入分成比例
		if (empty($bind['income_rate'])){
			$response = array('errno' => 5);
			echo json_encode($response);
			return false;
		}
		if ($bind['income_type']==2 || $bind['income_type']==3){
			$tmp_rate = explode(",", $bind['income_rate']);
			$tmp_num = count($tmp_rate);
			if ($tmp_num < 2){
				$response = array('errno' => 6);
				echo json_encode($response);
				return false;
			}
		}
		$parame = array($PartnerId, $AppId);
		$res = $this->oPartnerApp->update($parame, $bind);
		if($res)
		{
			$response = array('errno' => 0,'app' => $bind['AppId'],);
			$this->oPartnerApp->reBuildPartnerAppConfig();
		}
		else
		{
		 	$response = array('errno' => 9);
		}
		
		echo json_encode($response);
		return true;
	}
	
	/**
	 * 按产品取得运营商
	 * @author 陈晓东
	 * 
	 */
	public function partnerByAppAction()
	{
		$Config_Partner_App = new Config_Partner_App();
		$rows = $Config_Partner_App->getByApp($this->request->AppId);
		echo "<option value=''>--全部--</option>";
		foreach ($rows as $k => $v)
		{
			if($this->request->partner!=$v['PartnerId'])
				echo "<option value='{$v['PartnerId']}'>{$v['name']}</option>";
			else
				echo "<option value='{$v['PartnerId']}' selected>{$v['name']}</option>";
		}
	}
	
	
}

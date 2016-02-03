<?php
/**
 * 游戏区服管理
 * @author chen<cxd032404@hotmail.com>
 * $Id: ServerController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_ServerController extends AbstractController
{
	/**
	 * 权限限制
	 * @var unknown_type
	 */
	protected $sign = '?ctl=config/server';
	/**
	 * App对象
	 * @var object
	 */
	protected $oServer;
	protected $oApp;
	protected $oPartner;
	protected $oPartnerApp;

	/**
	 * 初始化对象
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oServer = new Config_Server();
		$this->oApp = new Config_App();
		$this->oPartner	= new Config_Partner();
		$this->oPartnerApp = new Config_Partner_App();
	}

	/**
	 * 区服列表
	 * @return unknown_type
	 */
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "区服管理\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		$AppId= $this->request->AppId;
		$PartnerId=$this->request->PartnerId;
		
		$appArr = $this->oApp->getAll('AppId,name');
		$rows = $this->oPartnerApp->getAppAll($AppId);
		$partnerArr = $this->oPartner->getAll('PartnerId,name');

		$param=array();
		if(!empty($AppId))
		{
			$param['AppId']=$AppId;
		}
		
		if(!empty($PartnerId))
		{
			$param['PartnerId']=$PartnerId;
		}
		
		$serverArr = $this->oServer->getByParam($param);
		foreach ($serverArr as $k => $v){
			//检查配置
			foreach ($appArr as $appKey => $appValue){
				if ( $appValue['AppId'] == $v['AppId']){
					if($this->request->by=='app')
						$serverArr[$k]['app_name'] = $appValue['name']." | <a href=\"$this->sign\">全部</a>";
					else
						$serverArr[$k]['app_name'] = "<a href=\"$this->sign&by=app&AppId={$appValue['AppId']}\">{$appValue['name']}</a>";

				}
			}
			foreach ($partnerArr as $partnerKey => $partnerValue){
				if ( $partnerValue['PartnerId'] == $v['PartnerId']){
					if($this->request->by=='partner')
						$serverArr[$k]['partner_name'] = $partnerValue['name']." | <a href=\"$this->sign\">全部</a>";
					else
						$serverArr[$k]['partner_name'] = "<a href=\"$this->sign&by=partner&PartnerId={$partnerValue['PartnerId']}\">{$partnerValue['name']}</a>";
				}
			}
			$serverArr[$k]['LoginStart'] = date('Y-m-d H:i:s', $v['LoginStart']);
			$serverArr[$k]['NextStart'] = date('Y-m-d H:i:s', $v['NextStart']);
			$serverArr[$k]['NextEnd'] = date('Y-m-d H:i:s', $v['NextEnd']);
			$serverArr[$k]['PayStart'] = date('Y-m-d H:i:s', $v['PayStart']);
			$serverArr[$k]['PayEnd'] = date('Y-m-d H:i:s', $v['PayEnd']);
			$serverArr[$k]['ServerIp'] = long2ip($v['ServerIp']);
			$serverArr[$k]['GMIp'] = long2ip($v['GMIp']);
		}
		include $this->tpl();
	}

	/**
	 * 添加区服页面
	 * @return unknown_type
	 */
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "区服添加\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$ServerId = null;
		$appArr = $this->oApp->getAll('AppId,name');
		$partnerArr = $this->oPartner->getAll('PartnerId,name');
		$nowtime = date('Y-m-d H:i:s',time());
		$AppId = $this->request->AppId;
		$PartnerId = $this->request->PartnerId;
		$serverArr = $this->oServer->getByAppPartner($AppId, $PartnerId, 'ServerId',0);
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
		$log = "区服添加入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$bind=$this->request->from('ServerId','name','AppId','PartnerId','LoginStart','NextStart','NextEnd','PayStart','PayEnd','ServerIp','SocketPort','ServerSocketPort','IpListWhite','IpListBlack','is_show','GMIp','GMSocketPort');

		$bind['LoginStart'] = strtotime($this->request->LoginStart);
		$bind['NextEnd'] = strtotime($this->request->NextEnd);
		$bind['NextStart'] = strtotime($this->request->NextStart);
		
		$bind['PayEnd'] = strtotime($this->request->PayEnd);
		$bind['PayStart'] = strtotime($this->request->PayStart);
		$bind['ServerIp'] = Base_Common::ip2long($bind['ServerIp']);
 		$bind['GMIp'] = Base_Common::ip2long($bind['GMIp']);


        
        if(!empty($bind['IpListWhite']))
        {
            $t = explode(',',$bind['IpListWhite']);
            foreach($t as $key => $value)
            {
            	$Comment['IpListWhite'][Base_Common::ip2long(trim($value))] = 1;	
            }
            ksort($Comment['IpListWhite']);
        }
        if(!empty($bind['IpListBlack']))
        {
            $t = explode(',',$bind['IpListBlack']);
            foreach($t as $key => $value)
            {
            	$Comment['IpListBlack'][Base_Common::ip2long(trim($value))] = 1;	
            }
            ksort($Comment['IpListBlack']);
        }
	    $bind['Comment'] = json_encode($Comment);
        unset($bind['IpListBlack'],$bind['IpListWhite']);

		//区服ID
		if (empty($bind['ServerId'])){
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}

		//停服时间
		if(!empty($bind['NextStart']) &&  !empty($bind['NextEnd']))
		{
			if ($bind['NextStart'] <= $bind['NextEnd'])
			{
				echo json_encode(array('errno' => 3));
				return false;
			}
		}
		//支付时间
		if(!empty($bind['PayStart']) &&  !empty($bind['PayEnd']))
		{
			if ($bind['PayStart'] <= $bind['PayEnd'])
			{
				echo json_encode(array('errno' => 4));
				return false;
			}
		}
		$res = $this->oServer->insert($bind);
		if($res)
		{
			$response = array('errno' => 0,'app' => $bind['AppId'],'partner' => $bind['PartnerId']);
			$this->oServer->reBuildServerConfig();
		}
		else
		{
		 	$response = array('errno' => 9);
		}
		
		echo json_encode($response);
		return true;
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
		$log = "区服修改\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$ServerId = $this->request->ServerId;
		$server = $this->oServer->getRow($ServerId);
		$appArr = $this->oApp->getAll('AppId,name');
		$partnerArr = $this->oPartner->getAll('PartnerId,name');

		$server['LoginStart'] = date('Y-m-d H:i:s',$server['LoginStart']);
		$server['NextEnd'] = date('Y-m-d H:i:s',$server['NextEnd']);
		$server['NextStart'] = date('Y-m-d H:i:s',$server['NextStart']);
		$server['PayEnd'] = date('Y-m-d H:i:s',$server['PayEnd']);
		$server['PayStart'] = date('Y-m-d H:i:s',$server['PayStart']);
		$server['ServerIp'] = long2ip($server['ServerIp']);
		$server['GMIp'] = long2ip($server['GMIp']);        
        
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
		$log = "区服修改入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$bind=$this->request->from('ServerId','name','AppId','PartnerId','LoginStart','NextStart','NextEnd','PayStart','PayEnd','ServerIp','SocketPort','ServerSocketPort','IpListWhite','IpListBlack','is_show','GMIp','GMSocketPort');

		$bind['LoginStart'] = strtotime($this->request->LoginStart);
		$bind['NextEnd'] = strtotime($this->request->NextEnd);
		$bind['NextStart'] = strtotime($this->request->NextStart);
		
		$bind['PayEnd'] = strtotime($this->request->PayEnd);
		$bind['PayStart'] = strtotime($this->request->PayStart);
		$bind['ServerIp'] = Base_Common::ip2long($bind['ServerIp']);
 		$bind['GMIp'] = Base_Common::ip2long($bind['GMIp']);
        if(!empty($bind['IpListWhite']))
        {
            $t = explode(',',$bind['IpListWhite']);
            foreach($t as $key => $value)
            {
            	$Comment['IpListWhite'][Base_Common::ip2long(trim($value))] = 1;	
            }
            ksort($Comment['IpListWhite']);
        }
        if(!empty($bind['IpListBlack']))
        {
            $t = explode(',',$bind['IpListBlack']);
            foreach($t as $key => $value)
            {
            	$Comment['IpListBlack'][Base_Common::ip2long(trim($value))] = 1;	
            }
            ksort($Comment['IpListBlack']);
        }
	    $bind['Comment'] = json_encode($Comment);
        unset($bind['IpListWhite'],$bind['IpListBlack']);
		//名称
		if (empty($bind['name'])){
			echo json_encode(array('errno' => 2));
			return false;
		}

		//停服时间
		if(!empty($bind['NextStart']) &&  !empty($bind['NextEnd']))
		{
			if ($bind['NextStart'] <= $bind['NextEnd'])
			{
				echo json_encode(array('errno' => 3));
				return false;
			}
		}
		//支付时间
		if(!empty($bind['PayStart']) &&  !empty($bind['PayEnd']))
		{
			if ($bind['PayStart'] <= $bind['PayEnd'])
			{
				echo json_encode(array('errno' => 4));
				return false;
			}
		}

		$res = $this->oServer->update($this->request->old_ServerId, $bind);
		if($res)
		{
			$response=array('errno' => 0,'app' => $bind['AppId'],'partner' => $bind['PartnerId']);
			$this->oServer->reBuildServerConfig();
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
		$log = "区服删除\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		
		$ServerId = intval($this->request->ServerId);
		$this->oServer->delete($ServerId);
		$this->oServer->reBuildServerConfig();
		$this->response->goBack();
	}

	/**
	 * 游戏列表
	 * @return unknown_type
	 */
	public function appByAllAction()
	{
		$rows = $this->oApp->getAll();
		echo "<option value=''>--全部--</option>";
		foreach ($rows as $k => $v)
		{
			if($this->request->all!=$v['AppId'])
				echo "<option value='{$v['AppId']}'>{$v['name']}</option>";
			else
				echo "<option value='{$v['AppId']}' selected>{$v['name']}</option>";
		}
	}

	/**
	 * 运营游戏列表
	 */
	public function partnerByAppAction()
	{
		$rows = $this->oPartnerApp->getAppAll($this->request->AppId);
		echo "<option value=''>--全部--</option>";
		foreach ($rows as $k => $v)
		{
			if($this->request->partner!=$v['PartnerId'])
				echo "<option value='{$v['PartnerId']}'>{$v['name']}</option>";
			else
				echo "<option value='{$v['PartnerId']}' selected>{$v['name']}</option>";
		}
	}

	/**
	 * 区服列表
	 * @return unknown_type
	 */
	public function serverByPartnerAction()
	{
		$rows = $this->oServer->getByPartner($this->request->PartnerId);
		echo "<option value=''>--全部--</option>";
		foreach ($rows as $k => $v)
		{
			echo "<option value='{$v['ServerId']}'>{$v['name']}:{$v['server_url']}</option>";
		}
	}

	/**
	 * 根据APP与Partner获取区服列表
	 * @return unknown_type
	 */
	public function serverByAppPartnerAction()
	{
		$app=$this->request->AppId;
		$partner=$this->request->PartnerId;
		$rows = $this->oServer->getByAppPartner($app,$partner);
		echo "<option value=''>--全部--</option>";
		foreach ($rows as $k => $v)
		{
			echo "<option value='{$v['ServerId']}'>{$v['name']}:{$v['server_url']}</option>";
		}
	}

}

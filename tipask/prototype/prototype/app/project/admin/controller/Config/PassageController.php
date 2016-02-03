<?php
/**
 * 支付渠道管理
 * @author chenxd
 * $Id: PassageController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_PassageController extends AbstractController
{

	/**
	 * 权限标识
	 * @var sting
	 */
	protected $sign = "?ctl=config/passage";

	public function init()
	{
		parent::init();
		$this->oPassage = new Config_Passage();
	}

	/**
	 * 列表页
	 */
	public function indexAction()
	{
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);

		$log = "查看支付渠道\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
		
		$passageArr = $this->oPassage->getAll();
		foreach ($passageArr as $key=>$value){
			$passageArr[$key]['kindname'] = $this->config->kindDefault[$value['kind']];
		}
		include $this->tpl("Config_Passage_index");
	}

	/**
	 * 添加
	 */
	public function addAction()
	{
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		include $this->tpl("Config_Passage_add");
	}

	/**
	 * 插入
	 */
	public function insertAction()
	{
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);

		$bind = $this->request->from('name','passage','passage_rate','finance_rate','kind','sort','weight','StageUrl','StagePartnerId','StageSecureCode');

		//验证passage
		$bind['passage'] = preg_replace('/[^a-z0-9_]/i', '', $bind['passage']);
		if (strlen($bind['passage']) < 2 || strlen($bind['passage']) > 8) {
			$response = array('errno' => 3);
			echo json_encode($response);
			return false;
		}

		if ($this->oPassage->passageExists($bind['passage'])) {
			$response = array('errno' => 4);
			echo json_encode($response);
			return false;
		}

		//验证name
		if (strlen($bind['name']) < 4 || strlen($bind['name']) > 20) {
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}

		//验证passage_rate
		if (!is_numeric($bind['passage_rate'])) {
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		}
		
		//验证finance_rate
		if (!is_numeric($bind['finance_rate'])) {
			$response = array('errno' => 5);
			echo json_encode($response);
			return false;
		}


		$res = $this->oPassage->insert($bind);

		$response = $res ? array('errno' => 0) : array('errno' => 9);
		echo json_encode($response);
		return true;
	}
	/**
	 * 修改
	 */
	public function modifyAction()
	{
		$this->manager->checkMenuPermission($this->sign,Widget_Manager::MENU_PURVIEW_UPDATE);

		$passage = $this->oPassage->getRow($this->request->passage_id);
		include $this->tpl("Config_Passage_modify");
	}
	/**
	 * UPDATE
	 */
	public function updateAction()
	{
		$this->manager->checkMenuPermission($this->sign,Widget_Manager::MENU_PURVIEW_UPDATE);

		$passage_id = $this->request->passage_id;
		$bind = $this->request->from('name','passage_rate','finance_rate','kind','sort','weight','StageUrl','StagePartnerId','StageSecureCode');

		//验证name
		if (strlen($bind['name']) < 4 || strlen($bind['name']) > 80) {
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}

		//验证passage_rate
		if (!is_numeric($bind['passage_rate'])) {
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		}
		
		//验证finance_rate
		if (!is_numeric($bind['finance_rate'])) {
			$response = array('errno' => 3);
			echo json_encode($response);
			return false;
		}

		$res = $this->oPassage->update($passage_id, $bind);
		$response = $res ? array('errno' => 0) : array('errno' => 9);

		echo json_encode($response);
		return true;

	}
	/**
	 * 删除
	 */
	public function deleteAction()
	{
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);

		$this->oPassage->delete($this->request->passage_id);
		$this->response->goBack();
	}
}
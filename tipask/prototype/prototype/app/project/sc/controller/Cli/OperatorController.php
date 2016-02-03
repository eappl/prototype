<?php
class Cli_OperatorController extends Base_Controller_Action{
    
	public function init()
	{
		parent::init();
		$this->oOperator = new Kubao_Operator();
		$this->oCommon = new Kubao_Common();
	}
	//分单循环
    public function updateOperatorAction()
    {
		sleep(1);
		$this->oCommon->addSystemLog(0,"system","system",20,"系统自动同步客服信息开始");//系统操作日志
		//获取客服列表
		$OperatorList = $this->oOperator->getAllOperator('login_name',array());
		$n = 0;
		foreach($OperatorList as $key => $OperatorInfo)
		{
			$Rebuild = $this->oOperator->RebuildOperator($OperatorInfo['login_name']);
			if($Rebuild)
			{
				$n++;
				$this->oCommon->addSystemLog(0,"system","system",20,"系统成功同步客服".$OperatorInfo['login_name']."信息");	
			}
		}
		$this->oCommon->addSystemLog(0,"system","system",20,"系统成功同步客服共计".$n."人信息");	
    }
}
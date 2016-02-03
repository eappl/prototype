<?php
/**
 * 通用用户信息控制层
 * @author chen<cxd032404@hotmail.com>
 * $Id: PvController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class PvController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;
 	protected $oActive;
 	protected $oPartner;
 	protected $oSecurityAnswer;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oPV = new Lm_PV();
	}

	/**
	 *账号生成
	 */
	public function insertPvAction()
	{
		//基础元素，必须参与验证
		$PV['PageId'] = abs(intval($this->request->PageId));
		$PV['Time'] = abs(intval($this->request->Time));
		$PV['IP'] = $this->request->IP?$this->request->IP:"127.0.0.1";
		$PV['Browser'] = $this->request->Browser;
		$PV['UserSourceId'] = abs(intval($this->request->UserSourceId));
		$PV['UserSourceDetail'] = abs(intval($this->request->UserSourceDetail));
		$PV['UserSourceProjectId'] = abs(intval($this->request->UserSourceProjectId));
		$PV['UserSourceActionId'] = abs(intval($this->request->UserSourceActionId));
		$PV['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($PV,$p_sign);
		//不参与验证的元素
	  	$PV['IP'] = Base_Common::ip2long($PV['IP']);

		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($PV['Time']-time())<=600)
			{
				unset($PV['ReturnType']);
				if(($PV['UserSourceId']==9)&&($PV['UserSourceDetail']==24))
                {
                    $insertLog = true;
                }
                else
                {
                   $insertLog = $this->oPV->insertPvLog($PV); 
                }               
				if($insertLog)
				{
					$result = array('return'=>1,'comment'=>"记录成功");	
				}
				else
				{
				 	$result = array('return'=>0,'comment'=>"记录失败");	
				}
				
			}
			else
			{
				$result = array('return'=>2,'comment'=>"时间有误");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		$PV['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($PV['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
}

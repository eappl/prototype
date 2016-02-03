<?php
/**
 * 公告控制层
 * $Id: BroadCastController.php 15498 2014-12-18 09:15:33Z 334746 $
 */

class BroadCastController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oBroadCast;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oBroadCast = new Kubao_BroadCast();
	}
	/**
	 *获取当前正在生效的公告列表
	 */
	public function broadcastAction()
	{
		$BroadCastZoneList = $this->config->BroadCastZoneList;
		//基础元素，必须参与验证
		$BroadCast['Time'] = abs(intval($this->request->Time));
		$BroadCast['Count'] = abs(intval($this->request->Count));
		$BroadCast['BroadCastZone'] = abs(intval($this->request->BroadCastZone));
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($BroadCast,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($BroadCast['Time']-time())<=600)
			{
				if(isset($BroadCastZoneList[$BroadCast['BroadCastZone']]))
				{
					//查询当前正在生效的公告列表
					$oMenCache = new Base_Cache_Memcache("Complaint");
					$M = $oMenCache -> get('CurrentBoradCast_'.$BroadCast['BroadCastZone'].'_'.$BroadCast['Count']);
					if($M)
					{
						$BroadCastList = json_decode($M,true);
					}
					else
					{
						$BroadCastList = $this->oBroadCast->getCurrentBroadCast($BroadCast,"StartTime,EndTime,Content",1);
						$oMenCache -> set('CurrentBoradCast_'.$BroadCast['BroadCastZone'].'_'.$BroadCast['Count'],json_encode($BroadCastList),10);
					}
					$result = array('return'=>1,'BroadCastList'=>$BroadCastList);	
				}
				else
				{
					$result = array('return'=>0,'comment'=>"作用范围有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"时间有误");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
		echo json_encode($result);
	}
	/**
	 *获取当前正在生效的常用问题
	 */
	public function commonQuestionAction()
	{
		//基础元素，必须参与验证
		$Common['Time'] = abs(intval($this->request->Time));
		$Common['Count'] = abs(intval($this->request->Count));
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($Common,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Common['Time']-time())<=600)
			{				
				//查询当前正在生效的公告列表
				$oMenCache = new Base_Cache_Memcache("Complaint");
				$M = $oMenCache -> get('CurrentBoradCast_'.$Common['Count']);
				if($M)
				{
					$CommonQustionList = json_decode($M,true);
				}
				else
				{
					$CommonQustionList = $this->oBroadCast->getCurrentCommonQustion($Common,"url as Url,title as Content");
					$oMenCache -> set('CurrentBoradCast_'.$Common['Count'],json_encode($CommonQustionList),3600);
				}
				$result = array('return'=>1,'CommonQustionList'=>$CommonQustionList);	
			}
			else
			{
				$result = array('return'=>0,'comment'=>"时间有误");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
		echo json_encode($result);
	}
	
}

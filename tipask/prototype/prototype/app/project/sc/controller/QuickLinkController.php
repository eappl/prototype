<?php
/**
 * 公告控制层
 * $Id: BroadCastController.php 425 2012-12-14 06:14:59Z chenxiaodong $
 */

class QuickLinkController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oQuickLink;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oQuickLink = new Kubao_QuickLink();
	}
	/**
	 *获取当前正在生效的公告列表
	 */
	public function quicklinkAction()
	{
		//基础元素，必须参与验证
		$QuickLink['Time'] = abs(intval($this->request->Time));
		$QuickLink['LinkType'] = trim($this->request->LinkType);
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($QuickLink,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($QuickLink['Time']-time())<=600)
			{
				//查询当前正在生效的公告列表
				$oMenCache = new Base_Cache_Memcache("Complaint");
				$M = $oMenCache -> get('QuickLink_'.$QuickLink['LinkType']);
				if($M)
				{
					$ParentQuickLink = json_decode($M,true);
				}
				else
				{
					$ParentQuickLink = $this->oQuickLink->getQuickLinkByType($QuickLink['LinkType'],"LinkName,LinkUrl,LinkIcon,Id");
					if($ParentQuickLink['Id'])
					{
						$ParentQuickLink['QuickLinkList'] = $this->oQuickLink->getQuickLinkByParent($ParentQuickLink['Id'],"LinkName,LinkUrl,LinkIcon,Id");
						$oMenCache -> set('QuickLink_'.$QuickLink['LinkType'],json_encode($ParentQuickLink),3600);
					}
					else
					{
						$result = array('return'=>0,'comment'=>"无此分类");	
					}					
				}
				$result = array('return'=>1,'QuickLinkList'=>$ParentQuickLink);	
				//echo "<pre>";
				//print_R($ParentQuickLink);
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

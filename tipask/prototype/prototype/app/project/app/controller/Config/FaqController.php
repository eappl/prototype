<?php
/**
 * FAQ配置获取控制层
 * $Id: FaqController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_FaqController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oServer;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oFaq = new Config_Faq();
		$this->oFaqType = new Config_Faq_Type();

	}

	/**
	 *获取FAQ列表
	 */
	public function getFaqListAction()
	{

		//基础元素，必须参与验证
		$Config['FaqTypeId'] = abs(intval($this->request->FaqTypeId));
		$Config['Start'] = abs(intval($this->request->Start));
		$Config['Count'] = abs(intval($this->request->Count));
		$Config['Time'] = abs(intval($this->request->Time));
		$Config['KeyWord'] = urldecode(trim($this->request->KeyWord));
		$Config['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;

		//URL验证码
		$sign = trim($this->request->sign);
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = Base_common::check_sign($Config,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Config['Time']-time())<=600)
			{
				$FaqList = $this->oFaq->getAll($Config['FaqTypeId'],$Config['KeyWord'],$Config['Start'],$Config['Count']);
				$FaqCount = $this->oFaq->getFAQCount($Config['FaqTypeId'],$Config['KeyWord']);				
				if(is_array($FaqList))
				{
					$result = array('return'=>1,'FaqList'=>$FaqList,'Count'=>$FaqCount);	
				}
				else
				{
					$result = array('return'=>0,'comment'=>'您所选择的分类下无FAQ问题');					 	
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
		if($Config['ReturnType'])
		{
			echo json_encode($result);
		}
		else
		{
//			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
//			if($result['return']==1)
//			{
//				$r = $r."|".$result['LoginId']."|".$result['adult'];
//			}
//			echo $r;
		}
	}
	/**
	 *获取FAQ分类列表
	 */
	public function getFaqTypeListAction()
	{
		//基础元素， 必须参与验证
		$Config['Time'] = abs(intval($this->request->Time));
		$Config['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;

		//URL验证码
		$sign = trim($this->request->sign);
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = Base_common::check_sign($Config,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Config['Time']-time())<=600)
			{
				$FaqTypeList = $this->oFaqType->getAll();
				if(is_array($FaqTypeList))
				{
					$result = array('return'=>1,'FaqTypeList'=>$FaqTypeList);	
				}
				else
				{
					$result = array('return'=>0,'comment'=>'无问题分类');					 	
					
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
		if($Config['ReturnType'])
		{
			echo json_encode($result);
		}
		else
		{
//			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
//			if($result['return']==1)
//			{
//				$r = $r."|".$result['LoginId']."|".$result['adult'];
//			}
//			echo $r;
		}
	}

	
}

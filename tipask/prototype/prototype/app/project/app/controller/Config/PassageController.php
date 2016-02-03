<?php
/**
 * 支付方式配置获取控制层
 * $Id: PassageController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_PassageController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oApp;
	protected $oPartnerApp;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oApp = new Config_App();
		$this->oPassage = new Config_Passage();
	}

	/**
	 *获取支付方式列表
	 */
	public function getPassageListAction()
	{
		//基础元素，必须参与验证
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
				$PList = $this->oPassage->getAll();
				if(count($PList))
				{
					foreach($PList as $key => $Pvalue)
					{
						$PassageList[$Pvalue['passage']] = $Pvalue;	
					}	
				}
				$result = array('return'=>1,'PassageInfo'=>$PassageList,'comment'=>"时间有误");	
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
	 *获取财付通子支付方式
	 */
	public function getTenpayListAction()
	{
		//基础元素，必须参与验证
		$Config['Time'] = abs(intval($this->request->Time));
		$Config['Start'] = abs(intval($this->request->Start));
		$Config['Count'] = abs(intval($this->request->Count));
		$Config['IsB2b'] = abs(intval($this->request->IsB2b));
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
				$TenPayList = $this->oPassage->getTenPayList($Config['Start'],$Config['Count'],$Config['IsB2b']);
				$result = array('return'=>1,'TenPayList'=>$TenPayList);	
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
	 *获取支付宝子支付方式
	 */
	public function getAlipayListAction()
	{
		//基础元素，必须参与验证
		$Config['Time'] = abs(intval($this->request->Time));
		$Config['Start'] = abs(intval($this->request->Start));
		$Config['Count'] = abs(intval($this->request->Count));
		$Config['IsB2b'] = abs(intval($this->request->IsB2b));
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
				$AliPayList = $this->oPassage->getAliPayList($Config['Start'],$Config['Count'],$Config['IsB2b']);
				$result = array('return'=>1,'AliPayList'=>$AliPayList);	
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
	 *获取支付宝子支付方式
	 */
	public function getKa91ListAction()
	{
		//基础元素，必须参与验证
		$Config['Time'] = abs(intval($this->request->Time));
		$Config['Start'] = abs(intval($this->request->Start));
		$Config['Count'] = abs(intval($this->request->Count));
		$Config['IsB2b'] = abs(intval($this->request->IsB2b));
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
				$Ka91List = $this->oPassage->getKa91List($Config['Start'],$Config['Count'],$Config['IsB2b']);
				$result = array('return'=>1,'91KaList'=>$Ka91List);	
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
	 *获取支付方式列表
	 */
	public function getPassageInfoAction()
	{
		//基础元素，必须参与验证
		$Config['PassageId'] = abs(intval($this->request->PassageId))?abs(intval($this->request->PassageId)):0;
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
				if($Config['PassageId'])
				{
					$PassageInfo = $this->oPassage->getRow($Config['PassageId']);
					if($PassageInfo['passage_id'])
					{
						$result = array('return'=>1,'PassageInfo'=>$PassageInfo);		
					}
					else
					{
					 	$result = array('return'=>0,'comment'=>"无此支付方式");	
					}					
				}
				else
				{
				 	$result = array('return'=>0,'comment'=>"请指定一个支付方式");	
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

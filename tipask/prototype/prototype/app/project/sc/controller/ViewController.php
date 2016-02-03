<?php
/**
 * 问题控制层
 * $Id: QuestionController.php 425 2012-12-14 06:14:59Z chenxiaodong $
 */

class ViewController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oView;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oView = new Kubao_View();
	}
	/**
	 *问题浏览量更新
	 */
	public function pageViewAction()
	{
		//基础元素，必须参与验证
		$View['PageId'] = abs(intval($this->request->PageId));
		$View['ViewIP'] = urldecode(trim($this->request->ViewIP));
		$View['Time'] = abs(intval($this->request->Time));
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($View,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($View['Time']-time())<=600)
			{
				$View['ViewIP'] = Base_Common::ip2long($View['ViewIP']);
				$InsertLog = $this->oView->addViewLog($View);
				if($InsertLog)
				{
					$result = array('return'=>1,'comment'=>"添加成功");
				}
				else
				{
					$result = array('return'=>2,'comment'=>"添加失败");
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
}

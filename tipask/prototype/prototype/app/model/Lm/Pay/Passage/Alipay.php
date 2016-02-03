<?php
/**
 * 支付宝
 * @author 陈晓东 <cxd032404@hotmail.com>
 * $Id: Alipay.php 15195 2014-07-23 07:18:26Z 334746 $
 */



class Lm_Pay_Passage_Alipay 
{

	function createPay($AppInfo,$PartnerInfo,$ServerInfo,$PassageInfo,$OrderInfo,$Pay)
	{
		$comment = json_decode($AppInfo['comment'],true);
		/* 商户号 */
		$partner = $PassageInfo['StagePartnerId'];
		
		/* 密钥 */
		$key = $PassageInfo['StageSecureCode'];
		$subject = "购买".$AppInfo['name'].$comment['coin_name'];	
		$body = "购买".$AppInfo['name']."-".$PartnerInfo['name']."-".$ServerInfo['name'].$OrderInfo['AppCoin'].$comment['coin_name'];			
		
		require ("AliPayClass/alipayto.php");
		//构造纯网关接口

		$alipayService = new AlipayService($aliapy_config,$PassageInfo);
		$params = $alipayService->create_direct_pay_by_user($parameter);
		return $params;
		
	}
	function endPay($PassageInfo,$OrderInfo)
	{
	
		/* 商户号 */
		$partner = $PassageInfo['StagePartnerId'];
		
		/* 密钥 */
		$key = $PassageInfo['StageSecureCode'];
		
		require ("AliPayClass/notify_url.php");
		
		//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($aliapy_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) 
		{	//验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代
			
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
		    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
		    $out_trade_no	= $_REQUEST['out_trade_no'];	    //获取订单号
		    $trade_no		= $_REQUEST['trade_no'];	    	//获取支付宝交易号
		    $total_fee		= $_REQUEST['total_fee'];			//获取总价格
		    if($_REQUEST['trade_status'] == 'TRADE_FINISHED') 
		    {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
						
				//注意：
				//该种交易状态只在两种情况下出现
				//1、开通了普通即时到账，买家付款成功后。
				//2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
		
		        //调试用，写文本函数记录程序运行情况是否正常
		        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		    }
		    else if ($_REQUEST['trade_status'] == 'TRADE_SUCCESS') 
		    {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
						
				//注意：
				//该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
		
		        //调试用，写文本函数记录程序运行情况是否正常
		        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
							if(($OrderInfo['OrderId']==$out_trade_no)&&($total_fee==$OrderInfo['Coin']*100))
							{
								$Pay['OrderId'] = $OrderInfo['OrderId'];
								$Pay['PayUserId'] = $OrderInfo['PayUserId'];
								$Pay['AcceptUserId'] = $OrderInfo['AcceptUserId'];
								$Pay['PassageId'] = $OrderInfo['PassageId'];
								$Pay['AppId'] = $OrderInfo['AppId'];
								$Pay['PartnerId'] = $OrderInfo['PartnerId'];
								$Pay['SubPassageId'] = $OrderInfo['SubPassageId'];
								$Pay['Amount'] = $OrderInfo['Amount'];
								$Pay['Coin'] = $OrderInfo['Coin'];
								$Pay['Credit'] = $OrderInfo['Credit'];
								$Pay['PayIP'] = $OrderInfo['PayIP'];
								$Pay['PayTime'] = $OrderInfo['PayTime'];
								$Pay['UserSourceId'] = $OrderInfo['UserSourceId'];
								$Pay['UserSourceDetail'] = $OrderInfo['UserSourceDetail'];
								$Pay['UserSourceProjectId'] = $OrderInfo['UserSourceProjectId'];
								$Pay['UserSourceActionId'] = $OrderInfo['UserSourceActionId'];
								$Pay['UserRegTime'] = $OrderInfo['UserRegTime'];
								$Pay['PayedTime'] = strtotime($_REQUEST['notify_time']);
								$Pay['StageOrder'] = $trade_no;
								$Pay['comment'] = json_encode(array('buyer_email'=>$_REQUEST['buyer_email'],'buyer_id'=>$_REQUEST['buyer_id']));
								return $Pay;
							}
							else
							{
							 	return false;
							}
		    }

	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
        
			echo "success";		//请不要修改或删除
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else 
		{
		    //验证失败
		    echo "fail";
		
		    //调试用，写文本函数记录程序运行情况是否正常
		    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}


	}
	function checkPay($PassageInfo,$OrderInfo)
	{
 
		/* 商户号 */
		$partner = $PassageInfo['StagePartnerId'];
		
		/* 密钥 */
		$key = $PassageInfo['StageSecureCode'];
					
			//创建查询请求
			$queryReq = new RequestHandler();
			$queryReq->init();
			$queryReq->setKey($key);
			$queryReq->setGateUrl("https://gw.tenpay.com/gateway/normalorderquery.xml");
			$queryReq->setParameter("partner", $partner);
			$queryReq->setParameter("out_trade_no", $OrderInfo['OrderId']);
			
			//通信对象
			$httpClient = new TenpayHttpClient();
			$httpClient->setTimeOut(5);
			//设置请求内容
			$httpClient->setReqContent($queryReq->getRequestURL());
			
			//后台调用
			if($httpClient->call()) 
			{
				//设置结果参数
				$queryRes = new ClientResponseHandler();
				$queryRes->setContent($httpClient->getResContent());
				$queryRes->setKey($key);
				
				//判断签名及结果
				//只有签名正确,retcode为0，trade_state为0才是支付成功
				if($queryRes->isTenpaySign())// && $queryRes->getParameter("retcode") == "0" && $queryRes->getParameter("trade_state") == "0" && $queryRes->getParameter("trade_mode") == "1" ) 
				{
					echo "here";
					
//					//取结果参数做业务处理
//					$out_trade_no = $queryRes->getParameter("out_trade_no");
//					//财付通订单号
//					$transaction_id = $queryRes->getParameter("transaction_id");
//					//金额,以分为单位
//					$total_fee = $queryRes->getParameter("total_fee");
//					//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
//					$discount = $queryRes->getParameter("discount");
//						
//					if($OrderInfo['OrderId']==$out_trade_no)&&($total_fee==$Pay['Coin'])
//					{
//						$Pay['OrderId'] = $OrderInfo['OrderId'];
//						$Pay['PayUserId'] = $OrderInfo['PayUserId'];
//						$Pay['AcceptUserId'] = $OrderInfo['AcceptUserId'];
//						$Pay['PassageId'] = $OrderInfo['PassageId'];
//						$Pay['Amount'] = $OrderInfo['Amount'];
//						$Pay['Coin'] = $OrderInfo['Coin'];
//						$Pay['Credit'] = $OrderInfo['Credit'];
//						$Pay['PayIP'] = $OrderInfo['PayIp'];
//						$Pay['PayTime'] = $OrderInfo['PayTime'];
//						$Pay['PayedTime'] = strtotime($queryRes->getParameter("time_end"));
//						$Pay['StageOrder'] = $transaction_id;
//						$Pay['comment'] = json_encode(array('bank_info'=>'bank_type'!="DEFAULT"?$queryRes->getParameter("bank_type")."|".$queryRes->getParameter("bank_billno"):"DEFAULT"));
//						return $Pay;
//					}
//					else
//					{
//					 	return false;
//					}
//					//------------------------------
//					//处理业务开始
//					//------------------------------
//					
//					//处理数据库逻辑
//					//注意交易单不要重复处理
//					//注意判断返回金额
//					
//					//------------------------------
//					//处理业务完毕
//					//------------------------------
//					echo "success";
					
				} 
				else 
				{
					//错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
					//echo "验证签名失败 或 业务错误信息:trade_state=" . $queryRes->getParameter("trade_state") . ",retcode=" . $queryRes->getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
					echo "1fail";
				}
				
				//获取查询的debug信息,建议把请求、应答内容、debug信息，通信返回码写入日志，方便定位问题
				/*
				echo "<br>------------------------------------------------------<br>";
				echo "http res:" . $httpClient->getResponseCode() . "," . $httpClient->getErrInfo() . "<br>";
				echo "query req:" . htmlentities($queryReq->getRequestURL(), ENT_NOQUOTES, "GB2312") . "<br><br>";
				echo "query res:" . htmlentities($queryRes->getContent(), ENT_NOQUOTES, "GB2312") . "<br><br>";
				echo "query reqdebug:" . $queryReq->getDebugInfo() . "<br><br>" ;
				echo "query resdebug:" . $queryRes->getDebugInfo() . "<br><br>";
				*/
			}
			else 
			{
				//通信失败
				echo "2fail";
				//后台调用通信失败,写日志，方便定位问题
				//echo "<br>call err:" . $httpClient->getResponseCode() ."," . $httpClient->getErrInfo() . "<br>";
			} 						

		
		//获取debug信息,建议把debug信息写入日志，方便定位问题
		//echo $resHandler->getDebugInfo() . "<br>";

	}

	}



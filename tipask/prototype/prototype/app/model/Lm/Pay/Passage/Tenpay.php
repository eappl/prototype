<?php
/**
 * 财付通
 * @author 陈晓东 <cxd032404@hotmail.com>
 * $Id: Tenpay.php 15195 2014-07-23 07:18:26Z 334746 $
 */


require ("TenPayClass/ResponseHandler.class.php");
require ("TenPayClass/RequestHandler.class.php");
require ("TenPayClass/client/ClientResponseHandler.class.php");
require ("TenPayClass/client/TenpayHttpClient.class.php");
class Lm_Pay_Passage_Tenpay extends RequestHandler 
{

	function createPay($AppInfo,$PartnerInfo,$ServerInfo,$PassageInfo,$OrderInfo,$Pay)
	{
		$comment = json_decode($AppInfo['comment'],true);
		/* 商户号 */
		$partner = $PassageInfo['StagePartnerId'];
		
		/* 密钥 */
		$key = $PassageInfo['StageSecureCode'];
					
		/* 创建支付请求对象 */
		$reqHandler = new RequestHandler();
		$reqHandler->init();
		$reqHandler->setKey($key);
		$reqHandler->setGateUrl($PassageInfo['StageUrl']);

		//----------------------------------------
		//设置支付参数 
		//----------------------------------------
		$reqHandler->setParameter("total_fee", $OrderInfo['Amount']*100);  //总金额
		//用户ip
		$reqHandler->setParameter("spbill_create_ip", $Pay['PayIP']);//客户端IP
		$reqHandler->setParameter("return_url", "http://passport.limaogame.com/?d=paycenter&c=payments&m=TenpayResponse");//支付成功后返回
		$reqHandler->setParameter("partner", $partner);
		$reqHandler->setParameter("out_trade_no", $OrderInfo['OrderId']);
		$reqHandler->setParameter("notify_url", "http://payment.limaogame.com/?ctl=pay&ac=payed");

		$reqHandler->setParameter("body", ("购买".$AppInfo['name']."-".$PartnerInfo['name']."-".$ServerInfo['name'].$OrderInfo['AppCoin'].$comment['coin_name']));
		$reqHandler->setParameter("bank_type", $Pay['SubPassageId']);  	  //银行类型，默认为财付通
		$reqHandler->setParameter("fee_type", "1");               //币种
		//系统可选参数
		$reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
		$reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
		$reqHandler->setParameter("input_charset", "UTF-8");   	  //字符集
		$reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号
		
		//业务可选参数
		$reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
		$reqHandler->setParameter("product_fee", "");        	  //商品费用
		$reqHandler->setParameter("transport_fee", "");      	  //物流费用
		$reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
		$reqHandler->setParameter("time_expire", "");             //订单失效时间
		
		$reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
		$reqHandler->setParameter("goods_tag", "");               //商品标记
		
		
		
		
		//请求的URL
		$reqUrl =($reqHandler->getRequestURL());
//		if ( substr($reqUrl, 0, 3)=="\xEF\xBB\xBF")
//		           $reqUrl=substr_replace($reqUrls, '', 0, 3);		
			
		$params = $reqHandler->getAllParameters();
//		foreach($params as $k => $v) {
//
//			$req_form .= ("<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n");
//		}		
		return $params;

	}
	function endPay($PassageInfo,$OrderInfo)
	{
	
		/* 商户号 */
		$partner = $PassageInfo['StagePartnerId'];
		
		/* 密钥 */
		$key = $PassageInfo['StageSecureCode'];
		
		
		/* 创建支付应答对象 */
		$resHandler = new ResponseHandler();
		$resHandler->setKey($key);
		
		//判断签名
		if($resHandler->isTenpaySign()) 
		{
			
			//通知id
			$notify_id = $resHandler->getParameter("notify_id");
			//通过通知ID查询，确保通知来至财付通
			//创建查询请求
			$queryReq = new RequestHandler();
			$queryReq->init();
			$queryReq->setKey($key);
			$queryReq->setGateUrl("https://gw.tenpay.com/gateway/verifynotifyid.xml");
			$queryReq->setParameter("partner", $partner);
			$queryReq->setParameter("notify_id", $notify_id);
			
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
				//echo "sign:".$queryRes->isTenpaySign().",retcode:".$queryRes->getParameter("retcode").",trade_state:".$queryRes->getParameter("trade_state").",trade_mode:".$queryRes->getParameter("trade_mode")."<br>";
				if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $queryRes->getParameter("trade_state") == "0" && $queryRes->getParameter("trade_mode") == "1" ) 
				{
					//取结果参数做业务处理
					$out_trade_no = $queryRes->getParameter("out_trade_no");
					//财付通订单号
					$transaction_id = $queryRes->getParameter("transaction_id");
					//金额,以分为单位
					$total_fee = $queryRes->getParameter("total_fee");
					//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
					$discount = $queryRes->getParameter("discount");
					if(($OrderInfo['OrderId']==$out_trade_no)&&($total_fee==$OrderInfo['Coin']*100))
					{
						$Pay['OrderId'] = $OrderInfo['OrderId'];
						$Pay['PayUserId'] = $OrderInfo['PayUserId'];
						$Pay['AcceptUserId'] = $OrderInfo['AcceptUserId'];
						$Pay['AppId'] = $OrderInfo['AppId'];
						$Pay['PartnerId'] = $OrderInfo['PartnerId'];
						$Pay['PassageId'] = $OrderInfo['PassageId'];
						$Pay['SubPassageId'] = $OrderInfo['SubPassageId'];
						$Pay['Amount'] = $OrderInfo['Amount'];
						$Pay['Coin'] = $OrderInfo['Coin'];
						$Pay['Credit'] = $OrderInfo['Credit'];
						$Pay['PayIP'] = $OrderInfo['PayIP'];
						$Pay['PayTime'] = $OrderInfo['PayTime'];
						$Pay['PayedTime'] = strtotime($queryRes->getParameter("time_end"));
						
						$Pay['UserSourceId'] = $OrderInfo['UserSourceId'];
						$Pay['UserSourceDetail'] = $OrderInfo['UserSourceDetail'];
						$Pay['UserSourceProjectId'] = $OrderInfo['UserSourceProjectId'];
						$Pay['UserSourceActionId'] = $OrderInfo['UserSourceActionId'];
						$Pay['UserRegTime'] = $OrderInfo['UserRegTime'];
						
						$Pay['StageOrder'] = $transaction_id;
						$Pay['comment'] = json_encode(array('bank_info'=>'bank_type'!="DEFAULT"?$queryRes->getParameter("bank_type")."|".$queryRes->getParameter("bank_billno"):"DEFAULT"));
						return $Pay;
					}
					else
					{
					 	return false;
					}
					//------------------------------
					//处理业务开始
					//------------------------------
					
					//处理数据库逻辑
					//注意交易单不要重复处理
					//注意判断返回金额
					
					//------------------------------
					//处理业务完毕
					//------------------------------
					echo "success";
					
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
		} 
		else 
		{
			//回调签名错误
			echo "3fail";
			//echo "<br>签名失败<br>";
		}
		
		//获取debug信息,建议把debug信息写入日志，方便定位问题
		//echo $resHandler->getDebugInfo() . "<br>";

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
				if($queryRes->isTenpaySign() && $queryRes->getParameter("trade_state") == "0")// && $queryRes->getParameter("retcode") == "0" && $queryRes->getParameter("trade_state") == "0" && $queryRes->getParameter("trade_mode") == "1" ) 
				{					
				    return true;	
				} 
				else 
				{
					//错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
					//echo "验证签名失败 或 业务错误信息:trade_state=" . $queryRes->getParameter("trade_state") . ",retcode=" . $queryRes->getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
					return false;
				}
				

			}
			else 
			{
				//通信失败
				return false;
				//后台调用通信失败,写日志，方便定位问题
				//echo "<br>call err:" . $httpClient->getResponseCode() ."," . $httpClient->getErrInfo() . "<br>";
			} 						

		
		//获取debug信息,建议把debug信息写入日志，方便定位问题
		//echo $resHandler->getDebugInfo() . "<br>";

	}

	}



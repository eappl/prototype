<?php
/**
 * 支付宝
 * @author 陈晓东 <cxd032404@hotmail.com>
 * $Id: Ka91.php 15195 2014-07-23 07:18:26Z 334746 $
 */



class Lm_Pay_Passage_Ka91
{

	function createPay($AppInfo,$PartnerInfo,$ServerInfo,$PassageInfo,$OrderInfo,$Pay)
	{
		$comment = json_decode($AppInfo['comment'],true);
		
		/* 密钥 */	
		
		if(in_array($OrderInfo['SubPassageId'],array('1001','1002','1003','1004','1005','1006','1009','1020','1022','1032','BOCB2C','PAB','GDB','POST','HXB','BEA','SHB','ECITIC','NBCB','NJB','GZRCC','CBHB','BJRCB','ZSB','SHRCB','YP','BILL','TENPAY','ALIPAY')))
		{
				$channelid=1;
                $Amount = trim(sprintf("%10.2F",$OrderInfo['Amount']));
		}
		Else
		{
			$paytype=1;
            $Amount = intval($OrderInfo['Amount']);
			If(in_array($OrderInfo['SubPassageId'],Array('CMPAY','YDSZX')))
			{
					$channelid=2;
			}
			Elseif(In_Array($OrderInfo['SubPassageId'],Array('DXCT')))
			{
					$channelid=3;
			} 
			Elseif(In_Array($OrderInfo['SubPassageId'],Array('UNION')))
			{
					$channelid=4;
			}
			Elseif(In_Array($OrderInfo['SubPassageId'],Array('JUNNET')))
			{
					$channelid=5;
			}
			Elseif(In_Array($OrderInfo['SubPassageId'],Array('TXTONG')))
			{
					$Params['key'] = "game_id=5462&prod_uid=119868&prod_type=1&sign=00ab0a497a5122328fc049b850df61e7";
                    $Params['validate'] = "";
                    return $Params;	
			}
			Elseif(In_Array($OrderInfo['SubPassageId'],Array('SDCARD')))
			{
					$channelid=7;
			} 
			Elseif(In_Array($OrderInfo['SubPassageId'],Array('EFT')))
			{
					$channelid=8;
			}
			Else
			{
				return False; 	
			}
		}
		$Fronturl   = 'http://passport.limaogame.com/?c=ka91';		
		$Bgurl   = 'http://payment.limaogame.com/?ctl=callback/ka91';
    $Params['key'] = "orderid=".$OrderInfo['OrderId']."&origin=".$PassageInfo['StagePartnerId']."&chargemoney=".$Amount."&channelid=".$channelid."&paytype=".$paytype."&bankcode=".$OrderInfo['SubPassageId']."&cardno=&cardpwd=&cardamount=&fronturl=".$Fronturl."&bgurl=".$Bgurl."&ext1=lm&ext2=limaogame"; 
    $Params['validate'] = "&version=2.0.1&validate=".substr(md5($Params['key'].$PassageInfo['StageSecureCode']),8,16);	
		return $Params;		
	}
	function endPay($PassageInfo,$OrderInfo,$RequestArr)
	{
		$channelid = $RequestArr['channelid'];
		if(in_array($channelid,array('1')))
		{
				$orderid = $RequestArr['orderid'];
				$chargemoney = trim(sprintf("%10.2f",$RequestArr['chargemoney']));
				$systemno = $RequestArr['systemno'];
				$status = $RequestArr['status'];
				$ext1 = $RequestArr['ext1'];
				$ext2 = $RequestArr['ext2'];				
		}
		elseif(in_array($channelid,array('2','3','4')))
		{
				$orderid = $RequestArr['orderid'];
				$chargemoney = intval($RequestArr['chargemoney']);
				$systemno = $RequestArr['systemno'];
				$status = $RequestArr['status'];
				$ext1 = $RequestArr['ext1'];
				$ext2 = $RequestArr['ext2'];						
		}
		$key = "orderid=".$orderid."&chargemoney=".$chargemoney."&systemno=".$systemno."&channelid=".$channelid."&status=".$status."&ext1=".$ext1."&ext2=".$ext2.$PassageInfo['StageSecureCode'];
		$sign =  strtolower(substr(md5($key),8,16));
		if($sign==$RequestArr['validate'])
		{
				if($status==1)
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
						$Pay['PayedTime'] = time();
						
						$Pay['UserSourceId'] = $OrderInfo['UserSourceId'];
						$Pay['UserSourceDetail'] = $OrderInfo['UserSourceDetail'];
						$Pay['UserSourceProjectId'] = $OrderInfo['UserSourceProjectId'];
						$Pay['UserSourceActionId'] = $OrderInfo['UserSourceActionId'];
						$Pay['UserRegTime'] = $OrderInfo['UserRegTime'];
						
						$Pay['StageOrder'] = $systemno;		
                        
                        
            return $Pay;				
				}
				else
				{
		        return false;
				}
		}
	}
	function checkPay($PassageInfo,$OrderInfo)
	{
	
		$url = "http://91ka.3322.org:1102/if/interface/auto_interface_third_query_order.php";
		$key = "origin=".$PassageInfo['StagePartnerId']."&orderid=".$OrderInfo['OrderId'];
		$sign =  strtolower(substr(md5($key.$PassageInfo['StageSecureCode']),8,16));
		$url = $url."?".$key."&validate=".$sign;
		$return = file_get_contents($url."?".$key."&validate=".$sign);
		$arr = explode("&",$return);
		foreach($arr as $key => $value)
		{
			$arr_2[$key] = explode("=",$value);	
		}
		$v = $arr[count($arr)-1];
		unset($arr[count($arr)-1]);
		$key = implode("&",$arr).$PassageInfo['StageSecureCode'];
		$sign =  strtolower(substr(md5($key),8,16));
		$vr = explode("=",$v);
		if(($vr['1']==$sign)&&($OrderInfo['OrderId']==$arr_2['1']['1']))
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
				$Pay['PayedTime'] = time();
				
				$Pay['UserSourceId'] = $OrderInfo['UserSourceId'];
				$Pay['UserSourceDetail'] = $OrderInfo['UserSourceDetail'];
				$Pay['UserSourceProjectId'] = $OrderInfo['UserSourceProjectId'];
				$Pay['UserSourceActionId'] = $OrderInfo['UserSourceActionId'];
				$Pay['UserRegTime'] = $OrderInfo['UserRegTime'];				
				$Pay['StageOrder'] = $arr_2['2']['1'];					
		}
		else
		{
			return false; 	
		}
				

	}
}


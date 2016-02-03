<?php
/**
 * Socket客户端函数
 * @author 张骥 <344505721@qq.com>
 */

//A=1,V=4,v=2,C=1
class Connect_SocketClient extends Base_Widget
{
    public function PackMsg($arr)
    {
        $bind = unserialize($arr['MessegeContent']);
        $packstr = $bind['PackFormat'];
        
        $res = pack($packstr,
        $bind['Length'],
        $bind['Length2'],
        $bind['uType'],
        $bind['MsgLevel'],
        $bind['Line'],
        $bind['CountDown'],
        iconv("utf-8","GB2312",$bind['MessegeContent']."\0")
        );        
        return $res;
    }
    public function PackExchangeNoSN($arr)
    {
        $bind = unserialize($arr['MessegeContent']);
        $packstr = $bind['PackFormat'];
        
        $res = pack($packstr,
        $bind['Length'],
        $bind['Length2'],
        $bind['uType'],
        $bind['MsgLevel'],
        $bind['Line'],
        $bind['UserID'],
        $bind['ZoneID'],
        $bind['iCash'],
        $bind['ExchangeId']."\0"
        );        
        return $res;
    }
    public function PackExchange($arr)
    {
        $bind = unserialize($arr['MessegeContent']);
        $packstr = $bind['PackFormat'];
        
        $res = pack($packstr,
        $bind['Length'],
        $bind['Length2'],
        $bind['uType'],
        $bind['MsgLevel'],
        $bind['Line'],
        $bind['UserID'],
        $bind['ZoneID'],
        $bind['SN'],
        $bind['iCash'],
        $bind['ExchangeId']."\0"
        );
        return $res;

    }

	public function UnPackSN($buff,$uType)
	{
		
		$oSocketType = (@include(__APP_ROOT_DIR__."/etc/SocketType.php"));
        $oExchange = new Lm_Exchange();
        
		$TypeInfo = $oSocketType[$uType];
		if($TypeInfo['Type'])
		{
			$format = $TypeInfo['UnPackFormat'];
			$unpackArr = @unpack($TypeInfo['UnPackFormat'],$buff);
            $ExchangeInfo = $oExchange->getQueuedExchange(trim($unpackArr['uExchangeID']));
            if($unpackArr['uResultID']==0)
			{				
				if(!$ExchangeInfo['ExchangeSn'])
                {
                    $StatusUpdate = $oExchange->updateExchangeSN($ExchangeInfo['ExchangeId'],$unpackArr['uSN']);
                }
			}
            else
            {
                if($ExchangeInfo['ReTryCount']<3)
                {
                    $reTry = $oExchange->updateExchangeQueue($ExchangeInfo['ExchangeId'],array('RetryCount'=>$ExchangeInfo['ReTryCount']+1));
                    $addQueue = $oExchange->convertExchangeToSocket($ExchangeInfo['ExchangeId']);
                }
                else
                {
                    $oExchange->ExchangeFail($unpackArr['uExchangeID']);                    
                }
            }
		}
	}
	public function UnPackExchangeResult($buff,$uType)
	{
		$oSocketType = (@include(__APP_ROOT_DIR__."/etc/SocketType.php"));
        $oExchange = new Lm_Exchange();
		$TypeInfo = $oSocketType[$uType];
		if($TypeInfo['Type'])
		{
			$format = $TypeInfo['UnPackFormat'];
			$unpackArr = @unpack($TypeInfo['UnPackFormat'],$buff);
            $ExchangeInfo = $oExchange->getQueuedExchange(trim($unpackArr['uExchangeID']));
			if($unpackArr['uResultID']==0)
			{
				if($ExchangeInfo['ExchangeStatus']==1)
                {
                    $EndExchange = $oExchange->endExchange($ExchangeInfo['ExchangeId'],$unpackArr['uSN']);
                }
			}
            else
            {
                if($ExchangeInfo['ReTryCount']<3)
                {
                    $reTry = $oExchange->updateExchangeQueue($ExchangeInfo['ExchangeId'],array('RetryCount'=>$ExchangeInfo['ReTryCount']+1));
                    $addQueue = $oExchange->convertExchangeToSocket($ExchangeInfo['ExchangeId']);
                }
                else
                {
                    $oExchange->ExchangeFail($unpackArr['uExchangeID']);
                }
            }
		}
	}
    public function PackAddHero($arr)
    {
        $bind = unserialize($arr['MessegeContent']);
        $packstr = $bind['PackFormat'];
        $res = pack($packstr,
        $bind['Length'],
        $bind['Length2'],
        $bind['uType'],
        $bind['MsgLevel'],
        $bind['Line'],
        $bind['UserID'],
        $bind['HeroID'],
        $bind['Serial']."\0"
        );        
        return $res;
    }
    public function PackKickOff($arr)
    {
        $bind = unserialize($arr['MessegeContent']);
        $packstr = $bind['PackFormat'];
        $res = pack($packstr,
        $bind['Length'],
        $bind['Length2'],
        $bind['uType'],
        $bind['MsgLevel'],
        $bind['Line'],
        $bind['UserID'],
        $bind['KickOffReason'],
        $bind['Serial']."\0"
        );        
        return $res;
    }
    public function PackAddSkin($arr)
    {
        $bind = unserialize($arr['MessegeContent']);
        $packstr = $bind['PackFormat'];        
        $res = pack($packstr,
        $bind['Length'],
        $bind['Length2'],
        $bind['uType'],
        $bind['MsgLevel'],
        $bind['Line'],
        $bind['UserID'],
        $bind['HeroID'],
        $bind['HeroEquip'],
        $bind['Serial']."\0"
        );        
        return $res;
    }
    public function PackAddMoney($arr)
    {
        $bind = unserialize($arr['MessegeContent']);
        $packstr = $bind['PackFormat'];        
        $res = pack($packstr,
        $bind['Length'],
        $bind['Length2'],
        $bind['uType'],
        $bind['MsgLevel'],
        $bind['Line'],
        $bind['UserID'],
        $bind['MoneyType'],
        $bind['MoneyChanged'],
        $bind['Serial']."\0"
        );        
        return $res;
    }
	public function UnPackAddHero($buff,$uType)
	{		
		$oSocketType = (@include(__APP_ROOT_DIR__."/etc/SocketType.php"));       
		$TypeInfo = $oSocketType[$uType];
		if($TypeInfo['Type'])
		{
			$unpackArr = unpack($TypeInfo['UnPackFormat'],$buff);
			$oProduct = new Config_Product_Product();
			$remove = $oProduct->removeSentLog($unpackArr['Serial'],$unpackArr['uHeroID'],$unpackArr['uResultID']);
			return $remove;
		}
		else
		{
			return false; 	
		}
	}
	public function UnPackAddSkin($buff,$uType)
	{
		$oSocketType = (@include(__APP_ROOT_DIR__."/etc/SocketType.php"));       
		$TypeInfo = $oSocketType[$uType];
		if($TypeInfo['Type'])
		{
			$unpackArr = unpack($TypeInfo['UnPackFormat'],$buff);
			$oProduct = new Config_Product_Product();
			$remove = $oProduct->removeSentLog($unpackArr['Serial'],$unpackArr['uHeroEquip'],$unpackArr['uResultID']);
			return $remove;
		}
		else
		{
			return false; 	
		}
	}
	public function UnPackAddMoney($buff,$uType)
	{
		$oSocketType = (@include(__APP_ROOT_DIR__."/etc/SocketType.php"));       
		$TypeInfo = $oSocketType[$uType];
		if($TypeInfo['Type'])
		{
			$unpackArr = unpack($TypeInfo['UnPackFormat'],$buff);
			$oProduct = new Config_Product_Product();
			$remove = $oProduct->removeSentLog($unpackArr['Serial'],$unpackArr['uMoneyType'],$unpackArr['uResultID']);
			return $remove;
		}
		else
		{
			return false; 	
		}
	}
}
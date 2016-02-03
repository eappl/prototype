<?php
class Cli_ItemController extends Base_Controller_Action
{
	protected $oItem;
    protected $oSeal;
    protected $oMoney;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
        $this->oItem = new Lm_Item();
        $this->oMoney = new Lm_Money();
        $this->oSeal = new Config_ItemSeal();
	}
    
    //道具日志压缩
    public function compressItemAction()
    {
        do
        {
            $S = time();
            $StartTime = intval($this->request->StartTime)?intval($this->request->StartTime):time();
            
            echo $this->oItem->compressItemByDay($StartTime)."\n";
            $E = time();
            
            if(($E-$S) < 600){
                sleep((600-($E-$S)));
            }
        }while(true);
    }
    
    //金币日志压缩
    public function compressMoneyAction()
    {
        do
        {
            $S = time();
            $StartTime = intval($this->request->StartTime)?intval($this->request->StartTime):time();
            
            echo $this->oMoney->compressMoneyByDay($StartTime)."\n";
            $E = time();
            
            if(($E-$S) < 600){
                sleep((600-($E-$S)));
            }
        }while(true);
    }
    
	public function itemSealAction()
	{
	    $LogType = $this->request->LogType;
        
        if($LogType){
            do
            {
                $S = time();
                $AppId = intval($this->request->AppId);
                
                if($AppId){
                    $ItemSeal = array();
                    $SealItem = $this->oSeal->getAll($AppId); 
                    foreach($SealItem as $k=>$v){
                        $ItemIds[$v['SealTimeType']][$v['SealTime']][$v['AppId']][] = $v['ItemId'];
                        $ItemSeal[$v['ItemId']]['ItemSeal'] = $v['ItemSeal'];
                        $ItemSeal[$v['ItemId']]['ItemName'] = $v['ItemName'];
                    }
                    
                    foreach($ItemIds as $k=>$v){
                        switch($k){
                            case "hh":                        
                                $this->checkSeal($v,"Y-m-d H:00:00",3600,$ItemSeal,$LogType,$k);
                                break;
                            case "dd":                        
                                $this->checkSeal($v,"Y-m-d 00:00:00",86400,$ItemSeal,$LogType,$k);
                                break;                    
                        }
                    }
                }
                $E = time();
                
                if(($E-$S) < 900){
                    sleep((900-($E-$S)));
                }
            }while(true);  
        }	    
	}
    
    public function checkSeal($v,$date,$time,$ItemSeal,$LogType,$dateType)
    {
        $StartTime = abs(intval($this->request->StartTime))?abs(intval($this->request->StartTime)):time();
        
        foreach($v as $ddk=>$ddv){
            $SealTime = date($date,($StartTime-(($ddk-1)*$time))-3600);
            echo "StartTime:".$SealTime."\n";
            
            foreach($ddv as $AppId=>$Items){
                $emailmsg = "";
                $ItemId = implode(",",$Items);
                
                if($LogType == "money"){
                    $ItemList = $this->oMoney->getItemSealList($AppId,$ItemId,strtotime($SealTime),$ddk,$dateType);
                }else if($LogType == "item"){
                    $ItemList = $this->oItem->getItemSealList($AppId,$ItemId,strtotime($SealTime),$ddk,$dateType);
                }
                
                foreach($ItemList as $UserId=>$vals){
                    foreach($vals as $ItemId=>$val){
                        if($val > $ItemSeal[$ItemId]['ItemSeal']){
                            $emailmsg .= "道具：".$ItemSeal[$ItemId]['ItemName']."，用户ID：".$UserId."，阀值：".$ItemSeal[$ItemId]['ItemSeal']."，获取：".$val."<br />";
                        }
                    }
                }
                
                if(!empty($emailmsg)){
                    if($dateType == "hh"){
                        $emailmsg .= "阀值监控时间：".$SealTime."至".date("Y-m-d H:59:59",strtotime($SealTime))."<br />";
                    }
                    
                    if($dateType == "dd"){
                        $emailmsg .= "阀值监控时间：".$SealTime."至".date("Y-m-d H:i:s",time())."<br />";
                    }
                    
                    $maillist = array(
                    'wanglei@limaogame.com',
                    '1546968480@qq.com',
                    '344505721@qq.com',
                    );
                    
                    foreach($maillist as $ek=>$ev){
                        $this->sendmail($ev,"道具阀值报警",$emailmsg);
                    }
                }                                
            }
        }
    }
    
    private function setxml($maillist,$title,$body)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';    
        $xml .= '<requestdata>';    
        $xml .= '<head>';    
        $xml .= '<user>celsochen</user>';    
        $xml .= '<passwrd>'.md5('83292776cgm').'</passwrd>';    
        $xml .= '<163ad>false</163ad>'; //网易标题自动加(AD) 如果不需要 false    
        $xml .= '<subject>'.$title.'</subject>';    
        $xml .= '<sender>noreply@edmemail.com</sender>'; //必须是邮箱格式    
        $xml .= '<nickname>狸猫游戏</nickname>';    
        $xml .= '<isradom>false</isradom>';//发件人邮箱加上随机数能稍微提高进收件箱的概率 默认不打开    
        $xml .= '<replyemail>noreply@edmemail.com</replyemail>'; //当客户收到email点击回复后发送的邮件地址  
        $xml .= '<service_id>1748</service_id>';//触发用户查询统计ID，需要此ID请联系客服   
        $xml .= '</head>';    
        $xml .= '<body>';    
        $xml .= '<modula>';//这个标签里面模板内容去掉换行，不要使用单引号    
        $xml .= '<html>';    
        $xml .= '<head>';    
        $xml .= '<meta http-equiv="Content-Type" charset="utf-8" />';    
        $xml .= '<title>'.$title.'</title>';    
        $xml .= '</head>';    
        $xml .= '<body>';    
        $xml .= $body;    
        $xml .= '</body>';    
        $xml .= '</html>';    
        $xml .= '</modula>';    
        $xml .= '<maillist>';
        $xml .= 'Mailbox'.PHP_EOL;
        if(is_array($maillist)){
            foreach($maillist as $k=>$v){
                $xml .= $v.PHP_EOL;
            }
        }else{
            $xml .= $maillist.PHP_EOL;
        }    
        $xml .= '</maillist>';    
        $xml .= '</body>';    
        $xml .= '</requestdata>';
        
        return $xml;                
    }
 
    public function sendmail($maillist,$title,$body){
        $xml = $this->setxml($maillist,$title,$body);
                        
        $URL_Info=parse_url('http://trigger.emailcar.net/recv.php');
        $URL_Info['port']=80;
        $request='';
        $request.="POST ".$URL_Info["path"]." HTTP/1.1\n";
        $request.="Host: ".$URL_Info["host"]."\n";
        $request.="Content-Type: text/html\n";
        $request.="Content-length: ".strlen($xml)."\n";
        $request.="Connection: close\n";    $request.="\n";
        $request.=$xml."\n";
        $result='';
        try
        {
            $fp = fsockopen($URL_Info["host"],$URL_Info["port"]);
            if(fputs($fp, $request)){
                while(!feof($fp)) {        
                    $result .= fgets($fp, 128);
                }    
                fclose($fp);
            }else{
                return false;
            }
            
            if(strstr($result,"<taskid>")){
                return 1;
            }
            
            if(strstr($result,"<error>")){
                return false;
            } 
        }catch(Exception $e){
            $filepath = dirname(dirname(dirname(dirname(__FILE__))))."/mailcheck_Log/maillinkerror.log";                                               
            $data = file_get_contents($filepath);
            $write = $data + 1;                         
            $handle = fopen($filepath, "w+");
            fwrite($handle,$write);
            fclose($handle);
            
            print_r($e->getMessage())."\n";
            return false;
        }
    } 	   
}

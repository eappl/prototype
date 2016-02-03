<?php
class Cli_ReadErrorLogController extends Base_Controller_Action{	
	public function init()
	{
		parent::init();
        $this->oCron = new Lm_Cron();
	}
    
	
	function getLogAction()
	{
        $file = "d:\scadmin.5173.log";
        if(file_exists($file))
        {
            $fd = fopen($file, "r");
        
            while ($buffer = fgets($fd)) 
            {                              
         	    $t = explode("]",$buffer);
         	    $time_text = substr($t[0],1);
         	    //echo strtotime($time_text)."\n";
         	    //if((!stripos($t[3],'png'))&&(!stripos($t[3],'favicon.ico')))
         	    {             	    
             	    $time = strtotime($time_text);

             	    if($time >= strtotime("2014-01-01"))
             	    {
                 	    $t2 = explode(' ',$t[2]);
                 	    $t3 = explode("referer: ",$t[3]);
                 	    $arr = array('time'=>$time,'host'=>$t2[2],'content'=>$t[3],'refer'=>$t3[1]);
                 	    $insert = $this->oCron->InsertErrorLog($arr);
                 	    if($insert)
                 	    {
                 	        echo "*";    
                 	    }
             	    }
             	    else
             	    {
                        echo "0";    
                    }
         	    }
//         	    else 
//         	    {
//                    //echo "-";    
//                }
            }
        }
        else
        {
            echo iconv("utf-8","gbk","文件".$file."不存在\n");
        }
    }
}
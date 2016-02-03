<?php
class Cli_TestController extends Base_Controller_Action{
    //protected $oTask;
    
	public function init()
	{
		parent::init();
		$this->oTask = new Lm_Task();
	}
    
    public function indexAction()
    {
        echo "here";
//		$StartTime = strtotime("2013-07-05 06:00:00");
//		$DataRange = array('1'=>array('Day'=>0,'Start'=>'19:00:00','End'=>'23:00:00'),
//		'2'=>array('Day'=>1,'Start'=>'19:00:00','End'=>'23:00:00'),
//		'3'=>array('Day'=>3,'Start'=>'19:00:00','End'=>'23:00:00'),
//		'4'=>array('Day'=>5,'Start'=>'19:00:00','End'=>'23:00:00')
//		);
//		$WeekLag = intval((time()-$StartTime)/86400/7);
//		$Start = $StartTime + 86400*7*$WeekLag;
//		$End = $Start + 86400*7;
//		foreach($DataRange as $key => $value)
//		{
//			$S = $Start;
//			while(date("w",$S)!=$value['Day'])
//			{
//				$S += 86400;	
//			}
//			$A = strtotime(date("Y-m-d",$S)." ".$value['Start']);
//			$B = strtotime(date("Y-m-d",$S)." ".$value['End']);
//			$t[$key] = " (PvpEnterTime >= $A and PvpEnterTime <= $B)";			
//		}
//		$whereTime = "( ".implode(" or ",$t)." )";				
//		$PvpLogTotal = $this->oTask->getPvpLogTotal($whereTime);
//		$this->oTask->getUserByPvpRank(date("YmdH",$Start)."-".date("YmdH",$End));
    }
}
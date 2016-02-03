<?php
class Cli_ScheduleController extends Base_Controller_Action{
    protected $oProductPack;
    
	public function init()
	{
		parent::init();
		$this->oProductPack = new Config_Product_Pack();
	}
    
    public function asignScheduleAction()
    {
		$Date = $this->request->Date? intval($this->request->Date) : date("Y-m-d");
		$ScheduleList = $this->oProductPack->getAllAsignSchedule($Date,$Date);
		foreach($ScheduleList as $ScheduleId => $value)
		{
			unset($CodeList);
			if($value['AsignedUserCount']==0)
			{
			    if(file_exists($value['FileName']))
			    {
			        $fd = fopen($value['FileName'], "r");			    
			        $ProcessTime = time();
			        while ($buffer = fgets($fd)) 
			        {
						$GenInfo = $this->oProductPack->GetGenPackCodeLogById($value['GenId']);
						if($GenInfo['needBind']==1)
						{
							$asigned = 0;
							$user = 0;
					        $List = explode(",",$buffer);
					        foreach($List as $k=>$UserName)
					        {
   					            $user++;
					            if(count($CodeList)==0)
					            {
					            	$CodeList = $this->oProductPack->getunSignedCode($value['GenId'],1000);
				                    $this->oProductPack->updateSchedule($ScheduleId, array('ProcessTime'=>$ProcessTime,'UserCount'=>$user,'AsignedUserCount'=>$asigned)); 
					            	if(count($CodeList)==0)
					            	{
					                    fclose($fd);
					                    $this->oProductPack->updateSchedule($ScheduleId, array('ProcessTime'=>$ProcessTime,'UserCount'=>$user,'AsignedUserCount'=>$asigned));
					                    return; 	
					            	}	
					            }
					            $Code = current($CodeList);
					            $AsignCode = $this->oProductPack->asignProductPackCode($UserName,$Code);	        
					    		echo $UserName."-".$Code['ProductPackCode']."-return:".$AsignCode."\n";;
					    		if($AsignCode)
					            {
					                $asigned++;
					                unset($CodeList[$Code['ProductPackCode']]);
					           	}        	
					        }       
						}				                       				        
			        }
                    fclose($fd);
                    $this->oProductPack->updateSchedule($ScheduleId, array('ProcessTime'=>$ProcessTime,'UserCount'=>$user,'AsignedUserCount'=>$asigned)); 
				}	
			}
		}
		return;
    }
}
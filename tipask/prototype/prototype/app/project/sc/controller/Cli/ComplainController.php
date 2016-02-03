<?php
class Cli_ComplainController extends Base_Controller_Action{
    
	public function init()
	{
		parent::init();
		$this->oComplain = new Kubao_Complain();
		$this->oQuestion = new Kubao_Question();
	}
    
    public function revokeCheckAction()
    {
       set_time_limit(0);
	   $StartTime = strtotime(date("Y-m-d H:00:00",time()-90*24*3600));
	   $EndTime = strtotime(date("Y-m-d H:00:00",time()));

       $count = 1;
	   $page = 1;
	   $pagesize = 1000;
	   while($count > 0)
	   {
			$ConditionList = array('StartTime'=>$StartTime,'EndTime'=>$EndTime,'Page'=>$page,'PageSize'=>$pagesize,'Public'=>0);
			$fields = "id,status,author";
			$SearchData = $this->oComplain->getComplainList($ConditionList,$fields);
			$page ++;
			$count = count($SearchData['QuestionList']);
			echo "count:".$count."\n";
			sleep(1);
			if($count>0)
			{
				foreach($SearchData['QuestionList'] as $key => $data)
				{
					$check = $this->oComplain->checkRevokeFromComplain($data['id']);
					echo $data['id'].","."check:".$check['return']."\n";
					if($data['status']!=2)
					{
						if($check['return']==1)
						{
							$Revoke = array('QuestionId'=>$data['id'],'RevokeReason'=>urldecode($check['RevokeReason']),'UserName'=>$data['author'],'IP'=>$check['RevokeIp']);
							$RevokeQuestion = $this->oComplain->RevokeQuestion($Revoke);
							echo "Revoke:".$RevokeQuestion."\n";
						}
					}
					else
					{
						if($check['return']==0)
						{
							$Revoke = array('QuestionId'=>$data['id'],'RevokeReason'=>urldecode($check['RevokeReason']),'UserName'=>$data['author'],'IP'=>$check['RevokeIp']);
							$RevokeQuestion = $this->oComplain->RevokeQuestion($Revoke,0);
							echo "Revoke:".$RevokeQuestion."\n";						
						}
					}
				}
			}
			sleep(1);
	   }
    }
}
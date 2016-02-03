<?php
class Cli_SearchController extends Base_Controller_Action{
    
	public function init()
	{
		parent::init();
		$this->oComplain = new Kubao_Complain();
		$this->oQuestion = new Kubao_Question();
	}
    
    public function rebuildSearchAction()
    {
       set_time_limit(0);
	   $StartDate = ($this->request->StartDate);
	   $EndDate = ($this->request->EndDate);
	   $History = intval($this->request->History);

	   $QuestionTypeArr = array('ask','suggest');
	   foreach($QuestionTypeArr as $Key => $QuestionType)
	   {
			$count = 1;
			$page = 1;
			$pagesize = 1000;
			while($count > 0)
			{
				$ConditionList = array('QuestionType'=>$QuestionType,'History'=>$History,'StartDate'=>$StartDate,'EndDate'=>$EndDate,'Page'=>$page,'PageSize'=>$pagesize,'Parent'=>0,'Revocation'=>0,'hidden'=>1,'Accepted'=>-1,'Help'=>-1);
				$fields = "id,description,time,atime";
				$SearchData = $this->oQuestion->getQuestionList($ConditionList,$fields,"desc");
				$page ++;
				$count = count($SearchData['QuestionList']);
				if($count>0)
				{
					foreach($SearchData['QuestionList'] as $key => $data)
					{
						$data['title'] = $data['description'];
						$data['tag'] = json_encode(array(),true);
						$data['question_type'] = 'question';
						$set_serach = base_common::set_search($data);
						$log = $QuestionType.": Id:".$data['id']."-".$set_serach->getHttpStatus()."\r\n";
						$fileName = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/file/searchLog.txt";
						file_put_contents($fileName,$log,FILE_APPEND);
						echo $log;
					}
				}
				echo $count."\n";
				sleep(1);
			}		   
		   
	   }
       $count = 1;
	   $page = 1;
	   $pagesize = 1000;
	   while($count > 0)
	   {
			$ConditionList = array('StartDate'=>$StartDate,'EndDate'=>$EndDate,'Page'=>$page,'PageSize'=>$pagesize,'Public'=>0);
			$fields = "id,description,time,atime,status";
			$SearchData = $this->oComplain->getComplainList($ConditionList,$fields);
			$page ++;
			$count = count($SearchData['QuestionList']);
			echo $count."-".$SearchData['QuestionNum']."\n";
			if($count>0)
			{
				foreach($SearchData['QuestionList'] as $key => $data)
				{
					$data['title'] = $data['escription'];
					$data['tag'] = json_encode(array(),true);
					$data['id'] = "c_".$data['id'];
					$data['question_type'] = 'complain';
					$data['atime'] = $data['status']==2?-1:$data['atime'];
					unset($data['status']);
					$set_serach = base_common::set_search($data);
					$log = "complain: Id:".$data['id']."-".$set_serach->getHttpStatus()."\r\n";
					$fileName = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/file/searchLog.txt";
					file_put_contents($fileName,$log,FILE_APPEND);
					echo $log;
				}
			}
			sleep(1);
	   }
    }
}
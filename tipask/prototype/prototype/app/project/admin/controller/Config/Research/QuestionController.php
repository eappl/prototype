<?php
/**
 * 调研管理
 * @author Chen<cxd032404@hotmail.com>
 * $Id: QuestionController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_Research_QuestionController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/research/question';
	/**
	 * Research对象
	 * @var object
	 */
	protected $oResearch;
	protected $oQuestion;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oResearch = new Config_Research();
		$this->oQuestion = new Config_Research_Question();

		$this->ResearchList = $this->oResearch->getAll();
		$this->AnswerTypeList = array('text'=>'字符串','radio'=>'单选','checkbox'=>"多选",'textarea'=>"文本框");

	}
	//调研配置列表页面
	public function indexAction()
	{
		$AnswerTypeList = $this->AnswerTypeList;
		$ResearchList = $this->ResearchList;
		$ResearchId = abs(intval($this->request->ResearchId));
		$QuestionArr = $this->oQuestion->getAll($ResearchId);
		foreach($QuestionArr as $QuestionId => $Question)
		{
			$QuestionArr[$QuestionId]['ResearchName'] = $ResearchList[$Question['ResearchId']]['ResearchName'];	
			$QuestionArr[$QuestionId]['AnswerTypeName'] = $AnswerTypeList[$Question['AnswerType']];	

		}
		include $this->tpl('Config_Research_Question_list');
	}
	//添加调研填写配置页面
	public function addAction()
	{
		$AnswerTypeList = $this->AnswerTypeList;
		$ResearchList = $this->ResearchList;
		include $this->tpl('Config_Research_Question_add');
	}
	
	//添加新调研
	public function insertAction()
	{
		$AnswerTypeList = $this->AnswerTypeList;
		$bind=$this->request->from('ResearchId','QuestionContent','AnswerType','Answer');


		if($bind['ResearchId']==0)
		{
			$response = array('errno' => 2);
		}
		elseif($bind['QuestionContent']=='')
		{
			$response = array('errno' => 3);
		}
		elseif(!isset($AnswerTypeList[$bind['AnswerType']]))
		{
			$response = array('errno' => 4);
		}	
		else
		{	
			$res = $this->oQuestion->insert($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改调研信息页面
	public function modifyAction()
	{
		$AnswerTypeList = $this->AnswerTypeList;
		$ResearchList = $this->ResearchList;
		$QuestionId = $this->request->QuestionId;
		$Question = $this->oQuestion->getRow($QuestionId);
		include $this->tpl('Config_Research_Question_modify');
	}
	
	//更新调研信息
	public function updateAction()
	{
		$AnswerTypeList = $this->AnswerTypeList;
		$bind=$this->request->from('ResearchId','QuestionId','QuestionContent','AnswerType','Answer');


		if($bind['ResearchId']==0)
		{
			$response = array('errno' => 2);
		}
		elseif($bind['QuestionId']==0)
		{
			$response = array('errno' => 5);
		}
		elseif($bind['QuestionContent']=='')
		{
			$response = array('errno' => 3);
		}
		elseif(!isset($AnswerTypeList[$bind['AnswerType']]))
		{
			$response = array('errno' => 4);
		}	
		else
		{	
			$res = $this->oQuestion->update($bind['QuestionId'], $bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		
		echo json_encode($response);
		return true;
	}
	
	//删除调研
	public function deleteAction()
	{
		$QuestionId = intval($this->request->QuestionId);
		$this->oQuestion->delete($QuestionId);
		$this->response->goBack();
	}
	public function getQuestionAction()
	{
		$ResearchId = intval($this->request->ResearchId)?intval($this->request->ResearchId):0;
		if($ResearchId)
		{
			$QuestionArr = $this->oQuestion->getAll($ResearchId);
		}
		echo "<option value=''>全部</option>";
		if(is_array($QuestionArr))
		{
			foreach ($QuestionArr as $question_id => $question)
			{
				echo "<option value='{$question_id}'>{$question['QuestionContent']}</option>";
			}
		}
	}
}

<?php
/**
 * 快捷链接mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Kubao_Qtype extends Base_Widget
{
	//声明所用到的表
	protected $table = 'ask_qtype';
	protected $table_question_num = 'ask_question_num';
	protected $table_order_count = 'ask_order_count';

	//根据类型获取快速链接顶层信息
	public function getQtypeById($QtypeId,$fields = '*')
	{	
		$table_to_process = Base_Widget::getDbTable($this->table);
		$Qtype = $this->db->selectRow($table_to_process,$fields,'`id`=?',array($QtypeId));
		return $Qtype;
	}
	//根据类型获取快速链接顶层信息
	public function getOrderCount($QtypeId,$fields = '*')
	{	
		$table_to_process = Base_Widget::getDbTable($this->table_order_count);
		$OrderCount = $this->db->selectRow($table_to_process,$fields,'`qtype`=?',array($QtypeId));
		return $OrderCount;
	}
	//获取所有问题
	public function getAllQtype($Visiable = 'all',$fields = '*')
	{	
		$table_to_process = Base_Widget::getDbTable($this->table);
		if($Visiable == 'all')
		{
			
			$Qtype = $this->db->select($table_to_process,$fields);
		}
		else
		{
			$Qtype = $this->db->select($table_to_process,$fields,'`visiable`=?',array($Visiable));		
		}
		
		
		return $Qtype;
	}
	//获取日期和问题主分类获取问题数量汇总列表
	public function getQuestionNumList($ConditionList)
	{	
		$table_to_process = Base_Widget::getDbTable($this->table_question_num);		
		//查询列
		$select_fields = array(
		'QuestionNum'=>'sum(questions)',
		'QuestionType'=>'question_type',
		'Qtype'=>'qtype');
		//分类统计列
		$group_fields = array('QuestionType','Qtype');
		//初始化查询条件
		if($ConditionList['StartDate'] == $ConditionList['EndDate'])
		{
			$whereDate = $ConditionList['StartDate']?" date = '".$ConditionList['StartDate']."' ":"";
			$whereStartDate = "";
			$whereEndDate = "";			
		}
		else
		{
			$whereStartDate = $ConditionList['StartDate']?" date >= '".$ConditionList['StartDate']."' ":"";
			$whereEndDate = $ConditionList['EndDate']?" date <= '".$ConditionList['EndDate']."' ":"";
			$whereDate = "";			
		}		
		$whereQtype = $ConditionList['QtypeId']?" qtype = ".$ConditionList['QtypeId']." ":"";
		//$whereQuestionType = $ConditionList['QuestionType']?" question_type in (".$ConditionList['QuestionType'].")":"";

		$QuestionTypeArrTemp = explode(',',$ConditionList['QuestionType']);
		$QuestionTypeArr = $this->config->QuestionTypeList;

		foreach($QuestionTypeArr as $key => $value)
		{
			if(!in_array($key,$QuestionTypeArrTemp))
			{
				unset($QuestionTypeArr[$key]);
			}			
		}
		if(count($QuestionTypeArr)<1)
		{
			$QuestionTypeArr = $this->config->QuestionTypeList;
		}
		$t = array();
		foreach($QuestionTypeArr as $key => $value)
		{
			$t[] = "'".$key."'";
		}
		$whereQuestionType = "question_type in (".implode(",",$t).")";
				

		$whereCondition = array($whereDate,$whereStartDate,$whereEndDate,$whereQtype,$whereQuestionType);
		//print_R($whereCondition);
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成分类汇总列
		$groups = Base_common::getGroupBy($group_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		//初始化问题分类数组

		//获取所有需要显示的主分类列表
		$QtypeList = $this->getAllQtype(1,'name,id,trading,pid');
		//初始化结果数组		

		$QuestionNum = array();
		foreach($QuestionTypeArr as $QuestionType => $QuestionTypeName)
		{
			$oCategory = new Kubao_Category();
			$CategoryInfo = $oCategory->getCategoryByQuestionType($QuestionType,'name');
			$QuestionNum[$QuestionType] = array('content'=>$CategoryInfo['name']."总量:",'QuestionNum'=>0,'QuestionNumList'=>array());
			foreach($QtypeList as $key => $QtypeInfo)
			{
				if($QuestionType == "complain")
				{
					$TradingConfig = unserialize($QtypeInfo['trading']);
					//如果该投诉下的问题分类不可直接提交订单申诉则留在列表中,否则去除
					if(trim($TradingConfig['directOrderUrl'])=="")
					{
						$OrderCount = $this->getOrderCount($QtypeInfo['id'],'order_count');
						$OrderCount = isset($OrderCount['order_count'])?intval($OrderCount['order_count']):0;
						$QuestionNum[$QuestionType]['QuestionNumList'][$QtypeInfo['id']] = array('QuestionNum'=>0,'OrderCount'=>$OrderCount,'QtypeId'=>$QtypeInfo['id'],'content'=>$QtypeInfo['name'],'url'=>'http://sc.5173.com/?index/questionTypeDetail/'.$QtypeInfo['id'].'/'.$QuestionType);
						$QuestionNum[$QuestionType]['OrderCount'] += $OrderCount;
					}					
				}
				else
				{
					$QuestionNum[$QuestionType]['QuestionNumList'][$QtypeInfo['id']] = array('QuestionNum'=>0,'QtypeId'=>$QtypeInfo['id'],'content'=>$QtypeInfo['name'],'url'=>'http://sc.5173.com/?index/questionTypeDetail/'.$QtypeInfo['id'].'/'.$QuestionType);				
				}
			}
			foreach($QtypeList as $key => $QtypeInfo)
			{
				if($QtypeInfo['pid']>0)
				{
					unset($QuestionNum[$QuestionType]['QuestionNumList'][$QtypeInfo['pid']]);
				}
			}
		}
		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where.$groups;
		$data = $this->db->getAll($sql);
		foreach($data as $key => $value)
		{
			//数据累加
			if(isset($QuestionNum[$value['QuestionType']]['QuestionNumList'][$value['Qtype']]))
			{
				if($value['QuestionType']=="complain")
				{
					$QuestionNum[$value['QuestionType']]['QuestionNum'] += $value['QuestionNum'];

					if($QuestionNum[$value['QuestionType']]['QuestionNumList'][$value['Qtype']]['OrderCount']>0)
					{
						$value['QuestionNum'] = $value['QuestionNum']/$QuestionNum[$value['QuestionType']]['QuestionNumList'][$value['Qtype']]['OrderCount'];

						if($value['QuestionNum']>1 || $value['QuestionNum']<1/100/100)
						{
							$value['QuestionNum'] = "0.01%";
							//$value['QuestionNum'] = $QuestionNum[$value['QuestionType']]['OrderCount'];
						}
						else
						{
							$value['QuestionNum'] = sprintf("%2.2f",$value['QuestionNum']*100)."%";
						}
					}
					else
					{
						$value['QuestionNum'] = "0.01%";
						//$value['QuestionNum'] = $value['QuestionNum'];//$QuestionNum[$value['QuestionType']]['OrderCount'];
					}
					$QuestionNum[$value['QuestionType']]['QuestionNumList'][$value['Qtype']]['QuestionNum'] = $value['QuestionNum'];
				}
				else
				{
					$QuestionNum[$value['QuestionType']]['QuestionNumList'][$value['Qtype']]['QuestionNum'] = $value['QuestionNum'];
					$QuestionNum[$value['QuestionType']]['QuestionNum'] += $value['QuestionNum'];
				}								
			}
		}
		//初始化为json可识别对象
		foreach($QuestionTypeArr as $QuestionType => $QuestionTypeName)
		{
			$QuestionNum[$QuestionType]['QuestionNumList2'] = array();
			foreach($QuestionNum[$QuestionType]['QuestionNumList'] as $key => $value)
			{
				$QuestionNum[$QuestionType]['QuestionNumList2'][] = $value;
			}
			$QuestionNum[$QuestionType]['QuestionNumList'] = $QuestionNum[$QuestionType]['QuestionNumList2'];
			unset($QuestionNum[$QuestionType]['QuestionNumList2']);
		}
		if(isset($QuestionNum["complain"]))
		{
			$Q = sprintf("%2.2f",$QuestionNum["complain"]['QuestionNum']/$QuestionNum["complain"]['OrderCount']*100)."%";
			if($Q>1 || $Q<1/100/100)
			{
				$QuestionNum["complain"]['QuestionNum'] = "0.01%";
			}
			else
			{
				$QuestionNum["complain"]['QuestionNum'] = sprintf("%2.2f",$Q)."%";
			}
		}
		return $QuestionNum;
	}
}

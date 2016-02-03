<?php
error_reporting(0);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', substr(dirname(__FILE__), 0, -4));
date_default_timezone_set('Etc/GMT-8');
defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

require_once TIPASK_ROOT.'/config.php';
require_once TIPASK_ROOT.'/lib/pdo/Mysql/pdo.class.php';
require_once TIPASK_ROOT.'/lib/pdo/Mysql/hash.class.php';
require_once TIPASK_ROOT.'/lib/global.func.php';
require_once TIPASK_ROOT.'/lib/db.class.php';
require_once TIPASK_ROOT.'/lib/config.class.php';
require_once TIPASK_ROOT.'/lib/CacheMemcache.class.php';
require TIPASK_ROOT.'/model/base.class.php';

$post = ($_POST);
//$post = ($_GET);
$base = new base();
$base->load('question');
$base->load('complain');
$base->load('qtype');
$base->load('operator');
$base->load('category');
$time = $_SERVER['REQUEST_TIME'];

!isset($post['act']) && exit(json_encode(array('msg'=>'act 为空')));

$LogName = TIPASK_ROOT."/data/logs/{$post['act']}".date("Y-m-d").".txt";
file_put_contents($LogName,date("Y-m-d H:i:s")."\r\n".var_export($post,true)."\r\n",FILE_APPEND);

//投诉回答处理
if($post['act'] == 'ts_cl')
{	
	!isset($post['qid']) && exit(json_encode(array('msg'=>'qid参数为空')));
	!isset($post['content']) && exit(json_encode(array('msg'=>'content参数为空')));
	!isset($post['csn']) && exit(json_encode(array('msg'=>'csn参数为空')));
	
	$result = $_ENV['complain']->updateComplainAnswer($post);
	if($result==1)
	{
		$_ENV['question']->rebuildQuestionDetail($post['qid'],"complain");
		$complainInfo = $_ENV['complain']->Get($post['qid']);
		$q_search['id'] = 'c_'.$complainInfo['id'];
		$q_search['title'] = $complainInfo['description'];
		$q_search['description'] = $complainInfo['description'];
		$q_search['tag'] = json_encode(array(),true);
		$q_search['question_type'] = 'complain';
		$q_search['time'] = $complainInfo['time'];
		$q_search['atime'] = $complainInfo['atime']['status']==2?-1:$complainInfo['atime'];
		if($complainInfo['public']==0)
		{
			try
			{
				$base->set_search($q_search);
			}
			catch(Exception $e)
			{
				send_AIC('http://sc.5173.com/api/ts/cl.html','搜索服务器异常',1,'搜索接口');
			}
		}
		else
		{
			$base->delete_search('c_'.$complainInfo['id']);
		}
		$return = array('msg'=>'success','return'=>1);
	}
	else if($result==2)
	{
		$return = array('msg'=>'failure','return'=>2);
	}
	else if($result==3)	
	{
		$return = array('msg'=>'qid不存在','return'=>3);
	}
		
	echo json_encode($return);	
}
elseif($post['act'] == 'ts_xs') // 投诉隐藏,显示
{	
	//投诉问题前台显示,$post['public']==1  对应sc 0 显示
	$qid    = intval($post['qid']);
	$public = intval($post['public']);
	
	if($qid>0)
	{
		$arr = array(1=>0,0=>1,2=>2);
		$public = isset($arr[$post['public']])?$arr[$post['public']]:1;
		$complainInfo = $_ENV['complain']->Get($qid,'*');
		if(isset($complainInfo['id']))
		{
			// 重复操作
			if($complainInfo['public']==$public)
			{
				$return = array('msg'=>'success','return'=>1);
			}
			else
			{
				$result = $_ENV['complain']->Update($qid,array('public'=>$public));
				if($result>0)
				{
					$_ENV['question']->rebuildQuestionDetail($post['qid'],"complain");
					if($public!=0)
					{
						$base->delete_search('c_'.$qid);	
					}
					else
					{
						$q_search['id'] = 'c_'.$qid;
						$q_search['title'] = $complainInfo['description'];
						$q_search['description'] = $complainInfo['description'];
						$q_search['tag'] = json_encode(array(),true);
						$q_search['question_type'] = 'complain';
						$q_search['time'] = $complainInfo['time'];						
						$q_search['atime'] = $complainInfo['status']==2?-1:$complainInfo['atime'];	
						try
						{			
							$search = $base->set_search($q_search);
							
						}
						catch(Exception $e)
						{
							send_AIC('http://sc.5173.com/index.php?question/complain.html','搜索服务器异常',1,'搜索接口');
						}						
					}
					$return = array('msg'=>'success','return'=>1,'search'=>$search);
				}
				else
				{
					$return = array('msg'=>'failure','return'=>2);
				}
			}
		}
		else
		{
			$return = array('msg'=>'qid不存在','return'=>3);
		}
	}
	else
	{
		$return = array('msg'=>'非法值qid','return'=>4);
	}
		
	echo json_encode($return);	
}
else if($post['act'] == 'ts_category')
{
	$qid = intval($post['qid']);
	$jid = intval($post['jid']);
	$sid = intval($post['sid']);
	
	if($qid>0)
	{
		$complainInfo = $_ENV['complain']->Get($qid);
		if(isset($complainInfo['id']))
		{			
			$qtypeInfo = $_ENV['qtype']->GetQTypeByComplain($jid);
			
			$updateArr = array("sname"=>trim($post['sname']),"jname"=>trim($post['jname']));
			$date = date("Y-m-d",$complainInfo['time']);
			if($jid>0)
			{
				$updateArr['jid'] = $jid;
				$updateArr['qtype'] = $qtypeInfo['id'];
				$update_num_before = $_ENV['question']->modifyUserQtypeNum($date,$complainInfo['qtype'],'complain',-1);
				$update_num_after = $_ENV['question']->modifyUserQtypeNum($date,$qtypeInfo['id'],'complain',1);				
			}
			
			if($sid>0)
			{
				$updateArr['sid'] = $sid;
			}				
			$update = $_ENV['complain']->Update($qid,$updateArr);

			if($update)
			{
				$_ENV['question']->rebuildQuestionDetail($post['qid'],"complain");
				$return = array('msg'=>'成功','return'=>1);
			}
			else
			{
				$return = array('msg'=>'失败','return'=>2);
			}			
		}
		else
		{
			$return = array('msg'=>'qid不存在','return'=>3);
		}
				
		echo json_encode($return);
	}
}
else if ($post['act'] == 'ts_status2')
{
	$qid = intval($post['qid']);
	if ($qid<=0)
	{
		$return = array('msg'=>'非法参数qid','return'=>4);
	}
	else
	{
		$complainInfo = $_ENV['complain']->Get($qid,'id,status,rtime');
		if (isset($complainInfo['id']))
		{
			if ($complainInfo['status']==2 && $complainInfo['status']!='')
			{
				$return = array('msg'=>'已经撤销过','return'=>1);
			}
			else
			{
				$result = $_ENV['complain']->Update($qid,array('status'=>2,'rtime'=>$time ));
				
				if($result)
				{
					$_ENV['question']->rebuildQuestionDetail($post['qid'],"complain");
					$return = array('msg'=>'撤销成功','return'=>1);
				}
				else
				{
					$return = array('msg'=>'撤销失败','return'=>2);
				}
			}
		}
		else
		{
			$return = array('msg'=>'qid不存在','return'=>3);
		}
	}
		
	echo json_encode($return);
}
else if ($post['act'] == 'ts_status4')
{
	$qid = intval($post['qid']);
	if($qid<=0)
	{
		$return = array('msg'=>'非法参数qid','return'=>4);
	}
	else
	{
		$complainInfo = $_ENV['complain']->Get($qid);
		if (isset($complainInfo['id']))
		{
			$dataArr = array(
					'loginId'=>taddslashes($post['loginId']),
					'countdown_time'=>intval($post['countdown_time']),
					'receive_time'=>$time,
					'status'=>4,
					);
			$result = $_ENV['complain']->Update($qid,$dataArr);
			
			if($result)
			{
				$_ENV['question']->rebuildQuestionDetail($post['qid'],"complain");
				$return = array('msg'=>'成功','return'=>1);
			}
			else
			{
				$return = array('msg'=>'失败','return'=>2);
			}
		}
		else
		{
			$return = array('msg'=>'qid不存在','return'=>3);
		}
	}
		
	echo json_encode($return);
}
else if ($post['act'] == 'ts_assess1')
{
	$qid = intval($post['qid']);
	if($qid<=0)
	{
		$return = array('msg'=>'非法参数qid','return'=>4);
	}
	else
	{
		$complainInfo = $_ENV['complain']->Get($qid);
		if (isset($complainInfo['id']))
		{
			if ($complainInfo['assess']==1 && $complainInfo['status']==3)
			{
				$return = array('msg'=>'重复评价,成功','return'=>1);
			}
			else
			{
				$dataArr = array('asnum'=>'_asnum+1','astime'=>$time,'assess'=>1,'status'=>3);
				$result = $_ENV['complain']->Update($qid,$dataArr);
				
				if($result)
				{
					$_ENV['question']->rebuildQuestionDetail($post['qid'],"complain");
					$return = array('msg'=>'成功','return'=>1);
				}
				else
				{
					$return = array('msg'=>'失败','return'=>2);
				}
			}
		}
		else
		{
			$return = array('msg'=>'qid不存在','return'=>3);
		}
	}
		
	echo json_encode($return);
}
else if($post['act'] == 'ts_assess2')
{
	$qid = intval($post['qid']);
	if($qid<=0)
	{
		$return = array('msg'=>'非法参数qid','return'=>4);
	}
	else
	{
		$complainInfo = $_ENV['complain']->Get($qid);
		if (isset($complainInfo['id']))
		{
			if ($complainInfo['assess']==2 && $complainInfo['status']==3)
			{
				$return = array('msg'=>'重复评价,成功','return'=>1);
			}
			else
			{
				$dataArr = array('asnum'=>'_asnum+1','astime'=>$time,'assess'=>2,'status'=>3);
				$result = $_ENV['complain']->Update($qid,$dataArr);
				
				if($result)
				{
					$_ENV['question']->rebuildQuestionDetail($post['qid'],"complain");
					$return = array('msg'=>'成功','return'=>1);
				}
				else
				{
					$return = array('msg'=>'失败','return'=>2);
				}
			}
		}
		else
		{
			$return = array('msg'=>'qid不存在','return'=>3);
		}
	}
		
	echo json_encode($return);
}
else if($post['act'] == 'ts_orderid')
{
	$qid = intval($post['qid']);
	if($qid<=0)
	{
		$return = array('msg'=>'非法参数qid','return'=>4);
	}
	else
	{
		$complainInfo = $_ENV['complain']->Get($qid);
		if (isset($complainInfo['id']))
		{
			$dataArr = array('order_id'=>taddslashes($post['order_id']));
			$result = $_ENV['complain']->Update($qid,$dataArr);
				
			if($result)
			{
				$return = array('msg'=>'成功','return'=>1);
			}
			else
			{
				$return = array('msg'=>'失败','return'=>2);
			}
		}
		else
		{
			$return = array('msg'=>'qid不存在','return'=>3);
		}
	}
		
	echo json_encode($return);
}
else if($post['act'] == 'user_confirm')
{
	$qid = intval($post['qid']);
	if($qid<=0)
	{
		$return = array('msg'=>'非法参数qid','return'=>4);
	}
	else
	{
		$complainInfo = $_ENV['complain']->Get($qid);
		if (isset($complainInfo['id']))
		{
			$dataArr = array(
					'order_id'=>taddslashes($post['order_id']),
					'good_id'=>taddslashes($post['good_id']),
					'author'=>taddslashes($post['author']),
					'author_id'=>taddslashes($post['author_id']),
					);
			$result = $_ENV['complain']->Update($qid,$dataArr);
	
			if($result)
			{
				$_ENV['question']->rebuildQuestionDetail($post['qid'],"complain");
				$return = array('msg'=>'成功','return'=>1);
			}
			else
			{
				$return = array('msg'=>'失败','return'=>2);
			}
		}
		else
		{
			$return = array('msg'=>'qid不存在','return'=>3);
		}
	}
		
	echo json_encode($return);
}
else if($post['act'] == 'evaluate_count')
{
	$count = intval($post['count']);
	
	if($count>0)
	{
		$base->load('setting');
		$result = $_ENV['setting']->replace(array('k'=>'EvaluateCount','v'=>$count));
		
		if($result)
		{
			$return = array('msg'=>'成功','return'=>1);
			$base->cache->set('EvaluateCount',$count,3600);
		}
		else
		{
			$return = array('msg'=>'失败','return'=>2);
		}
	}
	else
	{
		$return = array('msg'=>'非法值count','return'=>3);
		
	}
		
	echo json_encode($return);
}
else if($post['act'] == 'call')
{
	!isset($post['call_time']) && exit(json_encode(array('msg'=>'call_time参数为空')));
	!isset($post['call_type']) && exit(json_encode(array('msg'=>'call_type参数为空')));
	
	$call_type = config::getCallType();
	$time = intval(strtotime($post['call_time']));
	$type = isset($call_type[intval($post['call_type'])])?intval($post['call_type']):1;
	
	$qid = intval($post['qid']);
	if($qid<=0)
	{
		$return = array('msg'=>'非法参数qid','return'=>4);
	}
	else
	{
		$complainInfo = $_ENV['complain']->Get($qid,'id,call_time');
		if (isset($complainInfo['id'])&&($complainInfo['call_time']<$time))
		{
			$dataArr = array('call_time'=>$time,'call_type'=>$type);
			$result = $_ENV['complain']->Update($qid,$dataArr);
	
			if($result)
			{
				$_ENV['question']->rebuildQuestionDetail($post['qid'],"complain");
				$return = array('msg'=>'成功','return'=>1);
			}
			else
			{
				$return = array('msg'=>'失败','return'=>2);
			}
		}
		else
		{
			$return = array('msg'=>'qid不存在','return'=>3);
		}
	}
		
	echo json_encode($return);
}
else if($post['act'] == 'transform') // 投诉转咨询，建议
{
	intval($post['qid'])<=0 && exit(json_encode(array('msg'=>'非法参数qid','return'=>0)));
	trim($post['loginId'])=='' && exit(json_encode(array('msg'=>'loginId 参数为空','return'=>0)));
	trim($post['reason'])=='' && exit(json_encode(array('msg'=>'reason 参数为空','return'=>0)));
	!in_array($post['to_type'],array('suggest','ask')) && exit(json_encode(array('msg'=>'非法参数to_type','return'=>0)));

	$result = $_ENV['complain']->complainQuestionTransform($post);
	switch($result)
	{
		case 2:
			$return = array('msg'=>'failure','return'=>2);
			break;
		case 3:
			$return = array('msg'=>'sc投诉转咨询、建议开关没打开','return'=>3);
			break;
		case 4:
			$return = array('msg'=>'问题不存在','return'=>4);
			break;
		default:
			$_ENV['question']->rebuildQuestionDetail($post['qid'],"complain");
			$_ENV['question']->rebuildQuestionDetail($result,"question");
			$return = array('msg'=>'success','return'=>1);
			$base->delete_search('c_'.$post['qid']);
			$QuestionInfo = $_ENV['question']->Get($result);
			$q_search['id'] = $QuestionInfo['id'];
			$q_search['title'] = $QuestionInfo['description'];
			$q_search['description'] = $QuestionInfo['description'];
			$q_search['tag'] = json_encode(array(),true);
			$q_search['time'] = 0;
			$q_search['atime'] = 0;
			if($QuestionInfo['hidden']==1)
			{
				try
				{
					$base->set_search($q_search);
					
				}
				catch(Exception $e)
				{
					send_AIC('http://sc.5173.com/model/complain.class.php/complainQuestionTransform','投诉转咨询建议搜索服务器添加失败',1,'搜索接口');
				}   							
			}
			break;
	}
	exit(json_encode($return));
}
else if($post['act'] == 'orderPost')
{
	$qtype = intval($post['qtype']);
	$author = trim($post['author']);
	$orderId = trim($post['orderid']);
	$qtype<=0 && exit(json_encode(array('msg'=>'非法参数qtype','return'=>0)));
	empty($author) &&　exit(json_encode(array('msg'=>'author为空','return'=>0)));
	empty($orderId) &&　exit(json_encode(array('msg'=>'orderId为空','return'=>0)));
	
	$result = $_ENV['question']->getBaoXianOrderPost($post);
	if($result==1)
	{
		$return = array('msg'=>'success','return'=>1);
	}
	else if($result==2)
	{
		$return = array('msg'=>'qtype 不存在','return'=>2);
	}
	else if($result==0)
	{
		$return = array('msg'=>'failure','return'=>0);
	}
	
	exit(json_encode($return));
}
else
{
	echo json_encode(array('msg'=>'act参数不合法','return'=>0));
}
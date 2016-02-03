<?php
header("Content-type: text/html; charset=gb2312"); 
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', substr(dirname(__FILE__), 0, -4));
// 普通的 http 通知方式
error_reporting(0);
defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
require_once TIPASK_ROOT.'/config.php';
require TIPASK_ROOT.'/model/base.class.php';
require TIPASK_ROOT.'/lib/config.class.php';
require TIPASK_ROOT.'/lib/db.class.php';
require TIPASK_ROOT.'/lib/pdo/Mysql/pdo.class.php';
require TIPASK_ROOT.'/lib/pdo/Mysql/hash.class.php';
require_once TIPASK_ROOT.'/lib/global.func.php';
require_once TIPASK_ROOT.'/lib/CacheMemcache.class.php';
//require_once TIPASK_ROOT.'/lib/CacheRedis.class.php';
$table = array('table_question'=>"ask_question",'table_complain'=>"ask_complain");

$returnArray = array('suggest'=>array(),'ask'=>array(),'complain'=>array());
$type = trim($_GET['type'])=="new"?"new":"old";
$count = intval($_GET['count'])>0?intval($_GET['count']):10;
$qtype = intval($_GET['qtype'])>0?intval($_GET['qtype']):0;
$memcache = new CacheMemcache();
$base = new base();
$pdo = $base->init_pdo($table['table_question']);


	$base->load('question');
	$base->load('qtype');
	$base->load('answer');
	$base->load('category');
	$base->load('complain');

	$q = $memcache->get('newqtype_list');
	if(false !== $q)
	{
		$qtypeList = json_decode($q,true);
	}
	else
	{
		$qtypeList = $_ENV['qtype']->GetAllQType(1,"",0);
		$memcache->set('qtype_list',json_encode($qtypeList),30*60);//缓存60秒
	}
	if(!isset($qtypeList[$qtype]))
	{
		$qtype = 0;
	}
	if($qtype>0)
	{
		$faq = unserialize($qtypeList[$qtype]['faq']);
		if($faq['visiable']==0)
		{
			die(iconv("UTF-8", "gb2312//IGNORE", "尚未开通"));
		}
	}
if($type=="old")
{
	$table_question = $base->getDbTable($table['table_question']);
	$table_complain = $base->getDbTable($table['table_complain']);
	$c = $memcache->get('ctype_ask');
	if(false !== $c)
	{
		$ctype['ask'] = json_decode($c,true);
	}
	else
	{
		$ctype['ask'] = $_ENV['category']->getByQuestionType('ask');
		$memcache->set('ctype_ask',json_encode($ctype['ask']),30*60);//缓存60秒
	}
	$c = $memcache->get('ctype_suggest');
	if(false !== $c)
	{
		$ctype['suggest'] = json_decode($c,true);
	}
	else
	{
		$ctype['suggest'] = $_ENV['category']->getByQuestionType('suggest');
		$memcache->set('ctype_suggest',json_encode($ctype['suggest']),30*60);//缓存60秒
	}
	$whereQtype = $qtype==0?" ":" and qtype = ".$qtype;
	$cache_data = $memcache->get('faq_'.$qtype."_".$count);
	if(false !== $cache_data) exit($cache_data);
	$sql = "SELECT qtype,id,description,atime FROM $table_question WHERE 1 $whereQtype and revocation=0 AND pid=0 AND cid=".$ctype['suggest']['id']." AND hidden = 1 ORDER BY time DESC LIMIT $count";
	$suggest_query = $pdo->getAll($sql);
	foreach($suggest_query as $key => $row)
	{
		if($row['atime']>0)
		{
			$answer = $_ENV['answer']->get($row['id']);
		}
		else
		{
			$answer = array('content'=>"等待客服回复");
		}
		if(isset($qtypeList[$row['qtype']]))
		{
			$type = "[".$qtypeList[$row['qtype']]['name']."] ";    
		}
		else 
		{
			$type = "[其他分类] "; 
		}
		$row['description'] = preg_replace('/[\s"]/','',$row['description']);
		$row['description'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$row['description']);
		$row['description'] = strip_tags($row['description']);
		
		$answer['content'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$answer['content']);
		$answer['content'] = preg_replace('/[\s"]/','',$answer['content']);
		$answer['content'] = strip_tags($answer['content']);

		$returnArr['suggest'][$row['id']] = "{title:\"".cutstr($type.trim($row['description']),17)."\",url:\"".$_ENV['question']->getQuestionLink($row['id'],"question")."\",answer:\"".cutstr(trim($answer['content']),15)."\"}";
	}
	$sql = "SELECT qtype,id,description,atime FROM $table_question WHERE 1 $whereQtype and  revocation=0 AND pid=0 AND cid=".$ctype['ask']['id']." AND hidden = 1 ORDER BY time DESC LIMIT $count";

	$ask_query = $pdo->getAll($sql);
	foreach($ask_query as $key => $row)
	{
		if($row['atime']>0)
		{
			$answer = $_ENV['answer']->get($row['id']);
		}
		else
		{
			$answer = array('content'=>"等待客服回复");
		}
		if(isset($qtypeList[$row['qtype']]))
		{
			$type = "[".$qtypeList[$row['qtype']]['name']."] ";    
		}
		else 
		{
			$type = "[其他分类] "; 
		}
		$row['description'] = preg_replace('/[\s"]/','',$row['description']);
		$row['description'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$row['description']);
		$row['description'] = strip_tags($row['description']);
		
		$answer['content'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$answer['content']);
		$answer['content'] = preg_replace('/[\s"]/','',$answer['content']);
		$answer['content'] = strip_tags($answer['content']);
		$returnArr['ask'][$row['id']] = "{title:\"".cutstr($type.trim($row['description']),17)."\",url:\"".$_ENV['question']->getQuestionLink($row['id'],"question")."\",answer:\"".cutstr(trim($answer['content']),15)."\"}";
	}

	$sql = "SELECT qtype,id,description,time,atime FROM $table_complain where 1 $whereQtype and  public =0 ORDER BY time DESC LIMIT $count";
	$complain_query = $pdo->getAll($sql);
	foreach($complain_query  as $key => $row)
	{ 
		if(($row['atime']>0)||($row['time']==$row['atime']))
		{
			$answer = $_ENV['complain']->GetAnswer($row['id']);
		}
		else
		{
			$answer = array('content'=>"等待客服回复");
		}
		if(isset($qtypeList[$row['qtype']]))
		{
			$type = "[".$qtypeList[$row['qtype']]['name']."] ";    
		}
		else 
		{
			$type = "[其他分类] "; 
		}
		$row['description'] = preg_replace('/[\s"]/','',$row['description']);
		$row['description'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$row['description']);
		$row['description'] = strip_tags($row['description']);
		
		$answer['content'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$answer['content']);
		$answer['content'] = preg_replace('/[\s"]/','',$answer['content']);
		$answer['content'] = strip_tags($answer['content']);
		if(trim($answer['content'])=="")
		{
			$answer['content'] = "等待客服回复";    
		}
		$returnArr['complain'][$row['id']] = "{title:\"".cutstr($type.trim($row['description']),17)."\",url:\"".$_ENV['question']->getQuestionLink($row['id'],"complain")."\",answer:\"".cutstr(trim($answer['content']),15)."\"}";
	}
	$str = $_GET['jsoncallback']."({suggest:[".implode(",",$returnArr['suggest'])."],ask:[".implode(",",$returnArr['ask'])."],complain:[".implode(",",$returnArr['complain'])."]})";
	$str = iconv("UTF-8", "gb2312//IGNORE", $str);
	$memcache->set('newfaq_'.$qtype."_".$count,$str,5);//缓存5秒
	exit($str);
}
else
{
	$cache_data = $memcache->get('newtype_faq_'.$qtype."_".$count."_answered");
	if(false !== $cache_data) exit($cache_data);
	$question_list = $_ENV['question']->front_hot_newquestion($qtype,0,$count,1);
	foreach($question_list as $key => $row)
	{
		if(isset($qtypeList[$row['qtype']]))
		{
			$type = "[".$qtypeList[$row['qtype']]['name']."] ";    
		}
		else 
		{
			$type = "[其他分类] "; 
		}
		if($row['Atime']>0)
		{
			if($row['categoryInfo']['question_type']=="complain")
			{
				$answer = $_ENV['complain']->GetAnswer($row['id']);
			}
			else
			{
				$answer = $_ENV['answer']->get($row['id']);
			}
		}
		else
		{
			$answer = array('content'=>"等待客服回复");
		}
		if($row['categoryInfo']['question_type']=="complain")
		{
			$url = $_ENV['question']->getQuestionLink($row['id'],"complain");
		}
		else
		{
			$url = $_ENV['question']->getQuestionLink($row['id'],"question");
		}
		
		$row['description'] = preg_replace('/[\s"]/','',$row['description']);
		$row['description'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$row['description']);
		$row['description'] = strip_tags($row['description']);
		
		$answer['content'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$answer['content']);
		$answer['content'] = preg_replace('/[\s"]/','',$answer['content']);
		$answer['content'] = strip_tags($answer['content']);
		$returnArr[$row['id']] = "{title:\"".cutstr($type.trim($row['description']),17)."\",url:\"".$url."\",answer:\"".cutstr(trim($answer['content']),15)."\"}";	
	}
	$buttonArr['ask'] = array('name' => "我要咨询",
								'url' => $qtype==0?"http://sc.5173.com/index.php?question/ask_run/ask.html":"http://sc.5173.com/index.php?question/ask/".$qtype.".html" );
	$buttonArr['suggest'] = array('name' => "我要建议",
								'url' => $qtype==0?"http://sc.5173.com/index.php?question/ask_run/suggest.html":"http://sc.5173.com/index.php?question/suggest/".$qtype.".html" );
	$buttonArr['complain'] = array('name' => "我要投诉",
								'url' => $qtype==0?"http://sc.5173.com/index.php?question/ask_run/complain.html":"http://sc.5173.com/index.php?question/complain/".$qtype.".html" );
	foreach($buttonArr as $key => $value)
	{
		$button[$key] = "{name:\"".$value['name']."\",url:\"".$value['url']."\"}";
	}
	$str = $_GET['jsoncallback']."({questionList:[".implode(",",$returnArr)."],button:[".implode(",",$button)."]})";
	$str = iconv("UTF-8", "gb2312//IGNORE", $str);
	$memcache->set('newtype_faq_'.$qtype."_".$count,$str,3);//缓存5秒
	exit($str);	
}




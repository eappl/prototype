<?php 
error_reporting(0);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', substr(dirname(__FILE__), 0, -4));
defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
date_default_timezone_set('Etc/GMT-8');
require_once TIPASK_ROOT.'/config.php';
require_once TIPASK_ROOT.'/lib/db.class.php';
require_once TIPASK_ROOT.'/model/base.class.php';
require_once TIPASK_ROOT.'/lib/global.func.php';
require_once TIPASK_ROOT.'/lib/CacheMemcache.class.php';
require_once TIPASK_ROOT.'/lib/config.class.php';
require_once TIPASK_ROOT.'/lib/pdo/Mysql/hash.class.php';
$base = new base();
// 获取complian站点的新投诉问题
$base->load('complain');
$base->load('question');
$base->load('qtype');
if ($_GET['act'] == 'ts_new') 
{
	 md5($_GET['data'].'ts_new') != strtolower($_GET['key']) && exit; 
	 
	 $data = json_decode($_GET['data'],true); 
	 !isset($data['id'])  && exit;
	 !isset($data['sid']) && exit;
	 $data = taddslashes_new($data);
	 
	$sync = $_ENV['complain']->getSyncByComplain($data['id']);
	if($sync['cpid']>0)
	{
		$return_arr = array('return'=>1,'comment'=>$sync['scid'].",".$sync['cpid']);
	}
	else
	{
		$contactArr = explode(';',$data['contact']);
		if(isset($contactArr['1']))
		{
			$contact = array('OnceAnsweredQQ'=>$contact['3'],'contact'=>array('moblie'=>$contactArr['2'],'weixin'=>$contactArr[1],'qq'=>$contactArr['0']));
		}
		else
		{
			$contact = array('OnceAnsweredQQ'=>"",'contact'=>array('mobile'=>$contactArr['0'],'weixin'=>'','qq'=>''));
		}
		$contact = serialize($contact);
		$comment = array('OS'=>$data['OS'],'Browser'=>$data['Brower']);
		$qtypeInfo  = $_ENV['qtype']->GetQTypeByComplain($data['jid']);
				
		$ComplainArr = array('sid'=>$data['sid'],'sname'=>$data['sname'],'jid'=>$data['jid'],'jname'=>$data['jname'],'order_id'=>$data['order_id'],'good_id'=>$data['good_id'],
		'title'=>$data['title'],'description'=>$data['description'],'photo'=>$data['photo'],'contact'=>$contact,'real_name'=>$data['real_name'],'author'=>$data['author'],
		'author_id'=>$data['author_id'],'time'=>$data['time'],'receive_time'=>$data['receive_time'],'countdown_time'=>$data['countdown_time'],'assess'=>$data['assess'],
		'status'=>$data['status'],'sync'=>1,'public'=>$data['_public'],'qtype'=>$qtypeInfo['id'],'comment'=>serialize($comment),'loginId'=>$data['loginId']);
					
		$cid = $_ENV['complain']->insertNewComplainBySync($ComplainArr,$data['id']);		
		if($cid)
		{
			$update_num = $_ENV['question']->modifyUserQtypeNum(date("Y-m-d",$data['time']),$qtypeInfo['id'],'complain',1);					
			$return_arr = array('return'=>1,'comment'=>"$cid,{$data['id']}");
		}
		else
		{
			$return_arr = array('return'=>0);
		}
	}
	echo json_encode($return_arr);		
}
function taddslashes_new($string, $force = 0) 
{
	if (!MAGIC_QUOTES_GPC || $force) 
	{
		if (is_array($string)) 
		{
			foreach ($string as $key => $val)
			 {
				$string[$key] = taddslashes_new($val, $force);
			}
		} 
		else
	    {
			$string = addslashes($string);
		}
	}
	return $string;
}
?>

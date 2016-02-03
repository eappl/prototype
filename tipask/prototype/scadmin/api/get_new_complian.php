<?php 
define('TIPASK_ROOT', substr(dirname(__FILE__), 0, -4));
defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
date_default_timezone_set('Etc/GMT-8');
require_once TIPASK_ROOT.'/config.php';
require_once TIPASK_ROOT.'/lib/db.class.php';

// 获取complian站点的新投诉问题

if ($_GET['act'] == 'ts_new') 
{
	 md5($_GET['data'].'ts_new') != strtolower($_GET['key']) && exit; 
	 
	 $data = json_decode($_GET['data'],true); 
	 !isset($data['id'])  && exit;
	 !isset($data['sid']) && exit;
	 $data = taddslashes($data);
	 
	$db  = new db(DB_HOST, DB_USER, DB_PW, DB_NAME , DB_CHARSET , DB_CONNECT);
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
	
	$qtypeInfo  = $db->fetch_first("SELECT id FROM ".DB_TABLEPRE."qtype WHERE complain_type_id={$data['jid']}");
		
	if(!empty($qtypeInfo))
	{
		$qtypeId = $qtypeInfo['id'];
	
	}
	else
	{
		$otherQtypeId = $db->fetch_first("SELECT id FROM ".DB_TABLEPRE."qtype WHERE name='其他问题'");
		if(!empty($otherQtypeId)){
			$qtypeId = $otherQtypeId['id']; // 其他交易
		}
	
	}
	
	if($data['status'] == 1)
	{
		$sql = 'INSERT INTO ask_complain'.
				'(sid,sname,jid,jname,order_id,good_id,title,description,photo,contact,real_name,author,'.
				'author_id,time,receive_time,countdown_time,assess,status,sync,public,qtype)'.
				'VALUES'.
				"('{$data['sid']}','{$data['sname']}','{$data['jid']}','{$data['jname']}','{$data['order_id']}',
				'{$data['good_id']}','{$data['title']}','{$data['description']}','{$data['photo']}','{$contact}',
				'{$data['real_name']}','{$data['author']}','{$data['author_id']}','{$data['time']}','{$data['receive_time']}',
				'{$data['receive_time']}','{$data['assess']}','{$data['status']}',1,0,$qtypeId)";
		
	}
	else 
	{
		$sql = 'INSERT INTO ask_complain'.
				'(sid,sname,jid,jname,order_id,good_id,title,description,photo,contact,real_name,author,'.
				'author_id,time,receive_time,countdown_time,assess,status,sync,public,qtype)'.
				'VALUES'.
				"('{$data['sid']}','{$data['sname']}','{$data['jid']}','{$data['jname']}','{$data['order_id']}',
				'{$data['good_id']}','{$data['title']}','{$data['description']}','{$data['photo']}','{$contact}',
				'{$data['real_name']}','{$data['author']}','{$data['author_id']}','{$data['time']}','{$data['receive_time']}',
				'{$data['countdown_time']}','{$data['assess']}','{$data['status']}',1,0,$qtypeId)";
	}
	$db->query($sql);
	$cid = $db->insert_id(); 
		
	if ($cid>0)
	{
		$db->query("INSERT INTO ask_sync(cpid,scid,sync) VALUES({$data['id']},$cid,1)");
		exit("$cid,{$data['id']}");
	}
	else
	{
		exit;
	}
}
function taddslashes($string, $force = 0) 
{
	if (!MAGIC_QUOTES_GPC || $force) 
	{
		if (is_array($string)) 
		{
			foreach ($string as $key => $val)
			 {
				$string[$key] = taddslashes($val, $force);
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

<?php
error_reporting(0);
date_default_timezone_set('Etc/GMT-8');
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(dirname(__FILE__)));
ini_set('include_path', dirname(dirname(__FILE__)));

require 'config.php';
require 'lib/db.class.php';
require 'lib/CacheMemcache.class.php';
require 'model/base.class.php';

//标签与游戏缓存
class tag extends base {

    function tag() {
        $this->init_db();
        $this->init_cache();
        $this->load("question");
        $this->load("tag");
    }

    function tg2mem() {
    	set_time_limit(0);
    	$this->tag2mem();   	
    	$this->taglist2mem();
    	//$this->game2mem();
    }
    
    //标签memcache缓存
    function tag2mem(){
    	$zx = $this->getType(1);
    	$jy = $this->getType(2);
    	$ts = $this->getType(3);
    	$tag_arr = $this->db->fetch_all("SELECT id FROM ".DB_TABLEPRE."tag WHERE pid<>0");
    	foreach($tag_arr as $tag){
    		$sign = md5($tag['id'].'020');
    		$sql = "SELECT
					  id,
    				  cid,
					  title,
					  time,
					  views,
					  status,
					  q_handle_status,
    				  qtype,
					  atime AS Atime,
    				  description
					FROM ".DB_TABLEPRE."question
					WHERE pid = 0
					    AND revocation = 0
					    AND tag LIKE '%".$tag['id']."%'
					ORDER BY time DESC
					LIMIT 0,20";
    		$data = $this->db->fetch_all($sql);
    		foreach($data as $k=>$v){
    			if($data[$k]['cid'] == $zx){
    				$data[$k]['type'] = '[咨询] ';
    			}elseif($data[$k]['cid'] == $ts){
    				$data[$k]['type'] = '[投诉] ';
    			}elseif($data[$k]['cid'] == $jy){
    				$data[$k]['type'] = '[建议] ';
    			}else{
    				$data[$k]['type'] = '[垃圾箱] ';
    			}
    			$data[$k]['time']  = !empty($v['time']) ?$this->timeToText($v['time']) :''; //date("Y-m-d H:i", $v['time']);
    			$data[$k]['Atime'] = !empty($v['Atime'])?$this->timeLagToText($v['time'],$v['Atime']):'';//!empty($v['Atime']) ? date("Y-m-d H:i", $v['Atime']) : '';
    			$data[$k]['views'] = intval($v['views']);
    		}	
    		if(!empty($data)) $this->cache->set($sign,$data,600);//缓存10分钟
    		
    		$sign_count = md5('[count]'.$tag['id']);
    		$sql = "SELECT
				      COUNT(*)
				    FROM ".DB_TABLEPRE."question
    				WHERE pid = 0
    					AND revocation = 0
						AND tag LIKE '%".$tag['id']."%'";
    		$count = $this->db->result_first($sql);
    		if(!empty($count)) $this->cache->set($sign_count,$count,600);//写入缓存 ，缓存时间为10分钟
    	}   	    	   	
    }
    
    function taglist2mem(){
    	$tag_arr = $this->db->fetch_all("SELECT id FROM ".DB_TABLEPRE."tag WHERE pid<>0");
    	foreach($tag_arr as $tag){
    		$sign = md5('[tag]'.$tag['id']);
    		$list = array();
    		$t_arr = $_ENV['tag']->getNameById();
    		$tag_list = $_ENV['tag']->get_tag();
    		$tag_str = " AND tag LIKE '%".$tag['id']."%' ";
    		$sql = "SELECT id,tag FROM ".DB_TABLEPRE."question WHERE pid=0 AND revocation=0 $tag_str";
    		$query = $this->db->query($sql);
    		while($row = $this->db->fetch_array($query)){
    			$tag_tmp = json_decode($row['tag'],true);
    			if(!empty($tag_tmp)){
    				foreach($tag_tmp as $v){
    					if(!isset($list[$v]['id'])) $list[$v]['id'] = $v;
    					if(!isset($list[$v]['name'])) $list[$v]['name'] = $t_arr[$v];
    					if(!isset($list[$v]['num']))
    						$list[$v]['num'] = 1;
    					else
    						$list[$v]['num']++;
    				}
    			}
    		}
    		foreach($tag_list as $key => &$child){
    			foreach($child['child'] as $k => &$v){
    				if(array_key_exists($k, $list)){
    					$v = $list[$k];
    				}else{
    					unset($child['child'][$k]);
    				}
    			}
    			if(empty($child['child'])) unset($tag_list[$key]);
    		}
    		if(!empty($tag_list)) $this->cache->set($sign,$tag_list,600);
    	}
    }
    
    function game2mem(){
    	$zx = $this->getType(1);
    	$jy = $this->getType(2);
    	$ts = $this->getType(3);
    	$left_game = $_ENV['question']->get_question_game();
    	foreach($left_game as $game){
    		$game_id_arr[] = "'".$game['gameid']."'";
    		$game_str = " AND gameid='".$game['gameid']."' ";
    		$sign = md5($game_str.'020');
    		$sql = "SELECT
					  id,
    				  cid,
					  title,
					  time,
					  views,
					  status,
					  q_handle_status,
    				  qtype,
					  atime AS Atime,
    				  description
					FROM ".DB_TABLEPRE."question
					WHERE pid = 0
					    AND revocation = 0 $game_str					    
					ORDER BY time DESC
					LIMIT 0,20";
    		$data = $this->db->fetch_all($sql);
    		foreach($data as $k=>$v){
    			if($data[$k]['cid'] == $zx){
    				$data[$k]['type'] = '[咨询] ';
    			}elseif($data[$k]['cid'] == $ts){
    				$data[$k]['type'] = '[投诉] ';
    			}elseif($data[$k]['cid'] == $jy){
    				$data[$k]['type'] = '[建议] ';
    			}else{
    				$data[$k]['type'] = '[垃圾箱] ';
    			}
    			$data[$k]['time']  = !empty($v['time']) ?$this->timeToText($v['time']) :''; //date("Y-m-d H:i", $v['time']);
    			$data[$k]['Atime'] = !empty($v['Atime'])?$this->timeLagToText($v['time'],$v['Atime']):'';//!empty($v['Atime']) ? date("Y-m-d H:i", $v['Atime']) : '';
    			$data[$k]['views'] = intval($v['views']);
    		}
    		if(!empty($data)) $this->cache->set($sign,$data,600);//缓存10分钟
    	}
    	
    	$game_other_str = " AND gameid<>'' AND gameid NOT IN (".implode(',',$game_id_arr).") ";
    	$sign_other = md5($game_other_str.'020');
    	$sql = "SELECT
			      id,
    			  cid,
			      title,
			      time,
			      views,
			      status,
			      q_handle_status,
			      atime           AS Atime,
    			  description
			    FROM ".DB_TABLEPRE."question
			    WHERE pid = 0
			        AND revocation = 0 $game_other_str
			    ORDER BY time DESC
			    LIMIT 0,20";
    	$data_other = $this->db->fetch_all($sql);
    	foreach($data_other as $k=>$v){
    		if($data_other[$k]['cid'] == $zx){
    			$data_other[$k]['type'] = '[咨询]';
    		}elseif($data_other[$k]['cid'] == $ts){
    			$data_other[$k]['type'] = '[投诉]';
    		}elseif($data_other[$k]['cid'] == $jy){
    			$data_other[$k]['type'] = '[建议]';
    		}
    		$data_other[$k]['time']  = date("Y-m-d H:i", $v['time']);
    		$data_other[$k]['Atime'] = !empty($v['Atime']) ? date("Y-m-d H:i", $v['Atime']) : '';
    		$data_other[$k]['views'] = intval($v['views']);
    	}
    	if(!empty($data_other)) $this->cache->set($sign_other,$data_other,600);//缓存10分钟
    	
    }

    //获取咨询或投诉建议类型
    function getType($type=''){
    	$cid = '';
    	if($type == 1) {
    
    		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE name='咨询'";
    		$cache_key = md5($sql);
    		$cache_data = $this->cache->get($cache_key);
    		if(false !== $cache_data) return $cache_data;
    
    		$cid = $this->db->result_first($sql);
    		if(!empty($cid)) {
    			$this->cache->set($cache_key,$cid,2592000);
    		}
    	}elseif($type == 2) {
    
    		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE name='建议与意见'";
    		$cache_key = md5($sql);
    		$cache_data = $this->cache->get($cache_key);
    		if(false !== $cache_data) return $cache_data;
    
    		$cid = $this->db->result_first($sql);
    		if(!empty($cid)) {
    			$this->cache->set($cache_key,$cid,2592000);
    		}
    	}elseif($type==3) {
    		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE name='投诉'";
    		$cache_key = md5($sql);
    		$cache_data = $this->cache->get($cache_key);
    		if(false !== $cache_data) return $cache_data;
    
    		$cid = $this->db->result_first($sql);
    		if(!empty($cid)) {
    			$this->cache->set($cache_key,$cid,2592000);
    		}
    	}
    	return $cid;
    }
}

$crontab = new tag();
$crontab->tg2mem();
?>
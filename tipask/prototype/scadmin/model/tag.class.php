<?php

!defined('IN_TIPASK') && exit('Access Denied');

class tagmodel {

    var $db;
    var $base;
    var $cache;

    function tagmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }
    
	/* 查找父标签 */
    function get_tid($tid) {
    	if($this->db->fetch_total("tag","pid=$tid"))
    		echo 1;
    	else
    		echo 2;
    }
    
	/* 获取所有标签及其二级子标签  */ 
    function get_tag_list() {
       	    $sql = "SELECT id,name,pid,displayorder,questions FROM " . DB_TABLEPRE ."tag WHERE pid = 0 ORDER BY id DESC,displayorder ASC";
       	    $tag_list =  $this->db->fetch_all($sql);
       	    foreach ($tag_list as $key=>$arr){
       	    	$child_list  = $this->get_child_taglist($arr['id']);
       	    	if($child_list != false){
       	    		$tag_list[$key]['child'] = $child_list;
       	    	}
       	    }
       	    return $tag_list;
    }
/**
 * 
 * @param int $id 父标签id
 * @return array
 */    
    function get_child_taglist($id){
    	$arr = array();
    	$sql = "SELECT id,name,pid,displayorder,questions FROM " . DB_TABLEPRE ."tag WHERE pid = '$id' ORDER BY id DESC,displayorder ASC";
    	$child = $this->db->fetch_all($sql);
    	foreach ($child as $key=>$row){
    		$arr[$key]['id'] = $row['id'];
    		$arr[$key]['name'] = '--' . $row['name'];
    		$arr[$key]['tag_name'] =  $row['name'];
    		$arr[$key]['pid'] = $row['pid'];
    		$arr[$key]['questions'] = $row['questions'];
    	}
    	return $arr;
    }
    /* 添加标签 */
    function add($name,$pid = 0) {
    	$sql = "INSERT INTO `" . DB_TABLEPRE . "tag`(`name`,`pid`) VALUES ";
    	$sql .= "('$name',$pid)";
    	return $this->db->query($sql);
    }
    
     /* 更该标签名  */
    function set($tid,$name){
    	$this->db->update_field("tag","name",$name,"id = ".$tid);
    }
    /* 删除标签 */ 
    function remove($id) {
       $this->db->query("DELETE FROM `" . DB_TABLEPRE . "tag` WHERE `id` = '$id'");
    }
    
    function get_categories_list($qid)
	{
	 	 $cat_arr = '<table width="100%">';
	     $sql = "SELECT id,name,questions,displayorder,pid FROM " . DB_TABLEPRE ."tag WHERE pid=0 ORDER BY displayorder";
	     $res = $this->db->fetch_all($sql,'id');
	     foreach ($res as $row){	     	
	     	$cat_arr.='<tr><td>'.$row['name'].'</td><td>'.$this->get_child_list($row['id'],$qid).'</td></tr>';              
	     }
         $cat_arr.='</table>';
	     return $cat_arr; 
	}
	
	function get_child_list($tree_id = 0,$qid=0){
		$tag = array();
		if($qid){
			$q_arr = $this->db->fetch_first("SELECT * FROM `".DB_TABLEPRE."question` WHERE `id` = $qid");
			if(!empty($q_arr)){
				$tag = json_decode($q_arr['tag'],true);
			}
		}
	    $three_arr = '';
        $child_sql = "SELECT id,name,questions,displayorder,pid FROM " . DB_TABLEPRE ."tag WHERE pid = '$tree_id' ORDER BY displayorder";
        $res = $this->db->fetch_all($child_sql,'id');      
        foreach ($res as $row){
        	$checked = in_array($row['id'],$tag)?'checked':'';          
            $three_arr.='<input type="checkbox" id="tag'.$row['id'].'" '.$checked.' name="tag[]" value="'.$row['id'].'">'.$row['name'];
        }       
	    return $three_arr;
	}
	
		/* 根据条件获取所有标签及其二级子标签  */ 
    function get_tag_tree($tag_arr=array(),$game='') {
    	$sign = md5('[tag]'.implode(',', $tag_arr).$game);    	
    	$cache_data = $this->cache->get($sign);
    	if(false !== $cache_data) return $cache_data;
    	$list = array();
    	$t_arr = $this->getNameById();
       	$tag_list = $this->get_tag();
       	$tag_str = '';
       	if(!empty($tag_arr)){
       	    foreach($tag_arr as $tar){
       	    	$tag_str .= " AND tag LIKE '%$tar%' ";
       	    }
       	}      	    
       	$sql = "SELECT id,tag FROM ".DB_TABLEPRE."question WHERE pid=0 AND revocation=0 $tag_str $game";
       	$query = $this->db->query($sql);
       	while($row = $this->db->fetch_array($query)){
       	    $tag = json_decode($row['tag'],true);
       	    if(!empty($tag)){
       	    	foreach($tag as $v){
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
       	return $tag_list;
    }  
    
    function getById($id){
    	return $this->db->result_first("SELECT name FROM " . DB_TABLEPRE . "tag WHERE id ='".$id."'");
    }
    
    /*
     * 根据ID取标签名称*/
    function getNameById() {
    	$cache_data = $this->cache->get('[tag]');
    	if(false !== $cache_data) return $cache_data;
    	$taglist = $this->db->fetch_all("SELECT id,name FROM " . DB_TABLEPRE . "tag","id");
    	foreach($taglist as $key => &$val){
    		$taglist[$key]=$val['name'];
    	}
    	if(!empty($taglist)) $this->cache->set('[tag]',$taglist,1800);//标签缓存30分钟
    	return $taglist;
    }
    
    //前台获取标签分类树
    function get_tag(){
    	$cache_data = $this->cache->get('[tag_tree]');
    	if(false !== $cache_data) return $cache_data;
    	$tag_list = array();
    	$sql = "SELECT id,name FROM " . DB_TABLEPRE ."tag WHERE pid = 0 ";
    	$tag_list =  $this->db->fetch_all($sql);
    	foreach ($tag_list as $key=>$arr){
    		$child_list  = $this->get_child_tag($arr['id']);
    		$tag_list[$key]['child'] = $child_list;
    	}
    	if(!empty($tag_list)) $this->cache->set('[tag_tree]',$tag_list,1800);//标签缓存30分钟
    	return $tag_list;
    }
    
    function get_child_tag($id){
    	$sql = "SELECT id,name FROM " . DB_TABLEPRE ."tag WHERE pid = '$id'";
    	$tag_child = $this->db->fetch_all($sql,'id');
    	return $tag_child;
    }  

}

?>

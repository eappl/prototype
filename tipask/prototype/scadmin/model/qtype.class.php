<?php

!defined('IN_TIPASK') && exit('Access Denied');

class qtypemodel extends base{

    var $db;
    var $base;
    var $cache;

    function qtypemodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }
    
    //获取所有用户自选类型
    function GetAllQType($all = 1,$question_type='',$p=1,$allQuestion='')
    {           	
    	$wherep = ($p>0)?" and pid = 0 ":" ";
    	$is_visiable = ($all==1)?" and visiable = 1 ":"";
    	$sql = "SELECT * FROM ".DB_TABLEPRE."qtype WHERE 1 $is_visiable $wherep ORDER BY id";
    	$qtype = $this->db->fetch_all($sql,'id');
    	if($allQuestion == 1)
    	{
    		$qtype[0] = array('id' => 0,'name' => '全部类型', 'pid' => 0); 
    	 } 
    	ksort($qtype);
    	return $qtype;
    }	
	function updateQtype($id,$qtypeInfo)
	{
		foreach($qtypeInfo as $key => $value)
		{
			$txt[$key] = "`".$key."`='".$value."'";
		}
		$sql = "update ".DB_TABLEPRE."qtype set ".implode($txt,",")." where id = ".intval($id);
		return $this->db->query($sql);
	}
	function insertQtype($qtypeInfo)
	{
		foreach($qtypeInfo as $key => $value)
		{
			$array_key[$key] = $key;
			$array_value[$key] = "'".$value."'";			
		}
		$sql = "insert into " .DB_TABLEPRE."qtype (".implode($array_key,",").") values (".implode($array_value,",").")";
		return $this->db->query($sql);
	}
	
    
    //获取所有用户自选类型
    function GetQType($id,$fields = "*")
    {           	
    	$qtype_list = array();
    	$sql = "SELECT ".$fields." FROM ".DB_TABLEPRE."qtype WHERE id = $id";
    	$query = $this->db->query($sql);
    	/* $cache_key = md5($query);
    	$cache_data = $this->cache->get($cache_key);
    	if(false !== $cache_data) return $cache_data; */
    	
    	$data = $this->db->fetch_array($query);
    	/* if(!empty($data))
    	{
    		$this->cache->set($cache_key,$data,2592000);
    	} */
    	
    	return $data;
    }
    //获取所有用户自选类型
    function GetQTypeNum($date,$id,$question_type,$fields = "*")
    {           	
    	$date = strtotime($date)>0? $date:date("Y-m-d",time());
    	$qtype_list = array();
    	$sql = "SELECT ".$fields." FROM ".DB_TABLEPRE."question_num WHERE date = '".$date."' and qtype = $id and question_type='".$question_type."'";
    	$query = $this->db->query($sql);
    	
    	/* $cache_key = md5($query);
    	$cache_data = $this->cache->get($cache_key);
    	if(false !== $cache_data) return $cache_data; */
    	
    	$data = $this->db->fetch_array($query);
    	/* if(!empty($data))
    	{
    		$this->cache->set($cache_key,$data,2592000);
    	} */
    	return $data;
    }  
    //获取所有用户自选类型
    function GetQTypeByComplain($id,$fields = "*")
    {
    	$qtype_list = array();
    	$query = $this->db->query("SELECT ".$fields." FROM ".DB_TABLEPRE."qtype WHERE complain_type_id  = $id");
    
    	/* $cache_key = md5($query);
    	 $cache_data = $this->cache->get($cache_key);
    	if(false !== $cache_data) return $cache_data; */
    
    	$data = $this->db->fetch_array($query);
    	/* if(!empty($data))
    	 {
    	$this->cache->set($cache_key,$data,2592000);
    	} */
    	return $data;
    }
    //获取所有用户自选类型
    function GetOther($fields = "*")
    {
    	$query = $this->db->query("SELECT ".$fields." FROM ".DB_TABLEPRE."qtype WHERE name like '%其他%'");
    	

    	/* $cache_key = md5($query);
    	$cache_data = $this->cache->get($cache_key);
    	if(false !== $cache_data) return $cache_data; */
    	
    	$data = $this->db->fetch_array($query);
    	/* if(!empty($data))
    	{
    		$this->cache->set($cache_key,$data,2592000);
    	} */
    	return $data;
    }
    //根据用户所选问题的大类和主分类，确定是否需要输出子级菜单
    function GetSubList($qtype)
    {
    	$sql = "SELECT * FROM ".DB_TABLEPRE."qtype WHERE pid = ".$qtype;
    	//$query = $this->db->query($sql);
    	$data = $this->db->fetch_all($sql);
    	return $data;
    }   
    // 获取8大类当天问题和总共问题
    function getQuestionsNum($question_type,$qtype,$StartDate,$EndDate)
    {
    	$whereDate = ($StartDate == $EndDate)? " and date = '".$StartDate."'":" and date >= '".$StartDate."' and date <= '".$EndDate."' ";
    	$whereQuestionType = ($question_type=="")? " ":" and question_type = '".$question_type."' ";
    	$whereQType = ($qtype==0)? " ":" and qtype = '".$qtype."' ";
    	$sql = "SELECT qtype,question_type,sum(questions) as questions FROM ask_question_num where 1".$whereDate.$whereQuestionType.$whereQType." group by question_type,qtype";
    	$cache_key = md5($sql);
    	$cache_data = $this->cache->get($cache_key);
    	if(false !== $cache_data) return json_decode($cache_data,true); 
    	$return = $this->db->fetch_all($sql);
    	$this->cache->set($cache_key,json_encode($return),30);
    	return $return;
    }
    function getQuestionNumfront($question_type,$StartDate,$EndDate)
    {
        $qtypeList = $this->GetAllQType(1,$question_type,0);
        $pidList = array();
        foreach($qtypeList as $qtype => $qtypeInfo)
        {
            if($qtypeInfo['pid']>0)
            {
                $pidList[$qtypeInfo['pid']] = 1;
            }
        }
        foreach($pidList as $key => $value)
        {
            unset($qtypeList[$key]);    
        }
        $Numlist = $this->getQuestionsNum($question_type,0,$StartDate,$EndDate);
        foreach($Numlist as $key => $NumArr)
        {
            if(isset($qtypeList[$NumArr['qtype']]))
            {
               $qtypeList[$NumArr['qtype']]['questions_num'] =   $NumArr['questions'];   
            }  
        }
        return $qtypeList;   
    }
     
    //根据用户所选问题的大类和主分类   
    function getQtypeNnum($question_type,$qtype)
    {
    	$sql = "SELECT * FROM ask_qtype_num WHERE question_type = '".$question_type."' and qtype = ".$qtype;
    	$query = $this->db->query($sql);
    	return $this->db->fetch_array($query);
    }
    // 
    function getParentQtype($id)
    {
    	$sql = "select id,name from ask_qtype where id = (select pid from ask_qtype where id=$id)";
    	return $this->db->fetch_first($sql);
    }
}
?>

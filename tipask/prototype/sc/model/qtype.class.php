<?php

!defined('IN_TIPASK') && exit('Access Denied');

class qtypemodel extends base{

    var $db;
    var $base;
    var $cache;
    var $table_qtype = "ask_qtype";
	var $table_order_count = "ask_order_count";

    function qtypemodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
        $this->pdo = $this->base->init_pdo($this->table_qtype);
    }
   /**
    * 获取所有用户自选类型
    * complain visiable=0 时不显示
    * @param  $all 0显示所有问题，默认不显示隐藏问题
    * @param $question_type
    * @param  $p 是否显示子问题 1不显示,0显示所有问题
    * @param  $allQuestion
    * @return array
    */
    function GetAllQType($all = 1,$question_type='',$p=1,$allQuestion='')
    {           	
    	$wherep = ($p>0)?" and pid = 0 ":" ";
    	$is_visiable = ($all==1)?" and visiable = 1 ":"";
    	$sql = "SELECT * FROM ".DB_TABLEPRE."qtype WHERE 1 $is_visiable $wherep ORDER BY displayOrder";
    	$qtype = $this->db->fetch_all($sql,'id');
    	if($allQuestion == 1)
    	{
    		$qtype[0] = array('id' => 0,'name' => '全部类型', 'pid' => 0);
    		ksort($qtype);
    	 } 
    	return $qtype;
    }
    
    //获取所有用户自选类型
    function GetQType($id,$fields = "*")
    {           	
    	$table_name = $this->base->getDbTable($this->table_qtype);
    	$qtypeInfo = $this->pdo->selectRow($table_name, $fields, '`id` = ?', $id);
    	return $qtypeInfo;
    } 
    //获取订单数量
    function GetOrderCount($id,$fields = "*")
    {           	
    	$table_name = $this->base->getDbTable($this->table_order_count);
    	$qtypeInfo = $this->pdo->selectRow($table_name, $fields, '`qtype` = ?', $id);
    	return $qtypeInfo;
    } 
    //获取所有用户自选类型
    function GetQTypeByComplain($id,$fields = "*")
    {
    	$qtype_list = array();
    	$query = $this->db->query("SELECT ".$fields." FROM ".DB_TABLEPRE."qtype WHERE complain_type_id  = $id");   
    	$data = $this->db->fetch_array($query);
		if(!isset($data['id']))
		{
			$data = $this->getOther("*");
		}
    	return $data;
    }
    //获取所有用户自选类型
    function GetOther($fields = "*")
    {
    	$query = $this->db->query("SELECT ".$fields." FROM ".DB_TABLEPRE."qtype WHERE name like '%其他%'");   	
    	$data = $this->db->fetch_array($query);
    	return $data;
    }
    //根据用户所选问题的大类和主分类，确定是否需要输出子级菜单
    function GetSubList($qtype)
    {
    	$sql = "SELECT * FROM ".DB_TABLEPRE."qtype WHERE pid = ".$qtype." and visiable = 1";
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
    /**
     * 首页8大类 问题数量显示
     * 只显示子问题，和没有子问题的分类
     * @param  $question_type 问题类型（ask,complain,suggest）
     * @param  $StartDate 起始时间 date 类型(2014-6-18)
     * @param  $EndDate 结束时间
     * @return  
     */
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
}
?>

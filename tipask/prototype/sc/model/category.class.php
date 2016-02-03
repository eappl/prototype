<?php

!defined('IN_TIPASK') && exit('Access Denied');

class categorymodel extends base{

    var $db;
    var $base;
	var $table_category = "ask_category";
    function categorymodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
		$this->pdo = $this->base->init_pdo($this->table_category);
    }

    function getTypeDB($type='')
    {
    	$table_name = $this->base->getDbTable($this->table_category);
    	if($type == 1)
    	{
    		$question_type = 'ask';
    	}
    	elseif($type == 2)
    	{
    		$question_type = 'suggest';
    	}
    	elseif($type==3)
    	{
    		$question_type = 'complain';
    	}
    	elseif($type==4)
    	{
    		$question_type = 'dustbin';
    	}
		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE question_type='".$question_type."'";
    	$cid = $this->pdo->getOne($sql);
    	return $cid;
    }
	/* 获取分类信息 */

    function get($id) 
	{
    	$table_name = $this->base->getDbTable($this->table_category);
    	$Cagegory= $this->pdo->selectRow($table_name, "*", '`id` = ?', $id);
    	return $Cagegory;
    }

    function getByQuestionType($question_type,$fields = '*') 
	{
    	$table_name = $this->base->getDbTable($this->table_category);
    	$Cagegory= $this->pdo->selectRow($table_name,$fields, '`question_type` = ?', $question_type);
    	return $Cagegory;
    }
    function getByQType($qtype,$pid,$fields = '*') 
	{
    	$table_name = $this->base->getDbTable($this->table_category);
    	$Cagegory= $this->pdo->selectRow($table_name,$fields, '`qtype` = ? and `pid` = ?', array($qtype,$pid));
    	return $Cagegory;
    }
    
    function get_list() 
	{
		$table_name = $this->base->getDbTable($this->table_category);
		$sql = "select * from $table_name";
		$returnArr = array();
		$categorylist = $this->pdo->getAll($sql);
		foreach($categorylist as $key => $value)
		{
			$returnArr[$value['id']] = $value;
		}
        return $returnArr;
    }

    /*
     * 根据ID取分类名称*/
    function getNameById() 
	{
        $categorylist = $this->get_list();
        foreach($categorylist as $key => &$val)
		{
        	$categorylist[$key]=$val['name'];
        }
        return $categorylist;
    }
}

?>

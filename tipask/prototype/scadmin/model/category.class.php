<?php

!defined('IN_TIPASK') && exit('Access Denied');

class categorymodel{

    var $db;
    var $base;

    function categorymodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

     /* 获取分类信息 */

    function get($id) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "category WHERE id='$id'");
    }

     function set($cid,$name,$qtype,$question_type){
   		$query = "UPDATE " . DB_TABLEPRE . "category SET name='$name',qtype='$qtype',question_type='$question_type' WHERE id='$cid'";
        $this->db->query($query);
    }
    
    function get_list() {
        $categorylist = $this->db->fetch_all("SELECT * FROM " . DB_TABLEPRE . "category","id");
        return $categorylist;
    }
    function get_by_question_type($question_type) 
    {
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "category where grade = 1 and question_type = '".$question_type."'");
        $categorylist = $this->db->fetch_array($query);
        return $categorylist;
    }
    function get_by_qtype($qtype,$pid) 
    {
        $categorylist = $this->db->fetch_all("SELECT * FROM " . DB_TABLEPRE . "category where grade = 2 and qtype = $qtype and pid = $pid","id");
        return $categorylist;
    }
    function get_by_pid($pid) 
    {
        $categorylist = $this->db->fetch_all("SELECT * FROM " . DB_TABLEPRE . "category where pid = $pid","id");
        return $categorylist;
    }
	function check_cid($cid,$cid1,$cid2,$cid3,$cid4)
	{
		if($cid>0)
		{
			if($cid1>0)
			{
				if($cid2>0)
				{
					if($cid3>0)
					{
						if($cid4>0)
						{
							return true;
						}
						else
						{
							$list = $this->get_by_pid($cid3);
							if(count($list)>0)
							{
								return false;
							}
							else
							{
								return true;
							}	
						}
					}
					else
					{
						$list = $this->get_by_pid($cid2);
						if(count($list)>0)
						{
							return false;
						}
						else
						{
							return true;
						}
					}
				}
				else
				{
					$list = $this->get_by_pid($cid1);
					if(count($list)>0)
					{
						return false;
					}
					else
					{
						return true;
					}
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return fasle;
		}		
	}

    /* 添加分类 */

    function add($name,$qtype,$question_type,$cid = 0) {
        if(0 == $cid){
            $grade = 1;
            $pid = 0;
        } 
        $cid && $c_list = $this->get($cid);
        if(isset($c_list)){
            $grade = $c_list['grade']+1;
            $pid = $cid;
        }  
        $sql = "INSERT INTO `" . DB_TABLEPRE . "category`(`name` ,`dir` , `pid` , `grade` , `qtype`,`question_type`) VALUES ";      
        $sql .= "('$name','',$pid,$grade,$qtype,'".$question_type."')";
        
        return $this->db->query($sql);
    }

    /**
	 * 获得指定分类同级的所有分类以及该分类下的子分类
	 *
	 * @access  public
	 * @param   integer     $cat_id     分类编号
	 * @return  array
	 */
	function get_categories_list($cat_id = 0)
	{
	    if ($cat_id > 0) {
	        $sql = "SELECT pid,name FROM " . DB_TABLEPRE ."category WHERE id = '$cat_id'";
	        $rt =  $this->db->fetch_first($sql);
	        $parent_id = empty($rt)?'0':$rt['pid'];
	        $where = empty($rt)?'1':"AND name='{$rt['name']}'";
	    } else {
	        $parent_id = 0;
	    }
	    /*
	     判断当前分类中全是是否是底级分类，
	     如果是取出底级分类上级分类，
	     如果不是取当前分类及其下的子分类
	    */
	    $count = $this->db->fetch_total("category","pid = $parent_id");
	    if ($count || $parent_id == 0) {
	        /* 获取当前分类及其子分类 */
	        $sql = "SELECT id,name,dir,pid,grade,displayorder,questions,questions_today,question_type FROM " . DB_TABLEPRE ."category WHERE pid = '$parent_id' $where ORDER BY id ASC,displayorder ASC";
	        $res = $this->db->fetch_all($sql,'id');
	        foreach ($res AS $row)
	        {   
	            $cat_arr[$row['id']]['id']   = $row['id'];
	            $cat_arr[$row['id']]['grade'] = $row['grade'];
	            $row['grade'] > 1 && $depthstr = str_repeat("--", $row['grade']-1);
	            $cat_arr[$row['id']]['name'] = isset($depthstr)? $depthstr.$row['name']:$row['name'];         
	            $cat_arr[$row['id']]['questions'] = $row['questions'];
	            $cat_arr[$row['id']]['questions_today'] = $row['questions_today'];
	            $cat_arr[$row['id']]['question_type'] = $row['question_type'];
	            $cat_arr[$row['id']]['child'] = $this->get_child_list($row['id']);          
	        }
	    } 
	    if(isset($cat_arr)) {
	        return $cat_arr;
	    }
	}
	
	function get_child_list($tree_id = 0)
	{
	    $three_arr = array();
	    $count = $this->db->fetch_total("category","pid = $tree_id");
	    if ( $count || $tree_id == 0)
	    {
	         $child_sql = "SELECT id,name,dir,pid,grade,displayorder,questions,questions_today,qtype FROM " . DB_TABLEPRE ."category WHERE pid = '$tree_id' ORDER BY id DESC,displayorder ASC";
	        $res = $this->db->fetch_all($child_sql,'id');      
	        foreach ($res AS $row)
	        {          

	            $three_arr[$row['id']]['id']   = $row['id'];
	            $three_arr[$row['id']]['grade'] = $row['grade'];
	            $row['grade'] > 1 && $depthstr = str_repeat("----", $row['grade']-1);
	            $three_arr[$row['id']]['name'] = isset($depthstr)? $depthstr.$row['name']:$row['name'];         
	            $three_arr[$row['id']]['questions'] = $row['questions'];
	            $three_arr[$row['id']]['questions_today'] = $row['questions_today'];
	            $three_arr[$row['id']]['qtype'] = $row['qtype'];
	            $three_arr[$row['id']]['child'] = $this->get_child_list($row['id']);  
	        }
	    }
	    return $three_arr;
	}
    
    function remove($cid){
    	$cid && $count = $this->db->fetch_total("category","pid = $cid");
    	isset($count) && $count > 0 && exit("1");    	
    	$this->db->query("DELETE FROM `" . DB_TABLEPRE . "category` WHERE `id` = $cid") && exit("2");
    }
    
    /**
     * 获得分类树
     *
     * @param array $allcategory
     * @return string
     */
    function get_categrory_tree($selected_id=0) {
        $allcategory = $this->get_list();
        $categrorytree = '';
        foreach ($allcategory as $key => $category) {
            if ($category['pid'] == 0) {
            	$selected = $selected_id == $category['id']?'selected':'';
                $categrorytree .= "<option value=\"{$category['id']}\" $selected>{$category['name']}</option>";
                $categrorytree .=$this->get_child_tree($allcategory, $category['id'], 1,$selected_id);
            }
        }
        return $categrorytree;
    }

    function get_child_tree($allcategory, $pid, $depth=1,$selected_id) {
        $childtree = '';
        foreach ($allcategory as $key=>$category) {
            if ($pid == $category['pid']) {
            	$selected = $selected_id == $category['id']?'selected':'';
                $childtree .= "<option value=\"{$category['id']}\" $selected>";
                $depthstr = str_repeat("--", $depth);
                $childtree .= $depth ? "&nbsp;&nbsp;|{$depthstr}&nbsp;{$category['name']}</option>" : "{$category['name']}</option>";
                $childtree .= $this->get_child_tree($allcategory, $category['id'], $depth + 1,$selected_id);
            }
        }
        return $childtree;
    }
    /**
     *  获取咨询分类的所有二级分类
     */    
    function input_category_list($hekp_config){
    	$hekp_config =  !empty($hekp_config) ? $hekp_config : '';
    	$two_category_list = '';
    	$arr =  $this->db->fetch_all("SELECT id,name FROM " . DB_TABLEPRE . "category WHERE pid=0 AND name='咨询'");
    	foreach($arr as $key=>$value){
    		$two_category_list .=  $this->get_two_list($value['id'],$hekp_config);
    	}
    	return $two_category_list;
    }
    /**
     * 根据id查询分类
     */
    function get_two_list($id,$hekp_config = ''){
    	$tmp ='';
    	$count = 1;
    	$arr = $this->db->fetch_all("SELECT id,name  FROM " . DB_TABLEPRE . "category WHERE pid=$id",'id');
    	if($hekp_config != ''){
    		foreach($arr as $key=>$value){
    			$v = isset($hekp_config[$key]['time']) ? $hekp_config[$key]['time'] : '';
    			$tmp .= ($count++) % 3 == 0 ? ' ' . $value['name'] . ' <input size="3" name="y' . $value['id'] . '" type="text" value="' . $v . '"></input><br/>　　　　　'
    						                    :  ' ' . $value['name'] . ' <input size="3" name="y' . $value['id'] . '" type="text" value="' . $v. '"></input>';
    		}
    	}else{
    		foreach($arr as $key=>$value){
    			if(($key+1) % 3 ==0){
    				$tmp .=  ' ' . $value['name'] . ' <input size="3" name="y' . $value['id'] . '" type="text"></input><br/>　　　　　';
    			}else{
    				$tmp .=  ' ' . $value['name'] . ' <input size="3" name="y' . $value['id'] . '" type="text"></input>';
    			}
    		}
    	}
    	
    	return $tmp;
    }
    
    /**
     *  获取顶层的所有二级分类
     */
    function check_category_list($detail_type){
		$whereCid = " And id in (".$this->getTypeDB(1).",".$this->getTypeDB(2).")";
		$two_category_list = '';
    	$sql = "SELECT id,name FROM " . DB_TABLEPRE . "category WHERE pid=0 ".$whereCid;
		$arr =  $this->db->fetch_all($sql);
		$arr['-1'] = array('id'=>-1,'name'=>'其他');
		foreach($arr as $key=>$value)
		{
			if($value['id']>0)
			{
				$cid_info = $this->get($value['id']);
			}
			else
			{
				$cid_info = array('name'=>'其他');
			}
			$two_category_list .=  "<fieldset><legend>".$cid_info['name']."</legend>".$this->get_check_list($value['id'],$detail_type)."</fieldset>";
    	}
    	return $two_category_list;
    }
    /**
     * 根据id查询分类
     */
    function get_check_list($id,$detail_type){
    	$tmp ='';
    	if($id>0)
		{
			$arr = $this->db->fetch_all("SELECT id,name  FROM " . DB_TABLEPRE . "category WHERE pid=$id");
		}
		else
		{
			$arr = array('-1'=>array('id'=>-1,'name'=>'其他'));
		}
    	foreach($arr as $key=>$value){
    		$checked = in_array($value[id],$detail_type) ? 'checked' : '';
    		if(($key+1) % 5 ==0){
    			$tmp .=  '<input style="border:0" ' . $checked . ' type="checkbox" name="checks[]" value="' . $value[id]  . '" />' . $value['name'] . '<br/>';
    		}else{
    			$tmp .=  '<input style="border:0" ' . $checked . ' type="checkbox" name="checks[]" value="' . $value[id]  . '"/>' . $value['name'];
    		}
    	}
    	return $tmp;
    }
    /*
     * 根据ID取分类名称*/
    function getNameById() {
        $categorylist = $this->db->fetch_all("SELECT id,name FROM " . DB_TABLEPRE . "category","id");
        foreach($categorylist as $key => &$val){
        	$categorylist[$key]=$val['name'];
        }
        return $categorylist;
    }
    function  modifyCategoryNum($category,$count)
    {    	
        if($category>0)
		{
			$sql = "update  ask_category set questions = questions+($count) where id = ".$category;        
			return $this->db->query($sql);
		}
		else
		{
			return 1;
		}
    }
    function getTypeDB($type='')
    {
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
		$cid = $this->db->result_first($sql);
    	return $cid;
    }

}
?>

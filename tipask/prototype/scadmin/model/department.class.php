<?php

!defined('IN_TIPASK') && exit('Access Denied');

class departmentmodel {

    var $db;
    var $base;

    function departmentmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    // 获取在线服务部id
    function getOnlineWebId(){
    	$web_did = '';
    	$rs = $this->db->fetch_first("SELECT id,pid FROM " . DB_TABLEPRE . "department WHERE name='在线服务部'");
    	if(!empty($rs)){
    		$web_did = $_ENV['help']->get_web_id($rs['id']);
    	}
    	return rtrim($web_did,',');
    }
    // 获取在线服务部id
    function getOnlineWebOperator($operator)
	{
    	$Onlinedid = $this->getOnlineWebId();
		$OnlineOperator = $_ENV['operator']->getByColumn('did',$Onlinedid,1);
		$operatorList = explode("|",$operator);
		if($operatorList['0']!='' && count($operatorList)>=1)
		{
			foreach($OnlineOperator as $key => $value)
			{
				if(in_array($value['login_name'],$operatorList))
				{
					$o[$key] = "'".$value['login_name']."'";
				}				
			}
		}
		else
		{
			foreach($OnlineOperator as $key => $value)
			{
				$o[$key] = "'".$value['login_name']."'";				
			}
		}
		if(count($o)>=1)
		{
			$return = "(".implode(',',$o).")";
		}
		else
		{
			$return = "(0)";
		}
		return $return;
    }
    /* 获取所有部门及其子部门  */
    function get_department_list($id=0) {
    	$sql = "SELECT id,name,pid,displayorder,num,grade FROM " . DB_TABLEPRE ."department WHERE pid = $id";
    	$department_list =  $this->db->fetch_all($sql);
    	foreach ($department_list as $key=>$arr){
    		$child_list  = $this->get_child_list($arr['id']);
    		if($child_list != false){
    			$department_list[$key]['child'] = $child_list;
    		}
    	}
    	return $department_list;
    }
    function get_child_list($id){
    	$sql = "SELECT id,name,pid,displayorder,num,grade FROM " . DB_TABLEPRE ."department WHERE pid = '$id'";
    	$child = $this->db->fetch_all($sql);
    	if($child != false){
    		foreach ($child as $key=>$row){
    			$arr[$key]['id'] = $row['id'];
    			$arr[$key]['pid'] = $row['pid'];
    			$arr[$key]['grade'] = $row['grade'];
    			$row['grade'] > 1 && $depthstr = str_repeat("--", $row['grade']-1);
    			$arr[$key]['name'] = isset($depthstr)? $depthstr.$row['name']:$row['name'];
    			$arr[$key]['num'] = $row['num'];
    			$arr[$key]['child'] = $this->get_child_list($row['id']);
    		}
    	}
    	return $arr;
    }
    
    /**
     * 获得部门树
     *
     * @param array $allcategory
     * @return string
     */
    function get_categrory_tree($selected_id=0,$where='') {
        $allcategory = $this->get_list($where);
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
    
    function get_list($where='') {
        $departmentlist = $this->db->fetch_all("SELECT * FROM " . DB_TABLEPRE . "department $where","id");
        return $departmentlist;
    }
    
/* 添加部门 */
    function add($name,$pid = 0,$grade=1) {
    	
    	$sql = 'INSERT INTO ' . DB_TABLEPRE . 'department(name,pid,grade) VALUES ("' .$name . '",' . $pid . ',' . $grade . ')';
    	return $this->db->query($sql);
    	
    }
    
/* 更该部门  */
    function set($id,$name)
	{
    	$this->db->update_field("department","name",$name,"id = ".$id);
    }
    
/* 查找父部门 */
    function get_did($id) 
	{
       $count =  $this->db->result_first('select count(id) from ' . DB_TABLEPRE . 'department WHERE pid=' . $id);
       if($count)
	   {
       	  echo '1';
       } 
	   else 
	   {
       	  echo '2';
       }
    }

/*删除部门*/
    function remove($id) 
	{
    	$this->db->query("DELETE FROM " . DB_TABLEPRE . "department WHERE id=$id");
    }
    /**
     * 获取父级部门
     */
    function getParentDepartment()
    {
    	$sql = "select id,name from `" . DB_TABLEPRE ."department` where pid=0";
		$result = $this->db->fetch_all($sql);
		if (!empty($result)) 
		{
			return $result;
		}
		else 
		{
			return false;
		}
    }
	// 根据id获取部门信息
	function getDeparmenInfo($departmentId)
	{
		$sql = "select id,name,pid,num,grade from  `" . DB_TABLEPRE ."department` where id=".intval($departmentId);
		return $this->db->fetch_first($sql);
		
	}
}

?>

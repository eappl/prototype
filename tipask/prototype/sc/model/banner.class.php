<?php 
!defined('IN_TIPASK') && exit('Access Denied');
class bannermodel {
	var $db;
	var $base;
	function bannermodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}
	function get($id) {
		return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "banner WHERE id='$id'");
	}
	/**
	 * 获取所有banner及其二级子banner
	 * @param bool $tag 判断是否更新到首页 默认不更新
	 */
	function get_banner_list($tag = false) {
		$where = $tag ? ' AND display=1' : '';
		$sql = 'SELECT id,number,url,title,pid FROM ' . DB_TABLEPRE .'banner WHERE pid = 0' . $where . ' ORDER BY number ASC,id DESC';
		$banner_list =  $this->db->fetch_all($sql);
		foreach ($banner_list as $key=>$arr){
			$child_list  = $this->get_child_bannerlist($arr['id'],$tag);
			if($child_list != false){
				$banner_list[$key]['child'] = $child_list;
			}
		}
		return $banner_list;
	}
	/**
	 * @param int $id 根据id查找下级节点
	 * @param bool $tag 判断是否更新到首页 默认不更新
	 */
	function get_child_bannerlist($id,$tag = false){
		$arr = array();
		$where = $tag ? ' AND display=1' : '';
		$sql = "SELECT id,number,url,title,pid FROM " . DB_TABLEPRE ."banner WHERE pid = '$id' $where ORDER BY number ASC,id DESC";
		$child = $this->db->fetch_all($sql);
		foreach ($child as $row){
			$arr[$row['id']]['id'] = $row['id'];
			$arr[$row['id']]['title'] = '&nbsp;&nbsp;--' . $row['title'];
			$arr[$row['id']]['title_name'] = $row['title'];
			$arr[$row['id']]['pid'] = $row['pid'];
			$arr[$row['id']]['url'] = $row['url'];
			$arr[$row['id']]['number'] = $row['number'];
		}
		return $arr;
	}
	/* 添加banner */
	function add($title,$url,$pid = 0,$number=0) {
		$sql = 'INSERT INTO ' . DB_TABLEPRE . 'banner(title,url,pid,number) VALUES ("' . $title . '","' . $url . '",' . $pid . ',' . $number .')';
		return $this->db->query($sql);
	}
	/*查找banner的字问题数*/
	function get_banner_pid($bannerid){
		echo $this->db->fetch_total("banner","pid=$bannerid");
	}
	/*查找所有banner*/
	function get_all_banner(){
		echo $this->db->fetch_total("banner","pid=0");
	}
	/* 查找父问题 */
	function get_bannerid($bannerid) {
		if($this->db->fetch_total("banner","pid=$bannerid"))
			echo 1;
		else
			echo 2;
	}
	/* 删除问题*/
	function remove($bannerid) {
		$this->db->query("DELETE FROM `" . DB_TABLEPRE . "banner` WHERE `id` = '$bannerid'");
	}
	function set($id,$url='',$title='',$number=0){
		$this->db->query("UPDATE " . DB_TABLEPRE . "banner SET number='$number',url='$url',title='$title',display=0 WHERE id=$id");
	}
	
	function save_banner($number=0,$url='',$title=''){
		$this->db->query('INSERT INTO `' . DB_TABLEPRE . "banner`(`number`,`url`,`title`) values (" . $number . ",'$url','$title')");
	}
	
	function updateToHome(){
		$this->db->query("UPDATE " . DB_TABLEPRE . "banner SET display = 1");
	}
}
?>

<?php
/**
 * 用户相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Lm_Hero extends Base_Widget
{
	//声明所用到的表
	protected $table = 'lm_hero_add_log';
	protected $table_change = 'lm_hero_change_log';

	public function createHeroChangeTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_change);
		$table_to_process = Base_Widget::getDbTable($this->table_change)."_".$Date;
		$exist = $this->db->checkTableExist($table_to_process);
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE " . $table_to_check;
			$row = $this->db->getRow($sql);
			$sql = $row['Create Table'];
			$sql = str_replace('`' . $this->table_change. '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
			$create = $this->db->query($sql);
			if($create)
			{
				return $table_to_process;
			}
			else
			{
			 	return false;	
			}		 	
		}
	}

	public function InsertHeroChangeLog($DataArr)
	{
		$Date = date("Ym",$DataArr['HeroChangeTime']);
		$table_date = $this->createHeroChangeTable($Date);		
		return $this->db->insert($table_date,$DataArr);
	}
	
	public function createHeroAddTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table);
		$table_to_process = Base_Widget::getDbTable($this->table)."_".$Date;
		$exist = $this->db->checkTableExist($table_to_process);
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE " . $table_to_check;
			$row = $this->db->getRow($sql);
			$sql = $row['Create Table'];
			$sql = str_replace('`' . $this->table. '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
			$create = $this->db->query($sql);
			if($create)
			{
				return $table_to_process;
			}
			else
			{
			 	return false;	
			}		 	
		}
	}

	public function InsertHeroAddLog($DataArr)
	{
		$Date = date("Ym",$DataArr['HeroAddTime']);
		$table_date = $this->createHeroAddTable($Date);		
		return $this->db->insert($table_date,$DataArr);
	}
  //获取英雄数据
  public function getHeroByDate($StartDate,$EndDate,$ServerId,$HeroId,$oWherePartnerPermission,$AddReason)
  {
      //查询列
	$select_fields = array(
	'AddHeroCount'=>'count(*)',
  'UserCount'=>'count(distinct(UserId))',
  'HeroAddDate'=>"from_unixtime(HeroAddTime,'%Y-%m-%d')",
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" HeroAddTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" HeroAddTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	$whereReason = $AddReason!=999?" AddReason = ".$AddReason." ":"";
	$whereHero = $HeroId!=-1?" HeroId = ".$HeroId." ":"";  $group_fields = array('HeroAddDate');
  	$groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereReason,$whereHero);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      
      //初始化结果数组
      $Date = $StartDate;
      $StatArr = array('AddHero'=>array());         
      do
	{
		$StatArr['AddHero'][$Date] = array('AddHeroCount' => 0,'UserCount'=> 0);
		$Date = date("Y-m-d",(strtotime($Date)+86400));
	}
	while(strtotime($Date) <= strtotime($EndDate));
      
  $DateStart = date("Ym",strtotime($StartDate));
  $DateEnd = date("Ym",strtotime($EndDate));
  $DateList = array();  
  $Date = $StartDate;      
  do
  {
    $D = date("Ym",strtotime($Date));
    $DateList[] = $D;
    $Date = date("Y-m-d",strtotime("$Date +1 month"));
  }
  while($D!=$DateEnd);
      
  foreach($DateList as $k=>$v)
  {
      $table_name = Base_Widget::getDbTable($this->table)."_".$v;
      
  		$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$CharacterLogoutArr = $this->db->getAll($sql,false);
      
      foreach($CharacterLogoutArr as $key=>$val)
      {
          $StatArr['AddHero'][$val['HeroAddDate']]['AddHeroCount'] += $val['AddHeroCount'];
          $StatArr['AddHero'][$val['HeroAddDate']]['UserCount'] += $val['UserCount'];                
      }
  }
	return $StatArr;
  }
  //获取英雄数据
  public function getHeroByReason($StartDate,$EndDate,$ServerId,$HeroId,$oWherePartnerPermission,$AddReason,$AddReasonList)
  {
      //查询列
	$select_fields = array(
	'AddHeroCount'=>'count(*)',
  'UserCount'=>'count(distinct(UserId))',
  'AddReason',
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" HeroAddTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" HeroAddTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	$whereReason = $AddReason!=999?" AddReason = ".$AddReason." ":"";
	$whereHero = $HeroId!=-1?" HeroId = ".$HeroId." ":"";      
  	$group_fields = array('AddReason');
  	$groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereReason,$whereHero);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      
      //初始化结果数组
	foreach($AddReasonList as $Reason => $ReasonInfo)
	{
		$StatArr['AddHero'][$Reason] = array('name'=>$ReasonInfo,'AddHeroCount' => 0,'UserCount'=> 0);
	}
      
  $DateStart = date("Ym",strtotime($StartDate));
  $DateEnd = date("Ym",strtotime($EndDate));
  $DateList = array();  
  $Date = $StartDate;      
  do
  {
    $D = date("Ym",strtotime($Date));
    $DateList[] = $D;
    $Date = date("Y-m-d",strtotime("$Date +1 month"));
  }
  while($D!=$DateEnd);
      
  foreach($DateList as $k=>$v)
  {
      $table_name = Base_Widget::getDbTable($this->table)."_".$v;
      
  		$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$CharacterLogoutArr = $this->db->getAll($sql,false);
      
      foreach($CharacterLogoutArr as $key=>$val)
      {
          $StatArr['AddHero'][$val['AddReason']]['AddHeroCount'] += $val['AddHeroCount'];
          $StatArr['AddHero'][$val['AddReason']]['UserCount'] += $val['UserCount'];                
      }
  }
	return $StatArr;
  }
  //获取英雄数据
  public function getHeroByHero($StartDate,$EndDate,$ServerId,$HeroId,$oWherePartnerPermission,$AddReason,$HeroArr)
  {
      //查询列
	$select_fields = array(
	'AddHeroCount'=>'count(*)',
  'UserCount'=>'count(distinct(UserId))',
  'HeroId',
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" HeroAddTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" HeroAddTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	$whereReason = $AddReason!=999?" AddReason = ".$AddReason." ":"";
	$whereHero = $HeroId!=-1?" HeroId = ".$HeroId." ":"";      
  	$group_fields = array('HeroId');
  	$groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereReason,$whereHero);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      
      //初始化结果数组
	foreach($HeroArr as $Hero => $HeroInfo)
	{
		$StatArr['AddHero'][$Hero] = array('name'=>$HeroInfo['name'],'AddHeroCount' => 0,'UserCount'=> 0);
	}
      
  $DateStart = date("Ym",strtotime($StartDate));
  $DateEnd = date("Ym",strtotime($EndDate));
  $DateList = array();  
  $Date = $StartDate;      
  do
  {
    $D = date("Ym",strtotime($Date));
    $DateList[] = $D;
    $Date = date("Y-m-d",strtotime("$Date +1 month"));
  }
  while($D!=$DateEnd);
      
  foreach($DateList as $k=>$v)
  {
      $table_name = Base_Widget::getDbTable($this->table)."_".$v;
      
  		$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$CharacterLogoutArr = $this->db->getAll($sql,false);
      
      foreach($CharacterLogoutArr as $key=>$val)
      {
          $StatArr['AddHero'][$val['HeroId']]['AddHeroCount'] += $val['AddHeroCount'];
          $StatArr['AddHero'][$val['HeroId']]['UserCount'] += $val['UserCount'];                
      }
  }
	return $StatArr;
  }
  
  //切换英雄数据(按时间)
  public function changeHeroByDate($StartDate,$EndDate,$ServerId,$HeroId,$oWherePartnerPermission,$CurHeroId,$NewHeroId)
  {
      //查询列
	$select_fields = array(
	'HeroChangeCount'=>'count(*)',
    'UserCount'=>'count(distinct(UserId))',
    'HeroChangeDate'=>"from_unixtime(HeroChangeTime,'%Y-%m-%d')",
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" HeroChangeTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" HeroChangeTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	$whereCurHero = $CurHeroId?" CurHeroId = ".$CurHeroId." ":""; 
    $whereNewHero = $NewHeroId?" NewHeroId = ".$NewHeroId." ":"";       
  $group_fields = array('HeroChangeDate');
  $groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereReason,$whereCurHero,$whereNewHero);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      
      //初始化结果数组
      $Date = $StartDate;
      $StatArr = array('ChangeHero'=>array());         
      do
	{
		$StatArr['ChangeHero'][$Date] = array('HeroChangeCount' => 0,'UserCount'=> 0);
		$Date = date("Y-m-d",(strtotime($Date)+86400));
	}
	while(strtotime($Date) <= strtotime($EndDate));
      
  $DateStart = date("Ym",strtotime($StartDate));
  $DateEnd = date("Ym",strtotime($EndDate));
  $DateList = array();  
  $Date = $StartDate;      
  do
  {
    $D = date("Ym",strtotime($Date));
    $DateList[] = $D;
    $Date = date("Y-m-d",strtotime("$Date +1 month"));
  }
  while($D!=$DateEnd);
      
  foreach($DateList as $k=>$v)
  {
      $table_name = Base_Widget::getDbTable($this->table_change)."_".$v;
      
  		$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$ChangeHeroLogoutArr = $this->db->getAll($sql,false);

      foreach($ChangeHeroLogoutArr as $key=>$val)
      {
          $StatArr['ChangeHero'][$val['HeroChangeDate']]['HeroChangeCount'] += $val['HeroChangeCount'];
          $StatArr['ChangeHero'][$val['HeroChangeDate']]['UserCount'] += $val['UserCount'];                
      }
  }
	return $StatArr;
  }
  
  //切换英雄数据(按英雄)
  public function changeHeroByHero($StartDate,$EndDate,$ServerId,$HeroId,$oWherePartnerPermission,$CurHeroId,$NewHeroId,$HeroArr,$SeachType)
  {
    $groupby = $SeachType == 1 ? "CurHeroId" : "NewHeroId";
    
      //查询列
	$select_fields = array(
	'HeroChangeCount'=>'count(*)',
    'UserCount'=>'count(distinct(UserId))',
    $groupby => $groupby,
    'HeroChangeDate'=>"from_unixtime(HeroChangeTime,'%Y-%m-%d')",
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" HeroChangeTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" HeroChangeTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	$whereCurHero = $CurHeroId?" CurHeroId = ".$CurHeroId." ":""; 
    $whereNewHero = $NewHeroId?" NewHeroId = ".$NewHeroId." ":"";   
        
  $group_fields = array($groupby);
  $groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereReason,$whereCurHero,$whereNewHero);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      //初始化结果数组
      $StatArr = array('ChangeHero'=>array());
      foreach($HeroArr as $HeroId=>$data){
        $StatArr['ChangeHero'][$HeroId] = array('HeroChangeCount' => 0,'UserCount'=> 0,'name'=>$data['name']);
      }      
      
  $DateStart = date("Ym",strtotime($StartDate));
  $DateEnd = date("Ym",strtotime($EndDate));
  $DateList = array();  
  $Date = $StartDate;      
  do
  {
    $D = date("Ym",strtotime($Date));
    $DateList[] = $D;
    $Date = date("Y-m-d",strtotime("$Date +1 month"));
  }
  while($D!=$DateEnd);
      
  foreach($DateList as $k=>$v)
  {
      $table_name = Base_Widget::getDbTable($this->table_change)."_".$v;
      
  		$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$ChangeHeroLogoutArr = $this->db->getAll($sql,false);

      foreach($ChangeHeroLogoutArr as $key=>$val)
      {
          $StatArr['ChangeHero'][$val[$groupby]]['HeroChangeCount'] += $val['HeroChangeCount'];
          $StatArr['ChangeHero'][$val[$groupby]]['UserCount'] += $val['UserCount'];                
      }
  }
	return  $StatArr;
  }
  
  //切换英雄数据(按等级)
  public function changeHeroByLevel($StartDate,$EndDate,$ServerId,$HeroId,$oWherePartnerPermission,$CurHeroId,$NewHeroId)
  {    
      //查询列
	$select_fields = array(
	'HeroChangeCount'=>'count(*)',
    'UserCount'=>'count(distinct(UserId))',
    'CharacterLevel' => 'CharacterLevel',
    'HeroChangeDate'=>"from_unixtime(HeroChangeTime,'%Y-%m-%d')",
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" HeroChangeTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" HeroChangeTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	$whereCurHero = $CurHeroId?" CurHeroId = ".$CurHeroId." ":""; 
    $whereNewHero = $NewHeroId?" NewHeroId = ".$NewHeroId." ":"";   
        
  $group_fields = array('CharacterLevel');
  $groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereReason,$whereCurHero,$whereNewHero);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      //初始化结果数组
      $StatArr = array('ChangeHero'=>array());
      for($i=0;$i<=50;$i++){
        $StatArr['ChangeHero'][$i] = array('HeroChangeCount' => 0,'UserCount'=> 0);
      }      
      
  $DateStart = date("Ym",strtotime($StartDate));
  $DateEnd = date("Ym",strtotime($EndDate));
  $DateList = array();  
  $Date = $StartDate;      
  do
  {
    $D = date("Ym",strtotime($Date));
    $DateList[] = $D;
    $Date = date("Y-m-d",strtotime("$Date +1 month"));
  }
  while($D!=$DateEnd);
      
  foreach($DateList as $k=>$v)
  {
      $table_name = Base_Widget::getDbTable($this->table_change)."_".$v;
      
  		$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$ChangeHeroLogoutArr = $this->db->getAll($sql,false);

      foreach($ChangeHeroLogoutArr as $key=>$val)
      {
          $StatArr['ChangeHero'][$val['CharacterLevel']]['HeroChangeCount'] += $val['HeroChangeCount'];
          $StatArr['ChangeHero'][$val['CharacterLevel']]['UserCount'] += $val['UserCount'];                
      }
  }
	return  $StatArr;
  }
}

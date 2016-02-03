<?php
/**
 * 用户相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Lm_Character extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_character';
	protected $table_character = 'character_user';
	protected $table_create_log = 'user_character_create_log';
	protected $table_dead_log = 'user_character_dead_log';
  	protected $table_levelup_log = 'user_character_levelup_log';

  	protected $table_character_logout_log = 'lm_character_logout_log';
	protected $character_rank = 'user_character_rank';

    protected $table_send_log_user = 'product_send_log_user';


    
  	public function CreateCharacterLogoutLog($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_character_logout_log);
		$table_to_process = Base_Widget::getDbTable($this->table_character_logout_log)."_".$Date;
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
			$sql = str_replace('`' . $this->table_character_logout_log . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
	

	

	
	public function createCharacterCreateLogTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_create_log);
		$table_to_process = Base_Widget::getDbTable($this->table_create_log)."_".$Date;
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
			$sql = str_replace('`' . $this->table_create_log . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
	public function createCharacterDeadLogTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_dead_log);
		$table_to_process = Base_Widget::getDbTable($this->table_dead_log)."_".$Date;
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
			$sql = str_replace('`' . $this->table_dead_log . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
    
 	public function createCharacterLevelUpLogTable($Date)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_levelup_log);
		$table_to_process = Base_Widget::getDbTable($this->table_levelup_log)."_".$Date;
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
			$sql = str_replace('`' . $this->table_levelup_log . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
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
    
	public function CreateCharacter($DataArr)
	{
		//$this->db->begin();

		$positionId = Base_Common::getUserDataPositionById($DataArr['UserId']);
		$table_user = Base_Common::getUserTable($this->table,$positionId);
		
		$positionName = Base_Common::getUserDataPositionByName($DataArr['CharacterName']);
		$table_character = Base_Common::getUserTable($this->table_character,$positionName);
		
		$user = $this->db->insert($table_user,$DataArr);
		$log = $this->InsertCharacterCreateLog($DataArr);
		$character = $this->db->insert($table_character,$DataArr);
			
		$LevelUpArr = array('AppId'=>$DataArr['AppId'],'PartnerId'=>$DataArr['PartnerId'],'ServerId'=>$DataArr['ServerId'],
							'UserId'=>$DataArr['UserId'],'CharacterLevel'=>1,'CharacterLevelUpTime'=>$DataArr['CharacterCreateTime']);		
		$levelup = $this->InsertcharacterLevelUpLog($LevelUpArr,$DataArr['UserId']);
        
        return true;
		/*if($user&&$log&&$levelup&&$character)
		{
			$this->db->commit();
			return true;
		}
		else
		{
			$this->db->rollBack();
			return false;
		}*/		
	}
	public function InsertCharacterCreateLog($DataArr)
	{
		$Date = date("Ym",$DataArr['CharacterCreateTime']);
		$table_date = $this->createCharacterCreateLogTable($Date);		
		return $this->db->insert($table_date,$DataArr);
	}
	public function getCharacterInfoByUser($UserId,$ServerId,$fields = '*')
	{
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_to_process = Base_Common::getUserTable($this->table,$position);
		if($ServerId)
		{
			$sql = "select $fields from $table_to_process where `UserId` = ? and `ServerId` = ?";
			return $this->db->getAll($sql,array($UserId,$ServerId));				
		}
		else
		{
			$sql = "select $fields from $table_to_process where `UserId` = ?";
			return $this->db->getAll($sql,$UserId);			 	
		}	
	}
	public function getCharacterInfoByCharacter($CharacterName,$ServerId,$fields = '*')
	{
		$position = Base_Common::getUserDataPositionByName($CharacterName);
		$table_to_process = Base_Common::getUserTable($this->table_character,$position);
		if($ServerId)
		{
			$sql = "select $fields from $table_to_process where `CharacterName` = ? and `ServerId` = ?";
			return $this->db->getAll($sql,array($CharacterName,$ServerId));				
		}
		else
		{
			$sql = "select $fields from $table_to_process where `CharacterName` = ?";
			return $this->db->getAll($sql,$CharacterName);			 	
		}	
	}
	public function updateCharacterInfo($UserId,$ServerId,$bind)
	{	    
        return true;
        
//        $CharacterInfo = $this->getCharacterInfoByUser($UserId,$ServerId,"CharacterName");
//        
//        if($CharacterInfo){
//            $CharacterName = $CharacterInfo[0]['CharacterName'];
//        }else{
//            return false;
//        }        
//        
//        $this->db->begin();
//		$position = Base_Common::getUserDataPositionById($UserId);
//		$table_to_process = Base_Common::getUserTable($this->table,$position);
//        
//        $positionName = Base_Common::getUserDataPositionByName($CharacterName);
//		$table_character = Base_Common::getUserTable($this->table_character,$positionName);
//        
//        $update_user = $this->db->update($table_to_process, $bind , '`UserId` = ? and `ServerId` = ?',array($UserId,$ServerId));
//        $update_name = $this->db->update($table_character, $bind , '`UserId` = ? and `ServerId` = ?',array($UserId,$ServerId));        
//        
//        if($update_user && $update_name){
//            $this->db->commit();
//            return true;
//        }else{
//            $this->db->rollback();
//            return false;
//        }	
	}
	public function getUserCharacterList($UserId,$AppId,$PartnerId,$ServerId)
	{
		$whereApp = $AppId?" AppId = ".$AppId." ":"";
		$wherePartner = $PartnerId?" PartnerId = ".$PartnerId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
		$whereUser = " UserId = ".$UserId." ";
		$whereCondition = array($whereApp,$wherePartner,$whereServer,$whereUser);
		$position = Base_Common::getUserDataPositionById($UserId);
		$table_to_process = Base_Common::getUserTable($this->table,$position);
		$where = Base_common::getSqlWhere($whereCondition);
    	$sql = "SELECT * FROM $table_to_process as log where 1 ".$where;
    	$return = $this->db->getAll($sql);	
    	$CharacterList = array();
    	if(is_array($return))
    	{
    		foreach($return as $key => $value)
    		{
    			$CharacterList[$value['ServerId']] = $value;	
    		}	
    	}
    	return $CharacterList;
	}
    
	public function InsertCharacterDeadLog($DataArr)
	{
		$Date = date("Ym",$DataArr['CharacterDeadTime']);
		$table_date = $this->createCharacterDeadLogTable($Date);		
		return $this->db->insert($table_date,$DataArr);
	}
    
    public function createCharacterLevelUpLogTablebyuserid($tablesuffix)
	{
		$table_to_check = Base_Widget::getDbTable($this->table_levelup_log);
		$table_to_process = Base_Widget::getDbTable($this->table_levelup_log)."_user_".$tablesuffix;
		$exist = $this->db->checkTableExist($table_to_process);
        
		if($exist>0)
		{
			return $table_to_process;	
		}
		else
		{
			$sql = "SHOW CREATE TABLE ".$table_to_check;
			$row = $this->db->getRow($sql);
			$sql = $row['Create Table'];
			$sql = str_replace('`' . $this->table_levelup_log . '`', 'IF NOT EXISTS ' . $table_to_process, $sql);
            
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
    public function InsertcharacterLevelUpLog($DataArr)
    {
        $this->db->begin();
        $position = Base_Common::getUserDataPositionById($DataArr['UserId']);
        
        $Date = date("Ym",$DataArr['CharacterLevelUpTime']);
		$table_date = $this->createCharacterLevelUpLogTable($Date);	
        $bydate = $this->db->replace($table_date,$DataArr);
        
        $table_userid = $this->createCharacterLevelUpLogTablebyuserid($position['db_fix']);
        $byuserid = $this->db->replace($table_userid,$DataArr);        
        
        if($bydate && $byuserid){
            $this->db->commit();
            return true;
        }else{
            $this->db->rollBack();
            return false;
        }
    }
    
  //获取角色死亡数据
  public function getCharacterDeadByDate($StartDate,$EndDate,$ServerId,$oWherePartnerPermission)
  {
      //查询列
	$select_fields = array(
	'DeadCount'=>'count(*)',
      'UserCount'=>'count(distinct(UserId))',
      'CharacterDeadDate'=>"from_unixtime(CharacterDeadTime,'%Y-%m-%d')",
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" CharacterDeadTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" CharacterDeadTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
      
      $group_fields = array('CharacterDeadDate');
      $groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      
	  //初始化结果数组
	  $Date = $StartDate;
	  $StatArr = array('Dead'=>array());         
      do
	{
		$StatArr['Dead'][$Date] = array('DeadCount' => 0,'UserCount'=> 0);
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
      
      foreach($DateList as $k=>$v){
          $table_name = Base_Widget::getDbTable($this->table_dead_log)."_".$v;
          $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
  		$CharacterDeadArr = $this->db->getAll($sql,false);
          
          foreach($CharacterDeadArr as $key=>$val){
              $StatArr['Dead'][$val['CharacterDeadDate']]['UserCount'] += $val['UserCount'];
              $StatArr['Dead'][$val['CharacterDeadDate']]['DeadCount'] += $val['DeadCount'];                
          }
      }
	return $StatArr;
  }
  //获取角色死亡数据
  public function getCharacterDeadBySlk($StartDate,$EndDate,$ServerId,$SlkId,$oWherePartnerPermission)
  {
      //查询列
	$select_fields = array(
	'DeadCount'=>'count(*)','SlkId',
      'UserCount'=>'count(distinct(UserId))',
      'CharacterDeadDate'=>"from_unixtime(CharacterDeadTime,'%Y-%m-%d')",
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" CharacterDeadTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" CharacterDeadTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereSlk = $SlkId?" SlkId = ".$SlkId." ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
      
      $group_fields = array('CharacterDeadDate','SlkId');
      $groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereSlk,$whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      
      
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
      
      foreach($DateList as $k=>$v){
          $table_name = Base_Widget::getDbTable($this->table_dead_log)."_".$v;
          $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
  		$CharacterDeadArr = $this->db->getAll($sql,false);
          
          foreach($CharacterDeadArr as $key=>$val)
          {
              $StatArr['Dead'][$val['CharacterDeadDate']][$val['SlkId']]['UserCount'] += $val['UserCount'];
              $StatArr['Dead'][$val['CharacterDeadDate']][$val['SlkId']]['DeadCount'] += $val['DeadCount'];                
          }
      }
	return $StatArr;
  }
    
    //游戏退出日志进库
  public function InsertCharacterLogoutLog($DataArr)
	{
		$Date = date("Ym",$DataArr['LogoutTime']);
		$table_date = $this->CreateCharacterLogoutLog($Date);		
		return $this->db->insert($table_date,$DataArr);
	}
  //获取角色登出数据
  public function getCharacterLogoutByDate($StartDate,$EndDate,$ServerId,$oWherePartnerPermission,$LogoutReason)
  {
      //查询列
	$select_fields = array(
	'LogoutCount'=>'count(*)',
  'UserCount'=>'count(distinct(UserId))',
  'CharacterLogoutDate'=>"from_unixtime(LogoutTime,'%Y-%m-%d')",
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" LogoutTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" LogoutTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	$whereReason = $LogoutReason!=999?" LogoutReason = ".$LogoutReason." ":"";
      
  $group_fields = array('CharacterLogoutDate');
  $groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereReason);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      
      //初始化结果数组
      $Date = $StartDate;
      $StatArr = array('Logout'=>array());         
      do
	{
		$StatArr['Logout'][$Date] = array('LogoutCount' => 0,'UserCount'=> 0);
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
      $table_name = Base_Widget::getDbTable($this->table_character_logout_log)."_".$v;
      
  		$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$CharacterLogoutArr = $this->db->getAll($sql,false);
      
      foreach($CharacterLogoutArr as $key=>$val)
      {
          $StatArr['Logout'][$val['CharacterLogoutDate']]['UserCount'] += $val['UserCount'];
          $StatArr['Logout'][$val['CharacterLogoutDate']]['LogoutCount'] += $val['LogoutCount'];                
      }
  }
	return $StatArr;
  }
   //获取角色登出数据
  public function getCharacterLogoutByReason($StartDate,$EndDate,$ServerId,$oWherePartnerPermission,$LogoutReason,$LogoutReasonList)
  {
      //查询列
	$select_fields = array(
	'LogoutCount'=>'count(*)',
  'UserCount'=>'count(distinct(UserId))',
  'LogoutReason',
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" LogoutTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" LogoutTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	$whereReason = $LogoutReason!=999?" LogoutReason = ".$LogoutReason." ":"";
      
  $group_fields = array('LogoutReason');
  $groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereReason);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      
  //初始化结果数组
 	foreach($LogoutReasonList as $Reason => $ReasonInfo)
 	{
 		$StatArr['Logout'][$Reason] = array('name'=>$ReasonInfo,'LogoutCount' => 0,'UserCount'=> 0);
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
      $table_name = Base_Widget::getDbTable($this->table_character_logout_log)."_".$v;
      
  		$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$CharacterLogoutArr = $this->db->getAll($sql,false);
      
      foreach($CharacterLogoutArr as $key=>$val)
      {
          $StatArr['Logout'][$val['LogoutReason']]['UserCount'] += $val['UserCount'];
          $StatArr['Logout'][$val['LogoutReason']]['LogoutCount'] += $val['LogoutCount'];                
      }
  }
	return $StatArr;
  }
   //获取角色登出数据
  public function getCharacterLogoutByLevel($StartDate,$EndDate,$ServerId,$oWherePartnerPermission,$LogoutReason)
  {
      //查询列
	$select_fields = array(
	'LogoutCount'=>'count(*)',
  'UserCount'=>'count(distinct(UserId))',
  'LogoutLevel',
	);
      
	//初始化查询条件
	$whereStartDate = $StartDate?" LogoutTime >= '".strtotime($StartDate)."' ":"";
	$whereEndDate = $EndDate?" LogoutTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
	$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	$whereReason = $LogoutReason!=999?" LogoutReason = ".$LogoutReason." ":"";
      
  $group_fields = array('LogoutLevel');
  $groups = Base_common::getGroupBy($group_fields);

	$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission,$whereReason);

	//生成查询列
	$fields = Base_common::getSqlFields($select_fields);
	//生成条件列
	$where = Base_common::getSqlWhere($whereCondition);
      
  //初始化结果数组
 	for($i = 0;$i<=50;$i++)
 	{
 		$StatArr['Logout'][$i] = array('LogoutCount' => 0,'UserCount'=> 0);
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
      $table_name = Base_Widget::getDbTable($this->table_character_logout_log)."_".$v;
      
  		$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$CharacterLogoutArr = $this->db->getAll($sql,false);
      
      foreach($CharacterLogoutArr as $key=>$val)
      {
          $StatArr['Logout'][$val['LogoutLevel']]['UserCount'] += $val['UserCount'];
          $StatArr['Logout'][$val['LogoutLevel']]['LogoutCount'] += $val['LogoutCount'];                
      }
  }
	return $StatArr;
  }
  //获取角色升级数据
  public function getCharacterLevelUpByDate($StartDate,$EndDate,$ServerId,$oWherePartnerPermission)
  {
	      //查询列
		$select_fields = array(
		'LevelUpCount'=>'count(*)',
	  'UserCount'=>'count(distinct(UserId))',
	  'CharacterLevelUpDate'=>"from_unixtime(CharacterLevelUpTime,'%Y-%m-%d')",
		);
	      
		//初始化查询条件
		$whereStartDate = $StartDate?" CharacterLevelUpTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" CharacterLevelUpTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	      
	  $group_fields = array('CharacterLevelUpDate');
	  $groups = Base_common::getGroupBy($group_fields);
	
		$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission);
	
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
	      
	  //初始化结果数组
	  $Date = $StartDate;
	  $StatArr = array('LevelUp'=>array());         
	  do
		{
			$StatArr['LevelUp'][$Date] = array('LevelUpCount' => 0,'UserCount'=> 0);
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
	    $table_name = Base_Widget::getDbTable($this->table_levelup_log)."_".$v;
	    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$CharacterLevelUpArr = $this->db->getAll($sql,false);
	        
	    foreach($CharacterLevelUpArr as $key=>$val)
	    {
	        $StatArr['LevelUp'][$val['CharacterLevelUpDate']]['UserCount'] += $val['UserCount'];
	        $StatArr['LevelUp'][$val['CharacterLevelUpDate']]['LevelUpCount'] += $val['LevelUpCount'];                
	    }
	  }
		return $StatArr;
  }

    public function getMaxCharacterLevel($UserId,$AppId,$PartnerId)
    {
        //查询列
        $select_fields = array(
        'NowCharacterLevel'=>'max(CharacterLevel)',
        'CharacterLevelUpTime',
        );
        
        //初始化查询条件
        $whereUserId = $UserId?" UserId = '$UserId' ":"";
        $whereAppId = $AppId?" $AppId = '$AppId' ":"";
        $wherePartnerId = $PartnerId?" PartnerId = '$PartnerId' ":"";
        
        $whereCondition = array($whereUserId,$whereAppId,$wherePartnerId);
        
        //生成查询列
        $fields = Base_common::getSqlFields($select_fields);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
    
        $position = Base_Common::getUserDataPositionById($UserId);		
		$table_to_process = Base_Widget::getDbTable($this->table_levelup_log)."_user_".$position['db_fix'];
        
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getRow($sql);
    }
    
    //获取用户从开始到结束等级的时间
    public function getUserFullLevelDate($StartDate,$EndDate,$beginlevel,$endlevel,$oWherePartnerPermission)
    {
         //查询列
		$select_fields = array(
        'UserId'=>'distinct(UserId)',
        'UserCount'=>'count(distinct(UserId))',
        'CharacterLevelUpTime',
        );
	
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);		
        
        //初始化结果数组
        $Date = $StartDate;
        $DateArr = array();         
        do
        {
            $DateArr[$Date] = array('UserCount'=>0,'UserFull'=>array($Date=>"0"));
            $Date = date("Y-m-d",(strtotime($Date)+86400));
        }
        while(strtotime($Date) <= strtotime($EndDate));
        $dateTitle = array();
        
        for($i=0;$i<16;$i++){
            foreach($DateArr as $date=>$arr){
                //初始化查询条件
                $whereStartDate = $StartDate?" CharacterLevelUpTime >= '".strtotime($date)."' ":"";
                $whereEndDate = $EndDate?" CharacterLevelUpTime <= '".(strtotime($date)+86400-1)."' ":"";
                
                $whereCondition = array($whereStartDate,$whereEndDate,$whereendlevel,$oWherePartnerPermission);
                //生成条件列
    		    $where = Base_common::getSqlWhere($whereCondition);                                                
                
                $table_name = $table_name = Base_Widget::getDbTable($this->table_levelup_log)."_user_".dechex($i);            
                
                if(isset($DateArr[$date]['UserCount'])){
                    $DateArr[$date]['UserCount'] += $this->getNewCreateCharacterCount($date,$oWherePartnerPermission,dechex($i));
                }else{
                    $DateArr[$date]['UserCount'] = $this->getNewCreateCharacterCount($date,$oWherePartnerPermission,dechex($i));
                }
                
                $sql = "SELECT $fields FROM $table_name as log where UserId in (select UserId from $table_name where CharacterLevel = $beginlevel ".$where.") and CharacterLevel = $endlevel ";
                $UserList = $this->db->getAll($sql,false);
                
                foreach($UserList as $key=>$val){
                    if(!empty($val['CharacterLevelUpTime'])){
                        $CharacterCreatTime = $this->geCharactertCreateTime($val['UserId'],$oWherePartnerPermission);
                        
                        if($CharacterCreatTime){
                            if(isset($DateArr[date('Y-m-d',$CharacterCreatTime)]['UserFull'][date("Y-m-d",$val['CharacterLevelUpTime'])])){ 
                                $DateArr[date('Y-m-d',$CharacterCreatTime)]['UserFull'][date("Y-m-d",$val['CharacterLevelUpTime'])] += 1;
                            }else{                            
                                $DateArr[date('Y-m-d',$CharacterCreatTime)]['UserFull'][date("Y-m-d",$val['CharacterLevelUpTime'])] = 1;
                            }
                            
                            $dateTitle[date("Y-m-d",$val['CharacterLevelUpTime'])] += 1;
                        }                            
                    }                      
                }
            }
        }
        
        $DateArr['dateTitle'] = $dateTitle;
        return $DateArr;
    }
    
    //获取新创建角色数量
    public function getNewCreateCharacterCount($date,$oWherePartnerPermission,$table_sfix)
    {
        $whereCondition = array($oWherePartnerPermission);
        //生成条件列
	    $where = Base_common::getSqlWhere($whereCondition); 
        
        $table_name = $table_name = Base_Widget::getDbTable($this->table_levelup_log)."_user_".$table_sfix; 
        
        $sql = "SELECT count(distinct(UserId)) from $table_name where CharacterLevelUpTime >= '".strtotime($date)."' and CharacterLevelUpTime <= '".(strtotime($date)+86400-1)."' and CharacterLevel = 1 ".$where;
        return $this->db->getOne($sql,false);
    }
    
    //获取角色创建时间
    public function geCharactertCreateTime($UserId,$oWherePartnerPermission)
    {
        $whereCondition = array($oWherePartnerPermission);
        //生成条件列
	    $where = Base_common::getSqlWhere($whereCondition); 
        
        $position = Base_Common::getUserDataPositionById($UserId);
        
        $table_name = Base_Widget::getDbTable($this->table_levelup_log)."_user_".$position['db_fix']; 
        
        $sql = "SELECT CharacterLevelUpTime from $table_name where CharacterLevel = 1 and UserId = $UserId ".$where;
        return $this->db->getOne($sql,false);
    }
  
  //获取角色升级数据
  public function getCharacterLevelUpByLevel($StartDate,$EndDate,$ServerId,$oWherePartnerPermission)
  {
	      //查询列
		$select_fields = array(
		'LevelUpCount'=>'count(*)',
	  'UserCount'=>'count(distinct(UserId))',
	  'CharacterLevel',
		);
	      
		//初始化查询条件
		$whereStartDate = $StartDate?" CharacterLevelUpTime >= '".strtotime($StartDate)."' ":"";
		$whereEndDate = $EndDate?" CharacterLevelUpTime <= '".(strtotime($EndDate)+86400-1)."' ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	      
	  $group_fields = array('CharacterLevel');
	  $groups = Base_common::getGroupBy($group_fields);
	
		$whereCondition = array($whereStartDate,$whereEndDate,$whereServer,$oWherePartnerPermission);
	
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
	      
	  //初始化结果数组
	  $Date = $StartDate;
	  $StatArr = array('LevelUp'=>array());         
	  for($i=1;$i<=50;$i++)
		{
			$StatArr['LevelUp'][$i] = array('LevelUpCount' => 0,'UserCount'=> 0);
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
	    $table_name = Base_Widget::getDbTable($this->table_levelup_log)."_".$v;
	    $sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$CharacterLevelUpArr = $this->db->getAll($sql,false);
	        
	    foreach($CharacterLevelUpArr as $key=>$val)
	    {
	        $StatArr['LevelUp'][$val['CharacterLevel']]['UserCount'] += $val['UserCount'];
	        $StatArr['LevelUp'][$val['CharacterLevel']]['LevelUpCount'] += $val['LevelUpCount'];                
	    }
	  }
		return $StatArr;
  }
   //获取角色 等级
	public function getCharacterLevel($ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array(
		'UserId',
		'MaxLevel'=>'max(CharacterLevel)','ServerId',
		);
		
		//初始化查询条件
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	      
		$group_fields = array('UserId','ServerId');
		$groups = Base_common::getGroupBy($group_fields);
	
		$whereCondition = array($whereServer,$oWherePartnerPermission);
	
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
	      
		//初始化结果数组
		$StatArr = array('Level'=>array());         
		for($i=1;$i<=50;$i++)
		{
			$StatArr['Level'][$i] = array('UserCount'=> 0);
		}	      		
		for($i=0;$i<=15;$i++)
		{
			$table_name = Base_Widget::getDbTable($this->table_levelup_log)."_user_".dechex($i);
			$sql = "SELECT $fields FROM $table_name as log where 1 ".$where.$groups;
			$CharacterLevelArr = $this->db->getAll($sql,false);
		      
			foreach($CharacterLevelArr as $key=>$val)
			{
			    $StatArr['Level'][$val['MaxLevel']]['UserCount'] ++;
			}
		}
		return $StatArr;
	}
	/*
	*用户角色等级实力值 获取从socket的文件，插入数据库 
	* @author selena   2013/3/12
	*/
	public function truncateUserCharacterRankList()
	{
		 $table_name = Base_Widget::getDbTable($this->character_rank);
		 $sql = "truncate ".$table_name;
		 return $this->db->query($sql);
	}
	/*
	/*
	*用户角色等级实力值 获取从socket的文件，插入数据库 
	* @author selena   2013/3/12
	*/
	public function insertUserCharacterRankList($arr)
	{
		 $table_name = Base_Widget::getDbTable($this->character_rank);
         return $this->db->replace($table_name,$arr);
	}
	/*
	*按照用户的战斗力排名 输出到配置文件中
	* @author selena 2013/3/8
	*/
	public function getUserByFightRank()
	{
		$table_name = Base_Widget::getDbTable($this->character_rank);
		$sql = "select distinct(ServerId) as ServerId from $table_name";
		$ServerList = $this->db->getAll($sql);
		foreach($ServerList as $key => $value)
		{
			$sql = "select * from $table_name where ServerId = ".$value['ServerId']." order by FightingCapacity desc limit 1000";
			$ResultAll = $this->db->getAll($sql);
			$FightRankAll[$value['ServerId']] = array();
			foreach ($ResultAll as $k=>$v) {
			 	 $FightRankAll[$value['ServerId']][$k+1]=$v;
			} 				
		}
		$file_path = "/www/web_usercenter/app/etc/";
		$file_name = "FightRank.php";
		$var = var_export($FightRankAll,true);
		$text ='<?php $FightRankAll='.$var.'; return $FightRankAll;?>';		
		file_put_contents($file_path.$file_name,$text);		
	}
	/*
	*按照用户的战斗力排名 输出到配置文件中
	* @author selena 2013/3/8
	*/
	public function getUserByLiveRank()
	{
		$table_name = Base_Widget::getDbTable($this->character_rank);
		$sql = "select distinct(ServerId) as ServerId from $table_name";
		$ServerList = $this->db->getAll($sql);
        foreach($ServerList as $key => $value)
		{
			$sql = "select * from $table_name where ServerId = ".$value['ServerId']." order by Capacity desc limit 1000";
			$ResultAll = $this->db->getAll($sql);
            $CapacityRankAll[$value['ServerId']] = array();
			foreach ($ResultAll as $k=>$v) 
            {
			 	 $CapacityRankAll[$value['ServerId']][$k+1]=$v;
			} 				
		}

		$file_path = "/www/web_usercenter/app/etc/";
		$file_name = "CapacityRank.php";
		$var = var_export($CapacityRankAll,true);
		$text ='<?php $CapacityRankAll='.$var.'; return $CapacityRankAll;?>';		
		file_put_contents($file_path.$file_name,$text);	
	}
	public function getUserByPKPoint()
	{
		$oUser = new Lm_User();
		$table_name = Base_Widget::getDbTable($this->character_rank);
		$sql = "select distinct(ServerId) as ServerId from $table_name";
		$ServerList = $this->db->getAll($sql);
        foreach($ServerList as $key => $value)
		{
			$sql = "select * from $table_name where ServerId = ".$value['ServerId']." order by PKPoint desc limit 1000";
			$ResultAll = $this->db->getAll($sql);
            $PKPointAll[$value['ServerId']] = array();
			foreach ($ResultAll as $k=>$v) 
            {
			 	 $PKPointAll[$value['ServerId']][$k+1]=$v;
			 	 $UserInfo = $oUser->getCharacterInfoByUser($v['UserId'],'UserName');
				if(isset($UserInfo['UserName']))
				{
					$PKPointAll[$value['ServerId']][$k+1]['UserName'] = $UserInfo['UserName'];	
				}
				else
				{
				 	$PKPointAll[$value['ServerId']][$k+1]['UserName'] = 0;	
				}			 	 
			} 				
		}
		$file_path = "/www/web_usercenter/app/etc/";
		$file_name = "PKPoint.php";
		$var = var_export($PKPointAll,true);
		$text ='<?php $PKPointAll='.$var.'; return $PKPointAll;?>';		
		file_put_contents($file_path.$file_name,$text);
		$file_name = "PKPoint-".date("YmdHis",time()).".php";		
		file_put_contents($file_path.$file_name,$text);			
	}
 	public function getLevelUpDetail($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission,$start,$pagesize)
	{
		$LevelUpCount = $this->getLevelUpDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission);
		if($LevelUpCount)
		{
				//查询列
			$select_fields = array('*');
			//分类统计列
	
			//初始化查询条件
			$whereStartTime = $StartTime?" CharacterLevelUpTime >= ".strtotime($StartTime)." ":"";
			$whereEndTime = $EndTime?" CharacterLevelUpTime <= ".strtotime($EndTime)." ":"";
			$whereUser = $UserId?" UserId = ".$UserId." ":"";
			$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";
	
			$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereServer,$oWherePartnerPermission);
			
			$order = " order by CharacterLevelUpTime desc";
			$limit = $pagesize?" limit $start,$pagesize":"";
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);

			$Date = date("Ym",strtotime($StartTime));			
			
			if(!$UserId)
			{
				$table_to_process = Base_Widget::getDbTable($this->table_levelup_log)."_".$Date; 
			}
			else
			{
				$position = Base_Common::getUserDataPositionById($UserId);			
				$table_to_process = Base_Widget::getDbTable($this->table_levelup_log)."_user_".$position['db_fix'];  			 	
			}  
		    $StatArr = array('LevelUpDetail'=>array());
		
		    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where.$order.$limit;
			$LevelUpDetailArr = $this->db->getAll($sql,false);
			if(isset($LevelUpDetailArr))
		    {
	      		$i = 1;
	      		foreach ($LevelUpDetailArr as $key => $value) 
				{
					$StatArr['LevelUpDetail'][$i] = $value;
					$i++;
				}
		    }
		    
  		}
	 	$StatArr['LevelUpCount'] = $LevelUpCount; 
		return $StatArr;
	}
 	public function getLevelUpDetailCount($StartTime,$EndTime,$UserId,$ServerId,$oWherePartnerPermission)
	{
		//查询列
		$select_fields = array('LevelUpCount'=>'count(*)');
		//分类统计列

		//初始化查询条件
		$whereStartTime = $StartTime?" CharacterLevelUpTime >= ".strtotime($StartTime)." ":"";
		$whereEndTime = $EndTime?" CharacterLevelUpTime <= ".strtotime($EndTime)." ":"";
		$whereUser = $UserId?" UserId = ".$UserId." ":"";
		$whereServer = $ServerId?" ServerId = ".$ServerId." ":"";

		$whereCondition = array($whereUser,$whereStartTime,$whereEndTime,$whereServer,$oWherePartnerPermission);
		
		
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
	    
		$Date = date("Ym",strtotime($StartTime));			
		
		if(!$UserId)
		{
			$table_to_process = Base_Widget::getDbTable($this->table_levelup_log)."_".$Date; 
		}
		else
		{
			$position = Base_Common::getUserDataPositionById($UserId);			
			$table_to_process = Base_Widget::getDbTable($this->table_levelup_log)."_user_".$position['db_fix'];  			 	
		}      	
	    
	    $sql = "SELECT $fields FROM $table_to_process as log where 1 ".$where;		
		$LevelUpCount = $this->db->getOne($sql,false);
		if($LevelUpCount)
    	{
			return $LevelUpCount;    
		}
		else
		{
			return 0; 	
		}
	}

    public function insertCharacterProductSendLog($UserId,$DataArr)
    {        
        $position = Base_Common::getUserDataPositionById($UserId);
        $table_user = Base_Common::getUserTable($this->table_send_log_user,$position);
        return  $this->db->replace($table_user,$DataArr);
    }
    public function getSendErrorList($UserId,$SendId,$fields= '*')
    {        
        $position = Base_Common::getUserDataPositionById($UserId);
        $table_user = Base_Common::getUserTable($this->table_send_log_user,$position);
		$sql = "select $fields from $table_user where `SendStatus` = 2 and `UserId` = ? and `SendId` = ?";
        return  $this->db->getAll($sql,array($UserId,$SendId));
    }
	
}

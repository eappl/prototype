<?php

!defined('IN_TIPASK') && exit('Access Denied');

class operatormodel {

    var $db;
    var $base;

    function operatormodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }
    
    function getList($start=0, $limit=20,$where=''){
    	$sql = "select o.*,j.name as j_name,d.name as d_name,p.name as p_name,o.detail_type from ". DB_TABLEPRE . "operator as o " .
    		   "left join ".DB_TABLEPRE ."job as j on o.jid = j.id ".
    	       "left join ".DB_TABLEPRE ."post as p on o.pid = p.id ".
    	       "left join ".DB_TABLEPRE ."department as d on o.did = d.id $where";
    	$sql .=" ORDER BY o.id ASC";         
    	$sql .=$limit>0?" LIMIT $start,$limit":"";
    	$operatorlist = $this->db->fetch_all($sql,"id");
		return $operatorlist;
    }  
    
    function get($id) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "operator WHERE id='$id'");
    }
	function getByColumn($column,$name,$all = 0) 
	{
		$sql = "SELECT * FROM " . DB_TABLEPRE . "operator WHERE $column='$name'";
		if($all==0)
		{
			return $this->db->fetch_first($sql);
		}
		else
		{
			return $this->db->fetch_all($sql);		
		}
    }
	function updateOperatorById($id,$operatorInfo)
	{
		foreach($operatorInfo as $key => $value)
		{
			$txt[$key] = "`".$key."`='".$value."'";
		}
		$sql = "update ".DB_TABLEPRE."operator set ".implode($txt,",")." where id = ".intval($id);		
		return $this->db->query($sql);
	}
	function updateOperatorByName($login_name,$operatorInfo)
	{
		foreach($operatorInfo as $key => $value)
		{
			$txt[$key] = "`".$key."`='".$value."'";
		}
		$sql = "update ".DB_TABLEPRE."operator set ".implode($txt,",")." where login_name = '".trim($login_name)."'";		
		return $this->db->query($sql);
	}
	
    function set($login_name,$isbusy){
    	return $this->db->query("UPDATE ".DB_TABLEPRE."operator SET isbusy='$isbusy' WHERE login_name ='$login_name'");
    }
    function getNum($where){
    	return $this->db->result_first("SELECT COUNT(*) num FROM ".DB_TABLEPRE."operator $where");
    }
    
    function add($login_name,$department,$post,$job,$Vadmin,$id=0){
    	if($id == 0)
    	{
    	        $this->db->query("INSERT INTO " . DB_TABLEPRE . "operator SET Vadmin='$Vadmin',login_name='$login_name',did='$department',pid='$post',jid='$job'");
    	        $_ENV['operator']->rebuildOperator($login_name);
    	}	
        else
        { 
	        $sql = "UPDATE " . DB_TABLEPRE . "operator SET Vadmin='$Vadmin',login_name='$login_name',did='$department',pid='$post',jid='$job' WHERE id=$id";
	        $this->db->query($sql);
	    }
	        
    }
    
    function getWhere($login_name_search='',$name_search='',$cno_search='',$department_search='',$post_search='',$job_search='',$flag=false){
    	$where = " where 1 ";
    	if($flag){
    		$login_name_search != '' && $where.="and o.login_name='$login_name_search'";
	        $name_search != '' && $where.=" and o.name='$name_search'";
	        $cno_search != '' && $where.=" and o.cno='$cno_search'";
	        $department_search != 0 && $where.=" and o.did='$department_search'";
	        $post_search != 0 && $where.=" and o.pid='$post_search'";
	        $job_search != 0 && $where.=" and o.jid='$job_search'";
    	}else{
    		$login_name_search != '' && $where.=" and login_name='$login_name_search'";
	        $name_search != '' && $where.=" and name='$name_search'";
	        $cno_search != '' && $where.=" and cno='$cno_search'";
	        $department_search != 0 && $where.=" and did='$department_search'";
	        $post_search != 0 && $where.=" and pid='$post_search'";
	        $job_search != 0 && $where.=" and jid='$job_search'";
    	}
    	return $where;       
    }
    
    /**
     *  取得分单查询的条件
     * @param  $login_name_search 客服名 
     * @param  $job_search 岗位 
     * @param  $busy_search
     * @param  $handle_search
     * @param  $hawb_search
     * @param bool $flag 为true 则获取分单详细 ，否则获取分单总数
     * @return string
     */
    function getHawbWhere($login_name_search='',$job_search='',$busy_search='',$handle_search='',$hawb_search='',$isonjob_search='',$flag=false){
    	$where = " where 1 ";
    	if($flag){
    		$login_name_search != '' && $where.="and o.login_name='$login_name_search'";
	        $job_search != -1 && $where.=" and o.jid='$job_search'";
	        $busy_search != -1 && $where.=" and o.isbusy='$busy_search'";
	        $handle_search != -1 && $where.=" and o.ishandle='$handle_search'";
	        //$hawb_search != -1 && $where.=" and o.type='$hawb_search'";
	        $isonjob_search !=-1 && $where.=" and o.isonjob='$isonjob_search'";
    	}else{
    		$login_name_search != '' && $where.=" and login_name='$login_name_search'";
	        $job_search != -1 && $where.=" and jid='$job_search'";
	        $busy_search != -1 && $where.=" and isbusy='$busy_search'";
	        $handle_search != -1 && $where.=" and ishandle='$handle_search'";
	        //$hawb_search != -1 && $where.=" and type='$hawb_search'";
	        $isonjob_search !=-1 && $where.=" and isonjob='$isonjob_search'";
    	}
    	return $where;       
    }
    
    /**
     * 根据用户名查找用户信息
     */
    function getUser($name,$flush=0) {
		if($flush==0)
		{
    		$return = $this->cache->get("operatorCommunication_".$name);
    		if(false !== $return) 
    		{
    		    $OperatorInfo = json_decode($return,true);
    		    return($OperatorInfo);
    		}
	    }
	    $OperatorInfo =  $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "operator WHERE login_name='$name'");
    	$this->cache->set("operatorCommunication_".$name,json_encode($OperatorInfo),60);//缓存一分钟
    	return $OperatorInfo;

    }
    /**
     * 更新用户数据
     */
    function update($slogan,$photo,$jobnumber,$login_name,$qq,$mobile,$weixin,$tel,$Vadmin)
    { 	
    	if(empty($photo)){
    		$query = "UPDATE " . DB_TABLEPRE . "operator SET Vadmin='$Vadmin',slogan='$slogan',jobnumber='$jobnumber',QQ='$qq',weixin='$weixin',mobile='$mobile',tel='$tel' WHERE login_name='$login_name'";
    	}else{
    		$query = "UPDATE " . DB_TABLEPRE . "operator SET Vadmin='$Vadmin',is_photo=1,slogan='$slogan',jobnumber='$jobnumber',photo='$photo',QQ='$qq',weixin='$weixin',mobile='$mobile',tel='$tel' WHERE login_name='$login_name'";
    	}
    	$this->db->query($query);
    }
    //分单配置更新
    function hawbUpdate($id,$isbusy=0,$ishandle=0,$istype=0,$detail_type='',$isonjob=0){
    	$this->db->query("UPDATE " . DB_TABLEPRE . "operator SET isbusy='$isbusy',ishandle='$ishandle',type='$istype',detail_type='$detail_type',isonjob='$isonjob' WHERE id='$id'");
    }
    // 协助处理配置更新
    function helpUpdate($id,$ishelp=0,$istype=0,$detail_type=''){
    	$this->db->query("UPDATE " . DB_TABLEPRE . "operator SET ishelp='$ishelp',type='$istype',detail_type='$detail_type' WHERE id='$id'");
    }
    function get_detail_type($id){
    	$data = $this->db->result_first("SELECT detail_type FROM " . DB_TABLEPRE . "operator WHERE id='$id'");
    	if($data != ''){
    		return (explode(',',$data));
    	}else{
    		return false;
    	}
    }

    // 获取所有协助人列表
    function get_help_aid(){
    	$options_list = array();
    	$help_aid = $this->db->fetch_all('SELECT id,login_name FROM `' . DB_TABLEPRE . 'operator` WHERE ishelp=1 ');
    	foreach($help_aid as $key => $val){
    		$options_list[$val['id']] = $val['login_name'];
    	}
    	return $options_list;
    }
    
    //根据部门获取协助人列表
    function get_aid_by_did($did,$login_name){
    	$options_list = array();
    	$help_aid = $this->db->fetch_all("SELECT id,login_name FROM " . DB_TABLEPRE . "operator WHERE ishelp=1 AND isonjob=1 AND ishandle=1 AND did=$did AND login_name !='$login_name'");
    	foreach($help_aid as $key => $val){
    		$options_list[$val['id']] = $val['login_name'];
    	}
    	return $options_list;
    }
    // 获取所有协助人列表
    function getAllOperator(){
    	$operator_list = array();
    	$operator = $this->db->fetch_all('SELECT id,Vadmin,login_name FROM `' . DB_TABLEPRE . 'operator` ');
    	foreach($operator as $key => $val)
    	{
    		$operator[$val['id']]['Vadmin'] = $val['Vadmin'];
    		$operator[$val['id']]['login_name'] = $val['login_name'];
    	}
    	return $operator;
    }
    function rebuildOperator($operatorName)
    {
        if($operatorName=="")
        {
            return false;
        }
        $Operator = $this->getOperatorFromVadmin($operatorName);
		if(!empty($Operator['photo'])){
    		$query = "UPDATE " . DB_TABLEPRE . "operator SET is_photo=1,photo='".$Operator['photo']."',name='".$Operator['name']."',cno='".$Operator['cno']."',qq_url='".$Operator['qq_url']."',QQ='".$Operator['QQ']."',weixin='".$Operator['weixin']."',weixinPicUrl='".$Operator['weixinPicUrl']."',weixinPicUrl_officer='".$Operator['weixinPicUrl_officer']."',mobile='".$Operator['mobile']."',tel='".$Operator['tel']."',login_name_officer='".$Operator['login_name_officer']."',photo_officer='".$Operator['photo_officer']."',name_officer='".$Operator['name_officer']."',cno_officer='".$Operator['cno_officer']."',qq_url_officer='".$Operator['qq_url_officer']."',QQ_officer='".$Operator['QQ_officer']."',weixin_officer='".$Operator['weixin_officer']."',mobile_officer='".$Operator['mobile_officer']."',tel_officer='".$Operator['tel_officer']."',qq_link_type='".$Operator['qq_link_type']."',xnGroupId='".$Operator['xnGroupId']."',xnGroupId_officer='".$Operator['xnGroupId_officer']."' WHERE login_name= '".$operatorName."'";
    	}else{
    		$query = "UPDATE " . DB_TABLEPRE . "operator SET is_photo=0,photo='',name='".$Operator['name']."',cno='".$Operator['cno']."',qq_url='".$Operator['qq_url']."',QQ='".$Operator['QQ']."',weixin='".$Operator['weixin']."',weixinPicUrl='".$Operator['weixinPicUrl']."',weixinPicUrl_officer='".$Operator['weixinPicUrl_officer']."',mobile='".$Operator['mobile']."',tel='".$Operator['tel']."',login_name_officer='".$Operator['login_name_officer']."',photo_officer='".$Operator['photo_officer']."',name_officer='".$Operator['name_officer']."',cno_officer='".$Operator['cno_officer']."',qq_url_officer='".$Operator['qq_url_officer']."',QQ_officer='".$Operator['QQ_officer']."',weixin_officer='".$Operator['weixin_officer']."',mobile_officer='".$Operator['mobile_officer']."',tel_officer='".$Operator['tel_officer']."',qq_link_type='".$Operator['qq_link_type']."',xnGroupId='".$Operator['xnGroupId']."',xnGroupId_officer='".$Operator['xnGroupId_officer']."' WHERE login_name= '".$operatorName."'";
    	}
    	$this->db->query($query);
    	$num = $this->db->affected_rows(); 
    	return $num;       
    }
   function getOperatorFromVadmin($operatorName)
    {
        $IP = $this->base->getLocalIP();    

        $array = array('CurrentIp'=>$IP,'OpLoginId'=>$operatorName);
        $Data = json_encode($array);
        $key = "987654321!@#$%";
        $txt = "6".$Data.$key;
        $sign = md5(strtoupper($txt));
        $Data = urlencode(base64_encode($Data));  
        $url = "http://tradeservice.5173esb.com/CommService/CommonRequest.ashx?OperationType=6&Data=$Data&Sign=$sign";
        $return = file_get_contents($url);
        $return_arr = json_decode(base64_decode($return),true);
        if(is_array($return_arr))
        {
            $OperatorInfo = json_decode($return_arr['JsonData'],true);
            if($OperatorInfo['OpLoginId']!="")
            {
				$Operator = array('photo'=>$OperatorInfo['OpAvatar'],
				'QQ'=>$OperatorInfo['OPQQ'],
				'mobile'=>$OperatorInfo['OPMObile'],
				'tel'=>$OperatorInfo['OPTel'],
				'weixin'=>$OperatorInfo['OPWeiXin'],
				'name'=>$OperatorInfo['OPName'],
				'cno'=>$OperatorInfo['OpRealName'],
				'login_name'=>$OperatorInfo['OpLoginId'],
				'weixinPicUrl'=>$OperatorInfo['OPWeiXinPicUrl'],
				'weixinPicUrl_officer'=>$OperatorInfo['OPWeiXinPicUrl2'],

				'xnGroupId'=>$OperatorInfo['OPSmallCanUID'],
				'xnGroupId_officer'=>$OperatorInfo['OPSmallCanUID2'],

				'photo_officer'=>$OperatorInfo['OpAvatar2'],
				'qq_url'=>$OperatorInfo['OPQQIdKey']==""?"":('http://sighttp.qq.com/authd?IDKEY='.$OperatorInfo['OPQQIdKey']),
				'qq_url_officer'=>$OperatorInfo['OPQQIdKey2']==""?"":('http://sighttp.qq.com/authd?IDKEY='.$OperatorInfo['OPQQIdKey2']),
				'QQ_officer'=>$OperatorInfo['OPQQ2'],
				'mobile_officer'=>$OperatorInfo['OPMObile2'],
				'tel_officer'=>$OperatorInfo['OPTel2'],
				'weixin_officer'=>$OperatorInfo['OPWeiXin2'],
				'name_officer'=>$OperatorInfo['OPName2'],
				'cno_officer'=>$OperatorInfo['OpRealName2'],
				'login_name_officer'=>$OperatorInfo['OpLoginId2'],
				'qq_link_type'=>$OperatorInfo['IsNewPopQQ']==0?'js':'http',
                );  
                return $Operator;  	
            }
            else
            {
                return false;    
            }   
        }
        else
        {
            return false;    
        }            
    }
	public function getOnlineOperator($StartTime,$EndTime,$OperatorList)
	{
		if(count($OperatorList)>0)
		{
			$PostData = array('StartTime'=>$StartTime,'EndTime'=>$EndTime,'OperatorList'=>$OperatorList);
		}
		else
		{
			$PostData = array('StartTime'=>$StartTime,'EndTime'=>$EndTime);	
		}
		$PostData = rawurldecode(json_encode($PostData));
		$ComplainKey = "%YOJNCWQRIWA:OE YV)ENVRMQOWV {)RWCJNWQBCVCE WQMEJC WROL VR";
		$ComplainUrl = "http://complain.5173.com/sc/GetWorkStatus.ashx";
		$returnData = do_post($ComplainUrl,array('data'=>$PostData,'key'=>md5($PostData.$ComplainKey)));
		$returnData = json_decode($returnData,true);
		return $returnData;
	}
	function InsertWorkLog($operatorInfo)
	{
		$sql = "replace into operator_work_log (OperatorName,Date,Hour) values ('".$operatorInfo['OperatorName']."','".$operatorInfo['Date']."',".$operatorInfo['Hour'].")";
		return $this->db->query($sql);
	}
	function DeleteWorkLog($operatorInfo)
	{
		$sql = "delete from operator_work_log where Date = '".$operatorInfo['Date']."' and Hour = ".$operatorInfo['Hour'];
		return $this->db->query($sql);
	}
	function getOnlineOperatorCount($ConditionList)
	{
		$whereStartDate = $ConditionList['StartDate']?" Date >= '".$ConditionList['StartDate']."' ":"";
		$whereEndDate = $ConditionList['EndDate']?" Date <= '".$ConditionList['EndDate']."' ":"";
		if($ConditionList['DepartmentId'])
		{
			$OperatorList = $_ENV['operator']->getByColumn('did',$ConditionList['DepartmentId'],1);
		}
		$O = array();
		foreach($OperatorList as $key => $OperatorInfo)
		{
			
			$O[] = $OperatorInfo['login_name'];
		}
		if($ConditionList['DepartmentId'])
		{
			$t = array();
			foreach($OperatorList as $key => $OperatorInfo)
			{
				$t[] = "'".$OperatorInfo['login_name']."'";
				$OperatorListText = implode(",",$t);
				if($OperatorListText != "")
				{
					$WhereOperator = " OperatorName in (".$OperatorListText.")";
				}
			}
		}
		else
		{
			$WhereOperator = "";
		}
		
		$whereCondition = array($whereStartDate,$whereEndDate,$WhereOperator);
		
		foreach($whereCondition as $key => $value)
		{
			if(trim($value)=="")
			{
				unset($whereCondition[$key]);
			}
		}
		if(count($whereCondition)>0)
		{
			$where = "and ".implode(" and ",$whereCondition);
		}
		else
		{
			$where = "";
		}
				
		
		$data_sql = "select count(*) as OperatorCount,Hour from operator_work_log where 1  ".$where." group by Hour order by Hour";
		$OperatorData = $this->db->fetch_all($data_sql);
		foreach($OperatorData as $key => $value)
		{
			$Hour = sprintf("%02d",$value['Hour']);
			$returnArr[$Hour] += $value['OperatorCount'];
		}
		return $returnArr;
		
	}
}

?>

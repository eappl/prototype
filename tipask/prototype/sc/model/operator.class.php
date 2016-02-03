<?php

!defined('IN_TIPASK') && exit('Access Denied');

class operatormodel extends base{

    var $db;
    var $base;
	var $table_operator = "ask_operator";
	var $table_author_num = "ask_author_num";
    function operatormodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
        $this->pdo = $this->base->init_pdo($this->table_operator);

    }
    
    function get($id) {
    	$table_name = $this->base->getDbTable($this->table_operator);
    	$Operator = $this->pdo->selectRow($table_name, "*", '`id` = ?', $id);
    	return $Operator;
    }
	function getByColumn($column,$name,$all = 0) 
	{
    	$table_name = $this->base->getDbTable($this->table_operator);
		$sql = "SELECT * FROM $table_name WHERE $column='$name'";
		if($all==0)
		{
			return $this->pdo->getRow($sql);
		}
		else
		{
			return $this->pdo->getAll($sql);		
		}
    }
	function getAuthorNum($author) {
    	$table_name = $this->base->getDbTable($this->table_author_num);
    	$AuthorNum = $this->pdo->selectRow($table_name, "*", '`author` = ?', $author);
    	return $AuthorNum;
    }
	function updateAuthorNum($author,$time,$add=0) 
	{
    	$table_name = $this->base->getDbTable($this->table_author_num);
    	if($add==0)//首问
		{
			$sql = "INSERT INTO $table_name (author,num,num_add,last_receive,last_receive_add) VALUES ('".$author."',1,0,".$time.",0)  ON DUPLICATE KEY UPDATE num = num+1,last_receive=".$time;
		}
		else
		{
			$sql = "INSERT INTO $table_name (author,num,num_add,last_receive,last_receive_add) VALUES ('".$author."',0,1,0,".$time.")  ON DUPLICATE KEY UPDATE num_add = num_add+1,last_receive_add=".$time;		
		}
		return $this->pdo->query($sql);
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
     * 生成用户图片
     * @param unknown_type $data
     */
    function showphoto($data){
    	header('Content-type: image/gif');
    	echo $data;
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
		//print_R($Operator);
		//echo $query;
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
	/**
	 *  获取绑定客服
	 *  @loginName sc登陆用户名
	 *  @reuturn string
	 */
	function getMySelfAuthor($loginName)
	{
		if($loginName != '游客')
		{
			// 获取绑定客服id
			$scid = $_ENV['bind_log']->getBindAuthorId($loginName);
			if(!empty($scid))
			{
				$operatorInfo = $this->get($scid);
				if($operatorInfo['id']>0)
				{
					return $operatorInfo;
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
		else
		{
			return false;
		}
	}
}

?>

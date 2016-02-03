<?php
/**
 * 客服信息mod层
 * $Id: BroadCastController.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Kubao_Operator extends Base_Widget
{
	//声明所用到的表
	protected $table = 'ask_operator';
	protected $table_post = 'ask_post';
	protected $table_accepted = 'ask_author_num';
	
	//根据客服ID获取客服信息
	public function getOperatorById($OperatorId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`id` = ?', $OperatorId);
	}
	//根据客服登陆账号获取客服信息
	public function getOperatorByName($OperatorName,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`login_name` = ?', $OperatorName);
	}
	//根据职位获取客服信息
	public function getOperatorByPost($PostId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->select($table_to_process, $fields, '`pid` = ?', $PostId);
	}
	//从SC站点获取小能的默认配置信息
	public function getXNDefault()
	{
		//获取全局配置
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$Setting = $oMenCache -> get('setting');	
		$XNDefault['xnSiteId'] = $Setting['xn_siteid'];
		$XNDefault['xnSellerId'] = $Setting['xn_sellerid'];
		$XNDefault['xnDefaultSettingId'] = $Setting['xn_default_settingid'];
		return $XNDefault;
	}
	//格式化客服信息供前台显示
	public function processOperatorInfo($OperatorInfo)
	{
		//获取全局配置
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$Setting = $oMenCache -> get('setting');	
		$NewOperatorInfo = array();		
		if(isset($OperatorInfo['id']))
		{
			$NewOperatorInfo['OperatorId'] = $OperatorInfo['id'];
			$NewOperatorInfo['ServiceLogUrl'] = $this->config->ScUrl."/?question/selfHistoryQuestion/".urlencode($OperatorInfo['login_name']);
		}
		if(isset($OperatorInfo['login_name']))
		{
			$NewOperatorInfo['OperatorName'] = $OperatorInfo['login_name'];
		}
		if(isset($OperatorInfo['cno']))
		{
			$NewOperatorInfo['Cno'] = $OperatorInfo['cno'];
		}
		if(isset($OperatorInfo['name']))
		{
			$NewOperatorInfo['NickName'] = $OperatorInfo['name'];
		}
		if(isset($OperatorInfo['photo']))
		{
			$NewOperatorInfo['PhotoLink'] = strlen(trim($OperatorInfo['photo']))>=1?trim($OperatorInfo['photo']):$this->config->DefaultOperatorPic;
		}

		if(isset($OperatorInfo['tel']) && $Setting['telDisplay'])
		{
			$NewOperatorInfo['Tel'] = $OperatorInfo['tel'];
		}
		if(isset($OperatorInfo['mobile']))
		{
			$NewOperatorInfo['Mobile'] = Base_Common::convertPhoneNum($OperatorInfo['mobile']);
		}
		if(isset($OperatorInfo['weixin']))
		{
			$NewOperatorInfo['Weixin'] = $OperatorInfo['weixin'];
			$NewOperatorInfo['WeixinPhotoLink'] = $OperatorInfo['weixinPicUrl'];
		}
		if(isset($OperatorInfo['xnGroupId']) && $Setting['xnDisplay'])
		{
			$NewOperatorInfo['xn']['xnGroupId'] = $OperatorInfo['xnGroupId'];
			$XNDefault = $this->getXNDefault();
			$NewOperatorInfo['xn']['xnSiteId'] = $XNDefault['xnSiteId'];
			$NewOperatorInfo['xn']['xnSellerId'] = $XNDefault['xnSellerId'];
			$NewOperatorInfo['xn']['xnDefaultSettingId'] = $XNDefault['xnDefaultSettingId'];			
		}
		else
		{
			if(isset($OperatorInfo['QQ']) && $Setting['qqDisplay'])
			{
				$NewOperatorInfo['QQ'] = $OperatorInfo['QQ'];
			}						
		}
		return $NewOperatorInfo;				
	}
   function getOperatorFromVadmin($OperatorName,$Detail = "")
    {
		Base_Common::getLocalIP();    
		$array = array('CurrentIp'=>$IP,'OpLoginId'=>$OperatorName);
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
				//去除所需列表之外的数据
				$t = explode(",",$Detail);
				{
					if(count($t)>1)
					{
						foreach($Operator as $key => $value)
						{
							if(!in_array($key,$t))
							unset($Operator[$key]);
						}
					}
				}
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
	//获取所有客服职位的信息
	public function getAllPost($fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_post);
		$Sql = "select $fields from ".$table_to_process;
		return $this->db->getAll($Sql);
	}
	//获取指定客服职位的信息
	public function getPost($PostId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_post);
		return $this->db->selectRow($table_to_process, $fields, '`id` = ?', $PostId);
	}
	//获取所有客服接单数量
	public function getAllOperatorAccecpted($fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_accepted);
		$Sql = "select $fields from ".$table_to_process. " order by num";
		return $this->db->getAll($Sql);
	}
	//获取指定客服接单数量
	public function getOperatorAccecpted($OperatorName,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_accepted);
		return $this->db->selectRow($table_to_process, $fields, '`author` = ?', $OperatorName);
	}
	//更新指定客服接单数量
	public function UpdateOperatorAccecpted($OperatorName,$Num,$IsAdd)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_accepted);
		if($Num>0)
		{
			//如果是主问
			if($IsAdd == 0)
			{
				$InsertArr = array("author"=>$OperatorName,"num"=>$Num,"num_add"=>0,"last_receive"=>time(),"last_receive_add"=>0);
				$UpdateArr = array("num"=>"_num+".$Num,"last_receive"=>time());

			}
			else
			{
				$InsertArr = array("author"=>$OperatorName,"num"=>0,"num_add"=>$Num,"last_receive"=>0,"last_receive_add"=>time());
				$UpdateArr = array("num_add"=>"_num_add+".$Num,"last_receive_add"=>time());
			}
			return $this->db->insert_update($table_to_process,$InsertArr,$UpdateArr);
		}
		else
		{
			//如果是主问
			if($IsAdd == 0)
			{
				$UpdateArr = array("num"=>"_num-".abs($Num));

			}
			else
			{
				$UpdateArr = array("num_add"=>"_num_add-".abs($Num));
			}
			return $this->db->update($table_to_process,$UpdateArr,'`author`=?',array($OperatorName));
		}
	}
	//获取所有客服职位的信息
	public function getAllOperator($fields = '*',$ConditionList)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		if(is_array($ConditionList))
		{
			$where = array();
			$params = array();
			foreach($ConditionList as $key => $value)
			{
				$where[] = "`".$key."`" ."=?";
				$params[] = $value;
			}
			$where = implode(" and ",$where);
			return $this->db->select($table_to_process,$fields,$where,$params);
		}
		else
		{
			$Sql = "select $fields from ".$table_to_process;
			return $this->db->getAll($Sql);
		}
	}
	//获取可接单的客服列表，以及各人已接单量，剩余单量，以及总计剩余单量
    //$add=0为获取首问接单数据，否则为追问接单数据
    function getAcceptableOperator($add = 0)
    {
    	//本轮所要获取的最大单量
    	$o_arr = array('operator'=>array(),'totalAcceptable'=>0);
        //取各部门接单限额
    	$PostList = $this->getAllPost("id,name,question_limit,question_limit_add");
    	foreach($PostList as $key => $PostInfo)
    	{
    		 //职位的咨询最大单量
    		 $OperatorList['post'][$PostInfo['id']]['question_limit_add'] = $PostInfo['question_limit_add'];
    		 $OperatorList['post'][$PostInfo['id']]['question_limit'] = $PostInfo['question_limit'];
    		 $OperatorList['post'][$PostInfo['id']]['name'] = $PostInfo['name'];
    	}

        //取可以接单的客服
    	$ConditionList = array('isbusy'=>0,'ishandle'=>1,'isonjob'=>1);
		$Operator = $this->getAllOperator("login_name,pid,detail_type",$ConditionList);
		foreach($Operator as $key => $OperatorInfo)
		{
    		 //当前单量
    		 $OperatorList['operator'][$OperatorInfo['login_name']]['handling'] = 0;
    		 //剩余最大单量
    		 if(isset($OperatorList['post'][$OperatorInfo['pid']]))
    		 {
    		    if($add==0)
    		    {
    		        $limit = $OperatorList['post'][$OperatorInfo['pid']]['question_limit'];
    		        
    		    }
    		    else
    		    {
     		        $limit = $OperatorList['post'][$OperatorInfo['pid']]['question_limit_add'];
                }
                $OperatorList['operator'][$OperatorInfo['login_name']]['last'] = $limit;
    		 }
    		 else
    		 {
                $OperatorList['operator'][$OperatorInfo['login_name']]['last'] = 0;
             }
			 $OperatorList['operator'][$OperatorInfo['login_name']]['detail_type_list'] = explode(",",$OperatorInfo['detail_type']);
			 $OperatorList['totalAcceptable'] += $limit;
		}
		//获取客服的接单量情况
		$AcceptedStatus = $this->getAllOperatorAccecpted('author,num,num_add,last_receive,last_receive_add');
		foreach($AcceptedStatus as $key => $OperatorAccepted)
		{
			if(isset($OperatorList['operator'][$OperatorAccepted['author']]))
			{
				if($add==0)
				{
					$num = $OperatorAccepted['num'];
					$last_receive = $OperatorAccepted['last_receive'];    
				}
				else
				{
					$num = $OperatorAccepted['num_add'];
					$last_receive = $OperatorAccepted['last_receive_add'];   
				}
				//当前单量
				$OperatorList['operator'][$OperatorAccepted['author']]['handling'] = $num;
				$OperatorList['operator'][$OperatorAccepted['author']]['last_receive'] = $last_receive;
				//剩余最大单量
				$OperatorList['operator'][$OperatorAccepted['author']]['last'] = $OperatorList['operator'][$OperatorAccepted['author']]['last'] - $num;
				if($OperatorList['operator'][$OperatorAccepted['author']]['last']>=0)
				{
					$OperatorList['totalAcceptable'] -= $num;
				}
			}
		}

		unset($OperatorList['post']);
		return $OperatorList;
    }
    //根据可接单的客服列表选择最大单量的客服
    //$AcceptableOperatorList：可接单的客服列表
    function getMaxAcceptableOperator($AcceptableOperatorList,$cid = 0)
    {
		$operator['min'] = 0;
        $operator['operator'] = "";
        //优先不选择上轮分单的客服
        if($AcceptableOperatorList['last_accept']['operator']!='')
        {
			$AcceptableOperatorList['last_accept']['last'] = $AcceptableOperatorList['operator'][$AcceptableOperatorList['last_accept']['operator']]['last']; 
			unset($AcceptableOperatorList['operator'][$AcceptableOperatorList['last_accept']['operator']]);
        }
        foreach($AcceptableOperatorList['operator'] as $operator_name => $operator_data)
        {
			//该客服当前可以接单
			if($operator_data['last']>0)
            {
				//当前最小值为0
                if($operator['min']==0)
				{
					//获取当前值为非负
					if($operator_data['last_receive']>=0)
					{
						//如果最小值0为初始
						if($operator['operator']=="")
						{
							//if($cid == -1 || ($cid==0 && (in_array(-1,$operator_data['detail_type_list']))) || ($cid>0 && in_array($cid,$operator_data['detail_type_list'])))
							if(($cid==0 && (in_array(-1,$operator_data['detail_type_list']))) || ($cid>0 && in_array($cid,$operator_data['detail_type_list'])))
							//则赋值
							{
								$operator['min'] = $operator_data['last_receive'];
								$operator['operator'] = $operator_name;
							}
						}
					}
				}
				//如果最小值大于0
				elseif($operator['min']>0)
				{
					//如果最小值大于传入值
					if($operator['min']>$operator_data['last_receive'])
					{
						//if($cid == -1 || ($cid==0 && (in_array(-1,$operator_data['detail_type_list']))) || ($cid>0 && in_array($cid,$operator_data['detail_type_list'])))
						if(($cid==0 && (in_array(-1,$operator_data['detail_type_list']))) || ($cid>0 && in_array($cid,$operator_data['detail_type_list'])))
						//则覆盖
						{
							$operator['min'] = $operator_data['last_receive'];
							$operator['operator'] = $operator_name;
						}
					}
				}
            }
            else
            {
                
            }
        }
		//如果筛选结果为空
        if($operator['operator']=='')
        {
			//判断上轮接单的客服是否可以接单
			if($AcceptableOperatorList['last_accept']['last']>0)
            {
               //是则将单分给上轮接单客服
               $operator['operator'] = $AcceptableOperatorList['last_accept']['operator'];
            }
        }
        return $operator;
    }
    //根据从Vadmin获取的最新客服信息重建客服数据
    //$AcceptableOperatorList：可接单的客服列表
	function RebuildOperator($OperatorName)
	{
		$Operator = $this->getOperatorFromVadmin($OperatorName);
		echo "operator:".$OperatorName."\n";
		echo $Operator['login_name']."\n";
		sleep(1);
		
		if($Operator['login_name'])
		{
		    $OperatorInfo = array(
				'is_photo'=>strlen($Operator['photo'])>1?1:0,
				'photo'=>$Operator['photo'],
				'name'=>$Operator['name'],
				'cno'=>$Operator['cno'],
				'qq_url'=>$Operator['qq_url'],
				'QQ'=>$Operator['QQ'],
				'weixin'=>$Operator['weixin'],
				'weixinPicUrl'=>$Operator['weixinPicUrl'],
				'weixinPicUrl_officer'=>$Operator['weixinPicUrl_officer'],
				'mobile'=>$Operator['mobile'],
				'tel'=>$Operator['tel'],
				'login_name_officer'=>$Operator['login_name_officer'],
				'name_officer'=>$Operator['name_officer'],
				'photo_officer'=>$Operator['photo_officer'],
				'cno_officer'=>$Operator['cno_officer'],
				'qq_url_officer'=>$Operator['qq_url_officer'],
				'QQ_officer'=>$Operator['QQ_officer'],
				'weixin_officer'=>$Operator['weixin_officer'],
				'mobile_officer'=>$Operator['mobile_officer'],
				'tel_officer'=>$Operator['tel_officer'],
				'qq_link_type'=>$Operator['qq_link_type'],
				'xnGroupId'=>$Operator['xnGroupId'],
				'xnGroupId_officer'=>$Operator['xnGroupId_officer']
			);
			return $this->updateOperatorByName($Operator['login_name'],$OperatorInfo);
		}
	}
	//根据账号更新客服信息
	public function updateOperatorByName($OperatorName, array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`login_name` = ?', $OperatorName);
	}
}

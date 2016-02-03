<?php

!defined('IN_TIPASK') && exit('Access Denied');

class base {

    var $ip;
    var $time;
    var $db;
    var $db_h;
    var $cache;
    var $ask_config;
    var $ask_front_id = '';
    var $ask_front_name = '游客';
    var $ask_login_name;
    var $ask_permission;
    var $setting = array();
    var $onlineConfig = array();
    var $get = array();
    var $post = array();

    function base(& $get, & $post) {   	
        $this->time = time();
        $this->ip = getip();
        $this->get = & $get;
        $this->post = & $post;
        $this->init_db();
        $this->init_cache(); 
		//$this->init_redis(); 		
        $this->ask_config = new config();    
        $this->onlineConfig = getConfig(); // 获取配置文件
        $this->check_login();
        $this->sys_error_handle();
    }

    function init_db() {
        $this->db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    }
    //PDO MYSQL初始化
    function init_pdo($table) {
        $this->hash = new hash();
        $this->pdo = hash::getInstance()->prepare($table);
//        $Instance = $this->hash->getInstance();
//        $this->pdo = $this->hash->prepare($table);
        return $this->pdo;   
    }
	public function getDbTable($table = null,$db = null, $key = null)
	{
		return hash::getInstance()->getHashTable($table, $db, $key);
	}

    /* 一旦setting的缓存文件读取失败，则更新所有cache */

    function init_cache() {
        global $setting;
        $this->cache = new CacheMemcache(array('db'=>$this->db));
        $setting = $this->setting = $this->cache->load('setting');
    }
    function init_redis() {
        global $setting;
        $this->redis = new CacheRedis(array('db'=>$this->db));
    }

    function load($model, $base = NULL) {
        $base = $base ? $base : $this;
        if (empty($_ENV[$model])) {
            require TIPASK_ROOT . "/model/$model.class.php";
            eval('$_ENV[$model] = new ' . $model . 'model($base);');
        }
        return $_ENV[$model];
    }
 

    /* 	中转提示页面
      $ishtml=1 表示是跳转到静态网页
     */

    function message($message, $url = '') {
        $seotitle = '操作提示';
        if ('' == $url) {
            $redirect = SITE_URL;
        } else if ('BACK' == $url || 'STOP' == $url) {
            $redirect = $url;
        } else {
            $redirect = SITE_URL . $this->setting['seo_prefix'] . $url . $this->setting['seo_suffix'];
        }
        $tpldir = (0 === strpos($this->get[0], 'admin')) ? 'admin' : $this->setting['tpl_dir'];
        include template('tip', $tpldir);
        exit;
    }
    
    /*检查用户的登录状态*/
    function check_login()
    {
    	$isadmin = ('admin' == substr($this->get[0], 0, 5));
    	if($isadmin){
    		if(!isset($_COOKIE[config::ADMIN_COOKIE]) || empty($_COOKIE[config::ADMIN_COOKIE])){
    			header('Location: http://'.config::DOMAIN_NAME."/Account/Login/?returnUrl=".urlencode(curPageURL()));
    			exit;
    		}   	    
	    	$sign = md5($_COOKIE[config::ADMIN_COOKIE].'_'. $this->onlineConfig['DATA_KEY']);
	    	$url = "http://".config::DOMAIN_NAME."/Service/ValidateToken?token=".$_COOKIE[config::ADMIN_COOKIE]."&sign=".$sign;
	    	$rs = topen($url);
	    	$result = json_decode($rs,true);   	
	    	if(isset($result['ResultNo']) && isset($result['ResultMessage'])){
	    		if($result['ResultNo'] == -1){	    			
	    			send_AIC('http://scadmin.5173.com',$rs,1,'后台登陆接口');
	    			exit($result['ResultMessage']);
	    		}
	    	} 
			$info = explode("\n",$result['Name']);
	    	$this->ask_login_name = $info[2];			
    	}
    	else    	
    	{
    		if(isset($_COOKIE[config::FRONT_COOKIE])){
				$url = "http://".config::FRONT_LOGIN_DOMAIN."/passport/validatecookie?value=".urlencode($_COOKIE[config::FRONT_COOKIE]);
				$rs = topen($url);
		    	$result = json_decode($rs,true);
				if(!empty($result) && $result['ResultNo'] == 0){
		    		if( $result['Ticket']['UserID'] != '' &&  $result['Ticket']['UserName'] != '') {
		    			$this->ask_front_id = $result['Ticket']['UserID'];
		    			$this->ask_front_name = $result['Ticket']['UserName'];
		    		} else {
		    			file_put_contents(TIPASK_ROOT.'/data/logs/author.txt',date("Y-m-d H:i:s"). $rs . "\r\n" ,FILE_APPEND);
		    		}
	    		
		    	}else{		    		
		    		send_AIC('http://sc.5173.com',$rs,1,'前台登陆接口');
		    	}
			} 
    	}                        	
    }
    //检查菜单的权限
    function check_menu_per($permission){
    	if(!in_array($permission,$this->ask_permission)){
    		exit('<script>alert("对不起，您没有权限访问！");window.parent.location.reload();</script>');
    	}
    }
    //检查按钮的权限
    function check_button_per($permission){
    	if(!in_array($permission,$this->ask_permission)){
    		exit('3');
    	}
    }
    
    
    /*获取全部游戏*/
    function get_all_game(){
    	$cache_data = $this->cache->get('all_game');//如果存在缓存，则读取缓存
    	if(false !== $cache_data) return $cache_data;
    	//线下 userInAll user123   线上 shoping5173 sh5173
    	$arr = array();
    	$code = "shoping5173&kubao.cbo.game.get&1.1&&json&Id,Name&GET:http://routeapi.5173.com/rest.do&127.0.0.1&";	
		$sign = base64_encode("sh5173&" . md5($code) . "&");			
        $url = "http://routeapi.5173.com/rest.do?timestamp=".time()."&appid=shoping5173&method=kubao.cbo.game.get&vers=1.1&paraInfo=&format=json&fields=Id,Name&clientIP=127.0.0.1&token="."&sign=".$sign;
		$rs = topen($url);
		$result = json_decode($rs,true);
		if(isset($result[0]['Status']) && $result[0]['Status'] == 32) return $arr;
		foreach($result as $v){			
			$arr[$v['Id']] = $v['Name'];
		}
		if(!empty($arr)) $this->cache->set('all_game',$arr,3600);//写入缓存，缓存有有效期为1小时
		return $arr;
    }
    
    //更新Solr服务器
    function set_search($data=array()) {
    	$this->onlineConfig = require TIPASK_ROOT.'/onlineConfig.php';
    	if(!class_exists('Apache_Solr_Service',false)) {
    		require TIPASK_ROOT . '/api/SolrPhpClient/Apache/Solr/Service.php';
    	}
    	
    	$document = new Apache_Solr_Document();
    	foreach($data as $key => $val) {
    		$document->{$key} = $val;
    	}
    	
    	if(is_array($this->onlineConfig['SOLR_DOMAIN'])) {
    		foreach ($this->onlineConfig['SOLR_DOMAIN'] as $solrDomain) {
    			$solr = new Apache_Solr_Service( $solrDomain, $this->onlineConfig['SOLR_PORT'], '/solr' );
    			$solr->addDocument($document);
    			//$solr->commit(); //commit to see the deletes and the document
    			file_get_contents('http://'.$solrDomain.':'.$this->onlineConfig['SOLR_PORT'].'/solr/update/json?commit=true');
    		}
    		
    	} else {
    		$solr = new Apache_Solr_Service( $this->onlineConfig['SOLR_DOMAIN'], $this->onlineConfig['SOLR_PORT'], '/solr' );
    		$solr->addDocument($document);
    		//$solr->commit(); //commit to see the deletes and the document
    		file_get_contents('http://'.$this->onlineConfig['SOLR_DOMAIN'].':'.$this->onlineConfig['SOLR_PORT'].'/solr/update/json?commit=true'); 
    	}
        
    }
    
    //根据标题从Solr服务器上搜索
    function get_search($arr=array(),$startindex=0, $pagesize=20) {
    	if(!class_exists('Apache_Solr_Service',false)) {
    		require TIPASK_ROOT . '/api/SolrPhpClient/Apache/Solr/Service.php';
    	}  	
    	
		$solr = new Apache_Solr_Service( $this->onlineConfig['SOLR_SEARCH_SERVER'], $this->onlineConfig['SOLR_PORT'], '/solr' );
		//SOLR_SEARCH_SERVER
    	foreach($arr as $key => $val) {
    		$response=$solr->search( $key.':'.$val , $startindex, $pagesize,array('sort' => 'boost desc,time desc,score desc') );
    	}           	                   
        return json_decode($response->getRawResponse(),true);
    }

    //删除Solr服务器上的对应的问题id
    function delete_search($id) {
    	if (!class_exists('Apache_Solr_Service',false)) {
    		require TIPASK_ROOT . '/api/SolrPhpClient/Apache/Solr/Service.php';
    	}
    	
    	if (is_array($this->onlineConfig['SOLR_DOMAIN'])) {
    		
    		foreach ($this->onlineConfig['SOLR_DOMAIN'] as $solrDomain) {
    			$solr = new Apache_Solr_Service( $solrDomain, $this->onlineConfig['SOLR_PORT'], '/solr' );
    			$solr->deleteByQuery('id:'.$id);
    			file_get_contents('http://'.$solrDomain.':'.$this->onlineConfig['SOLR_PORT'].'/solr/update/json?commit=true');
    			 
    		}
    	} else {
    		$solr = new Apache_Solr_Service( $this->onlineConfig['SOLR_DOMAIN'], $this->onlineConfig['SOLR_PORT'], '/solr' );
    		$solr->deleteByQuery('id:'.$id);
    		file_get_contents('http://'.$this->onlineConfig['SOLR_DOMAIN'].':'.$this->onlineConfig['SOLR_PORT'].'/solr/update/json?commit=true');
    	}

    }
    
    //系统故障跳转到老站点
    function sys_error_handle(){
    	$sys_error_btn = $this->db->result_first("SELECT v FROM ".DB_TABLEPRE."setting WHERE k='sys_error_btn'");   	
    	if($sys_error_btn){
    		if(config::FRONT_DOMAIN == getServerName()){
    			if($this->get[0] == 'question' && $this->get[1] == 'ask'){
    				header("Location: http://ask.5173.com/Question.aspx?t=");
    			}else{
    				header("Location: http://ask.5173.com/");
    			}
    		}
    	}    	
    }

   /*系统操作日志
    */
    function sys_admin_log($qid,$user,$message,$type=0){
    	$LogType = $this->ask_config->getLogType();
		$type = intval($type);
		if(isset($LogType[$type]))
		{
			$tip = $LogType[$type];
		}
		else
		{
			return false;
		}
    	$qid = intval($qid);
    	if($qid == 0)
		{
			return false;
		}
    	$log_id = get_log_sn();
    	$message = $tip.$message;
		$time = time();
		$date = date("Ym",$time);
		$sql = "SELECT author from  ".DB_TABLEPRE."question where id = $qid";
		$return = $this->db->fetch_first($sql);//系统操作日志
		$sql = "INSERT INTO ".DB_TABLEPRE."log_".$date." SET AuthorName = '".$return['author']."',id='".$log_id."',qid=".$qid.",user='".$user."',message='".$message."',time='".time()."'";
		$this->db->query($sql);//系统操作日志    
    }
           
    //短信接口
    function send_SMS($author_id){
    	$msg_switch_off = $this->db->result_first("SELECT v FROM ".DB_TABLEPRE."setting WHERE k='msg_switch_off'");
    	if($msg_switch_off == 1){
    		$m_url = "http://usercenter.5173esb.com/service/GetUserBindMobileForSc?UserId=".$author_id;
    		$m_rs = topen($m_url);
    		$m_result = base64_decode($m_rs);
    		if(!empty($m_result)){
    			$key = "KFYTH";
    			$clientIP = $_SERVER["SERVER_ADDR"];
    			$category = "7028";
    			$mobile = $m_result;
    			$msg_content = $this->db->result_first("SELECT v FROM ".DB_TABLEPRE."setting WHERE k='msg_content'");
    			$content = urlencode($msg_content);
    			$sign = md5($key.$clientIP);
    			$url = vsprintf("http://mobile.5173.com/MobileAPI/SendSingleMessage?m_sign=%s&m_clientIP=%s&category=%s&mobile=%s&content=%s",array($sign,$clientIP,$category,$mobile,$content));
    			$rs = topen($url);
    			$result = json_decode($rs,true);
    			if($result['ResultNo'] != 0){   				
    				send_AIC('http://scadmin.5173.com',$result['ResultDescription'],1,'手机短信接口');
    			}
    		}
    	}
    }
    //根据条件数组拼接查询条件
	public static function getSqlWhere($whereCondition)
	{
		foreach($whereCondition as $key => $value)
		{
			if($value=='')
			{
				unset($whereCondition[$key]);
			}
			else
			{
				$whereCondition[$key] = ' and '.$value;
			}
		}
		$where = implode(' ',$whereCondition);
		return $where;
	}
	//根据数组拼接group by
	public static function getGroupBy($group_fields)
	{
		$return = "";
		if(is_array($group_fields))
		{
			$return = implode(",",$group_fields);
			$return = " group by ".$return;
		}
		return $return;
	}
	//根据数组拼接sql查询字段列表，含别名
    //别名＝>列运算符
	public static function getSqlFields($fields)
	{
		foreach($fields as $key => $value)
		{
			if(!is_int($key))
			{
				$fields[$key] = $value." as ".$key;
			}
		}
		$fields = implode(',',$fields);
		return $fields;
	}
	//根据两个时间计算时间差,并据此生成文本串
	public static function timeLagToText($StartTime,$EndTime)
	{
		$Lag = $EndTime-$StartTime;
		if($Lag < 0)
		{
		    $prefix = "时光倒流 ";
		    $suffix = "前";    
		}
		elseif($Lag > 0)
		{
            $prefix = "" ;
            $suffix = "后";   
        }
        else
        {
            $prefix = "" ;
            $suffix = "";              
        }
        $Lag = abs($Lag);
        if($Lag >= 3600*24*365)
        {
            $text = intval($Lag/(3600*24*365))."年";    
        }
        elseif($Lag >= 3600*24*30)
        {
            $text = intval($Lag/(3600*24*30))."个月";      
        }
        elseif ($Lag >= 3600*24*7) 
        {
            $text = intval($Lag/(3600*24*7))."周";
        }
        elseif ($Lag >= 3600*24)
        {
            $text = intval($Lag/(3600*24))."天";
        }  
        elseif ($Lag >= 3600)
        {
            $text = intval($Lag/(3600))."小时";
        }
        elseif ($Lag >= 60)
        {
            $text = intval($Lag/(60))."分钟";
        }
        elseif ($Lag >= 1)
        {
            $text = intval($Lag)."秒";
        }
        else 
        {
            $text = "当场";    
        }                
		return $prefix." ".$text.$suffix;
	}
	//根据时间生成文本串
	public static function timeToText($time)
	{
		if(date("Y-m-d",$time)==date("Y-m-d",time()))
		{
			$text = date("H:i",$time);
		}
		elseif(date("Y-m-d",$time+86400)==date("Y-m-d",time()))
		{
			$text = "昨天  ".date("H:i",$time);
		}
		else
		{
			$text = date("Y.m.d H:i",$time);
		}
		return $text;
	}

    function getLocalIP() 
    {
        $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
        //获取操作系统为win2000/xp、win7的本机IP真实地址
        exec("ipconfig", $out, $stats);
        if (!empty($out)) 
        {
            foreach ($out AS $row) 
            {
                if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) 
                {
                    $tmpIp = explode(":", $row);
                    if (preg_match($preg, trim($tmpIp[1]))) 
                    {
                        return trim($tmpIp[1]);
                    }
                }
            }
        }
    //获取操作系统为linux类型的本机IP真实地址
        exec("ifconfig", $out, $stats);
        if (!empty($out)) 
        {
            if (isset($out[1]) && strstr($out[1], 'addr:')) 
            {
                $tmpArray = explode(":", $out[1]);
                $tmpIp = explode(" ", $tmpArray[1]);
                if (preg_match($preg, trim($tmpIp[0]))) 
                {
                    return trim($tmpIp[0]);
                }
            }
        }
        return '127.0.0.1';
    }
	// 取md5用户名的后两位,作为表后缀
	function getSuffixTable($string = '', $length=2)
	{
		if( $string == '')
		{
			return '';
		}
		else
		{
			return '_'.substr(md5($string),-$length);
		}
	}
	function keyWordCheck($string)
	{
		/* $cache_data = $this->cache->get('KeyWords');
		var_dump($cache_data);
		if(false !== $cache_data)
		{
			$keyWordArr = $cache_data;	//如果存在缓存，则读取缓存
			echo 'a';
		}
		else
		{
			echo 'b';
			$keyWordArr = include TIPASK_ROOT.'/lib/KeyWords.php';
			$this->cache->set('KeyWords',$keyWordArr,2592000);//写入缓存，缓存时间为30天
		} */
		$keyWordArr = include TIPASK_ROOT.'/lib/KeyWords.php';
		$rsult =  str_replace($keyWordArr,'******',$string);
		return $rsult;
	}
	/**
	 * 封装命令行的参数,返回数组
	 * @return array
	 */
	function getCmdArgv()
	{
		//根据传入字符串封装参数
		$argvStr = trim($_SERVER["argv"][1],"\"");
		
		// 只有一个参数,返回该参数
		if(strpos($argvStr,'=') === false)
		{
			$argv['operator'] = $argvStr;
		}
		else
		{
			// 有多个参数,返回 数组
			$argvArr = explode('&',$argvStr);
			foreach($argvArr as $v)
			{
				$tmp = explode('=',$v);
				$argv[$tmp[0]] = $tmp[1];
			}
		}
		return $argv;
	}
	//对输入的数组p_sign计算得出sign
	function check_sign($arr,$p_sign)
	{
		foreach($arr as $key => $value)
		{
			if((strlen(trim($value))==0)||(($value==0)&&(is_numeric($value))))
			{
				unset($arr[$key]);	
			}
		}		
		ksort($arr);
		$text_arr = implode("|",$arr);
		$text_arr = $text_arr."|".$p_sign;
 		$sign = md5($text_arr);
		return $sign;
	}
}

?>

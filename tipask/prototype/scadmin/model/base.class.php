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
        $this->ask_config = new config();    
        $this->onlineConfig = getConfig(); // 获取配置文件
        $this->check_login();
        $this->sys_error_handle();
    }

    function init_db() {
        $this->db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    }
	public function getDbTable($table_name = null,$db = null, $key = null)
	{
		require TIPASK_ROOT . "/db_config/table.php";
		if(isset($table[$table_name]))
		{
			return $table[$table_name]['db'].".".$table_name;
		}
		else
		{
			return false;
		}
	}

    /* 一旦setting的缓存文件读取失败，则更新所有cache */

    function init_cache() {
        global $setting;
        $this->cache = new CacheMemcache(array('db'=>$this->db));
        $setting = $this->setting = $this->cache->load('setting');
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
		    		$this->ask_front_id = $result['Ticket']['UserID'];	    		
		    		$this->ask_front_name = $result['Ticket']['UserName'];		    		
		    	}else{		    		
		    		send_AIC('http://sc.5173.com',$rs,1,'前台登陆接口');
		    	}
			} 
    	}                        	
    }
        
    /*获取全部游戏*/
    function get_all_game(){
    	$cache_data = $this->cache->get('all_game_new');//如果存在缓存，则读取缓存
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
		$p = $this->Pinyin($v['Name'],1);
		   if($p=="")
		   {
				$p = $this->Pinyin($v['Name']);
		   }
			$arr[$v['Id']] = strtoupper(substr($p,0,1)).'-'.$v['Name'];
		}
		if(!empty($arr)) $this->cache->set('all_game_new',$arr,7*24*3600);//写入缓存，缓存有有效期为7天
		return $arr;
    }
    
    //更新Solr服务器
    function set_search($data=array()) {
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
    	
    	$solr = new Apache_Solr_Service( $this->onlineConfig['SOLR_SERVER'], $this->onlineConfig['SOLR_PORT'], '/solr' );
    	foreach($arr as $key => $val) {
    		$response=$solr->search( $key.':'.$val , $startindex, $pagesize );
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
		$CommonConfig = require(dirname(dirname(dirname(__FILE__)))."/CommonConfig/commonConfig.php");
		$LogType = $CommonConfig['sys_log_arr'];
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
    	// if($qid == 0)
		// {
			// return false;
		// }
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


 
	function Pinyin($_String, $_Code='gb2312')
	{
		$_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
		"|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
		"cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
		"|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
		"|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
		"|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
		"|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
		"|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
		"|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
		"|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
		"|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
		"she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
		"tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
		"|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
		"|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
		"zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
		$_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
		"|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
		"|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
		"|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
		"|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
		"|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
		"|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
		"|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
		"|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
		"|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
		"|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
		"|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
		"|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
		"|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
		"|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
		"|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
		"|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
		"|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
		"|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
		"|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
		"|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
		"|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
		"|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
		"|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
		"|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
		"|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
		"|-10270|-10262|-10260|-10256|-10254";
		$_TDataKey = explode('|', $_DataKey);
		$_TDataValue = explode('|', $_DataValue);
		$_Data = (PHP_VERSION>='5.0') ? array_combine($_TDataKey, $_TDataValue) : _Array_Combine($_TDataKey, $_TDataValue);
		arsort($_Data);
		reset($_Data);
		if($_Code != 'gb2312') $_String = $this->_U2_Utf8_Gb($_String);
		$_Res = '';
		for($i=0; $i<strlen($_String); $i++)
		{
			$_P = ord(substr($_String, $i, 1));
			if($_P>160)
			{ 
				$_Q = ord(substr($_String, ++$i, 1)); 
				$_P = $_P*256 + $_Q - 65536; 
			}
				$_Res .= $this->_Pinyin($_P, $_Data);
		}
		return $_Res;
		//return preg_replace("/[^a-z0-9]*/", '', $_Res);
	}
	function _Pinyin($_Num, $_Data)
	{
		if ($_Num>0 && $_Num<160 ) return chr($_Num);
		elseif($_Num<-20319 || $_Num>-10247) return '';
		else {
		foreach($_Data as $k=>$v){ if($v<=$_Num) break; }
		return $k;
	}
	}
	function _U2_Utf8_Gb($_C)
	{
		$_String = '';
		if($_C < 0x80) $_String .= $_C;
		elseif($_C < 0x800)
		{
		$_String .= chr(0xC0 | $_C>>6);
		$_String .= chr(0x80 | $_C & 0x3F);
		}elseif($_C < 0x10000){
		$_String .= chr(0xE0 | $_C>>12);
		$_String .= chr(0x80 | $_C>>6 & 0x3F);
		$_String .= chr(0x80 | $_C & 0x3F);
		} elseif($_C < 0x200000) {
		$_String .= chr(0xF0 | $_C>>18);
		$_String .= chr(0x80 | $_C>>12 & 0x3F);
		$_String .= chr(0x80 | $_C>>6 & 0x3F);
		$_String .= chr(0x80 | $_C & 0x3F);
		}
		return iconv('UTF-8', 'GB2312', $_String);
	}
	function _Array_Combine($_Arr1, $_Arr2)
	{
		for($i=0; $i<count($_Arr1); $i++) $_Res[$_Arr1[$i]] = $_Arr2[$i];
		return $_Res;
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
	function ip2long($ip)
	{
		list($a, $b, $c, $d) = explode('.', $ip);
		$ip_long = (($a * 256 + $b) * 256 + $c) * 256 + $d;
		return $ip_long;
	}
}

?>

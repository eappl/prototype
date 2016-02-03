<?php

class config {
    /**撤销状态*/
    const REVOCATION_START          = 0; // 已开启
    const PERMISSION_END            = 1; // 已撤销
    
    /**问题状态*/
    const QUESTION_WAIT             = 1; //  等待处理
    const QUESTION_ASSESS           = 2; //  等待评价
    const QUESTION_END              = 3; //  已结束
    
    /**评价状态*/
    const ASSESS_UNKNOWN		= 0; // 未评价
    const ASSESS_YES            = 1; //  满意
    const ASSESS_NO             = 2; //  不满意
    
    /**帮助状态*/
    const HELP_NO              = 0; //  没帮助
    const HELP_YES             = 1; //  有帮助
    
    /**忙碌状态*/
    const BUSY_ONE              = 0; //  空闲
    const BUSY_TWO              = 1; //  忙碌
    
    /**处理状态*/
    const HANDLE_ONE            = 0; //  非处理人员
    const HANDLE_TWO            = 1; //  处理人员
    
    /**接单类型*/
    const HAWB_ONE              = 1; //  咨询
    
    /**协助处理状态*/
    const HELP_ONE				= 0; // 否
    const HELP_TWO			    = 1; // 是
    
    /**协助处理是否逾期*/
    const OVERDUE_ONE			= 0; // 未逾期
    const OVERDUE_TWO			= 1; // 已逾期
    
    /**协助处理 协助状态*/
    const HELP_STATUS_ONE		= 0; // 授理中
    const HELP_STATUS_TWO		= 1; // 已反馈
    const HELP_STATUS_THREE		= 2; // 已撤销
    /**问题处理状态*/
    const QUE_STATUS_ONE 		= 0; // 未处理
    const QUE_STATUS_TWO 		= 1; // 已处理
    
    /**后台登陆接口相关配置*/
    const DOMAIN_NAME			 = 'cis.5173.com';//域名
    const ADMIN_COOKIE			 = 'Bk5173Admin';//cookie的名字
    
    /**passport接口相关配置*/
    const FRONT_LOGIN_DOMAIN	 = 'passport.5173.com';//域名
    const FRONT_COOKIE			 = '_5173auth';//cookie的名字
    
    /**投诉接口sign配置*/
    const TS_SIGN	 = 'YT698DSFGHJKLTYUIOPsajasfkapimfaefakfaskfafifjS';
    
    /**是否在班**/
    const IS_ONJOB_ONE = 1; // 在班
    const IS_ONJOB_TWO = 0; // 不在班
    
    /**问题是否协助处理**/
    const Q_help_Sone = 0;    // 不是
    const Q_help_Stwo = 1;   // 是协助处理
    
    /**域名配置*/
    const FRONT_DOMAIN	 = 'sc.5173.com';//前台域名
    const ADMIN_DOMAIN	 = 'scadmin.5173.com';//后台域名
    
    /**投诉状态*/
    const COMPlAIN_STATUS_ONE   = 0;  // 客服处理中
    const COMPlAIN_STATUS_TWO   = 1;  //待确认
    const COMPlAIN_STATUS_THREE = 2;  //已撤销
    const COMPlAIN_STATUS_FOUR  = 3;  //处理结束
    const COMPlAIN_STATUS_FIVE  = 4; //处理中
    
    /**获取撤销状态*/
    function getRevocation(){
    	return array(
    	    self::REVOCATION_START => "已开启",
			self::PERMISSION_END   => "已撤销"
    	);
    }
    
    /**获取问题状态*/
    function getQuestion(){
    	return array(
    	    self::QUESTION_WAIT    => "等待处理",
			self::QUESTION_ASSESS  => "等待评价",
			self::QUESTION_END 	   => "已结束"
    	);
    }
    
    /**获取评价状态*/
    function getAssess(){
    	return array(
    		self::ASSESS_UNKNOWN => "未评价",
    	    self::ASSESS_NO 	 => "不满意",
			self::ASSESS_YES     => "满意"
    	);
    }
    
    /**获取帮助状态*/
    function getHelp(){
    	return array(
    	    self::HELP_NO 	=> "没帮助",
			self::HELP_YES  => "有帮助"
    	);
    }
    
    /**获取忙碌状态*/
    function getBusy(){
    	return array(
    	    self::BUSY_ONE 	=> "空闲",
			self::BUSY_TWO  => "忙碌"
    	);
    }
    
    /**获取处理状态*/
    function getHandle(){
    	return array(
    	    self::HANDLE_ONE  => "非处理人员",
			self::HANDLE_TWO  => "处理人员"
    	);
    }
    
    /**获取接单类型*/
    function getHawb(){
    	return array(
    	    self::HAWB_ONE 	=> "咨询"
    	);
    }
    
    /**获取协助处理状态*/
    function getAid(){
    	return array(
    			self::HELP_ONE 	=> "否",
    			self::HELP_TWO  => "是"
    	);
    }
    /**获取协助处理是否逾期**/
    function getOverdue(){
    	return array(
    			self::OVERDUE_ONE 	=> "未逾期",
    			self::OVERDUE_TWO   => "已逾期"
    	);
    }
    /**获取协助处理状态**/
    function helpStatus(){
    	return array(
    			self::HELP_STATUS_ONE 	=> "授理中",
    			self::HELP_STATUS_TWO   => "已回复",
    			self::HELP_STATUS_THREE => "已撤销"
    	);
    }
    /**获取是否在班**/
    function isonjob(){
    	return array(
    			self::IS_ONJOB_ONE => "是",
    			self::IS_ONJOB_TWO => "否"
    			);
    }
    /**获取问题是否协助处理**/
    function getHelpStatus(){
    	return array(
    			self::Q_help_Sone => "未协助",
    			self::Q_help_Stwo => "协助处理"
    	);
    }
    /**获取问题是否处理**/
    function getQueStatus(){
    	return array(
    			self::QUE_STATUS_ONE=>"未处理",
    			self::QUE_STATUS_TWO=>"已处理"
    			);
    }
    /**获取投诉状态**/
    function getComStatus(){
    	return array(
    		    self::COMPlAIN_STATUS_ONE=>"等待处理",
    			self::COMPlAIN_STATUS_FIVE=>"客服处理中",
    			self::COMPlAIN_STATUS_TWO=>"待确认",
    			self::COMPlAIN_STATUS_THREE=>"已撤销",
    			self::COMPlAIN_STATUS_FOUR=>"处理结束"
    			);
    }
    function getCallType(){
		return array(
    		    1=>"电话",
    			2=>"短信"
    			);
    }
    function getQuestionType(){
		return array(
		'ask'=>"咨询",
		'suggest'=>"建议",
		'complain'=>"投诉",
		'dustbin'=>"垃圾箱"
		); 
    }
    function getLogType(){
		$CommonConfig = require(dirname(dirname(dirname(__FILE__)))."/CommonConfig/commonConfig.php");
		return $CommonConfig['sys_log_arr'];		
    }
    
}

?>
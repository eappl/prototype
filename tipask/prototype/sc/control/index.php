<?php

!defined('IN_TIPASK') && exit('Access Denied');

class indexcontrol extends base {
	var $count=24;//标题字符长度
	function indexcontrol(& $get, & $post)
    {
		$this->base(& $get, & $post);
		$this->load("banner");
		$this->load("common_question");
		$this->load("tag");
		$this->load("question");
		$this->load("setting");
		$this->load("complain");
		$this->load("qtype");
		$this->load("category");
	}

	function ondefault()
   {
		$title       = '服务中心';
		$all_num     = $_ENV['question']->total_question();
		$left_game   = $_ENV['question']->get_question_game();
		//$banner_list = $_ENV['banner']->get_banner_list(true);
		$common_list = 	$_ENV['common_question']->get_common_list(true);
		$taglist     = $_ENV['tag']->get_tag();
		// 判断用户是否登录
		$url =  'http://' . config::FRONT_LOGIN_DOMAIN . '/?returnUrl=' . curPageURL();
		$imgType = intval($this->get[2]);
		$Qtype = intval($this->get[3]);
		if($imgType>5)
		{
			$imgType=0;
		}
		include template('index');
	}

	/**
	 * 最新
	 */
	function onajaxnew()
	{
		$page     = isset($this->post['page'])?intval($this->post['page']):1;
		$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;
		$pagesize = $this->setting['list_default'];

		$rownum  = $_ENV['question']->front_newQuestionRowNum($qtype);//返回记录总行数
		if(empty($rownum))
		{
			$newlist = array();
		}
		else
		{
			$quePage = $page == 0 ? 1 : $page;
			$totalPage = @ceil($rownum / $pagesize);//计算总页数
			$quePage > $totalPage  && $quePage = $totalPage;
			$startindex = ($quePage - 1) * $pagesize;

			$newlist=$_ENV['question']->front_hot_newquestion($qtype,$startindex,$pagesize);
			$questr 	= ajax_page($rownum, $pagesize, $quePage, "index/ajaxnew",$imgType,$qtype);
			$str = '';
			$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
			$allQtypeLiList = '';
			$str = '';
			if(!empty($allQtypeList))
			{
				foreach($allQtypeList as $value)
				{
					$qtypeComplain = unserialize($value['complain']);
					if(intval($qtypeComplain['visiable'])==1)
					{
						$allQtypeLiList .= "<li onclick=\"gotopage('?index/ajaxnew',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
					}
					if($value['name']=="申请理赔")
					{
						$allQtypeLiList .= "<li onclick=\"gotopage('?index/ajaxnew',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
					}
				}
			}
		}
		if(!empty($newlist))
		 {
		 	$str.= '<dl class="b_top">
                            <dd>
                              	<span class="titlenew1  t_cen">问题描述</span>
                              	<span class="titlenew2">
			 						<span class="listselect_type">问题类型</span>
		 							<ul class="list_select">'.$allQtypeLiList.'</ul>
		 						</span>
                                <span class="titlenew3 color_999">提问时间</span>
		 			            <span class="titlenew4 color_999">回复时间</span>
                                <span class="titlenew5">回复状态</span>
                                <span class="titlenew6">浏览量</span>
                            </dd>
                    </dl>';
			$str .= '<dl id="info'.$imgType.'">';
			foreach($newlist as $v)
			{
				
				$str .= '<dd>';
				if($v['categoryInfo']['question_type']=="complain")
				{
					$qtype = $_ENV['qtype']->GetQType($v['qtype']);
					
					$category = !empty($qtype['name']) ? $qtype['name']:'其他问题 ';
					if($v['status'] == 1 || $v['status'] == 3) // 投诉问题 状态 1,3 为已回复
					{
						$s_class =  '<span class="c_090">已解决</span>';
					}
					else if($v['status'] == 2)
					{
						$s_class = '<span>已撤销</span>';
					}
					else
					{
						$s_class = '<span class="c_f00">解决中</span>';
					}

					$url = '?question/ts_detail/';
				}
				else
				{
					$qtype = $_ENV['qtype']->GetQType($v['qtype']); // 非投诉问题 8大类
					$category = !empty($qtype['name']) ? $qtype['name']:'其他问题';
					if($v['status'] == 1)
					 {
						$s_class = '<span class="c_f00">回复中</span>';
					}
				    else
					{
						$s_class =  '<span class="c_090">已回复</span>';
					}
					$url = '?question/detail/';
				}
				if(mb_strlen($v["time"],'UTF-8')>9)
				{
					$classAskTime = 'titlenew3 line_h18';
				}
				else
				{
					$classAskTime = 'titlenew3';
				}
				$category = ajax_page_single($category, $pagesize, $quePage, "index/ajaxnew",$imgType,$v['qtype']);

				$str .= '<span class="titlenew1"><a href="'.$url.$v['id'].'/'.$imgType.'" target="_blank">'.cutstr($v['type'].$v['description'],$this->count,'..').'</a></span>';
				$str .= '<span class="titlenew2">'.$category.'</span>';
				$str .= '<span class="'.$classAskTime.'">'.$v["time"].'</span>';
				$str .= '<span class="titlenew4">'.$v["Atime"].'</span>';
				$str .= '<span class="titlenew5">' . $s_class.'</span>';
				$str .= '<span class="titlenew6">'.$v['views'].'</span>';
				$str .= '</dd>';
			}
			$str.='</dl>';
			$str .= '<div class="pagination">'.$questr.'</div>';
		}
		else
		{
			$str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
		}
		echo $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
	}

	//热门
	function onajaxhot()
	{
		$page     = isset($this->post['page'])?intval($this->post['page']):1;
		$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;
		$pagesize = $this->setting['list_default'];

		$rownum 	= $_ENV['question']->front_hotQuestionRowNum($qtype);//返回记录总行数
		if(empty($rownum))
		{
			$hotlist = array();
		}
		else
		{
			$quePage = $page == 0 ? 1 : $page;
			$totalPage  = @ceil($rownum / $pagesize);//计算总页数
			$quePage > $totalPage  && $quePage = $totalPage;
			$startindex = ($quePage - 1) * $pagesize;

			$hotlist = $_ENV['question']->front_hot_question($qtype,$startindex,$pagesize);
			$questr  = ajax_page($rownum, $pagesize, $quePage, "index/ajaxhot",$imgType,$qtype);
			$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
			$allQtypeLiList = '';
			$str = '';
			if(!empty($allQtypeList))
			{
				foreach($allQtypeList as $value)
				{
					$qtypeComplain = unserialize($value['complain']);
					if(intval($qtypeComplain['visiable'])==1)
					{
						$allQtypeLiList .= "<li onclick=\"gotopage('?index/ajaxhot',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
					}
					if($value['name']=="申请理赔")
					{
						$allQtypeLiList .= "<li onclick=\"gotopage('?index/ajaxhot',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
					}
				}
			}
		}

		if(!empty($hotlist))
		 {
		 	$str.= '<dl class="b_top">
                            <dd>
                              	<span class="titlenew1  t_cen">问题描述</span>
                              	<span class="titlenew2">
			 						<span class="listselect_type">问题类型</span>
		 							<ul class="list_select">'.$allQtypeLiList.'</ul>
		 						</span>
                                <span class="titlenew3 color_999">提问时间</span>
		 			            <span class="titlenew4 color_999">回复时间</span>
                                <span class="titlenew5">回复状态</span>
                                <span class="titlenew6">浏览量</span>
                            </dd>
                    </dl>';
			$str .= '<dl id="info'.$imgType.'">';
			foreach($hotlist as $v)
			{
				$qtype = $_ENV['qtype']->GetQType($v['qtype']);
				$category = !empty($qtype['name']) ? $qtype['name']:'其他问题';
				$str .= '<dd>';
				if($v['status']==1) // 未回复 1
				{
					$s_class =  '<span class="c_f00">回复中</span>';
				}
				else
				{
					$s_class =  '<span class="c_090">已回复</span>';
				}
				if(mb_strlen($v["time"],'UTF-8')>9)
				{
					$classAskTime = 'titlenew3 line_h18';
				}
				else
				{
					$classAskTime = 'titlenew3';
				}
				$category = ajax_page_single($category, $pagesize, $quePage, "index/ajaxhot",$imgType,$v['qtype']);
				$str .= '<span class="titlenew1"><a href="?question/detail/'. $v['id'].'/'.$imgType.'" target="_blank">'.cutstr($v['type'].$v['description'],$this->count,'..').'</a></span>';
    			$str .= '<span class="titlenew2">'.$category.'</span>';
				$str .= '<span class="'.$classAskTime.'">'.$v["time"].'</span>';
				$str .= '<span class="titlenew4">'.$v["Atime"].'</span>';
				$str .= '<span class="titlenew5">' .$s_class.'</span>';
				$str .= '<span class="titlenew6">'.$v['views'].'</span>';
				$str .= '</dd>';
			}
			$str.='</dl>';
			$str .= '<div class="pagination">'.$questr.'</div>';
		}
		else
		{
			$str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
		}
		echo $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
	}
	function onajaxcomplain()
	{
		$page     = isset($this->post['page'])?intval($this->post['page']):1;
		$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;
		$pagesize = $this->setting['list_default'];
		$quePage = $page == 0 ? 1 : $page;
		$where 	= $_ENV['complain']->front_myComWhere('','',-1,0,$qtype,'month');
		$rownum 	= $_ENV['complain']->front_myComNum($where);
		if(empty($rownum))
		{
			$tsList = array();
		}
		else
		{
			$totalPage  = @ceil($rownum / $pagesize);
			$quePage > $totalPage  && $quePage = $totalPage;
			$startindex = ($quePage - 1) * $pagesize;
			$tsList 	=  $_ENV['complain']->front_myComList($where,$startindex, $pagesize);
			$questr 	= ajax_page($rownum, $pagesize, $quePage, "index/ajaxcomplain",$imgType,$qtype);
			$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
			$allQtypeLiList = '';
			$str = '';
			if(!empty($allQtypeList))
			{
				foreach($allQtypeList as $value)
				{
					$qtypeComplain = unserialize($value['complain']);
					if(intval($qtypeComplain['visiable'])==1)
					{
						$allQtypeLiList .= "<li onclick=\"gotopage('?index/ajaxcomplain',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
					}
				}
			}
		}

		if(!empty($tsList))
		{
			$str.= '<dl class="b_top">
                            <dd>
                              	<span class="titlenew1  t_cen">问题描述</span>
                              	<span class="titlenew2">
			 						<span class="listselect_type">问题类型</span>
		 							<ul class="list_select">'.$allQtypeLiList.'</ul>
		 						</span>
                                <span class="titlenew3 color_999">提问时间</span>
		 			            <span class="titlenew4 color_999">回复时间</span>
                                <span class="titlenew5">回复状态</span>
                                <span class="titlenew6">浏览量</span>
                            </dd>
                    </dl>';
			$str .= '<dl id="info'.$imgType.'">';
			foreach($tsList as $v)
			{
				$time  = !empty($v['time']) ?$this->timeToText($v['time']) :'';
				if($v['time'] == $v['atime'])
				{
					$atime = '-';
				}
				else
				{
					$atime = !empty($v['atime'])?$this->timeLagToText($v['time'],$v['atime']):'-';
				}
				
				$qtype = $_ENV['qtype']->GetQType($v['qtype']);				

				$category = !empty($qtype['name']) ? $qtype['name']:'其他问题 ';
				if($v['status'] == 1 || $v['status'] == 3) // 已回答
				{
					$s_class =  '<span class="c_090">已解决</span>';
				}
				else if($v['status'] == 2)
				{
					$s_class = '<span>已撤销</span>';
				}
				else
				{
					$s_class = '<span class="c_f00">解决中</span>'; // 未回答
				}
				if(mb_strlen($time,'UTF-8')>9)
				{
					$classAskTime = 'titlenew3 line_h18';
				}
				else
				{
					$classAskTime = 'titlenew3';
				}
				$str .= '<dd>';
				$category = ajax_page_single($category, $pagesize, $quePage, "index/ajaxcomplain",$imgType,$qtype['id']);

				$str .= '<span class="titlenew1"><a href="?question/ts_detail/'. $v['id'].'/'.$imgType.'" target="_blank">'.cutstr('[投诉] '.$v['description'],$this->count,'..').'</a></span>';
				$str .= '<span class="titlenew2">'.$category.'</span>';
				$str .= '<span class="'.$classAskTime.'">'.$time.'</span>';
				$str .= '<span class="titlenew4">'.$atime.'</span>';
				$str .= '<span class="titlenew5">' .$s_class. '</span>';
				$str .= '<span class="titlenew6">'.$v['view'].'</span>';
				$str .= '</dd>';
			}
			$str.='</dl>';
			$str .= '<div class="pagination">'.$questr.'</div>';
		}
		else
		{
			$str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
		}
		echo $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
	}
	//咨询,建议列表

	/**
	 * $imgType为 2咨询，为4建议
	 */
	function onajaxask()
	{
		$page     = isset($this->post['page'])?intval($this->post['page']):1;
		$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;

		$pagesize = $this->setting['list_default'];
		$categoryType = ''; // 获取咨询 ，建议类型 1 咨询，2建议
		if($imgType == 2)
		{
			$categoryType = 1; // 咨询类型
			$typeCatogary = '[咨询] ';
		}
		else if($imgType == 4)
		{
			$categoryType = 2; // 建议类型
			$typeCatogary = '[建议] ';
		}
		$cidType   = $_ENV['question']->getType($categoryType);
		$where  = $_ENV['question']->front_getWhere($cidType,"",$qtype,'month');
		$rownum = $_ENV['question']->front_getNum($where);
		if(empty($rownum))
		{
			$question_list = array();
		}
		else
		{
			$quePage = $page == 0 ? 1 : $page;
			$totalPage  = @ceil($rownum / $pagesize);
			$quePage > $totalPage  && $quePage = $totalPage;
			$startindex = ($quePage - 1) * $pagesize;

			$question_list  = $_ENV['question']->front_get_list($where,$startindex, $pagesize,1);
			$questr = ajax_page($rownum, $pagesize, $quePage, "index/ajaxask",$imgType,$qtype);
			$str = '';
			$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
			$allQtypeLiList = '';
			$str = '';
			if(!empty($allQtypeList))
			{
				foreach($allQtypeList as $value)
				{
					$qtypeComplain = unserialize($value['complain']);
					if(intval($qtypeComplain['visiable'])==1)
					{
						$allQtypeLiList .= "<li onclick=\"gotopage('?index/ajaxask',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
					}
					if($value['name']=="申请理赔")
					{
						$allQtypeLiList .= "<li onclick=\"gotopage('?index/ajaxask',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
					}
				}
			}
		}

		if(!empty($question_list))
		{
			$str.= '<dl class="b_top">
                            <dd>
                              	<span class="titlenew1  t_cen">问题描述</span>
                              	<span class="titlenew2">
			 						<span class="listselect_type">问题类型</span>
		 							<ul class="list_select">'.$allQtypeLiList.'</ul>
		 						</span>
                                <span class="titlenew3 color_999">提问时间</span>
		 			            <span class="titlenew4 color_999">回复时间</span>
                                <span class="titlenew5">回复状态</span>
                                <span class="titlenew6">浏览量</span>
                            </dd>
                    </dl>';
			$str .= '<dl id="info'.$imgType.'">';

			foreach($question_list as $key => $QueList)
			{
				$qtype = $_ENV['qtype']->GetQType($QueList['qtype']);
				if(!empty($qtype))
				{
					$category = $qtype['name'];
				}
				else
				{
					$category = '其他问题 ';
				}
				if($QueList['status']==1) // 未回复 1
				{
					$s_class =  '<span class="c_f00">回复中</span>';
				}
				else
				{
					$s_class =  '<span class="c_090">已回复</span>';
				}
				if(mb_strlen($QueList['time'],'UTF-8')>9)
				{
					$classAskTime = 'titlenew3 line_h18';
				}
				else
				{
					$classAskTime = 'titlenew3';
				}
				$str .= '<dd>';
				$category = ajax_page_single($category, $pagesize, $quePage, "index/ajaxask",$imgType,$QueList['qtype']);

    			$str .='<span class="titlenew1"><a href="?question/detail/'.$QueList['id'].'/'.$imgType.'" target="_blank">'.cutstr($typeCatogary.$QueList['description'],$this->count,'..').'</a></span>';
    			$str .= '<span class="titlenew2">'.$category.'</span>';
    			$str .= '<span class="'.$classAskTime.'">'.$QueList['time'].'</span>';
    			$str .= '<span class="titlenew4">'.$QueList['Atime'].'</span>';
    			$str .= '<span class="titlenew5">'.$s_class.'</span>';
    			$str .= '<span class="titlenew6">'.$QueList['views'].'</span>';
    			$str .= '</dd>';
    		}
    		$str.='</dl>';
    		$str .='<div class="pagination">'.$questr.'</div>';
    	}
    	else
    	{
    	    $str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
    	}
    	echo $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
    }
    // 垃圾箱
    function onajaxdustbin()
    {
    	$page     = isset($this->post['page'])?intval($this->post['page']):1;
		$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;

		$pagesize = $this->setting['list_default'];
    	$dustbinCidType   = $_ENV['question']->getType(4); // 获取垃圾箱问题分类

    		$where  = $_ENV['question']->front_getWhere($dustbinCidType,"",$qtype,'month');
    		$rownum = $_ENV['question']->front_getNum($where);
    		if(empty($rownum))
    		{
    			$question_list = array();
    		}
    		else
    		{
    			$quePage = $page == 0 ? 1 : $page;
    			$totalPage  = @ceil($rownum / $pagesize);
    			$quePage > $totalPage  && $quePage = $totalPage;
    			$startindex 	= ($quePage - 1) * $pagesize;
    			$question_list  = $_ENV['question']->front_get_list($where,$startindex, $pagesize,2);
    			$questr	 = ajax_page($rownum, $pagesize, $quePage, "index/ajaxdustbin",$imgType,$qtype);
    			$str .= '';
    			$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
    			$allQtypeLiList = '';
    			$str = '';
    			if(!empty($allQtypeList))
    			{
    				foreach($allQtypeList as $value)
    				{
    					$qtypeComplain = unserialize($value['complain']);
    					if(intval($qtypeComplain['visiable'])==1)
    					{
    						$allQtypeLiList .= "<li onclick=\"gotopage('?index/ajaxdustbin',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
    					}
    					if($value['name']=="申请理赔")
    					{
    						$allQtypeLiList .= "<li onclick=\"gotopage('?index/ajaxdustbin',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
    					}
    				}
    			}
    		}

    		if(!empty($question_list))
    		{
    			$str.= '<dl class="b_top">
                            <dd>
                              	<span class="title7 t_cen">问题描述</span>
                              	<span class="title6 titlenew2">
    								<span class="listselect_type">问题类型</span>
		 							<ul class="list_select">'.$allQtypeLiList.'</ul>
		 						</span>
                                <span class="titlenew3 color_999">提问时间</span>
                                <span class="title4 color_999">原因</span>
                                <span class="titlenew6">浏览量</span>
                            </dd>
                    </dl>';
    			$str .= '<dl id="info'.$imgType.'">';

    			foreach($question_list as $key => $QueList)
    			{
    				$qtype = $_ENV['qtype']->GetQType($QueList['qtype']);
    				$category = !empty($qtype['name']) ? $qtype['name'] :'其他问题 ';
    				if(mb_strlen($QueList['time'],'UTF-8')>9)
    				{
    					$classAskTime = 'titlenew3 line_h18';
    				}
    				else
    				{
    					$classAskTime = 'titlenew3';
    				}
    				$str .='<dd>';
				    $category = ajax_page_single($category, $pagesize, $quePage, "index/ajaxdustbin",$imgType,$QueList['qtype']);

    				$str .= '<span class="title7"><a href="?question/detail/'.$QueList['id'].'/'.$imgType.'" target="_blank">'.cutstr('[垃圾箱] '.$QueList['description'],28,'..').'</a></span>';
    				$str .= '<span class="title6">'.$category.'</span>';
    				$str .= '<span class="'.$classAskTime.'">'.$QueList['time'].'</span>';
    				$str .= '<span class="title4">'.$QueList['comment'].'</span>';
    				$str .= '<span class="titlenew6">'.$QueList['views'].'</span>';
    				$str .= '</dd>';
    			}
    			$str .= '</dl>';
    			$str .= '<div class="pagination">'.$questr.'</div>';
    		}
    		else
    		{
    			$str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
    		}
    		echo $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
    }
    // 8统计大类统计量
    function onquestionTypeDetail()
    {
    	$all_num     = $_ENV['question']->total_question();
    	$left_game   = $_ENV['question']->get_question_game();
    	$common_list = 	$_ENV['common_question']->get_common_list(true);
    	$taglist     = $_ENV['tag']->get_tag();
    	$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):1;
    	$qtype = isset($this->get[2]) ? intval($this->get[2]) : 0;  // 8大类类型
    	$question_type = isset($this->get[3]) ? trim($this->get[3]) : '';  // ask,suggest,complain
    	$currentTypeArr = array('new'=>0,'hot'=>1,'ask'=>2,'complain'=>3,'suggest'=>4,'dustbin'=>5);
    	if(isset($currentTypeArr[$question_type]))
    	{
    		$currentType = $currentTypeArr[$question_type];
    	}
    	else
    	{
    		$currentType = 0;
    	}
    	//$question_type=='ask'?2:($question_type=='suggest'?4:($question_type=='complain'?3:0));
    	$todayOrMonth  = isset($this->get[4]) ? intval($this->get[4]) : 0; // 1选中今日，2本月
    	$ajaxUrl = array('ask'=>'?index/questionDetailajaxask',
    			'suggest'=>'?index/questionDetailajaxask',
    			'complain'=>'?index/questionDetailajaxComplain',
    			'hot'=>'?index/questionDetailajaxHot',
    			'new'=>'?index/questionDetailajaxNew',
    			'dustbin'=>'?index/questionDetailajaxDustbin'
    			);
    	
    	if(!array_key_exists($question_type, $ajaxUrl) || $qtype<=0 )
    	{
    		header("Location: http://sc.5173.com");
    	}
    	if($todayOrMonth !=0 && $todayOrMonth !=1)
    	{
    		$todayOrMonth =0;
    	}
		$question_type_list = $this->ask_config->getQuestionType();
		//$crumb = $question_type_list[$question_type]."量";
		//$title = '服务中心-'.$question_type_list[$question_type].'量详情';
		$title = '服务中心-统计详情';
		$crumb = '统计详情';
       	$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
    	$qtypeName = $allQtypeLiList = '';
    	if(!empty($allQtypeList))
    	{
    		foreach($allQtypeList as $value)
    		{
    			if($value['id'] == $qtype)
    			{
    				$qtypeName = $value['name'];
    			}
    			$qtypeComplain = unserialize($value['complain']);
    			if(intval($qtypeComplain['visiable'])==1)
    			{
    				if($value['id']==$qtype)
					{
						$allQtypeLiList .= "<li onclick=\"indexTypepage('{$ajaxUrl[$question_type]}',{$value['id']},'{$question_type}',this)\" style='display:none;'>{$value['name']}</li>";
					}
					else
					{
						$allQtypeLiList .= "<li onclick=\"indexTypepage('{$ajaxUrl[$question_type]}',{$value['id']},'{$question_type}',this)\">{$value['name']}</li>";
					}
    			}
    		}
    	}
    	include template('questionTypeDetail');
    }
    function onquestionDetailajaxask()
    {
    	$page     = isset($this->post['page'])?intval($this->post['page']):1;
    	$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
    	$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;
    	$question_type = isset($this->post['question_type'])?trim($this->post['question_type']):'';
    	
    	//$date = isset($this->post['date'])?trim($this->post['date']):'';
    	$date = 'month';// 取所有问题

    	$pagesize = $this->setting['list_default'];
    	$categoryType = ''; // 获取咨询 ，建议类型 1 咨询，2建议
    	if($question_type == 'ask')
    	{
    		$categoryType = 1; // 咨询类型
    		$typeCatogary = '[咨询] ';
    	}
    	else if($question_type == 'suggest')
    	{
    		$categoryType = 2; // 建议类型
    		$typeCatogary = '[建议] ';
    	}
    	$cidType   = $_ENV['question']->getType($categoryType);
    	$where  = $_ENV['question']->front_getWhere($cidType,"",$qtype,$date);
    	$rownum = $_ENV['question']->front_getNum($where);
    	if(empty($rownum))
    	{
    		$question_list = array();
    	}
    	else
    	{
    		$quePage = $page == 0 ? 1 : $page;
    		$totalPage  = @ceil($rownum / $pagesize);
    		$quePage > $totalPage  && $quePage = $totalPage;
    		$startindex = ($quePage - 1) * $pagesize;

    		$question_list  = $_ENV['question']->front_get_list($where,$startindex, $pagesize,1);
    		$questr = ajax_questionDetailpage($rownum, $pagesize, $quePage, "index/questionDetailajaxask",$imgType,$qtype,$question_type,$date);
    		$str = '';
    		$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
    		$allQtypeLiList = '';
    		$str = '';
    		if(!empty($allQtypeList))
    		{
    			foreach($allQtypeList as $value)
    			{
    				$qtypeComplain = unserialize($value['complain']);
    				if(intval($qtypeComplain['visiable'])==1)
    				{
    					$allQtypeLiList .= "<li onclick=\"questionTypepage('?index/questionDetailajaxask',1,{$imgType},{$value['id']},'{$question_type}','{$date}',this.innerHTML)\">{$value['name']}</li>";
    				}
    				if($value['name']=="申请理赔")
    				{
    					$allQtypeLiList .= "<li onclick=\"questionTypepage('?index/questionDetailajaxask',1,{$imgType},{$value['id']},'{$question_type}','{$date}',this.innerHTML)\">{$value['name']}</li>";
    				}
    			}
    		}
    	}
    	if(!empty($question_list))
    	{
    		$str.= '<dl class="b_top">
                            <dd>
                              	<span class="titlenew1  t_cen">问题描述</span>
                              	<span class="titlenew2">
			 						<span class="listselect_type">问题类型</span>
		 							<ul class="list_select">'.$allQtypeLiList.'</ul>
		 						</span>
                                <span class="titlenew3 color_999">提问时间</span>
		 			            <span class="titlenew4 color_999">回复时间</span>
                                <span class="titlenew5">回复状态</span>
                                <span class="titlenew6">浏览量</span>
                            </dd>
                    </dl>';
    		$str .= '<dl id="info'.$imgType.'">';

    		foreach($question_list as $key => $QueList)
    		{
    			$qtype = $_ENV['qtype']->GetQType($QueList['qtype']);
    			if(!empty($qtype))
    			{
    				$category = $qtype['name'];
    			}
    			else
    			{
    				$category = '其他问题 ';
    			}
    			if($QueList['status']==1) // 未回复 1
    			{
					$s_class =  '<span class="c_f00">回复中</span>';
    			}
    			else
    			{
					$s_class =  '<span class="c_090">已回复</span>';
    			}
    			if(mb_strlen($QueList['time'],'UTF-8')>9)
    			{
    				$classAskTime = 'titlenew3 line_h18';
    			}
    			else
    			{
    				$classAskTime = 'titlenew3';
    			}
    			$str .= '<dd>';

    			$str .='<span class="titlenew1"><a href="?question/detail/'.$QueList['id'].'/'.$imgType.'" target="_blank">'.cutstr($typeCatogary.$QueList['description'],$this->count,'..').'</a></span>';
    			$str .= '<span class="titlenew2">'.$category.'</span>';
    			$str .= '<span class="'.$classAskTime.'">'.$QueList['time'].'</span>';
    			$str .= '<span class="titlenew4">'.$QueList['Atime'].'</span>';
    			$str .= '<span class="titlenew5">'.$s_class.'</span>';
    			$str .= '<span class="titlenew6">'.$QueList['views'].'</span>';
    			$str .= '</dd>';
    		}
    		$str.='</dl>';
    		$str .='<div class="pagination">'.$questr.'</div>';
    	}
    	else
    	{
    		$str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
    	}
    	echo $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
    }
	function onquestionDetailajaxComplain()
	{
		$page     = isset($this->post['page'])?intval($this->post['page']):1;
		$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;
		$question_type = 'complain';//isset($this->post['question_type'])?trim($this->post['question_type']):'';
		
		//$date = isset($this->post['date'])?trim($this->post['date']):'';
		$date = 'month';// 取所有问题
		$pagesize = $this->setting['list_default'];
		$quePage = $page == 0 ? 1 : $page;
		$where 	= $_ENV['complain']->front_myComWhere('','',-1,0,$qtype,$date);
		$rownum 	= $_ENV['complain']->front_myComNum($where);
		if(empty($rownum))
		{
			$tsList = array();
		}
		else
		{
			$totalPage  = @ceil($rownum / $pagesize);
			$quePage > $totalPage  && $quePage = $totalPage;
			$startindex = ($quePage - 1) * $pagesize;
			$tsList 	=  $_ENV['complain']->front_myComList($where,$startindex, $pagesize);
			$questr 	= ajax_questionDetailpage($rownum, $pagesize, $quePage, "index/questionDetailajaxComplain",$imgType,$qtype,$question_type,$date);
			$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
			$allQtypeLiList = '';
			$str = '';
			if(!empty($allQtypeList))
			{
				foreach($allQtypeList as $value)
				{
					$qtypeComplain = unserialize($value['complain']);
					if(intval($qtypeComplain['visiable'])==1)
					{
						$allQtypeLiList .= "<li onclick=\"questionTypepage('?index/questionDetailajaxComplain',1,{$imgType},{$value['id']},'{$question_type}','{$date}',this.innerHTML)\">{$value['name']}</li>";
					}
				}
			}
		}
		if(!empty($tsList))
		{
			$str.= '<dl class="b_top">
                            <dd>
                              	<span class="titlenew1  t_cen">问题描述</span>
                              	<span class="titlenew2">
			 						<span class="listselect_type">问题类型</span>
		 							<ul class="list_select">'.$allQtypeLiList.'</ul>
		 						</span>
                                <span class="titlenew3 color_999">提问时间</span>
		 			            <span class="titlenew4 color_999">回复时间</span>
                                <span class="titlenew5">回复状态</span>
                                <span class="titlenew6">浏览量</span>
                            </dd>
                    </dl>';
			$str .= '<dl id="info'.$imgType.'">';
			foreach($tsList as $v)
			{
				$time  = !empty($v['time']) ?$this->timeToText($v['time']) :'';
				if($v['time'] == $v['atime'])
				{
					$atime = '-';
				}
				else
				{
					$atime = !empty($v['atime'])?$this->timeLagToText($v['time'],$v['atime']):'-';
				}
				$qtype = $_ENV['qtype']->GetQType($v['qtype']);
				

				$category = !empty($qtype['name']) ? $qtype['name']:'其他问题 ';
				if($v['status'] == 1 || $v['status'] == 3) // 已回答
				{
					$s_class =  '<span class="c_090">已解决</span>';
				}
				else if($v['status'] == 2)
				{
					$s_class = '<span>已撤销</span>';
				}
				else
				{
					$s_class = '<span class="c_f00">解决中</span>'; // 未回答
				}
				if(mb_strlen($time,'UTF-8')>9)
				{
					$classAskTime = 'titlenew3 line_h18';
				}
				else
				{
					$classAskTime = 'titlenew3';
				}
				$str .= '<dd>';

				$str .= '<span class="titlenew1"><a href="?question/ts_detail/'. $v['id'].'/'.$imgType.'" target="_blank">'.cutstr('[投诉] '.$v['description'],$this->count,'..').'</a></span>';
				$str .= '<span class="titlenew2">'.$category.'</span>';
				$str .= '<span class="'.$classAskTime.'">'.$time.'</span>';
				$str .= '<span class="titlenew4">'.$atime.'</span>';
				$str .= '<span class="titlenew5">' .$s_class. '</span>';
				$str .= '<span class="titlenew6">'.$v['view'].'</span>';
				$str .= '</dd>';
			}
			$str.='</dl>';
			$str .= '<div class="pagination">'.$questr.'</div>';
		}
		else
		{
			$str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
		}
		echo $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
	}
	// 首页，咨询、建议、投诉，今日本月统计量
	function onquestionQtypeNum()
	{
		$date  = isset($this->post['date'])?trim($this->post['date']):'today';
		$current = isset($this->post['current'])?intval($this->post['current']):'3'; // 当前选中的是咨询，建议，还是投诉,默认投诉
		$zxCurrent = $jyCurrent = $tsCurrent = $zxStyle = $jyStyle = $tsStyle = ''; // 咨询，建议，还是投诉默认不选中

		if($current == 1)
		{
			$zxCurrent = 'class="current"';
			$jyStyle   =  $tsStyle = 'style="display: none;"';
		}
		else if($current == 2)
		{
			$jyCurrent = 'class="current"';
			$zxStyle   =  $tsStyle = 'style="display: none;"';
		}
		else
		{
			$tsCurrent = 'class="current"';
			$zxStyle   =  $jyStyle = 'style="display: none;"';
		}
		
		/* if($date == 'month')
		{
			$todayOrMonth = '1'; // 统计量详情页选中本月选项
			$startTime = date('Y-m-01',time());
			$endTime   = date('Y-m-t',time());
		}
		else
		{
			$todayOrMonth = '0'; // 统计量详情页选中今日选项
			$startTime = $endTime = date('Y-m-d',time());
		} */
		$startTime = date('Y-m-d',mktime(0,0,0,date('m')-1,1));
		$endTime   = date('Y-m-d');
		// 咨询，投诉，建议，统计量
		$zx_qtypeDate = $_ENV['qtype']->getQuestionNumfront('ask',$startTime,$endTime);
		$jy_qtypeDate = $_ENV['qtype']->getQuestionNumfront('suggest',$startTime,$endTime);
		$ts_qtypeDate =	$_ENV['qtype']->getQuestionNumfront('complain',$startTime,$endTime);
		$str = '<ul class="quantity_type">
					<li id="dv2"'.$tsCurrent.'  onclick="currentClass1(3)">投诉量</li>
					<li id="dv1"'.$zxCurrent.'  onclick="currentClass1(1)">咨询量</li>
					<li id="dv3"'.$jyCurrent.'  onclick="currentClass1(2)">建议量</li>
				 </ul>';
		$str .= '<div id="dvcon1" class="type_question"'.$tsStyle.'><ul>';
		foreach($ts_qtypeDate as $value)
		{
			$qtypeComplain = unserialize($value['complain']);
			if(intval($qtypeComplain['visiable'])==1)
			{
				$num = isset($value['questions_num']) ? $value['questions_num'] : 0;
				$str .= '<li><a href="?index/questionTypeDetail/'.$value['id'].'/complain/'.$todayOrMonth.'"><span class="type_777">'.$value['name'].'</span><span class="num_red">'.$num.'</span></a></li>';
			}
		}
		$str  .= '</ul></div>';
		
        $str .= '<div id="dvcon2" class="type_question" '.$zxStyle.'><ul>';
		foreach($zx_qtypeDate as $value)
		{
			$num = isset($value['questions_num']) ? $value['questions_num'] : 0;
			$str .= '<li><a href="?index/questionTypeDetail/'.$value['id'].'/ask/'.$todayOrMonth.'"><span class="type_777">'.$value['name'].'</span><span class="num_red">'.$num.'</span></a></li>';
		}
		 $str .= '</ul></div>';

		 $str .= '<div id="dvcon3" class="type_question" '.$jyStyle.'><ul>';
		 foreach($jy_qtypeDate as $value)
		 {
		 	$num = isset($value['questions_num']) ? $value['questions_num'] : 0;
			$str .= '<li><a href="?index/questionTypeDetail/'.$value['id'].'/suggest/'.$todayOrMonth.'"><span class="type_777">'.$value['name'].'</span><span class="num_red">'.$num.'</span></a></li>';
         }
		 $str  .= '</ul></div>';
		 echo $str;
	}
	// 垃圾箱
	function onquestionDetailajaxDustbin()
	{
		$page     = isset($this->post['page'])?intval($this->post['page']):1;
		$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;
		$question_type = 'dustbin';//isset($this->post['question_type'])?trim($this->post['question_type']):'';
		//$date = isset($this->post['date'])?trim($this->post['date']):'';
		$date = 'month';// 取所有问题
		
		$pagesize = $this->setting['list_default'];
		$cidType = $_ENV['question']->getType(4); // 获取垃圾箱问题分类
		$where  = $_ENV['question']->front_getWhere($cidType,"",$qtype,$date);
		$rownum = $_ENV['question']->front_getNum($where);
		if(empty($rownum))
		{
			$question_list = array();
		}
		else
		{
			$quePage = $page == 0 ? 1 : $page;
			$totalPage  = @ceil($rownum / $pagesize);
			$quePage > $totalPage  && $quePage = $totalPage;
			$startindex 	= ($quePage - 1) * $pagesize;
			$question_list  = $_ENV['question']->front_get_list($where,$startindex, $pagesize,2);
			$questr = ajax_questionDetailpage($rownum, $pagesize, $quePage, "index/questionDetailajaxDustbin",$imgType,$qtype,$question_type,$date);
			
			$str .= '';
			$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
			$allQtypeLiList = '';
			$str = '';
			if(!empty($allQtypeList))
			{
				foreach($allQtypeList as $value)
				{
					$qtypeComplain = unserialize($value['complain']);
					if(intval($qtypeComplain['visiable'])==1)
					{
						$allQtypeLiList .= "<li onclick=\"questionTypepage('?index/questionDetailajaxDustbin',1,{$imgType},{$value['id']},'{$question_type}','{$date}',this.innerHTML)\">{$value['name']}</li>";
					}
					if($value['name']=="申请理赔")
					{
						$allQtypeLiList .= "<li onclick=\"questionTypepage('?index/questionDetailajaxDustbin',1,{$imgType},{$value['id']},'{$question_type}','{$date}',this.innerHTML)\">{$value['name']}</li>";
					}
				}
			}
		}
		
		if(!empty($question_list))
		{
			$str.= '<dl class="b_top">
                            <dd>
                              	<span class="title7 t_cen">问题描述</span>
                              	<span class="title6 titlenew2">
    								<span class="listselect_type">问题类型</span>
		 							<ul class="list_select">'.$allQtypeLiList.'</ul>
		 						</span>
                                <span class="titlenew3 color_999">提问时间</span>
                                <span class="title4 color_999">原因</span>
                                <span class="titlenew6">浏览量</span>
                            </dd>
                    </dl>';
			$str .= '<dl id="info'.$imgType.'">';
		
			foreach($question_list as $key => $QueList)
			{
				$qtype = $_ENV['qtype']->GetQType($QueList['qtype']);
				$category = !empty($qtype['name']) ? $qtype['name'] :'其他问题 ';
				if(mb_strlen($QueList['time'],'UTF-8')>9)
				{
					$classAskTime = 'titlenew3 line_h18';
				}
				else
				{
					$classAskTime = 'titlenew3';
				}
				$str .='<dd>';
				
				$str .= '<span class="title7"><a href="?question/detail/'.$QueList['id'].'/'.$imgType.'" target="_blank">'.cutstr('[垃圾箱] '.$QueList['description'],28,'..').'</a></span>';
				$str .= '<span class="title6">'.$category.'</span>';
				$str .= '<span class="'.$classAskTime.'">'.$QueList['time'].'</span>';
				$str .= '<span class="title4">'.$QueList['comment'].'</span>';
				$str .= '<span class="titlenew6">'.$QueList['views'].'</span>';
				$str .= '</dd>';
			}
			$str .= '</dl>';
			$str .= '<div class="pagination">'.$questr.'</div>';
		}
		else
		{
			$str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
		}
		echo $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
		
	}

	
	/**
	 * 最新
	 */
	function onquestionDetailajaxNew()
	{
		$page     = isset($this->post['page'])?intval($this->post['page']):1;
		$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;
		$question_type = 'new';//isset($this->post['question_type'])?trim($this->post['question_type']):'';
		//$date = isset($this->post['date'])?trim($this->post['date']):'';
		$date = 'all';// 取所有问题
		$pagesize = $this->setting['list_default'];	

		$rownum  = $_ENV['question']->front_newQuestionRowNum($qtype,$date);//返回记录总行数
		if(empty($rownum))
		{
			$newlist = array();
		}
		else
		{
			$quePage = $page == 0 ? 1 : $page;
			$totalPage = @ceil($rownum / $pagesize);//计算总页数
			$quePage > $totalPage  && $quePage = $totalPage;
			$startindex = ($quePage - 1) * $pagesize;

			$newlist=$_ENV['question']->front_hot_newquestion($qtype,$startindex,$pagesize,0,$date);
			$questr 	= ajax_questionDetailpage($rownum, $pagesize, $quePage, "index/questionDetailajaxNew",$imgType,$qtype,$question_type,$date);
			$str = '';
			$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
			$allQtypeLiList = '';
			$str = '';
			if(!empty($allQtypeList))
			{
				foreach($allQtypeList as $value)
				{
					$qtypeComplain = unserialize($value['complain']);
					if(intval($qtypeComplain['visiable'])==1)
					{
						$allQtypeLiList .= "<li onclick=\"questionTypepage('?index/questionDetailajaxNew',1,{$imgType},{$value['id']},'{$question_type}','{$date}',this.innerHTML)\">{$value['name']}</li>";
					}
					if($value['name']=="申请理赔")
					{
						$allQtypeLiList .= "<li onclick=\"questionTypepage('?index/questionDetailajaxNew',1,{$imgType},{$value['id']},'{$question_type}','{$date}',this.innerHTML)\">{$value['name']}</li>";
					}
				}
			}
		}
		if(!empty($newlist))
		 {
		 	$str.= '<dl class="b_top">
                            <dd>
                              	<span class="titlenew1  t_cen">问题描述</span>
                              	<span class="titlenew2">
			 						<span class="listselect_type">问题类型</span>
		 							<ul class="list_select">'.$allQtypeLiList.'</ul>
		 						</span>
                                <span class="titlenew3 color_999">提问时间</span>
		 			            <span class="titlenew4 color_999">回复时间</span>
                                <span class="titlenew5">回复状态</span>
                                <span class="titlenew6">浏览量</span>
                            </dd>
                    </dl>';
			$str .= '<dl id="info'.$imgType.'">';
			foreach($newlist as $v)
			{
				
				$str .= '<dd>';
				if($v['categoryInfo']['question_type']=="complain")
				{
					$qtype = $_ENV['qtype']->GetQType($v['qtype']);
					
					$category = !empty($qtype['name']) ? $qtype['name']:'其他问题 ';
					if($v['status'] == 1 || $v['status'] == 3) // 投诉问题 状态 1,3 为已回复
					{
						$s_class =  '<span class="c_090">已解决</span>';
					}
					else if($v['status'] == 2)
					{
						$s_class = '<span>已撤销</span>';
					}
					else
					{
						$s_class = '<span class="c_f00">解决中</span>';
					}

					$url = '?question/ts_detail/';
				}
				else
				{
					$qtype = $_ENV['qtype']->GetQType($v['qtype']); // 非投诉问题 8大类
					$category = !empty($qtype['name']) ? $qtype['name']:'其他问题';
					if($v['status'] == 1)
					 {
						$s_class = '<span class="c_f00">回复中</span>';
					}
				    else
					{
						$s_class =  '<span class="c_090">已回复</span>';
					}
					$url = '?question/detail/';
				}
				if(mb_strlen($v["time"],'UTF-8')>9)
				{
					$classAskTime = 'titlenew3 line_h18';
				}
				else
				{
					$classAskTime = 'titlenew3';
				}
				
				$str .= '<span class="titlenew1"><a href="'.$url.$v['id'].'/'.$imgType.'" target="_blank">'.cutstr($v['type'].$v['description'],$this->count,'..').'</a></span>';
				$str .= '<span class="titlenew2">'.$category.'</span>';
				$str .= '<span class="'.$classAskTime.'">'.$v["time"].'</span>';
				$str .= '<span class="titlenew4">'.$v["Atime"].'</span>';
				$str .= '<span class="titlenew5">' . $s_class.'</span>';
				$str .= '<span class="titlenew6">'.$v['views'].'</span>';
				$str .= '</dd>';
			}
			$str.='</dl>';
			$str .= '<div class="pagination">'.$questr.'</div>';
		}
		else
		{
			$str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
		}
		echo $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
	}

	//热门
	function onquestionDetailajaxHot()
	{
		$page     = isset($this->post['page'])?intval($this->post['page']):1;
    	$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
    	$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;
    	$question_type = 'hot';//isset($this->post['question_type'])?trim($this->post['question_type']):'';
    	//$date = isset($this->post['date'])?trim($this->post['date']):'';
    	$date = 'all';// 取所有问题
    	$pagesize = $this->setting['list_default'];

		$rownum = $_ENV['question']->front_hotQuestionRowNum($qtype,$date);//返回记录总行数
		if(empty($rownum))
		{
			$hotlist = array();
		}
		else
		{
			$quePage = $page == 0 ? 1 : $page;
			$totalPage  = @ceil($rownum / $pagesize);//计算总页数
			$quePage > $totalPage  && $quePage = $totalPage;
			$startindex = ($quePage - 1) * $pagesize;

			$hotlist = $_ENV['question']->front_hot_question($qtype,$startindex,$pagesize,$date);
			$questr 	= ajax_questionDetailpage($rownum, $pagesize, $quePage, "index/questionDetailajaxHot",$imgType,$qtype,$question_type,$date);
			$allQtypeList   = $_ENV['qtype']->GetAllQType(1,'',0,1);
			$allQtypeLiList = '';
			$str = '';
			if(!empty($allQtypeList))
			{
				foreach($allQtypeList as $value)
				{
					$qtypeComplain = unserialize($value['complain']);
					if(intval($qtypeComplain['visiable'])==1)
					{
						$allQtypeLiList .= "<li onclick=\"questionTypepage('?index/questionDetailajaxHot',1,{$imgType},{$value['id']},'{$question_type}','{$date}',this.innerHTML)\">{$value['name']}</li>";
					}
					if($value['name']=="申请理赔")
					{
						$allQtypeLiList .= "<li onclick=\"questionTypepage('?index/questionDetailajaxHot',1,{$imgType},{$value['id']},'{$question_type}','{$date}',this.innerHTML)\">{$value['name']}</li>";
					}
				}
			}
		}

		if(!empty($hotlist))
		 {
		 	$str.= '<dl class="b_top">
                            <dd>
                              	<span class="titlenew1  t_cen">问题描述</span>
                              	<span class="titlenew2">
			 						<span class="listselect_type">问题类型</span>
		 							<ul class="list_select">'.$allQtypeLiList.'</ul>
		 						</span>
                                <span class="titlenew3 color_999">提问时间</span>
		 			            <span class="titlenew4 color_999">回复时间</span>
                                <span class="titlenew5">回复状态</span>
                                <span class="titlenew6">浏览量</span>
                            </dd>
                    </dl>';
			$str .= '<dl id="info'.$imgType.'">';
			foreach($hotlist as $v)
			{
				$qtype = $_ENV['qtype']->GetQType($v['qtype']);
				$category = !empty($qtype['name']) ? $qtype['name']:'其他问题';
				$str .= '<dd>';
				if($v['status']==1) // 未回复 1
				{
					$s_class =  '<span class="c_f00">回复中</span>';
				}
				else
				{
					$s_class =  '<span class="c_090">已回复</span>';
				}
				if(mb_strlen($v["time"],'UTF-8')>9)
				{
					$classAskTime = 'titlenew3 line_h18';
				}
				else
				{
					$classAskTime = 'titlenew3';
				}
				$str .= '<span class="titlenew1"><a href="?question/detail/'. $v['id'].'/'.$imgType.'" target="_blank">'.cutstr($v['type'].$v['description'],$this->count,'..').'</a></span>';
    			$str .= '<span class="titlenew2">'.$category.'</span>';
				$str .= '<span class="'.$classAskTime.'">'.$v["time"].'</span>';
				$str .= '<span class="titlenew4">'.$v["Atime"].'</span>';
				$str .= '<span class="titlenew5">' .$s_class.'</span>';
				$str .= '<span class="titlenew6">'.$v['views'].'</span>';
				$str .= '</dd>';
			}
			$str.='</dl>';
			$str .= '<div class="pagination">'.$questr.'</div>';
		}
		else
		{
			$str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
		}
		echo $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
	}
}
?>
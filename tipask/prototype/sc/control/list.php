<?php

!defined('IN_TIPASK') && exit('Access Denied');

class listcontrol extends base
{
	var $count=24; //标题字符长度
    function listcontrol(& $get, & $post)
    {

        $this->base(& $get, & $post);
        $this->load("tag");
        $this->load("common_question");
        $this->load("question");
        $this->load("setting");
        $this->load("complain");
        $this->load("qtype");
		$this->load("category");
    }

    function ondefault()
     {
    	header("Cache-control: private");
    	$title = '服务中心 - 问题列表页';

    	$all_num = $_ENV['question']->total_question();
    	$left_game = $_ENV['question']->get_question_game(); // 问题分类
    	foreach($left_game as $val)
    	{
			$game_id_arr[] = "'".$val['gameid']."'";
		}
		$game = '';
        $game_id = isset($this->post['gameid'])?$this->post['gameid']:(isset($this->get[2])?$this->get[2]:'');
        if($game_id != '')
        {
        	if($game_id == 'other_games')
        	{
   				$game = " AND gameid<>'' AND gameid NOT IN (".implode(',',$game_id_arr).") ";
    		}
    		else
    		{
    			$game = " AND gameid='".$game_id."' ";
    		}
        }

    	$tag_arr = isset($this->post['tag'])?$this->post['tag']:(isset($this->get[3])?explode(",",$this->get[3]):array());
		foreach($tag_arr as $k => &$v)
		{
    		if($v == '')
    		{
    			unset($tag_arr[$k]);
    		}
    	}
    	$tag_str = implode(',',$tag_arr);
    	$tag_list = $_ENV['tag']->get_tag_tree($tag_arr,$game);
    	$rigth_game = $_ENV['question']->get_game_list($tag_arr,$game);
        //保存搜索条件
    	$search_conf = array();
    	$search_conf['tag'] = $tag_arr;
    	$search_conf['game'] = $game;
    	setcookie('search_conf',serialize($search_conf));

        $common_list = 	$_ENV['common_question']->get_common_list(true); // 常见问答
        $taglist = $_ENV['tag']->get_tag(); // 问题分类

        // 判断是用户是否登录
        $url =  'http://' . config::FRONT_LOGIN_DOMAIN . '/?returnUrl=' . curPageURL();
        $imgType = 0;
        include template('list');

    }
    // 最新
    function onajaxnew()
    {
    	$search_conf = unserialize(stripslashes($_COOKIE['search_conf']));
    	$tag_arr = $search_conf['tag'];
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;

    	$game = $search_conf['game'];
    	$rownum = $_ENV['question']->front_get_num($tag_arr,$game,"",$qtype);
    	if(empty($rownum))
    	{
    		$question_list = array();
    	}
    	else
    	{
    		$page     = isset($this->post['page'])?intval($this->post['page']):1;
    		$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
    		$pagesize = $this->setting['list_default'];
    		$quePage  = $page == 0 ? 1 : $page;

    		$totalPage  = @ceil($rownum / $pagesize);//计算总页数
    		$quePage > $totalPage  && $quePage = $totalPage;

    		$startindex = ($quePage - 1) * $pagesize;
    		$question_list = $_ENV['question']->front_question_show($qtype,$tag_arr,$game,false,$startindex, $pagesize);
    		$questr = ajax_page($rownum, $pagesize, $quePage, "list/ajaxnew",$imgType,$qtype);
    	}
    	echo $this->newOrhotToHtml($question_list, $questr, $imgType,$qtype,'list/ajaxnew');
    }
    //热门
    function onajaxhot()
    {
    	$page     = isset($this->post['page'])?intval($this->post['page']):1;
    	$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;
    	$pagesize = $this->setting['list_default'];
    	$quePage  = $page == 0 ? 1 : $page;

    	$rownum 	= $_ENV['question']->front_hotQuestionRowNum($qtype);//返回记录总行数
    	if(empty($rownum))
    	{
    		$hotlist = array();
    	}
    	else
    	{
    		@$quePage   = max(1, intval($page));
    		$totalPage  = @ceil($rownum / $pagesize);//计算总页数
    		$quePage > $totalPage  && $quePage = $totalPage;
    		$startindex = ($quePage - 1) * $pagesize;
    		$hotlist = $_ENV['question']->front_hot_question($qtype,$startindex,$pagesize);
    		$questr  = ajax_page($rownum, $pagesize, $quePage, "list/ajaxhot",$imgType,$qtype);
    	}

    	echo $this->newOrhotToHtml($hotlist, $questr, $imgType,$qtype,'list/ajaxhot');
    }
    /**
     *
     * @param  $queList 问题列表
     * @param  $questr 分页字符串
     * @param  $imgType 1热门问题 或者 0最新问题
     * @param  $qtype 8大类id
     * @return string
     */
    function newOrhotToHtml($queList, $questr, $imgType,$qtype,$urlType)
    {
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
					$allQtypeLiList .= "<li onclick=\"gotopage('?{$urlType}',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
				}
				if($value['name']=="申请理赔")
				{
					$allQtypeLiList .= "<li onclick=\"gotopage('?{$urlType}',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
				}
			}
		}
    	if(!empty($queList))
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

    		foreach($queList as $v)
    		{
    			$qtype = $_ENV['qtype']->GetQType($v['qtype']);
    			$category = !empty($qtype['name']) ? $qtype['name']:'其他问题 ';
    			if($v['status']==1) // 未回复 1
    			{
    				// changed
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
    			$str .= '<dd>';
				$category = ajax_page_single($category, $pagesize, $quePage,$urlType,$imgType,$v['qtype']);
    			$str .= '<span class="titlenew1"><a href="?question/detail/'. $v['id'].'/'.$imgType .'" target="_blank">'.cutstr($v['type'].$v['description'],$this->count,'..').'</a></span>';
    			$str .= '<span class="titlenew2">'.$category.'</span>';
    			$str .= '<span class="'.$classAskTime.'">'.$v["time"].'</span>';
    			$str .= '<span class="titlenew4">'.$v["Atime"].'</span>';
    			$str .= '<span class="titlenew5">' .$s_class.'</span>';
    			$str .= '<span class="titlenew6">'.$v['views'].'</span>';
    			$str .= '</dd>';
    		}
    		$str.'</dl>';
    		$str .= '<div class="pagination">'.$questr.'</div>';
    	}
    	else
    	{
    		$str .= '<div class="message_box"><s class="ico_warning_5"></s>啊噢，没有相关的问题噢，您可以尝试修改条件重新搜索。</div>';
    	}
    	return  $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
    }
    //咨询
    function onajaxask()
    {
    	$search_conf = unserialize(stripslashes($_COOKIE['search_conf']));
    	$tag_arr = $search_conf['tag'];
    	$game = $search_conf['game'];

    	$page     = isset($this->post['page'])?intval($this->post['page']):1;
    	$imgType  = isset($this->post['imgType'])?intval($this->post['imgType']):0;
		$qtype    = isset($this->post['qtype'])?intval($this->post['qtype']):0;
    	$pagesize = $this->setting['list_default'];
    	$quePage  = $page == 0 ? 1 : $page;

    	$categoryType = ''; // 获取咨询 ，建议类型 1 咨询，2建议
    	if($imgType == 2)
    	{
    		$categoryType = 1; // 咨询类型
    		$typeCatogary = '[咨询] ';
    	}
    	else if($imgType == 4)
    	{
    		$categoryType = 3; // 建议类型
    		$typeCatogary = '[建议] ';
    	}
    	$rownum = $_ENV['question']->front_get_num($tag_arr,$game,$categoryType,$qtype);
    	if(empty($rownum))
    	{
    		$question_list = array();
    	}
    	else
    	{
    		$totalPage = @ceil($rownum / $pagesize);
    		$quePage > $totalPage  && $quePage = $totalPage;
    		$startindex = ($quePage - 1) * $pagesize;

    		$question_list = $_ENV['question']->front_question_show($qtype,$tag_arr,$game,$categoryType,$startindex, $pagesize);
    		$questr = ajax_page($rownum, $pagesize, $quePage, "list/ajaxask",$imgType,$qtype);
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
    					$allQtypeLiList .= "<li onclick=\"gotopage('?list/ajaxask',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
    				}
    				if($value['name']=="申请理赔")
    				{
    					$allQtypeLiList .= "<li onclick=\"gotopage('?list/ajaxask',1,{$imgType},{$value['id']})\">{$value['name']}</li>";
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
    			$category = !empty($qtype['name']) ? $qtype['name']:'其他问题 ';
    			if($QueList['status']==1)
    			{
    				// changed
					$s_class = '<span class="c_f00">回复中</span>';
    			}
    			else
    			{
					$s_class = '<span class="c_090">已回复</span>';
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
				$category = ajax_page_single($category, $pagesize, $quePage, "list/ajaxask",$imgType,$QueList['qtype']);

    			$str .= '<span class="titlenew1"><a href="?question/detail/'.$QueList['id'].'/'.$imgType.'" target="_blank">'.cutstr($typeCatogary.$QueList['description'],$this->count,'..').'</a></span>';
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
    	echo  $str.'<script type="text/javascript">$("#info'.$imgType.' dd:even").attr("className", "bg_gray");</script>';
    }
}

?>
<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: table.php 15497 2014-12-18 09:13:55Z 334746 $
 */

$table = array();

//公告表
$table['broadcast']['db'] = 'tipask';
$table['broadcast']['num'] = 1;

//快捷链接表
$table['quicklink']['db'] = 'tipask';
$table['quicklink']['num'] = 1;

//咨询/建议表
$table['ask_question']['db'] = 'tipask';
$table['ask_question']['num'] = 1;

//咨询/建议表回答
$table['ask_answer']['db'] = 'tipask';
$table['ask_answer']['num'] = 1;

//客服信息
$table['ask_operator']['db'] = 'tipask';
$table['ask_operator']['num'] = 1;

//客服职位信息
$table['ask_post']['db'] = 'tipask';
$table['ask_post']['num'] = 1;

//客服接单数量
$table['ask_author_num']['db'] = 'tipask';
$table['ask_author_num']['num'] = 1;

//投诉表
$table['ask_complain']['db'] = 'tipask';
$table['ask_complain']['num'] = 1;

//投诉表回答
$table['ask_complain_answer']['db'] = 'tipask';
$table['ask_complain_answer']['num'] = 1;

//问题分类表
$table['ask_category']['db'] = 'tipask';
$table['ask_category']['num'] = 1;

//问题主分类表
$table['ask_qtype']['db'] = 'tipask';
$table['ask_qtype']['num'] = 1;

//问题分类数量汇总表
$table['ask_question_num']['db'] = 'tipask';
$table['ask_question_num']['num'] = 1;

//常用问题配置表
$table['ask_common_question']['db'] = 'tipask';
$table['ask_common_question']['num'] = 1;

//最近一段时间的交易订单数量表
$table['ask_order_count']['db'] = 'tipask';
$table['ask_order_count']['num'] = 1;

//禁言记录
$table['ask_gag']['db'] = 'tipask';
$table['ask_gag']['num'] = 1;

//投诉理由配置
$table['ask_complain_revoke_reason']['db'] = 'tipask';
$table['ask_complain_revoke_reason']['num'] = 1;

//投诉撤销队列
$table['ask_complain_revoke_queue']['db'] = 'tipask';
$table['ask_complain_revoke_queue']['num'] = 1;

//咨询/建议历史记录映射表
$table['ask_histroy_map']['db'] = 'tipask';
$table['ask_histroy_map']['num'] = 1;

//问题表历史_2013
$table['ask_question_h_2013']['db'] = 'tipask_h_2013';
$table['ask_question_h_2013']['num'] = 1;

//问题表历史_2014
$table['ask_question_h_2014']['db'] = 'tipask_h_2014';
$table['ask_question_h_2014']['num'] = 1;

//问题表历史_2015
$table['ask_question_h_2015']['db'] = 'tipask_h_2015';
$table['ask_question_h_2015']['num'] = 1;

//问题表回答历史_2013
$table['ask_answer_h_2013']['db'] = 'tipask_h_2013';
$table['ask_answer_h_2013']['num'] = 1;

//问题表回答历史_2014
$table['ask_answer_h_2014']['db'] = 'tipask_h_2014';
$table['ask_answer_h_2014']['num'] = 1;

//问题表回答历史_2015
$table['ask_answer_h_2015']['db'] = 'tipask_h_2015';
$table['ask_answer_h_2015']['num'] = 1;

//系统日志表
$table['ask_log']['db'] = 'tipask';
$table['ask_log']['num'] = 1;

//页面访问记录
$table['page_view_log']['db'] = 'tipask';
$table['page_view_log']['num'] = 1;

//页面访问配置
$table['page_view_config']['db'] = 'tipask';
$table['page_view_config']['num'] = 1;

return $table;

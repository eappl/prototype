<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: table.php 557 2012-12-27 12:35:33Z chenxiaodong $
 */

$table = array();

//菜单表
$table['ask_menu']['db'] = 'tipask';
$table['ask_menu']['num'] = 1;

//权限表
$table['ask_menu_permission']['db'] = 'tipask';
$table['ask_menu_permission']['num'] = 1;

//后台管理员表
$table['ask_operator']['db'] = 'tipask';
$table['ask_operator']['num'] = 1;

//后台管理员表接单缓存表
$table['ask_author_num']['db'] = 'tipask';
$table['ask_author_num']['num'] = 1;

//后台部门表
$table['ask_department']['db'] = 'tipask';
$table['ask_department']['num'] = 1;

//后台职位表
$table['ask_post']['db'] = 'tipask';
$table['ask_post']['num'] = 1;

//后台岗位表
$table['ask_job']['db'] = 'tipask';
$table['ask_job']['num'] = 1;

//后台岗位表
$table['ask_category']['db'] = 'tipask';
$table['ask_category']['num'] = 1;

//问题表
$table['ask_question']['db'] = 'tipask';
$table['ask_question']['num'] = 1;

//问题表历史_2013
$table['ask_question_h_2013']['db'] = 'tipask_h_2013';
$table['ask_question_h_2013']['num'] = 1;

//问题表历史_2014
$table['ask_question_h_2014']['db'] = 'tipask_h_2014';
$table['ask_question_h_2014']['num'] = 1;

//问题表历史_2015
$table['ask_question_h_2015']['db'] = 'tipask_h_2015';
$table['ask_question_h_2015']['num'] = 1;

//问题表回答
$table['ask_answer']['db'] = 'tipask';
$table['ask_answer']['num'] = 1;

//问题表回答历史
$table['ask_answer_h']['db'] = 'tipask';
$table['ask_answer_h']['num'] = 1;

//问题表回答历史_2013
$table['ask_answer_h_2013']['db'] = 'tipask_h_2013';
$table['ask_answer_h_2013']['num'] = 1;

//问题表回答历史_2014
$table['ask_answer_h_2014']['db'] = 'tipask_h_2014';
$table['ask_answer_h_2014']['num'] = 1;

//问题表回答历史_2015
$table['ask_answer_h_2015']['db'] = 'tipask_h_2015';
$table['ask_answer_h_2015']['num'] = 1;

//投诉问题表
$table['ask_complain']['db'] = 'tipask';
$table['ask_complain']['num'] = 1;

//资讯/建议问题浏览量缓存表
$table['views_question']['db'] = 'tipask';
$table['views_question']['num'] = 1;

//系统自检表
$table['self_test']['db'] = 'tipask';
$table['self_test']['num'] = 1;

//问题分类转换记录表
$table['ask_transform_log']['db'] = 'tipask';
$table['ask_transform_log']['num'] = 1;


// 绑定专属客服日志
$table['bind_log']['db'] = 'binding_service';
$table['bind_log']['num'] = 1;

// 专属客服订单待处理队列
$table['order_log']['db'] = 'binding_service';
$table['order_log']['num'] = 1;

// 专属客服订单待处理队列
$table['order_log_e']['db'] = 'binding_service';
$table['order_log_e']['num'] = 1;

// 禁言用户列表
$table['ask_gag']['db'] = 'tipask';
$table['ask_gag']['num'] = 1;

// 投诉同步状态表
$table['ask_sync']['db'] = 'tipask';
$table['ask_sync']['num'] = 1;

// 8大类
$table['ask_qtype']['db'] = 'tipask';
$table['ask_qtype']['num'] = 1;

//投诉回答表
$table['ask_complain_answer']['db'] = 'tipask';
$table['ask_complain_answer']['num'] = 1;

//投诉回答表
$table['ask_complain_revoke_reason']['db'] = 'tipask';
$table['ask_complain_revoke_reason']['num'] = 1;

// 协助处理表
$table['ask_help']['db'] = 'tipask';
$table['ask_help']['num'] = 1;

// 投诉问题与咨询问题互转记录 表(即将废弃)
$table['ask_transform_log']['db'] = 'tipask';
$table['ask_transform_log']['num'] = 1;

// 投诉问题与咨询问题互转记录 表
$table['ask_complain_transform_log']['db'] = 'tipask';
$table['ask_complain_transform_log']['num'] = 1;

// 常用配置表
$table['ask_setting']['db'] = 'tipask';
$table['ask_setting']['num'] = 1;

// 保险订单日志
$table['ask_BaoXianLog']['db'] = 'tipask';
$table['ask_BaoXianLog']['num'] = 1;

// 撤销重试队列
$table['ask_complain_revoke_queue']['db'] = 'tipask';
$table['ask_complain_revoke_queue']['num'] = 1;

// 历史记录映射表
$table['ask_histroy_map']['db'] = 'tipask';
$table['ask_histroy_map']['num'] = 1;

// 订单数量表
$table['ask_order_count']['db'] = 'tipask';
$table['ask_order_count']['num'] = 1;
return $table;

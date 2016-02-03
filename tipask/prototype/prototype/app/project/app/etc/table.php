<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: table.php 15195 2014-07-23 07:18:26Z 334746 $
 */

$table = array();

//用户等级和战斗力实力（从socke读取的文件写入数据库） user_chat_capacity
$table['error_log']['db'] = 'lm_config_global';
$table['error_log']['num'] = 1;

//用户等级和战斗力实力（从socke读取的文件写入数据库） user_chat_capacity
$table['readlog_last_update']['db'] = 'lm_config_global';
$table['readlog_last_update']['num'] = 1;

//用户等级和战斗力实力（从socke读取的文件写入数据库） user_chat_capacity
$table['user_character_rank']['db'] = 'lm_character';
$table['user_character_rank']['num'] = 1;

//游戏内货币类型
$table['game_money_type']['db'] = 'lm_config_game';
$table['game_money_type']['num'] = 1;

//游戏内货币类型(用于发放
$table['game_money']['db'] = 'lm_config_game';
$table['game_money']['num'] = 1;

//91卡订单
$table['ka91_order']['db'] = 'lm_order';
$table['ka91_order']['num'] = 1;

//检查数据库连接表 check_1
$table['check_1']['db'] = 'lm_order';
$table['check_1']['num'] = 1;

//检查数据库连接表 check_2
$table['check_2']['db'] = 'lm_loto';
$table['check_2']['num'] = 1;

#用户删除列表
$table['user_to_del']['db'] = 'lm_user';
$table['user_to_del']['num'] = 1;

#用户基础信息表
$table['user_info_base']['db'] = 'lm_user';
$table['user_info_base']['num'] = 1;

#用户角色信息表
$table['user_character']['db'] = 'lm_character';
$table['user_character']['num'] = 1;

#PVE塔日志用户分表
$table['pve_tower_log_user']['db'] = 'lm_character';
$table['pve_tower_log_user']['num'] = 1;

#用户角色信息表
$table['character_user']['db'] = 'lm_character';
$table['character_user']['num'] = 1;

#用户角色封停记录表
$table['character_freeze']['db'] = 'lm_user';
$table['character_freeze']['num'] = 1;

#用户角色踢下线记录表
$table['character_kick_off']['db'] = 'lm_user';
$table['character_kick_off']['num'] = 1;

#用户pvp记录
$table['pvp_log_user']['db'] = 'lm_character';
$table['pvp_log_user']['num'] = 1;

#用户角色封停记录表
$table['character_freeze_log']['db'] = 'lm_user_freeze_log';
$table['character_freeze_log']['num'] = 1;


#用户角色踢下线记录表
$table['character_kick_off_log']['db'] = 'lm_user_freeze_log';
$table['character_kick_off_log']['num'] = 1;

#用户注册日志表
$table['user_reg_log']['db'] = 'lm_user_reg_log';
$table['user_reg_log']['num'] = 1;

#用户联系信息信息表
$table['user_info_communication']['db'] = 'lm_user';
$table['user_info_communication']['num'] = 1;

#用户激活信息表
$table['user_info_active']['db'] = 'lm_user';
$table['user_info_active']['num'] = 1;

#用户邮件信息表
$table['user_mail']['db'] = 'lm_user';
$table['user_mail']['num'] = 1;

#用户邮件信息表
$table['user_security_answer']['db'] = 'lm_user';
$table['user_security_answer']['num'] = 1;

#用户登录日志表
$table['login_log']['db'] = 'lm_login_log';
$table['login_log']['num'] = 1;

#用户登录日志表
$table['online_log']['db'] = 'lm_login_log';
$table['online_log']['num'] = 1;

#用户首次登录日志表
$table['first_login']['db'] = 'lm_login_log';
$table['first_login']['num'] = 1;

#用户最后登录日志表
$table['last_logout']['db'] = 'lm_login_log';
$table['last_logout']['num'] = 1;

#用户登录日志表(日期维度)
$table['login_log_date']['db'] = 'lm_login_log';
$table['login_log_date']['num'] = 1;

#用户登录日志表（用户维度）
$table['login_log_user']['db'] = 'lm_login_log';
$table['login_log_user']['num'] = 1;

#激活码表
$table['active_code']['db'] = 'lm_active_code';
$table['active_code']['num'] = 1;

#激活码生成日志表
$table['active_gen_log']['db'] = 'lm_active_code';
$table['active_gen_log']['num'] = 1;

#激活码分配日志表
$table['active_asign_log']['db'] = 'lm_active_code';
$table['active_asign_log']['num'] = 1;

#礼包码表
$table['product_pack_code']['db'] = 'lm_product_pack_code';
$table['product_pack_code']['num'] = 1;

#礼包码分配计划任务表
$table['product_pack_code_asign_schedule']['db'] = 'lm_product_pack_code';
$table['product_pack_code_asign_schedule']['num'] = 1;

#礼包码生成日志表
$table['product_pack_code_gen_log']['db'] = 'lm_product_pack_code';
$table['product_pack_code_gen_log']['num'] = 1;

#道具发送记录（日期分表） 
$table['product_send_log']['db'] = 'lm_product_pack_code';
$table['product_send_log']['num'] = 1;


#道具发送记录（用户分表）
$table['product_send_log_user']['db'] = 'lm_character';
$table['product_send_log_user']['num'] = 1;


#道具发送队列 
$table['product_send_queue']['db'] = 'lm_product_pack_code';
$table['product_send_queue']['num'] = 1;

#系统密保问题列表
$table['security_answer']['db'] = 'lm_config_global';
$table['security_answer']['num'] = 1;

#财付通支付方式子列表
$table['tenpaylist']['db'] = 'lm_config_global';
$table['tenpaylist']['num'] = 1;

#新宽联支付方式子列表
$table['ka91list']['db'] = 'lm_config_global';
$table['ka91list']['num'] = 1;

#允许的邮箱后缀列表
$table['mail_sub_fix']['db'] = 'lm_config_global';
$table['mail_sub_fix']['num'] = 1;

#管理员
$table['config_manager']['db'] = 'lm_config_global';
$table['config_manager']['num'] = 1;

#管理员组
$table['config_group']['db'] = 'lm_config_global';
$table['config_group']['num'] = 1;

#菜单
$table['config_menu']['db'] = 'lm_config_global';
$table['config_menu']['num'] = 1;

#菜单权限
$table['config_menu_purview']['db'] = 'lm_config_global';
$table['config_menu_purview']['num'] = 1;

#合作商信息
$table['config_partner']['db'] = 'lm_config_global';
$table['config_partner']['num'] = 1;

#产品信息
$table['config_app']['db'] = 'lm_config_global';
$table['config_app']['num'] = 1;

#运营商信息
$table['partner']['db'] = 'lm_config_global';
$table['partner']['num'] = 1;

#运营游戏信息
$table['config_partner_app']['db'] = 'lm_config_global';
$table['config_partner_app']['num'] = 1;

#服务器信息
$table['config_server']['db'] = 'lm_config_global';
$table['config_server']['num'] = 1;

#运营游戏权限
$table['config_partner_permission']['db'] = 'lm_config_global';
$table['config_partner_permission']['num'] = 1;

#运营游戏权限2 selena 2013/3/4
$table['config_partner_permission2']['db'] = 'lm_config_global';
$table['config_partner_permission2']['num'] = 1;


#运营游戏权限(默认)
$table['config_partner_permission_default']['db'] = 'lm_config_global';
$table['config_partner_permission_default']['num'] = 1;


//支付渠道
$table['config_passage']['db'] = 'lm_config_global';
$table['config_passage']['num'] = 1;

//所在地区
$table['config_area']['db'] = 'lm_config_global';
$table['config_area']['num'] = 1;

//测试用户
$table['config_test_user']['db'] = 'lm_config_global';
$table['config_test_user']['num'] = 1;

//Socket队列类型配置表
$table['socket_type']['db'] = 'lm_config_global';
$table['socket_type']['num'] = 1;

//订单表
$table['lm_order']['db'] = 'lm_order';
$table['lm_order']['num'] = 1;


//用户首次付费记录表
$table['first_pay']['db'] = 'lm_order';
$table['first_pay']['num'] = 1;

//订单表(日期维度)
$table['lm_order_date']['db'] = 'lm_order';
$table['lm_order_date']['num'] = 1;

//订单表(用户维度)
$table['lm_order_user']['db'] = 'lm_order';
$table['lm_order_urser']['num'] = 1;


//支付记录表(日期维度)
$table['lm_pay_date']['db'] = 'lm_order';
$table['lm_pay_date']['num'] = 1;

//支付记录表(用户维度)
$table['lm_pay_user']['db'] = 'lm_order';
$table['lm_pay_urser']['num'] = 1;


//支付记录表
$table['lm_pay']['db'] = 'lm_order';
$table['lm_pay']['num'] = 1;

//兑换记录表
$table['lm_exchange']['db'] = 'lm_order';
$table['lm_exchange']['num'] = 1;

//兑换队列记录表
$table['exchange_queue']['db'] = 'lm_order';
$table['exchange_queue']['num'] = 1;

//游戏配置
$table['partner_app']['db'] = 'lm_config_global';
$table['partner_app']['num'] = 1;

//服务器
$table['config_server']['db'] = 'lm_config_global';
$table['config_server']['num'] = 1;

//游戏类别
$table['config_class']['db'] = 'lm_config_global';
$table['config_class']['num'] = 1;

//游戏产品配置表
$table['game_product']['db'] = 'lm_config_game';
$table['game_product']['num'] = 1;

//游戏产品包配置表
$table['game_product_pack']['db'] = 'lm_config_game';
$table['game_product_pack']['num'] = 1;

//操作日志
$table['config_logs_manager']['db'] = 'lm_config_global';
$table['config_logs_manager']['num'] = 16;

//数据权限组的日期限制
$table['date_permission']['db'] = 'lm_config_global';
$table['date_permission']['num'] = 1;

//职业配置表
$table['game_job']['db'] = 'lm_config_game';
$table['game_job']['num'] = 1;

//英雄配置表
$table['game_hero']['db'] = 'lm_config_game';
$table['game_hero']['num'] = 1;

//皮肤配置表
$table['game_skin']['db'] = 'lm_config_game';
$table['game_skin']['num'] = 1;

//脸型配置表
$table['game_face']['db'] = 'lm_config_game';
$table['game_face']['num'] = 1;

//发型配置表
$table['game_hair']['db'] = 'lm_config_game';
$table['game_hair']['num'] = 1;

//任务配置表
$table['game_quest']['db'] = 'lm_config_game';
$table['game_quest']['num'] = 1;

//产品类型配置表
$table['game_product_type']['db'] = 'lm_config_game';
$table['game_product_type']['num'] = 1;

//广告活动配置表
$table['user_source_action']['db'] = 'lm_config_global';
$table['user_source_action']['num'] = 1;

//广告商分类配置表
$table['user_source_type']['db'] = 'lm_config_global';
$table['user_source_type']['num'] = 1;

//广告商配置表
$table['user_source']['db'] = 'lm_config_global';
$table['user_source']['num'] = 1;

//推广项目配置表
$table['user_source_project']['db'] = 'lm_config_global';
$table['user_source_projuet']['num'] = 1;

//推广项目配置表
$table['user_source_project_detail']['db'] = 'lm_config_global';
$table['user_source_project_detail']['num'] = 1;

//广告位配置表
$table['user_source_detail']['db'] = 'lm_config_global';
$table['user_source_detail']['num'] = 1;

//FAQ分类配置表
$table['faq_type']['db'] = 'lm_config_global';
$table['faq_type']['num'] = 1;

//FAQ配置表
$table['faq']['db'] = 'lm_config_global';
$table['faq']['num'] = 1;

//道具阀值配置
$table['game_item_seal']['db'] = 'lm_config_game';
$table['game_item_seal']['num'] = 1;

//IP数据库
$table['ip_data']['db'] = 'lm_config_global';
$table['ip_data']['num'] = 1;

//邮件列表
$table['mail_queue']['db'] = 'mail_queue';
$table['mail_queue']['num'] = 1;

//密码重置状态日志表
$table['password_reset_log']['db'] = 'lm_user';
$table['password_reset_log']['num'] = 1;

//角色创建日志表
$table['user_character_create_log']['db'] = 'lm_character_create_log';
$table['user_character_create_log']['num'] = 1;

//角色死亡日志表
$table['user_character_dead_log']['db'] = 'lm_character_dead_log';
$table['user_character_dead_log']['num'] = 1;

//角色进出副本日志表
$table['user_character_slk_log']['db'] = 'lm_character_task_log';
$table['user_character_slk_log']['num'] = 1;

#PVE塔日志日期分表
$table['pve_tower_log']['db'] = 'lm_character_task_log';
$table['pve_tower_log']['num'] = 1;

//角色进出副本日志表
$table['pvp_log']['db'] = 'lm_character_task_log';
$table['pvp_log']['num'] = 1;

//角色进出副本日志表
$table['slkid_map']['db'] = 'lm_character_task_log';
$table['slkid_map']['num'] = 1;

//PVP副本时间
$table['pvp_total_log']['db'] = 'lm_character_task_log';
$table['pvp_total_log']['num'] = 1;

//副本配置表
$table['game_instmap']['db'] = 'lm_config_game';
$table['game_instmap']['num'] = 1;

//机房配置表
$table['depot']['db'] = 'lm_config_global';
$table['depot']['num'] = 1;

//机柜配置表
$table['cage']['db'] = 'lm_config_global';
$table['cage']['num'] = 1;

//服务器机体配置表
$table['machine']['db'] = 'lm_config_global';
$table['machine']['num'] = 1;

//抽奖基础配置表
$table['loto_list']['db'] = 'lm_loto';
$table['loto_list']['num'] = 1;

//抽奖奖品基础配置表
$table['loto_prize_list']['db'] = 'lm_loto';
$table['loto_prize_list']['num'] = 1;

//抽奖奖品概率配置表
$table['loto_prize_detail_list']['db'] = 'lm_loto';
$table['loto_prize_detail_list']['num'] = 1;

//抽奖日志表
$table['loto_log']['db'] = 'lm_loto';
$table['loto_log']['num'] = 1;

//服务器在线日志表
$table['lm_statlog_online']['db'] = 'lm_statlog';
$table['lm_statlog_online']['num'] = 1;

//获得永久英雄日志表
$table['lm_hero_add_log']['db'] = 'lm_hero';
$table['lm_hero_add_log']['num'] = 1;

//切换英雄日志表
$table['lm_hero_change_log']['db'] = 'lm_hero';
$table['lm_hero_change_log']['num'] = 1;

//接取任务日志表
$table['lm_accept_task_log']['db'] = 'lm_character_task_log';
$table['lm_accept_task_log']['num'] = 1;

//完成任务日志表
$table['lm_task_complete_log']['db'] = 'lm_character_task_log';
$table['lm_task_complete_log']['num'] = 1;

//PVP日志场次汇总表
$table['pvp_log_total']['db'] = 'lm_character_task_log';
$table['pvp_log_total']['num'] = 1;

//商城购买
$table['lm_item_Purchase_log']['db'] = 'lm_shop';
$table['lm_item_Purchase_log']['num'] = 1;

//npc商城购买
$table['lm_npc_item_Purchase_log']['db'] = 'lm_shop';
$table['lm_npc_item_Purchase_log']['num'] = 1;

//用户余额保有量
$table['lm_user_lastmoney']['db'] = 'lm_shop';
$table['lm_user_lastmoney']['num'] = 1;

//道具拾取
$table['lm_item_pickup_log']['db'] = 'lm_item';
$table['lm_item_pickup_log']['num'] = 1;

//道具拾取压缩
$table['lm_item_pickup_date']['db'] = 'lm_item';
$table['lm_item_pickup_date']['num'] = 1;

//道具拾取用户分表
$table['lm_item_pickup_log_user']['db'] = 'lm_character';
$table['lm_item_pickup_log_user']['num'] = 1;

//角色升级日志
$table['user_character_levelup_log']['db'] = 'lm_character_levelup_log';
$table['user_character_levelup_log']['num'] = 1;

//调研基础配置表
$table['research']['db'] = 'Researching';
$table['research']['num'] = 1;

//调研问题配置表
$table['question']['db'] = 'Researching';
$table['question']['num'] = 1;

//调研回答配置表
$table['research_answer']['db'] = 'Researching';
$table['research_answer']['num'] = 1;

//Socket待发队列表
$table['socket_queue']['db'] = 'socket_queue';
$table['socket_queue']['num'] = 1;

//Socket待发队列表
$table['socket_queue_date']['db'] = 'socket_queue';
$table['socket_queue_date']['num'] = 1;

//游戏退出日志
$table['lm_character_logout_log']['db'] = 'lm_character_login_log';
$table['lm_character_logout_log']['num'] = 1;

//微信
$table['weixin']['db'] = 'lm_weixin';
$table['weixin']['num'] = 1;

//金钱日志
$table['lm_moneylog']['db'] = 'lm_money';
$table['lm_moneylog']['num'] = 1;

//金钱日志压缩表
$table['lm_moneylog_compress']['db'] = 'lm_money';
$table['lm_moneylog_compress']['num'] = 1;

//PV浏览记录表
$table['pv_log']['db'] = 'lm_pv_log';
$table['pv_log']['num'] = 1;

//视频列表
$table['video_list']['db'] = 'lm_config_global';
$table['video_list']['num'] = 1;

//视频分类列表
$table['video_type_list']['db'] = 'lm_config_global';
$table['video_type_list']['num'] = 1;

return $table;

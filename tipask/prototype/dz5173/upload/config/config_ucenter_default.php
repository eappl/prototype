<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config_ucenter_default.php 11023 2010-05-20 02:23:09Z monkey $
 */

// ============================================================================
define('UC_CONNECT', 'mysql');				// 连接 UCenter 的方式: mysql/NULL, 默认为空时为 fscoketopen(), mysql 是直接连接的数据库, 为了效率, 建议采用 mysql
// 数据库相关 (mysql 连接时)
define('UC_DBHOST', 'localhost');			// UCenter 数据库主机
define('UC_DBUSER', 'root');				// UCenter 数据库用户名
define('UC_DBPW', 'root');				// UCenter 数据库密码
define('UC_DBNAME', 'ucenter');				// UCenter 数据库名称
define('UC_DBCHARSET', 'utf8');				// UCenter 数据库字符集
define('UC_DBTABLEPRE', '`ucenter`.uc_');		// UCenter 数据库表前缀
define('UC_DBCONNECT', '0');				// UCenter 数据库持久连接 0=关闭, 1=打开

// 通信相关
define('UC_KEY', 'N1B5X8vfl9p0K6M1n1p4F4C7L9B418kdEeh3A591k3x4q4d649e7mfc2Z2udNfja');	// 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', 'http://bbs.5173.com/uc_server'); // UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', 'utf-8');				// UCenter 的字符集
define('UC_IP', '127.0.0.1');				// UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', '1');				// 当前应用的 ID

// ============================================================================

define('UC_PPP', '20');

?>
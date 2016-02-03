<?php
/**
 * web入口
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: index.php 15195 2014-07-23 07:18:26Z 334746 $
 */
header("Content-type: text/html; charset=utf-8");
//error_reporting(0);
include dirname(__FILE__) . '/init.php';

Base_Controller_Front::run();

<?php
/**
 * cli模式模拟Http request
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Cli.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Controller_Request_Cli extends Base_Controller_Request_Abstract
{
	public function __construct()
	{
		global $argv;

		$this->setParams($argv[1]);
	}

}

<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: Exchange.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Widget_Log_Exchange extends Widget_Log
{

	protected static $instance = null;

	public static function getInstance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}


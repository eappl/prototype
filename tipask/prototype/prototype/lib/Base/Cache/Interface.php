<?php
/**
 * cache interface
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Interface.php 15195 2014-07-23 07:18:26Z 334746 $
 */

interface Base_Cache_Interface
{
	public function get($key);
	public function set($key, $value, $expire = 900);
	public function remove($key);
}

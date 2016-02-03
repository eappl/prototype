<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: User.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Widget_User extends Base_Widget
{

	protected $table = 'p_clb';

	protected $isLogged = null;

	protected $key = 'k4kfEOJdi9asUWKKIfjsoUH5Aa09dnAB';

	protected $loginUrl = '?ctl=index';

	public function getLoginUrl()
	{
		return $this->loginUrl;
	}
	
	/**
	 * 判断用户是否登录
	 * @return boolean
	 */
	public function isLogin()
	{
		if (null !== $this->isLogged) {
			return $this->isLogged;
		} else {
			$this->isLogged = false;

			//判断是否设置了weeCookie,如果设置则验证是否正确
			if (!empty($_COOKIE['weeCookie'])) 
			{
				$cookieUser = json_decode(str_replace(array('\\"','\\\\'),array('"','\\'),$_COOKIE['weeCookie']));
				if (md5($cookieUser->username . $this->key) == $cookieUser->mac && $cookieUser->loginFlag) {
					$user = array('uid' => $cookieUser->uid, 'username' => $cookieUser->username, 'nickname' => $cookieUser->nickname);
					$this->push($user);
					$this->isLogged = true;
				}
			}
			return $this->isLogged;
		}
	}
	
	/**
	 * 获取用户登录信息
	 * @return array
	 */
	public function getUser()
	{
		return json_decode(str_replace(array('\\"','\\\\'),array('"','\\'),$_COOKIE['weeCookie']),true);
	}
	
}

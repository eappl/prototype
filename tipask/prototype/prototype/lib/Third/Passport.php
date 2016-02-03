<?php

/******************************************************************

	Name: Passport 通行证类
	Version: 1.3.5
	Update: 2008/09/10
	Author: Eks

/******************************************************************/

class Third_Passport
{
	var $appId = 10; // 服务ID
	var $encryptKey = 'k4kfEOJdi9asUWKKSDXxCKDIfjsoUH5Aa09dnAB'; // 私钥
	var $varName = "p"; // 保存 Passport 信息的 URL 变量名称
	var $returnVarName = 'p'; // 保存 Passport 返回信息的 URL 变量名称
	var $useSocket = false;
	var $scheme = "";
	var $host = "passport.9wee.com";
	var $hostIp = "";
	var $port = "80";
	var $api = "/api/x_verify.php";

	var $urlInterface = "http://passport.9wee.com"; // Passport 站点地址
	var $urlRegister = "http://passport.9wee.com/register"; // 注册地址
	var $urlLogin = "http://passport.9wee.com/login?referer=%s"; // 登陆地址
	var $urlLogout = "http://passport.9wee.com/logout?referer=%s"; // 注销地址
	var $apiVerify = "http://passport.9wee.com/api/x_verify.php"; // 获取信息验证接口
	var $useCookie = 1;
	var $cookieDomain = null;
	var $debug = false;

	var $vars;

	function postQuery ( $postVars )
	{
		$postQuery = urlencode ( $this->encrypt ( $this->serialize ( $postVars ) ) );
		$postData = $this->varName . '=' . $postQuery;

		if ( $this->useSocket )
		{
			$postLength = strlen ( $postData );

			if ( !$this->hostIp ) $this->hostIp = @gethostbyname ( $this->host );
			$hostIp = $this->hostIp ? $this->hostIp : $this->host;

			$fp = fsockopen ( $this->scheme . $hostIp, $this->port, $errStr, $errNo, 10 );

			if ( !$fp ) exit ();

			$out = "POST {$this->api}?appId={$this->appId} HTTP/1.1\n";
			$out .= "Host: {$this->host}\n";
			$out .= "Content-type: application/x-www-form-urlencoded\n";
			$out .= "Content-length: $postLength\n";
			$out .= "Connection: close\n\n";
			$out .= "$postData\n";

			fwrite ( $fp, $out );

			$results = "";
			$inHeader = true;
			while ( !feof ( $fp ) )
			{
				$line = fgets ( $fp, 1024 );
				if ( $inHeader && ( $line == "\n" || $line == "\r\n" ) )
				{
					$inHeader = false;
				}
				elseif ( !$inHeader )
				{
					$results .= $line;
				}
			}
			fclose ( $fp );
		}
		else
		{
			$results = file_get_contents ( "{$this->apiVerify}?appId={$this->appId}&$postData", "r" );
		}

		if ( $this->debug )
		{
			echo ( "{$this->apiVerify}?appId={$this->appId}&$postData" );
		}

		$this->vars = $this->unserialize ( $this->decrypt ( $results ) );
		return $this->vars;
	}

	// 验证
	function verify ()
	{
		$passportVars = $this->loadSession ( $this->returnVarName );
		$arrVars = array (
			'cookie' => $passportVars,
			'get' => $_GET[$this->returnVarName],
			);

		while ( list ( $key, $xPassportVars ) = @each ( $arrVars ) )
		{
			$this->vars = false;
			if ( $xPassportVars )
			{
				$this->saveSession ( $this->returnVarName, $xPassportVars );
				$vars = $this->unserialize ( $this->decrypt ( $xPassportVars ) );
				if ( $vars['session_id'] )
				{
					/*
					$postArr = array (
						'sessionId' => $vars['session_id'],
						'username' => $vars['username'],
						'action' => 'verify',
						);

					$this->postQuery ( $postArr );
					*/

					$this->vars = array (
						'session_id' => $vars['session_id'],
						'username' => $vars['username'],
						'nickname' => $vars['nickname'],
						'password' => $vars['password'],
						'email' => $vars['email'],
						'app_user_id' => $vars['app_user_id'],
						);

					if ( $this->vars['username'] )
					{
						if ( $key == 'get' )
						{
							$this->saveSession ( "loginSession", $this->vars['session_id'] );
							$this->redirect ( $this->currentPage () );
						}
						else
						{
							$this->deleteSession ( $this->returnVarName );
						}
						return true;
					}
				}
			}
		}

		if ( !$this->vars['username'] && !isset ( $_GET[$this->returnVarName] ) )
		{
			$vars = array (
				'referer' => $this->currentPage (),
				'pVar' => $this->returnVarName,
				);
			$xPassportVars = $this->encrypt ( $this->serialize ( $vars ) );
			$this->redirect ( "{$this->apiVerify}?appId={$this->appId}&{$this->varName}=" . urlencode ( $xPassportVars ) );
		}
		return false;
	}

	// 获取用户信息
	function getUserInfo ( $getFields = null )
	{
		$xPassportVars = $this->loadSession ( $this->returnVarName );
		$vars = $this->unserialize ( $this->decrypt ( $xPassportVars ) );
		if ( $vars['session_id'] )
		{
			$postArr = array (
				'sessionId' => $vars['session_id'],
				'username' => $vars['username'],
				'getFields' => $getFields,
				'action' => 'getUserInfo',
				);
			$this->postQuery ( $postArr );
			return $this->vars;
		}
	}

	/****** 以下模块不需要身份验证 ******/

	// 激活用户
	function active ( $username, $appUserId, $appUserLevel = 0 )
	{
		$postArr = array (
			'username' => $username,
			'appUserId' => $appUserId,
			'appUserLevel' => $appUserLevel,
			'action' => 'active',
			);
		return $this->postQuery ( $postArr );
	}

	// 检验用户是否存在
	function appUserExist ( $username )
	{
		$postArr = array (
			'username' => $username,
			'action' => 'appUserExist',
			);
		return $this->postQuery ( $postArr );
	}

	// 更新用户状态
	// $state 0: 未激活, 1: 未认证, 2: 已认证, 3: 已冻结
	function appUpdateUserState ( $username, $state )
	{
		$postArr = array (
			'username' => $username,
			'state' => $state,
			'action' => 'appUpdateUserState',
			);
		return $this->postQuery ( $postArr );
	}

	// 获取程序列表
	function appGetList ( $appType = 0 )
	{
		$postArr = array (
			'appType' => $appType,
			'action' => 'appGetList',
			);
		return $this->postQuery ( $postArr );
	}

	// 根据通行证获取该用户在当前程序的 ID
	function appGetUserId ( $username, $appId = 0 )
	{
		$postArr = array (
			'username' => $username,
			'appId' => $appId ? $appId : $this->appId,
			'action' => 'appGetUserId',
			);
		return $this->postQuery ( $postArr );
	}

	// 根据通行证获取用户在某服务上的等级
	function appGetUserLevel ( $username, $appId )
	{
		$postArr = array (
			'username' => $username,
			'appId' => $appId,
			'action' => 'appGetUserLevel',
			);
		return $this->postQuery ( $postArr );
	}

	// 改变用户在服务的级别
	function appUpdateUserAppLevel ( $username, $appLevel )
	{
		$postArr = array (
			'username' => $username,
			'action' => 'appUpdateUserAppLevel',
			'appLevel' => $appLevel,
			);
		return $this->postQuery ( $postArr );
	}

	// 更新金币
	function appUpdateUserMoney ( $username, $quantity )
	{
		$quantity = intval ( $quantity );
		$postArr = array (
			'username' => $username,
			'action' => 'appUpdateUserMoney',
			'quantity' => $quantity,
			);
		return $this->postQuery ( $postArr );
	}

	// 更新积分
	function appUpdateUserCredit ( $username, $quantity )
	{
		$quantity = intval ( $quantity );
		$postArr = array (
			'username' => $username,
			'action' => 'appUpdateUserCredit',
			'quantity' => $quantity,
			);
		return $this->postQuery ( $postArr );
	}

	// 根据昵称获取用户名
	function appGetUsernameByNickname ( $nickname )
	{
		$postArr = array (
			'nickname' => $nickname,
			'action' => 'appGetUsernameByNickname',
			);
		$this->postQuery ( $postArr );
		return $this->vars;
	}

	// 根据通行证账号获取用户信息
	function appGetUserInfo ( $username, $getFields = null )
	{
		$postArr = array (
			'username' => $username,
			'action' => 'appGetUserInfo',
			'getFields' => $getFields,
			);
		$this->postQuery ( $postArr );
		return $this->vars;
	}

	/****** 以下为功能函数 ******/

	function keyEd ( $txt )
	{
		$encryptKey = md5 ( $this->encryptKey );
		$ctr = 0;
		$tmp = "";
		for ( $i = 0; $i < strlen ( $txt ); $i++ )
		{
			if ( $ctr == strlen ( $encryptKey ) )
			{
				$ctr=0;
			}
			$tmp .= substr ( $txt, $i, 1 ) ^ substr ( $encryptKey, $ctr, 1);
			$ctr ++;
		}
		return $tmp;
	}

	function encrypt ( $txt )
	{
		srand ( (double)microtime () * 1000000 );
		$encryptKey = md5 ( rand ( 0, 32000 ) );
		$ctr = 0;
		$tmp = "";
		for ( $i = 0; $i < strlen ( $txt ); $i++ )
		{
			if ( $ctr == strlen ( $encryptKey ) )
			{
				$ctr=0;
			}
			$tmp .= substr ( $encryptKey, $ctr, 1) . ( substr ( $txt, $i, 1 ) ^ substr ( $encryptKey, $ctr, 1 ) );
			$ctr ++;
		}
		return base64_encode ( $this->keyEd ( $tmp ) );
	}

	function decrypt ( $txt )
	{
		$txt = $this->keyEd ( base64_decode ( $txt ) );
		$tmp = "";
		for ( $i=0; $i < strlen ( $txt ); $i++ )
		{
			$md5 = substr ( $txt, $i, 1 );
			$i ++;
			$tmp.= ( substr ( $txt, $i, 1 ) ^ $md5 );
		}
		return $tmp;
	}

	// 读取 Session
	function loadSession ( $var )
	{
		if ( $this->useCookie )
		{
			$ret = $_COOKIE[$var];
			if ( $ret == 'deleted' ) return;
		}
		else
		{
			$ret = $_SESSION[$var];
		}

		// $ret = $this->decrypt ( $ret );
		return $ret;
	}

	// 保存 Session
	function saveSession ( $var, $val, $expire = 0 )
	{
		// $val = $this->encrypt ( $val );
		if ( $this->useCookie )
		{
			if ( $expire > 0 )
			{
				$expire += time ();
			}
			setCookie ( $var, $val, $expire, '/', $this->cookieDomain );
		}
		else
		{
			if ( !session_is_registered ( $var ) )
			{
				session_register ( $var );
			}
			$_SESSION[$var] = $val;
		}
		return $val;
	}

	// 删除 Session
	function deleteSession ( $var )
	{
		if ( $this->useCookie )
		{
			setCookie ( $var, '', 0, '/', $this->cookieDomain );
			unset ( $_COOKIE[$var] );
		}
		else
		{
			session_unregister ( $var );
			unset ( $_SESSION[$var] );
		}
		return true;
	}

	// 删除所有 Session
	function deleteAllSessions ()
	{
		$setting = loadAllSession ();
		while ( list ( $key, $val ) = @each ( $setting ) )
		{
			deleteSession ( $key );
		}
		return true;
	}

	// 当前页面
	function currentPage ()
	{
		$scheme = ( $_SERVER["https"] == 'on' || $_SERVER["https"] == 'https' ) ? 'https://' : 'http://';
		$currentPage = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		if ( $_SERVER['QUERY_STRING'] != '' )
		{
			$currentPage .= '?' . $_SERVER['QUERY_STRING'];
		}
		$currentPage = preg_replace ( "/[\?|&]" . $this->returnVarName . "=[^&]*/", '', $currentPage );
		return $currentPage;
	}

	// 来路页面
	function refererPage ()
	{
		if ( $_REQUEST['referer'] ) return $_REQUEST['referer'];
		else return $_SERVER['HTTP_REFERER'];
	}

	function arrayDeal ( $Arr, $Func = '' )
	{
		if ( !$Func )
		{
			return false;
		}

		while ( list ( $key, $item ) = @each ( $Arr ) )
		{
			$Arr[$key] = is_array ( $item ) ? $this->arrayDeal ( $item, $Func ) : $Func ( $item );
		}
		return $Arr;
	}

	function serialize ( $Arr )
	{
		$Arr = $this->arrayDeal ( $Arr, 'urlencode' );
		$Str = serialize ( $Arr );
		return $Str;
	}

	function unserialize ( $Str )
	{
		$Arr = @unserialize ( $Str );
		$Arr = $this->arrayDeal ( $Arr, 'urldecode' );
		$Arr = $this->arrayDeal ( $Arr, 'stripslashes' );
		@reset ( $Arr );
		return $Arr;
	}

	// 重定向
	function redirect ( $URL = '' )
	{
		if ( $URL == '' )
		{
			$URL = $_SERVER['PHP_SELF'];
		}
		header ( "Location: $URL" );
		exit;
	}

	// 注册跳转
	function register ()
	{
		$this->redirect ( $this->urlRegister );
	}

	// 登陆跳转
	function login ()
	{
		$this->redirect ( sprintf ( $this->urlLogin, $this->refererPage () ) );
	}

	// 退出跳转
	function logout ( $referer = null )
	{
		$this->deleteSession ( $this->returnVarName );
		if ( $referer !== false )
		{
			if ( is_null ( $referer ) ) $referer = $this->refererPage ();
			$this->redirect ( sprintf ( $this->urlLogout, $referer ) );
		}
	}
}

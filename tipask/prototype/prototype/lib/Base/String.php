<?php
/**
 * 字符串处理
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: String.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_String
{
	/**
	 * 根据用户名获取十进制三位数字的库 表
	 * @param string $username
	 * @return string
	 */
	public static function strconvert($username){

		return sprintf("%03d",base_convert(substr(md5($username),0,2), 16, 10));
	}
	
	/**
	 * 签名生成
	 * @var string
	 */
	public static function sign($data = array(), $key, $exchange_rule = "")
	{
		$sign = "";
		if(!empty($exchange_rule))
		{
			//加密使用
			$encryptArr = array();
			$encryptArr['~partner_order~'] = $data['partner_order'];
			$encryptArr['~username~'] = $data['username'];
			$encryptArr['~AppId~'] = $data['AppId'];
			$encryptArr['~ServerId~'] = $data['ServerId'];
			$encryptArr['~amount~'] = $data['amount'];
			$search = array_keys($encryptArr);
			$replace = array_values($encryptArr);
			$sign = str_replace($search, $replace, $exchange_rule);
		}
		else
		{
			if (!empty($data['sign']))
				unset($data['sign']);
				
			krsort($data);
			foreach($data as $k => $v)
			{
				$sign .= $v;
			}
		}
		return md5($sign.$key);
	}
	
	/**
	 * 按字数截取
	 * @param string $str
	 * @param integer $length
	 * @param string $dot
	 * @return string
	 */
	public static function substr($str, $length, $strimmarker = '...', $start = 0)
	{
		if (function_exists('mb_get_info')) {
			$iLength = mb_strlen($str, 'utf-8');
			$str = mb_substr($str, $start, $length, 'utf-8');
			return ($length < $iLength) ? $str . $strimmarker : $str;
		} else {
			preg_match_all("/./us", $str, $m);
			$str = join("", array_splice($m[0], $start, $length));
			return ($length < count($m[0])) ? $str . $strimmarker : $str;
		}
	}

	/**
	 * 按字符宽度截取
	 * @param string $str
	 * @param integer $width
	 * @param string $strimmarker
	 * @param integer $start
	 * @return string
	 */
	public static function strimwidth($str, $width, $strimmarker = '...', $start = 0)
	{
		return mb_strimwidth($str, $start, $width, $strimmarker, 'utf-8');
	}

	/**
	 * 字数
	 * @param string $str
	 * @return integer
	 */
	public static function strlen($str)
	{
		if (function_exists('mb_get_info')) {
			return mb_strlen($str, 'UTF-8');
		} else {
			preg_match_all("/./us", $str, $m);
			return count($m[0]);
		}
	}

	/**
	 * 生成随机字符串或数字
	 * @param integer $length
	 * @param boolean $numeric
	 * @return string
	 */
	public static function random($length, $numeric = false)
	{
		if($numeric) {
			return sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
		} else {
			$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$max = strlen($chars) - 1;

			/** 生成以字母开头的随机字符串 **/
			$str = $chars[mt_rand(9, $max)];
			for($i = 1; $i < $length; $i++) {
				$str .= $chars[mt_rand(0, $max)];
			}

			return $str;
		}
	}

	/**
	 * 解密字符串, 应用的是dz的authcode算法
	 * @param string $str
	 * @param string $key 密钥
	 * @return string
	 */
	public static function decode($str, $key = '')
	{
		$ckeyLen = 4;
		$key = md5($key ? $key : Base_Common::$config['private_key']);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckeyLen ? substr($str, 0, $ckeyLen) : '';

		$cryptkey = $keya . md5($keya . $keyc);
		$keyLen = strlen($cryptkey);

		$str = base64_decode(substr($str, $ckeyLen));
		$strLen = strlen($str);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $keyLen]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $strLen; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($str[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0)
		&& substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	}

	/**
	 * 加密字符串, 应用的是dz的authcode算法
	 * @param string $str
	 * @param string $key 密钥
	 * @param integer $expiry 有效期(秒)
	 * @return string
	 */
	public static function encode($str, $key = '', $expiry = 0)
	{
		$ckeyLen = 4;
		$key = md5($key ? $key : Base_Common::$config['private_key']);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckeyLen ? substr(md5(microtime()), -$ckeyLen) : '';

		$cryptkey = $keya . md5($keya . $keyc);
		$keyLen = strlen($cryptkey);

		$str = sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($str . $keyb), 0, 16) . $str;
		$strLen = strlen($str);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $keyLen]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $strLen; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($str[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		return $keyc . str_replace('=', '', base64_encode($result));
	}

}

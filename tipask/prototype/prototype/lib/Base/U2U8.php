<?php
/**
 * utf8编码转换为unicode
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: U2U8.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_U2U8
{

	public static function convert($str, $target = 'utf8')
	{
		switch ($target) {
			case 'unicode':
				return self::utf8ToUnicode($str);
			case 'utf8':
				return self::unicodeToUtf8($str);
		}
	}

	public static function unicodeToUtf8($str)
	{
		$str = preg_replace("|&#(x[0-9a-fA-F]{1,5});|se", '"&#".hexdec("\\1").";"', $str);

        $str = preg_replace("|&#([0-9a-fA-F]{1,5});|se", 'self::unicode_utf8("\\1")', $str);

        return $str;
	}

	public static function utf8ToUnicode($str)
	{
		$str = preg_replace("|.|use", '"&#".self::utf8_unicode("\\0").";"', $str);

		return $str;
	}

	public static function unicode_utf8($c)
	{
	    $str = "";
	    if ($c < 0x80) {
	         $str.=$c;
	    } else if ($c < 0x800) {
	         $str.=chr(0xC0 | $c>>6);
	         $str.=chr(0x80 | $c & 0x3F);
	    } else if ($c < 0x10000) {
	         $str.=chr(0xE0 | $c>>12);
	         $str.=chr(0x80 | $c>>6 & 0x3F);
	         $str.=chr(0x80 | $c & 0x3F);
	    } else if ($c < 0x200000) {
	         $str.=chr(0xF0 | $c>>18);
	         $str.=chr(0x80 | $c>>12 & 0x3F);
	         $str.=chr(0x80 | $c>>6 & 0x3F);
	         $str.=chr(0x80 | $c & 0x3F);
	    }
	    return $str;
	}

	public static function utf8_unicode($char)
	{
		switch(strlen($char))
		{
			case 1:
				return ord($char);
			case 2:
				$n = (ord($char[0]) & 0x3f) << 6;
				$n += ord($char[1]) & 0x3f;
				return $n;
			case 3:
				$n = (ord($char[0]) & 0x1f) << 12;
				$n += (ord($char[1]) & 0x3f) << 6;
				$n += ord($char[2]) & 0x3f;
				return $n;
			case 4:
				$n = (ord($char[0]) & 0x0f) << 18;
				$n += (ord($char[1]) & 0x3f) << 12;
				$n += (ord($char[2]) & 0x3f) << 6;
				$n += ord($char[3]) & 0x3f;
				return $n;
		}
	}
}

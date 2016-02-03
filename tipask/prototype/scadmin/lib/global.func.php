<?php
/* 伪静态和html纯静态可以同时存在 */

function url($var, $url='') {
    global $setting;
    $location = '?' . $var . $setting['seo_suffix'];   
    if ($url)
        return SITE_URL . $location; //程序动态获取的，给question的model使用
    else
        return '<?=SITE_URL?>' . $location; //模板编译时候生成使用
}

/**
 * random
 * @param int $length
 * @return string $hash
 */
function random($length=6, $type=0) {
    $hash = '';
    $chararr = array(
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',
        '0123456789',
        '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'
    );
    $chars = $chararr[$type];
    $max = strlen($chars) - 1;
    PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

function cutstr1($string, $length, $dot = ' ...') {
    if (strlen($string) <= $length) {
        return $string;
    }
    $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
    $strcut = '';
    if (strtolower(TIPASK_CHARSET) == 'utf-8') {
        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
    } else {
        for ($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
    }
    $strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
    return $strcut . $dot;
}

function generate_key() {
    $random = random(20);
    $info = md5($_SERVER['SERVER_SOFTWARE'] . $_SERVER['SERVER_NAME'] . $_SERVER['SERVER_ADDR'] . $_SERVER['SERVER_PORT'] . $_SERVER['HTTP_USER_AGENT'] . time());
    $return = '';
    for ($i = 0; $i < 64; $i++) {
        $p = intval($i / 2);
        $return[$i] = $i % 2 ? $random[$p] : $info[$p];
    }
    return implode('', $return);
}

/**
 * getip
 * @return string
 */
function getip() {
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } else if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    preg_match("/[\d\.]{7,15}/", $ip, $temp);
    $ip = $temp[0] ? $temp[0] : 'unknown';
    unset($temp);
    return $ip;
}

//格式化前端IP显示
function formatip($ip, $type=1) {
    if (strtolower($ip) == 'unknown') {
        return false;
    }
    if ($type == 1) {
        $ipaddr = substr($ip, 0, strrpos($ip, ".")) . ".*";
    }
    return $ipaddr;
}

function forcemkdir($path) {
    if (!file_exists($path)) {
        forcemkdir(dirname($path));
        mkdir($path, 0777);
    }
}

function cleardir($dir, $forceclear=false) {
    if (!is_dir($dir)) {
        return;
    }
    $directory = dir($dir);
    while ($entry = $directory->read()) {
        $filename = $dir . '/' . $entry;
        if (is_file($filename)) {
            @unlink($filename);
        } elseif (is_dir($filename) && $forceclear && $entry != '.' && $entry != '..') {
            chmod($filename, 0777);
            cleardir($filename, $forceclear);
            rmdir($filename);
        }
    }
    $directory->close();
}

function iswriteable($file) {
    $writeable = 0;
    if (is_dir($file)) {
        $dir = $file;
        if ($fp = @fopen("$dir/test.txt", 'w')) {
            @fclose($fp);
            @unlink("$dir/test.txt");
            $writeable = 1;
        }
    } else {
        if ($fp = @fopen($file, 'a+')) {
            @fclose($fp);
            $writeable = 1;
        }
    }
    return $writeable;
}

function readfromfile($filename) {
    if ($fp = @fopen($filename, 'rb')) {
        if (PHP_VERSION >= '4.3.0' && function_exists('file_get_contents')) {
            return file_get_contents($filename);
        } else {
            flock($fp, LOCK_EX);
            $data = fread($fp, filesize($filename));
            flock($fp, LOCK_UN);
            fclose($fp);
            return $data;
        }
    } else {
        return '';
    }
}

function writetofile($filename, &$data) {
    if ($fp = @fopen($filename, 'wb')) {
        if (PHP_VERSION >= '4.3.0' && function_exists('file_put_contents')) {
            return @file_put_contents($filename, $data);
        } else {
            flock($fp, LOCK_EX);
            $bytes = fwrite($fp, $data);
            flock($fp, LOCK_UN);
            fclose($fp);
            return $bytes;
        }
    } else {
        return 0;
    }
}

function extname($filename) {
    $pathinfo = pathinfo($filename);
    return strtolower($pathinfo['extension']);
}
function remove_xss($val) {
	// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
	// this prevents some character re-spacing such as <java\0script>
	// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
	$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

	// straight replacements, the user should never need these since they're normal characters
	// this prevents like <IMG SRC=@avascript:alert('XSS')>
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		// ;? matches the ;, which is optional
		// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

		// @ @ search for the hex values
		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
		// @ @ 0{0,7} matches '0' zero to seven times
		$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
	}

	// now the only remaining whitespace attacks are \t, \n, and \r
	$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	$ra = array_merge($ra1, $ra2);

	$found = true; // keep replacing as long as the previous round replaced something
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
			$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
			if ($val_before == $val) {
				// no replacements were made, so exit the loop
				$found = false;
			}
		}
	}
	return $val;
}
function taddslashes($string, $force = 0) {
    if (!MAGIC_QUOTES_GPC || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = taddslashes($val, $force);
            }
        } else {
            $string = remove_xss(addslashes($string));
        }
    }
    return $string;
}

function tstripslashes($string) {
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = tstripslashes($val);
        }
    } else {
        $string = stripslashes($string);
    }
    return $string;
}

function template($file, $tpldir = '') {
    global $setting;
    $tpldir = ('' == $tpldir) ? $setting['tpl_dir'] : $tpldir;
    $tplfile = TIPASK_ROOT . '/view/' . $tpldir . '/' . $file . '.html';
    $objfile = TIPASK_ROOT . '/data/view/' . $tpldir . '_' . $file . '.tpl.php';
    if ('default' != $tpldir && !is_file($tplfile)) {
        $tplfile = TIPASK_ROOT . '/view/default/' . $file . '.html';
        $objfile = TIPASK_ROOT . '/data/view/default_' . $file . '.tpl.php';
    }
    if (!file_exists($objfile) || (@filemtime($tplfile) > @filemtime($objfile))) {
        require_once TIPASK_ROOT . '/lib/template.func.php';
        parse_template($tplfile, $objfile);
    }
    return $objfile;
}

function timeLength($time) {
    $length = '';
    if ($day = floor($time / (24 * 3600))) {
        $length .= $day . '天';
    }
    if ($hour = floor($time % (24 * 3600) / 3600)) {
        $length .= $hour . '小时';
    }
    if ($day == 0 && $hour == 0) {
        $length = floor($time / 60) . '分';
    }
    return $length;
}

/* 验证码生成 */

function makecode($code) {
    $codelen = strlen($code);
    $im = imagecreate(50, 20);
    $font_type = TIPASK_ROOT . '/css/common/ninab.ttf';
    $bgcolor = ImageColorAllocate($im, 245, 245, 245);
    $iborder = ImageColorAllocate($im, 0x71, 0x76, 0x67);
    $fontColor = ImageColorAllocate($im, 0x50, 0x4d, 0x47);
    $fontColor2 = ImageColorAllocate($im, 0x36, 0x38, 0x32);
    $fontColor1 = ImageColorAllocate($im, 0xbd, 0xc0, 0xb8);
    $lineColor1 = ImageColorAllocate($im, 130, 220, 245);
    $lineColor2 = ImageColorAllocate($im, 225, 245, 255);
    for ($j = 3; $j <= 16; $j = $j + 3)
        imageline($im, 2, $j, 48, $j, $lineColor1);
    for ($j = 2; $j < 52; $j = $j + (mt_rand(3, 6)))
        imageline($im, $j, 2, $j - 6, 18, $lineColor2);
    imagerectangle($im, 0, 0, 49, 19, $iborder);
    $strposs = array();
    for ($i = 0; $i < $codelen; $i++) {
        if (function_exists("imagettftext")) {
            $strposs[$i][0] = $i * 10 + 6;
            $strposs[$i][1] = mt_rand(15, 18);
            imagettftext($im, 11, 5, $strposs[$i][0] + 1, $strposs[$i][1] + 1, $fontColor1, $font_type, $code[$i]);
        } else {
            imagestring($im, 5, $i * 10 + 6, mt_rand(2, 4), $code[$i], $fontColor);
        }
    }
    for ($i = 0; $i < $codelen; $i++) {
        if (function_exists("imagettftext")) {
            imagettftext($im, 11, 5, $strposs[$i][0] - 1, $strposs[$i][1] - 1, $fontColor2, $font_type, $code[$i]);
        }
    }
    header("Pragma:no-cache\r\n");
    header("Cache-Control:no-cache\r\n");
    header("Expires:0\r\n");
    if (function_exists("imagejpeg")) {
        header("content-type:image/jpeg\r\n");
        imagejpeg($im);
    } else {
        header("content-type:image/png\r\n");
        imagepng($im);
    }
    ImageDestroy($im);
}

/* 通用加密解密函数，phpwind、phpcms、dedecms都用此函数 */

function strcode($string, $auth_key, $action='ENCODE') {
    $key = substr(md5($_SERVER["HTTP_USER_AGENT"] . $auth_key), 8, 18);
    $string = $action == 'ENCODE' ? $string : base64_decode($string);
    $len = strlen($key);
    $code = '';
    for ($i = 0; $i < strlen($string); $i++) {
        $k = $i % $len;
        $code .= $string[$i] ^ $key[$k];
    }
    $code = $action == 'DECODE' ? $code : base64_encode($code);
    return $code;
}

/* 日期格式显示 */

function tdate($time, $type = 3, $friendly=1) {
    global $setting;
    ($setting['time_friendly'] != 1) && $friendly = 0;
    $format[] = $type & 2 ? (!empty($setting['date_format']) ? $setting['date_format'] : 'Y-n-j') : '';
    $format[] = $type & 1 ? (!empty($setting['time_format']) ? $setting['time_format'] : 'H:i') : '';
    $timeoffset = $setting['time_offset'] * 3600 + $setting['time_diff'] * 60;
    $timestring = gmdate(implode(' ', $format), $time + $timeoffset);
    if ($friendly) {
        $time = time() - $time;
        if ($time <= 24 * 3600) {
            if ($time > 3600) {
                $timestring = intval($time / 3600) . '小时前';
            } elseif ($time > 60) {
                $timestring = intval($time / 60) . '分钟前';
            } elseif ($time > 0) {
                $timestring = $time . '秒前';
            } else {
                $timestring = '现在前';
            }
        }
    }
    return $timestring;
}

/* cookie设置和读取 */

function tcookie($var, $value=0, $life = 0) {
    global $setting;
    $cookiepre = $setting['cookie_pre'] ? $setting['cookie_pre'] : 't_';
    if (0 === $value) {
        return isset($_COOKIE[$cookiepre . $var]) ? $_COOKIE[$cookiepre . $var] : '';
    } else {
        $domain = $setting['cookie_domain'] ? $setting['cookie_domain'] : '';
        setcookie($cookiepre . $var, $value, $life ? time() + $life : 0, '/', $domain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
    }
}

/* 日志记录 */

function runlog($file, $message, $halt=0) {
    $nowurl = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : ($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
    $log = tdate($_SERVER['REQUEST_TIME'], 'Y-m-d H:i:s') . "\t" . $_SERVER['REMOTE_ADDR'] . "\t{$nowurl}\t" . str_replace(array("\r", "\n"), array(' ', ' '), trim($message)) . "\n";
    $yearmonth = gmdate('Ym', $_SERVER['REQUEST_TIME']);
    $logdir = TIPASK_ROOT . '/data/logs/';
    if (!is_dir($logdir))
        mkdir($logdir, 0777);
    $logfile = $logdir . $yearmonth . '_' . $file . '.php';
    if (@filesize($logfile) > 2048000) {
        $dir = opendir($logdir);
        $length = strlen($file);
        $maxid = $id = 0;
        while ($entry = readdir($dir)) {
        	if (strpos($entry, $yearmonth . '_' . $file) !== false) {
        		$id = intval(substr($entry, $length + 8, -4));
        		var_dump($id);
        		$id > $maxid && $maxid = $id;
        	}   	
        }
        closedir($dir);
        $logfilebak = $logdir . $yearmonth . '_' . $file . '_' . ($maxid + 1) . '.php';
        @rename($logfile, $logfilebak);
        @unlink($logfilebak);
    }
    if ($fp = @fopen($logfile, 'a')) {
        @flock($fp, 2);
        fwrite($fp, "<?PHP exit;?>\t" . str_replace(array('<?', '?>', "\r", "\n"), '', $log) . "\n");
        fclose($fp);
    }
    if ($halt)
        exit();
}

/* 翻页函数 */

function page($num, $perpage, $curpage, $operation) {
    global $setting;
    $multipage = '';

    $mpurl = SITE_URL . $setting['seo_prefix'] . $operation . '/';
    ('admin' == substr($operation, 0, 5)) && ( $mpurl = 'index.php?' . $operation . '/');

    if ($num > $perpage) {
        $page = 10;
        $offset = 2;
        $pages = @ceil($num / $perpage);
        if ($page > $pages) {
            $from = 1;
            $to = $pages;
        } else {
            $from = $curpage - $offset;
            $to = $from + $page - 1;
            if ($from < 1) {
                $to = $curpage + 1 - $from;
                $from = 1;
                if ($to - $from < $page) {
                    $to = $page;
                }
            } elseif ($to > $pages) {
                $from = $pages - $page + 1;
                $to = $pages;
            }
        }
        $multipage = ($curpage - $offset > 1 && $pages > $page ? '<a  class="n" href="' . $mpurl . '1' . $setting['seo_suffix'] . '" >首页</a>' . "\n" : '') .
                ($curpage > 1 ? '<a href="' . $mpurl . ($curpage - 1) . $setting['seo_suffix'] . '"  class="n">上一页</a>' . "\n" : '');
        for ($i = $from; $i <= $to; $i++) {
             $multipage .= $i == $curpage ? "<strong>$i</strong>\n" :
                    '<a href="' . $mpurl . $i . $setting['seo_suffix'] . '">' . $i . '</a>' . "\n";
        }
        $multipage .= ( $curpage < $pages ? '<a class="n" href="' . $mpurl . ($curpage + 1) . $setting['seo_suffix'] . '">下一页</a>' . "\n" : '') .
                ($to < $pages ? '<a class="n" href="' . $mpurl . $pages . $setting['seo_suffix'] . '" >最后一页</a>' . "\n" : '');
    }
    return $multipage;
}
function page_url($text, $operation) 
{
    global $setting;
    $mpurl = SITE_URL . $setting['seo_prefix'] . $operation;
	$multipage = '<a class="n" href="' . $mpurl . $setting['seo_suffix'] .'" >'.$text.'</a>' . "\n";
    return $multipage;
}

/**
 *  ajax分页
 * @param  $num 总行数
 * @param  $perpage 每页行数
 * @param  $curpage 当前页
 * @return string  分页字符串
 */
function ajax_page($num, $perpage, $curpage, $operation, $type='',$qtype=0) {
	global $setting;
	$multipage = '';
	$mpurl = SITE_URL . $setting['seo_prefix'] . $operation . '/';
	if ($num > $perpage) {
		$page = 8;
		$offset = 2;
		$pages = @ceil($num / $perpage); // 总页数
		if ($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if ($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if ($to - $from < $page) {
					$to = $page;
				}
			} elseif ($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}
		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a  href="javascript:gotopage(\''.$mpurl.'\',1,\''.$type.'\',\''.$qtype.'\');">首页</a>': '') .
		($curpage > 1 ? '<a href="javascript:gotopage(\''.$mpurl.'\','.($curpage - 1).',\''.$type.'\',\''.$qtype.'\');" class="n">上一页</a>': '');
		for ($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? '<span class="current">' . $i . '</span>' :
			'<a href="javascript:gotopage(\''.$mpurl.'\','.$i.',\''.$type.'\',\''.$qtype.'\');" >' . $i . '</a>';
		}
		$multipage .= ( $curpage < $pages ? '<a class="n" href="javascript:gotopage(\''.$mpurl.'\','.($curpage + 1).',\''.$type.'\',\''.$qtype.'\');">下一页</a>' : '') .
		($to < $pages ? '<a href="javascript:gotopage(\''.$mpurl.'\','.$pages.',\''.$type.'\',\''.$qtype.'\');">最后一页</a>' : '')  .
		'<span class="page-skip">到第<input type="text" name="gotoPage" class="goto" onmouseover="this.focus();this.select();"/>页<button type="button" onclick="gotopage(\''.$mpurl.'\',$(this).prev().val(),\''.$type.'\',\''.$qtype.'\');" >确认</button></span>' ;
	}

	return $multipage;
}
/**
 *  ajax分页
 * @param  $num 总行数
 * @param  $perpage 每页行数
 * @param  $curpage 当前页
 * @return string  分页字符串
 */
function ajax_page_single($text, $perpage, $curpage, $operation, $type='',$qtype=0) 
{
	global $setting;
	$multipage = '';
	$mpurl = SITE_URL . $setting['seo_prefix'] . $operation . '/';
			$multipage = '<a class="n" href="javascript:gotopage(\''.$mpurl.'\',1,\''.$type.'\',\''.$qtype.'\');">'.$text.'</a>';
	return $multipage;
}

//投诉订单分单
function ajax_ts_page($num, $perpage, $curpage, $type='') {
	global $setting;
	$multipage = '';
	if ($num > $perpage) {
		$page = 8;
		$offset = 2;
		$pages = @ceil($num / $perpage); // 总页数
		if ($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if ($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if ($to - $from < $page) {
					$to = $page;
				}
			} elseif ($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}
		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a  href="javascript:search_order(\''.$type.'\',1);">首页</a>': '') .
		($curpage > 1 ? '<a href="javascript:search_order(\''.$type.'\','.($curpage - 1).');" class="n">上一页</a>': '');
		for ($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? '<span class="current">' . $i . '</span>' :
			'<a href="javascript:search_order(\''.$type.'\','.$i.');" >' . $i . '</a>';
		}
		$multipage .= ( $curpage < $pages ? '<a class="n" href="javascript:search_order(\''.$type.'\','.($curpage + 1).');">下一页</a>' : '') .
		($to < $pages ? '<a href="javascript:search_order(\''.$type.'\','.$pages.');">最后一页</a>' : '')  .
		'<span class="page-skip">到第<input type="text" name="gotoPage" class="goto" onmouseover="this.focus();this.select();"/>页<button type="button" onclick="search_order(\''.$type.'\',$(this).prev().val());" >确认</button></span>' ;
	}

	return $multipage;
}

/* 过滤关键词 */

function checkwords($content) {
    global $setting, $badword;
    $status = 0;
    $text = $content;
    if (!empty($badword)) {
        foreach ($badword as $word => $wordarray) {
            $replace = $wordarray['replacement'];
            $content = str_replace($word, $replace, $content, $matches);
            if ($matches > 0) {
                '{MOD}' == $replace && $status = 1;
                '{BANNED}' == $replace && $status = 2;
                if ($status > 0) {
                    $content = $text;
                    break;
                }
            }
        }
    }
//$content = str_replace(array("\r\n", "\r", "\n"), '<br />', htmlentities($content));
    return array($status, $content);
}

/* http请求 */

function topen($url, $timeout = 15, $post = '', $cookie = '', $limit = 0, $ip = '', $block = TRUE) {
    $return = '';
    $matches = parse_url($url);
    $host = $matches['host'];
    $path = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
    $port = !empty($matches['port']) ? $matches['port'] : 80;
    if ($post) {
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
//$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= 'Content-Length: ' . strlen($post) . "\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cache-Control: no-cache\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
        $out .= $post;
    } else {
        $out = "GET $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
//$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
    }
    $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
    if (!$fp) {
        return '';
    } else {
        stream_set_blocking($fp, $block);
        stream_set_timeout($fp, $timeout);
        @fwrite($fp, $out);
        $status = stream_get_meta_data($fp);
        if (!$status['timed_out']) {
            while (!feof($fp)) {
                if (($header = @fgets($fp)) && ($header == "\r\n" || $header == "\n")) {
                    break;
                }
            }
            $stop = false;
            while (!feof($fp) && !$stop) {
                $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                $return .= $data;
                if ($limit) {
                    $limit -= strlen($data);
                    $stop = $limit <= 0;
                }
            }
        }
        @fclose($fp);
        return $return;
    }
}

/* 发送邮件 */

function sendmail($touser, $subject, $message, $from = '') {
    global $setting;
    $toemail = $touser['email'];
    $tousername = $touser['username'];
    $message = <<<EOT
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset={TIPASK_CHARSET}">
		<title>$subject</title>
		</head>
		<body>
		hi, $tousername<br>
            $subject<br>
            $message<br>
		这封邮件由系统自动发送，请不要回复。
		</body>
		</html>
EOT;

    $maildelimiter = $setting['maildelimiter'] == 1 ? "\r\n" : ($setting['maildelimiter'] == 2 ? "\r" : "\n");
    $mailusername = isset($setting['mailusername']) ? $setting['mailusername'] : 1;
    $mailserver = $setting['mailserver'];
    $mailport = $setting['mailport'] ? $setting['mailport'] : 25;
    $mailsend = $setting['mailsend'] ? $setting['mailsend'] : 1;

    if ($mailsend == 3) {
        $email_from = empty($from) ? $setting['maildefault'] : $from;
    } else {
        $email_from = $from == '' ? '=?' . TIPASK_CHARSET . '?B?' . base64_encode($setting['site_name']) . "?= <" . $setting['maildefault'] . ">" : (preg_match('/^(.+?) \<(.+?)\>$/', $from, $mats) ? '=?' . TIPASK_CHARSET . '?B?' . base64_encode($mats[1]) . "?= <$mats[2]>" : $from);
    }

    $email_to = preg_match('/^(.+?) \<(.+?)\>$/', $toemail, $mats) ? ($mailusername ? '=?' . CHARSET . '?B?' . base64_encode($mats[1]) . "?= <$mats[2]>" : $mats[2]) : $toemail;
    ;

    $email_subject = '=?' . TIPASK_CHARSET . '?B?' . base64_encode(preg_replace("/[\r|\n]/", '', '[' . $setting['site_name'] . '] ' . $subject)) . '?=';
    $email_message = chunk_split(base64_encode(str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message))))));

    $headers = "From: $email_from{$maildelimiter}X-Priority: 3{$maildelimiter}X-Mailer: Tipask1.0 {$maildelimiter}MIME-Version: 1.0{$maildelimiter}Content-type: text/html; charset=" . TIPASK_CHARSET . "{$maildelimiter}Content-Transfer-Encoding: base64{$maildelimiter}";

    if ($mailsend == 1) {
        if (function_exists('mail') && @mail($email_to, $email_subject, $email_message, $headers)) {
            return true;
        }
        return false;
    } elseif ($mailsend == 2) {

        if (!$fp = fsockopen($mailserver, $mailport, $errno, $errstr, 30)) {
            runlog('SMTP', "($mailserver:$mailport) CONNECT - Unable to connect to the SMTP server", 0);
            return false;
        }
        stream_set_blocking($fp, true);

        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != '220') {
            runlog('SMTP', "($mailserver:$mailport) CONNECT - $lastmessage", 0);
            return false;
        }

        fputs($fp, ($setting['mailauth'] ? 'EHLO' : 'HELO') . " Tipask\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {
            runlog('SMTP', "($mailserver:$mailport) HELO/EHLO - $lastmessage", 0);
            return false;
        }

        while (1) {
            if (substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
                break;
            }
            $lastmessage = fgets($fp, 512);
        }

        if ($setting['mailauth']) {
            fputs($fp, "AUTH LOGIN\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 334) {
                runlog('SMTP', "($mailserver:$mailport) AUTH LOGIN - $lastmessage", 0);
                return false;
            }

            fputs($fp, base64_encode($setting['mailauth_username']) . "\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 334) {
                runlog('SMTP', "($mailserver:$mailport) USERNAME - $lastmessage", 0);
                return false;
            }

            fputs($fp, base64_encode($setting['mailauth_password']) . "\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 235) {
                runlog('SMTP', "($mailserver:$mailport) PASSWORD - $lastmessage", 0);
                return false;
            }

            $email_from = $setting['maildefault'];
        }

        fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from) . ">\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from) . ">\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 250) {
                runlog('SMTP', "($mailserver:$mailport) MAIL FROM - $lastmessage", 0);
                return false;
            }
        }

        fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail) . ">\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail) . ">\r\n");
            $lastmessage = fgets($fp, 512);
            runlog('SMTP', "($mailserver:$mailport) RCPT TO - $lastmessage", 0);
            return false;
        }

        fputs($fp, "DATA\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 354) {
            runlog('SMTP', "($mailserver:$mailport) DATA - $lastmessage", 0);
            return false;
        }

        $headers .= 'Message-ID: <' . gmdate('YmdHs') . '.' . substr(md5($email_message . microtime()), 0, 6) . rand(100000, 999999) . '@' . $_SERVER['HTTP_HOST'] . ">{$maildelimiter}";

        fputs($fp, "Date: " . gmdate('r') . "\r\n");
        fputs($fp, "To: " . $email_to . "\r\n");
        fputs($fp, "Subject: " . $email_subject . "\r\n");
        fputs($fp, $headers . "\r\n");
        fputs($fp, "\r\n\r\n");
        fputs($fp, "$email_message\r\n.\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            runlog('SMTP', "($mailserver:$mailport) END - $lastmessage", 0);
        }
        fputs($fp, "QUIT\r\n");

        return true;
    } elseif ($mailsend == 3) {

        ini_set('SMTP', $mailserver);
        ini_set('smtp_port', $mailport);
        ini_set('sendmail_from', $email_from);

        if (function_exists('mail') && @mail($email_to, $email_subject, $email_message, $headers)) {
            return true;
        }
        return false;
    }
}

/* 取得一个字符串的拼音表示形式 */

function getpinyin($str, $ishead=0, $isclose=1) {
    if (!function_exists('gbk_to_pinyin')) {
        require_once(TIPASK_ROOT . '/lib/iconv.func.php');
    }
    if (TIPASK_CHARSET == 'utf-8') {
        $str = utf8_to_gbk($str);
    }
    return gbk_to_pinyin($str, $ishead, $isclose);
}

/* 得到一个分类的getcategorypath，一直到根分类 */

function getcategorypath($cid) {
    global $category;
    $item = $category[$cid];
    $dirpath = $item['dir'];
    while (true) {
        if (0 == $item['pid']) {
            break;
        } else {
            $item = $category[$item['pid']];
        }
        $dirpath = $item['dir'] . '/' . $dirpath;
    }
    return $dirpath;
}

/* 得到问题纯静态的存储路径 */

function getstaticurl($question) {
    global $setting, $category;
    $staticurl = $setting['static_url'];
    $repacearray = array(
        'typedir' => getcategorypath($question['cid']),
        'timestamp' => $question['time'],
        'Y' => date('Y', $question['time']),
        'M' => date('m', $question['time']),
        'D' => date('d', $question['time']),
        'qid' => $question['id'],
        'pinyin' => getpinyin($question['title']) . '_' . $question['id'],
        'py' => getpinyin($question['title'], 1) . '_' . $question['id'],
        'cc' => date('md', $question['time']) . '_' . md5($question['id']),
    );
    foreach ($repacearray as $search => $replace) {
        $staticurl = str_replace($search, $replace, $staticurl);
    }
    return $staticurl;
}

/* 数组类型，是否是向量类型 */

function isVector(&$array) {
    $next = 0;
    foreach ($array as $k => $v) {
        if ($k !== $next)
            return false;
        $next++;
    }
    return true;
}

/* 自己定义tjson_encode */

function tjson_encode($value) {
    switch (gettype($value)) {
        case 'double':
        case 'integer':
            return $value > 0 ? $value : '"' . $value . '"';
        case 'boolean':
            return $value ? 'true' : 'false';
        case 'string':
            return '"' . str_replace(
                            array("\n", "\b", "\t", "\f", "\r"), array('\n', '\b', '\t', '\f', '\r'), addslashes($value)
                    ) . '"';
        case 'NULL':
            return 'null';
        case 'object':
            return '"Object ' . get_class($value) . '"';
        case 'array':
            if (isVector($value)) {
                if (!$value) {
                    return $value;
                }
                foreach ($value as $v) {
                    $result[] = tjson_encode($v);
                }
                return '[' . implode(',', $result) . ']';
            } else {
                $result = '{';
                foreach ($value as $k => $v) {
                    if ($result != '{')
                        $result .= ',';
                    $result .= tjson_encode($k) . ':' . tjson_encode($v);
                }
                return $result . '}';
            }
        default:
            return '"' . addslashes($value) . '"';
    }
}

/* 是否是外部url */

function is_outer($url) {
    $findstr = $domain = $_SERVER["HTTP_HOST"];
    $words = explode('.', $domain);
    if (count($words) > 2) {
        array_shift($words);
        $findstr = implode('.', $words);
    }
    return false === strpos($url, $findstr);
}

/* html中的是否包含外部url */

function has_outer($content) {
    $contain = false;
    if (!function_exists('file_get_html')) {
        require_once(TIPASK_ROOT . '/lib/simple_html_dom.php');
    }
    $html = str_get_html($content);
    $ret = $html->find('a');
    foreach ($ret as $a) {
        if (is_outer($a->href)) {
            $contain = true;
            break;
        }
    }
    $html->clear();
    return $contain;
}

/* 过滤外部url */

function filter_outer($content) {
    if (!function_exists('file_get_html')) {
        require_once(TIPASK_ROOT . '/lib/simple_html_dom.php');
    }
    $html = str_get_html($content);
    $ret = $html->find('a');
    foreach ($ret as $a) {
        if (is_outer($a->href)) {
            $a->outertext = $a->innertext;
        }
    }
    $content = $html->save();
    $html->clear();
    return $content;
}

/* 内存是否够用 */

function is_mem_available($mem) {
    $limit = trim(ini_get('memory_limit'));
    if (empty($limit))
        return true;
    $unit = strtolower(substr($limit, -1));
    switch ($unit) {
        case 'g':
            $limit = substr($limit, 0, -1);
            $limit *= 1024 * 1024 * 1024;
            break;
        case 'm':
            $limit = substr($limit, 0, -1);
            $limit *= 1024 * 1024;
            break;
        case 'k':
            $limit = substr($limit, 0, -1);
            $limit *= 1024;
            break;
    }
    if (function_exists('memory_get_usage')) {
        $used = memory_get_usage();
    }
    if ($used + $mem > $limit) {
        return false;
    }
    return true;
}

//图片处理函数
/* 根据扩展名判断是否图片 */
function isimage($extname) {
    return in_array($extname, array('jpg', 'jpeg', 'png', 'gif', 'bmp'));
}

function image_resize($src, $dst, $width, $height, $crop=0) {
    if (!list($w, $h) = getimagesize($src))
        return "Unsupported picture type!";

    $type = strtolower(substr(strrchr($src, "."), 1));
    if ($type == 'jpeg')
        $type = 'jpg';
    switch ($type) {
        case 'bmp': $img = imagecreatefromwbmp($src);
            break;
        case 'gif': $img = imagecreatefromgif($src);
            break;
        case 'jpg': $img = imagecreatefromjpeg($src);
            break;
        case 'png': $img = imagecreatefrompng($src);
            break;
        default : return false;
    }
// resize
    if ($crop) {
        if ($w < $width or $h < $height) {
            rename($src, $dst);
            return true;
        }
        $ratio = max($width / $w, $height / $h);
        $h = $height / $ratio;
        $x = ($w - $width / $ratio) / 2;
        $w = $width / $ratio;
    } else {
        if ($w < $width and $h < $height) {
            rename($src, $dst);
            return true;
        }
        $ratio = min($width / $w, $height / $h);
        $width = $w * $ratio;
        $height = $h * $ratio;
        $x = 0;
    }
    $new = imagecreatetruecolor($width, $height);
// preserve transparency
    if ($type == "gif" or $type == "png") {
        imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
        imagealphablending($new, false);
        imagesavealpha($new, true);
    }

    imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);

    switch ($type) {
        case 'bmp': imagewbmp($new, $dst);
            break;
        case 'gif': imagegif($new, $dst);
            break;
        case 'jpg': imagejpeg($new, $dst);
            break;
        case 'png': imagepng($new, $dst);
            break;
    }
    return true;
}

function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale) {
    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
    $thumb_image_name = TIPASK_ROOT . $thumb_image_name;
    $imageType = image_type_to_mime_type($imageType);
    $newImageWidth = ceil($width * $scale);
    $newImageHeight = ceil($height * $scale);
    $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
    switch ($imageType) {
        case "image/gif":
            $source = imagecreatefromgif($image);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            $source = imagecreatefromjpeg($image);
            break;
        case "image/png":
        case "image/x-png":
            $source = imagecreatefrompng($image);
            break;
    }
    imagecopyresampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);
    switch ($imageType) {
        case "image/gif":
            imagegif($newImage, $thumb_image_name);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            imagejpeg($newImage, $thumb_image_name);
            break;
        case "image/png":
        case "image/x-png":
            imagepng($newImage, $thumb_image_name);
            break;
    }
    chmod($thumb_image_name, 0777);
    return $thumb_image_name;
}

/**
 * 获取内容中的第一张图
 * @param unknown_type $string
 * @return unknown|string
 */
function getfirstimg(&$string) {
    preg_match("/<img.+?src=[\\\\]?\"(.+?)[\\\\]?\"/i", $string, $imgs);
    if (isset($imgs[1])) {
        return $imgs[1];
    } else {
        return "";
    }
}

function highlight($content, $words, $highlightcolor='red') {
    $wordlist = explode(" ", $words);
    foreach ($wordlist as $hightlightword) {
        if (strlen($content) < 1 || strlen($hightlightword) < 1) {
            return $content;
        }
        $content = preg_replace("/$hightlightword/is", "<font color=red>\\0</font>;", $content);
    }
    return $content;
}

function do_post($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_URL, $url);
    $ret = curl_exec($ch);

    curl_close($ch);
    return $ret;
}

function get_url_contents($url) {
    if (ini_get("allow_url_fopen") == "1")
        return file_get_contents($url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function get_remote_image($url, $savepath) {
    ob_start();
    readfile($url);
    $img = ob_get_contents();
    ob_end_clean();
    $size = strlen($img);
    $fp2 = @fopen(TIPASK_ROOT . $savepath, "a");
    fwrite($fp2, $img);
    fclose($fp2);
    return $savepath;
}

//二次开发
//异步请求fsockopen实现
function async_topen($url, $post = '', $timeout = 15, $cookie = '', $ip = ''){
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;
	if ($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: ' . strlen($post) . "\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if ($fp !== false) {
		@fwrite($fp, $out);
		@fclose($fp);
	} 
}

//异步请求curl实现
function async_post($url, $data) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	
	curl_exec($ch);
	curl_close($ch);
}
 
//获取当前页面的URL
function curPageURL(){
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } 
    else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

//过滤掉特殊字符+-&|!(){}[]^~*?:
function  search_addcslashes($search){	
	strpos($search,'+') !== false && $search = addcslashes($search,'+');
	strpos($search,'-') !== false && $search = addcslashes($search,'-');
	strpos($search,'&') !== false && $search = addcslashes($search,'&');
	strpos($search,'|') !== false && $search = addcslashes($search,'|');
	strpos($search,'!') !== false && $search = addcslashes($search,'!');
	strpos($search,'(') !== false && $search = addcslashes($search,'(');
	strpos($search,')') !== false && $search = addcslashes($search,')');
	strpos($search,'{') !== false && $search = addcslashes($search,'{');
	strpos($search,'}') !== false && $search = addcslashes($search,'}');
	strpos($search,'[') !== false && $search = addcslashes($search,'[');
	strpos($search,']') !== false && $search = addcslashes($search,']');
	strpos($search,'^') !== false && $search = addcslashes($search,'^');
	strpos($search,'~') !== false && $search = addcslashes($search,'~');
	strpos($search,'*') !== false && $search = addcslashes($search,'*');
	strpos($search,'?') !== false && $search = addcslashes($search,'?');
	strpos($search,':') !== false && $search = addcslashes($search,':');
	strpos($search,'/') !== false && $search = addcslashes($search,'/');
	return $search;
}

//还原特殊字符显示+-&|!(){}[]^~*?:
function  search_stripcslashes($search){
    $search = stripcslashes($search);	
	return $search;
}

//获取当前域名
function getServerName(){
	$ServerName = strtolower($_SERVER['SERVER_NAME']?$_SERVER['SERVER_NAME']:$_SERVER['HTTP_HOST']);
	if( strpos($ServerName,'http://') ){
		return str_replace('http://','',$ServerName);
	}
	return $ServerName;
}

/**
 * 得到系统操作日志编号
 * @return  string
 */
function get_log_sn()
{
	return date('YmdHis').sprintf("%04d",rand(1,9999));
	/* 选择一个随机的方案 */
	mt_srand((double) microtime() * 1000000);

	return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
/**
 * 根据传进来的秒数，返回对应00:00:00
 * @return string
 */
function getHour($seconds){
	if(intval($seconds)<=0){
		return '';
	}else{
		$h = floor($seconds/3600);
		$m = floor($seconds%3600/60);
		$s = $seconds%3600%60;
		$m<10 && $m='0'.$m;
		$s<10 && $s='0'.$s;
		return $h.': '.$m.': '.$s;
	}

}

//站内信接口
function send_message($qid,$author_id,$author,$title=''){
	header("content-type:text/html;charset=utf-8");
	if(extension_loaded('mbstring')){
		if(mb_strlen($title,'UTF-8') > 10) $title = mb_substr($title,0,10,'UTF-8').'...';
	}else{
		$title = cutstr($title,20,'...');
	}
	try {
		$client = new SoapClient("http://message.5173.com/Service/SiteMessageWebService.asmx?wsdl");
		$header = new SoapHeader('http://5173.com/',
				'SiteMessageSoapHead',
				array('DomainName' => 'scadmin.5173.com',
						'UserId' => 'scadmin',
						'Password' => 'scadmin'
				)
		);
		$client->__setSoapHeaders($header);
		$params = array('siteMessage'=>array(
				'PolicyId' => 'KfScadmin',
				'ReceiverId' => $author_id,//用户ID
				'ReceiverName' => $author,//用户名
				'SenderId' => '5173.com',
				'SenderName' => '5173.com',
				'RelatedId' => '5173.com',
				'RelatedType' => 'Bk.Core.Imp.OrderExtended',
				'ReceiverType' => 'User',
				'MsgLinkParams' => array('http://'.config::FRONT_DOMAIN.'/index.php?question/detail/'.$qid),//站内信标题跳转地址
				'MsgListTitleParams' => array($title),//连接标题参数（你自己传入的内容）
				'OperationLinkParams' => array('http://'.config::FRONT_DOMAIN.'/index.php?question/detail/'.$qid),//点击按钮 立即查看 跳转地址
				'PolicyTitleParams' => null,
				'MsgContentParams' => null,
		),
				'sync'=>false
		);
		$client->__soapCall("SendSiteMessage", array('parameters'=>$params));
	} catch (SoapFault $ex) {		
		send_AIC('http://scadmin.5173.com','发送站内信异常',1,'站内信接口');
		exit('1');
	}
}

//AIC接口
function send_AIC($requestrul='',$message='',$messagetype=1,$shortmessage=''){
	$data_AIC=array(
			'ServerIP'      => $_SERVER["SERVER_ADDR"],
			'ClientIP'      => $_SERVER["REMOTE_ADDR"],
			'RequestURL'    => $requestrul,
			'Message'       => $message,
			'MessageType'   => $messagetype,
			'ShortMessage'  => $shortmessage,
			'Domain'        => getServerName()
	);
	$json = sprintf("aicdata=%s",json_encode($data_AIC,true));
	topen("http://searchmonitor.5173esb.com:888/AicSimple.ashx",15,$json);
}

//获取登陆用户绑定的手机号
function get_mobile($user_id){
	$m_url = "http://usercenter.5173esb.com/service/GetUserBindMobileForSc?UserId=".$user_id;
	$m_rs = topen($m_url);
	$m_result = base64_decode($m_rs);
	return $m_result;
}

//获取登陆用户的真实姓名
function get_realname($user_id){
	$r_url = "http://usercenter.5173esb.com/Service/GetRealNameForSc?UserId=".$user_id;
	$r_rs = topen($r_url);
	$r_result = base64_decode($r_rs);
	return $r_result;
}

function en_chinese($str){
	$len = mb_strlen($str,'UTF-8');
	$last = mb_substr($str,$len-1,1,'UTF-8');
	return str_repeat('*',$len-1).$last;	
}

function order_status($v){
	$msg = '';
	switch ($v)
	{
	 case 0:
	 	$msg = "交易中 ";
	 	break;
	 case 1:
	 	$msg = "撤单申请";
	 	break;
	 case 2:
	 	$msg = "已撤单";
	 	break;
	 case 3:
		$msg = "卖家确认移交";
		break;
	 case 4:
	 	$msg = "移交给买家";
	 	break;
 	 case 5:
 	 	$msg = "交易成功";
 	 	break;
	}
	return $msg;
}

function basicType($v){
	$msg = '';
	switch ($v)
	{
	 case 0:
	 	$msg = "装备";
	 	break;
	 case 1:
	 	$msg = "游戏币";
	 	break;
	 case 2:
	 	$msg = "ID交易";
	 	break;
	 case 3:
	 	$msg = "升级";
	 	break;
	 case 4:
	 	$msg = "包裹";
	 	break;
	 case 5:
	 	$msg = "点卡";
	 	break;
 	 case 6:
 		$msg = "网店";
 		break;
 	 case 7:
 		$msg = "密保卡";
 		break;
 	 case 8:
 		$msg = "激活码";
 		break;
 	 case 9:
 		$msg = "手机充值卡";
 		break;
 	 case 10:
 		$msg = "其它";
 		break;
 	 case 11:
 		$msg = "元宝类";
 		break;
	}
	return $msg;
}

function tradingType($v){
	$msg = '';
	switch ($v)
	{
		case 1:
			$msg = "担保交易";
			break;
		case 2:
			$msg = "寄售交易";
			break;
		case 4:
			$msg = "点卡交易";
			break;
		case 5:
			$msg = "账号交易";
			break;		
	}
	return $msg;
}

//提问id，分类写入cookie
function get_que_id($q_cid,$id){
	$ask_type = unserialize(stripslashes($_COOKIE['quickask']));
	if(is_array($ask_type)){
		if(!empty($ask_type[$q_cid])) {
			$ask_type[$q_cid] .= ",$id";
			setcookie('quickask',serialize($ask_type),time()+604800);
		} else {
			$quickask[$q_cid] = $id;
			$type = array_merge($ask_type,$quickask);
			setcookie('quickask',serialize($type),time()+604800);
		}
	}else{
		$quickask[$q_cid] = $id;
		setcookie('quickask',serialize($quickask),time()+604800);
	}

}
function __msg($backReturn)
{
	echo "<script type='text/javascript'>alert('{$backReturn['comment']}');if('{$backReturn['url']}'=='?admin_main'){window.top.location.href='{$backReturn['url']}';}else if('{$backReturn['url']}'=='?admin_question/handle'){window.parent.location.href='{$backReturn['url']}';}else{window.location.href='{$backReturn['url']}';}</script>";
}
// 获取配置文件
function getConfig()
{
	static $onlineconfig = array();
	$onlineconfig = require TIPASK_ROOT.'/onlineConfig.php';
	return $onlineconfig;
}
function isQQ($qq)
{
	return preg_match('/^[1-9][0-9]{4,11}$/', $qq);
}
function checkmobile($mobilephone)
{
	if(preg_match("/^13[0-9]{1}[0-9]{8}$|15[0-9]{9}$|18[0-9]{9}$/",$mobilephone))
	{
		return true;//验证通过
	}
	else
	{
		return false; //手机号码格式不对
	}
}
function cutstr($str,$len,$replace = '...')
{
    $ascLen=strlen($str);

    $i = 0;
    $l = 0;
    for($i;$i<$ascLen;$i++)
    {
        if($l < 2*$len)
        {            
            $c=ord(substr($str,0,1));
            if($c>=127)
            {
                $ll = 2;    
            }
            else
            {
                $ll = 1;    
            }
            if(ord(substr($str,0,1)) >252){$p = 5;}elseif($c > 248){$p = 4;}elseif($c > 240){$p = 3;}elseif($c > 224){$p = 2;}elseif($c > 192){$p = 1;}else{$p = 0;}
        
            $truekey=substr($str,0,$p+1);
            
            if($truekey===false)
            {break;}       
        
            $splikey[]=$truekey;
            
            $str=substr($str,$p+1);
            $l+=$ll;             
        }
        else
        {
            break;    
        }        
    }
    if(strlen($str)>0)
    {
        return implode("",$splikey).$replace;             
    }
    else
    {
        return (implode("",$splikey));
    }
}
?>
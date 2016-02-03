<?php
/**
 * 模板解析类
 * @example Base_Template::factory()->get('Index_index');
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Template.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Template
{
	/**
	 * 模板文件绝对路径
	 * @var string
	 */
    protected $sourceFile;

    /**
     * 解析后的目标文件绝对路径
     * @var string
     */
    protected $cacheFile;

    /**
     * 模板内容
     * @var string
     */
    protected $source = '';

    /**
     * debug模式
     * @var boolean
     */
    protected $debug = true;

    /**
     * 开始标记
     * @var string
     */
    protected $startTag = '{';

    /**
     * 结束标记
     * @var string
     */
    protected $endTag = '}';

    /**
     * 命名空间
     * @var string
     */
    protected $nameSpace = 'tpl';

    /**
     * 模板文件后缀
     * @var string
     */
    protected $suffix = '.tpl';

    public static function factory($identification)
    {
    	return new self($identification);
    }

    /**
     * 获取解析后的模板文件绝对路径
     * @return string
     */
    public function get()
    {
        /**
         * 缓存模式
         */
        if (!$this->debug && is_file($this->cacheFile) && filemtime($this->cacheFile) > filemtime($this->sourceFile)) {
            return $this->cacheFile;
        }

        $this->getSource();
        $this->parseSource();

        file_put_contents($this->cacheFile, $this->source);
				@chmod($this->cacheFile, 0644);

        return $this->cacheFile;
    }

    private function __construct($identification)
    {
        $this->sourceFile = Base_Common::$config['tpl_dir'] . str_replace('_', '/', $identification) . $this->suffix;

        if (!is_file($this->sourceFile)) {
            throw new Base_Exception("Template {$this->sourceFile} not exists!");
        }

        $this->cacheFile = Base_Common::$config['var_dir'] . 'tpl_cache/' . $identification . '_' . md5($this->sourceFile) . '.php';
    }

    public function setStartTag($tag)
    {
        $this->startTag = $tag;
    }

    public function setEndTag($tag)
    {
        $this->endTag = $tag;
    }

    public function setNameSpace($nameSpace)
    {
        $this->nameSpace = $nameSpace;
    }

    public function setDebug($debug = true)
    {
        $this->debug = $debug ? true : false;
    }

    /**
     * 显示模板源码
     * @return void
     */
    public function displaySource()
    {
        echo $this->source;
        exit();
    }

    /**
     * 获取模板内容
     * @return void
     */
    protected function getSource()
    {
        $this->source = file_get_contents($this->sourceFile);
    }

    /**
     * 模板解析
     * @return void
     */
    protected function parseSource()
    {

        /**
         * 模板嵌套
         */
        /**
         * {cms:tpl default_header /}
         */
        $this->source = preg_replace("/" . preg_quote($this->startTag . $this->nameSpace . ':', '/')
        . 'tpl\s+([a-z0-9_-]+)\s{0,}' . preg_quote('/' . $this->endTag, '/')
        . "/is", "<?php include Base_Common::tpl('\\1'); ?>", $this->source);

        /**
         * 赋值
         * 二维数组
         *
         * {cms:assign}{/cms:assign}
         */
        $this->source = preg_replace_callback("/" . preg_quote($this->startTag . $this->nameSpace . ':', '/')
        . 'assign\s+(.+?)' . preg_quote($this->endTag . $this->startTag . '/' . $this->nameSpace . ':', '/')
        . "assign" . preg_quote($this->endTag, '/') . "/is", array($this, 'assignParse'), $this->source);

        /**
         * if,elseif,else
         *
         * {cms:else}
         */
        $this->source = preg_replace("/" . preg_quote($this->startTag . $this->nameSpace . ':', '/')
        . 'else' . preg_quote($this->endTag, '/') . "/is", "<?php } else { ?>", $this->source);

        /**
         * {/cms:if}
         */
        $this->source = preg_replace("/" . preg_quote($this->startTag . '/' . $this->nameSpace . ':', '/')
        . 'if' . preg_quote($this->endTag, '/') . "/is", "<?php } ?>", $this->source);

        /**
         * {cms:else if ($i == 1)}
         */
        $this->source = preg_replace_callback("/" . preg_quote($this->startTag . $this->nameSpace . ':', '/')
        . 'else\s{0,}if\s{0,}\((.+?)\)\s{0,}' . preg_quote($this->endTag, '/')
        . "/is", array($this, 'elseifParse'), $this->source);

        /**
         * {cms:if ($i == 1)}
         */
        $this->source = preg_replace_callback("/" . preg_quote($this->startTag . $this->nameSpace . ':', '/')
        . 'if\s{0,}\((.+?)\)\s{0,}' . preg_quote($this->endTag, '/')
        . "/is", array($this, 'ifParse'), $this->source);

        /**
         * foreach
         *
         * {/cms:loop}
         */
        $this->source = preg_replace("/" . preg_quote($this->startTag . "/" . $this->nameSpace . ':', '/')
        . 'loop' . preg_quote($this->endTag, '/') . "/is", "<?php } } ?>", $this->source);

        // {cms:loop $array $value}
        $this->source = preg_replace_callback("/" . preg_quote($this->startTag . $this->nameSpace . ':', '/')
        . "loop\s+(\S+)\s+(\S+)\s{0,}" . preg_quote($this->endTag, '/')
        . "/is", array($this, 'loopValueParse'), $this->source);

        /**
         * {cms:loop $array $key $value}
         */
        $this->source = preg_replace_callback("/" . preg_quote($this->startTag . $this->nameSpace . ':', '/')
        . 'loop\s+(\S+)\s+(\S+)\s+(\S+)\s{0,}' . preg_quote($this->endTag)
        . "/is", array($this, 'loopKeyValueParse'), $this->source);

        /**
         * this,config 输出
         *
         * {cms:$config.siteUrl/}
         */
        $this->source = preg_replace_callback("/" . preg_quote($this->startTag . $this->nameSpace . ':', '/')
        . '\$config\.([a-z0-9_\.]+)\s{0,}' . preg_quote('/' . $this->endTag, '/')
        . "/is", array($this, 'echoConfigParse'), $this->source);

        /**
         * {cms:$this.a.b/}
         */
        $this->source = preg_replace_callback("/" . preg_quote($this->startTag . $this->nameSpace . ':', '/')
        . '\$this\.([a-z0-9_\.]+)\s{0,}' . preg_quote('/' . $this->endTag, '/')
        . "/is", array($this, 'echoThisParse'), $this->source);

        /**
         * 变量输出
         *
         * {cms:$a.b/}
         */
        $this->source = preg_replace_callback("/" . preg_quote($this->startTag . $this->nameSpace . ':', '/')
        . '\$(.+?)\s?' . preg_quote('/' . $this->endTag, '/') . "/is", array($this, 'echoVarParse'), $this->source);

        if (!$this->debug) {
            $this->source = preg_replace("/\?\>\<\?php/is", '', $this->source);
        }
    }

    /**
     * 赋值
     *
     * @param array $matches
     * @return string
     */
    private function assignParse($matches)
    {
        $string = $matches[1];

        /**
         * this, config, 变量
         */
        $string = preg_replace_callback("/\\\$config\.([a-z0-9_\.]+)/is", array($this, 'configStringParse'), $string);
        $string = preg_replace_callback("/\\\$this\.([a-z0-9_\.]+)/is", array($this, 'thisStringParse'), $string);
        $string = preg_replace_callback("/\\\$([a-z0-9_\.]+)/is", array('self', 'varStringParse'), $string);

        /**
         * 解析赋值字符串
         */
        $string = preg_replace("/var=\"(.+?)\"/is", '\\1', $string);
        $return = self::xmlParse($string);

        /**
         * 变量名
         */
        $var = key($return);

        $params = $return[$var];

        /**
         * 调用函数
         */
        $func = $params['func'];
        unset($params['func']);
        $funcArr = explode(',', $func);
        $funcArr[0] = trim($funcArr[0]);
        if (2 == count($funcArr)) {

            /**
             * 处理对象
             */
            $symbol = ($funcArr[0] == 'self' || substr($funcArr[0], 0, 1) == '$') ? '' : "'";

            $func = 'array(' . $symbol . $funcArr[0] . $symbol . ', \'' . trim($funcArr[1]) . '\')';
        } else {
            $func = "'" . trim($funcArr[0]) . "'";
        }

        /**
         * 参数
         */
        $conditions = '';
        foreach ($params as $name => $value) {
            $conditions .= '$_conditions[\'' . $name . '\'] = ';
            if (is_numeric($value)) {
                $conditions .= $value .';';
            } else {
                if (substr($value, 0, 1) == '$') {
                    $conditions .= $value . ';';
                } else {
                    $conditions .= '"' . $value .'";';
                }
            }
        }

        return '<?php $_conditions = array();' . $conditions . '$' . $var . ' = call_user_func(' . $func . ', $_conditions); ?>';
    }

    /**
     * $config.a.b -> $this->config->a['b']
     *
     * @param array $matches
     * @return string
     */
    private function configStringParse($matches)
    {
        return '$this->config->' . substr(self::varStringParse($matches), 1);
    }

    /**
     * $this.a.b -> $this->a['b']
     *
     * @param array $matches
     * @return string
     */
    private function thisStringParse($matches)
    {
        return '$this->' . substr(self::varStringParse($matches), 1);
    }

    /**
     * 替换 . 语法为数组语法
     * $a.b.c -> $a['b']['c']
     *
     * @param array $varString
     * @return string
     */
    private static function varStringParse($matches)
    {
        $varArr = explode('.', $matches[1]);
        $varString = $varArr[0];
        array_shift($varArr);
        foreach ($varArr as $key) {
            $symbol = is_numeric($key) ? '' : "'";
            $varString .= '[' . $symbol . $key . $symbol . ']';
        }

        return '$' . $varString;
    }

    /**
     * {u8:$a.b/}
     *
     * @param array $matches
     * @return string
     */
    private function echoVarParse($matches)
    {
        return '<?php echo ' . $this->varParse($matches) . '; ?>';
    }

    /**
     * {u8:this.a.b/}
     *
     * @param array $matches
     * @return string
     */
    private function echoThisParse($matches)
    {
        return '<?php echo ' . $this->varParse($matches, 'this') . '; ?>';
    }

    /**
     * {u8:config.a/}
     *
     * @param array $matches
     * @return string
     */
    private function echoConfigParse($matches)
    {
        return '<?php echo ' . $this->varParse($matches, 'config') . '; ?>';
    }

    /**
     * 解析变量， 支持函数
     *
     * @param array $matches
     * @param string $key
     * @return string
     */
    private function varParse($matches, $key = NULL)
    {
        $string = $matches[1];

        $return = self::xmlParse($string);

        /**
         * 变量
         */
        $varString = key($return);
        $varArr = array(1 => $varString);
        if ($key == 'this') {
            $var = $this->thisStringParse($varArr);
        } elseif($key == 'config') {
            $var = $this->configStringParse($varArr);
        } else {
            $var = self::varStringParse($varArr);
        }

        /**
         * 处理函数
         */
        $func = isset($return[$varString]['func']) ? trim($return[$varString]['func']) : '';

        return empty($func) ? $var : str_replace('@@', $var, $func);
    }

    /**
     * foreach($arr as $key => $val)
     *
     * @param array $matches
     * @return string
     */
    public function loopKeyValueParse($matches)
    {
        $string = $matches[1];

        /**
         * this, config, 变量
         */
        $string = preg_replace_callback("/\\\$config\.([a-z0-9_\.]+)/is", array($this, 'configStringParse'), $string);
        $string = preg_replace_callback("/\\\$this\.([a-z0-9_\.]+)/is", array($this, 'thisStringParse'), $string);
        $string = preg_replace_callback("/\\\$([a-z0-9_\.]+)/is", array('self', 'varStringParse'), $string);

        return '<?php if (is_array(' . $string . ')) { foreach (' . $string . ' as ' . $matches[2] . ' => ' . $matches[3] . ') { ?>';
    }

    /**
     * foreach($arr as $var)
     *
     * @param array $matches
     * @return string
     */
    public function loopValueParse($matches)
    {
        $string = $matches[1];

        /**
         * this, config, 变量
         */
        $string = preg_replace_callback("/\\\$config\.([a-z0-9_\.]+)/is", array($this, 'configStringParse'), $string);
        $string = preg_replace_callback("/\\\$this\.([a-z0-9_\.]+)/is", array($this, 'thisStringParse'), $string);
        $string = preg_replace_callback("/\\\$([a-z0-9_\.]+)/is", array('self', 'varStringParse'), $string);

        return '<?php if (is_array(' . $string . ')) { foreach (' . $string . ' as ' . $matches[2] . ') { ?>';
    }

    /**
     * if
     *
     * @param array $matches
     * @return string
     */
    private function ifParse($matches)
    {
        $string = $matches[1];

        /**
         * this, config, 变量
         */
        $string = preg_replace_callback("/\\\$config\.([a-z0-9_\.]+)/is", array($this, 'configStringParse'), $string);
        $string = preg_replace_callback("/\\\$this\.([a-z0-9_\.]+)/is", array($this, 'thisStringParse'), $string);
        $string = preg_replace_callback("/\\\$([a-z0-9_\.]+)/is", array('self', 'varStringParse'), $string);

        return '<?php if(' . $string . ') { ?>';
    }

    /**
     * 解析elseif，支持变量
     *
     * @param array $matches
     * @return string
     */
    private function elseifParse($matches)
    {
        $string = $matches[1];

        /**
         * this, config, 变量
         */
        $string = preg_replace_callback("/\\\$config\.([a-z0-9_\.]+)/is", array($this, 'configStringParse'), $string);
        $string = preg_replace_callback("/\\\$this\.([a-z0-9_\.]+)/is", array($this, 'thisStringParse'), $string);
        $string = preg_replace_callback("/\\\$([a-z0-9_\.]+)/is", array('self', 'varStringParse'), $string);

        return '<?php } else if (' . $string . ') { ?>';
    }

    private static function xmlParse($string)
    {
        $return = array();

        require_once 'Base/Xml.php';
        $xml = new Base_Xml();
        $string = '<?xml version="1.0" encoding="utf-8"?>' . "\n<root>\n" . '<' . $string . '/>' . "\n</root>";

        $xml->setEncode('UTF-8');
        $xml->setXMLData($string);

        if (!$xml->isXMLFile()) {
            print_r('模板标记错误: %s', $string);
            exit();
        }

        if ($xml->parse()) {
            $result = Base_Xml::getChild($xml->getXMLRoot());
            foreach ($result as $tag) {
                $tagname = Base_Xml::getTagName($tag);
                $return[$tagname] = Base_Xml::getAttribute($tag);
            }
        }

        return $return;
    }

}

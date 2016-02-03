<?php
/**
 * xml解析类
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Xml.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Xml
{
    /**
     * 解析指针
     *
     * @var resource
     */
    private $parser;

    private $XMLData;

    /**
     * 错误信息
     *
     * @var string
     */
    private $error;

    /**
     * xml编码
     *
     * @var string
     */
    private $encode;

    private $stack;

    public function __construct($encode = '')
    {
        $this->encode = $encode ? $encode : 'UTF-8';
        $this->XMLData = '';
        $this->error = '';
        $this->stack = array();
    }

    /**
     * 源XML数据
     *
     * @param string $data
     */
    public function setXMLData($data)
    {
        $this->XMLData = trim($data);
    }

    /**
     * 根据指定URL读取XML数据
     *
     * @param string $url
     */
    public function setXMLUrl($url)
    {
        $this->XMLData = trim(file_get_contents($url));
    }

    /**
     * Sets an option in an XML parser
     *
     * @param integer $option
     * @param mixed $value
     */
    public function setOption($option, $value)
    {
        xml_parser_set_option($this->parser, $option, $value);
    }

    /**
     * 是否为xml格式文件
     *
     * @return boolean
     */
    public function isXMLFile()
    {
        return (strpos(strtolower($this->XMLData), '<?xml') !== false);
    }

    /**
     * 设置XML编码
     *
     * @param string $encode
     */
    public function setEncode($encode)
    {
        $this->encode = $encode;
    }

    /**
     * 获取现有XML编码
     *
     * @return string
     */
    public function getEncode()
    {
        if(empty($this->encode))
        {
            $this->getXMLEncode();
        }
        return $this->encode;
    }

    /**
     * 获取XML数据的编码
     *
     * @return string
     */
    private function getXMLEncode()
    {
        $start = strpos($this->XMLData, '<?xml');
        $end = strpos($this->XMLData, '>');
        $str = substr($this->XMLData, $start, $end - $start);
        $pos = strpos($str, 'encoding');
        if($pos !== false)
        {
            $str = substr($str, $pos);
            $pos = strpos($str, '=');
            $str = substr($str, $pos + 1);
            $str = trim($str);
            $pos = 0;
            $this->encode = '';
            while(!empty($str[$pos]) && $str[$pos] != '?')
            {
                if($str[$pos] != '"' && $str[$pos] != "'")
                {
                    $this->encode .= $str[$pos];
                }
                $pos++;
            }
        }
        return $this->encode;
    }

    /**
     * Gets the current line number for the given XML parser
     *
     * @return integer
     */
    private function getLineNumber()
    {
        return xml_get_current_line_number($this->parser);
    }

    /**
     * Gets the current column number of the given XML parser
     *
     * @return integer
     */
    private function getColumnNumber()
    {
        return xml_get_current_column_number($this->parser);
    }

    /**
     * Gets the current byte index of the given XML parser
     *
     * @return integer
     */
    public function getCharacterOffset()
    {
        return xml_get_current_byte_index($this->parser);
    }

    private function _start_element($parser, $name, $attribs)
    {
        $tag = array('TagName' => $name, 'attribute' => $attribs);
        if(empty($this->stack))
        {
            $tag['parent'] = null;
            $tag['depth'] = 1;
        }
        array_push($this->stack, $tag);
    }

    private function _end_element($parser, $name)
    {
        $total = count($this->stack);
        if($total > 1)
        {
            $this->stack[$total - 1]['depth'] = $this->stack[$total - 2]['depth'] + 1;
            $this->stack[$total - 1]['parent'] = &$this->stack[$total - 2];
            $this->stack[$total - 2]['children'][] = $this->stack[$total - 1];
            array_pop($this->stack);
        }
    }

    private function _character_data($parser, $data)
    {
        $total = count($this->stack);
        if(isset($this->stack[$total - 1]['data']))
        {
            $this->stack[$total - 1]['data'] .= trim($data);
        }
        else
        {
            $this->stack[$total - 1]['data'] = trim($data);
        }
    }

    private function _create_parser()
    {
        if(empty($this->parser))
        {
            $this->parser = xml_parser_create($this->encode);
            xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
            xml_set_object($this->parser, $this);
            xml_set_element_handler($this->parser, array($this, '_start_element'), array($this, '_end_element'));
            xml_set_character_data_handler($this->parser, array($this, '_character_data'));
        }
    }

    /**
     * XML解析入口主函数
     *
     * @param string $data
     * @return boolean
     */
    public function parse($data = '')
    {
        $this->_create_parser();
        $data && $this->XMLData = $data;
        if(empty($this->XMLData))
        {
            $this->error = "XML error: XMLData is empty";
            return false;
        }

        if(!xml_parse($this->parser, $this->XMLData, true))
        {
            $column = $this->getColumnNumber();
            $line = $this->getLineNumber();
            $errorCode = xml_get_error_code($this->parser);
            $errorString = xml_error_string($errorCode);
            $this->error = "XML error: $column at line $line: $errorString";
            return false;
        }
        xml_parser_free($this->parser);
        return true;
    }

    /**
     * 返回根节点
     *
     * @return array
     */
    public function getXMLRoot()
    {
        return $this->stack[0];
    }

    /**
     * 返回解析后的数据文档数组
     *
     * @return array
     */
    public function getXMLDocument()
    {
        return $this->stack;
    }

    /**
     * 返回指定父节点下的所有子节点
     *
     * @param string $parentTagName
     * @return array
     */
    public function getTagChild($parentTagName = '')
    {
        if(empty($parentTagName))
        {
            return $this->stack[0]['children'];
        }
        else
        {
            $vector = array();
            $parentTag = $this->getElementsByTagName($parentTagName);
            foreach($parentTag as $tag)
            {
                if(count($tag['children']))
                {
                    array_push($vector, $tag['children']);
                }
            }
            return $vector;
        }
    }

    public function getTagByTagName($TagName)
    {
        return self::_getTagByTagName($this->stack[0], $TagName);
    }

    private static function _getTagByTagName($tree, $TagName)
    {
        if($tree['TagName'] == $TagName)
        {
            return $tree;
        }
        else
        {
            $total = count($tree['children']);
            for($i = 0; $i < $total; $i++)
            {
                $result = self::_getTagByTagName($tree['children'][$i], $TagName);
                if($result)
                {
                    return $result;
                }
            }
        }
        return false;
    }

    public function getElementsByTagName($TagName)
    {
        $vector = array();
        self::_getElementByTagName($this->stack[0], $TagName, $vector);
        return $vector;
    }

    private static function _getElementByTagName($tree, $TagName, &$vector)
    {
        if($tree['TagName'] == $TagName)
        {
            array_push($vector, $tree);
        }
        $total = count($tree['children']);
        for($i = 0; $i < $total; $i++)
        {
            self::_getElementByTagName($tree['children'][$i], $TagName, $vector);
        }
    }

    /**
     * 根据属性名name查找节点
     *
     * @param array $stack
     * @param string $name
     * @return array
     */
    public static function getChildByName($stack, $name)
    {
        $total = count($stack['children']);
        for($i = 0; $i < $total; $i++)
        {
            if($stack['children'][$i]['attribute']['name'] == $name)
            {
                return $stack['children'][$i];
            }
        }
        return false;
    }

    public static function getChildByTagName($stack, $TagName)
    {
        foreach($stack['children'] as $key => $value)
        {
            if($value['TagName'] == $TagName)
            {
                return $stack['children'][$key];
            }
        }
        return false;
    }

    /**
     * 在指定节点下根据标签名查找子节点
     *
     * @param array $stack
     * @param string $TagName
     * @return array
     */
    public static function getChildrenByTagName($stack, $TagName)
    {
        $vector = array();
        foreach($stack['children'] as $key => $value)
        {
            if($value['TagName'] == $TagName)
            {
                $vector[] = $stack['children'][$key];
            }
        }
        return $vector;
    }

    /**
     * 当前节点的子节点
     *
     * @param array $stack
     * @return array
     */
    public static function getChild($stack)
    {
        return $stack['children'];
    }

    /**
     * 节点属性表
     *
     * @param array $stack
     * @return array
     */
    public static function getAttribute($stack)
    {
        return $stack['attribute'];
    }

    /**
     * 节点指定属性值
     *
     * @param array $stack
     * @param string $name
     * @return string
     */
    public static function getProperty($stack, $name)
    {
        return $stack['attribute'][$name];
    }

    /**
     * 节点数据
     *
     * @param array $stack
     * @return string
     */
    public static function getData($stack)
    {
        return isset($stack['data']) ? $stack['data'] : null;
    }

    /**
     * 当前节点的父节点
     *
     * @param array $stack
     * @return array
     */
    public static function getParent($stack)
    {
        return $stack['parent'];
    }

    /**
     * 节点标签名称
     *
     * @param array $stack
     * @return string
     */
    public static function getTagName($stack)
    {
        return $stack['TagName'];
    }

    /**
     * 返回错误提示
     *
     * @return string
     */
    public function getXMLError()
    {
        return $this->error;
    }

}

<?php
/**
 * 异常
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Exception.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Exception extends Exception
{
    /**
     * 强制指定异常信息
     * @param string $message
     * @param integer $code
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

}

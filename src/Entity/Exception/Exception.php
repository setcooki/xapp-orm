<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../../core/core.php');

xapp_import('xapp.Orm.Exception');

/**
 * Orm entity exception class
 *
 * @package Orm
 * @subpackage Orm_Entity
 * @class Xapp_Orm_Entity_Exception
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Orm_Entity_Exception extends Xapp_Orm_Exception
{
    /**
     * error exception class constructor directs instance
     * to error xapp error handling
     *
     * @param string $message excepts error message
     * @param int $code expects error code
     * @param int $severity expects severity flag
     */
    public function __construct($message, $code = 0, $severity = XAPP_ERROR_ERROR)
    {
        parent::__construct($message, $code, $severity);
        xapp_error($this);
    }
}
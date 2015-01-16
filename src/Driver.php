<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Orm');

/**
 * Orm driver abstract base class
 *
 * @package Orm
 * @subpackage Orm_Driver
 * @class Xapp_Orm_Driver
 * @error 130
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Orm_Driver extends Xapp_Orm
{
    /**
     * return available pdo driver information
     *
     * @error 13001
     * @return array
     */
    public static function getDriver()
    {
        return PDO::getAvailableDrivers();
    }
}
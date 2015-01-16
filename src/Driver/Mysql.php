<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Orm.Driver');

/**
 * Orm driver mysql class
 *
 * @package Orm
 * @subpackage Orm_Driver
 * @class Xapp_Orm_Driver_Mysql
 * @error 131
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Orm_Driver_Mysql extends Xapp_Orm_Driver
{
    /**
     * overwrite parent init method to initialize pdo with mysql
     * and the correct connection charset
     *
     * @error 13101
     * @return void
     */
    protected function init()
    {
        parent::init();
        if(version_compare(PHP_VERSION, '5.3.6', '<'))
        {
            $this->pdo->exec("SET NAMES '".xapp_get_option(self::CHARSET, $this)."'");
            $this->pdo->exec("SET CHARACTER SET '".xapp_get_option(self::CHARSET, $this)."'");
        }
    }
}
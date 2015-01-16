<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../Core/core.php');

/**
 * Orm observer interface
 *
 * @package Orm
 * @subpackage Orm_Observer
 * @author Frank Mueller <set@cooki.me>
 */
interface Xapp_Orm_Observer_Interface
{
    /**
     * notify all observers of event passing parameter(s)
     *
     * @param string $event expects the event name
     * @param null|mixed $params expects optional parameter
     * @return mixed
     */
    public function notify($event, $params = null);
}
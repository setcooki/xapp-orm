<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

/**
 * Orm observer abstract class
 *
 * @package Orm
 * @class Xapp_Orm_Observer
 * @error 133
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Orm_Observer
{
    /**
     * contains all observer class instances
     *
     * @var array
     */
    protected $_observers = array();


    /**
     * attach observer to event to be called in the moment
     * the event is triggered. if observer is already attached
     * to event nothing will happens
     *
     * @error 13301
     * @param string $event expects the event name
     * @param Xapp_Orm_Observer_Interface $observer expects object that implements observer interface
     * @return void
     */
    public function attach($event, Xapp_Orm_Observer_Interface $observer)
    {
        $event = (string)$event;
        if(!isset($this->_observers[$event]))
        {
            $this->_observers[$event] = array();
        }
        if(!is_array($observer))
        {
            $observer = array($observer);
        }
        foreach($observer as $o)
        {
            foreach($this->_observers[$event] as $k => $v)
            {
                if($v === $o) break 2;
            }
            $this->_observers[$event][] = $o;
        }
    }


    /**
     * detach observer from event
     *
     * @error 13302
     * @param Xapp_Orm_Observer_Interface $observer expects object that implements observer interface
     * @param string $event expects the event name
     * @return void
     */
    public function detach(Xapp_Orm_Observer_Interface $observer, $event)
    {
        $tmp = array();

        if(!isset($this->_observers[$event]))
        {
            $this->_observers[$event] = array();
        }
        foreach($this->_observers[$event] as $o)
        {
            if($o !== $observer) $tmp[] = $o;
        }
        $this->_observers[$event] = $tmp;
    }


    /**
     * trigger event calling all observers attached to this event
     * and executing notify function defined in Xapp_Orm_Observer_Interface
     * interface
     *
     * @error 13303
     * @param string $event expects the event name
     * @param null|mixed $params expects optional parameters passed to observer notify function
     * @return void
     */
    final public function trigger($event, &$params = null)
    {
        if(!isset($this->_observers[$event]))
        {
            $this->_observers[$event] = array();
        }
        foreach($this->_observers[$event] as $o)
        {
            $o->notify($event, $params);
        }
    }
}
<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

/**
 * Orm expression class
 *
 * @package Orm
 * @class Xapp_Orm_Expression
 * @error 135
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Orm_Expression
{
    /**
     * expects the the expression statement
     *
     * @var null|mixed
     */
    protected $_expr = null;

    /**
     * expects the sprintf % parseable values
     *
     * @var array
     */
    protected $_values = array();


    /**
     * constructs instance and sets value
     *
     * @error 13501
     * @param mixed $expr expects the expression statement to set
     * @param null|string|array $values expects optional sprintf parseable values
     */
    public function __construct($expr, $values = null)
   	{
   		$this->_expr = $expr;
        if($values !== null)
        {
            $this->_values = (array)$values;
        }
   	}


    /**
     * short cut function to statically create new expression
     * passing expression statement and values to class constructor and return
     * instance
     *
     * @error 13502
     * @param mixed $expr expects the expression statement to set
     * @param null|string|array $values expects optional sprintf parseable values
     * @return Xapp_Orm_Expression
     */
    public static function create($expr, $values = null)
    {
        return new self($expr, $values);
    }


    /**
     * overwrite expression statement and values on the fly
     *
     * @error 13503
     * @param mixed $expr expects the expression statement to set
     * @param null|string|array $values expects optional sprintf parseable values
     * @return void
     */
    public function set($expr, $values = null)
    {
        $this->_expr = $expr;
        if($values !== null)
        {
            $this->_values = (array)$values;
        }
    }


    /**
     * get expression value
     *
     * @error 13504
     * @return mixed|null
     */
    public function get()
   	{
   		return xapp_sprintf($this->_expr, $this->_values);
   	}


    /**
     * set/getter method for expression statement
     *
     * @error 13505
     * @param null|mixed $expr expects the expression statement
     * @return mixed|null|Xapp_Orm_Expression
     */
    public function expr($expr = null)
    {
        if($expr === null)
        {
            return $this->_expr;
        }else{
            $this->_expr = $expr;
            return $this;
        }
    }


    /**
     * set/getter method for sprintf parseable values
     *
     * @error 13506
     * @param null|string|array $values expect the sprintf parseable values
     * @return mixed|null|Xapp_Orm_Expression
     */
    public function values($values = null)
    {
        if($values === null)
        {
            return $this->_values;
        }else{
            $this->_values = (array)$values;
            return $this;
        }
    }


    /**
     * instance to string conversion returns expression value
     *
     * @error 13507
     * @return string
     */
    final public function __toString()
   	{
   		return (string)$this->get();
    }
}
<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Orm.Filter');

/**
 * Orm join class
 *
 * @package Orm
 * @class Xapp_Orm_Join
 * @error 134
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Orm_Join
{
    /**
     * constant for inner joins
     *
     * @const INNER_JOIN
     */
    const INNER_JOIN                = 'INNER_JOIN';

    /**
     * constant for cross joins
     *
     * @const CROSS_JOIN
     */
    const CROSS_JOIN                = 'CROSS_JOIN';

    /**
     * constant for straight joins
     *
     * @const STRAIGHT_JOIN
     */
    const STRAIGHT_JOIN             = 'STRAIGHT_JOIN';

    /**
     * constant for left joins
     *
     * @const LEFT_JOIN
     */
    const LEFT_JOIN                 = 'LEFT_JOIN';

    /**
     * constant for left outer joins
     *
     * @const LEFT_OUTER_JOIN
     */
    const LEFT_OUTER_JOIN           = 'LEFT_OUTER_JOIN';

    /**
     * constant for right joins
     *
     * @const RIGHT_JOIN
     */
    const RIGHT_JOIN                = 'RIGHT_JOIN';

    /**
     * constant for right outer joins
     *
     * @const RIGHT_OUTER_JOIN
     */
    const RIGHT_OUTER_JOIN          = 'RIGHT_OUTER_JOIN';


    /**
     * contains table of join expression
     *
     * @var null|string
     */
    protected $_table = null;

    /**
     * contains type of join according to class constants
     *
     * @var null|string
     */
    protected $_type = null;

    /**
     * contains all join objects in array associated to this table and join type
     *
     * @var null|array
     */
    protected $_join = null;


    /**
     * sets table and join type
     *
     * @error 13401
     * @param $table
     * @param string $type
     */
    public function __construct($table, $type = self::INNER_JOIN)
    {
        $this->_table = $table;
        $this->_type = strtoupper(trim($type));
    }


    /**
     * set join object for join instance defining all necessary parameter
     * to build a valid join from
     *
     * @error 13402
     * @param string $column1 expects the left column of join expression
     * @param string $operator expects the operator of join expression
     * @param string $column2 expects the right column of join expression
     * @param string $connector expects optional condition connector
     * @return Xapp_Orm_Join
     */
    public function on($column1, $operator = '=' , $column2, $connector = 'AND')
    {
        $obj = new XO();
        $obj->mask = false;
        $obj->type = 'where';
        $obj->column = $column1;
        $obj->value = $column2;
        $obj->operator = Xapp_Orm_Filter::mapOperator($operator);
        $obj->connector = Xapp_Orm_Filter::mapConnector($connector);
        $this->_join[] = $obj;
        return $this;
    }


    /**
     * returns table of join instance
     *
     * @error 13403
     * @return null|string
     */
    public function getTable()
    {
        return $this->_table;
    }


    /**
     * returns type of join instance
     *
     * @error 13404
     * @return null|string
     */
    public function getType()
    {
        return $this->_type;
    }


    /**
     * returns all join objects of join instance
     *
     * @error 13405
     * @return array|null
     */
    public function getJoin()
    {
        return $this->_join;
    }


    /**
     * checks whether any join object has been set or not
     *
     * @error 13406
     * @return bool
     */
    public function hasJoin()
    {
        return ($this->_join !== null) ? true : false;
    }
}
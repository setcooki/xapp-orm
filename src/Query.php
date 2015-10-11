<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../core/core.php');

xapp_import('xapp.Orm.Query.Exception');
xapp_import('xapp.Orm.Orm');
xapp_import('xapp.Orm.Filter');

class XOQ extends Xapp_Orm_Query{}

/**
 * Orm query base class
 *
 * @package Orm
 * @subpackage Orm_Query
 * @class Xapp_Orm_Query
 * @error 131
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Orm_Query
{
    /**
     * wrapper sprintf value to wrap/replace table, columns, fields
     * in sql statement
     *
     * @var string
     */
    protected $_wrapper = '`%s`';

    /**
     * contains the database connection/driver instance for this
     * instance
     *
     * @var null|Xapp_Orm|Xapp_Orm_Driver
     */
    protected $_connection = null;

    /**
     * contains the the optional table prefix if set in connection
     * instance
     *
     * @var null|string
     */
    protected $_prefix = null;


    /**
     * class constructor sets connection either by passed connection
     * or by retrieving current connection and storing it to be used
     * throughout the lifetime of instance. you can instantiated base
     * class since it is abstract
     *
     * @error 13101
     * @param null|string|Xapp_Orm $connection expects optional connection string name or instance
     */
    public function __construct($connection = null)
    {
        if($connection !== null)
        {
            if($connection instanceof Xapp_Orm)
            {
                $this->_connection = $connection;
            }else{
                $this->_connection = Xapp_Orm::connection((string)$connection);
            }
        }else{
            $this->_connection = Xapp_Orm::connection();
        }
        if(xapp_has_option(Xapp_ORM::TABLE_PREFIX, $this->_connection))
        {
            $this->_prefix = xapp_get_option(Xapp_ORM::TABLE_PREFIX, $this->_connection);
        }
    }


    /**
     * shortcut create function. use this function to statically create a new
     * instance for the connection driver value, e.g. mysql query for mysql driver...
     * if the driver is not implemented will throw exception.
     *
     * @error 13102
     * @param null|string|Xapp_Orm $connection expects optional connection string name or instance
     * @return Xapp_Orm_Query instance of this base class
     * @throws Xapp_Orm_Query_Exception
     */
    public static function create($connection = null)
    {
        if($connection === null)
        {
            $connection = Xapp_Orm::connection();
        }
        if($connection !== null)
        {
            $class = __CLASS__ . '_' . ucfirst(strtolower($connection->driver()));
            if(class_exists($class, true))
            {
                return new $class($connection);
            }else{
                throw new Xapp_Orm_Query_Exception(xapp_sprintf(_("driver dependent query class: %s is not implemented"), $class), 1310201);
            }
        }else{
            throw new Xapp_Orm_Query_Exception(_("connection instance can not be null"), 1310202);
        }
    }


    /**
     * generic mysql compatible insert query builder build query from filter instance
     * and returns valid insert sql statement
     *
     * @error 13103
     * @param Xapp_Orm_Filter $filter expects filter instance
     * @return string
     */
    public function insert(Xapp_Orm_Filter $filter)
    {
        $sql = array();

        $sql[] = "INSERT INTO";
        if($filter->has('table'))
        {
            $sql[] = $this->table($filter->get('table'), 'reset');
        }
        $sql[] = "SET";
        if($filter->has('bindings'))
        {
            $sql[] = $this->bindings($filter->get('bindings'));
        }
        return implode(' ', $sql);
    }


    /**
     * generic mysql compatible update query builder build query from filter instance
     * and returns valid update sql statement
     *
     * @error 13104
     * @param Xapp_Orm_Filter $filter expects filter instance
     * @return string
     */
    public function update(Xapp_Orm_Filter $filter)
    {
        $sql = array();

        $sql[] = "UPDATE";
        if($filter->has('table'))
        {
            $sql[] = $this->table($filter->get('table'), 'reset');
        }
        $sql[] = "SET";
        if($filter->has('bindings'))
        {
            $bindings = $filter->get('bindings');
            if($filter->has('where'))
            {
                $where = $filter->get('where');
                foreach((array)$where as $w)
                {
                    if(array_key_exists($w->column, $bindings))
                    {
                        unset($bindings[$w->column]);
                    }
                }
            }
            $sql[] = $this->bindings($bindings);
        }
        if($filter->has('where'))
        {
            $sql[] = $this->where($filter->get('where'));
        }
        if($filter->has('order'))
        {
            $sql[] = $this->order($filter->get('order'));
        }
        if($filter->has('limit'))
        {
            $sql[] = $this->limit($filter->get('limit'));
        }
        return implode(' ', $sql);
    }


    /**
     * generic mysql compatible delete query builder build query from filter instance
     * and returns valid delete sql statement
     *
     * @error 13105
     * @param Xapp_Orm_Filter $filter expects filter instance
     * @return string
     */
    public function delete(Xapp_Orm_Filter $filter)
    {
        $sql = array();

        $sql[] = "DELETE FROM";
        if($filter->has('table'))
        {
            $sql[] = $this->table($filter->get('table'), 'reset');
        }
        if($filter->has('where'))
        {
            $sql[] = $this->where($filter->get('where'));
        }
        if($filter->has('order'))
        {
            $sql[] = $this->order($filter->get('order'));
        }
        if($filter->has('limit'))
        {
            $sql[] = $this->limit($filter->get('limit'));
        }
        return implode(' ', $sql);
    }


    /**
     * generic mysql compatible select query builder build query from filter instance
     * and returns valid select sql statement
     *
     * @error 13106
     * @param Xapp_Orm_Filter $filter expects filter instance
     * @return string
     */
    public function select(Xapp_Orm_Filter $filter)
    {
        $sql = array();

        $sql[] = "SELECT";
        if($filter->has('distinct'))
        {
            $sql[] = $this->distinct();
        }
        if($filter->has('field'))
        {
            $sql[] = $this->field($filter->get('field'));
        }else{
            $sql[] = "*";
        }
        $sql[] = "FROM";
        if($filter->has('table'))
        {
            $sql[] = $this->table($filter->get('table'));
        }
        if($filter->has('join'))
        {
            $sql[] = $this->join($filter->get('join'));
        }
        if($filter->has('where'))
        {
            $sql[] = $this->where($filter->get('where'));
        }
        if($filter->has('group'))
        {
            $sql[] = $this->group($filter->get('group'));
        }
        if($filter->has('having'))
        {
            $sql[] = $this->having($filter->get('having'));
        }
        if($filter->has('order'))
        {
            $sql[] = $this->order($filter->get('order'));
        }
        if($filter->has('limit'))
        {
            $sql[] = $this->limit($filter->get('limit'));
        }
        return implode(' ', $sql);
    }


    /**
     * aggregate function max returns complete mysql compatible statement
     *
     * @error 13107
     * @param string $table expects table to aggregate function for
     * @param string $column expects column to aggregate
     * @return string
     */
    public function max($table, $column)
    {
        return "SELECT MAX(".$this->wrap($column).") AS max FROM " . $this->wrap($table);
    }


    /**
     * aggregate function min returns complete mysql compatible statement
     *
     * @error 13108
     * @param string $table expects table to aggregate function for
     * @param string $column expects column to aggregate
     * @return string
     */
    public function min($table, $column)
    {
        return "SELECT MIN(".$this->wrap($column).") AS min FROM " . $this->wrap($table);
    }


    /**
     * aggregate function avg returns complete mysql compatible statement
     *
     * @error 13109
     * @param string $table expects table to aggregate function for
     * @param string $column expects column to aggregate
     * @return string
     */
    public function avg($table, $column)
    {
        return "SELECT AVG(".$this->wrap($column).") AS avg FROM " . $this->wrap($table);
    }


    /**
     * aggregate function sum returns complete mysql compatible statement
     *
     * @error 13110
     * @param string $table expects table to aggregate function for
     * @param string $column expects column to aggregate
     * @return string
     */
    public function sum($table, $column)
    {
        return "SELECT SUM(".$this->wrap($column).") AS sum FROM " . $this->wrap($table);
    }


    /**
     * aggregate function count returns complete mysql compatible statement
     *
     * @error 13111
     * @param string $table expects table to aggregate function for
     * @return string
     */
    public function size($table)
    {
        return "SELECT COUNT(*) AS count FROM " . $this->wrap($table);
    }


    /**
     * mysql compatible function to truncate table
     *
     * @error 13112
     * @param string $table expects table to aggregate function for
     * @return string
     */
    public function truncate($table)
    {
        return "TRUNCATE " . $this->wrap($table);
    }


    /**
     * mysql compatible function to drop table
     *
     * @error 13113
     * @param string $table expects table to aggregate function for
     * @return string
     */
    public function drop($table)
    {
        return "DROP TABLE " . $this->wrap($table);
    }


    /**
     * mysql compatible function to show table information
     *
     * @error 13114
     * @param string $table expects table to aggregate function for
     * @return string
     */
    public function show($table)
    {
        return "SHOW TABLE STATUS LIKE '$table'";
    }


    /**
     * build sql update/insert column = value syntax getting bindings from
     * filter class and iterate to build complete sql set syntax
     *
     * @error 13115
     * @param mixed|array $bindings expects array of key => value parameter bindings
     * @return string
     */
    protected function bindings($bindings)
    {
        $sql = array();

        $bindings = (array)$bindings;
        foreach($bindings as $k => $v)
        {
            $sql[] = "{$this->wrap($k)} = {$this->bind($k)}";
        }
        return implode(', ', $sql);
    }


    /**
     * build sql select field/select expression syntax e.g. (table.column AS c, UNIX_TIMESTAMP(foo) AS c)
     * from filter class set field values containing field name/value and optional alias value
     *
     * @error 13116
     * @param mixed|array $field expects array of field objects
     * @return string
     */
    protected function field($field)
    {
        $sql = array();

        $field = (array)$field;
        foreach($field as $k => $v)
        {
            if(!empty($v->table) && stripos($v->field, '.') === false)
            {
                $v->field = $v->table . '.' . $v->field;
            }
            $sql[] = $this->wrap($v->field) . ((isset($v->alias)) ? " AS " . $this->wrap($v->alias) : "");
        }
        return implode(', ', $sql);
    }


    /**
     * build sql table expression syntax from filter class containing table name and optional alias.
     * since multiple tables can be contained in filter class - update/delete/insert queries only allow
     * for single tables expects array pointer value to get either first or last table from collection
     *
     * @error 13117
     * @param mixed|array $table expects array of table objects
     * @param null|string $pointer expects php array pointer function as string
     * @return string
     */
    protected function table($table, $pointer = null)
    {
        $sql = array();
        $table = (array)$table;

        if($pointer !== null)
        {
            $pointer = strtolower(trim($pointer));
            if(in_array($pointer, array('end', 'current', 'reset')))
            {
                $table = array($pointer($table));
            }
        }
        foreach($table as $k => $v)
        {
            $sql[] = $this->wrap($v->table) . ((isset($v->alias)) ? " AS {$v->alias}" : "");
        }
        return implode(', ', $sql);
    }


    /**
     * build distinct expression
     *
     * @error 13118
     * @return string
     */
    protected function distinct()
    {
        return "DISTINCT";
    }


    /**
     * build limit, offset part for mysql compatible query
     *
     * @error 13119
     * @param object $limit expects limit object from filter class
     * @return string
     */
    protected function limit($limit)
    {
        $limit = (object)$limit;
        if(isset($limit->offset))
        {
            return "LIMIT {$limit->offset}, {$limit->limit}";
        }else{
            return "LIMIT {$limit->limit}";
        }
    }


    /**
     * build mysql compatible order clause for order objects of filter
     * class
     *
     * @error 13120
     * @param mixed|object $order expects order object from filter class
     * @return string
     */
    protected function order($order)
    {
        $sql = array();

        $order = (array)$order;
        foreach($order as $o)
        {
            if(!empty($o->table) && stripos($o->column, '.') === false)
            {
                $o->column = $o->table . '.' . $o->column;
            }
            $sql[] = "{$this->wrap($o->column)} {$o->direction}";
        }
        return "ORDER BY " . implode(', ', $sql);
    }


    /**
     * build join part of mysql compatible select statement. joins are set by using
     * the join class containing all necessary properties to construct multiple joins
     * here
     *
     * @error 13121
     * @param mixed|array $join expects join objects from filter class
     * @return string
     */
    protected function join($join)
    {
        $sql = array();

        $join = (array)$join;
        foreach($join as $j)
        {
            $tmp = array();
            if($j->hasJoin())
            {
                foreach($j->getJoin() as $k => $v)
                {
                    $tmp[] = "{$this->whereMapper($v, false)} {$v->connector}";
                }
                $tmp[sizeof($tmp) - 1] = trim(preg_replace('/(AND|OR|XOR)\s?$/', '', $tmp[sizeof($tmp) - 1]));
                $tmp = trim(implode(' ', $tmp));
                $sql[] = $this->joinMapper($j->getType()) . " " . $this->wrap($j->getTable()) . "  ON " . $tmp;
            }

        }
        return implode(' ', $sql);
    }


    /**
     * build group part of mysql compatible select statement capable of handling
     * also modifiers in mysql modifier can be set only once at end of statement
     *
     * @error 13122
     * @param array|mixed $group expects group objects
     * @return string
     */
    protected function group($group)
    {
        $sql = array();

        $group = (array)$group;
        foreach($group as $g)
        {
            if(!empty($g->table) && stripos($g->column, '.') === false)
            {
                $g->column = $g->table . '.' . $g->column;
            }
            $sql[] = $this->wrap($g->column);
        }
        $last = end($group);
        return "GROUP BY " . implode(', ', $sql) . ((isset($last->modifier) && $last->modifier !== null) ? " {$last->modifier}" : '');
    }


    /**
     * build where clause for mysql compatible statement by iterating through
     * multidimensional array with where conditions set by filter class. since where conditions
     * can be nested and the nesting is reflected in array in array logic the where builder is
     * a recursive function calling itself to get into a nested set of where conditions. all
     * single conditions are connected with the connector passed in where object leaving the where
     * condition string for finally cleanup. subselects are nested by nature
     *
     * @error 13123
     * @param mixed|array $where expects the where objects of filter class
     * @param array $sql is set through recursive calling
     * @return string
     */
    protected function where($where, &$sql = array())
    {
        $where = (array)$where;

        for($i = 0; $i < sizeof($where); $i++)
        {
            if(is_array($where[$i]))
            {
                $sql[] = "(";
                $this->where($where[$i], $sql);
                $sql[] = ")";
                if($where[$i][sizeof($where[$i]) -1] instanceof stdClass)
                {
                    $sql[] = $where[$i][sizeof($where[$i]) -1]->connector;
                }
            }else{
                if(strtolower($where[$i]->type) === 'subselect')
                {
                    $where[$i]->value = "(" . trim(trim($where[$i]->value), "()") . ")";
                }
                $sql[] = $this->whereMapper($where[$i]) . " " . (($i < sizeof($where) -1) ? $where[$i]->connector : "");
            }
        }
        return "WHERE " . trim(preg_replace(array('/(AND|OR|XOR)\s?([\)]?)\s?$/i', '/\s+/i'), array('$2', ' '), trim(implode(' ', $sql))));
    }


    /**
     * build having clause for mysql compatible select statement by iterating through
     * all having objects connecting them with the set connector to be cleaned
     * up in the end
     *
     * @error 13124
     * @param mixed|array $having expects the having object from filter class
     * @return string
     */
    protected function having($having)
    {
        $sql = array();
        $having = (array)$having;
        for($i = 0; $i < sizeof($having); $i++)
        {
            $sql[] = $this->whereMapper($having[$i]) . " " . (($i < sizeof($having) -1) ? $having[$i]->connector : "");
        }
        return "HAVING " . trim(preg_replace(array('/(AND|OR|XOR)\s?$/i', '/\s+/i'), array('', ' '), trim(implode(' ', $sql))));
    }


    /**
     * build single where condition of all native supported and not supported raw mysql compatible where conditions.
     * the function can be overwritten by defining a custom method with the same name as the condition operator. e.g.
     * if a function equal($where) exists all conditions of the operator will be handled by that function. if not set
     * everything will be handled by this function which is an generic approach for must operators and drivers. raw
     * queries will be ignored. if the second parameter is set to true the actuall where condition values are replaced
     * by pdo statement compatible ? placeholder
     *
     * @error 13125
     * @param object $where expects the single where condition object from filter class
     * @param bool $mask defines whether to mask values for prepared statements or not
     * @return mixed|string
     * @throws Xapp_Orm_Query_Exception
     */
    protected function whereMapper($where, $mask = true)
    {
        $where = (object)$where;

        //make table.column combination if table is set
        if(!empty($where->table) && stripos($where->column, '.') === false)
        {
            $where->column = $where->table . '.' . $where->column;
        }
        //raw where clause
        if(strtolower($where->type) === 'whereraw')
        {
            return $where->column;
        //custom overwriting function for operator exists
        }else if(method_exists($this, $where->operator)){
            return call_user_func(array($this, $where->operator), $where);
        //build where clause
        }else{
            $where->column = $this->wrapWhere($where->column);
            $where->_value = $where->value;
            if(
                is_string($where->_value)
                &&
                substr_count($where->_value, '.') === 1
                &&
                class_exists(substr($where->_value, 0, strpos($where->_value, '.')), true)
                &&
                property_exists(substr($where->_value, 0, strpos($where->_value, '.')), substr($where->_value , strpos($where->_value, '.') + 1))
            ){
                $where->value = $this->wrapWhere($where->value);
            }else{
                if($where->mask && (bool)$mask)
                {
                    $where->value = $this->mask($where->value);
                }else{
                    $where->value = implode(', ', (array)$where->value);
                }
            }
            switch($where->operator)
            {
                case Xapp_Orm_Filter::EQUAL:
                    $return = "{$where->column} = {$where->value}";
                    break;
                case Xapp_Orm_Filter::NOT_EQUAL:
                    $return = "{$where->column} != {$where->value}";
                    break;
                case Xapp_Orm_Filter::GREATER_THAN:
                    $return = "{$where->column} > {$where->value}";
                    break;
                case Xapp_Orm_Filter::GREATER_THAN_EQUAL:
                    $return = "{$where->column} >= {$where->value}";
                    break;
                case Xapp_Orm_Filter::LESSER_THAN:
                    $return = "{$where->column} < {$where->value}";
                    break;
                case Xapp_Orm_Filter::LESSER_THAN_EQUAL:
                    $return = "{$where->column} <= {$where->value}";
                    break;
                case Xapp_Orm_Filter::IS:
                    $return = "{$where->column} IS TRUE";
                    break;
                case Xapp_Orm_Filter::IS_NOT:
                    $return = "{$where->column} IS FALSE";
                    break;
                case Xapp_Orm_Filter::IS_NULL:
                    $return = "{$where->column} IS NULL";
                    break;
                case Xapp_Orm_Filter::IS_NOT_NULL:
                    $return = "{$where->column} IS NOT NULL";
                    break;
                case Xapp_Orm_Filter::IN:
                    $return = "{$where->column} IN({$where->value})";
                    break;
                case Xapp_Orm_Filter::NOT_IN:
                    $return = "{$where->column} NOT IN({$where->value})";
                    break;
                case Xapp_Orm_Filter::FIND_IN_SET:
                    $return = "FIND_IN_SET({$where->column}, {$where->value})";
                    break;
                case Xapp_Orm_Filter::REGEXP:
                    $return = "{$where->column} REGEXP {$where->value}";
                    break;
                case Xapp_Orm_Filter::NOT_REGEXP:
                    $return = "{$where->column} NOT REGEXP {$where->value}";
                    break;
                case Xapp_Orm_Filter::MATCH_AGAINST:
                    if(isset($where->modifier))
                    {
                        $return = "MATCH ({$where->column}) AGAINST ({$where->value} {$where->modifier})";
                    }else{
                        $return = "MATCH ({$where->column}) AGAINST ({$where->value})";
                    }
                    break;
                case Xapp_Orm_Filter::LIKE:
                    $return = "{$where->column} LIKE " . $where->value;
                    break;
                case Xapp_Orm_Filter::NOT_LIKE:
                    $return = "{$where->column} NOT LIKE " . $where->value;
                    break;
                case Xapp_Orm_Filter::STRCMP:
                    $return = "STRCMP({$where->column}, $where->value) = 0";
                    break;
                case Xapp_Orm_Filter::BETWEEN:
                    $return = "{$where->column} BETWEEN {$where->value[0]} AND {$where->value[1]}";
                    break;
                case Xapp_Orm_Filter::NOT_BETWEEN:
                    $return = "{$where->column} NOT BETWEEN {$where->value[0]} AND {$where->value[1]}";
                    break;
                default:
                    throw new Xapp_Orm_Query_Exception(xapp_sprintf(_("where condition operator: %s is not supported"), $where->operator), 1312501);
            }
            return $return;
        }
    }


    /**
     * join mapper returns the mysql compatible join expression according to joins object
     * join type defined by class constants
     *
     * @error 13126
     * @param string $type expects the join type from join object of filter class
     * @return string
     * @throws Xapp_Orm_Query_Exception
     */
    protected function joinMapper($type)
    {
        $type = strtoupper(trim($type));

        switch($type)
        {
            case Xapp_Orm_Filter::INNER_JOIN:
                $return = 'INNER JOIN';
                break;
            case Xapp_Orm_Filter::CROSS_JOIN:
                $return = 'CROSS JOIN';
                break;
            case Xapp_Orm_Filter::STRAIGHT_JOIN:
                $return = 'STRAIGHT_JOIN';
                break;
            case Xapp_Orm_Filter::LEFT_JOIN:
                $return = 'LEFT JOIN';
                break;
            case Xapp_Orm_Filter::LEFT_OUTER_JOIN:
                $return = 'LEFT OUTER JOIN';
                break;
            case Xapp_Orm_Filter::RIGHT_JOIN:
                $return = 'RIGHT JOIN';
                break;
            case Xapp_Orm_Filter::RIGHT_OUTER_JOIN:
                $return = 'RIGHT OUTER JOIN';
                break;
            default:
                throw new Xapp_Orm_Query_Exception(xapp_sprintf(_("join expression type: %s is not supported"), $type), 1312601);
        }
        return $return;
    }


    /**
     * wraps values, quotes sql non keyword values capable of wrapping/quoting, e.g table names, columns, fields.
     * wrapping will only be done if passed value is not instance of Xapp_Orm_Expression returning the raw value
     *
     * @error 13127
     * @param string|array $value expects the value to wrap
     * @return string
     */
    protected function wrap($value)
    {
        $wrap = array();

        if(!is_array($value))
        {
            $value = array($value);
        }
        foreach($value as $key => $val)
        {
            if(!($val instanceof Xapp_Orm_Expression))
            {
                //is a single table name or field
                if(preg_match('/^[a-z0-9\_]{1,}$/i', $val))
                {
                    $val = sprintf($this->_wrapper, $val);
                //is a table.field value
                }else if(strpos($val, '.') !== false){
                    $val = preg_replace_callback('/([a-z0-9\_]{1,})\.([a-z0-9\_]{1,})/i', array($this, 'wrapCallback'), $val);
                }
            }
            $wrap[] = (string)$val;
        }
        return implode(', ', $wrap);
    }


    /**
     * internal callback function for Xapp_Orm_Query::wrap
     *
     * @error 13132
     * @see Xapp_Orm_Query::wrap
     * @param array $match expects the matches from Xapp_Orm_Query::wrap method
     * @return string
     */
    final protected function wrapCallback($match)
    {
        if(preg_match('/[a-z]{1,}/i', $match[0]))
        {
            return sprintf($this->_wrapper, (($this->_prefix !== null) ? $this->_prefix . $match[1] : $match[1])) . "." . sprintf($this->_wrapper, $match[2]);
        }else{
            return $match[0];
        }
    }


    /**
     * wrap where columns of where condition. NOTE: even though multiple columns are
     * valid according to SQL-92 standard its most likly it is not a portable behaviour
     * in mysql will work only on where conditions and subselects
     *
     * @error 13128
     * @param string|array $value expects where columns to wrap
     * @return string
     */
    protected function wrapWhere($value)
    {
        $_value = (array)$value;
        if(sizeof($_value) > 1)
        {
            return "(" . $this->wrap($value) . ")";
        }else{
            return $this->wrap($value);
        }
    }


    /**
     * mask values for pdo statements replacing all values with ? placeholders. this functions
     * will return a string unless the value is an array of values used to be injected in different
     * places of the statement. if the second parameter is set to false will return value as the same
     * type as it entered mask function, e.g. an array will be returned not as imploded string but array
     * with placeholders
     *
     * @error 13129
     * @param string|array|Xapp_Orm_Expression $value expects the value to be wrapped
     * @param bool $force expects boolean value on whether to always return value as masked string
     * @return array|string
     */
    protected function mask($value, $force = true)
    {
        $mask = array();

        if($value instanceof Xapp_Orm_Expression)
        {
           return (string)$value;
        }
        //special case that holds array(array(), array()) values for operators
        //that need values in different parts of condition
        if(is_array($value) && isset($value[0]) && is_array($value[0][0]))
        {
            foreach($value as $k => $v)
            {
                $size = (!empty($v)) ? sizeof((array)$v) : 1;
                if((bool)$force)
                {
                    $mask[$k] = implode(', ', array_fill(0, $size, '?'));
                }else{
                    $mask[$k] = array_fill(0, $size, '?');
                }
            }
            return $mask;
        }else{
            $size = (!empty($value)) ? sizeof((array)$value) : 1;
            if((bool)$force)
            {
                return implode(', ', array_fill(0, $size, '?'));
            }else{
                return array_fill(0, $size, '?');
            }
        }
    }


    /**
     * make a pdo bindable parameter out of parameter key(s) for pdo compatible
     * statements with parameter placeholder with :
     *
     * @error 13130
     * @param string|array $key expects the keys to bind
     * @return string
     */
    protected function bind($key)
    {
        $bind = array();

        foreach((array)$key as $k)
        {
            $bind[] = ':' . trim(trim($k), ':');
        }
        return implode(', ', $bind);
    }


    /**
     * auto construct sql crud query from filter by getting action from filter
     * to call internal builder class
     *
     * @error 13131
     * @param Xapp_Orm_Filter $filter expects instance of filter class
     * @return string
     */
    final public function execute(Xapp_Orm_Filter $filter)
    {
        $action = $filter->get('action');
        return $this->$action($filter);
    }
}
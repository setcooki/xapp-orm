<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../core/core.php');

xapp_import('xapp.Orm.Filter.Exception');
xapp_import('xapp.Orm.Expression');
xapp_import('xapp.Orm.Join');

class XOF extends Xapp_Orm_Filter{}

/**
 * Orm filter class
 *
 * @package Orm
 * @class Xapp_Orm_filter
 * @error 136
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Orm_Filter
{
    /**
     * mysql compatible filter operator equal =
     *
     * @const EQUAL
     */
    const EQUAL                     = 'EQUAL';

    /**
     * mysql compatible filter operator not equal !=
     *
     * @const NOT_EQUAL
     */
    const NOT_EQUAL                 = 'NOT_EQUAL';

    /**
     * mysql compatible filter operator greater than >
     *
     * @const GREATER_THAN
     */
    const GREATER_THAN              = 'GREATER_THAN';

    /**
     * mysql compatible filter operator greater than equal >=
     *
     * @const GREATER_THAN_EQUAL
     */
    const GREATER_THAN_EQUAL        = 'GREATER_THAN_EQUAL';

    /**
     * mysql compatible filter operator lesser than <
     *
     * @const LESSER_THAN
     */
    const LESSER_THAN               = 'LESSER_THAN';

    /**
     * mysql compatible filter operator lesser than equal <=
     *
     * @const LESSER_THAN_EQUAL
     */
    const LESSER_THAN_EQUAL         = 'LESSER_THAN_EQUAL';

    /**
     * mysql compatible filter operator is
     *
     * @const IS
     */
    const IS                        = 'IS';

    /**
     * mysql compatible filter operator is not
     *
     * @const IS_NOT
     */
    const IS_NOT                    = 'IS_NOT';

    /**
     * mysql compatible filter operator is null
     *
     * @const IS_NULL
     */
    const IS_NULL                   = 'IS_NULL';

    /**
     * mysql compatible filter operator is not null
     *
     * @const IS_NOT_NULL
     */
    const IS_NOT_NULL               = 'IS_NOT_NULL';

    /**
     * mysql compatible filter operator in
     *
     * @const IN
     */
    const IN                        = 'IN';

    /**
     * mysql compatible filter operator not in
     *
     * @const NOT_IN
     */
    const NOT_IN                    = 'NOT_IN';

    /**
     * mysql compatible filter operator find in set
     *
     * @const FIND_IN_SET
     */
    const FIND_IN_SET               = 'FIND_IN_SET';

    /**
     * mysql compatible filter operator regexp
     *
     * @const REGEXP
     */
    const REGEXP                    = 'REGEXP';

    /**
     * mysql compatible filter operator not regexp
     *
     * @const NOT_REGEXP
     */
    const NOT_REGEXP                = 'NOT_REGEXP';

    /**
     * mysql compatible filter operator match agains
     *
     * @const MATCH_AGAINST
     */
    const MATCH_AGAINST             = 'MATCH_AGAINST';

    /**
     * mysql compatible filter operator like %
     *
     * @const LIKE
     */
    const LIKE                      = 'LIKE';

    /**
     * mysql compatible filter operator not like !%
     *
     * @const NOT_LIKE
     */
    const NOT_LIKE                  = 'NOT_LIKE';

    /**
     * mysql compatible filter operator strcmp
     *
     * @const STRCMP
     */
    const STRCMP                    = 'STRCMP';

    /**
     * mysql compatible filter operator between <>
     *
     * @const BETWEEN
     */
    const BETWEEN                   = 'BETWEEN';

    /**
     * mysql compatible filter operator not between !<>
     *
     * @const NOT_BETWEEN
     */
    const NOT_BETWEEN               = 'NOT_BETWEEN';

    /**
     * special operator for subselect queries
     *
     * @const SUB_SELECT
     */
    const SUB_SELECT                = 'SUB_SELECT';


    /**
     * order direction constant ASC
     *
     * @const ASC
     */
    const ASC                       = 'ASC';

    /**
     * order direction constant DESC
     *
     * @const DESC
     */
    const DESC                      = 'DESC';


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
     * operator map will map operator short cut values to operator
     * constants so both are inter exchangeable
     *
     * @var array
     */
    protected static $_operatorMap = array
    (
        self::EQUAL                 => "=",
        self::NOT_EQUAL             => "!=",
        self::GREATER_THAN          => ">",
        self::GREATER_THAN_EQUAL    => ">=",
        self::LESSER_THAN           => "<",
        self::LESSER_THAN_EQUAL     => "<=",
        self::IS                    => "is",
        self::IS_NOT                => "is_not",
        self::IS_NULL               => "is_null",
        self::IS_NOT_NULL           => "is_not_null",
        self::IN                    => "in",
        self::NOT_IN                => "not_in",
        self::FIND_IN_SET           => "find_in_set",
        self::REGEXP                => "regexp",
        self::NOT_REGEXP            => "not_regexp",
        self::MATCH_AGAINST         => "match_against",
        self::LIKE                  => "%",
        self::NOT_LIKE              => "!%",
        self::STRCMP                => "strcmp",
        self::BETWEEN               => "<>",
        self::NOT_BETWEEN           => "!<>",
        self::SUB_SELECT            => "sub_select"
    );

    /**
     * connector map so shortcur connector value is inter
     * exchangeable to full written connector value
     *
     * @var array
     */
    protected static $_connectorMap = array
    (
        'AND'                       => '&&',
        'OR'                        => '||',
        'XOR'                       => 'xor'
    );

    /**
     * contains the query action/command (select, update, insert, delete)
     * when constructing filter for equivalent action. defaults to "select"
     * since in default case a select query is constructed with filter
     *
     * @var string
     */
    protected $_action = 'select';

    /**
     * contains the the distinct value
     *
     * @var null|mixed
     */
    protected $_distinct = null;

    /**
     * contains the tables for query
     *
     * @var null|mixed
     */
    protected $_table = null;

    /**
     * contains the where clauses for query
     *
     * @var null|mixed
     */
    protected $_where = null;

    /**
     * contains the having clauses for query
     *
     * @var null|mixed
     */
    protected $_having = null;

    /**
     * contains the fields for select query
     *
     * @var null|mixed
     */
    protected $_field = null;

    /**
     * contains the columns for not select queries
     *
     * @var null|mixed
     */
    protected $_column = null;

    /**
     * contains the group columns for query
     *
     * @var null|mixed
     */
    protected $_group = null;

    /**
     * contains the limit and offset values for query
     *
     * @var null|mixed
     */
    protected $_limit = null;

    /**
     * contains the order column/directions for query
     *
     * @var null|mixed
     */
    protected $_order = null;

    /**
     * contains the values for ? parametrized select statements
     *
     * @var null|mixed
     */
    protected $_values = null;

    /**
     * contains the key => value bindings for : parametrized not select statements
     *
     * @var null|mixed
     */
    protected $_bindings = null;

    /**
     * contains the join statement object for query
     *
     * @var null|mixed
     */
    protected $_join = null;


    /**
     * construct instance by passing optional table(s) defining the table(s)
     * to be used for the query that is build from filter. the second parameter
     * is usually set automatic when using short cut method to create filter.
     * allowed are only crud action (select, insert, update, delete). the filter
     * class will not handle any other database operations
     *
     * @error 13601
     * @param null|mixed $table expects either single table as string or multiple by array
     * @param string $action expects the crud action
     * @throws Xapp_Orm_Filter_Exception
     */
    public function __construct($table = null, $action = 'select')
    {
        if($table !== null)
        {
            $this->table($table);
        }
        $action = strtolower(trim($action));
        if(in_array($action, array('select', 'insert', 'update', 'delete')))
        {
            $this->_action = $action;
        }else{
            throw new Xapp_Orm_Filter_Exception(_("only crud actions allowed in filter class"), 1360101);
        }
    }


    /**
     * short cut function to create filter for select query with
     * not defining table in constructor. use this shortcut method when
     * building complex filter objects
     *
     * @error 13602
     * @return Xapp_Orm_Filter
     */
    public static function create()
    {
        return new self();
    }


    /**
     * short cut function to create select filter expecting to pass the
     * tables that are queried for to be passed in first parameter
     *
     * @error 13603
     * @param string|array $table expects single table as string or multiple as array
     * @return Xapp_Orm_Filter
     */
    public static function select($table)
    {
        return new self($table, __FUNCTION__);
    }


    /**
     * short cut function to create update filter expecting to pass the
     * table that is update in the first parameter. multiple tables could
     * be passed but will be ignored since update is only valid on one table
     *
     * @error 13604
     * @param string $table expects single table as string
     * @return Xapp_Orm_Filter
     */
    public static function update($table)
    {
        return new self($table, __FUNCTION__);
    }


    /**
     * short cut function to create insert filter expecting to pass the
     * table that is used in insert statement in the first parameter.
     * multiple tables could be passed but will be ignored since insert
     * is only valid on one table
     *
     * @error 13605
     * @param string $table expects single table as string
     * @return Xapp_Orm_Filter
     */
    public static function insert($table)
    {
        return new self($table, __FUNCTION__);
    }


    /**
     * short cut function to create delete filter expecting to pass the
     * tables that are used in delete statement in the first parameter.
     * in theory data in multiple tables can be delete if the query implementation
     * permits so
     *
     * @error 13606
     * @param string|array $table expects single table as string or multiple tables as array
     * @return Xapp_Orm_Filter
     */
    public static function delete($table)
    {
        return new self($table, __FUNCTION__);
    }


    /**
     * short cut function to create new expression instance by passing expression value
     * in first parameter and optional values in second parameter if expression value
     * has sprintf parseable placeholder
     *
     * @error 13607
     * @param mixed $expr expects the value to be passed to expression instance
     * @param null|string|array $values expects values for % placeholder
     * @return Xapp_Orm_Expression
     */
    public static function expr($expr, $values = null)
    {
        return Xapp_Orm_Expression::create($expr, $values);
    }


    /**
     * short cut function to quote value using pdo quoting passing
     * value type value in second parameter which is by default string quoting
     *
     * @error 13608
     * @param mixed $value expects value to quote
     * @param int $type expects the pdo param type hint value
     * @return mixed
     */
    public static function quote($value, $type = PDO::PARAM_STR)
    {
        return Xapp_Orm::quote($value, $type);
    }


    /**
     * add table(s) to filter with optional alias value. table(s) can be passed
     * as single string value in first parameter or as multiple as array. if passed
     * as array alias values should be passed also as array
     *
     * @error 13609
     * @param string|array $table expects table as single string or multiple as array
     * @param null|string|array $alias expects optional alias as string or array
     * @return Xapp_Orm_Filter
     */
    public function table($table, $alias = null)
    {
        $alias = (array)$alias;
        $table = (array)$table;
        for($i = 0; $i < sizeof($table); $i++)
        {
            $obj = new XO();
            $obj->table = (string)$table[$i];
            $obj->alias = (isset($alias[$i])) ? (string)$alias[$i] : null;
            $this->_table[] = $obj;
        }
        return $this;
    }


    /**
     * add fields to filter for select query only. pass single field in first
     * parameter of array of fields. pass alias as string when first parameter
     * is also a string. or array of aliases for each field or null for fields
     * that do not need aliases. NOTE: dont use this function to set key => value
     * pairs for update, insert queries - use Xapp_Orm_Filter::set to set pairs
     *
     * @error 13610
     * @param string|array $field expects single field value as string or multiple as array
     * @param null|string|array $alias expects optional alias as string or array
     * @return Xapp_Orm_Filter
     */
    public function field($field, $alias = null)
    {
        $alias = (array)$alias;
        if(!is_array($field))
        {
            $field = array($field);
        }
        for($i = 0; $i < sizeof($field); $i++)
        {
            $obj = new XO();
            $obj->field = $field[$i];
            $obj->alias = (isset($alias[$i])) ? (string)$alias[$i] : null;
            $this->_field[] = $obj;
        }
        return $this;
    }


    /**
     * set/bind key => value pairs to be used in insert or update queries and pdo
     * prepared statements. pass column key as single value with second parameter
     * as single value or pass key => value pairs as array only in first parameter
     * or divide keys and values to go in first and second parameter. the latter will
     * throw an error if size of arrays are not identical
     *
     * @error 13611
     * @param string|array $column expects single column, array of columns or array of key => value pairs
     * @param null|string|array $value expects values as explained above
     * @return Xapp_Orm_Filter
     * @throws Xapp_Orm_Filter_Exception
     */
    public function set($column, $value = null)
    {
        if(is_array($column) && is_array($value))
        {
            if(sizeof($column) === sizeof($value))
            {
                for($i = 0; $i < sizeof($value); $i++)
                {
                    $this->setBindings($column[$i], $value[$i]);
                }
            }else{
                throw new Xapp_Orm_Filter_Exception(_("values and columns/keys must have the same array length when binding"), 1361101);
            }

        }else if(is_array($column) && $value === null){
            foreach($column as $k => $v)
            {
                $this->setBindings($k, $v);
            }
        }else{
            $this->setBindings($column, $value);
        }
        return $this;
    }


    /**
     * use this method for building filters for update or delete as a short cut to not
     * add a where clause when addressing the primary identification key in update/delete
     * statement. pass the primary key name as first parameter and the values in second parameter.
     * the function will work also for deleting multiple record sets by simply passing an array
     * of primary key ids as second parameter. NOTE: this method is not allowed for select
     * statements since parameters are bound via parameter name. dont use this function if
     * you dont have a primary key defined!
     *
     * @error 13612
     * @param string $key expects the primary key name
     * @param int|array $value expects primary key value as single value or array with multiple
     * @return Xapp_Orm_Filter
     * @throws Xapp_Orm_Filter_Exception
     */
    public function key($key, $value)
    {
        $tmp = array();

        if($this->_action !== 'select')
        {
            $obj = new XO();
            $obj->mask = false;
            $obj->type = __FUNCTION__;
            $obj->connector = null;
            $obj->column = $key;
            if(is_array($value))
            {
                foreach($value as $k => $v)
                {
                    $this->setBindings($key . $k , $v);
                    $tmp[] = ":{$key}{$k}";
                }
                $obj->value = $tmp;
                $obj->operator = self::IN;
            }else{
                $obj->value = ":{$key}";
                $obj->operator = self::EQUAL;
                $this->setBindings($key, $value);
            }
            $this->_where[] = $obj;
        }else{
            throw new Xapp_Orm_Filter_Exception(_("filter method: key not allowed for select statements"), 1361201);
        }
        return $this;
    }


    /**
     * set the distinct keyword to true to be used in query
     *
     * @error 13613
     * @return Xapp_Orm_Filter
     */
    public function distinct()
    {
        $this->_distinct = true;
        return $this;
    }


    /**
     * add join to filter either by passing closure to second paramter or passing all required parameter
     * to build a single join from. when using closure use as
     *
     * <code>
     *      $filter->join('table', function($join){
     *          $join->on('column1', '=', 'value', 'AND');
     *          $join->on('column2', '!=', 'value', 'AND');
     *      }, Filter::LEFT_JOIN)
     * </code>
     *
     * this will add multiple on conditions to join statement. use closures when you have only multiple on
     * conditions else use filter join function to add a complete join. when using closure define the join
     * type, e.g. LEFT_JOIN in third parameter - all other parameters are rendered useless.
     *
     * if you have an single ON condition for your join use join function without condition passing all
     * parameter as they are intended to. you must set second and fourth parameter as they are the needed
     * columns for ON statement
     *
     * @error 13614
     * @param string $table expects the table to perform join on
     * @param string $column1 expects either instance of closure or column1 name for regular join
     * @param null|string $operator expects either join type when using closure or ON operator when using regular join
     * @param null|string $column2 expects right column or column2 of ON join condition
     * @param null|string $type expects optional join type in regular join defaults to INNER_JOIN
     * @return Xapp_Orm_Filter
     * @throws Xapp_Orm_Filter_Exception
     */
    public function join($table, $column1, $operator = null, $column2 = null, $type = null)
    {
        if($column1 instanceof Closure)
        {
            if($operator === null)
            {
                $operator = self::INNER_JOIN;
            }
            $this->_join[] = new Xapp_Orm_Join($table, $operator);
         	call_user_func($column1, end($this->_join));
        }else{
            if($operator === null)
            {
                $operator = self::EQUAL;
            }
            if($type === null)
            {
                $type = self::INNER_JOIN;
            }
            if($column2 !== null )
            {
                $join = new Xapp_Orm_Join($table, $type);
                $join->on($column1, $operator, $column2);
                $this->_join[] = $join;
            }else{
                throw new Xapp_Orm_Filter_Exception(_("filter parameter \$column2 for joins must be set for single joins"), 1361401);
            }
        }
        return $this;
    }


    /**
     * set limit and offset to filter instance
     *
     * @error 13615
     * @param int $limit expects the limit value
     * @param null|int $offset expects the optional offset value
     * @return Xapp_Orm_Filter
     */
    public function limit($limit, $offset = null)
    {
        $obj = new XO();
        $obj->limit = (int)$limit;
        if($offset !== null)
        {
            $obj->offset = (int)$offset;
        }
        $this->_limit = $obj;
        return $this;
    }


    /**
     * add order expression to filter by passing either single value in first parameter or array
     * of multiple columns to be order with the same direction defined in second parameter unless
     * second parameter is also an array containing all direction values for all values in array
     * of columns. if direction array does not match or has empty values default direction ASC
     * will be used
     *
     * @error 13616
     * @param string|array $column expects single column as string or multiple as array
     * @param string|array $direction expects value as explained above
     * @return Xapp_Orm_Filter
     */
    public function order($column, $direction = self::ASC)
    {
        $column = (array)$column;
        for($i = 0; $i < sizeof($column); $i++)
        {
            $obj = new XO();
            $obj->column = (string)$column[$i];
            if(!is_array($direction))
            {
                $obj->direction = strtoupper(trim($direction));
            }else{
                $obj->direction = (isset($direction[$i])) ? strtoupper(trim((string)$direction[$i])) : self::ASC;
            }
            $this->_order[] = $obj;
        }
        return $this;
    }


    /**
     * add a subselect to the where query part of a select statement. use this function in favor of trying
     * to add a subselect via the where() method since unless the subselect is passed via expression the
     * subselect will be masked and will lead to an error. using the this in build subselect method all
     * subselects will also be nested properly. pass a subselect as you would pass a pdo statement:
     * select * from foo where id = ?. dont use brackets since they will be replaced by brackets in query builder.
     * set the placeholder value in third parameter so it is correctly bound when preparing and executing
     * statement. use the rest of the parameters as if you would do in where() method
     *
     * @see Xapp_Orm_Filter::where
     * @error 13617
     * @param string $column expects the column to connect to subselect
     * @param string $query expects the subselect statement as explained above
     * @param string|array $values expects the optional values if needed for subselect
     * @param string $operator expects the operator that connects the column with the subselect
     * @param string $connector expects connector that will connect the next where condition
     * @return Xapp_Orm_Filter
     */
    public function subselect($column, $query, $values = null, $operator = self::EQUAL, $connector = 'AND')
    {
        $obj = new XO();
        $obj->mask = false;
        $obj->type = __FUNCTION__;
        $obj->column = $column;
        $obj->value = $query;
        $obj->operator = self::mapOperator($operator);
        $obj->connector = self::mapConnector($connector);
        $this->_where[] = $obj;
        $this->_values = array_merge((array)$this->_values, (array)$values);
        return $this;
    }


    /**
     * add between clause to filter. use this function in favor of using generic where() function
     * since it can not deal with between clause expecting two values instead of only one. define the
     * min and max value of between clause with second and third parameter. this function can be used
     * to compare not only integer values but also date/time values optional passing the values as
     * expression for correct comparison
     *
     * @error 13618
     * @param string $column expects the column for the between condition
     * @param mixed $min expects the minimum value of between condition
     * @param mixed $max expects the maximum value of between condition
     * @param string $connector expects connector that will connect the next where condition
     * @param bool $not expects boolean value of whether to use between or not between operator
     * @return Xapp_Orm_Filter
     */
    public function between($column, $min, $max, $connector = 'AND', $not = false)
    {
        $obj = new XO();
        $obj->mask = true;
        $obj->type = __FUNCTION__;
        $obj->value = array(array($min), array($max));
        $obj->column = $column;
        $obj->operator = ((bool)$not) ? self::NOT_BETWEEN : self::BETWEEN;
        $obj->connector = self::mapConnector($connector);
        $this->_where[] = $obj;
        $this->setValues($min);
        $this->setValues($max);
        return $this;
    }


    /**
     * add between not clause to filter. see Xapp_Orm_Filter::between for further
     * explanation
     *
     * @see Xapp_Orm_Filter::between
     * @error 13619
     * @param string $column expects the column for the between condition
     * @param mixed $min expects the minimum value of between condition
     * @param mixed $max expects the maximum value of between condition
     * @param string $connector expects connector that will connect the next where condition
     * @return Xapp_Orm_Filter
     */
    public function notBetween($column, $min, $max, $connector = 'AND')
    {
        return $this->between($column, $min, $max, $connector, true);
    }


    /**
     * add where clause to filter. this is the standard function to add where conditions
     * to filter. the first parameter can be instance of Closure. be using closures where
     * conditions can be nested in brackets for complex select statements. use like:
     *
     * <code>
     *      $filter->where(function($filter){
     *          $filter->where('foo', 1);
     *          $filter->where('name', 'foo');
     *      })
     * </code>
     *
     * this will add brackets around the two where condition passed as closure
     *
     * @error 13620
     * @param string $column expects the column for the where condition
     * @param null|mixed $value expects the value for where condition which can also be an expression
     * @param string $operator expects the where condition operator
     * @param string $connector expects connector that will connect the next where condition
     * @return Xapp_Orm_Filter
     */
    public function where($column, $value = null, $operator = self::EQUAL, $connector = 'AND')
    {
        if($column instanceof Closure)
        {
            return $this->mapNesting($column);
        }
        $operator = self::mapOperator($operator);
        if(in_array($operator, array(self::FIND_IN_SET, self::MATCH_AGAINST)))
        {
            $value = preg_split('/\s+/i', (array)$value);
        }
        if(!in_array($operator, array(self::IS, self::IS_NOT, self::IS_NULL, self::IS_NOT_NULL)))
        {
            $value = $this->setValues($value);
        }
        $obj = new XO();
        $obj->mask = true;
        $obj->type = __FUNCTION__;
        $obj->column = $column;
        $obj->value = $value;
        $obj->operator = $operator;
        $obj->connector = self::mapConnector($connector);
        $this->_where[] = $obj;
        return $this;
    }


    /**
     * use this function to nest where conditions encapsulating the where conditions
     * in closure with brackets like:
     *
     * <code>
     *      $filter->nest(function($filter){
     *          $filter->nest(function($filter){
     *              $filter->where("foo", '1', XOF::EQUAL);
     *              $filter->where("name", 'test', XOF::EQUAL);
     *          }, 'OR');
     *          $filter->nest(function($filter){
     *              $filter->where("foo", '2', XOF::EQUAL);
     *              $filter->where("name", 'foo', XOF::EQUAL);
     *          }, 'OR');
     *      }, 'OR');
     * </code>
     *
     * will produce a sql statement like: "WHERE ((foo = 1 OR name = 'test) OR (foo = 2 OR name = 'foo'))
     * this will allow for very complex where conditions. the second parameter can be set to overwrite
     * the connector defined in the closure itself
     *
     * @error 13621
     * @param Closure $closure expects instance of closure
     * @param null|string $connector expects connector that will connect the next where condition
     * @return Xapp_Orm_Filter
     */
    public function nest(Closure $closure, $connector = null)
    {
        return $this->mapNesting($closure, $connector);
    }


    /**
     * add a like where condition to filter. use this function instead of using where() function
     * since querying like is one of the most used where conditions. use the full like syntax
     * will all wildcard characters and dont escape the values used since the whole like pattern
     * is escaped by pdo statement class. e.g. use for value: %foo% or %$foo%
     *
     * @error 13622
     * @param string $column expects the column for the where condition
     * @param string $value expects the full like pattern containing wildcard characters and value
     * @param string $connector expects connector that will connect the next where condition
     * @return Xapp_Orm_Filter
     */
    public function like($column, $value, $connector = 'AND')
    {
        $obj = new XO();
        $obj->mask = true;
        $obj->type = __FUNCTION__;
        $obj->column = $column;
        $obj->value = $this->setValues($value);
        $obj->operator = self::LIKE;
        $obj->connector = self::mapConnector($connector);
        $this->_where[] = $obj;
        return $this;
    }


    /**
     * negation of like function. see all explanations in Xapp_Orm_Filter::like function
     *
     * @see Xapp_Orm_Filter::like
     * @error 13623
     * @param string $column expects the column for the where condition
     * @param string $value expects the full like pattern containing wildcard characters and value
     * @param string $connector expects connector that will connect the next where condition
     * @return Xapp_Orm_Filter
     */
    public function notLike($column, $value, $connector = 'AND')
    {
        $obj = new XO();
        $obj->mask = true;
        $obj->type = __FUNCTION__;
        $obj->column = $column;
        $obj->value = $this->setValues($value);
        $obj->operator = self::NOT_LIKE;
        $obj->connector = self::mapConnector($connector);
        $this->_where[] = $obj;
        return $this;
    }


    /**
     * add match against to where clause activating full text search if supported by driver.
     * pass additional modifier for database driver. e.g. for mysql pass a string like 'IN BOOLEAN MODE'
     * to activate boolean search. pass multiple columns ar array in first parameter and multiple search
     * words as array in second parameter even though these will be implode to a string again because
     * match against mysql implementation only understands on string in against clause. set the words
     * with search modifiers +- etc.
     *
     * @error 13624
     * @param string|array $column expects single column or column list as array
     * @param string|array $value expects search words as string or array
     * @param null|string $modifier expects optional operator modifiers
     * @param string $connector expects connector that will connect the next where condition
     * @return Xapp_Orm_Filter
     */
    public function match($column, $value, $modifier = null, $connector = 'AND')
    {
        if(is_array($value))
        {
            $value = implode(' ', $value);
        }
        $obj = new XO();
        $obj->mask = true;
        $obj->type = __FUNCTION__;
        $obj->column = $column;
        $obj->value = $this->setValues($value);
        $obj->operator = self::MATCH_AGAINST;
        $obj->connector = self::mapConnector($connector);
        $obj->modifier = $modifier;
        $this->_where[] = $obj;
        return $this;
    }


    /**
     * add group column to filter with additional modifier. NOTE: the modifier
     * will be set to each group object but used only once in add end of group
     * statement
     *
     * @error 13625
     * @param string|array $column expects single column or column list as array
     * @param string $modifier expects optional modifier value
     * @return Xapp_Orm_Filter
     */
    public function group($column, $modifier = null)
    {
        foreach((array)$column as $c)
        {
            $obj = new XO();
            $obj->column = $c;
            $obj->modifier = $modifier;
            $this->_group[] = $obj;
        }
        return $this;
    }


    /**
     * add having clause to filter. see Xapp_Orm_Filter::where since functionality is the same
     *
     * @see Xapp_Orm_Filter::where
     * @error 13626
     * @param string $column expects the column for the where condition
     * @param null|mixed $value expects the value for where condition which can also be an expression
     * @param string $operator expects the where condition operator
     * @param string $connector expects connector that will connect the next where condition
     * @return Xapp_Orm_Filter
     */
    public function having($column, $value, $operator = self::EQUAL, $connector = 'AND')
    {
        $operator = self::mapOperator($operator);
        if(in_array($operator, array(self::FIND_IN_SET, self::MATCH_AGAINST)))
        {
            $value = preg_split('/\s+/i', (array)$value);
        }
        if(!in_array($operator, array(self::IS, self::IS_NOT, self::IS_NULL, self::IS_NOT_NULL)))
        {
            $value = $this->setValues($value);
        }
        $obj = new XO();
        $obj->mask = true;
        $obj->type = __FUNCTION__;
        $obj->column = $column;
        $obj->value = $value;
        $obj->operator = $operator;
        $obj->connector = self::mapConnector($connector);
        $this->_having[] = $obj;
        return $this;
    }


    /**
     * add raw where condition to filter. raw conditions should be written with ? pdo
     * statement placeholders. e.g. UNIX_TIMESTAMP(column) LIKE ? and add the value %test%
     * for example to second parameter array. the first parameter can be also an array
     * of custom raw where queries - just make sure that all values are either escaped
     * properly or placeholder values exist in second parameter array. the connector in
     * third parameter can be single connector string or array of connectors foreach where
     * condition if multiple passed in first parameter
     *
     * @error 13627
     * @param string|array $where expects raw where condition as single string or array conditions
     * @param null|mixed|array $values expects optional parameter values
     * @param string|array $connector expects connector(s) that will connect the next where condition
     * @return Xapp_Orm_Filter
     */
    public function whereRaw($where, $values = null, $connector = 'AND')
    {
        $connector = (array)$connector;
        $i = 0;
        foreach((array)$where as $w)
        {
            $obj = new XO();
            $obj->mask = false;
            $obj->type = __FUNCTION__;
            $obj->column = $w;
            $obj->connector = (isset($connector[$i])) ? self::mapConnector($connector[$i]) : 'AND';
            $this->_where[] = $obj;
            $i++;
        }
        $this->_values = array_merge((array)$this->_values, (array)$values);
        return $this;
    }


    /**
     * reset where conditions
     *
     * @error 13628
     * @return void
     */
    public function resetWhere()
    {
        $this->_where = null;
    }


    /**
     * reset any filter property by calling this function and passing
     * the adequate value, e.g. reset all tables call reset('table')
     *
     * @error 13629
     * @param string $what expects the filter property to reset
     * @return void
     */
    public function reset($what)
    {
        $what = "_" . trim(trim($what), "_");
        if($this->has($what))
        {
            $this->$what = null;
        }
    }


    /**
     * set all parameter values for prepared statements overwriting all values
     * that has been set before. this function should be used only under special
     * circumstances since parameters must always match ? placeholders if not will
     * produce errors
     *
     * @error 13630
     * @param string|array $values expects string/array of parameter values to overwrite
     * @return Xapp_Orm_Filter
     */
    public function values($values)
    {
        $this->_values = (array)$values;
        return $this;
    }


    /**
     * set all bindings values for prepared statements for update/delete/insert
     * statements with : placeholder. bindings must be in key => value pairs. this
     * function should only be used under special circumstances to reset all bindings
     * values
     *
     * @error 13631
     * @param array $bindings expects array of bindings with key => value pairs
     * @return Xapp_Orm_Filter
     */
    public function bindings(Array $bindings)
    {
        $this->_bindings = $bindings;
        return $this;
    }


    /**
     * get any filter property from instance
     *
     * @error 13632
     * @param string $what expects property name to get value for
     * @return mixed
     * @throws Xapp_Orm_Filter_Exception
     */
    public function get($what)
    {
        $what = "_" . trim(trim($what), "_");
        if($this->has($what))
        {
            return $this->$what;
        }else{
            throw new Xapp_Orm_Filter_Exception(xapp_sprintf(_("unable to get: %s since property does not exist"), $what), 1363201);
        }
    }


    /**
     * checks whether a property exists and is not null since all relevant filter properties
     * have a default null value and will have once reset
     *
     * @error 13633
     * @param string $what expects the property name to check for
     * @return bool
     */
    public function has($what)
    {
        $what = "_" . trim(trim($what), "_");
        if(xapp_property_exists($this, $what) && $this->$what !== null)
        {
            return true;
        }else{
            return false;
        }
    }


    /**
     * set parameter value as expected for select statements using ? placeholder.
     * returns the same value passed into function. also will handle expression
     * set have placeholder values set. expression is a statement that can have place
     * holders ?. the expression is handled as raw statement but the value must be
     * retrieved from expression instance and added to filter values
     *
     * @error 13634
     * @param mixed|array $values expects single values or values in array
     * @return mixed
     */
    protected function setValues($values)
    {
        if(!is_array($values))
        {
            $_values = array($values);
        }else{
            $_values = $values;
        }
        foreach($_values as $k => $v)
        {
            if($v instanceof Xapp_Orm_Expression)
            {
                $this->setValues($v->values());
            }else{
                $this->_values[] = $v;
            }
        }
        return $values;
    }


    /**
     * get all parameters values
     *
     * @error 13635
     * @return mixed|null
     */
    public function getValues()
    {
        return $this->_values;
    }


    /**
     * checks if there are any parameters set or not
     *
     * @error 13636
     * @return bool
     */
    public function hasValues()
    {
        return ($this->_values !== null && sizeof($this->_values) > 0) ? true : false;
    }


    /**
     * set bindings parameter key => value pairs internaly for update/delete/insert statements
     * bindings must always have key name and an associated value. this function can digest
     * single and multiple values. single value in first and second parameter or all pairs
     * only in first parameter as array or two array for keys in first parameter and values
     * in first parameter
     *
     * @error 13637
     * @param string|array $name expects parameter keys as explained above
     * @param null|mixed|array $value expects parameter values as explained above
     * @return null|array
     * @throws Xapp_Orm_Filter_Exception
     */
    protected function setBindings($name, $value = null)
    {
        if(is_array($name) && is_array($value))
        {
            if(sizeof($name) === sizeof($value))
            {
                for($i = 0; $i < sizeof($name); $i++)
                {
                    $this->_bindings[trim(trim($name[$i]), ':')] = $value[$i];
                }
            }else{
                throw new Xapp_Orm_Filter_Exception(_("binding array values must have the same array length"));
            }
        }else if(is_array($name) && $value === null){
            foreach($name as $k => $v)
            {
                $this->_bindings[trim(trim($k), ':')] = $v;
            }
        }else{
            $this->_bindings[trim(trim($name), ':')] = $value;
        }
        return $this->_bindings;
    }


    /**
     * return all bindings
     *
     * @error 13638
     * @return mixed|null
     */
    public function getBindings()
    {
        return $this->_bindings;
    }


    /**
     * check whether any bindings has been set or not
     *
     * @error 13639
     * @return bool
     */
    public function hasBindings()
    {
        return ($this->_bindings !== null && sizeof($this->_bindings) > 0) ? true : false;
    }


    /**
     * return filter action set in constructor or short cut constructor methods
     *
     * @error 13640
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }


    /**
     * maps the nesting of where conditions when where conditions are passed
     * as closure. the conditions passed by closure are merged with the where
     * conditions set regular. furthermore if the the nesting function has been
     * called with its own connector the connector from the nested where conditions
     * in closure will be overwritten since it has higher priority.
     *
     * @see Xapp_Orm_Filter::nest
     * @error 13641
     * @param Closure $closure expects instance of closure
     * @param null|string $connector expects optional connector
     * @return Xapp_Orm_Filter
     */
    protected function mapNesting(Closure $closure, $connector = null)
    {
        $filter = new self();
        call_user_func($closure, $filter);
        if($filter->has('where'))
        {
            $where = (array)$filter->get('where');
            if($connector !== null)
            {
                $connector = self::mapConnector($connector);
                if(!function_exists('_renest'))
                {
                    function _renest(&$where, $connector, $pointer = 'reset')
                    {
                        $e = $pointer($where);
                        if($e instanceof stdClass)
                        {
                            $e->connector = $connector;
                        }else{
                            _renest($e, $connector, 'end');
                        }
                    }
                }
                _renest($where, $connector);
            }
            $this->_where[] = $where;
        }
        $this->_values = array_merge((array)$this->_values, (array)$filter->_values);

        return $this;
    }


    /**
     * map operator either by receiving short code operator returning the equivalent
     * long code operator code for or return the long code directly if supported by
     * filter class. if the operator is not supported will throw error. use custom
     * where function instead
     *
     * @error 13642
     * @param string $operator expects short or long code operator
     * @return string
     * @throws Xapp_Orm_Filter_Exception
     */
    public static function mapOperator($operator)
    {
        if(!defined("self::" . strtoupper($operator)))
        {
            if(($key = array_search(trim(strtolower($operator)), self::$_operatorMap)) !== false)
            {
                return $key;
            }else{
                throw new Xapp_Orm_Filter_Exception(xapp_sprintf(_("operator: %s is not supported"), $operator), 1364201);
            }
        }else{
            return strtoupper($operator);
        }
    }


    /**
     * map connector by passing either short code or long code connector value. if short code
     * is passed will look for long code equivalent. throws error if the connector value
     * does not exist
     *
     * @error 13643
     * @param string $connector expects the connector value to be mapped
     * @return string
     * @throws Xapp_Orm_Filter_Exception
     */
    public static function mapConnector($connector)
    {
        $connector = trim($connector);
        if(in_array($connector, self::$_connectorMap))
        {
            return self::$_connectorMap[$connector];
        }
        if(array_key_exists(strtoupper($connector), self::$_connectorMap))
        {
            return strtoupper($connector);
        }else{
            throw new Xapp_Orm_Filter_Exception(xapp_sprintf(_("connector: %s is not supported"), $connector), 1364301);
        }
    }


    /**
     * execute the filter and have it compiled by query class to return full sql statement
     * in return on current connection or connection passed optional in first parameter
     *
     * @error 13644
     * @param null $connection expects optional connection name or instance
     * @return string
     */
    final public function execute($connection = null)
    {
        return Xapp_Orm_Query::create($connection)->execute($this);
    }


    /**
     * on clone reset all where conditions
     *
     * @error 13645
     * @return void
     */
    public function __clone()
    {
        $this->resetWhere();
    }


    /**
     * return object on string conversion
     *
     * @error 13646
     * @return string
     */
    final public function __toString()
    {
        return spl_object_hash($this);
    }
}
<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Orm.Exception');
xapp_import('xapp.Orm.Query');
xapp_import('xapp.Orm.Filter');

/**
 * Orm base class
 *
 * @package Orm
 * @class Xapp_Orm
 * @error 129
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Orm
{
    /**
     * constant to define query fetch mode - all fetches all results
     *
     * @const FETCH_ALL
     */
    const FETCH_ALL             = 'all';

    /**
     * constant to define query fetch mode - first fetches only first row
     *
     * @const FETCH_FIRST
     */
    const FETCH_FIRST           = 'first';

    /**
     * constant to define query fetch mode - last fetches last row of result
     *
     * @const FETCH_LAST
     */
    const FETCH_LAST            = 'last';

    /**
     * constant to define query fetch mode - one fetches the first column of first row or if passed a numeric column index
     * or associative column index
     *
     * @const FETCH_ONE
     */
    const FETCH_ONE             = 'one';

    /**
     * constant to define query fetch mode - count always returns an integer value of the rows count in result set even
     * if nothing is found returning 0
     *
     * @const FETCH_COUNT
     */
    const FETCH_COUNT           = 'count';

    /**
     * constant to define query fetch mode - object expects instance or class name of object to be passed when querying
     * and returns populated instances of the same
     *
     * @const FETCH_OBJECT
     */
    const FETCH_OBJECT          = 'object';

    /**
     * constant to define query fetch mode - column returns a specific column defined by numeric index or default 1-index
     * the first column
     *
     * @const FETCH_COLUMN
     */
    const FETCH_COLUMN          = 'column';

    /**
     * constant to define query fetch mode - func expects a callable function that will receive column name and value and
     * returns aggregated function value
     *
     * @const FETCH_FUNC
     */
    const FETCH_FUNC            = 'func';

    /**
     * constant to define query fetch mode - scroll returns pdo scrollable cursor that can be iterated by pdo fetch
     * function and scroll options
     *
     * @const FETCH_SCROLL
     */
    const FETCH_SCROLL          = 'scroll';

    /**
     * constant to define query fetch mode - paired returns array with first column as key and second column as value
     *
     * @const FETCH_PAIRED
     */
    const FETCH_PAIRED          = 'paired';


    /**
     * database driver constant for cubrid
     *
     * @const DRIVER_CUBRID
     */
    const DRIVER_CUBRID         = 'cubrid';

    /**
     * database driver constant for firebird
     *
     * @const DRIVER_FIREBIRD
     */
    const DRIVER_FIREBIRD       = 'firebird';

    /**
     * database driver constant for mysql
     *
     * @const DRIVER_MYSQL
     */
    const DRIVER_MYSQL          = 'mysql';

    /**
     * database driver constant for oci
     *
     * @const DRIVER_OCI
     */
    const DRIVER_OCI            = 'oci';

    /**
     * database driver constant for odbc
     *
     * @const DRIVER_ODBC
     */
    const DRIVER_ODBC           = 'odbc';

    /**
     * database driver constant for pgsql
     *
     * @const DRIVER_PGSQL
     */
    const DRIVER_PGSQL          = 'pgsql';

    /**
     * database driver constant for sqlite
     *
     * @const DRIVER_SQLITE
     */
    const DRIVER_SQLITE         = 'sqlite';


    /**
     * class option dsn expects the pdo dsn string
     *
     * @const DSN
     */
    const DSN                   = 'ORM_DSN';

    /**
     * class option user expects the pdo user name
     *
     * @const USER
     */
    const USER                  = 'ORM_USER';

    /**
     * class option pass expects the pdo password if required. the password should be passed in class constructor or
     * factory method for security reason
     *
     * @const PASS
     */
    const PASS                  = 'ORM_PASS';

    /**
     * class option persistent expects boolean value to set pdo driver
     * option persistent
     *
     * @const PERSISTENT
     */
    const PERSISTENT            = 'ORM_PERSISTENT';

    /**
     * class option charset expects default connection charset
     *
     * @const CHARSET
     */
    const CHARSET               = 'ORM_CHARSET';

    /**
     * class option debug expects boolean value to de/activate orm debug modus using xapp internal debug system defined
     * in xapp config
     *
     * @const DEBUG
     */
    const DEBUG                 = 'ORM_DEBUG';

    /**
     * class option pdo options expects optional pdo driver options set when instantiating pdo class
     *
     * @const PDO_OPTIONS
     */
    const PDO_OPTIONS           = 'ORM_PDO_OPTIONS';

    /**
     * class option error mode expects error mode value which can be of:
     * 1)   ERROR_MODE_WARNING will set pdo error mode to warning and return
     *      boolean false values for empty results
     * 2)   ERROR_MODE_EXCEPTION will throw exception on empty results
     * 3)   ERROR_MODE_BOOLEAN will return false on empty results
     *
     * @const ERROR_MODE
     */
    const ERROR_MODE            = 'ORM_ERROR_MODE';

    /**
     * class option pdo attributes expects optional attributes to be set after pdo instance is created
     *
     * @const PDO_ATTRIBUTES
     */
    const PDO_ATTRIBUTES        = 'ORM_PDO_ATTRIBUTES';

    /**
     * class option timeout expects connection timeout value
     *
     * @const TIMEOUT
     */
    const TIMEOUT               = 'ORM_TIMEOUT';

    /**
     * class option table prefix expects optional table prefix for all tables in same database as defined in pdo dsn string.
     * the table prefix will be considered in all queries that are not custom. set prefix like e.g. "tbl_"
     *
     * @const TABLE_PREFIX
     */
    const TABLE_PREFIX          = 'ORM_TABLE_PREFIX';

    /**
     * class option log expects either log file pointer (full qualified absolute log file pointer) or xapp log class instance
     * that implements log interface
     *
     * @const LOG
     */
    const LOG                   = 'ORM_LOG';

    /**
     * class option log modes expects array of values defining which sql queries to log. e.g. array('select', 'delete'),
     * ... complies to sql action modes
     *
     * @const LOG_MODES
     */
    const LOG_MODES             = 'ORM_LOG_MODES';

    /**
     * class option to define whether to log PDO statement debug info. if set to true will store debug info if set to true
     * nothing is logged
     *
     * @const DEBUG_STATEMENTS
     */
    const DEGUG_STATEMENTS       = 'ORM_DEBUG_STATEMENTS';

    /**
     * class option allow only crud if set to true will only allow crud operations
     * (insert, update, delete, select) all other queries are blocked when they are called
     * via generic query methods (Xapp_Orm::query, Xapp_Orm::exec, Xapp_Orm::fetch and dynamic
     * fetch functions)
     *
     * @const ALLOW_ONLY_CRUD
     */
    const ALLOW_ONLY_CRUD       = 'ORM_ALLOW_ONLY_CRUD';

    /**
     * class option to emulate mysqlnd native typed results if mysqlnd is not enabled. this optionmwill turn any float and
     * int values as string into real int and float values just like mysqlnd native would do
     *
     * @const EMULATE_NATIVE_TYPES
     */
    const EMULATE_NATIVE_TYPES  = 'ORM_EMULATE_NATIVE_TYPES';


    /**
     * error mode warning as explained in ERROR_MODE
     *
     * @const ERROR_MODE_WARNING
     */
    const ERROR_MODE_WARNING    = 3;

    /**
     * error mode warning as explained in ERROR_MODE
     *
     * @const ERROR_MODE_BOOLEAN
     */
    const ERROR_MODE_BOOLEAN    = 2;

    /**
     * error mode exception as explained in ERROR_MODE
     *
     * @const ERROR_MODE_EXCEPTION
     */
    const ERROR_MODE_EXCEPTION  = 1;


    /**
     * contains passwords passed via class constructor or factory methods the password can only be retrieved by same instance
     *
     * @var array
     */
    private static $_pass = array();

    /**
     * contains all sql queries as strings to be evaluated
     *
     * @var array
     */
    protected static $_queries = array();

    /**
     * contains sql log entries as explained in log method
     *
     * @var array
     */
    protected static $_log = array();

    /**
     * contains current connection which is instance of this class or sub driver classes
     *
     * @var null|Xapp_Orm
     */
    protected static $_connection = null;

    /**
     * pool of all connections created
     *
     * @var array
     */
    protected static $_connections = array();

    /**
     * pool of statement debug information
     *
     * @var array
     */
    protected static $_statements = array();

    /**
     * boolean value of connection status
     *
     * @var bool
     */
    protected $_connected = false;

    /**
     * can contain table to be used in quick aggregate queries methods max, min, size, ... called like
     * $connection->table('table')->max('column') the table value can be set only once until other table is queried
     *
     * @var null|string
     */
    protected $_table = null;


    /**
     * contains database name for which the connection instance was instantiated with
     *
     * @var null|string
     */
    public $database = null;

    /**
     * contains driver name for which the connection instance was instantiated with
     *
     * @var null|string
     */
    public $driver = null;

    /**
     * contains pdo instance for this driver instance
     *
     * @var null|PDO
     */
    public $pdo = null;



    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::DSN                   => XAPP_TYPE_STRING,
        self::USER                  => XAPP_TYPE_STRING,
        self::PASS                  => XAPP_TYPE_STRING,
        self::PERSISTENT            => XAPP_TYPE_BOOL,
        self::CHARSET               => XAPP_TYPE_STRING,
        self::DEBUG                 => XAPP_TYPE_BOOL,
        self::PDO_OPTIONS           => XAPP_TYPE_ARRAY,
        self::ERROR_MODE            => XAPP_TYPE_INT,
        self::PDO_ATTRIBUTES        => XAPP_TYPE_ARRAY,
        self::TIMEOUT               => XAPP_TYPE_INT,
        self::TABLE_PREFIX          => XAPP_TYPE_STRING,
        self::LOG                   => array(XAPP_TYPE_FILE, 'Xapp_Log_Interface'),
        self::LOG_MODES             => XAPP_TYPE_ARRAY,
        self::DEGUG_STATEMENTS      => XAPP_TYPE_BOOL,
        self::ALLOW_ONLY_CRUD       => XAPP_TYPE_BOOL,
        self::EMULATE_NATIVE_TYPES  => XAPP_TYPE_BOOL
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::DSN                   => 1,
        self::USER                  => 0,
        self::PASS                  => 0,
        self::PERSISTENT            => 1,
        self::CHARSET               => 0,
        self::DEBUG                 => 1,
        self::PDO_OPTIONS           => 0,
        self::ERROR_MODE            => 0,
        self::PDO_ATTRIBUTES        => 0,
        self::TIMEOUT               => 0,
        self::TABLE_PREFIX          => 0,
        self::LOG                   => 0,
        self::LOG_MODES             => 0,
        self::DEGUG_STATEMENTS      => 1,
        self::ALLOW_ONLY_CRUD       => 1,
        self::EMULATE_NATIVE_TYPES  => 1
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::ALLOW_ONLY_CRUD       => true,
        self::EMULATE_NATIVE_TYPES  => true,
        self::PERSISTENT            => false,
        self::CHARSET               => 'utf8',
        self::DEBUG                 => false,
        self::TIMEOUT               => 30,
        self::LOG_MODES             => array('select'),
        self::DEGUG_STATEMENTS      => false,
        self::ERROR_MODE            => self::ERROR_MODE_EXCEPTION,
        self::PDO_ATTRIBUTES        => array
                                    (
                                        PDO::ATTR_CASE => PDO::CASE_NATURAL,
                                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
                                        PDO::ATTR_STRINGIFY_FETCHES => false,
                                        PDO::ATTR_EMULATE_PREPARES => false
                                    )
    );


    /**
     * class constructor validated options sets driver and database names, password if set and connects to database
     * calling connect and init function. second argument expects optional connection name which if not set will be set
     * automatically "c0", "c1" starting with instance 0. each orm instances needs a connection name regardless of how
     * the orm class is instantiated. third argument password expects optional db password if not passed in dsn connection
     * string option. NOTE: passing connection name will overwrite connections already set under same connection name
     * previously
     *
     * @error 12901
     * @param null|mixed $options expects optional options
     * @param null|mixed $name expects optional connection name
     * @param null|string $password expects optional password
     * @throws Xapp_Orm_Exception
     */
    public function __construct($options = null, $name = null, $password = null)
    {
        try
        {
            xapp_init_options($options, $this);
            $dsn = xapp_get_option(self::DSN, $this);
            if(version_compare(PHP_VERSION, '5.3.6', '>=') && stripos($dsn, 'charset') === false)
            {
                xapp_set_option(self::DSN, trim($dsn, ';') . ';charset=' . strtolower(xapp_get_option(self::CHARSET, $this)));
            }
            if(preg_match('/dbname\=(?:([^\;]+)|$)/i', xapp_get_option(self::DSN, $this), $m))
            {
                $this->database = trim($m[1]);
            }else{
                throw new Xapp_Orm_Exception(_("no database selected in dsn string"), 1290103);
            }
            if($password !== null)
            {
                self::$_pass[spl_object_hash($this)] = $password;
            }
            if($name === null)
            {
                $name = 'c' . sizeof(self::$_connections);
            }
            if(preg_match('/^('.implode('|', Xapp_Orm_Driver::getDriver()).')\:/i', xapp_get_option(self::DSN, $this), $m))
            {
                $this->driver = trim($m[1]);
                $this->connect();
                $this->init();
                self::$_connection = self::$_connections[$name] = $this;
            }else{
                throw new Xapp_Orm_Exception(xapp_sprintf(_("the pdo driver in dsn: %s is not supported by the system"), $dsn), 1290102);
            }
        }
        catch(PDOException $e)
        {
            throw new Xapp_Orm_Exception(xapp_sprintf(_("pdo error: %d, %s"), $e->getCode(), $e->getMessage()), 1290101);
        }
    }


    /**
     * instantiate new pdo connection and connect to database
     *
     * @error 12902
     * @return void
     * @throws Xapp_Orm_Exception
     */
    protected function connect()
    {
        try
        {
            if(isset(self::$_pass[spl_object_hash($this)]))
            {
                $pass = self::$_pass[spl_object_hash($this)];
            }else{
                $pass = (string)xapp_get_option(self::PASS, $this);
            }
            $options = array
            (
                PDO::ATTR_PERSISTENT => (bool)xapp_get_option(self::PERSISTENT, $this)
            );
            $options = $options + (array)xapp_get_option(self::PDO_OPTIONS, $this);
            if(!$this->_connected)
            {
                $this->pdo = new PDO
                (
                    (string)xapp_get_option(self::DSN, $this),
                    (string)xapp_get_option(self::USER, $this),
                    $pass,
                    $options
                );
                $this->_connected = true;
                xapp_debug('connected to: ' . $this->database, 'orm');
                xapp_event('xapp.orm.connect', array(&$this));
            }
        }
        catch(PDOException $e)
        {
            throw new Xapp_Orm_Exception(xapp_sprintf(_("pdo error: %d, %s"), $e->getCode(), $e->getMessage()), 1290201);
        }
    }


    /**
     * after connection init set pdo attributes
     *
     * @error 12903
     * @return void
     * @throws Xapp_Orm_Exception
     */
    protected function init()
    {
        try
        {
            if(xapp_is_option(self::PDO_ATTRIBUTES, $this))
            {
                foreach(xapp_get_option(self::PDO_ATTRIBUTES, $this) as $k => $v)
                {
                    $this->pdo->setAttribute((int)$k, $v);
                }
            }
            if(xapp_get_option(self::ERROR_MODE, $this) === self::ERROR_MODE_WARNING)
            {
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            }
            if(xapp_is_option(self::TIMEOUT, $this));
            {
                $this->pdo->setAttribute(PDO::ATTR_TIMEOUT, (int)xapp_get_option(self::TIMEOUT, $this));
            }
        }
        catch(PDOException $e)
        {
            throw new Xapp_Orm_Exception(xapp_sprintf(_("pdo error: %d, %s"), $e->getCode(), $e->getMessage()), 1290301);
        }
    }


    /**
     * class factory method creates new instance for according driver passed in first parameter. the factory method expects
     * a connection name since multiple connections with different drivers can be created. the connection name or identifier
     * can be used to switch/select another connection as the current one at any time
     *
     * @error 12904
     * @param string $driver expects the driver name e.g. (mysql, cubrid,...)
     * @param string|int $name expects the connection name/identifier (should be string as proper identifier)
     * @param null|mixed $options expects optional class options
     * @param null|string $password expects optional password
     * @return Xapp_Orm_Driver instance
     * @throws Xapp_Orm_Exception
     */
    public static function factory($driver, $name, $options = null, $password = null)
    {
        if(in_array(strtolower($driver), Xapp_Orm_Driver::getDriver()))
        {
            if(!array_key_exists($name, self::$_connections))
            {
                $driver = __CLASS__ . '_Driver_' . ucfirst(strtolower($driver));
                if(class_exists($driver, true))
                {
                    return new $driver($options, $name, $password);
                }else{
                    throw new Xapp_Orm_Exception(xapp_sprintf(_("driver class for driver: %s is not implemented"), $driver), 1290402);
                }
            }else{
                return self::$_connection = self::$_connections[$name];
            }
        }else{
            throw new Xapp_Orm_Exception(xapp_sprintf(_("driver: %s is not supported by this system"), $driver), 1290401);
        }
    }


    /**
     * set or return pdo instance for this driver instance. setting pdo instance after itmhas been created in class
     * constructor is not recommended since class init method are not executed when setting new pdo instance. all pdo
     * options must be set before passing
     *
     * @error 12905
     * @param PDO $pdo
     * @return null|PDO
     */
    public function pdo(PDO $pdo = null)
    {
        if($pdo !== null)
        {
            return $this->pdo = $pdo;
        }else{
            return $this->pdo;
        }
    }


    /**
     * heart function of the xapp orm implementation. get and set connections with this method combining the factory method
     * with the getter functionality of either getting any connection by connection name/ident or the current connection.
     * if no parameter is set will always return the current connection! if the second parameter is set will look for
     * connection in connection pool by its name/ident. if found will return the connection setting it as the current
     * connection. it not found will try to create the connection using the factory method when dsn string in option
     * contains the driver to instantiate class for. if not will throw exception since connection can not be created
     * without concrete driver class existent and driver not beeing passed in dsn options
     *
     * @error 12906
     * @param null|string|int $name expects the optional connection name
     * @param null|mixed $options expects the optional class options
     * @param null|string $password expects optional password
     * @return null|Xapp_Orm|Xapp_Orm_Driver
     * @throws Xapp_Orm_Exception
     */
    public static function connection($name = null, $options = null, $password = null)
    {
        if($name !== null)
        {
            if(!array_key_exists($name, self::$_connections))
            {
                $options = (array)$options;
                if(array_key_exists(self::DSN, $options))
                {
                    if(preg_match('/^('.implode('|', Xapp_Orm_Driver::getDriver()).')\:/i', $options[self::DSN], $m))
                    {
                        $driver = __CLASS__ . '_Driver_' . ucfirst(strtolower($m[1]));
                        if(class_exists($driver, true))
                        {
                            return self::factory($m[1], $name, $options, $password);
                        }else{
                            throw new Xapp_Orm_Exception(xapp_sprintf(_("concrete driver implementation for: %s does not exist"), $driver), 1290603);
                        }
                    }else{
                        throw new Xapp_Orm_Exception(xapp_sprintf(_("driver in: %s is not supported by system"), $options[self::DSN]), 1290602);
                    }
                }else{
                    throw new Xapp_Orm_Exception(_("dsn string in class options must be set when creating new connection"), 1290601);
                }
            }else{
                return self::$_connection = self::$_connections[$name];
            }
        }else{
            return self::$_connection;
        }
    }


    /**
     * select connection by name and set it as current connection will throw exception if connection does not exist.
     *
     * @error 12907
     * @param string|int $name expects connection name/ident
     * @return Xapp_Orm_Driver
     * @throws Xapp_Orm_Exception
     */
    public static function select($name)
    {
        if(array_key_exists($name, self::$_connections))
        {
            return self::$_connection = self::$_connections[$name];
        }else{
            throw new Xapp_Orm_Exception(xapp_sprintf(_("connection: %s is not set"), $name), 1290701);
        }
    }


    /**
     * magic transaction method by function callback. pass a callable function to this method and encapsulate your
     * transaction logic in that function. before callable is executed transaction is started and after callback committed.
     * the callback function will receive instance of connection defined by second parameter name which is the connection
     * name/ident which is empty by default equivalent to current connection. returns the callback result.
     *
     * @error 12908
     * @param callable $callback expects callback function
     * @param null|string|int $name expects optional connection name/ident
     * @return mixed
     * @throws Xapp_Orm_Exception
     */
    public static function transaction($callback, $name = null)
    {
        $return = null;

        if(is_callable($callback))
        {
            self::begin($name);
            try
            {
                $return = call_user_func_array($callback, array(self::connection()));
                self::commit();
                return $return;
            }
            catch(Exception $e)
            {
                self::rollback();
                throw new Xapp_Orm_Exception(xapp_sprintf(_("transaction error: %d, %s"), $e->getCode(), $e->getMessage()), 1290802);
            }
        }else{
            throw new Xapp_Orm_Exception(_("first parameter callback must be a valid callback"), 1290801);
        }
    }


    /**
     * start pdo transaction for current connection or connection passed in second parameter which will be set a current
     * connection if not null.
     *
     * @error 12909
     * @param null|string|int $name expects optional connection name/ident
     * @return void
     * @throws Xapp_Orm_Exception
     */
    public static function begin($name = null)
    {
        try
        {
            if($name !== null)
            {
               self::connection($name);
            }
            xapp_event('xapp.orm.begin', array(self::connection()));
            if((bool)self::connection()->pdo->inTransaction() === false)
            {
                self::connection()->pdo->beginTransaction();
            }else{
                throw new Xapp_Orm_Exception(_("transaction is already active and can not be started"), 1290902);
            }
        }
        catch(PDOException $e)
        {
            throw new Xapp_Orm_Exception(xapp_sprintf(_("pdo error: %d, %s"), $e->getCode(), $e->getMessage()), 1290901);
        }
    }


    /**
     * commit a transaction if in transaction on current connection
     *
     * @error 12910
     * @return void
     * @throws Xapp_Orm_Exception
     */
    public static function commit()
    {
        try
        {
            xapp_event('xapp.orm.commit', array(self::connection()));
            if((bool)self::connection()->pdo->inTransaction() === true)
            {
                self::connection()->pdo->commit();
            }
        }
        catch(PDOException $e)
        {
            throw new Xapp_Orm_Exception(xapp_sprintf(_("pdo error: %d, %s"), $e->getCode(), $e->getMessage()), 1291001);
        }
    }


    /**
     * rollback a transaction if in transaction on current connection
     *
     * @error 12911
     * @return void
     * @throws Xapp_Orm_Exception
     */
    public static function rollback()
    {
        try
        {
            xapp_event('xapp.orm.rollback', array(self::connection()));
            if((bool)self::connection()->pdo->inTransaction() === true)
            {
                self::connection()->pdo->rollback();
            }
        }
        catch(PDOException $e)
        {
            throw new Xapp_Orm_Exception(xapp_sprintf(_("pdo error: %d, %s"), $e->getCode(), $e->getMessage()), 1291101);
        }
    }


    /**
     * return current driver string
     *
     * @error 12912
     * @return null|string
     */
    public function driver()
    {
        return $this->driver;
    }


    /**
     * test connection if still connected returning boolean value
     *
     * @error 12913
     * @return bool
     */
    public function connected()
    {
        try
        {
            $stmt = $this->pdo->query('SELECT 1');
            if($stmt === false || $stmt->rowCount() !== 1)
            {
                return $this->_connected = false;
            }else{
                return $this->_connected = true;
            }
        }
        catch(Exception $e)
        {
            return $this->_connected = false;
        }
    }


    /**
     * reconnects to database if connection is lost calling connect and init methods forcing a new pdo instance to connect
     * to database
     *
     * @error 12914
     * @return void
     */
    public function reconnect()
    {
        if(!$this->connected())
        {
            xapp_event('xapp.orm.reconnect', array(&$this));
            $this->connect();
            $this->init();
        }
    }


    /**
     * get last id from of last inserted row calling pdos last id method passing sequence name if set in first parameter
     *
     * @error 12915
     * @param null|string $name expects optional sequence name
     * @return string
     * @throws Xapp_Orm_Exception
     */
    public function lastId($name = null)
    {
        try
        {
            return $this->pdo->lastInsertId($name);
        }
        catch(PDOException $e)
        {
            throw new Xapp_Orm_Exception(xapp_sprintf(_("pdo error: %d, %s"), $e->getCode(), $e->getMessage()), 1291501);
        }
    }


    /**
     * public query method intended to channel all custom select queries to underlying execute function. only select
     * queries should be passed to this function. use Xapp_Orm::fetch if you use Xapp_Orm_Filter class for querying.
     * always use sql syntax with placeholders parseable by php natives sprintf function e.g. "select * from foo where foo = %s"
     * and pass value in parameter array as first array value. the values will be auto quoted - no need to set string values
     * like "foo = '%s'". returns results from query or false if error mode is set to boolean. NOTE: you must escape/quote
     * custom queries before passing to this method using internal Xapp_Orm::quote function for all parameters!!!
     *
     * @see Xapp_Orm::execute
     * @error 12916
     * @param string|Xapp_Orm_Filter $sql expects either custom sql string query or filter instance (use custom sql string but)
     * @param null|array $params expects optional parameter array for custom queries
     * @param string $fetchmode expects the fetchmode as defined in fetch constants
     * @param null|mixed $fetcharg expects optional fetch style depending arguments - see fetch modes for more
     * @return mixed
     */
    public function query($sql, $params = null, $fetchmode = self::FETCH_ALL, $fetcharg = null)
    {
        return $this->execute($sql, $params, $fetchmode, $fetcharg);
    }


    /**
     * public query method that expects Xapp_Orm_Filter instance as first parameter. see Xapp_Orm::query or
     * Xapp_Orm::execute for more info. returns results from query or false if error mode is set to boolean
     *
     * @see Xapp_Orm::execute
     * @error 12917
     * @param Xapp_Orm_Filter $filter expects filter instance
     * @param string $fetchmode expects the fetchmode as defined in fetch constants
     * @param null|mixed $fetcharg expects optional fetch style depending arguments - see fetch modes for more
     * @return mixed
     */
    public function fetch(Xapp_Orm_Filter $filter, $fetchmode = self::FETCH_ALL, $fetcharg = null)
    {
        return $this->execute($filter, null, $fetchmode, $fetcharg);
    }


    /**
     * public query method for custom sql queries that are not select queries like insert, update, delete, ... see
     * Xapp_Orm::query for more explanation on how to use sql placeholder. returns the number of affected rows but also
     * false in case of error. check return value always against expected number of rows affected or cast to boolean to
     * see if query failed because expected query could not modify rows or pdo exec returned false
     *
     * @see Xapp_Orm::execute
     * @error 12918
     * @param string $sql expects custom sql query string for insert, update, delete actions
     * @param null|array $params expects optional parameter array for custom queries
     * @return mixed
     */
    public function exec($sql, $params = null)
    {
        return $this->execute($sql, $params);
    }


    /**
     * overloading is only allowed to shortcut query fetch modes as direct query calls, e.g to get first row of query use
     * $connection->first($query). available shortcut query methods are all methods that do have an equivalent fetch mode
     * constant like FETCH_FIRST, FETCH_COUNT, FETCH_SCROLL, ... see Xapp_Orm::execute for further explanations. returns
     * results from query or false if error mode is set to boolean
     *
     * @see Xapp_Orm::execute
     * @error 12919
     * @param string $name contains the overloading method name which is the query shortcut value
     * @param array $arguments contains arguments which will be passed to Xapp_Orm::execute function 1-to-1
     * @return mixed
     * @throws Xapp_Orm_Exception
     */
    public function __call($name, Array $arguments)
    {
        $mode = 'self::FETCH_' . trim(strtoupper($name));
        if(defined($mode) && sizeof($arguments) >= 1)
        {
            return $this->execute($arguments[0], ((isset($arguments[1])) ? $arguments[1] : null), constant($mode), ((isset($arguments[2])) ? $arguments[2] : null));
        }else{
            throw new Xapp_Orm_Exception(_("overloading only allowed for direct query calls of class fetch modes"), 1291901);
        }
    }


    /**
     * global query execute function. all queries, independent of type (select, update, insert, delete, ...) go through
     * this function. the function accepts raw queries, raw queries with placeholder compatible with php native sprintf
     * function, already preprepared pdo statements, Xapp_Orm_Filter Objects as first parameter. this function will digest
     * all of these query modes logging them, and executing them to return result according to fetch mode passed in third
     * parameter.
     *
     * @error 12920
     * @param string|PDOStatement|Xapp_Orm_Filter $statement expects the query as explained above
     * @param null|array $params expects optional parameter array
     * @param null|string $fetchmode expects the optional fetch mode
     * @param null|mixed $fetcharg expects optional fetch mode dependent fetch arguments
     * @return array|bool|int|mixed|PDOStatement
     * @throws Xapp_Result_Exception if result is empty an in exception error mode
     * @throws Xapp_Orm_Exception on generic error
     */
    protected function execute($statement, $params = null, $fetchmode = null, $fetcharg = null)
    {
        $filter = null;
        $options = array();

        try
        {
            if($statement instanceof PDOStatement)
            {
                $sql = $this->tidy($statement->queryString);
            }else if($statement instanceof Xapp_Orm_Filter){
                $sql = $this->tidy(Xapp_Orm_Query::create(self::connection())->execute($filter = $statement));
            }else{
                $sql = $this->tidy($statement);
            }
            self::$_queries[] = $sql;
            $start = microtime(true);
            $mode = $this->detect($sql);

            if($params !== null || $statement instanceof Xapp_Orm_Filter)
            {
                if($statement instanceof Xapp_Orm_Filter)
                {
                    if($statement->has('limit') && (int)$statement->get('limit')->limit === 1 && !in_array($fetchmode, array(self::FETCH_COUNT, self::FETCH_ONE, self::FETCH_OBJECT)))
                    {
                        $fetchmode = self::FETCH_FIRST;
                    }
                    if($statement->getAction() === 'select')
                    {
                        $params = (array)$statement->getValues();
                    }else{
                        $params = ($statement->hasBindings()) ? (array)$statement->getBindings() : (array)$statement->getValues();
                    }
                }else{
                    $params = (array)$params;
                }
                if(strpos($sql, '?') !== false || strpos($sql, ':') !== false)
                {
                    if($fetchmode === self::FETCH_SCROLL)
                    {
                        $options = array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL);
                    }
                    if(($statement = $this->pdo->prepare($sql, $options)) !== false)
                    {
                        if(isset($params[0]))
                        {
                            $params = array_combine(array_keys(array_fill(1, sizeof($params), "")), array_values($params));
                        }
                        foreach((array)$params as $k => $v)
                        {
                            $statement->bindValue($k, $v, self::type($v));
                        }
                        if($statement->execute() === false)
                        {
                            throw new Xapp_Orm_Exception(_("unable to execute pdo statement"), 1292001);
                        }
                    }else{
                        throw new Xapp_Orm_Exception(_("unable to prepare pdo statement"), 1292002);
                    }
                }else{
                    if(in_array($mode, array('select', 'optimize')))
                    {
                        $statement = $this->pdo->query($this->wrap($sql, $params));
                    }else{
                        $statement = $this->pdo->exec($this->wrap($sql, $params));
                    }
                }
            }else{
                if(in_array($mode, array('select', 'optimize')))
                {
                    if(stripos($sql, 'LIMIT 1') !== false && !in_array($fetchmode, array(self::FETCH_COUNT, self::FETCH_ONE, self::FETCH_OBJECT)))
                    {
                        $fetchmode = self::FETCH_FIRST;
                    }
                    $statement = $this->pdo->query($sql);
                }else{
                    $statement = $this->pdo->exec($sql);
                }
            }

            $this->log($this->driver, $this->database, $mode, $sql, $params, $start);

            if($statement instanceof PDOStatement)
            {
                if(xapp_get_option(self::DEGUG_STATEMENTS, $this))
                {
                    ob_start();
                    $statement->debugDumpParams();
                    self::$_statements[] = ob_get_contents();
                    ob_end_clean();
                }

                if($mode === 'select')
                {
                    $rows = (int)$statement->rowCount();
                    if($fetchmode !== self::FETCH_COUNT && $rows === 0)
                    {
                        if(xapp_get_option(self::ERROR_MODE, $this) === self::ERROR_MODE_EXCEPTION)
                        {
                            throw new Xapp_Result_Exception(_("query returned empty result"), 1292006);
                        }else{
                            return false;
                        }
                    }
                    //return pdo statement directly
                    if(is_null($fetchmode)){
                        return $statement;
                    //return by pdo native fetchmode
                    }else if(is_int($fetchmode)){
                        if(xapp_get_option(self::EMULATE_NATIVE_TYPES, $this) && stripos($this->pdo()->getAttribute(PDO::ATTR_CLIENT_VERSION), 'mysqlnd') === false)
                        {
                            return $this->typify((!is_null($fetcharg)) ? $statement->fetchAll($fetchmode, $fetcharg) :  $statement->fetchAll($fetchmode));
                        }else{
                            return (!is_null($fetcharg)) ? $statement->fetchAll($fetchmode, $fetcharg) :  $statement->fetchAll($fetchmode);
                        }
                    //return by xapp native fetchmode
                    }else{
                        $fetchmode = strtolower(trim($fetchmode));
                        switch($fetchmode)
                        {
                            case self::FETCH_FIRST:
                                $res = $statement->fetch(PDO::FETCH_ASSOC);
                                break;
                            case self::FETCH_LAST:
                                $res = $statement->fetchAll(PDO::FETCH_ASSOC);
                                $res = end($res);
                                break;
                            case self::FETCH_ONE:
                                $res = $statement->fetch(PDO::FETCH_BOTH);
                                if($fetcharg !== null)
                                {
                                    $fetcharg = (!is_int($fetcharg)) ? $fetcharg : (string)$fetcharg;
                                    if(isset($res[$fetcharg]))
                                    {
                                        $res = $res[$fetcharg];
                                    }else{
                                        throw new Xapp_Orm_Exception(_("unable to fetch one column value since column index does not exist"), 1292003);
                                    }
                                }else{
                                    $res = $res[0];
                                }
                                break;
                            case self::FETCH_COLUMN:
                                $res = $statement->fetchAll(PDO::FETCH_COLUMN, (int)$fetcharg);
                                break;
                            case self::FETCH_COUNT:
                                $res = $statement->fetch(PDO::FETCH_BOTH);
                                if($fetcharg !== null)
                                {
                                    $fetcharg = (!is_int($fetcharg)) ? $fetcharg : (string)$fetcharg;
                                    $res = (isset($res[$fetcharg])) ? $res[$fetcharg] : 0;
                                }else if(stripos($sql, 'COUNT(*') !== false){
                                    $res = (int)$res[0];
                                }else{
                                    $res = $rows;
                                }
                                break;
                            case self::FETCH_OBJECT:
                                if(is_string($fetcharg))
                                {
                                    $res = $statement->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $fetcharg);
                                }else if(is_object($fetcharg)){
                                    $statement->setFetchMode(PDO::FETCH_INTO, $fetcharg);
                                    $res = $statement->fetchAll();
                                }else if(!is_null($filter) && $filter->has('table')){
                                    $res = $filter->get('table');
                                    $res = $statement->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $res[0]->table);
                                }else{
                                    $res = $statement->fetchAll(PDO::FETCH_OBJ);
                                }
                                $res = (sizeof($res) === 1) ? $res[0] : $res;
                                break;
                            case self::FETCH_FUNC:
                                if(is_callable($fetcharg))
                                {
                                    $res = $statement->fetchAll(PDO::FETCH_FUNC, $fetcharg);
                                }else{
                                    throw new Xapp_Orm_Exception(_("fetch function must be of type callable and valid"), 1292004);
                                }
                                break;
                            case self::FETCH_SCROLL:
                                $res = $statement;
                                break;
                            case self::FETCH_PAIRED:
                                $tmp = array();
                                if($fetcharg !== null)
                                {
                                    if(is_array($fetcharg) && sizeof($fetcharg) >= 2)
                                    {
                                        $res = $statement->fetchAll(PDO::FETCH_ASSOC);
                                        if(array_key_exists($fetcharg[0], $res[0]) && array_key_exists($fetcharg[1], $res[0]))
                                        {
                                            foreach($res as $r)
                                            {
                                                $tmp[$r[$fetcharg[0]]] = $r[$fetcharg[1]];
                                            }
                                            $res = $tmp;
                                        }else{
                                            throw new Xapp_Orm_Exception(_("fetch mode paired arguments are not valid valid keys"), 1292008);
                                        }
                                    }else{
                                        throw new Xapp_Orm_Exception(_("fetch mode paired with fetch arguments expects array with two values"), 1292007);
                                    }
                                }else{
                                    $res = $statement->fetchAll(PDO::FETCH_NUM);
                                    foreach($res as $r)
                                    {
                                        $tmp[$r[0]] = $r[1];
                                    }
                                    $res = $tmp;
                                }
                                break;
                            default:
                                $res = $statement->fetchAll(PDO::FETCH_ASSOC);
                        }
                        $statement->closeCursor();
                        $statement = null;
                        $filter = null;
                        if(xapp_get_option(self::EMULATE_NATIVE_TYPES, $this) && stripos($this->pdo()->getAttribute(PDO::ATTR_CLIENT_VERSION), 'mysqlnd') === false)
                        {
                            return $this->typify($res);
                        }else{
                            return $res;
                        }
                    }
                }else{
                    if($statement instanceof PDOStatement)
                    {
                        $res = (int)$statement->rowCount();
                        $statement->closeCursor();
                    }else{
                        $res = $this->pdo->exec($sql);
                    }
                    $statement = null;
                    $filter = null;
                    return $res;
                }
            }else{
                $res = $statement;
                $statement = null;
                $filter = null;
                return $res;
            }
        }
        catch(PDOException $e)
        {
            throw new Xapp_Orm_Exception(xapp_sprintf(_("pdo error: %d, %s"), $e->getCode(), $e->getMessage()), 1292005);
        }
    }


    /**
     * shortcut function to query a table directly only valid for aggregate functions like min, max, size... the table
     * must be set each time calling a aggregate function or global table function like truncate drop, etc. call like
     * $connection->table('foo')->drop(); to drop table e.g.
     *
     * @error 12921
     * @param string $table expects the table to perform operations on
     * @return Xapp_Orm
     */
    public function table($table)
    {
        $this->_table = $table;
        return $this;
    }


    /**
     * sql aggregate function to get max value of a column of a table called like: $connection->table('foo')->max('column')
     * returning max value of column
     *
     * @error 12922
     * @param string $column expects the column on which to operate on
     * @return int|mixed
     * @throws Xapp_Orm_Exception
     */
    public function max($column)
    {
        if($this->_table !== null)
        {
            $return = $this->execute(Xapp_Orm_Query::create(self::connection())->max($this->_table, $column), null, self::FETCH_ONE);
            $this->_table = null;
            return $return;
        }else{
            throw new Xapp_Orm_Exception(_("table must be set prior to using this function"), 1292201);
        }

    }


    /**
     * sql aggregate function to get min value of a column of a table called like: $connection->table('foo')->min('column')
     * returning min value of column
     *
     * @error 12923
     * @param string $column expects the column on which to operate on
     * @return int|mixed
     * @throws Xapp_Orm_Exception
     */
    public function min($column)
    {
        if($this->_table !== null)
        {
            $return = $this->execute(Xapp_Orm_Query::create(self::connection())->min($this->_table, $column), null, self::FETCH_ONE);
            $this->_table = null;
            return $return;
        }else{
            throw new Xapp_Orm_Exception(_("table must be set prior to using this function"), 1292301);
        }
    }


    /**
     * sql aggregate function to get average value of a column of a table called like: $connection->table('foo')->avg('column')
     * returning average value of column
     *
     * @error 12924
     * @param string $column expects the column on which to operate on
     * @return int|mixed
     * @throws Xapp_Orm_Exception
     */
    public function avg($column)
    {
        if($this->_table !== null)
        {
            $return = $this->execute(Xapp_Orm_Query::create(self::connection())->avg($this->_table, $column), null, self::FETCH_ONE);
            $this->_table = null;
            return $return;
        }else{
            throw new Xapp_Orm_Exception(_("table must be set prior to using this function"), 1292401);
        }
    }


    /**
     * sql aggregate function to get sum of a column of a table called like: $connection->table('foo')->sum('column')
     * returning the sum value of column
     *
     * @error 12925
     * @param string $column expects the column on which to operate on
     * @return int|mixed
     * @throws Xapp_Orm_Exception
     */
    public function sum($column)
    {
        if($this->_table !== null)
        {
            $return = $this->execute(Xapp_Orm_Query::create(self::connection())->sum($this->_table, $column), null, self::FETCH_ONE);
            $this->_table = null;
            return $return;
        }else{
            throw new Xapp_Orm_Exception(_("table must be set prior to using this function"), 1292501);
        }
    }


    /**
     * get the total count or size of all entries in a table by calling this function like: $connection->table('foo')->size();
     *
     * @error 12926
     * @return int|mixed
     * @throws Xapp_Orm_Exception
     */
    public function size()
    {
        if($this->_table !== null)
        {
            $return = $this->execute(Xapp_Orm_Query::create(self::connection())->size($this->_table), null, self::FETCH_ONE);
            $this->_table = null;
            return $return;
        }else{
            throw new Xapp_Orm_Exception(_("table must be set prior to using this function"), 1292601);
        }
    }


    /**
     * truncate a table by calling: $connection->table('foo')->truncate();
     *
     * @error 12927
     * @return int|mixed
     * @throws Xapp_Orm_Exception
     */
    public function truncate()
    {
        if($this->_table !== null)
        {
            $return = $this->execute(Xapp_Orm_Query::create(self::connection())->truncate($this->_table));
            $this->_table = null;
            return $return;
        }else{
            throw new Xapp_Orm_Exception(_("table must be set prior to using this function"), 1292701);
        }
    }


    /**
     * drop a table by calling: $connection->table('foo')->drop();
     *
     * @error 12928
     * @return int|mixed
     * @throws Xapp_Orm_Exception
     */
    public function drop()
    {
        if($this->_table !== null)
        {
            $return = $this->execute(Xapp_Orm_Query::create(self::connection())->drop($this->_table));
            $this->_table = null;
            return $return;
        }else{
            throw new Xapp_Orm_Exception(_("table must be set prior to using this function"), 1292801);
        }
    }


    /**
     * get table status from table like: $connection->table('foo')->show();
     *
     * @error 12929
     * @return array|mixed
     * @throws Xapp_Orm_Exception
     */
    public function show()
    {
        if($this->_table !== null)
        {
            $return = $this->execute(Xapp_Orm_Query::create(self::connection())->show($this->_table));
            $this->_table = null;
            return $return;
        }else{
            throw new Xapp_Orm_Exception(_("table must be set prior to using this function"), 1292901);
        }
    }


    /**
     * typify value for pdo binding of parameter. all values besides boolean, null, int and 'NULL' values are typified to
     * correct pdo data type - the rest is typified with default string data type.
     *
     * @error 12929
     * @param mixed $value expects value to return pdo data type value for
     * @return int
     */
    protected static function type($value)
    {
        if(is_bool($value))
        {
            return PDO::PARAM_INT;
        }else if(is_null($value)){
            return PDO::PARAM_NULL;
        }else if(is_int($value) && $value <= 2147483647){
            return PDO::PARAM_INT;
        }else if($value === 'NULL'){
            return PDO::PARAM_NULL;
        }else if($value === 'null'){
            return PDO::PARAM_NULL;
        }else{
            return PDO::PARAM_STR;
        }
    }


    /**
     * emulates native mysqlnd auto typify turning integers and floats returned as strings to real integer and floats.
     * emulation will only be used if selected driver is not mysqlnd and EMULATE_NATIVE_TYPES option is set to true
     *
     * @error 12941
     * @param mixed $data expects array or object from pdo result to typify
     * @return array|object
     */
    protected function typify($data)
    {
        if(!function_exists('_typify'))
        {
            function _typify(&$d)
            {
                if(is_numeric($d) && (int)$d <= PHP_INT_MAX)
                {
                    if((int)$d != $d){
                        $d = (float)$d;
                    }else{
                        $d = (int)$d;
                    }
                }
            }
        }

        $_data = $data;
        if(is_array($_data))
        {
            if(array_key_exists(0, $data) && (is_array($data[0]) || is_object($data[0])))
            {
            }else{
                $_data = array($_data);
            }
            foreach($_data as &$val)
            {
                foreach($val as &$v)
                {
                    _typify($v);
                }
            }
            if(array_key_exists(0, $data) && (is_array($data[0]) || is_object($data[0])))
            {
                return $_data;
            }else{
                return $_data[0];
            }
        }else if(is_object($data)){
            foreach($data as &$d)
            {
                _typify($d);
            }
            return $data;
        }else{
            return $data;
        }
    }


    /**
     * tidy up sql string
     *
     * @error 12930
     * @param string $sql expects the sql string to tidy up
     * @return string
     */
    protected function tidy($sql)
    {
        $sql = trim($sql);
        $sql = preg_replace('/[[:blank:]]+/', ' ', $sql);
        return $sql;
    }


    /**
     * parse sql queries with php natives sprintf syntax and wrap all values existent in second parameter, e.g. the sql
     * string 'select * from foo where name = %s" will return a quoted string value for first placeholder in second parameter
     *
     * @error 12931
     * @param string $sql expects sql string to parse
     * @param array $params expects array of parameters to be replaced in sql string
     * @return string
     */
    protected function wrap($sql, Array $params = null)
    {
        foreach($params as $k => &$v)
        {
            if(!is_int($v))
            {
                $v = self::quote($v, self::type($v));
            }
        }
        return vsprintf($sql, $params);
    }


    /**
     * re-masks a sql pdo statement with ? or %s or :name placeholder to full valid sql query filling in the parameters
     * supplied in second argument
     *
     * @error 12944
     * @param string $sql expects the sql statement
     * @param array $params expects the parameters that will be replaced in sql statement
     * @return string
     */
    public static function remask($sql, Array $params = array())
    {
        foreach($params as $k => $v)
        {
            if(is_int($k))
            {
                $sql = preg_replace("/\?|\%\b(s|d)/i", Xapp_Orm::quote($v), (string)$sql, 1);
            }else{
                $sql = preg_replace("/\:\b$k/i", Xapp_Orm::quote($v), (string)$sql, 1);
            }
        }
        return $sql;
    }


    /**
     * quotes values using pdo quote function with data type passed in second parameter
     *
     * @error 12932
     * @param mixed $value expects value to quote
     * @param int $type expects the pdo param type hint value
     * @return mixed
     */
    public static function quote($value, $type = PDO::PARAM_STR)
    {
        if(is_null($value))
        {
            return "NULL";
        }
        return self::connection()->pdo->quote($value, (int)$type);
    }


    /**
     * auto quotes values determining pdo data type by Xapp_Orm::type function and passing the PDO int type value to PDO
     * quote function
     *
     * @error 12945
     * @param mixed $value expects value to quote
     * @return mixed
     */
    public static function autoQuote($value)
    {
        return self::quote($value, self::type($value));
    }


    /**
     * internal log function will log all sql queries into internal query log pool.
     *
     * @error 12933
     * @param string $driver expects the current driver name string
     * @param string $database expects the current database name string
     * @param string $mode expects the current sql query mode (e.g. select, update, ...)
     * @param string $sql expects the sql string
     * @param null|array $params expects the optional parameters belonging to query
     * @param float $start expects the start time in microseconds
     * @return void
     */
    protected function log($driver, $database, $mode, $sql, $params = null, $start)
    {
        $log = array
        (
            strftime(xapp_conf(XAPP_CONF_DATETIME_FORMAT), $start),
            number_format((microtime(true) - $start) * 1000, 2),
            $driver,
            $database,
            $mode,
            $sql,
            $params
        );
        self::$_log[] = $log;
        if(xapp_get_option(self::DEBUG, $this))
        {
            xapp_debug($log, 'orm');
        }
        xapp_event('xapp.orm.query', array($sql, $params, microtime(true) - $start));
        $log = null;
    }


    /**
     * returns last executed query from query pool if existent
     *
     * @error 12934
     * @return null|string
     */
    public static function lastQuery()
    {
        return (!empty(self::$_queries)) ? self::$_queries[sizeof(self::$_queries) - 1] : null;
    }


    /**
     * return the query pool of all executed queries
     *
     * @error 12935
     * @return array
     */
    public static function getQueries()
    {
        return self::$_queries;
    }


    /**
     * print complete query pool to screen
     *
     * @error 12936
     * @return void
     */
    public static function printQueries()
    {
        foreach(self::$_queries as $k => $v)
        {
            echo "<pre>$v</pre>\n";
        }
    }


    /**
     * returns the number of already executed queries in pool
     *
     * @error 12937
     * @return int
     */
    public static function countQueries()
    {
        return sizeof(self::$_queries);
    }


    /**
     * return the statement pool of all pdo statements for
     * debug purpose
     *
     * @error 12938
     * @return array
     */
    public static function getStatements()
    {
        return self::$_statements;
    }


    /**
     * print complete statement debug pool to screen
     *
     * @error 12939
     * @return void
     */
    public static function printStatements()
    {
        foreach(self::$_statements as $k => $v)
        {
            echo "<pre>$v</pre>\n";
        }
    }


    /**
     * auto detect for sql raw string syntax detecting the sql command/action to determine which pdo functions to use for
     * queries. will throw exception if option ALLOW_ONLY_CRUD is set to true and user tries to execute query that is not
     * compatible with crud operations.
     *
     * @error 12940
     * @param string $sql expects the sql string to auto detect
     * @return string
     * @throws Xapp_Orm_Exception
     */
    protected function detect($sql)
    {
        if(preg_match('/^(?:[\s]*)([^\s]+)/i', trim($sql), $m))
        {
            $m[1] = strtolower(trim($m[1]));
            if((bool)xapp_get_option(self::ALLOW_ONLY_CRUD, $this) && !in_array($m[1], array('insert', 'update', 'select', 'delete')))
            {
                throw new Xapp_Orm_Exception(_("only crud operation allowed for this database instance"), 1294001);
            }else{
                return $m[1];
            }
        }else{
            throw new Xapp_Orm_Exception(_("unable to detect database action from sql string"), 1294002);
        }
    }


    /**
     * destroy pdo instance and disconnect from database
     *
     * @error 12942
     * @return void
     */
    public function disconnect()
    {
        $this->pdo = null;
        $this->_connected = false;
    }


    /**
     * class constructor writes away log entries to log file if LOG option is set in class instance option. LOG option
     * value must either by a absolute file pointer or instance of Xapp_Log_Interface
     *
     * @error 12943
     * @return void
     * @throws Xapp_Orm_Exception
     */
    final public function __destruct()
    {
        $tmp1 = array();
        $tmp2 = array();

        $modes = xapp_get_option(self::LOG_MODES, $this);
        if(xapp_has_option(self::LOG, $this))
        {
            foreach(self::$_log as $k => $v)
            {
                if(in_array(strtolower($v[4]), $modes))
                {
                    if(is_array($v[6]))
                    {
                        $v[6] = http_build_query($v[6], '', '&');
                    }
                    $tmp1[] = $v;
                    $tmp2[] = implode(', ', $v);
                }
            }
            $log = xapp_get_option(self::LOG, $this);
            if(is_writable(dirname($log)))
            {
                file_put_contents($log, implode("\n", $tmp2) . "\n", FILE_APPEND);
            }else if($log instanceof Xapp_Log_Interface){
                $log->log($tmp1);
            }else{
                throw new Xapp_Orm_Exception(_("unable to write to log file since log option value is not recognized"), 1294301);
            }
        }
    }
}
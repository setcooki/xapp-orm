<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../core/core.php');

xapp_import('xapp.Orm.Model.Exception');
xapp_import('xapp.Orm');
xapp_import('xapp.Orm.Observer');
xapp_import('xapp.Orm.Observer.Interface');
xapp_import('xapp.Orm.Query');
xapp_import('xapp.Orm.Filter');
xapp_import('xapp.Orm.Entity');

/**
 * Orm model class
 *
 * @package Orm
 * @class Xapp_Orm_Model
 * @error 137
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Orm_Model extends Xapp_Orm_Observer implements Xapp_Orm_Observer_Interface, Xapp_Singleton_Interface
{
    /**
     * contains the primary key column name value which can be
     * overwritten in concrete model implementation
     *
     * @var string
     */
    public static $key = 'id';

    /**
     * contains instance of Xapp_Orm_Query
     *
     * @var null|Xapp_Orm_Query
     */
    protected $_query = null;

    /**
     * contains the connection instance of Xapp_Orm_Driver
     *
     * @var null|Xapp_Orm|Xapp_Orm_Driver
     */
    protected $_connection = null;

    /**
     * contains array of all singleton instances create for
     * models
     *
     * @var array
     */
    protected static $_instances = array();


    /**
     * class constructor sets connection or gets gets connection if not set in
     * first parameter and also will create new query instance according to
     * passed connection instance. if the concrete model contains the $model
     * property defining which model class to use will test if created instance
     * is of that class and otherwise will throw error. this can happen when creating
     * singleton instance of wrong model
     *
     * @error 13701
     * @param null|string|Xapp_Orm_Driver $connection expects optional connection to pass to query instance
     * @throws Xapp_Orm_Model_Exception
     */
    public function __construct($connection = null)
    {
        $class = get_class($this);
        if(xapp_property_exists($class, 'model') && $class !== $class::$model)
        {
            throw new Xapp_Orm_Model_Exception(xapp_sprintf(__("calling model class must be instance of: %s"), $class::$model), 1370101);
        }
        if($connection !== null)
        {
            if(!$connection instanceof Xapp_Orm)
            {
                $connection = Xapp_Orm::connection((string)$connection);
            }
            $this->_query = Xapp_Orm_Query::create($connection);
        }else{
            $connection = Xapp_Orm::connection();
            $this->_query = Xapp_Orm_Query::create();
        }
        $this->_connection = $connection;
        $this->init();
    }


    /**
     * preferred method to create model instance since model is bound to a table
     * therefore to a database and connection unless you have the same model used
     * in different databases use this method to create instance. you need to use correct
     * model static class name as callee otherwise you will produce errors in your script
     * when your extend your model but use the model base class as callee. e.g. if your model
     * implementation class is called myModel use myModel::instance() to create the correct
     * instance. will throw exception if you try to create a wrong model
     *
     * @error 13702
     * @param null|string|Xapp_Orm_Driver $connection expects optional connection to pass to query instance
     * @return Xapp_Orm_Model
     * @throws Xapp_Orm_Model_Exception
     */
    public static function instance($connection = null)
    {
        $class = get_called_class();
        if($class !== __CLASS__)
        {
            if(!array_key_exists($class, self::$_instances))
            {
                self::$_instances[$class] = new $class($connection);
            }
            return self::$_instances[$class];
        }else{
            throw new Xapp_Orm_Model_Exception(__("model can not be instantiated by abstract model base class"), 1370201);
        }
    }


    /**
     * init model be attaching events to observer to listen to model actions
     * to trigger callbacks if set in concrete model implementation
     *
     * @error 13703
     * @return void
     */
    protected function init()
    {
        $this->attach('beforeUpdate', $this);
        $this->attach('afterUpdate', $this);
        $this->attach('beforeInsert', $this);
        $this->attach('afterInsert', $this);
        $this->attach('beforeDelete', $this);
        $this->attach('afterDelete', $this);
        $this->attach('afterGet', $this);
    }


    /**
     * observer event trigger listener will be called once event is triggered calling
     * all registered observers in this case events that will be called after/before
     * update/insert/delete/... actions passing entity to model to be modified if
     * necessary. the concrete model must implement the event names as functions
     * acception the entity in first argument of function
     *
     * @error 13704
     * @param string $event expects the event name that is triggered
     * @param null|mixed $params expects optional parameters passed when triggered
     * @return void
     */
    public function notify($event, $params = null)
    {
        if(method_exists($this, $event))
        {
            $this->$event($params);
        }
    }


    /**
     * get entity be id or create new empty entity if no id has been passed. the
     * id will be used to query against primary key column. after populating new
     * entity with values from database "afterGet" event is triggered
     *
     * @error 13705
     * @param null|int $id expects the primary key value to get row from table
     * @return Xapp_Orm_Entity
     */
    public function get($id = null)
    {
        $entity = $this->getEntity();

        if($id === null)
        {
            $entity     = new $entity();
        }else{
            $filter     = Xapp_Orm_Filter::select($this->getTable())
                        ->where($this->getKey(), $id)
                        ->limit(1);
            $query      = $this->getQuery()->select($filter);
            $entity     = $this->getConnection()->query($query, $filter->getValues(), Xapp_Orm::FETCH_OBJECT, $entity);
        }

        $this->trigger('afterGet', $entity);

        $filter = null;
        $query = null;

        return $entity;
    }


    /**
     * checks if entity already exist in database or not by receiving entity instance of model key
     * defined in model of entity. returns boolean true if exists else false
     *
     * @error 13727
     * @param Xapp_Orm_Entity|int $mixed expects either entity or entity key value
     * @return boolean
     */
    public function has($mixed)
    {

        if($mixed instanceof Xapp_Orm_Entity)
        {
            $id = $mixed->{$this->getKey()};
        }else{
            $id = $mixed;
        }
        if(!is_null($id))
        {
            $filter     = Xapp_Orm_Filter::select($this->getTable())
                        ->where($this->getKey(), $id)
                        ->limit(1);
            $query      = $this->getQuery()->select($filter);
            $result     = $this->getConnection()->query($query, $filter->getValues(), Xapp_Orm::FETCH_COUNT);

            $filter     = null;
            $query      = null;

            return (bool)$result;
        }else{
            return false;
        }
    }


    /**
     * query table to return entity by either passing raw queries in first parameter
     * or filter instance. if using custom raw where queries see Xapp_Orm_Filter::whereRaw for
     * more info how to define custom where conditions. pass the values for the custom
     * where condition in second parameter.
     *
     * @error 13706
     * @param string|array|Xapp_Orm_Filter $filter expects either filter instance or custom where queries
     * @param null|string|array $values expects optional values for custom where queries
     * @return Xapp_Orm_Entity
     */
    public function find($filter, $values = null)
    {
        $entity = $this->getEntity();

        if(!($filter instanceof Xapp_Orm_Filter))
        {
            $filter = Xapp_Orm_Filter::select($this->getTable())->whereRaw($filter, $values)->limit(1);
        }else{
            $filter->limit(1);
        }
        $query      = $this->getQuery()->select($filter);
        $entity     = $this->getConnection()->query($query, $filter->getValues(), Xapp_Orm::FETCH_OBJECT, $entity);

        $filter     = null;
        $query      = null;

        return $entity;
    }


    /**
     * query table to get entities according to parameters passed. parameter filter is empty
     * returns all entities from table. if first parameter is instance of Xapp_Orm_Filter
     * will build query from filter to get entities for. if first parameter is not a filter
     * instance must be valid raw custom where conditions with all required values passed
     * in second parameter. NOTE: calling this function like all() will mean eventually to query
     * the database for thousand of entities! the function will return array of contain entities
     * as object instances
     *
     * @error 13707
     * @param null|string|array|Xapp_Orm_Filter $filter expects values as explained above
     * @param null|string|array $values expects optional values for custom where queries
     * @return mixed
     */
    public function all($filter = null, $values = null)
    {
        $entity = $this->getEntity();

        if($filter !== null)
        {
            if(!($filter instanceof Xapp_Orm_Filter))
            {
                $filter = Xapp_Orm_Filter::select($this->getTable())->whereRaw($filter, $values);
            }
        }else{
            $filter = Xapp_Orm_Filter::select($this->getTable());
        }

        $query      = $this->getQuery()->select($filter);
        $entity     = $this->getConnection()->query($query, $filter->getValues(), Xapp_Orm::FETCH_OBJECT, $entity);

        $filter     = null;
        $query      = null;

        return $entity;
    }


    /**
     * truncate the table belonging to this model. NOTE: use this function carefully as it
     * will delete all data in table. returns 1 on success
     *
     * @error 13708
     * @return int|mixed
     */
    public function truncate()
    {
        return $this->getConnection()->exec($this->getQuery()->truncate($this->getTable()));
    }


    /**
     * create a new entity from array, std obj, other instance of same entity or null to create
     * empty new entity.
     *
     * @error 13709
     * @param null|object|array|Xapp_Orm_Entity $mixed expects any of the values explained above
     * @return Xapp_Orm_Entity
     */
    public function create($mixed = null)
    {
        $entity = $this->getEntity();

        if($mixed !== null)
        {
            return $this->populate(new $entity(), $mixed, true);
        }else{
            return new $entity();
        }
    }


    /**
     * populate entity by other entities values, array or object containing key => value pairs.
     * if you pass entity instance to copy values from entity must be of same entity class as
     * entity that get populated. if you use array or php objects to populate entity make sure
     * that all keys are entity properties unless you want to overload you entity instance
     * with unused properties not know in entity table. to make sure only properties are set
     * which do exist in entity pass third parameter as boolean true
     *
     * @error 13710
     * @param Xapp_Orm_Entity $entity
     * @param array|object|Xapp_Orm_Entity $mixed expects on of the above options
     * @param boolean $safe expects boolean value to copy values safe
     * @return Xapp_Orm_Entity
     * @throws Xapp_Orm_Model_Exception
     */
    public function populate(Xapp_Orm_Entity $entity, $mixed, $safe = true)
    {
        if($mixed instanceof Xapp_Orm_Entity)
        {
            if(get_class($mixed) === get_class($this))
            {
                $mixed = $mixed->toArray();
            }else{
                throw new Xapp_Orm_Model_Exception(xapp_sprintf(__("can not populate values from: %s since entity must be of same class as entity to populate"), get_class($mixed)), 1371001);
            }
        }
        foreach((object)$mixed as $k => $v)
        {
            if((bool)$safe)
            {
                if($entity->has($k))
                {
                    $entity->set($k, $v);
                }
            }else{
                $entity->set($k, $v);
            }
        }
        return $entity;
    }


    /**
     * save an entity to database. this will check for primary key value existent in
     * entity and be that distinguish if entity needs to be updated or new entity
     * must be inserted into table. returns the result of insert/update operation
     *
     * @error 13711
     * @param Xapp_Orm_Entity $entity
     * @return mixed
     */
    public function save(Xapp_Orm_Entity $entity)
    {
        if($this->has($entity))
        {
            return $this->update($entity);
        }else{
            return $this->insert($entity);
        }
    }


    /**
     * insert new entity in table by checking if passed entity belongs to used
     * model and if entity does not already exist in database. triggers
     * before/after events for model to listen to. will return 1 on success throw
     * exception or return false on failure
     *
     * @error 13712
     * @param Xapp_Orm_Entity $entity expects the entity to save to table
     * @return mixed
     * @throws Xapp_Orm_Model_Exception
     */
    protected function insert(Xapp_Orm_Entity $entity)
    {
        $fields = array();
        $this->canModel($entity);

        $this->trigger('beforeInsert', $entity);

        if(!$this->has($entity))
        {
            foreach((array)$entity as $k => $v)
            {
                if(!is_null($v))
                {
                    $fields[$k] = $v;
                }
            }

            $filter     = Xapp_Orm_Filter::insert($this->getTable())->set($fields);
            $query      = $this->getQuery()->insert($filter);
            $return     = $this->getConnection()->query($query, $filter->getBindings());

            $entity->{$this->getKey()} = $this->getConnection()->lastId();

            $this->trigger('afterInsert', $entity);

            $filter     = null;
            $query      = null;

            return $return;
        }else{
            throw new Xapp_Orm_Model_Exception(__("insert is not allowed since entity does already exist"), 1371201);
        }
    }


    /**
     * update entity be updating table record set checking first if model can store entity or
     * model belongs to entity. if entity does not have a value set for primary key will throw
     * exception. triggers before/after update events. returns 1 on success throw
     * exception or return false on failure
     *
     * @error 13713
     * @param Xapp_Orm_Entity $entity expects the entity to update record set for
     * @return mixed
     * @throws Xapp_Orm_Model_Exception
     */
    protected function update(Xapp_Orm_Entity $entity)
    {
        $fields = array();
        $this->canModel($entity);

        $this->trigger('beforeUpdate', $entity);

        if($this->has($entity))
        {
            foreach((array)$entity as $k => $v)
            {
                if(!is_null($v) && $k !== $this->getKey())
                {
                    $fields[$k] = $v;
                }
            }
            $filter     = Xapp_Orm_Filter::update($this->getTable())->set($fields)->key($this->getKey(), $entity->{$this->getKey()});
            $query      = $this->getQuery()->update($filter);
            $return     = $this->getConnection()->query($query, $filter->getBindings());

            $filter     = null;
            $query      = null;

            $this->trigger('afterUpdate', $entity);

            return $return;
        }else{
            throw new Xapp_Orm_Model_Exception(__("update is not allowed since entity does not exist"), 1371301);
        }
    }


    /**
     * delete entity by passing its instance single value or array of values for primary key.
     * if entity is passed in first parameter checks if entity can be deleted by model or not.
     * will trigger before/after events. will throw also exception if passed value is not numeric
     * or will cast to int = 0. will returns 1 on success throws exception or return
     * false on failure
     *
     * @error 13714
     * @param int|array|Xapp_Orm_Entity $mixed expects primary key value(s) or entity instance
     * @return mixed
     * @throws Xapp_Orm_Model_Exception
     */
    public function delete($mixed)
    {
        $this->trigger('beforeDelete', $mixed);

        if(!is_array($mixed))
        {
            if($mixed instanceof Xapp_Orm_Entity){
                $this->canModel($mixed);
                $id = $mixed->{$this->getKey()};
            }else if(is_numeric($mixed)){
                $id = (int)$mixed;
                if($id === 0)
                {
                    throw new Xapp_Orm_Model_Exception(__("primary key value 0 is not valid for delete operation"), 1371401);
                }
            }else{
                throw new Xapp_Orm_Model_Exception(__("primary key value must be a numeric value - delete action aborted"), 1371402);
            }
            $filter = Xapp_Orm_Filter::delete($this->getTable())->key($this->getKey() ,$id)->limit(1);
        }else{
            $filter = Xapp_Orm_Filter::delete($this->getTable())->key($this->getKey() ,$mixed);
        }

        $params = ($filter->hasBindings()) ? $filter->getBindings() : $filter->getValues();
        $return = $this->getConnection()->query($this->getQuery()->delete($filter), $params);

        $this->trigger('afterDelete', $mixed);

        if($mixed instanceof Xapp_Orm_Entity)
        {
            $mixed = null;
            unset($mixed);
        }
        return $return;
    }


    /**
     * checks whether the base model has a property called model to define if the user has specified
     * a custom model
     *
     * @error 13715
     * @return bool
     */
    protected function hasModel()
    {
        return (xapp_property_exists($this, 'model')) ? true : false;
    }


    /**
     * get class name of this model
     *
     * @error 13716
     * @return string
     */
    protected function getModel()
    {
        return get_class($this);
    }


    /**
     * check if an entity used can be used be this model throwing
     * an exception if it can not be used
     *
     * @error 13717
     * @param Xapp_Orm_Entity $entity expects the entity to check
     * @throws Xapp_Orm_Model_Exception
     */
    protected function canModel(Xapp_Orm_Entity $entity)
    {
        if($this->hasEntity())
        {
            $e = $this->getEntity();
            if(!($entity instanceof $e))
            {
                throw new Xapp_Orm_Model_Exception(__("the passed entity does not belong to this model"), 1371701);
            }
        }
    }


    /**
     * checks whether the base model has a property called entity to define which entity class
     * to use for this model
     *
     * @error 13718
     * @return bool
     */
    protected function hasEntity()
    {
        return (xapp_property_exists($this, 'entity')) ? true : false;
    }


    /**
     * get entity class name for model returning default entity which is Xapp_Orm_Entity
     * or entity class defined in model property entity
     *
     * @error 13719
     * @return string
     */
    protected function getEntity()
    {
        $model = $this->getModel();
        if($this->hasEntity())
        {
            return $model::$entity;
        }else{
            return 'Xapp_Orm_Entity';
        }
    }


    /**
     * checks whether the base model has a property called table to define which database
     * table belongs to this model
     *
     * @error 13720
     * @return bool
     */
    protected function hasTable()
    {
        return (xapp_property_exists($this, 'table')) ? true : false;
    }


    /**
     * get table name from model either by getting the table from property table if set
     * or deduct table name from model class name, e.g. BookModel = book
     *
     * @error 13721
     * @return string
     */
    protected function getTable()
    {
        $model = $this->getModel();
        if($this->hasTable())
        {
            return $model::$table;
        }else{
            return str_ireplace(array('_', 'model', 'base'), '', strtolower(get_class($this)));
        }
    }


    /**
     * get the primary key name from model which defaults to id
     *
     * @error 13722
     * @return string
     */
    protected function getKey()
    {
        $model = $this->getModel();
        return $model::$key;
    }


    /**
     * get the query instance
     *
     * @error 13723
     * @return null|Xapp_Orm_Query
     */
    protected function getQuery()
    {
        return $this->_query;
    }


    /**
     * get the connection instance
     *
     * @error 13724
     * @return null|Xapp_Orm|Xapp_Orm_Driver
     */
    protected function getConnection()
    {
        return $this->_connection;
    }


    /**
     * return object hash when class conversion to string
     *
     * @error 13725
     * @return string
     */
    final public function __toString()
    {
        return spl_object_hash($this);
    }


    /**
     * use static overloading to dynamically call non static functions
     *
     * @error 13726
     * @param string $method expects the method name
     * @param array $arguments expects optional arguments
     * @return mixed
     */
    public static function __callStatic($method, Array $arguments)
   	{
   		$model = get_called_class();
   		return call_user_func_array(array(new $model(), $method), $arguments);
   	}
}
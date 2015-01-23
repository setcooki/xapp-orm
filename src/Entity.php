<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../core/core.php');

/**
 * Orm entity class
 *
 * @package Orm
 * @class Xapp_Orm_Entity
 * @error 138
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Orm_Entity
{
    /**
     * class constructor can recieve array/object of key => value pairs
     * to be set as class properties. checks if a method with the same
     * name as the key exist in concrete entity and will use that method
     * as setter/getter method
     *
     * @error 13801
     * @param null|array|object $mixed expects optional key => value pairs to populate instance with
     */
    public function __construct($mixed = null)
    {
        if($mixed !== null)
        {
            foreach((object)$mixed as $k => $v)
            {
                if(method_exists($this, $k))
                {
                    $this->$k($v);
                }else{
                    $this->$k = $v;
                }
            }
        }
    }


    /**
     * set value to key/property. value can be also set to null in that
     * case the key/property value wont be stored in database since null
     * values are skipped in process. will look also for function of the
     * same as first parameter key in concrete entity to set value
     *
     * @error 13802
     * @param string $name expects the property name = column name of table
     * @param null|mixed $value expects value to set
     * @return Xapp_Orm_Entity
     */
    public function set($name, $value = null)
    {
        if(method_exists($this, $name))
        {
            $this->$name($value);
        }else{
            $this->$name = $value;
        }
        return $this;
    }


    /**
     * try to get key/propertyfrom entity by passing name in first parameter.
     * will look for getter method implemented in concrete entity, e.g. when
     * trying to get $entity->foo will look for foo() function in class
     *
     * @error 13803
     * @param string $name expects the property name = column name of table
     * @return mixed
     */
    public function get($name)
    {
        if(method_exists($this, $name))
        {
            return $this->$name();
        }else{
            return $this->$name;
        }
    }


    /**
     * checks if key/property exists in entity by testing strict or not =
     * must have value other then null
     *
     * @error 13804
     * @param string $name expects the property name = column name of table
     * @param bool $strict expects boolean value whether to check strict or not
     * @return bool
     */
    public function has($name, $strict = false)
    {
        if(xapp_property_exists($this, $name))
        {
            return (((bool)$strict) ? ($name !== null) : true);
        }else{
            return false;
        }
    }


    /**
     * tries to save entity by determine model for entity either id $model public static property has been
     * defined in entity class or by looking for model by entity class name. will throw exception if
     * model was not found
     *
     * @error 13810
     * @return mixed
     * @throws Xapp_Orm_Entity_Exception
     */
    public function save()
    {
        $model = null;
        if(xapp_property_exists(get_class($this), 'model'))
        {
            $model = xapp_property_get($this, 'model');
        }else{
            $entity = ucfirst(str_ireplace(array('_', 'entity', 'base'), '', strtolower(get_class($this))));
            if(class_exists($entity . 'Model', true))
            {
                $model = $entity . 'Model';
            }
        }
        if($model !== null)
        {
            return call_user_func_array(array($model::instance(), 'save'), array($this));
        }else{
            throw new Xapp_Orm_Entity_Exception(_("model for entity was not found"), 1381001);
        }
    }


    /**
     * overloading not existent properties is only allowed for entities instantiated
     * by this class because entity base class is designed for dynamic entities
     *
     * @error 13805
     * @param string $name property name to overload
     * @param null|mixed $value expects value to set
     * @return void
     * @throws Xapp_Orm_Entity_Exception
     */
    public function __set($name, $value = null)
    {
        $vars = get_class_vars(get_class($this));
        if(empty($vars))
        {
            $this->$name = $value;
        }else{
            throw new Xapp_Orm_Entity_Exception(xapp_sprintf(_("overloading entity with new properties is only allowed for dynamic instance of: %s"), __CLASS__), 1380501);
        }
    }


    /**
     * magic method __call is only allowed to set/get properties of the entity. will issue a
     * warning when trying to get key/property that does not exist. setting properties
     * via __call is only allowed on entities instantiated by entity dynamic base class
     *
     * @error 13806
     * @param string $name expects the property name which is the function name like $entity->foo()
     * @param array $arguments expects optional set values
     * @return mixed
     */
    public function __call($name, Array $arguments)
    {
        if(!empty($arguments))
        {
            $this->__set($name, $arguments[0]);
        }else{
            return $this->$name;
        }
    }


    /**
     * convert entity to array
     *
     * @error 13807
     * @return array
     */
    final public function toArray()
    {
        $array = array();
        foreach(get_object_vars($this) as $k => $v)
        {
            $array[$k] = $this->$k;
        }
        return $array;
    }


    /**
     * convert entity to std class object
     *
     * @error 13808
     * @return stdClass
     */
    final public function toObject()
    {
        $object = new stdClass();
        foreach(get_object_vars($this) as $k => $v)
        {
            $object->$k = $this->$k;
        }
        return $object;
    }


    /**
     * convert entity to json
     *
     * @error 13809
     * @param int $options expects optional json options
     * @return string
     */
    final public function toJson($options = 0)
    {
        return json_encode($this->toArray(), (int)$options);
    }
}
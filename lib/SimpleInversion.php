<?php
/**
 * @author      Jarret Byrne
 * @copyright   2015 Jarret Byrne
 * @link        http://www.github.com/stratedge/simple-inversion
 * @version     0.0.1
 * @package     SimpleInversion
 */
namespace Stratedge\SimpleInversion;

use ReflectionClass;

/**
 * SimpleInversion
 * @package SimpleInversion
 * @author  Jarret Byrne
 * @since   0.0.1
 */
class SimpleInversion {


    /**
     * Property that will contain objects registered to class paths
     * @var array[object]
     */
    protected $registered = [];


    /**
     * Empties the array of registered objects.
     */
    public function clear()
    {
        $this->registered = [];
    }


    /**
     * Given a fully-qualified class name, returns a new copy of the requested object. For any
     * parameter of the class' constructor that is type-hinted, a new copy of the type-hinted class
     * will be passed to the constructor if the parameter's position is not set in the list of
     * parameters passed to the this method.
     * 
     * @param   string  $class      The fully-qualified class name of the class to load
     * @param   array   $parameters An array of parameters to be passed to the constructor of the
     *                              class to to be loaded
     * @return  object
     */
    public function get($class, $parameters = [])
    {
        if(isset($this->registered[$class])) return $this->registered[$class];

        $reflection = new ReflectionClass($class);

        //If the requested class has a constructor...
        if($reflection->hasMethod('__construct')) {

            $params = $reflection->getConstructor()->getParameters();

            foreach($params as $key => $param)
            {
                //If the parameter is not defined...
                if(isset($parameters[$key]) === false) {

                    //If the constructor's parameter list extends this far...
                    if(isset($params[$key])) {

                        //If the parameter has a class as a typehint...
                        if(is_null($params[$key]->getClass()) === false) {

                            //Then call self::get() with the dependent class
                            $parameters[$key] = $this->get($params[$key]->getClass()->getName());
                        }

                    }

                }
            }

        }

        return $reflection->newInstanceArgs($parameters);
    }


    /**
     * Set the given object to be returned when the given fully-qualified class name is invoked
     * through the get method.
     *
     * @see     \Stratedge\SimpleInversion\SimpleInversion::get()
     * @param   string  $class  The fully-qualified class name of the object to replace
     * @param   object  $obj    The object that will be returned with the get method is invoked for
     *                          $class
     */
    public function register($class, $object)
    {
        $this->registered[$class] = $object;
    }


    /**
     * Removes the registration entry for the given fully-qualified class name
     * @param   string  $class  The fully-qualified class name that should be unregistered
     */
    public function unregister($class)
    {
        unset($this->registered[$class]);
    }

}
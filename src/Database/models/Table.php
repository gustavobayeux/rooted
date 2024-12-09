<?php
namespace Rooted\Database;
use Rooted\Environment\Application;

#[\AllowDynamicProperties]
class Table
{


    public string $table_name;
    public $mapper;


    /* DYNAMIC METHODS */
    private array $loadedFunctions = [];

    /**
     *  Table constructor
     *  @param string $table_name | Name of your table, example: "table1" 
     *  @param array $table_columns | Table matrix (Example: ["col1","col2","colN"...]] 
     *  @param object $mapper | THE MAPPER OBJECT INSTANCE 
     *
     **/
    function __construct(string $table_name, array $table_columns, object $mapper)
    {
        $this->table_name = $table_name;
        $this->table_columns = $table_columns;
        $this->mapper = $mapper;
    }




    /**
     * STARTS INSERT OPERATION
     * 
     *  @param array $values | FORMAT: [ "column1","column2"...]
     * 
     *  @return True on success, False otherwise.
     *
     **/
    function insert(array $values)
    {
        $this->auth();

        
        return new QueryBuilder("INSERT",$values,null,$this);
    }



    /**
     * STARTS SELECT OPERATION
     * 
     *  optional @param string|array Column or Columns, leave empty for ALL (example 1: "name"; example 2:"['name','surname','email']")
     *  @return True on success, False otherwise.
     *
     **/
    function select(string|array $columns = null)
    {
        $this->auth();

        return new QueryBuilder("SELECT",null, $columns,$this);

    }

    /**
     * STARTS AN UPDATE SQL OPERATION
     *  @return True on success, False otherwise.
     *
     **/
    function update()
    {
        $this->auth();
        
        return new QueryBuilder("UPDATE",null, null,$this);
    }
    /**
     * STARTS A DELETE SQL OPERATION
     *  @param string $where | SQL "WHERE" condition. (example: id = ?)
     *  @param array $where_value | Example: [1]
     * 
     *  @return True on success, False otherwise.
     *
     **/
    public function delete()
    {
        $this->auth();
      
        return new QueryBuilder("DELETE",null, null,$this);
    }




    /**
     * CHECK IF FILE CALLING A CRUD FUNCTION WAS INCLUDED IN THE ORM
     * 
     * 
     *  
     *  @return True on success, False otherwise.
     *
     **/
    private function auth()
    {

        /*
        Debug backtrace will return an Array from 0 [this file]
        To the file that called it [For example, functions.php]
        */

        if(empty($this -> mapper -> include_paths)){
            echo "The file using the ORM is not included.";
            exit(500);
            return false;
        }

        echo var_dump($this->mapper->include_paths);

        if (!in_array(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1]["file"], $this->mapper->include_paths)) {

            echo "Access Denied.";
            exit(500);
            return false;
        }

        return true;
    }



    /**
     * LOAD A USER-DEFINED FUNCTIONS
     * 
     * Loads a set of custom functions by setting a method that calls your custom function
     * 
     *  @param array $functions_names; //Array of the names of the functions being loaded (ex: ['func1','func2])
     * 
     *  
     *  @return True on success, False otherwise.
     *
     **/
    public function load(array $functions_names)
    {
        

        foreach ($functions_names as $function_name) {


            if (!in_array(strtolower($function_name), $this->mapper->included_functions)) {
                
                Application::register_error(new \Error("Could not load the functions"));
                return false;
            }

            array_push($this->loadedFunctions, $function_name);

            // Create a dynamic property, that will have your function inside

            $this->createMethod($function_name);
        }

        return true;
    }

    private function createMethod($function_name)
    {
        // Defines a dynamic method with the name of your function, (e. g. $this -> your_function(...$args))
        $this->{$function_name} = function (...$args) use ($function_name) {

            $table_name = ucfirst($this->table_name);

            //Passes this instance as a global to be used in your function
            global ${$table_name};
            ${$table_name} = $this;

            // Call the function your included (we pass the $args from the closure to the $args of your function)
            return $function_name(...$args);
        };
    }


    //Turn a DYNAMIC PROPERTY INTO A DYNAMIC METHOD
    public function __call($method, $args)
    {
        // If the method exists (if it was dynamically defined), invoke it
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
    }
}
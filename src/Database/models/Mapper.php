<?php

namespace Rooted\Database;

use Rooted\Environment\Application;

#[\AllowDynamicProperties]
class Mapper extends Connection
{
    //MAPPING
    private $tables; //Table matrix (Example: [table=>[column1,column2,column3]] 
    protected $database_name;
    public array $include_paths;
    public array $included_functions;
   

    /**
     * Database connection Constructor
     * 
     * @param string $dsn                | DATA SOURCE NAME (Acording to PDO definitions)
     * @param string $username           | DATABASE USERNAME
     * @param string $password           | DATABASE PASSWORD
     * @param string $database_name      | DATABASE NAME
     * @param string $included_paths     | INCLUDED PATHS
     * @param string $included_functions | INCLUDED FUNCTIONS
     *  
     * @return True on success, False otherwise.
     **/
    function __construct(
        string $dsn,
        string $username,
        string $password,
        string $database_name,
        array $include_paths,
        array $included_functions
    ) {

        //Creates a connection
        $this->create_connection(
            $dsn,
            $username,
            $password,
            $database_name
        );

        if($this -> connected == false){
            return false;
        }

        $this->map_tables();

        $this->map_columns();

        //Turn each table into an Object
        $this->table_to_object();

        $this->include_paths = $include_paths;

        $this->included_functions = $included_functions;
    }


    /**
     * Creates a PDO connection 
     * 
     * @param string $dsn       | DATA SOURCE NAME (Acording to PDO definitions)
     * @param string $username  | DATABASE USERNAME
     * @param string $password  | DATABASE PASSWORD
     * @param string $database_name  | DATABASE NAME
     *  
     * @return True on success, False otherwise.
     **/
    private function create_connection($dsn, $username, $password, $database_name)
    {

        try {
            
            parent::__construct($dsn, $username, $password, $database_name);
           

            $this->database_name = $database_name;

        } catch (\Throwable $e) {
            Application::register_error(new \Error("Mapping Connection Error: $e"));
            return false;
        }
        return true;
    }

    /**
     * Find all tables in a given database and push it in the $tables matrix
     * 
     *
     * @return True on success, False otherwise.
     **/
    private function map_tables()
    {
        $tables["table_name"] =
            $this->query("SELECT table_name FROM information_schema.tables
         WHERE table_schema =  '$this->database_name' ;");



        if (!is_array($tables)) {
            Application::register_error(new \Error("Table Mapping error"));
        }

        for ($i = 0; $i <= count($tables["table_name"]) - 1; $i++) { //Insert table name in the $tables Matrix

            $this->tables[$tables["table_name"][$i]["table_name"]] = [];

        }
    }



    /**
     * Find all columns in a given table and push it in the tables matrix
     * 
     *
     * @return True on success, False otherwise.
     **/
    private function map_columns()
    {


        //ITERATES OVER EACH TABLE
        foreach (array_keys($this->tables) as $table) {

            $tableFormatted = "TABLE_NAME = N'" . $table . "' ";

            //GET COLUMNS FOR THE TABLE
            $result = $this->query(
                "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE " . $tableFormatted,
                $this->password
            );


            if ($result == false) {
                Application::register_error(new \Error("Column Mapping error"));
            }


            //ITERATES OVER EACH COLUMN
            for ($i = 0; $i <= count($result) - 1; $i++) {
                array_push($this->tables[$table], $result[$i]["COLUMN_NAME"]); //Insert column name in the $tables Matrix
            }
        }

        return true;
    }

    /**
     * Turn each table to an Object
     * 
     *
     * @return True on success, False otherwise.
     **/
    private function table_to_object()
    {
        try {
            $i = 0;
            foreach (array_keys($this->tables) as $table) {

                //users -> Users
                $tableFormatted = ucfirst(strtolower($table));

                // Create table as an object in a property.  
                $this->$tableFormatted = new Table($table, $this->tables[$table], $this);

                $i++;
                /* Looks like this example: $this -> Users = new Table(...) */
            }
        } catch (\Throwable $e) {
            Application::register_error(new \Error("Mapping Connection Error: $e"));
        }

        return true;
    }
}

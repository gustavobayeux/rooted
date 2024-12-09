<?php

namespace Rooted\Database;

use Rooted\Environment\Application;
use Rooted\Database\Mapper;


class Database
{

    static array $connections = [];


    //USER FUNCTIONS
    static public array $include_paths = [];
    static public array $included_functions = [];

    /**
     * Database's connections constructor
     *
     *@return True on success, False otherwise.
     **/
    static public function start()
    {
        Self::load_connections();
    }

    /**
     * 
     * Returns the PDO instance for a given connection
     * 
     * @param string $connection | The connection you want to open, set in the .env file
     * 
     *@return object | The PDO instance
     **/
    static public function open_connection($connection)
    {

        if (!isset(Self::$connections[$connection]) || count(Self::$connections[$connection]) !== 4) {

            Application::register_error(new \Exception("The connection does not exist."));
            return false;
        }

        try {
            $dsn = Self::$connections[$connection][0];
            $user = Self::$connections[$connection][1];
            $pass = Self::$connections[$connection][2];
            $dbname = Self::$connections[$connection][3];

            // Try to create the PDO connection
            $pdo = new \PDO($dsn, $user, $pass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            // Catch any PDO exception and return null
            Application::register_error(new \Exception("Database Connection Error: " . $e->getMessage()));
            return null;  // Return null for any PDOException
        }

        return new Mapper($dsn, $user, $pass, $dbname, Self::$include_paths, Self::$included_functions);
    }

    static private function load_connections()
    {
        $connections = [];

        $i = 0;

        $dsn_to_assemble = []; //0=CONNECTION NAME, 1=TYPE,2=NAME, 3=HOST, 4=PORT, 5=USER, 6=PASSWORD

        foreach ($_ENV as $env) {

            if (str_contains(array_search($env, $_ENV), "DB")) {

                //Start counting the 7 DSN parts
                if ($i < 6) {

                    array_push($dsn_to_assemble, $env);
                } else {

                    array_push($dsn_to_assemble, $env); //Append last item to this

                    $connection_name = $dsn_to_assemble[0];

                    $dsn =
                        $dsn_to_assemble[1] . ":" .
                        "host=" . $dsn_to_assemble[2] .
                        ";port=" . $dsn_to_assemble[3] .
                        ";dbname=" . $dsn_to_assemble[4];

                    $user = $dsn_to_assemble[5];
                    $pass = $dsn_to_assemble[6];
                    $dbname = $dsn_to_assemble[4];

                    $connections[$connection_name] = [$dsn, $user, $pass, $dbname];

                    //Unset the DSN and the $i so we can go for the next connection
                    $dsn_to_assemble = [];
                    $i = -1;
                }

                $i++;
            }
        }

        if (!empty($connections)) {

            define("CONNECTIONS_LOADED", true);
            Self::$connections = $connections;
            return true;
        }
        return false;
    }


    /**
     * Imports custom functions 
     * ! Use it before opening the connection
     *
     * @param string $path     | Path to your file from the classes root
     *
     * @return True on success, False otherwise.
     **/

    static public function include_functions($path)
    { //By project or by user

        $path = realpath($path);

        $includeArr = [];


        require_once $path;

        for ($i = 0; $i <= count(get_defined_functions()["user"]) - 1; $i++) {

            $fct = new \ReflectionFunction(get_defined_functions()["user"][$i]);

            array_push($includeArr, get_defined_functions()["user"][$i]);
        }



        array_push(Self::$include_paths, $path);
        Self::$included_functions = $includeArr;

        return true;
    }



    /**
     * Imports custom methods, use it in __construct
     * ! Use it before opening the connection
     *
     * @param string $path     | Path to the file containing the class
     * @param object $class_instance | The current instance of this class
     *
     * @return True on success, False otherwise.
     **/
    static public function include_methods(string $path, object $class_instance)
    {
        $includeArr = [];
        $class_name = get_class($class_instance);

        // Get all methods from the class instance
        $methods = get_class_methods($class_instance);

        foreach ($methods as $method) {
            $reflectionMethod = new \ReflectionMethod($class_name, $method);
            // Optionally, filter by visibility or other criteria if needed
            $includeArr[] = $method;
        }

        // Track included methods and paths
        $path = realpath($path);
        array_push(Self::$include_paths, $path);
        Self::$included_functions = $includeArr;

        return true;
    }


    static public function get_allowed_databases()
    {
        //   return Self::$supporte_databases;
    }

    /**
     *Set New database scheme to be used in DSN

     *@param string $scheme: Database type being used. (Types: cubrid, pgsql, oci, sqlite, firebird)
     *@return True on success, false otherwise.
     **/

    static public function set_allowed_databases(string $scheme)
    {

        $schemes = ["cubrid", "pgsql", "oci", "sqlite", "firebird"];

        if (in_array($scheme, $schemes)) {
            // array_push(Self::$supportedDatabases, $scheme);
        } else {
            throw new \Exception("Scheme \"$scheme\" Doesn't exist.");
            exit(422);
        }
    }
}

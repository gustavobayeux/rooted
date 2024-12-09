<?php

namespace Rooted\Database;

use Rooted\Environment\Application;
use PDO;

class Connection
{
    public $PDO; // Public to be accessed from the Table class
    protected $password; // Ensure the script connecting to DB is the same using it
    public $id; // Used for multiple databases
    protected $prohibited_statements = ["DELETE", "DROP", "TRUNCATE"];

    public $connected = false;

    /**
     * Connects to database 
     * @param string $dsn The Data Source Name for connection
     * @param string $user The user for this connection
     * @param string $password The password for this connection
     */
    public function __construct(string $dsn, string $user, string $password)
    {
        $pdo = Self::connect($dsn, $user, $password);
        
        if(Self::verify_connection($pdo)){
            $this->connected=true;
            $this->PDO = $pdo;
        }
    }

    /**
     * Set unallowed SQL statements.
     * @param mixed $statement Array or statement to block.
     * @return bool True on success, false on failure.
     */
    public function set_blacklist($statement = ["DELETE", "DROP", "TRUNCATE"]): bool
    {
        try {
            
            $this->prohibited_statements = $statement;
            return true;
        } catch (\Exception $e) {
            echo $e;
            return false;
        }
    }

    /**
     * Establish a PDO connection.
     * @param string $dsn The Data Source Name.
     * @param string $user The database user.
     * @param string $password The database password.
     * @return PDO The PDO connection object.
     */
    private static function connect(string $dsn, string $user, string $password)
    {
        if (!defined("CONNECTIONS_LOADED")) {
            Application::register_error(new \Error("Database not initiated."));
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throws exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulated prepared statements
            PDO::ATTR_CASE               => PDO::CASE_NATURAL,      // Preserve original case of column names
            PDO::ATTR_PERSISTENT         => false,                  // Disable persistent connections
            PDO::ATTR_TIMEOUT            => 5                       // Set a connection timeout of 5 seconds
        ];
        $pdo = new PDO($dsn, $user, $password, $options);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
       
        return $pdo;

    }

    /**
     * Detect if SQL statement contains prohibited keywords.
     * @param string $statement SQL statement to check.
     * @return bool True if risky, false otherwise.
     */
    public function in_blacklist(string $statement): bool
    {
        $prevent = $this->prohibited_statements;
        $prohibited_statements = is_array($prevent) ? $prevent : [$prevent];

        foreach ($prohibited_statements as $key) {
            if (str_contains($statement, $key)) {
                return true; // Dangerous SQL
            }
        }

        return false; // Non-dangerous SQL
    }

    /**
     * Verify if PDO connection is successful.
     * @param PDO $pdo The PDO connection object.
     */
    private static function verify_connection(mixed $pdo)
    {
        // Placeholder for connection verification logic
        if($pdo == null){
          Application::register_error(new \Exception("Connection Failed."));
          return false;
        }

        if($pdo -> query("SELECT 1") == false){
        
            return false;
        }


        return true;


    }

    /**
     * Perform an SQL query and return its results.
     * @param string $query SQL query to execute.
     * @return array|false Fetched results or false on error.
     */
    public function query(string $query)
    {
        if ($this->in_blacklist($query) || !str_contains(strtolower($query), 'select')) {
            return false;
        }

        try {
            $queryHandler = $this->PDO->query($query);
            $res = $queryHandler->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            Application::register_error(new \Error("Could not create the database connection, error: $e"));
            return false;
        }

        return $res ?? false;
    }
}

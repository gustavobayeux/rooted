<?php

namespace Rooted\Database;

use Rooted\Database\Table;
use Rooted\Environment\Application;

class QueryBuilder
{

    //These properties hold the state of the query being built
    private string $statement;
    private string $query;
    private array $prepared_values = [];
    private mixed $result_fetch_all;


    protected object $table; //The table object
    private string $table_name;
    private object $pdo;
    private array $table_columns;

    function __construct($statement, $values = null, $columns = null, $table)
    {



        $this->table = $table;

        $this->pdo = $this->table->mapper->PDO;

        $this->table_name = $this->table->table_name;

        $this->table_columns = $this->table->table_columns;

        $this -> statement = $statement;
        switch ($statement) {

            case "SELECT":
                    $this->select_formatter($columns);
                
                break;

            case "INSERT":

                if (isset($values)) {
                    $this->insert_formatter($values);
                }
                break;

            case "UPDATE":
                    $this->update_formatter();
                
                break;

            case "DELETE":

                $this->delete_formatter();

                break;
        }
    }
    /**
     * Format a SELECT query.
     * 
     * @param mixed $columns The columns to be selected
     * 
     * @return $this
     */
    private function select_formatter($columns)
    {
        
        if (empty($columns)) {
            $columnsSql = "*";
        } elseif (is_array($columns)) {
            $columnsSql = implode(",", $columns);
        } else {
            $columnsSql = $columns;
        }

        $sql = "SELECT $columnsSql FROM " . strtolower($this->table_name);
        $this->query = $sql;

        return $this;
    }

    /**
     * Format an INSERT query.
     * 
     * @param array $values The values to be inserted into the table
     * 
     * @return $this|false Returns the current instance on success, false on failure
     */
    private function insert_formatter($values)
    {
        if (count($values) !== count($this->table_columns)) {
            Application::register_error(new \Exception("Your values are not matching your table columns."));
            return false;
        }

        $params = $this->create_question_marks($values); // Turn values into question marks for future insertion

        if ($params !== false && count($params) == count($values)) {
            $paramsStr = implode(",", $params);
            $sql = "INSERT INTO " . strtolower($this->table_name) . " VALUES(" . $paramsStr . ")";
        } else {
            Application::register_error(new \Exception("Couldn't place \"?\" parameters for your INSERT statement."));
            return false;
        }

        $this->prepared_values = $values;
        $this->query = $sql;

        return $this;
    }

    /**
     * Format an UPDATE query.
     * 
     * @return $this
     */
    private function update_formatter()
    {
       

        $sql = "UPDATE " . strtolower($this->table_name);
        $this->query = $sql;

        return $this;
    }

    /**
     * Set the columns and values for an UPDATE query.
     * 
     * @param array $columns The columns to be updated
     * @param array $values The new values for the columns
     * 
     * @return $this|false Returns the current instance on success, false on failure
     */
    public function set($columns, $values)
    {
        if ($this -> statement !== "UPDATE") {
            Application::register_error(new \ParseError("You cannot use a SET statement if not right after an UPDATE statement."));
            return false;
        }
        
        $params = $this->create_question_marks($values);

        if (!is_array($params) || count($params) !== count($values)) {
      
            Application::register_error(new \Exception("Couldn't place \"?\" parameters for your UPDATE statement."));
            return false;
        }

        $setStr = " SET ";

        // Create column=VALUE assignments
        $i = 0;
        foreach ($params as $param) {
            $comma = $i < count($params) - 1 ? "," : "";
            $setStr .= $columns[$i] . "=" . $param . $comma;
            $i++;
        }

        $this->prepared_values = $values;
        $this->query .= $setStr;

        return $this;
    }

    /**
     * Format a DELETE query.
     * 
     * @return $this
     */
    private function delete_formatter()
    {
        $sql = "DELETE FROM " . strtolower($this->table_name);
        $this->query = $sql;

        return $this;
    }

    /**
     * Add a WHERE clause to the query.
     * 
     * @param string|null $where The WHERE condition
     * @param array|null $where_values The values for the WHERE clause (optional)
     * 
     * @return $this|false Returns the current instance on success, false on failure
     */
    public function where(string $where, array $where_values = null)
    {
      
        if (defined("LIMIT")) {
            Application::register_error(new \Exception("You cannot use a WHERE statement after a LIMIT statement"));
            return false;
        }else if ($this -> statement == "INSERT") {

            Application::register_error(new \Exception("You cannot use a WHERE statement in a INSERT statement"));
            return false;
        }

        if (str_contains($where, "?")) {

            if (substr_count($where, "?") !== count($where_values)) {
                Application::register_error(new \Exception("Parameter number \"?\" not matching \$where_values."));
                return false;
            }
        }
        $sql = " WHERE $where";

        $this->query .= $sql;

        if (str_contains($where, "?")) {
            $this->prepared_values = array_merge($this->prepared_values, $where_values);
        }

        
        echo $this -> query ;
        return $this;
    }

    /**
     * Add a LIMIT clause to the query.
     * 
     * @param int $limit The maximum number of rows to return
     * 
     * @return $this|false Returns the current instance on success, false on failure
     */
    public function limit(int $limit)
    {
        define("LIMIT", true);

        if (!is_int($limit) || $limit > 10000) {
            Application::register_error(new \Exception("Bad formatting for the statement LIMIT."));
            return false;
        }

        $sql = " LIMIT $limit ";
        $this->query .= $sql;

        return $this;
    }

    /**
     * Execute the query and return the results.
     * 
     * @param bool $return | True for Fetch All, False for not returning results.
     * @return void
     */
    public function exec($return = false)
    {

        $sql = $this->query;

        try {
            $pdo = $this->pdo;
            $dbh = $pdo->prepare($sql);

            $main_statement_counter = 1;

            // Insert values to the question marks
            foreach ($this->prepared_values as $value) {
                $dbh->bindValue($main_statement_counter, $value);
                $main_statement_counter++;
            }

            $dbh->execute();

            //In the future, allow different types of fetching
            if ($return) {

                $result_fetch_all = $dbh->fetchAll();
                $dbh = null;//Close the connection, opened riiiiight back in Database::open_connection

                return $result_fetch_all;
            }
        } catch (\Throwable $e) {
            Application::register_error(new \Error("Error during SQL execution: $e"));
            return false;
        }
    }

    /**
     * Fetch all results from the executed query.
     * 
     * @return mixed The result set from the executed query
     */
    public function fetchAll()
    {
        return $this->result_fetch_all;
    }

    /**
     * Convert values into PDO positional placeholders.
     * 
     * @param array $values The values to be converted into placeholders
     * 
     * @return array|false The array of placeholders or false if there is an error
     */
    private function create_question_marks(array $values)
    {
        return array_fill(0, count($values), "?");
    }
}

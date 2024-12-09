
 # Swiss: The Swiss knife of PHP Micro-ORMs

 ### Compact, Familiar and Useful. Always sharp and packed with juxtaposing utilities, like a Swiss Knife.

 ### This tool is intended for fast development of CRUDS, the goal of Swiss is handling simple all-time needed tasks.
 
- Three files, Three Classes, 12KB, No dependencies, Purely PHP 8+
 
### **Features:**
 - Schema-independent Mapping
 - CRUD Operations
 - Automatic JOIN creation
 - Prepares all SQL values (to prevent SQL injection)
 - Multiple ODBC Connections
 - Risky SQL filtering
 - Alows custom methods for your tables (ex: Users -> banUser())
 - Works with any Framework and other ORMs
##
 ### **DEMONSTRATION:**
 Without Swiss:

 `$dbh -> query("SELECT username FROM users_table WHERE id = 1")`
 
 `$result = $dbh -> fetchAll(dbh)`
 
 `echo $result`

 with Swiss:
 
 `result = $dbh -> Users_table -> select("username","id = 1")`
 
 `echo $result`
 
 ### **UTILIZATION:**
 
 After mapping, every table turns into an Object, with CRUD operations.
 
 CREATE: `$orm -> Users -> insert (['col1 datatype()','col2 datatype()'...])`
 
 READ:   `$orm -> Users -> select (["cols"],"ID = 123") //parameters are optional`

 UPDATE: `$orm -> Users -> update ("ID = 123",['col1 datatype()']...)`

 DELETE: `$orm -> Users -> delete ("ID = 123")` 
 
 ##

 Automatic joins using the same CRUD operations from before

 `$orm -> join("Sales","Products") -> select(["table1.col", "table2.col"],"id = 2") //Results join where id = 2`

 ##

 Structural methods [in the Future]

 `$orm -> Users -> getColumns() // Returns array of columns`
 `$orm -> Users -> setColumn("colname")`
 `$orm -> Users -> deleteColumn("colname")`

 ##
 
 You can also use the PDO native Query and Prepare functions
 
 `$orm -> PDO -> query("SELECT * FROM ...", PDO:FETCH_ASSOC);`
 
 `$orm -> PDO -> prepare("INSERT INTO users... VALUES(:v1,:v2...)");`
 ##

 You can set SQL statements you want to prevent:
 
 `$orm -> setBlacklist(mixed $statement) // String or Array of statement(s)`
 
 ##
 
  You can include your custom functions:
  
 `$orm -> include('your_functions.php')`

  Then, assign them to a table:
 
 `$orm -> Users -> load('function_name')`
 
  Once, loaded, use them like built-in methods:

 `$orm -> Users -> function_name()`
 
 ### **INSTALLING:**

 You can have multiple databases, each one mapped in a different ORM   instance:
 
 1) Set your database constants:

 define("DB_USER", "username_here")
 
 define("DB_PASSWORD", "password_here")
  
 2) Map your database using Mapper:
 
 `new Mapper($dsn, $connection_password, 0)`
 
 `//$connection_password is used internally, to connect the modules`
 
 `new Mapper($this->getDsn(0), "ABCDEFG", 0)//Example of connection`
 
  Optional, include your own functions now:
 
 `$orm::import("functions.php");`


 ### **CLASSES IN THIS ORM:**

 *Connection* | Organize PDO connections and prevents risky SQL statements
 
 *Mapper*  | Maps database Tables and Columns
 
 *Table*   | Table Class for CRUD operations
 
___
 Made with <3 By Gustavo Bayeux 
 
 GitHub: https://github.com/gustavobayeux/Swiss-ORM
 
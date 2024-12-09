<?php

namespace App\Controllers;

use Rooted\Database\Database;
use Rooted\Environment\Application;

class site
{

    public function abrir($route)
    {
        echo "\nController Opened, at the method abrir\n\n";

        Database::include_methods(__FILE__,$this);

        Application::create_src_script("middlewares","autenticar2","<?php echo 'vapo';");
    }


    //Example of database 
    public function user_func()
    {
        $Users = Database::open_connection("DEFAULT");

        echo "\n" . var_dump($Users-> Users -> select("username")->exec(true));
    }
}

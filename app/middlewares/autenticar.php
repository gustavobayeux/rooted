<?php 
namespace App\Middlewares;

class Autenticar
{

    public function __construct($route) {

        echo"\nHas passed in the middleware defined by the user\n";
        $route -> next();

        return $route;

    }


}

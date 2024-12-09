<?php 
namespace Rooted\Middlewares;

class Autenticar
{

    public function __construct($route) {

        echo"\npassou no middleware Interno";
        $route -> next();

        return $route;

    }


}

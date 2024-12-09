<?php

$facades = [
 "/Database/facades/Database.php",
 "/Routing/facades/Router.php"
];

$models = [
 "/Environment/models/Application.php",
 "/Database/models/Connection.php",
 "/Database/models/Mapper.php", 
 "/Database/models/Table.php",
 "/Database/models/QueryBuilder.php",
 "/Routing/models/Route.php"
];

$controllers = [
];


$classes = array_merge($facades,$models,$controllers);

try{
 foreach($classes as $class){
    require_once __DIR__ . $class;
 }
}catch(\Throwable $e){
   echo "Framework unexpected structure";
}

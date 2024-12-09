<?php
use Rooted\Routing\Router;


Router::post("/caminho1") -> open_controller("site","abrir");

Router::group(["/caminho2","/caminho3"]) -> middleware("src","autenticar") -> open_controller("site","abrir");

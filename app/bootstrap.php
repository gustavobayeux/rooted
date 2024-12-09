<?php
use Rooted\Environment\Application;
use Rooted\Database\Database;
use Rooted\Routing\Router;

Application::start();

Database::start();

Router::start();

//Application::load_addon("f51a8f1bfae11cfd7559b2c6e110f6de");


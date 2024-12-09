<?php

namespace Rooted\Routing;

use Rooted\Environment\Application;
use Rooted\Routing\Router;

class Route
{
    private string $method;
    private mixed $path;
    private bool $route_innit = false;
    private bool $has_middleware = false;
    private bool $middleware_passed = false;
    private string $template_engine_extension = "";

    /**
     * Constructor for initializing a route.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string|array $path Route path (e.g., "/home")
     * @param string $template_engine_extension The template engine file extension (e.g., ".twig", ".php")
     */
    public function __construct(string $method, string|array $path, string $template_engine_extension)
    {


        if ($this->catch_path( $path)) {

            $this->path = $path;
            $this->route_innit = true;
            $this->method = $method;
            $this->template_engine_extension = $template_engine_extension;

        }
    }

    private function catch_path( $path)
    {

        $currentPath = addslashes(explode("?", $_SERVER["REQUEST_URI"])[0]);
    
        if (is_array( $path)) {
            return in_array($currentPath,  $path);
        }
    
        return $currentPath ===  $path;
    }
    
    /**
     * Sets a middleware for the route.
     *
     * @param string $component | "app" for your own Middlewares, "src" for an addon middleware.
     * @param string $name Middleware name to load
     * @return $this Returns the current route object for method chaining
     */
    public function middleware(string $component,string $name)
    {
        if($component == "app"){

            Application::load_user_script("middlewares", $name);
            $fully_qualified_name = "App\Middlewares\\" . ucfirst($name);    

        }else if($component == "src"){

            Application::load_src_script("middlewares", $name);
            $fully_qualified_name = "Rooted\Middlewares\\" . ucfirst($name);

        }else{

        Application::register_error(new \Exception("the middleware \$component must be either \"app\" or \"src\""));
            return false;
        }

        if (!$this->route_innit) {
            return $this;
        }
        $this->has_middleware = true;

        if (!class_exists($fully_qualified_name)) {
            
            Application::register_error(new \Error("Middleware Class and/or Method does not exists"));
            return false;
        }


        new $fully_qualified_name($this);

        return $this;
    }

    public function next()
    {
        $this->middleware_passed = true;
        return $this;
    }


    /**
     * Loads and renders a view template.
     *
     * @param string $name View template name, not filepath
     * @return bool|string Returns false if middleware is not passed, or the result of loading the view file
     */
    public function open_view(string $name)
    {
        if (!$this->route_innit) {
            return $this;
        }

        if ($this->has_middleware && !$this->middleware_passed) {
            Application::register_error(new \Exception("Middleware not properly set during View loading."));
            return false;
        }

        $path_to_view = str_contains($name, ".") ? str_replace(".", "/", $name) : $name;

        return Application::load_user_script(
            "views",
            $path_to_view . $this->template_engine_extension
        );
    }

    /**
     * Loads a controller for the route.
     * param string $name Controller name
     * param string $entry_method Method to be called
     * @return bool|string Returns false if middleware is not passed, or the result of loading the controller
     */
    public function open_controller(string $name, string $entry_method)
    {
        if (!$this->route_innit) {
            return $this;
        }


        if ($this->has_middleware && !$this->middleware_passed) {
            Application::register_error(new \Exception("Middleware not properly set during Controller loading."));
            return false;
        }

        $path_to_controller = str_contains($name, ".") ? str_replace(".", "/", $name) : $name;

        if (!Application::load_user_script(
            "controllers",
            $path_to_controller . $this->template_engine_extension
        )) {

            Application::register_error(new \Exception("Controller not working."));
            return false;
        }

        $fully_qualified_name = "App\Controllers\\" . ucfirst($name);

        // Check if the class exists
        if (!class_exists($fully_qualified_name) || !method_exists($fully_qualified_name, $entry_method)) {

            // Handle method not found in the controller
            Application::register_error(new \Exception("Controller Class not working"));
            return false;
        }
        // Instantiate the controller
        $controller = new $fully_qualified_name();
        $controller->$entry_method($this);
    }
}

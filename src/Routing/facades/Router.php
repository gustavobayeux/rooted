<?php

namespace Rooted\Routing;

use Rooted\Environment\Application;
use Rooted\Routing\Route;

/**
 * Class Router
 * Handles routing and request processing for the application.
 */
class Router
{
    /**
     * @var string $request The type of the request origin (e.g., "browser", "api", "cli").
     */
    private static string $request = '';

    /**
     * Starts the routing process by loading user-defined routes.
     *
     * @return bool True if routes were successfully loaded, false otherwise.
     */
    public static function start()
    {
        if (!Application::load_user_script("routes", Self::request_origin())) {
            return false;
            exit(); // Exit ensures the script stops executing, though redundant after return.
        }

        return true;
    }

    /**
     * Configures a templating engine for a specific controller.
     *
     * @param string $controller The name of the controller.
     * @param string $extension The file extension of the templating engine (e.g., ".php", ".twig").
     */
    public static function set_templating_engine($controller, $extension)
    {
        // Allows you to point to a specific templating engine for a controller.
    }

    /**
     * Creates a new GET route for the given path.
     *
     * @param string $path The URL path for the GET route.
     * @return Route An instance of the Route class for the GET route.
     */
    public static function get($path)
    {
        return new Route("GET", $path, "");
    }

    /**
     * Creates a new POST route for the given path.
     *
     * @param string $path The URL path for the POST route.
     * 
     * @return Route An instance of the Route class for the POST route.
     * 
     */
    public static function post($path)
    {
        return new Route("POST", $path, "");
    }


    /**
     * Creates a new PUT route for the given path.
     *
     * @param string $path The URL path for the PUT route.
     * @return Route An instance of the Route class for the PUT route.
     */
    public static function put($path)
    {
        return new Route("PUT", $path, "");
    }

    /**
     * Creates a new ANY route for the given path.
     *
     * @param string $path The URL path for the ANY route.
     * @return Route An instance of the Route class for the ANY route.
     */
    public static function any($path)
    {
        return new Route("ANY", $path, "");
    }

    
    /**
     * Creates a new ANY route for the given path.
     *
     * @param array $path The URL paths for the ANY route.
     * @return Route An instance of the Route class for the ANY route.
     */
    public static function group(array $path)
    {
        return new Route("ANY", $path, "");
    }



    /**
     * Determines the origin of the request (e.g., browser, API, or CLI).
     *
     * @return string The origin of the request.
     */
    public static function request_origin()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) { // Access over HTTP
            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            // Verify if it's a known Browser
            if (str_contains($userAgent, "Mozilla/5.0")) {
                return "browser";
            }

            // Verify if it's a known API provider
            foreach (KNOWN_API_PROVIDERS as $api) {
                if (str_contains($userAgent, $api) !== false) {
                    return "api";
                }
            }
        }

        // Verify if it comes from CLI (Most of the time, a cronjob)
        if (php_sapi_name() == "cli") {
            return "cli";
        }

        http_response_code("501"); // Return a 501 HTTP status code for unknown request origins.
        exit();
    }
}

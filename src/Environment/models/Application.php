<?php

namespace Rooted\Environment;

use Rooted\Environment\ApplicationBuild;

class Application
{

    private static $errors = [];

    private static $env_path = "../app/.env";

    static function start()
    {

        // Ensure that SSL is enforced, environment is loaded, and constants are set
        $SSL = Self::enforce_ssl();
        $ENV = Self::load_env();
        $CONSTANTS = Self::set_constants();

        if ($SSL && $ENV && $CONSTANTS) {

            return true;
        } else {
            Self::register_error(new \Error("Application could not be started. Please check the SSL, environment, and constants setup."));
            exit();
        }
    }

    /**
     * LOAD .ENV FILE TO $_ENV
     * 
     * @param string $file_path | Path to the .env file that will be loaded
     * @param True on success or False on error.
     **/
    static private function load_env()
    {

        $file_path = Self::$env_path;

        try {
            $lines = file($file_path);
            $envKey = ''; // .env keys to check after loading

            // Iterates over each line on the .env file
            foreach ($lines as $line) {

                // Jumps comments and blank lines
                if (str_contains($line, "#") || !isset(explode('=', $line, 2)[1])) {
                    continue;
                }


                [$key, $value] = explode('=', trim($line), 2);


                $key = trim($key);
                $value = trim($value);

                putenv(sprintf('%s=%s', $key, $value));
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;

                //Store last key to check if it got loaded
                if ($lines[count($lines) - 1] == $line) {
                    $envKey = $key;
                }
            }

            if (!isset($_ENV[$envKey])) {

                Self::register_error(new \Exception("Env not loading."));
                return false;
            }

            return true;
        } catch (\Throwable) {
            Self::register_error(new \Exception("Unknown Error during .Env loading"));
            return false;
        }
    }

    /**
     * Set a custom .Env path, in case its not in the same level as bootstrap.php
     * 
     * @return True on success, False on error;
     **/
    static public function set_env($path)
    {

        Self::$env_path = $path;
    }



    /**
     * Enforces a SSL connection
     * 
     * @return True on success, False on error;
     **/
    static private function enforce_ssl()
    {
        if (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {

            // SSH check
            if (php_sapi_name() !== 'cli' || !isset($_SERVER['SSH_CONNECTION'])) {
                Self::register_error(new \Error("This Application cannot run without SSL or SSH."));
                return false;
            }
        }

        return true;
    }

    /**
     * Stores Error in Array
     *
     * @param object Warning, Exception or Error object handler;
     * 
     * @return True on success or Error;
     **/
    static public function register_error($handler)
    {
        $encapsulatedError = [
            "message" => $handler?->getMessage() ?? null,
            "previous" => $handler?->getPrevious() ?? null,
            "code" => $handler?->getCode() ?? null,
            "file" => $handler?->getFile() ?? null,
            "line" => $handler?->getLine() ?? null,
            "trace" => $handler?->getTrace() ?? null
        ];

        if (array_push(Self::$errors, $encapsulatedError)) {

            return true;
        } else {
            throw new \Error("Couldn't handle the error.");
            return false;
        }
    }


    /**
     * Returns all Error messages, be they Warnings, Exceptions or Errors
     * 
     * @return Array | Returns an array containing error details,
     *  
     **/
    static public function get_errors()
    {

        if (!isset($_ENV["DEBUG_PASSWORD"])) {
            return [
                ["message" => "The environment could not be loaded."]
            ];
        }

        if (isset($_GET[$_ENV["DEBUG_PASSWORD"]])) {



            return Self::$errors;
        }
    }

    /**
     * Require an User script, within /app folder
     *
     * @param string $component | "controllers", "models", "routes", "middlewares" or "views"
     *  
     * @return bool | True on success, False otherwise
     *  
     **/
    static public function load_user_script($component, $script)
    {
        $path = realpath('../app/' . $component . "/" . $script . ".php");

        if (!$path) {
            Self::register_error(new \Error("The required user script does not exists"));
            return false;
        }

        require_once $path;

        return true;
    }

    /**
     * Require an Internal script, within /src/includes folder
     *
     * @param string $component | "middlewares" or "views"
     *  
     * @return bool | True on success, False otherwise
     *  
     **/
    static public function load_src_script($component, $script)
    {
        $path = realpath('../src/includes/' . $component . "/" . $script . ".php");

        if (!$path) {
            Self::register_error(new \Error("The required internal script does not exists"));
            return false;
        }

        require_once $path;

        return true;
    }

    /**
     * Create an internal script
     *
     * @param string $component | "middlewares" or "views"
     * @param string $name   | The name of the script being created
     * @param string $content   | The PHP code that will be written 
     *  
     * @return bool | True on success, False otherwise
     *  
     **/
    static public function create_src_script($component, $name, $content)
    {

        $path = '../src/includes/' . $component . "/" . $name . ".php";

        if (!realpath($path)) {
            file_put_contents($path, $content);
        } else {
            Self::register_error(new \Error("Script already exists"));
            return false;
        }


        return true;
    }

    /**
     * Load an Addon
     *
     * @param string $md5_paths | all depencies as an MD5
     * @param string $key | key for opening the MD5
     *  
     * @return bool | True on success, False otherwise
     *  
     **/
    static public function load_addon($md5_paths,$key)
    {

       //For future development

    }


    /**
     * Configures environmental constants
     * 
     * @return bool | True on success, False otherwise
     *  
     **/
    static private function set_constants()
    {


        return define(
            "KNOWN_API_PROVIDERS",
            [
                'curl', // curl
                'Postman', // Postman
                'newman', // Postman CLI (newman)
                'aws-sdk-php', // AWS SDK for PHP
                'GCP-API-Client', // Google Cloud API Client
                'Azure-SDK-for-PHP', // Microsoft Azure SDK for PHP
                'PostmanRuntime', // Postman Runtime
                'Insomnia', // Insomnia
                'RapidAPI', // RapidAPI
                'Apigee', // Apigee API gateway
                'Kong', // Kong API Gateway
                'Axios', // Axios (JavaScript)
                'Requests', // Requests (Python)
                'Guzzle', // Guzzle (PHP)
                'Twilio', // Twilio
                'Stripe', // Stripe
                'OpenAI', // OpenAI API
            ]
        );
    }
}

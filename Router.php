<?php
namespace OOSlim;

use Slim\Slim;
Class Router
{
    protected $app;
    private $namespace;

    public function __construct($ns = NULL)
    {
        $this->app = new Slim;

        $this->namespace = empty($ns) ? "\\rg" : $ns;

        // Esto es para permitir las peticiones mediante Cross domain
        $this->app->map('/:x+', function($x) {
            http_response_code(200);

        })->via('OPTIONS');
    }

    public function addRoutes($routes)
    {
        foreach ($routes as $argsArray) {
            $args = array();
            $method = "any";
            
            array_push($args, array_shift($argsArray)); // Extract the pattern and add at the start of the array
            
            $pathStr = array_pop($argsArray); // Extract the main function string

            if(count($argsArray) > 0) {
                foreach ($argsArray as $middleStr) {
                    $args[] = $this->processMiddleWare($middleStr);
                }
            }

            if (strpos($pathStr, "@") !== false) {
                list($pathStr, $method) = explode("@", $pathStr);
            }
            // Proccess the main function string
            $args[] = $this->processCallback($pathStr);
            call_user_func_array( array($this->app,$method), $args);
        }
    }

    private function processMiddleWare($path)
    {
        if (strpos($path, ":") !== false) {
            list($class, $path) = explode(":", $path);
        }

        list($path, $strArgs) = explode("#" , $path);
        $args = explode(",", $strArgs);

        $function = ($path != "") ? $path : "index";

        $func = function () use ($class, $function, $args) {
            $class = $this->newClass($class);
            return call_user_func_array(array($class, $function), $args);
        };

        return $func;       
    }

    private function processCallback($path)
    {
        $class = "Main";

        if (strpos($path, ":") !== false) {
            list($class, $path) = explode(":", $path);
        }

        $function = ($path != "") ? $path : "index";

        $func = function () use ($class, $function) {
            $class = $this->newClass($class);
            $args = func_get_args();

            return call_user_func_array(array($class, $function), $args);
        };

        return $func;
    }

    private function newClass($class)
    {
        $class = $this->namespace . '\\Routes\\' . $class . 'Route';
        $class = new $class();
        return $class;
    }

    public function start()
    {
        $this->app->run();
    }
}

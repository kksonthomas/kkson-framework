<?php

namespace KKsonFramework\Classes\Slim;

use Slim\Slim as SlimBase;

class Slim extends SlimBase {
    protected function mapRoute($args)
    {
        $pattern = array_shift($args);
        $callable = array_pop($args);

        if(!is_callable($callable)) {
            $callable = $this->warpCallable($callable);
        }

        $route = new \Slim\Route($pattern, $callable, $this->settings['routes.case_sensitive']);
        $this->router->map($route);
        if (count($args) > 0) {
            $route->setMiddleware($args);
        }

        return $route;
    }

    protected function warpCallable($callable) {
        $callableName = "";
        if(!is_callable($callable, true, $callableName)) {
            throw new \Exception("Invalid callable on warpCallable");
        }
        $callableName = str_replace("::", ":", $callableName);
        return $callableName;
    }
}


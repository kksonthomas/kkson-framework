<?php

namespace KKsonFramework\Classes\Slim;

use Slim\Slim as SlimBase;

class Slim extends SlimBase {
    /**
     * Variadic wrappers so static analysis (e.g. Intelephense P1119) matches arity for Slim 2
     * runtime behavior (zero declared parameters + func_get_args() in the parent class).
     */
    public function map(...$args)
    {
        return parent::map(...$args);
    }

    public function get(...$args)
    {
        return parent::get(...$args);
    }

    public function post(...$args)
    {
        return parent::post(...$args);
    }

    public function put(...$args)
    {
        return parent::put(...$args);
    }

    public function patch(...$args)
    {
        return parent::patch(...$args);
    }

    public function delete(...$args)
    {
        return parent::delete(...$args);
    }

    public function options(...$args)
    {
        return parent::options(...$args);
    }

    public function any(...$args)
    {
        return parent::any(...$args);
    }

    public function group(...$args)
    {
        parent::group(...$args);
    }

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


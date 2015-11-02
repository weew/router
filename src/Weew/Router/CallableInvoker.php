<?php

namespace Weew\Router;

class CallableInvoker implements ICallableInvoker {
    /**
     * @param callable $callable
     * @param IRouter $router
     */
    public function invoke(callable $callable, IRouter $router) {
        $callable($router);
    }
}

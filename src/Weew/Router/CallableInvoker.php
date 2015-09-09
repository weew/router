<?php

namespace Weew\Router;

class CallableInvoker implements ICallableInvoker {
    /**
     * @param IRouter $router
     * @param callable $callable
     */
    public function invoke(IRouter $router, callable $callable) {
        $callable($router);
    }
}

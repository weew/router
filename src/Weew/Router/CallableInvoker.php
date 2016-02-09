<?php

namespace Weew\Router;

class CallableInvoker implements ICallableInvoker {
    /**
     * @param $callable
     * @param IRouter $router
     */
    public function invoke($callable, IRouter $router) {
        $callable($router);
    }
}

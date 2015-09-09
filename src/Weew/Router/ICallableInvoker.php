<?php

namespace Weew\Router;

interface ICallableInvoker {
    /**
     * @param IRouter $router
     * @param callable $callable
     */
    function invoke(IRouter $router, callable $callable);
}

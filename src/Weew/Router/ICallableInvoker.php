<?php

namespace Weew\Router;

interface ICallableInvoker {
    /**
     * @param callable $callable
     * @param IRouter $router
     */
    function invoke($callable, IRouter $router);
}

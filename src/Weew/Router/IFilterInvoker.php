<?php

namespace Weew\Router;

interface IFilterInvoker {
    /**
     * @param callable $filter
     * @param IRoute $route
     *
     * @return bool
     */
    function invoke(callable $filter, IRoute $route);
}

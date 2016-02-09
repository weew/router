<?php

namespace Weew\Router;

interface IFilterInvoker {
    /**
     * @param $filter
     * @param IRoute $route
     *
     * @return bool
     */
    function invoke($filter, IRoute $route);
}

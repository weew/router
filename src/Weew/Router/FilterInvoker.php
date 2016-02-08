<?php

namespace Weew\Router;

class FilterInvoker implements IFilterInvoker {
    /**
     * @param callable $filter
     * @param IRoute $route
     *
     * @return bool
     */
    public function invoke(callable $filter, IRoute $route) {
        return $filter($route);
    }
}

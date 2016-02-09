<?php

namespace Weew\Router;

class FilterInvoker implements IFilterInvoker {
    /**
     * @param $filter
     * @param IRoute $route
     *
     * @return bool
     */
    public function invoke($filter, IRoute $route) {
        return $filter($route);
    }
}

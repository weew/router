<?php

namespace Weew\Router;

class FilterInvoker implements IFilterInvoker {
    /**
     * @param callable $filter
     *
     * @return bool
     */
    public function invoke(callable $filter) {
        return !! $filter();
    }
}

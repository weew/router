<?php

namespace Weew\Router;

interface IFilterInvoker {
    /**
     * @param callable $filter
     *
     * @return bool
     */
    function invoke(callable $filter);
}

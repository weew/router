<?php

namespace Weew\Router;

interface IParameterResolverInvoker {
    /**
     * @param $parameter
     * @param callable $resolver
     *
     * @return mixed
     */
    function invoke($parameter, callable $resolver);
}

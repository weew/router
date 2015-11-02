<?php

namespace Weew\Router;

interface IParameterResolverInvoker {
    /**
     * @param callable $resolver
     * @param $parameter
     *
     * @return mixed
     */
    function invoke(callable $resolver, $parameter);
}

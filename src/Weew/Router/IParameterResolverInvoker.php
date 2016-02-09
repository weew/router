<?php

namespace Weew\Router;

interface IParameterResolverInvoker {
    /**
     * @param $resolver
     * @param $parameter
     *
     * @return mixed
     */
    function invoke($resolver, $parameter);
}

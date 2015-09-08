<?php

namespace Weew\Router;

class ParameterResolverInvoker implements IParameterResolverInvoker {
    /**
     * @param $parameter
     * @param callable $resolver
     *
     * @return mixed
     */
    public function invoke($parameter, callable $resolver) {
        return $resolver($parameter);
    }
}

<?php

namespace Weew\Router;

class ParameterResolverInvoker implements IParameterResolverInvoker {
    /**
     * @param callable $resolver
     * @param $parameter
     *
     * @return mixed
     */
    public function invoke(callable $resolver, $parameter) {
        return $resolver($parameter);
    }
}

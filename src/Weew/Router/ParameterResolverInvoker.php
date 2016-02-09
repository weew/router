<?php

namespace Weew\Router;

class ParameterResolverInvoker implements IParameterResolverInvoker {
    /**
     * @param $resolver
     * @param $parameter
     *
     * @return mixed
     */
    public function invoke($resolver, $parameter) {
        return $resolver($parameter);
    }
}

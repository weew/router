<?php

namespace Weew\Router;

interface IParameterResolver {
    /**
     * @param IRoute $route
     */
    function resolveRouteParameters(IRoute $route);

    /**
     * @return array
     */
    function getResolvers();

    /**
     * @param array $resolvers
     */
    function setResolvers(array $resolvers);

    /**
     * @param $name
     * @param callable $resolver
     */
    function addResolver($name, callable $resolver);

    /**
     * @return IParameterResolverInvoker
     */
    function getParameterResolverInvoker();

    /**
     * @param IParameterResolverInvoker $parameterResolverInvoker
     */
    function setParameterResolverInvoker(IParameterResolverInvoker $parameterResolverInvoker);
}

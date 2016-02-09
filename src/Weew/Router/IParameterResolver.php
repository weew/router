<?php

namespace Weew\Router;

interface IParameterResolver {
    /**
     * @param IRoute $route
     */
    function resolveRouteParameters(IRoute $route);

    /**
     * @return IRouteResolver[]
     */
    function getResolvers();

    /**
     * @param IRouteResolver[] $resolvers
     */
    function setResolvers(array $resolvers);

    /**
     * @param IRouteResolver $resolver
     */
    function addResolver(IRouteResolver $resolver);

    /**
     * @return IParameterResolverInvoker
     */
    function getParameterResolverInvoker();

    /**
     * @param IParameterResolverInvoker $parameterResolverInvoker
     */
    function setParameterResolverInvoker(IParameterResolverInvoker $parameterResolverInvoker);
}

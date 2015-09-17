<?php

namespace Weew\Router;

use Weew\Url\IUrl;

interface IRoutesMatcher {
    /**
     * @param IRoute[] $routes
     * @param $method
     * @param IUrl $url
     *
     * @return IRoute|null
     *
     * @see HttpRequestMethod
     */
    function match(array $routes, $method, IUrl $url);

    /**
     * @return array
     */
    function getPatterns();

    /**
     * @param array $patterns
     */
    function setPatterns(array $patterns);

    /**
     * @param string $name
     * @param string $pattern
     */
    function addPattern($name, $pattern);

    /**
     * @return array
     */
    function getFilters();

    /**
     * @param array $filters
     */
    function setFilters(array $filters);

    /**
     * @param $name
     * @param callable $filter
     */
    function addFilter($name, callable $filter);

    /**
     * @param $names
     */
    function enableFilters(array $names);

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
     * @return IFilterInvoker
     */
    function getFilterInvoker();

    /**
     * @param IFilterInvoker $filterInvoker
     */
    function setFilterInvoker(IFilterInvoker $filterInvoker);

    /**
     * @return IParameterResolverInvoker
     */
    function getParameterResolverInvoker();

    /**
     * @param IParameterResolverInvoker $parameterResolverInvoker
     */
    function setParameterResolverInvoker(IParameterResolverInvoker $parameterResolverInvoker);

    /**
     * @return IRestrictionsMatcher
     */
    function getRestrictionsMatcher();

    /**
     * @param IRestrictionsMatcher $restrictionsMatcher
     */
    function setRestrictionsMatcher(IRestrictionsMatcher $restrictionsMatcher);
}

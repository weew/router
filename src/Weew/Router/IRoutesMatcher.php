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
     * @return IFilterInvoker
     */
    function getFilterInvoker();

    /**
     * @param IFilterInvoker $filterInvoker
     */
    function setFilterInvoker(IFilterInvoker $filterInvoker);

    /**
     * @return IRestrictionsMatcher
     */
    function getRestrictionsMatcher();

    /**
     * @param IRestrictionsMatcher $restrictionsMatcher
     */
    function setRestrictionsMatcher(IRestrictionsMatcher $restrictionsMatcher);

    /**
     * @return IParameterResolver
     */
    function getParameterResolver();

    /**
     * @param IParameterResolver $parameterResolver
     */
    function setParameterResolver(IParameterResolver $parameterResolver);
}

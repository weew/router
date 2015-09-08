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
     * @return array
     */
    function getProtocols();

    /**
     * @param array $protocols
     */
    function setProtocols(array $protocols);

    /**
     * @return array
     */
    function getTLDs();

    /**
     * @param array $tlds
     */
    function setTLDs(array $tlds);

    /**
     * @return array
     */
    function getDomains();

    /**
     * @param array $domains
     */
    function setDomains(array $domains);

    /**
     * @return array
     */
    function getSubdomains();

    /**
     * @param array $subdomains
     */
    function setSubdomains(array $subdomains);

    /**
     * @return array
     */
    function getHosts();

    /**
     * @param array $hosts
     */
    function setHosts(array $hosts);

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
}

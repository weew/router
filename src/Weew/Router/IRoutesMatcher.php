<?php

namespace Weew\Router;

use Weew\Url\IUrl;
use Weew\UrlMatcher\IUrlMatcher;

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
     * @param string $name
     * @param string $pattern
     */
    function addPattern($name, $pattern);

    /**
     * @return IUrlMatcher
     */
    function getUrlMatcher();

    /**
     * @param IUrlMatcher $urlMatcher
     */
    function setUrlMatcher(IUrlMatcher $urlMatcher);

    /**
     * @return IFiltersMatcher
     */
    function getFiltersMatcher();

    /**
     * @param IFiltersMatcher $filtersMatcher
     */
    function setFiltersMatcher(IFiltersMatcher $filtersMatcher);

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

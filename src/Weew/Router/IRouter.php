<?php

namespace Weew\Router;

use Weew\Url\IUrl;

interface IRouter {
    /**
     * @param $path
     * @param $abstract
     *
     * @return IRouter
     */
    function get($path, $abstract);

    /**
     * @param $path
     * @param $abstract
     *
     * @return IRouter
     */
    function post($path, $abstract);

    /**
     * @param $path
     * @param $abstract
     *
     * @return IRouter
     */
    function put($path, $abstract);

    /**
     * @param $path
     * @param $abstract
     *
     * @return IRouter
     */
    function update($path, $abstract);

    /**
     * @param $path
     * @param $abstract
     *
     * @return IRouter
     */
    function patch($path, $abstract);

    /**
     * @param $path
     * @param $abstract
     *
     * @return IRouter
     */
    function delete($path, $abstract);

    /**
     * @param $path
     * @param $abstract
     *
     * @return IRouter
     */
    function options($path, $abstract);

    /**
     * @param callable $callback
     *
     * @return IRouter
     */
    function group(callable $callback);

    /**
     * @return IRouter
     */
    function createNestedRouter();

    /**
     * @param $name
     * @param $pattern
     *
     * @return IRouter
     */
    function addPattern($name, $pattern);

    /**
     * @param $name
     * @param callable $callable
     *
     * @return IRouter
     */
    function addFilter($name, callable $callable);

    /**
     * @param $name
     *
     * @return IRouter
     */
    function enableFilter($name);

    /**
     * @param $name
     * @param callable $resolver
     *
     * @return IRouter
     */
    function addResolver($name, callable $resolver);

    /**
     * @param $path
     *
     * @return IRouter
     */
    function setBasePath($path);

    /**
     * @param $protocol
     *
     * @return IRouter
     */
    function restrictProtocol($protocol);

    /**
     * @param $tld
     *
     * @return IRouter
     */
    function restrictTLD($tld);

    /**
     * @param $domain
     *
     * @return IRouter
     */
    function restrictDomain($domain);

    /**
     * @param $subdomain
     *
     * @return IRouter
     */
    function restrictSubdomain($subdomain);

    /**
     * @param $host
     *
     * @return IRouter
     */
    function restrictHost($host);

    /**
     * @param $method
     * @param IUrl $url
     *
     * @return null|IRoute
     * @see HttpRequestMethod
     */
    function match($method, IUrl $url);

    /**
     * @return IRoute[]
     */
    function getRoutes();

    /**
     * @param IRoute[] $routes
     */
    function setRoutes(array $routes);

    /**
     * @return IRoutesMatcher
     */
    function getRoutesMatcher();

    /**
     * @param IRoutesMatcher $matcher
     */
    function setRoutesMatcher(IRoutesMatcher $matcher);

    /**
     * @return ICallableInvoker
     */
    function getCallableInvoker();

    /**
     * @param ICallableInvoker $callableInvoker
     */
    function setCallableInvoker(ICallableInvoker $callableInvoker);
}

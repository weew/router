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
     * @param $method
     * @param $url
     *
     * @return IRoute|null
     *
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
}

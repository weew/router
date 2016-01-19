<?php

namespace Weew\Router;

use Weew\Contracts\IArrayable;

interface IRoute extends IArrayable {
    /**
     * @return string
     */
    function getMethod();

    /**
     * @param $method
     *
     * @see HttpRequestMethod
     */
    function setMethod($method);

    /**
     * @return string
     */
    function getPath();

    /**
     * @param $path
     */
    function setPath($path);

    /**
     * @return mixed
     */
    function getHandler();

    /**
     * @param $handler
     */
    function setHandler($handler);

    /**
     * @return array
     */
    function getParameters();

    /**
     * @param array $parameters
     */
    function setParameters(array $parameters);

    /**
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    function getParameter($key, $default = null);

    /**
     * @param $key
     * @param $value
     */
    function setParameter($key, $value);
}

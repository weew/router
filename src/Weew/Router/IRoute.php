<?php

namespace Weew\Router;

use Weew\Contracts\IArrayable;

interface IRoute extends IArrayable {
    /**
     * @return array
     */
    function getMethods();

    /**
     * @param array $methods
     *
     * @see HttpRequestMethod
     */
    function setMethods(array $methods);

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

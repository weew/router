<?php

namespace Weew\Router;

use Weew\Foundation\Interfaces\IArrayable;

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
    function getValue();

    /**
     * @param $value
     */
    function setValue($value);

    /**
     * @return array
     */
    function getParameters();

    /**
     * @param array $parameters
     */
    function setParameters(array $parameters);
}

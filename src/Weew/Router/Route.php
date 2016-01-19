<?php

namespace Weew\Router;

use Exception;
use Weew\Http\HttpRequestMethod;

class Route implements IRoute {
    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var mixed
     */
    protected $handler;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @param $method
     * @param $path
     * @param $handler
     *
     * @throws Exception
     *
     * @see HttpRequestMethod
     */
    public function __construct($method, $path, $handler) {
        $this->setMethod($method);
        $this->setPath($path);
        $this->setHandler($handler);
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param $method
     *
     * @throws Exception
     * @see HttpRequestMethod
     */
    public function setMethod($method) {
        if ( ! HttpRequestMethod::isValid($method)) {
            throw new Exception(
                s(
                    'Invalid request method %s. Valid methods are %s.',
                    $method, implode(', ', HttpRequestMethod::getMethods())
                )
            );
        }

        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param $path
     */
    public function setPath($path) {
        if ( ! str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getHandler() {
        return $this->handler;
    }

    /**
     * @param $handler
     */
    public function setHandler($handler) {
        $this->handler = $handler;
    }

    /**
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public function getParameter($key, $default = null) {
        $parameter = array_get($this->parameters, $key, $default);

        if ($parameter === null) {
            return $default;
        }

        return $parameter;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setParameter($key, $value) {
        array_set($this->parameters, $key, $value);
    }

    /**
     * @return array
     */
    public function toArray() {
        return [
            'method' => $this->getMethod(),
            'path' => $this->getPath(),
            'handler' => $this->getHandler(),
            'parameters' => $this->parameters,
        ];
    }
}

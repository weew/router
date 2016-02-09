<?php

namespace Weew\Router;

use Exception;
use Weew\Http\HttpRequestMethod;

class Route implements IRoute {
    /**
     * @var array
     */
    protected $methods;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var mixed
     */
    protected $action;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * Route constructor.
     *
     * @param array $methods
     * @param $path
     * @param $handler
     *
     * @see HttpRequestMethod
     */
    public function __construct(array $methods, $path, $handler) {
        $this->setMethods($methods);
        $this->setPath($path);
        $this->setAction($handler);
    }

    /**
     * @return array
     */
    public function getMethods() {
        return $this->methods;
    }

    /**
     * @param array $methods
     *
     * @throws Exception
     * @see HttpRequestMethod
     */
    public function setMethods(array $methods) {
        foreach ($methods as $method) {
            if ( ! HttpRequestMethod::isValid($method)) {
                throw new Exception(
                    s(
                        'Invalid request method %s. Valid methods are %s.',
                        $method, implode(', ', HttpRequestMethod::getMethods())
                    )
                );
            }
        }

        $this->methods = $methods;
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
    public function getAction() {
        return $this->action;
    }

    /**
     * @param $action
     */
    public function setAction($action) {
        $this->action = $action;
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
            'methods' => $this->getMethods(),
            'path' => $this->getPath(),
            'handler' => $this->getAction(),
            'parameters' => $this->parameters,
        ];
    }
}

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
    protected $value;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param $method
     * @param $path
     * @param $value
     *
     * @throws Exception
     *
     * @see HttpRequestMethod
     */
    public function __construct($method, $path, $value) {
        $this->parameters = [];

        $this->setMethod($method);
        $this->setPath($path);
        $this->setValue($value);
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
    public function getValue() {
        return $this->value;
    }

    /**
     * @param $value
     */
    public function setValue($value) {
        $this->value = $value;
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
     * @return array
     */
    public function toArray() {
        return [
            'method' => $this->getMethod(),
            'path' => $this->getPath(),
            'value' => $this->getValue(),
            'parameters' => $this->parameters,
        ];
    }
}

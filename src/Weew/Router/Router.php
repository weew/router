<?php

namespace Weew\Router;

use Exception;
use Weew\Http\HttpRequestMethod;
use Weew\Router\Exceptions\FilterException;
use Weew\Url\IUrl;

class Router implements IRouter {
    /**
     * @var Router[]
     */
    protected $nestedRouters = [];

    /**
     * @var IRoute[]
     */
    protected $routes = [];

    /**
     * @var IRoutesMatcher
     */
    protected $matcher;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var ICallableInvoker
     */
    protected $callableInvoker;

    /**
     * @param IRoutesMatcher $matcher
     * @param ICallableInvoker $callableInvoker
     */
    public function __construct(
        IRoutesMatcher $matcher = null,
        ICallableInvoker $callableInvoker = null
    ) {
        if ( ! $matcher instanceof IRoutesMatcher) {
            $matcher = $this->createRoutesMatcher();
        }

        if ( ! $callableInvoker instanceof ICallableInvoker) {
            $callableInvoker = $this->createCallableInvoker();
        }

        $this->setRoutesMatcher($matcher);
        $this->setCallableInvoker($callableInvoker);
    }

    /**
     * @param $method
     * @param IUrl $url
     *
     * @return null|IRoute
     */
    public function match($method, IUrl $url) {
        $exceptions = [];
        $route = $this->matchRouter($method, $url, $exceptions);

        if ($route === null && count($exceptions) > 0) {
            $exception = array_pop($exceptions);
            throw $exception->getOriginalException();
        }

        return $route;
    }

    /**
     * @return string
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * @param $class
     * @param bool $nest
     *
     * @return IRouter
     */
    public function setController($class, $nest = true) {
        if ($nest) {
            $router = $this->createNestedRouter();
            $router->setController($class, false);

            return $router;
        }

        $this->controller = $class;

        return $this;
    }

    /**
     * @return IRouter
     */
    public function removeController() {
        $this->controller = null;

        return $this;
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function get($path, $abstract) {
        return $this->route(HttpRequestMethod::GET, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function post($path, $abstract) {
        return $this->route(HttpRequestMethod::POST, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function put($path, $abstract) {
        return $this->route(HttpRequestMethod::PUT, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function update($path, $abstract) {
        return $this->route(HttpRequestMethod::UPDATE, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function patch($path, $abstract) {
        return $this->route(HttpRequestMethod::PATCH, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function delete($path, $abstract) {
        return $this->route(HttpRequestMethod::DELETE, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function options($path, $abstract) {
        return $this->route(HttpRequestMethod::OPTIONS, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return Router
     */
    public function head($path, $abstract) {
        return $this->route(HttpRequestMethod::HEAD, $path, $abstract);
    }

    /**
     * @param $method
     * @param $path
     * @param $abstract
     *
     * @return Router
     */
    public function route($method, $path, $abstract) {
        return $this->createRoute($method, $path, $abstract);
    }

    /**
     * @param callable $callable
     *
     * @return IRouter
     */
    public function group(callable $callable = null) {
        $router = $this->createNestedRouter();

        if ($callable !== null) {
            $this->invokeCallable($callable, $router);
        }

        return $router;
    }

    /**
     * @param $name
     * @param $pattern
     *
     * @return $this
     */
    public function addPattern($name, $pattern) {
        $this->getRoutesMatcher()->addPattern($name, $pattern);

        return $this;
    }

    /**
     * @param $name
     * @param $callable
     *
     * @return $this
     */
    public function addFilter($name, $callable) {
        $filter = new RouteFilter($name, $callable);
        $this->getRoutesMatcher()->getFiltersMatcher()
            ->addFilter($filter);

        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function enableFilter($name) {
        if ( ! is_array($name)) {
            $name = [$name];
        }

        $this->getRoutesMatcher()->getFiltersMatcher()
            ->enableFilters($name);

        return $this;
    }

    /**
     * @param $name
     * @param callable $callable
     *
     * @return $this
     */
    public function addResolver($name, $callable) {
        $resolver = new RouteResolver($name, $callable);
        $this->getRoutesMatcher()->getParameterResolver()
            ->addResolver($resolver);

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix() {
        return $this->prefix;
    }

    /**
     * @param $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix) {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @param $prefix
     *
     * @return $this
     */
    public function addPrefix($prefix) {
        if ($this->getPrefix() !== null) {
            $this->setPrefix(url($this->getPrefix(), $prefix));
        } else {
            $this->setPrefix($prefix);
        }

        return $this;
    }

    /**
     * @param $protocol
     *
     * @return $this
     */
    public function restrictProtocol($protocol) {
        if ( ! is_array($protocol)) {
            $protocol = [$protocol];
        }

        $this->getRoutesMatcher()->getRestrictionsMatcher()
            ->setProtocols($protocol);

        return $this;
    }

    /**
     * @param $tld
     *
     * @return $this
     */
    public function restrictTLD($tld) {
        if ( ! is_array($tld)) {
            $tld = [$tld];
        }

        $this->getRoutesMatcher()->getRestrictionsMatcher()
            ->setTLDs($tld);

        return $this;
    }

    /**
     * @param $domain
     *
     * @return $this
     */
    public function restrictDomain($domain) {
        if ( ! is_array($domain)) {
            $domain = [$domain];
        }

        $this->getRoutesMatcher()->getRestrictionsMatcher()
            ->setDomains($domain);

        return $this;
    }

    /**
     * @param $subdomain
     *
     * @return $this
     */
    public function restrictSubdomain($subdomain) {
        if ( ! is_array($subdomain)) {
            $subdomain = [$subdomain];
        }

        $this->getRoutesMatcher()->getRestrictionsMatcher()
            ->setSubdomains($subdomain);

        return $this;
    }

    /**
     * @param $host
     *
     * @return $this
     */
    public function restrictHost($host) {
        if ( ! is_array($host)) {
            $host = [$host];
        }

        $this->getRoutesMatcher()->getRestrictionsMatcher()
            ->setHosts($host);

        return $this;
    }

    /**
     * @return IRoute[]
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * @param IRoute[] $routes
     */
    public function setRoutes(array $routes) {
        $this->routes = $routes;
    }

    /**
     * @return IRoutesMatcher
     */
    public function getRoutesMatcher() {
        return $this->matcher;
    }

    /**
     * @param IRoutesMatcher $matcher
     */
    public function setRoutesMatcher(IRoutesMatcher $matcher) {
        $this->matcher = $matcher;
    }

    /**
     * @return Router
     */
    public function createNestedRouter() {
        $router = $this->createRouter();
        $router->setPrefix($this->getPrefix());
        $router->setController($this->controller, false);
        $router->setRoutesMatcher(clone $router->getRoutesMatcher());
        $this->addNestedRouter($router);

        return $router;
    }

    /**
     * @return ICallableInvoker
     */
    public function getCallableInvoker() {
        return $this->callableInvoker;
    }

    /**
     * @param ICallableInvoker $callableInvoker
     */
    public function setCallableInvoker(ICallableInvoker $callableInvoker) {
        $this->callableInvoker = $callableInvoker;
    }

    /**
     * @param $method
     * @param IUrl $url
     * @param array $exceptions
     * @param IRoute|null $route
     *
     * @return null|IRoute
     */
    public function matchRouter(
        $method,
        IUrl $url,
        array &$exceptions,
        IRoute $route = null
    ) {
        try {
            $route = $this->getRoutesMatcher()
                ->match($this->getRoutes(), $method, $url);
        } catch (FilterException $ex) {
            $exceptions[] = $ex;
        }

        if ($route === null) {
            $route = $this->matchNestedRouters(
                $method, $url, $exceptions, $route
            );
        }

        return $route;
    }

    /**
     * @param $method
     * @param IUrl $url
     * @param array $exceptions
     * @param IRoute|null $route
     *
     * @return null|IRoute
     */
    protected function matchNestedRouters(
        $method,
        IUrl $url,
        array &$exceptions,
        IRoute $route = null
    ) {
        foreach ($this->nestedRouters as $router) {
            $match = $router->matchRouter($method, $url, $exceptions, $route);

            if ($match !== null) {
                $route = $match;
                break;
            }
        }

        return $route;
    }

    /**
     * @return RoutesMatcher
     */
    protected function createRoutesMatcher() {
        return new RoutesMatcher();
    }

    /**
     * @param $method
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    protected function createRoute($method, $path, $abstract) {
        if ($this->getPrefix() !== null) {
            $path = url($this->getPrefix(), $path);
        }

        if ( ! is_array($method)) {
            $method = [$method];
        }

        $handler = $this->createHandler($abstract);
        $route = new Route($method, $path, $handler);
        $this->routes[] = $route;

        return $this;
    }

    /**
     * @param $abstract
     *
     * @return array
     */
    protected function createHandler($abstract) {
        $controller = $this->getController();

        if ($controller !== null &&
            is_string($abstract)) {
            $abstract = [$controller, $abstract];
        }

        return $abstract;
    }

    /**
     * @return Router
     */
    protected function createRouter() {
        return new Router($this->getRoutesMatcher());
    }

    /**
     * @param IRouter $router
     */
    protected function addNestedRouter(IRouter $router) {
        $this->nestedRouters[] = $router;
    }

    /**
     * @param callable $callable
     * @param IRouter $router
     */
    protected function invokeCallable(callable $callable, IRouter $router) {
        $this->getCallableInvoker()->invoke($callable, $router);
    }

    /**
     * @return CallableInvoker
     */
    protected function createCallableInvoker() {
        return new CallableInvoker();
    }
}

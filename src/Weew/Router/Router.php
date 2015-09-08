<?php

namespace Weew\Router;

use Weew\Http\HttpRequestMethod;
use Weew\Url\IUrl;

class Router implements IRouter {
    /**
     * @var IRouter[]
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
    protected $basePath;

    /**
     * @param IRoutesMatcher $matcher
     */
    public function __construct(
        IRoutesMatcher $matcher = null
    ) {
        if ( ! $matcher instanceof IRoutesMatcher) {
            $matcher = $this->createRoutesMatcher();
        }

        $this->setRoutesMatcher($matcher);
    }

    /**
     * @param $method
     * @param IUrl $url
     *
     * @return IRoute|null
     *
     * @see HttpRequestMethod
     */
    public function match($method, IUrl $url) {
        $matcher = $this->getRoutesMatcher();
        $route = $matcher->match($this->getRoutes(), $method, $url);

        if ($route !== null) {
            return $route;
        } else {
            foreach ($this->nestedRouters as $router) {
                $route = $router->match($method, $url);

                if ($route !== null) {
                    return $route;
                }
            }
        }

        return null;
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function get($path, $abstract) {
        return $this->createRoute(HttpRequestMethod::GET, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function post($path, $abstract) {
        return $this->createRoute(HttpRequestMethod::POST, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function put($path, $abstract) {
        return $this->createRoute(HttpRequestMethod::PUT, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function update($path, $abstract) {
        return $this->createRoute(HttpRequestMethod::UPDATE, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function patch($path, $abstract) {
        return $this->createRoute(HttpRequestMethod::PATCH, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function delete($path, $abstract) {
        return $this->createRoute(HttpRequestMethod::DELETE, $path, $abstract);
    }

    /**
     * @param $path
     * @param $abstract
     *
     * @return $this
     * @throws Exception
     */
    public function options($path, $abstract) {
        return $this->createRoute(HttpRequestMethod::OPTIONS, $path, $abstract);
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function group(callable $callback) {
        $router = $this->createNestedRouter();
        $this->addNestedRouter($router);

        $this->invokeCallable($callback, $router);

        return $this;
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

    public function addFilter($name, callable $callable) {

    }

    /**
     * @param $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath) {
        $this->basePath = $basePath;

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

        $this->getRoutesMatcher()->setProtocols($protocol);

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

        $this->getRoutesMatcher()->setTLDs($tld);

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

        $this->getRoutesMatcher()->setDomains($domain);

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

        $this->getRoutesMatcher()->setSubdomains($subdomain);

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

        $this->getRoutesMatcher()->setHosts($host);

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
        if ($this->basePath !== null) {
            $path = url($this->basePath, $path);
        }

        $route = new Route($method, $path, $abstract);

        $this->routes[] = $route;

        return $this;
    }

    /**
     * @return Router
     */
    protected function createRouter() {
        return new Router($this->getRoutesMatcher());
    }

    /**
     * @return Router
     */
    protected function createNestedRouter() {
        $router = $this->createRouter();
        $router->setBasePath($this->basePath);
        $router->setRoutesMatcher(clone $router->getRoutesMatcher());

        return $router;
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
     *
     * @return mixed
     */
    protected function invokeCallable(callable $callable, IRouter $router) {
        return $callable($router);
    }
}

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
    protected $routes;

    /**
     * @var IRoutesMatcher
     */
    protected $matcher;

    /**
     * @param IRoute[] $routes
     * @param IRoutesMatcher $matcher
     */
    public function __construct(
        array $routes = [],
        IRoutesMatcher $matcher = null
    ) {
        if ( ! $matcher instanceof IRoutesMatcher) {
            $matcher = $this->createRoutesMatcher();
        }

        $this->setRoutes($routes);
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
        $router = $this->createRouter();
        $this->addNestedRouter($router);

        $callback($router);

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
        $route = new Route($method, $path, $abstract);

        $this->routes[] = $route;

        return $this;
    }

    /**
     * @return Router
     */
    protected function createRouter() {
        return new Router([], $this->getRoutesMatcher());
    }

    /**
     * @param IRouter $router
     */
    protected function addNestedRouter(IRouter $router) {
        $this->nestedRouters[] = $router;
    }
}

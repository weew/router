<?php

namespace Weew\Router;

use Exception;
use Weew\Http\HttpRequestMethod;
use Weew\Router\Exceptions\FilterException;
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
     * @param $path
     * @param $abstract
     *
     * @return Router
     */
    public function head($path, $abstract) {
        return $this->createRoute(HttpRequestMethod::HEAD, $path, $abstract);
    }

    /**
     * @param callable $callable
     *
     * @return $this
     */
    public function group(callable $callable) {
        $router = $this->createNestedRouter();
        $this->invokeCallable($callable, $router);

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
     * @param $name
     * @param callable $callable
     *
     * @return $this
     */
    public function addFilter($name, callable $callable) {
        $this->getRoutesMatcher()->getFiltersMatcher()
            ->addFilter($name, $callable);

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
     * @param callable $resolver
     *
     * @return $this
     */
    public function addResolver($name, callable $resolver) {
        $this->getRoutesMatcher()->getParameterResolver()
            ->addResolver($name, $resolver);

        return $this;
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
        $router->setBasePath($this->basePath);
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
        if ($this->basePath !== null) {
            $path = url($this->basePath, $path);
        }

        $route = new Route($method, $path, $abstract);
        $this->routes[] = $route;

        if ($method == HttpRequestMethod::GET) {
            $this->createRoute(HttpRequestMethod::HEAD, $path, $abstract);
        }

        return $this;
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

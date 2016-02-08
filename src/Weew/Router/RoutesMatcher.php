<?php

namespace Weew\Router;

use Weew\Http\HttpRequestMethod;
use Weew\Url\IUrl;
use Weew\UrlMatcher\IUrlMatcher;
use Weew\UrlMatcher\UrlMatcher;

class RoutesMatcher implements IRoutesMatcher {
    /**
     * @var IUrlMatcher
     */
    protected $urlMatcher;

    /**
     * @var IFiltersMatcher
     */
    protected $filtersMatcher;

    /**
     * @var IRestrictionsMatcher
     */
    protected $restrictionsMatcher;

    /**
     * @var IParameterResolver
     */
    protected $parameterResolver;

    /**
     * @param IUrlMatcher $urlMatcher
     * @param IFiltersMatcher $filtersMatcher
     * @param IRestrictionsMatcher $restrictionsMatcher
     * @param IParameterResolver $parameterResolver
     */
    public function __construct(
        IUrlMatcher $urlMatcher = null,
        IFiltersMatcher $filtersMatcher = null,
        IRestrictionsMatcher $restrictionsMatcher = null,
        IParameterResolver $parameterResolver = null
    ) {
        if ( ! $urlMatcher instanceof IUrlMatcher) {
            $urlMatcher = $this->createUrlMatcher();
        }

        if ( ! $filtersMatcher instanceof IFiltersMatcher) {
            $filtersMatcher = $this->createFiltersMatcher();
        }

        if ( ! $restrictionsMatcher instanceof IRestrictionsMatcher) {
            $restrictionsMatcher = $this->createRestrictionsMatcher();
        }

        if ( ! $parameterResolver instanceof IParameterResolver) {
            $parameterResolver = $this->createParameterResolver();
        }

        $this->setUrlMatcher($urlMatcher);
        $this->setFiltersMatcher($filtersMatcher);
        $this->setRestrictionsMatcher($restrictionsMatcher);
        $this->setParameterResolver($parameterResolver);
    }

    /**
     * @param IRoute[] $routes
     * @param $method
     * @param IUrl $url
     *
     * @return IRoute|null
     *
     * @see HttpRequestMethod
     */
    public function match(array $routes, $method, IUrl $url) {
        $route = $this->matchRoute($routes, $method, $url);

        if ($route instanceof IRoute) {
            if ($this->getFiltersMatcher()->applyFilters($route)) {
                $allParametersResolved = $this->getParameterResolver()
                    ->resolveRouteParameters($route);

                if ($allParametersResolved) {
                    return $route;
                }
            }
        }

        if ($method === HttpRequestMethod::HEAD) {
            return $this->match($routes, HttpRequestMethod::GET, $url);
        }

        return null;
    }

    /**
     * @param string $name
     * @param string $pattern
     */
    public function addPattern($name, $pattern) {
        $this->getUrlMatcher()->addPattern($name, $pattern);
    }

    /**
     * @return IUrlMatcher
     */
    public function getUrlMatcher() {
        return $this->urlMatcher;
    }

    /**
     * @param IUrlMatcher $urlMatcher
     */
    public function setUrlMatcher(IUrlMatcher $urlMatcher) {
        $this->urlMatcher = $urlMatcher;
    }

    /**
     * @return IFiltersMatcher
     */
    public function getFiltersMatcher() {
        return $this->filtersMatcher;
    }

    /**
     * @param IFiltersMatcher $filtersMatcher
     */
    public function setFiltersMatcher(IFiltersMatcher $filtersMatcher) {
        $this->filtersMatcher = $filtersMatcher;
    }

    /**
     * @return IRestrictionsMatcher
     */
    public function getRestrictionsMatcher() {
        return $this->restrictionsMatcher;
    }

    /**
     * @param IRestrictionsMatcher $restrictionsMatcher
     */
    public function setRestrictionsMatcher(IRestrictionsMatcher $restrictionsMatcher) {
        $this->restrictionsMatcher = $restrictionsMatcher;
    }

    /**
     * @return IParameterResolver
     */
    public function getParameterResolver() {
        return $this->parameterResolver;
    }

    /**
     * @param IParameterResolver $parameterResolver
     */
    public function setParameterResolver(IParameterResolver $parameterResolver) {
        $this->parameterResolver = $parameterResolver;
    }

    /**
     * @param IRoute $route
     * @param $method
     *
     * @return bool
     */
    public function compareRouteToMethod(IRoute $route, $method) {
        return in_array($method, $route->getMethods());
    }

    /**
     * Custom clone method.
     */
    public function __clone() {
        $this->setUrlMatcher(
            clone $this->getUrlMatcher()
        );
        $this->setRestrictionsMatcher(
            clone $this->getRestrictionsMatcher()
        );
        $this->setFiltersMatcher(
            clone $this->getFiltersMatcher()
        );
        $this->setFiltersMatcher(
            clone $this->getFiltersMatcher()
        );
    }

    /**
     * @param IRoute[] $routes
     * @param $method
     * @param IUrl $url
     *
     * @return IRoute|null
     */
    protected function matchRoute(array $routes, $method, IUrl $url) {
        foreach ($routes as $route) {
            if ($this->compareRouteToMethod($route, $method) &&
                $this->getUrlMatcher()->match($route->getPath(), $url->getPath()) &&
                $this->getRestrictionsMatcher()->match($url)
            ) {
                $parameters = $this->getUrlMatcher()
                    ->parse($route->getPath(), $url->getPath());

                $route->setParameters($parameters->toArray());

                return $route;
            }
        }

        return null;
    }

    /**
     * @return RestrictionsMatcher
     */
    protected function createRestrictionsMatcher() {
        return new RestrictionsMatcher();
    }

    /**
     * @return ParameterResolver
     */
    protected function createParameterResolver() {
        return new ParameterResolver();
    }

    /**
     * @return FiltersMatcher
     */
    protected function createFiltersMatcher() {
        return new FiltersMatcher();
    }

    /**
     * @return UrlMatcher
     */
    protected function createUrlMatcher() {
        return new UrlMatcher();
    }
}

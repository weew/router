<?php

namespace Weew\Router;

use Weew\Router\Exceptions\FilterNotFoundException;
use Weew\Url\IUrl;

class RoutesMatcher implements IRoutesMatcher {
    /**
     * @var array
     */
    protected $patterns = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $parameterResolvers = [];

    /**
     * @var IFilterInvoker
     */
    protected $filterInvoker;

    /**
     * @var IRestrictionsMatcher
     */
    protected $restrictionsMatcher;

    /**
     * @var IParameterResolver
     */
    protected $parameterResolver;

    /**
     * @param IFilterInvoker|null $filterInvoker
     * @param IRestrictionsMatcher $restrictionsMatcher
     * @param IParameterResolver $parameterResolver
     */
    public function __construct(
        IFilterInvoker $filterInvoker = null,
        IRestrictionsMatcher $restrictionsMatcher = null,
        IParameterResolver $parameterResolver = null
    ) {
        if ( ! $filterInvoker instanceof IFilterInvoker) {
            $filterInvoker = $this->createFilterInvoker();
        }

        if ( ! $restrictionsMatcher instanceof IRestrictionsMatcher) {
            $restrictionsMatcher = $this->createRestrictionsMatcher();
        }

        if ( ! $parameterResolver instanceof IParameterResolver) {
            $parameterResolver = $this->createParameterResolver();
        }

        $this->setFilterInvoker($filterInvoker);
        $this->setRestrictionsMatcher($restrictionsMatcher);
        $this->setParameterResolver($parameterResolver);

        $this->addDefaultPatterns();
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
            if ($this->applyFilters()) {
                $this->getParameterResolver()
                    ->resolveRouteParameters($route);

                return $route;
            }
        }

        return null;
    }

    /**
     * @param IRoute $route
     * @param $method
     *
     * @return bool
     */
    public function compareRouteToMethod(IRoute $route, $method) {
        return $route->getMethod() == $method;
    }

    /**
     * @param IRoute $route
     * @param IUrl $url
     *
     * @return bool
     */
    public function compareRouteToUrl(IRoute $route, IUrl $url) {
        $path = $this->getUrlPath($url);
        $pattern = $this->createRegexPatternForRoutePath($route->getPath());
        $matches = [];

        if (preg_match_all($pattern, $path, $matches) === 1) {
            $matchedPath = $this->addTrailingSlash(array_get($matches, '0.0'));

            return $matchedPath == $path;
        };

        return false;
    }

    /**
     * @return array
     */
    public function getPatterns() {
        return $this->patterns;
    }

    /**
     * @param array $patterns
     */
    public function setPatterns(array $patterns) {
        $this->patterns = $patterns;
    }

    /**
     * @param string $name
     * @param string $pattern
     */
    public function addPattern($name, $pattern) {
        array_unshift(
            $this->patterns,
            [
                'name' => $name,
                'pattern' => $pattern,
                'regexName' => '#\{' . preg_quote($name) . '\?\}#',
                'regexPattern' => '(' . $pattern . ')?',
            ]
        );

        array_unshift(
            $this->patterns,
            [
                'name' => $name,
                'pattern' => $pattern,
                'regexName' => '#\{' . preg_quote($name) . '\}#',
                'regexPattern' => '(' . $pattern . ')',
            ]
        );
    }

    /**
     * @return array
     */
    public function getFilters() {
        return $this->filters;
    }

    /**
     * @param array $filters
     */
    public function setFilters(array $filters) {
        $this->filters = $filters;
    }

    /**
     * @param $name
     * @param callable $filter
     */
    public function addFilter($name, callable $filter) {
        $this->filters[$name] = [
            'filter' => $filter,
            'enabled' => false,
        ];
    }

    /**
     * @param array $names
     *
     * @throws FilterNotFoundException
     */
    public function enableFilters(array $names) {
        foreach ($names as $name) {
            $filter = array_get($this->filters, $name);

            if ($filter === null) {
                throw new FilterNotFoundException(
                    s('Filter with name %s not found.', $name)
                );
            }

            $filter['enabled'] = true;
            $this->filters[$name] = $filter;
        }
    }

    /**
     * @return IFilterInvoker
     */
    public function getFilterInvoker() {
        return $this->filterInvoker;
    }

    /**
     * @param IFilterInvoker $filterInvoker
     */
    public function setFilterInvoker(IFilterInvoker $filterInvoker) {
        $this->filterInvoker = $filterInvoker;
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
     * @param IUrl $url
     *
     * @return array
     */
    public function extractRouteParameters(IRoute $route, IUrl $url) {
        $names = $this->extractRouteParameterNames($route);
        $values = $this->extractRouteParameterValues($route, $url);
        $parameters = array_combine($names, array_pad($values, count($names), null));

        return $parameters;
    }

    /**
     * @param IRoute $route
     *
     * @return array
     */
    public function extractRouteParameterNames(IRoute $route) {
        $names = [];
        $matches = [];
        preg_match_all('#\{([a-zA-Z0-9?]+)\}#', $route->getPath(), $matches);

        foreach (array_get($matches, 1, []) as $name) {
            $names[] = str_replace('?', '', $name);
        }

        return $names;
    }

    /**
     * @param IRoute $route
     * @param IUrl $url
     *
     * @return array
     */
    public function extractRouteParameterValues(IRoute $route, IUrl $url) {
        $path = $this->getUrlPath($url);
        $matches = [];

        $pattern = $this->createRegexPatternForRoutePath($route->getPath());
        preg_match_all($pattern, $path, $matches);
        array_shift($matches);

        $values = $this->processRouteParameterValues($matches);

        return $values;
    }

    public function __clone() {
        $this->setRestrictionsMatcher(
            clone $this->getRestrictionsMatcher()
        );
    }

    /**
     * @param array $routes
     * @param $method
     * @param IUrl $url
     *
     * @return IRoute|null
     */
    protected function matchRoute(array $routes, $method, IUrl $url) {
        foreach ($routes as $route) {
            if ($this->compareRouteToMethod($route, $method) &&
                $this->compareRouteToUrl($route, $url) &&
                $this->getRestrictionsMatcher()->matchRestrictions($url)
            ) {
                $route->setParameters(
                    $this->extractRouteParameters($route, $url)
                );

                return $route;
            }
        }

        return null;
    }

    protected function addDefaultPatterns() {
        $this->addPattern('any', '.+');
    }

    /**
     * @param $routePath
     *
     * @return string
     */
    protected function createRegexPatternForRoutePath($routePath) {
        $pattern = $this->applyCustomRegexPatternsToRoutePath($routePath);
        $pattern = $this->applyStandardRegexPatternsToRoutePath($pattern);
        $pattern = '#' . $pattern . '#';

        return $pattern;
    }

    /**
     * @param IUrl $url
     *
     * @return string
     */
    protected function getUrlPath(IUrl $url) {
        return $this->addTrailingSlash($url->getPath());
    }

    /**
     * @param $string
     *
     * @return string
     */
    protected function addTrailingSlash($string) {
        if ( ! str_ends_with($string, '/')) {
            $string .= '/';
        }

        return $string;
    }

    /**
     * @param $string
     *
     * @return string
     */
    protected function removeTrailingSlash($string) {
        if (str_ends_with($string, '/')) {
            $string = substr($string, 0, -1);
        }

        return $string;
    }

    /**
     * @param $routePath
     *
     * @return string
     */
    protected function applyStandardRegexPatternsToRoutePath($routePath) {
        $pattern = preg_replace('#\{([a-zA-Z0-9_-]+)\?\}#', '([^/]+)?', $routePath);
        $pattern = preg_replace('#\{([a-zA-Z0-9_-]+)\}#', '([^/]+)', $pattern);

        return $pattern;
    }

    /**
     * @param $routePath
     *
     * @return string
     */
    protected function applyCustomRegexPatternsToRoutePath($routePath) {
        foreach ($this->patterns as $pattern) {
            $routePath = preg_replace([$pattern['regexName']], $pattern['regexPattern'], $routePath);
        }

        return $routePath;
    }

    /**
     * @param array $matches
     *
     * @return array
     */
    protected function processRouteParameterValues(array $matches) {
        $values = [];

        foreach ($matches as $group) {
            if (is_array($group)) {
                foreach ($group as $value) {
                    if ($value == '') {
                        $value = null;
                    } else {
                        $value = $this->removeTrailingSlash($value);
                    }

                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    /**
     * @return bool
     */
    protected function applyFilters() {
        foreach ($this->filters as $filter) {
            if ($filter['enabled']) {
                $invoker = $this->getFilterInvoker();
                $result = $invoker->invoke($filter['filter']);

                if ($result === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return IFilterInvoker
     */
    protected function createFilterInvoker() {
        return new FilterInvoker();
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
}

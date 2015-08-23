<?php

namespace Weew\Router;

use Weew\Url\IUrl;

class RoutesMatcher implements IRoutesMatcher {
    /**
     * @var array
     */
    protected $patterns = [];

    public function __construct() {
        $this->addPattern('any', '.+');
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
        foreach ($routes as $route) {
            if ( ! $this->compareRouteToMethod($route, $method) ||
                ! $this->compareRouteToUrl($route, $url)
            ) {
                continue;
            }

            $parameters = $this->extractRouteParameters($route, $url);
            $route->setParameters($parameters);

            return $route;
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

    /**
     * @return array
     */
    public function getPatterns() {
        return $this->patterns;
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
     * @param $routePath
     *
     * @return string
     */
    protected function createRegexPatternForRoutePath($routePath) {
        $routePath = $this->applyCustomRegexPatternsToRoutePath($routePath);
        $pattern = preg_replace('#\{([a-zA-Z0-9]+)\?\}#', '([^/]+)?', $routePath);
        $pattern = preg_replace('#\{([a-zA-Z0-9]+)\}#', '([^/]+)', $pattern);
        $pattern = $this->applyCustomRegexPatternsToRoutePath($routePath);
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
}

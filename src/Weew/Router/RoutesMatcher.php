<?php

namespace Weew\Router;

use Weew\Url\IUrl;

class RoutesMatcher implements IRoutesMatcher {
    /**
     * @var array
     */
    protected $protocols = [];

    /**
     * @var array
     */
    protected $tlds = [];

    /**
     * @var array
     */
    protected $domains = [];

    /**
     * @var array
     */
    protected $subdomains = [];

    /**
     * @var array
     */
    protected $hosts = [];

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
            if ( ! $this->compareUrlToProtocols($url, $this->getProtocols()) ||
                ! $this->compareUrlToHosts($url, $this->getHosts()) ||
                ! $this->compareUrlToTLDs($url, $this->getTlds()) ||
                ! $this->compareUrlToDomains($url, $this->getDomains()) ||
                ! $this->compareUrlToSubdomains($url, $this->getSubdomains()) ||
                ! $this->compareRouteToMethod($route, $method) ||
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
    public function getProtocols() {
        return $this->protocols;
    }

    /**
     * @param array $protocols
     */
    public function setProtocols(array $protocols) {
        $this->protocols = $protocols;
    }

    /**
     * @return array
     */
    public function getTLDs() {
        return $this->tlds;
    }

    /**
     * @param array $tlds
     */
    public function setTLDs(array $tlds) {
        $this->tlds = $tlds;
    }

    /**
     * @return array
     */
    public function getDomains() {
        return $this->domains;
    }

    /**
     * @param array $domains
     */
    public function setDomains(array $domains) {
        $this->domains = $domains;
    }

    /**
     * @return array
     */
    public function getSubdomains() {
        return $this->subdomains;
    }

    /**
     * @param array $subdomains
     */
    public function setSubdomains(array $subdomains) {
        $this->subdomains = $subdomains;
    }

    /**
     * @return array
     */
    public function getHosts() {
        return $this->hosts;
    }

    /**
     * @param array $hosts
     */
    public function setHosts(array $hosts) {
        $this->hosts = $hosts;
    }

    /**
     * @param IUrl $url
     * @param array $protocols
     *
     * @return bool
     */
    public function compareUrlToProtocols(IUrl $url, array $protocols) {
        if (count($protocols) == 0) {
            return true;
        }

        return in_array($url->getProtocol(), $protocols);
    }

    /**
     * @param IUrl $url
     * @param array $hosts
     *
     * @return bool
     */
    public function compareUrlToHosts(IUrl $url, array $hosts) {
        if (count($hosts) == 0) {
            return true;
        }

        return in_array($url->getHost(), $hosts);
    }

    /**
     * @param IUrl $url
     * @param array $tlds
     *
     * @return bool
     */
    public function compareUrlToTLDs(IUrl $url, array $tlds) {
        if (count($tlds) == 0) {
            return true;
        }

        return in_array($url->getTLD(), $tlds);
    }

    /**
     * @param IUrl $url
     * @param array $domains
     *
     * @return bool
     */
    public function compareUrlToDomains(IUrl $url, array $domains) {
        if (count($domains) == 0) {
            return true;
        }

        return in_array($url->getDomain(), $domains);
    }

    /**
     * @param IUrl $url
     * @param array $subdomains
     *
     * @return bool
     */
    public function compareUrlToSubdomains(IUrl $url, array $subdomains) {
        if (count($subdomains) == 0) {
            return true;
        }

        return in_array($url->getSubdomain(), $subdomains);
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
}

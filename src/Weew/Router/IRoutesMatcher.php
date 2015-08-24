<?php

namespace Weew\Router;

use Weew\Url\IUrl;

interface IRoutesMatcher {
    /**
     * @param IRoute[] $routes
     * @param $method
     * @param IUrl $url
     *
     * @return IRoute|null
     *
     * @see HttpRequestMethod
     */
    function match(array $routes, $method, IUrl $url);

    /**
     * @return array
     */
    function getPatterns();

    /**
     * @param array $patterns
     */
    function setPatterns(array $patterns);

    /**
     * @param string $name
     * @param string $pattern
     */
    function addPattern($name, $pattern);

    /**
     * @return array
     */
    function getProtocols();

    /**
     * @param array $protocols
     */
    function setProtocols(array $protocols);

    /**
     * @return array
     */
    function getTLDs();

    /**
     * @param array $tlds
     */
    function setTLDs(array $tlds);

    /**
     * @return array
     */
    function getDomains();

    /**
     * @param array $domains
     */
    function setDomains(array $domains);

    /**
     * @return array
     */
    function getSubdomains();

    /**
     * @param array $subdomains
     */
    function setSubdomains(array $subdomains);

    /**
     * @return array
     */
    function getHosts();

    /**
     * @param array $hosts
     */
    function setHosts(array $hosts);
}

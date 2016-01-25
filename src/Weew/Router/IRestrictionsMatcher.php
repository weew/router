<?php

namespace Weew\Router;

use Weew\Url\IUrl;

interface IRestrictionsMatcher {
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

    /**
     * @param IUrl $url
     *
     * @return bool
     */
    function match(IUrl $url);
}

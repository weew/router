<?php

namespace Weew\Router;

use Weew\Url\IUrl;

class RestrictionsMatcher implements IRestrictionsMatcher {
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
     * @param IUrl $url
     *
     * @return bool
     */
    public function matchRestrictions(IUrl $url) {
        if ( ! $this->compareUrlToProtocols($url, $this->getProtocols()) ||
            ! $this->compareUrlToHosts($url, $this->getHosts()) ||
            ! $this->compareUrlToTLDs($url, $this->getTlds()) ||
            ! $this->compareUrlToDomains($url, $this->getDomains()) ||
            ! $this->compareUrlToSubdomains($url, $this->getSubdomains())
        ) {
            return false;
        }

        return true;
    }
}

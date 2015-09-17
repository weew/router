<?php

namespace Tests\Weew\Router;

use PHPUnit_Framework_TestCase;
use Weew\Router\RestrictionsMatcher;
use Weew\Url\Url;

class RestrictionsMatcherTest extends PHPUnit_Framework_TestCase {
    public function test_compare_url_to_protocol() {
        $matcher = new RestrictionsMatcher();
        $url = new Url('https://foo.bar');
        $this->assertTrue($matcher->compareUrlToProtocols($url, []));
        $this->assertTrue($matcher->compareUrlToProtocols($url, ['https']));
        $this->assertFalse($matcher->compareUrlToProtocols($url, ['http']));
    }

    public function test_compare_url_to_tld() {
        $matcher = new RestrictionsMatcher();
        $url = new Url('https://foo.bar');
        $this->assertTrue($matcher->compareUrlToTLDs($url, []));
        $this->assertTrue($matcher->compareUrlToTLDs($url, ['bar']));
        $this->assertFalse($matcher->compareUrlToTLDs($url, ['foo']));
    }

    public function test_compare_url_to_domain() {
        $matcher = new RestrictionsMatcher();
        $url = new Url('https://foo.bar');
        $this->assertTrue($matcher->compareUrlToDomains($url, []));
        $this->assertTrue($matcher->compareUrlToDomains($url, ['foo']));
        $this->assertFalse($matcher->compareUrlToDomains($url, ['bar']));
    }

    public function test_compare_url_to_subdomain() {
        $matcher = new RestrictionsMatcher();
        $url = new Url('https://yolo.foo.bar');
        $this->assertTrue($matcher->compareUrlToSubdomains($url, []));
        $this->assertTrue($matcher->compareUrlToSubdomains($url, ['yolo']));
        $this->assertFalse($matcher->compareUrlToSubdomains($url, ['bar']));
    }

    public function test_compare_url_to_host() {
        $matcher = new RestrictionsMatcher();
        $url = new Url('https://yolo.foo.bar');
        $this->assertTrue($matcher->compareUrlToHosts($url, []));
        $this->assertTrue($matcher->compareUrlToHosts($url, ['yolo.foo.bar']));
        $this->assertFalse($matcher->compareUrlToHosts($url, ['foo.bar']));
    }

    public function test_get_and_set_restrictions() {
        $matcher = new RestrictionsMatcher();

        $this->assertEquals([], $matcher->getProtocols());
        $matcher->setProtocols(['https']);
        $this->assertEquals(['https'], $matcher->getProtocols());

        $this->assertEquals([], $matcher->getTLDs());
        $matcher->setTLDs(['com']);
        $this->assertEquals(['com'], $matcher->getTlds());

        $this->assertEquals([], $matcher->getDomains());
        $matcher->setDomains(['foo']);
        $this->assertEquals(['foo'], $matcher->getDomains());

        $this->assertEquals([], $matcher->getSubdomains());
        $matcher->setSubdomains(['bar']);
        $this->assertEquals(['bar'], $matcher->getSubdomains());

        $this->assertEquals([], $matcher->getHosts());
        $matcher->setHosts(['baz']);
        $this->assertEquals(['baz'], $matcher->getHosts());
    }
}

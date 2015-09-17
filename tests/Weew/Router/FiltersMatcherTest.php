<?php

namespace Tests\Weew\Router;

use PHPUnit_Framework_TestCase;
use Weew\Http\HttpRequestMethod;
use Weew\Router\Exceptions\FilterNotFoundException;
use Weew\Router\FiltersMatcher;
use Weew\Router\Route;
use Weew\Router\RoutesMatcher;
use Weew\Url\Url;

class FiltersMatcherTest extends PHPUnit_Framework_TestCase {
    public function test_get_and_set_filters() {
        $matcher = new FiltersMatcher();
        $this->assertEquals([], $matcher->getFilters());
        $filters = [];
        $matcher->setFilters($filters);
        $this->assertTrue($filters === $matcher->getFilters());
    }

    public function test_add_filter() {
        $matcher = new FiltersMatcher();
        $matcher->addFilter('foo', function() {return 1;});
        $filters = $matcher->getFilters();
        $this->assertEquals(1, count($filters));
        $this->assertNotNull($filters['foo']);
        $this->assertEquals(1, $filters['foo']['filter']());
        $this->assertFalse($filters['foo']['enabled']);
    }

    public function test_enable_filter() {
        $matcher = new FiltersMatcher();
        $matcher->addFilter('foo', function() {return 1;});
        $filters = $matcher->getFilters();
        $this->assertFalse($filters['foo']['enabled']);
        $matcher->enableFilters(['foo']);
        $filters = $matcher->getFilters();
        $this->assertTrue($filters['foo']['enabled']);
    }

    public function test_enable_invalid_filter() {
        $matcher = new FiltersMatcher();
        $this->setExpectedException(FilterNotFoundException::class);
        $matcher->enableFilters(['foo']);
    }

    public function test_filter_gets_invoked() {
        $routes = [
            new Route(HttpRequestMethod::GET, 'foo', 'value'),
        ];
        $matcher = new RoutesMatcher();
        $matcher->getFiltersMatcher()->addFilter('foo', function() {
            return true;
        });

        $route = $matcher->match($routes, HttpRequestMethod::GET, new Url('foo'));
        $this->assertNotNull($route);

        $matcher->getFiltersMatcher()->enableFilters(['foo']);
        $route = $matcher->match($routes, HttpRequestMethod::GET, new Url('foo'));
        $this->assertNotNull($route);

        $matcher->getFiltersMatcher()->addFilter('foo', function() {
            return false;
        });
        $route = $matcher->match($routes, HttpRequestMethod::GET, new Url('foo'));
        $this->assertNotNull($route);

        $matcher->getFiltersMatcher()->enableFilters(['foo']);
        $route = $matcher->match($routes, HttpRequestMethod::GET, new Url('foo'));
        $this->assertNull($route);
    }
}

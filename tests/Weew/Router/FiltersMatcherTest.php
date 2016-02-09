<?php

namespace Tests\Weew\Router;

use PHPUnit_Framework_TestCase;
use Weew\Http\HttpRequestMethod;
use Weew\Router\Exceptions\FilterNotFoundException;
use Weew\Router\FiltersMatcher;
use Weew\Router\IRoute;
use Weew\Router\IRouteFilter;
use Weew\Router\Route;
use Weew\Router\RouteFilter;
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
        $filter = new RouteFilter('foo', function() {return 1;});
        $matcher->addFilter($filter);
        $filters = $matcher->getFilters();
        $this->assertEquals(1, count($filters));
        $this->assertTrue($filters['foo'] instanceof IRouteFilter);
        $cb = $filters['foo']->getFilter();
        $this->assertEquals(1, $cb());
        $this->assertFalse($filters['foo']->isEnabled());
    }

    public function test_enable_filter() {
        $matcher = new FiltersMatcher();
        $filter = new RouteFilter('foo', function() {return 1;});
        $matcher->addFilter($filter);
        $filters = $matcher->getFilters();
        $this->assertFalse($filters['foo']->isEnabled());
        $matcher->enableFilters(['foo']);
        $filters = $matcher->getFilters();
        $this->assertTrue($filters['foo']->isEnabled());
    }

    public function test_enable_invalid_filter() {
        $matcher = new FiltersMatcher();
        $this->setExpectedException(FilterNotFoundException::class);
        $matcher->enableFilters(['foo']);
    }

    public function test_route_gets_passed_to_filter() {
        $matcher = new FiltersMatcher();
        $bar = 1;
        $filter = new RouteFilter('foo', function(IRoute $route) use (&$bar) {
            $bar += $route->getAction();
        });
        $matcher->addFilter($filter);
        $matcher->enableFilters(['foo']);
        $matcher->applyFilters(new Route([HttpRequestMethod::GET], '', '5'));
        $this->assertEquals(6, $bar);
    }

    public function test_filter_gets_invoked() {
        $routes = [
            new Route([HttpRequestMethod::GET], 'foo', 'handler'),
        ];
        $filter = new RouteFilter('foo', function() {
            return true;
        });
        $matcher = new RoutesMatcher();
        $matcher->getFiltersMatcher()->addFilter($filter);

        $route = $matcher->match($routes, HttpRequestMethod::GET, new Url('foo'));
        $this->assertNotNull($route);

        $matcher->getFiltersMatcher()->enableFilters(['foo']);
        $route = $matcher->match($routes, HttpRequestMethod::GET, new Url('foo'));
        $this->assertNotNull($route);

        $filter = new RouteFilter('foo', function() {
            return false;
        });
        $matcher->getFiltersMatcher()->addFilter($filter);
        $route = $matcher->match($routes, HttpRequestMethod::GET, new Url('foo'));
        $this->assertNotNull($route);

        $matcher->getFiltersMatcher()->enableFilters(['foo']);
        $route = $matcher->match($routes, HttpRequestMethod::GET, new Url('foo'));
        $this->assertNull($route);
    }

    public function test_set_filters() {
        $matcher = new FiltersMatcher();
        $filters = [
            'foo' => new RouteFilter('foo', function() {}),
            'bar' => new RouteFilter('bar', function() {}),
        ];
        $matcher->setFilters($filters);

        $this->assertTrue($matcher->getFilters() === $filters);
    }
}

<?php

namespace Tests\Weew\Router;

use PHPUnit_Framework_TestCase;
use Weew\Http\HttpRequestMethod;
use Weew\Router\Exceptions\FilterNotFoundException;
use Weew\Router\IRoute;
use Weew\Router\Route;
use Weew\Router\Router;
use Weew\Router\RoutesMatcher;
use Weew\Url\Url;

class RoutesMatcherTest extends PHPUnit_Framework_TestCase {
    public function routes_provider() {
        $router = new Router();
        $router->get('/shop/{name}', 'foo');
        $router->get('/category/{name}/item/{id}/{alias?}', 'foo');
        $router->post('/category/{name}/item/{id}', 'bar');
        $router->delete('/country/{country}/city/{city}/{any}', 'foo');
        $router->delete('country/{country}/city/{city}/{any?}', 'bar');
        $router->put('yolo/{what}/swag/{huh?}', 'bar');

        $routes = $router->getRoutes();

        return [
            [$routes, HttpRequestMethod::GET, new Url('http://foo.bar/category'), false, [], null],
            [$routes, HttpRequestMethod::GET, new Url('http://foo.bar/category/foo/item/bar'), true, ['name' => 'foo', 'id' => 'bar', 'alias' => null], 'foo'],
            [$routes, HttpRequestMethod::GET, new Url('http://foo.bar/category/foo/item/bar/swag'), true, ['name' => 'foo', 'id' => 'bar', 'alias' => 'swag'], 'foo'],
            [$routes, HttpRequestMethod::POST, new Url('http://foo.bar/category/foo/item/bar/swag'), false, [], null],
            [$routes, HttpRequestMethod::POST, new Url('http://foo.bar/category/foo/item/bar'), true, ['name' => 'foo', 'id' => 'bar'], 'bar'],
            [$routes, HttpRequestMethod::DELETE, new Url('http://foo.bar/country/foo/city/bar/yolo/swag'), true, ['country' => 'foo', 'city' => 'bar', 'any' => 'yolo/swag'], 'foo'],
            [$routes, HttpRequestMethod::DELETE, new Url('http://foo.bar/country/foo/city/bar'), true, ['country' => 'foo', 'city' => 'bar', 'any' => null], 'bar'],
            [$routes, HttpRequestMethod::PUT, new Url('yolo/foo/swag/bar'), true, ['what' => 'foo', 'huh' => 'bar'], 'bar'],
            [$routes, HttpRequestMethod::PUT, new Url('yolo/foo/swag'), true, ['what' => 'foo', 'huh' => null], 'bar'],
        ];
    }

    /**
     * @dataProvider routes_provider
     */
    public function test_match($routes, $method, $url, $match, $expectedParameters, $expectedValue) {
        $matcher = new RoutesMatcher();

        $route = $matcher->match($routes, $method, $url);

        if ($match) {
            $this->assertTrue($route instanceof IRoute);
            $this->assertEquals($expectedValue, $route->getHandler());
            $this->assertEquals($expectedParameters, $route->getParameters());
        } else {
            $this->assertNull($route);
        }
    }

    public function test_compare_route_to_method() {
        $route = new Route(HttpRequestMethod::GET, 'foo', 'bar');
        $matcher = new RoutesMatcher();
        $this->assertTrue(
            $matcher->compareRouteToMethod($route, HttpRequestMethod::GET)
        );
        $this->assertFalse(
            $matcher->compareRouteToMethod($route, HttpRequestMethod::POST)
        );
    }


    public function test_compare_route_to_url() {
        $matcher = new RoutesMatcher();
        $route = new Route(HttpRequestMethod::GET, 'yolo/{what}/swag/{huh?}', 'foo');

        $this->assertFalse(
            $matcher->compareRouteToUrl($route, new Url('yolos/foo/swag/bar'))
        );
        $this->assertTrue(
            $matcher->compareRouteToUrl($route, new Url('yolo/foo/swag/bar'))
        );
        $this->assertTrue(
            $matcher->compareRouteToUrl($route, new Url('yolo/foo/swag'))
        );
    }

    public function test_extract_route_parameter_names() {
        $matcher = new RoutesMatcher();
        $route = new Route(HttpRequestMethod::GET, 'yolo/{foo}/{bar}/{baz}/swag/{huh?}', 'foo');

        $this->assertEquals(
            ['foo', 'bar', 'baz', 'huh'],
            $matcher->extractRouteParameterNames($route)
        );

        $route = new Route(HttpRequestMethod::GET, 'foo/bar', 'foo');
        $this->assertEquals(
            [],
            $matcher->extractRouteParameterNames($route)
        );
    }

    public function test_extract_route_parameter_values() {
        $matcher = new RoutesMatcher();
        $route = new Route(HttpRequestMethod::GET, 'yolo/{foo}/{bar}/{baz}/swag/{huh?}', 'foo');

        $this->assertEquals(
            ['a', 'b', 'c', 'd'],
            $matcher->extractRouteParameterValues($route, new Url('yolo/a/b/c/swag/d'))
        );

        $this->assertEquals(
            ['a', 'b', 'c', null],
            $matcher->extractRouteParameterValues($route, new Url('yolo/a/b/c/swag'))
        );
    }

    public function test_extract_route_parameters() {
        $matcher = new RoutesMatcher();
        $route = new Route(HttpRequestMethod::GET, 'yolo/{foo}/{bar}/{baz}/swag/{huh?}', 'foo');

        $this->assertEquals(
            ['foo' => 'a', 'bar' => 'b', 'baz' => 'c', 'huh' => 'd'],
            $matcher->extractRouteParameters($route, new Url('yolo/a/b/c/swag/d'))
        );
        $this->assertEquals(
            ['foo' => 'a', 'bar' => 'b', 'baz' => 'c', 'huh' => null],
            $matcher->extractRouteParameters($route, new Url('yolo/a/b/c/swag'))
        );
    }

    public function test_get_patterns() {
        $matcher = new RoutesMatcher();
        $this->assertTrue(is_array($matcher->getPatterns()));
        $matcher->setPatterns(['foo']);
        $this->assertEquals(['foo'], $matcher->getPatterns());
    }

    public function test_add_pattern() {
        $routes = [
            new Route(HttpRequestMethod::GET, 'foo/{id}/{slug}', 'foo')
        ];
        $matcher = new RoutesMatcher();
        $this->assertNotNull(
            $matcher->match($routes, HttpRequestMethod::GET, new Url('foo/a/b_'))
        );
        $matcher->addPattern('id', '[0-9]+');
        $this->assertNull(
            $matcher->match($routes, HttpRequestMethod::GET, new Url('foo/a/b_'))
        );
        $this->assertNotNull(
            $matcher->match($routes, HttpRequestMethod::GET, new Url('foo/1/b_'))
        );
        $matcher->addPattern('slug', '[a-z-]+');
        $this->assertNull(
            $matcher->match($routes, HttpRequestMethod::GET, new Url('foo/1/b_'))
        );
        $this->assertNotNull(
            $matcher->match($routes, HttpRequestMethod::GET, new Url('foo/1/b-'))
        );
        $route = $matcher->match($routes, HttpRequestMethod::GET, new Url('foo/1/b-'));
        $this->assertEquals(
            ['id' => 1, 'slug' => 'b-'], $route->getParameters()
        );
    }

    public function test_match_advanced_routes() {
        $matcher = new RoutesMatcher();
        $url = new Url('http://foo.bar.baz/foo');
        $route = new Route(HttpRequestMethod::GET, '/foo', 'foo');

        $this->assertNotNull($matcher->match([$route], HttpRequestMethod::GET, $url));

        $matcher->getRestrictionsMatcher()->setProtocols(['https']);
        $this->assertNull($matcher->match([$route], HttpRequestMethod::GET, $url));
        $matcher->getRestrictionsMatcher()->setProtocols(['http']);
        $this->assertNotNull($matcher->match([$route], HttpRequestMethod::GET, $url));

        $matcher->getRestrictionsMatcher()->setTLDs(['bar']);
        $this->assertNull($matcher->match([$route], HttpRequestMethod::GET, $url));
        $matcher->getRestrictionsMatcher()->setTLDs(['baz']);
        $this->assertNotNull($matcher->match([$route], HttpRequestMethod::GET, $url));

        $matcher->getRestrictionsMatcher()->setDomains(['baz']);
        $this->assertNull($matcher->match([$route], HttpRequestMethod::GET, $url));
        $matcher->getRestrictionsMatcher()->setDomains(['bar']);
        $this->assertNotNull($matcher->match([$route], HttpRequestMethod::GET, $url));

        $matcher->getRestrictionsMatcher()->setSubdomains(['bar']);
        $this->assertNull($matcher->match([$route], HttpRequestMethod::GET, $url));
        $matcher->getRestrictionsMatcher()->setSubdomains(['foo']);
        $this->assertNotNull($matcher->match([$route], HttpRequestMethod::GET, $url));

        $matcher->getRestrictionsMatcher()->setHosts(['foo.bar']);
        $this->assertNull($matcher->match([$route], HttpRequestMethod::GET, $url));
        $matcher->getRestrictionsMatcher()->setHosts(['foo.bar.baz']);
        $this->assertNotNull($matcher->match([$route], HttpRequestMethod::GET, $url));
    }

    public function test_parameter_resolver_gets_invoked() {
        $routes = [
            new Route(HttpRequestMethod::GET, 'foo/{item}/{id}', 'handler'),
        ];
        $matcher = new RoutesMatcher();
        $matcher->getParameterResolver()->addResolver('item', function($parameter) {
            return $parameter + 1;
        });
        $route = $matcher->match($routes, HttpRequestMethod::GET, new Url('foo/10/20'));

        $this->assertNotNull($route);
        $this->assertEquals(11, $route->getParameter('item'));
        $this->assertEquals(20, $route->getParameter('id'));
    }
}

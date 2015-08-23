<?php

namespace Tests\Weew\Router;

use PHPUnit_Framework_TestCase;
use Weew\Http\HttpRequestMethod;
use Weew\Router\IRoute;
use Weew\Router\IRouter;
use Weew\Router\IRoutesMatcher;
use Weew\Router\Router;
use Weew\Router\RoutesMatcher;
use Weew\Url\Url;

class RouterTest extends PHPUnit_Framework_TestCase {
    public function test_get_and_set_routes() {
        $routes = [];
        $router = new Router($routes);
        $this->assertTrue($routes === $router->getRoutes());
        $routes = [];
        $router->setRoutes($routes);
        $this->assertTrue($routes === $router->getRoutes());

        $router = new Router();
        $this->assertTrue(is_array($router->getRoutes()));
    }

    public function test_get_and_set_routes_matcher() {
        $matcher = new RoutesMatcher();
        $router = new Router([], $matcher);
        $this->assertTrue($matcher === $router->getRoutesMatcher());
        $matcher = new RoutesMatcher();
        $router->setRoutesMatcher($matcher);
        $this->assertTrue($matcher === $router->getRoutesMatcher());

        $router = new Router();
        $this->assertTrue($router->getRoutesMatcher() instanceof IRoutesMatcher);
    }

    public function test_route_methods() {
        $router = new Router();
        $router->get('get', '_get');
        $router->post('post', '_post');
        $router->put('put', '_put');
        $router->update('update', '_update');
        $router->patch('patch', '_patch');
        $router->delete('delete', '_delete');
        $router->options('options', '_options');

        $routes = $router->getRoutes();
        $this->assertEquals('_get', $routes[0]->getValue());
        $this->assertEquals('_post', $routes[1]->getValue());
        $this->assertEquals('_put', $routes[2]->getValue());
        $this->assertEquals('_update', $routes['3']->getValue());
        $this->assertEquals('_patch', $routes['4']->getValue());
        $this->assertEquals('_delete', $routes[5]->getValue());
        $this->assertEquals('_options', $routes[6]->getValue());
    }

    public function test_match() {
        $router = new Router();
        $router->get('home', 'home');
        $router->group(function(IRouter $route) {
            $route->get('items/{id}', 'id');
        });
        $router->group(function(IRouter $router) {
            $router->get('items/{id}/slug/{alias?}', 'slug');
        });

        $this->assertNull($router->match(HttpRequestMethod::POST, new Url('home')));
        $route = $router->match(HttpRequestMethod::GET, new Url('home'));
        $this->assertTrue($route instanceof IRoute);
        $this->assertEquals('home', $route->getValue());
        $this->assertEquals([], $route->getParameters());

        $this->assertNull($router->match(HttpRequestMethod::GET, new Url('items')));
        $route = $router->match(HttpRequestMethod::GET, new Url('items/foo'));
        $this->assertTrue($route instanceof IRoute);
        $this->assertEquals('id', $route->getValue());
        $this->assertEquals(['id' => 'foo'], $route->getParameters());

        $route = $router->match(HttpRequestMethod::GET, new Url('items/foo/slug'));
        $this->assertTrue($route instanceof IRoute);
        $this->assertEquals('slug', $route->getValue());
        $this->assertEquals(['id' => 'foo', 'alias' => null], $route->getParameters());

        $route = $router->match(HttpRequestMethod::GET, new Url('items/foo/slug/bar'));
        $this->assertTrue($route instanceof IRoute);
        $this->assertEquals('slug', $route->getValue());
        $this->assertEquals(['id' => 'foo', 'alias' => 'bar'], $route->getParameters());
    public function test_add_pattern() {
        $router = new Router();
        $router->addPattern('id', '[a-zA-Z]+');

        $router->get('items/{id}', 'id');
        $router->group(function(IRouter $router) {
            $router->get('items/{id}/slug', 'slug');
        });

        $this->assertNotNull(
            $router->match(HttpRequestMethod::GET, new Url('items/foo'))
        );
        $this->assertNotNull(
            $router->match(HttpRequestMethod::GET, new Url('items/foo/slug'))
        );
        $this->assertNull(
            $router->match(HttpRequestMethod::GET, new Url('items/1'))
        );
        $this->assertNull(
            $router->match(HttpRequestMethod::GET, new Url('items/1/slug'))
        );

        $router->addPattern('id', '[0-9]+');

        $this->assertNotNull(
            $router->match(HttpRequestMethod::GET, new Url('items/1'))
        );
        $this->assertNotNull(
            $router->match(HttpRequestMethod::GET, new Url('items/1/slug'))
        );
        $this->assertNull(
            $router->match(HttpRequestMethod::GET, new Url('items/foo'))
        );
        $this->assertNull(
            $router->match(HttpRequestMethod::GET, new Url('items/foo/slug'))
        );
    }
}

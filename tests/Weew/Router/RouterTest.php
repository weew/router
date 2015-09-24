<?php

namespace Tests\Weew\Router;

use Exception;
use PHPUnit_Framework_TestCase;
use stdClass;
use Weew\Http\HttpRequestMethod;
use Weew\Router\Exceptions\FilterException;
use Weew\Router\IRoute;
use Weew\Router\IRouter;
use Weew\Router\IRoutesMatcher;
use Weew\Router\Router;
use Weew\Router\RoutesMatcher;
use Weew\Url\Url;

class RouterTest extends PHPUnit_Framework_TestCase {
    public function test_get_and_set_routes() {
        $router = new Router();
        $routes = [];
        $router->setRoutes($routes);
        $this->assertTrue($routes === $router->getRoutes());

        $router = new Router();
        $this->assertTrue(is_array($router->getRoutes()));
    }

    public function test_get_and_set_routes_matcher() {
        $matcher = new RoutesMatcher();
        $router = new Router($matcher);
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
        $router->head('head', '_head');
        $router->post('post', '_post');
        $router->put('put', '_put');
        $router->update('update', '_update');
        $router->patch('patch', '_patch');
        $router->delete('delete', '_delete');
        $router->options('options', '_options');

        $routes = $router->getRoutes();
        $this->assertEquals('_get', array_shift($routes)->getValue());
        $this->assertEquals('_get', array_shift($routes)->getValue());
        $this->assertEquals('_head', array_shift($routes)->getValue());
        $this->assertEquals('_post', array_shift($routes)->getValue());
        $this->assertEquals('_put', array_shift($routes)->getValue());
        $this->assertEquals('_update', array_shift($routes)->getValue());
        $this->assertEquals('_patch', array_shift($routes)->getValue());
        $this->assertEquals('_delete', array_shift($routes)->getValue());
        $this->assertEquals('_options', array_shift($routes)->getValue());
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
        $route = $router->match(HttpRequestMethod::GET, new Url('items/_foo-bar'));
        $this->assertTrue($route instanceof IRoute);
        $this->assertEquals('id', $route->getValue());
        $this->assertEquals(['id' => '_foo-bar'], $route->getParameters());

        $route = $router->match(HttpRequestMethod::GET, new Url('items/_foo-bar/slug'));
        $this->assertTrue($route instanceof IRoute);
        $this->assertEquals('slug', $route->getValue());
        $this->assertEquals(['id' => '_foo-bar', 'alias' => null], $route->getParameters());

        $route = $router->match(HttpRequestMethod::GET, new Url('items/_foo-bar/slug/bar'));
        $this->assertTrue($route instanceof IRoute);
        $this->assertEquals('slug', $route->getValue());
        $this->assertEquals(['id' => '_foo-bar', 'alias' => 'bar'], $route->getParameters());
    }

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
        $this->assertNull(
            $router->match(HttpRequestMethod::GET, new Url('items/1/slug'))
        );
        $this->assertNull(
            $router->match(HttpRequestMethod::GET, new Url('items/foo'))
        );
        $this->assertNotNull(
            $router->match(HttpRequestMethod::GET, new Url('items/foo/slug'))
        );

        $router->group(function(IRouter $router) {
            $router->addPattern('id', '[0-9]+');
            $router->get('items/{id}/slug', 'slug');
        });

        $this->assertNotNull(
            $router->match(HttpRequestMethod::GET, new Url('items/1'))
        );
        $this->assertNotNull(
            $router->match(HttpRequestMethod::GET, new Url('items/1/slug'))
        );
        $this->assertNull(
            $router->match(HttpRequestMethod::GET, new Url('items/foo'))
        );
        $this->assertNotNull(
            $router->match(HttpRequestMethod::GET, new Url('items/foo/slug'))
        );
    }

    public function test_with_restrictions() {
        $router = new Router();
        $url1 = new Url('https://w.x.y.z/foo');
        $url2 = new Url('http://a.b.c.d/foo');

        $router->restrictProtocol('https');

        $router->group(function(IRouter $router) {
            $router->restrictProtocol('http');
            $router->get('foo', 'baz');
        });

        $router->get('foo', 'bar');

        $route = $router->match(HttpRequestMethod::GET, $url1);
        $this->assertNotNull($route);
        $this->assertEquals('bar', $route->getValue());

        $route = $router->match(HttpRequestMethod::GET, $url2);
        $this->assertNotNull($route);
        $this->assertEquals('baz', $route->getValue());

        $router->restrictTLD('y');
        $this->assertNull($router->match(HttpRequestMethod::GET, $url1));
        $router->restrictTLD(['z']);
        $this->assertNotNull($router->match(HttpRequestMethod::GET, $url1));

        $router->restrictSubdomain('a');
        $this->assertNull($router->match(HttpRequestMethod::GET, $url1));
        $router->restrictSubdomain(['w.x']);
        $this->assertNotNull($router->match(HttpRequestMethod::GET, $url1));

        $router->restrictDomain('foo');
        $this->assertNull($router->match(HttpRequestMethod::GET, $url1));
        $router->restrictDomain(['y']);
        $this->assertNotNull($router->match(HttpRequestMethod::GET, $url1));

        $router->restrictHost('foo');
        $this->assertNull($router->match(HttpRequestMethod::GET, $url1));
        $router->restrictHost(['w.x.y.z']);
        $this->assertNotNull($router->match(HttpRequestMethod::GET, $url1));
    }

    public function test_set_base_path() {
        $router = new Router();
        $router->setBasePath('api/v1');
        $router->get('users', 'users');
        $router->group(function(IRouter $router) {
            $router->get('posts', 'posts');
        });

        $route = $router->match(HttpRequestMethod::GET, new Url('api/v1/users'));
        $this->assertTrue($route instanceof IRoute);
        $this->assertEquals('users', $route->getValue());

        $route = $router->match(HttpRequestMethod::GET, new Url('api/v1/posts'));
        $this->assertTrue($route instanceof IRoute);
        $this->assertEquals('posts', $route->getValue());
    }

    public function test_with_filters() {
        $router = new Router();
        $router->get('users', 'users');
        $router->addFilter('foo', function() {
            return true;
        });
        $router->enableFilter('foo');
        $route = $router->match(HttpRequestMethod::GET, new Url('users'));
        $this->assertNotNull($route);

        $router->addFilter('bar', function() {
            return false;
        });
        $router->enableFilter(['bar']);
        $route = $router->match(HttpRequestMethod::GET, new Url('users'));
        $this->assertNull($route);
    }

    public function test_with_resolvers() {
        $router = new Router();
        $router->get('users/{user}/{name?}', 'users');
        $router->addResolver('user', function($parameter) {
            return new stdClass();
        });

        $route = $router->match(HttpRequestMethod::GET, new Url('users/22/foo'));

        $this->assertNotNull($route);
        $this->assertTrue($route->getParameter('user') instanceof stdClass);
        $this->assertEquals('foo', $route->getParameter('name'));
    }

    public function test_with_throwing_filters() {
        $router = new Router();
        $router->addFilter('foo', function() {
            throw new FilterException(
                new Exception('foo bar')
            );
        });

        $router->group(function(IRouter $router) {
            $router->get('users', 'users');
        });
        $router->group(function(IRouter $router) {
            $router->enableFilter(['foo']);
            $router->get('profile', 'profile');
        });

        $route = $router->match(HttpRequestMethod::GET, new Url('users'));
        $this->assertNotNull($route);

        $this->setExpectedException(Exception::class, 'foo bar');
        $router->match(HttpRequestMethod::GET, new Url('profile'));
    }

    public function test_that_throwing_filters_get_temporarily_handled() {
        $router = new Router();
        $router->addFilter('foo', function() {
            throw new FilterException(
                new Exception('foo bar')
            );
        });

        $router->group(function(IRouter $router) {
            $router->get('users', 'users');
        });
        $router->group(function(IRouter $router) {
            $router->enableFilter(['foo']);
            $router->get('profile', 'profile');
        });

        $router->group(function(IRouter $router) {
            $router->group(function(IRouter $router) {
                $router->get('profile', 'unsecure');
            });
        });

        $route = $router->match(HttpRequestMethod::GET, new Url('users'));
        $this->assertNotNull($route);

        $route = $router->match(HttpRequestMethod::GET, new Url('profile'));
        $this->assertNotNull($route);
        $this->assertEquals('unsecure', $route->getValue());
    }

    public function test_that_exceptions_thrown_by_filters_do_not_get_swallowed() {
        $router = new Router();
        $router->addFilter('foo', function() {
            throw new Exception('foo bar');
        });

        $router->group(function(IRouter $router) {
            $router->enableFilter(['foo']);
            $router->get('profile', 'profile');
        });

        $this->setExpectedException(Exception::class, 'foo bar');
        $router->match(HttpRequestMethod::GET, new Url('profile'));
    }

    public function test_head_routes_get_added() {
        $router = new Router();
        $router->get('foo', 'foo');
        $router->head('bar', 'bar');

        $route = $router->match(HttpRequestMethod::HEAD, new Url('foo'));
        $this->assertNotNull($route);
        $this->assertEquals('foo', $route->getValue());

        $route = $router->match(HttpRequestMethod::HEAD, new Url('bar'));
        $this->assertNotNull($route);
        $this->assertEquals('bar', $route->getValue());
    }
}

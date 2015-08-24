<?php

namespace Tests\Weew\Router;

use PHPUnit_Framework_TestCase;
use Weew\Http\HttpRequestMethod;
use Weew\Router\Route;

class RouteTest extends PHPUnit_Framework_TestCase {
    public function test_get_and_set_method() {
        $route = new Route(HttpRequestMethod::GET, null, null);
        $this->assertEquals(HttpRequestMethod::GET, $route->getMethod());
        $route->setMethod(HttpRequestMethod::POST);
        $this->assertEquals(HttpRequestMethod::POST, $route->getMethod());

        $this->setExpectedException('Exception');
        $route->setMethod('foo');
    }

    public function test_get_and_set_path() {
        $route = new Route(HttpRequestMethod::GET, 'foo', null);
        $this->assertEquals('/foo', $route->getPath());
        $route->setPath('/bar');
        $this->assertEquals('/bar', $route->getPath());
    }

    public function test_get_and_set_value() {
        $route = new Route(HttpRequestMethod::GET, 'foo', 'bar');
        $this->assertEquals('bar', $route->getValue());
        $route->setValue('foo');
        $this->assertEquals('foo', $route->getValue());
    }

    public function test_to_array() {
        $route = new Route(HttpRequestMethod::GET, 'foo', 'bar');
        $route->setParameters(['foo' => 'bar']);
        $this->assertEquals(
            [
                'method' => HttpRequestMethod::GET,
                'path' => '/foo',
                'value' => 'bar',
                'parameters' => ['foo' => 'bar'],
            ],
            $route->toArray()
        );
    }

    public function test_get_and_set_parameters() {
        $route = new Route(HttpRequestMethod::GET, '/', 'foo');
        $route->setParameters(['foo' => 'bar']);
        $this->assertEquals('bar', $route->getParameter('foo'));
        $this->assertEquals('swag', $route->getParameter('yolo', 'swag'));
        $route->setParameter('yolo', 'swag');
        $this->assertEquals('swag', $route->getParameter('yolo'));
    }
}

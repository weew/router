<?php

namespace Tests\Weew\Router;

use PHPUnit_Framework_TestCase;
use Weew\Router\Exceptions\InvalidResolverException;
use Weew\Router\RouteResolver;

class RouteResolverTest extends PHPUnit_Framework_TestCase {
    public function test_getters_and_setters() {
        $cb = function() {};
        $resolver = new RouteResolver('foo', $cb);
        $this->assertEquals('foo', $resolver->getName());
        $this->assertTrue($cb === $resolver->getResolver());
    }

    public function test_set_invalid_resolver() {
        $this->setExpectedException(InvalidResolverException::class);
        new RouteResolver('foo', 'bar');
    }
}

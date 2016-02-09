<?php

namespace Tests\Weew\Router;

use PHPUnit_Framework_TestCase;
use Weew\Router\ParameterResolver;
use Weew\Router\RouteResolver;

class ParameterResolverTest extends PHPUnit_Framework_TestCase {
    public function test_get_and_set_resolvers() {
        $resolver = new ParameterResolver();
        $this->assertEquals([], $resolver->getResolvers());
        $resolvers = [
            'foo' => new RouteResolver('foo', function() {})
        ];
        $resolver->setResolvers($resolvers);
        $this->assertTrue($resolvers === $resolver->getResolvers());
    }
}

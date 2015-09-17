<?php

namespace Tests\Weew\Router;

use PHPUnit_Framework_TestCase;
use Weew\Router\ParameterResolver;

class ParameterResolverTest extends PHPUnit_Framework_TestCase {
    public function test_get_and_set_resolvers() {
        $resolver = new ParameterResolver();
        $this->assertEquals([], $resolver->getResolvers());
        $resolvers = [1];
        $resolver->setResolvers($resolvers);
        $this->assertTrue($resolvers === $resolver->getResolvers());
    }
}

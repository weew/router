<?php

namespace Tests\Weew\Router;

use PHPUnit_Framework_TestCase;
use Weew\Router\RouteFilter;

class RouteFilterTest extends PHPUnit_Framework_TestCase {
    public function test_getters_and_setters() {
        $cb = function() {};
        $filter = new RouteFilter('foo', $cb, true);

        $this->assertEquals('foo', $filter->getName());
        $this->assertTrue($filter->getFilter() === $cb);
        $this->assertTrue($filter->isEnabled());
    }
}

<?php

namespace Weew\Router;

use Weew\Router\Exceptions\InvalidResolverException;

class RouteResolver implements IRouteResolver {
    /**
     * @var string
     */
    protected $name;

    /**
     * @var callable
     */
    protected $resolver;

    /**
     * RouteResolver constructor.
     *
     * @param $name
     * @param $callable
     */
    public function __construct($name, $callable) {
        $this->setName($name);
        $this->setResolver($callable);
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return callable
     */
    public function getResolver() {
        return $this->resolver;
    }

    /**
     * @param $callable
     *
     * @throws InvalidResolverException
     */
    public function setResolver($callable) {
        if ( ! is_callable($callable)) {
            throw new InvalidResolverException(s(
                'Routing resolver must be a callable, received "%s".', get_type($callable)
            ));
        }

        $this->resolver = $callable;
    }
}

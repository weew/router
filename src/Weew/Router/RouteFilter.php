<?php

namespace Weew\Router;

use Weew\Router\Exceptions\InvalidFilterException;

class RouteFilter implements IRouteFilter {
    /**
     * @var string
     */
    protected $name;

    /**
     * @var callable
     */
    protected $filter;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * RouteFilter constructor.
     *
     * @param $name
     * @param $callable
     * @param bool $enabled
     */
    public function __construct($name, $callable, $enabled = false) {
        $this->setName($name);
        $this->setFilter($callable);
        $this->setEnabled($enabled);
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
    public function getFilter() {
        return $this->filter;
    }

    /**
     * @param $callable
     *
     * @throws InvalidFilterException
     */
    public function setFilter($callable) {
        if ( ! is_callable($callable)) {
            throw new InvalidFilterException(s(
                'Routing filter must be a callable, received "%s".', get_type($callable)
            ));
        }

        $this->filter = $callable;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled;
    }
}

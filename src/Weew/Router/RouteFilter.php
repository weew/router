<?php

namespace Weew\Router;

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
     * @param callable $filter
     * @param bool $enabled
     */
    public function __construct($name, callable $filter, $enabled = false) {
        $this->setName($name);
        $this->setFilter($filter);
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
     * @param callable $filter
     */
    public function setFilter(callable $filter) {
        $this->filter = $filter;
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

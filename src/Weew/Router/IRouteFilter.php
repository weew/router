<?php

namespace Weew\Router;

interface IRouteFilter {
    /**
     * @return string
     */
    function getName();

    /**
     * @param string $name
     */
    function setName($name);

    /**
     * @return callable
     */
    function getFilter();

    /**
     * @param callable $filter
     */
    function setFilter(callable $filter);

    /**
     * @param bool $enabled
     */
    function setEnabled($enabled);

    /**
     * @return bool
     */
    function isEnabled();
}

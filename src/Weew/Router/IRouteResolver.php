<?php

namespace Weew\Router;

interface IRouteResolver {
    /**
     * @return string
     */
    function getName();

    /**
     * @param $name
     */
    function setName($name);

    /**
     * @return callable
     */
    function getResolver();

    /**
     * @param $callable
     */
    function setResolver($callable);
}

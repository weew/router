<?php

namespace Weew\Router;

interface IFiltersMatcher {
    /**
     * @return bool
     */
    function applyFilters();

    /**
     * @return array
     */
    function getFilters();

    /**
     * @param array $filters
     */
    function setFilters(array $filters);

    /**
     * @param $name
     * @param callable $filter
     */
    function addFilter($name, callable $filter);

    /**
     * @param $names
     */
    function enableFilters(array $names);

    /**
     * @return IFilterInvoker
     */
    function getFilterInvoker();

    /**
     * @param IFilterInvoker $filterInvoker
     */
    function setFilterInvoker(IFilterInvoker $filterInvoker);
}

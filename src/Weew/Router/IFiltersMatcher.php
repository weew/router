<?php

namespace Weew\Router;

interface IFiltersMatcher {
    /**
     * @param IRoute $route
     *
     * @return bool
     */
    function applyFilters(IRoute $route);

    /**
     * @return IRouteFilter[]
     */
    function getFilters();

    /**
     * @param IRouteFilter[] $filters
     */
    function setFilters(array $filters);

    /**
     * @param IRouteFilter $filter
     */
    function addFilter(IRouteFilter $filter);

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

<?php

namespace Weew\Router;

use Weew\Router\Exceptions\FilterNotFoundException;

class FiltersMatcher implements IFiltersMatcher {
    /**
     * @var IRouteFilter[]
     */
    protected $filters = [];

    /**
     * @var IFilterInvoker
     */
    protected $filterInvoker;

    /**
     * @param IFilterInvoker|null $filterInvoker
     */
    public function __construct(IFilterInvoker $filterInvoker = null) {
        if ( ! $filterInvoker instanceof IFilterInvoker) {
            $filterInvoker = $this->createFilterInvoker();
        }

        $this->setFilterInvoker($filterInvoker);
    }

    /**
     * @param IRoute $route
     *
     * @return bool
     */
    public function applyFilters(IRoute $route) {
        foreach ($this->getFilters() as $filter) {
            if ($filter->isEnabled()) {
                $invoker = $this->getFilterInvoker();
                $result = $invoker->invoke($filter->getFilter(), $route);

                if (is_bool($result)) {
                    return $result;
                }
            }
        }

        return true;
    }

    /**
     * @return IRouteFilter[]
     */
    public function getFilters() {
        return $this->filters;
    }

    /**
     * @param IRouteFilter[] $filters
     */
    public function setFilters(array $filters) {
        $this->filters = [];

        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * @param IRouteFilter $filter
     */
    public function addFilter(IRouteFilter $filter) {
        $this->filters[$filter->getName()] = $filter;
    }

    /**
     * @param array $names
     *
     * @throws FilterNotFoundException
     */
    public function enableFilters(array $names) {
        foreach ($names as $name) {
            $filter = array_get($this->filters, $name);

            if ( ! $filter instanceof IRouteFilter) {
                throw new FilterNotFoundException(
                    s('Filter with name %s not found.', $name)
                );
            }

            $filter->setEnabled(true);
        }
    }

    /**
     * @return IFilterInvoker
     */
    public function getFilterInvoker() {
        return $this->filterInvoker;
    }

    /**
     * @param IFilterInvoker $filterInvoker
     */
    public function setFilterInvoker(IFilterInvoker $filterInvoker) {
        $this->filterInvoker = $filterInvoker;
    }

    /**
     * Clone filters matcher and all nested objects.
     */
    public function __clone() {
        $filters = $this->getFilters();
        $this->setFilters([]);

        foreach ($filters as $filter) {
            $this->addFilter(clone $filter);
        }
    }

    /**
     * @return IFilterInvoker
     */
    protected function createFilterInvoker() {
        return new FilterInvoker();
    }
}

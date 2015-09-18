<?php

namespace Weew\Router;

use Weew\Router\Exceptions\FilterNotFoundException;

class FiltersMatcher implements IFiltersMatcher {
    /**
     * @var array
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
            if ($filter['enabled']) {
                $invoker = $this->getFilterInvoker();
                $result = $invoker->invoke($filter['filter'], $route);

                if ($result === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getFilters() {
        return $this->filters;
    }

    /**
     * @param array $filters
     */
    public function setFilters(array $filters) {
        $this->filters = $filters;
    }

    /**
     * @param $name
     * @param callable $filter
     */
    public function addFilter($name, callable $filter) {
        $this->filters[$name] = [
            'filter' => $filter,
            'enabled' => false,
        ];
    }

    /**
     * @param array $names
     *
     * @throws FilterNotFoundException
     */
    public function enableFilters(array $names) {
        foreach ($names as $name) {
            $filter = array_get($this->filters, $name);

            if ($filter === null) {
                throw new FilterNotFoundException(
                    s('Filter with name %s not found.', $name)
                );
            }

            $filter['enabled'] = true;
            $this->filters[$name] = $filter;
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
     * @return IFilterInvoker
     */
    protected function createFilterInvoker() {
        return new FilterInvoker();
    }
}

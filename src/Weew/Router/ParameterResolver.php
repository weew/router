<?php

namespace Weew\Router;

class ParameterResolver implements IParameterResolver {
    /**
     * @var array
     */
    protected $parameterResolvers = [];

    /**
     * @var IParameterResolverInvoker
     */
    protected $resolverInvoker;

    /**
     * @param IParameterResolverInvoker|null $resolverInvoker
     */
    public function __construct(
        IParameterResolverInvoker $resolverInvoker = null
    ) {
        if ( ! $resolverInvoker instanceof IParameterResolverInvoker) {
            $resolverInvoker = $this->createParameterResolverInvoker();
        }

        $this->setParameterResolverInvoker($resolverInvoker);
    }

    /**
     * @param IRoute $route
     */
    public function resolveRouteParameters(IRoute $route) {
        $parameters = $route->getParameters();
        $resolvers = $this->getResolvers();
        $invoker = $this->getParameterResolverInvoker();

        foreach ($parameters as $name => $parameter) {
            if ($resolver = array_get($resolvers, $name)) {
                $parameters[$name] = $invoker->invoke($parameter, $resolver);
            }
        }

        $route->setParameters($parameters);
    }

    /**
     * @return array
     */
    public function getResolvers() {
        return $this->parameterResolvers;
    }

    /**
     * @param array $resolvers
     */
    public function setResolvers(array $resolvers) {
        $this->parameterResolvers = $resolvers;
    }

    /**
     * @param $name
     * @param callable $resolver
     */
    public function addResolver($name, callable $resolver) {
        $this->parameterResolvers[$name] = $resolver;
    }

    /**
     * @return IParameterResolverInvoker
     */
    public function getParameterResolverInvoker() {
        return $this->resolverInvoker;
    }

    /**
     * @param IParameterResolverInvoker $resolverInvoker
     */
    public function setParameterResolverInvoker(
        IParameterResolverInvoker $resolverInvoker
    ) {
        $this->resolverInvoker = $resolverInvoker;
    }

    /**
     * @return IParameterResolverInvoker
     */
    protected function createParameterResolverInvoker() {
        return new ParameterResolverInvoker();
    }
}

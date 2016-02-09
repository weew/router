<?php

namespace Weew\Router;

class ParameterResolver implements IParameterResolver {
    /**
     * @var IRouteResolver[]
     */
    protected $resolvers = [];

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
     *
     * @return bool
     */
    public function resolveRouteParameters(IRoute $route) {
        $parameters = $route->getParameters();
        $resolvers = $this->getResolvers();
        $invoker = $this->getParameterResolverInvoker();

        foreach ($parameters as $name => $parameter) {
            $resolver = array_get($resolvers, $name);

            if ($resolver instanceof IRouteResolver) {
                $value = $invoker->invoke($resolver->getResolver(), $parameter);

                if ($value === null) {
                    return false;
                }

                $parameters[$name] = $value;
            }
        }

        $route->setParameters($parameters);

        return true;
    }

    /**
     * @return IRouteResolver[]
     */
    public function getResolvers() {
        return $this->resolvers;
    }

    /**
     * @param IRouteResolver[] $resolvers
     */
    public function setResolvers(array $resolvers) {
        $this->resolvers = [];

        foreach ($resolvers as $resolver) {
            $this->addResolver($resolver);
        }
    }

    /**
     * @param IRouteResolver $resolver
     */
    public function addResolver(IRouteResolver $resolver) {
        $this->resolvers[$resolver->getName()] = $resolver;
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

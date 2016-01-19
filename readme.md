# Simple router

[![Build Status](https://img.shields.io/travis/weew/php-router.svg)](https://travis-ci.org/weew/php-router)
[![Code Quality](https://img.shields.io/scrutinizer/g/weew/php-router.svg)](https://scrutinizer-ci.com/g/weew/php-router)
[![Test Coverage](https://img.shields.io/coveralls/weew/php-router.svg)](https://coveralls.io/github/weew/php-router)
[![Dependencies](https://img.shields.io/versioneye/d/php/weew:php-router.svg)](https://versioneye.com/php/weew:php-router)
[![Version](https://img.shields.io/packagist/v/weew/php-router.svg)](https://packagist.org/packages/weew/php-router)
[![Licence](https://img.shields.io/packagist/l/weew/php-router.svg)](https://packagist.org/packages/weew/php-router)

## Table of contents

- [Installation](#installation)
- [Introduction](#introduction)
- [Registering routes](#registering-routes)
- [Route parameters](#route-parameters)
- [Matching routes](#matching-routes)
- [Custom patterns](#custom-patterns)
- [Firewalls](#firewalls)
- [Parameter resolvers](#parameter-resolvers)
- [Rules](#rules)
- [Grouping routes](#grouping-routes)
- [Complete example](#complete-example)
- [Existing container integrations](#existing-container-integrations)
- [Related projects](#related-projects)

## Installation

`composer require weew/php-router`

## Introduction

What the router basically does is matching a URL to a list of registered routes and returns you a route upon a successful match. If there was no match,you'll get null. A route can contain any value you want, since it's up to you to create a response based on the route after all. This gives you the flexibility to use the router together with any other existing dependency injection containers or any other components. The router doesn't do anything but matching a URL to a route.

## Registering routes

Below you'll see the basic methods for route registration. Route path may
contain some placeholders for expected values, like `{id}`. If
the placeholder ends with a `?`, like here `{alias?}`, it is
considered optional. As the second argument you may pass anything you
want. You'll have access to that value later, so you can create a
response or similar.

```php
$router = new Router();
$router->get('/', 'home')
    ->post('login', 'SomeController::handleLogin')
    ->put('users/{id}', function() {})
    ->patch('users/{id}', '')
    ->update('some/path/{optional?}', '')
    ->delete('users/{id}/{alias?}', '')
    ->options('you/wont/need/it', '');
```

Mostly you are going to use this definition format for your route definitions:

```php
$router = new Router();
$router
    ->get('/', [SampleController::class, 'getHome'])
    ->get('about', [SampleController::class, 'getAbout'])
    ->get('contact', [SampleController::class, 'getContact']);
```

As you see in this example, you've got to write the `SampleController` class over and over again. You can avoid this by setting a controller class on the router itself. Doing so, will create a new [nested router](#grouping-routes). Example below does exactly the same as the example above, except you have less boilerplate code.

```php
$router = new Router();
$router
    ->setController(SampleController::class)
        ->get('/', 'getHome')
        ->get('about', 'getAbout')
        ->get('contact', 'getContact');
```

## Route parameters

Let's say you've defined some routes that expect a parameter
to be set in the url, here you'll see how you can access them.

```php
$router = new Router();
$router->get('home/{greeting?}', 'home');

$route = $router->match(HttpRequestMethod::GET, new Url('home/welcome'));
echo $route->getParameter('greeting');
// welcome
```

## Matching routes

By now you have registered all of your routes and want to find
the one that matches the specified url.

```php
$router = new Router();
$router->get('home/{greeting?}', 'home');
$route = $router->match(HttpRequestMethod::GET, new Url('home/hello-there'));

if ( ! $route === null) {
    echo $route->getValue();
    // home
    echo $route->getParameter('greeting');
    // hello-there
} else {
    // no route found, thow a 404?
}
```

## Custom patterns

In some situations you wont to be able to specify custom
patterns for route parameters. For example, lets say you want your ids
to consist of numerical characters only.

```php
$router = new Router();
$router
    ->addPattern('id', '[0-9]+')
    ->get('users/{id}', '');
```

## Firewalls

It is very easy to protect routes with custom filters.

```php
$router = new Router();
$router->addFilter('auth', function(IRoute $route) {
    return false; // not authenticated
});

$router->enableFilter('auth');
```

A filter has to return a boolean value to indicate whether the affected routes are good to go or rather should be ignored. Filter work best with groups, see below.

Sometimes you might want to throw an exception that would hold the reason why a filter did not pass. If you simply throw a regular exception, this would kill the program flow, and even if you catch this exception somewhere, it would kill the whole routing process. Even though a particular route did not match, because a filter failed, there might be another one that would fit. After a regular exception was thrown, there is no way another route might match. To work around this you might simply wrap you exception in the `FilterException`. This would ensure that the routing process finishes as supposed to and gives a chance for another route to match. If no route was found after all, you original exception will be thrown.

```php
$router = new Router();
$router->addFilter('auth', function(IRoute $route) {
    throw new FilterException(
        new UnauthorizedException()
    );
});
```

Now, failure of a filter will not break the routing process. If a route gets matched after all, there will be no exception thrown. But if there was no other route that could take it's place (be matched instead), the `UnauthorizedException` will be thrown.

## Parameter resolvers

Often you might want to process a route parameter and replace it with another one. For example when you're using models. This route `/users/{id}` would always hold the id of the requested user. Wouldn't it be cool if it would hold the user model instead?

```php
$router = new Router();
$router->addResolver('user', function($parameter) {
    return new User(); // for the sake of the example lets just return a new model
});

$router->get('users/{user}', function(User $user) {
    // work with the user model
});
```

User's id has been magically resolved to it's model. Now you can use it in your
route handlers.

## Rules

You might also want to specify additional routing restrictions
based on the current url. For example, limiting your routes to a
subdomain or protocol.

```php
$router = new Router();
$router
    ->restrictSubdomain('api')
    ->get('users/{id}', '');
```

There are many other restrictions that you might find useful.

```php
$router = new Router();
$router->restrictProtocol('https')
    ->restrictDomain(['domain1', 'domain2'])
    ->restrictTLD(['com', 'net'])
    ->restrictSubdomain('api')
    ->restrictHost(['domain1.com', 'domain2.net'])
```

## Grouping routes

Sometimes you have an obvious boundary between your routes.
Lets say you want your api routes to be available from the
`api` subdomain and over https only. All the other routes should
remain as is.

```php
$router = new Router();
$router->get('/', 'home');
$router->group(function(IRouter $router) {
    $router->restrictProtocol('https')
        ->restrictSubdomain('api')
        ->get('users/{id}', '');
});
$router->get('about', 'about');
```

## Complete example

A complete example of how you might use the router out of the box. The router itself is very flexible and at the end it comes down to your preference on how you will use it. Basically all it does is returning a route. After that it's up to you how you want to handle it. You might dynamically resolve the controller, or event combine it with a dependency injection container.

```php
$router = new Router();

$router->get('/', 'home');
$router->get('about', 'about');
$router->post('login', 'login');

$router->addFilter('auth', function(IRoute $route) {
    return fasle; // not logged in
});

$router->addResolver('user', function($id) {
    return new User($id);
});

$router->group(function(IRouter $router) {
    $router->setBasePath('api/v1');
    $router->restrictProtocol('https');
    $router->restrictSubdomain('api');
    $router->enableFilter('auth');

    $router->get('users/{user}/{alias?}', function(IRoute $route) {
        $response = new JsonResponse(HttpRequestMethod::GET, [
            'id' => $route->getParameter('user')->id,
            'alias' => $route->getParameter('alias', 'no alias')
        ]);
        $response->send();
    });
});

$router->group(function(IRouter $router) {
    $router->setBasePath('api/v2');
    $router->addPattern('id', '[a-zA-Z]+');

    $router->get('users/{user}/{alias?}', function(IRoute $route) {
        $response = new JsonResponse(HttpRequestMethod::GET, [
            'id' => $route->getParameter('user')->id,
            'alias' => $route->getParameter('alias', 'no alias')
        ]);
        $response->send();
    });
});

// recommended way to work with the request
$request = new CurrentRequest();
$route = $router->match($request->getMethod(), $request->getUrl());

// create a response based on the route
if ($route instanceof IRoute) {
    $abstract = $route->getValue();

    if (is_callable($abstract)) {
        $abstract($route);
    } else {
        echo $abstract;
    }
} else {
    echo '404';
}
```

## Existing container integrations

There is an integration available for the [weew/php-container](https://github.com/weew/php-container) container. See [weew/php-router-container-aware](https://github.com/weew/php-router-container-aware).

## Related Projects

- [URL](https://github.com/weew/php-url): used throughout the project.
- [HTTP Layer](https://github.com/weew/php-http): offers response and request objects,
handles cookies, headers and much more.
- [HTTP Blueprint](https://github.com/weew/php-http-blueprint): spin up a server,
serve some content, shutdown the server.
- [Dependency Injection Container](https://github.com/weew/php-container): Router works great together with this library.

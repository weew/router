# Simple router

[![Build Status](https://travis-ci.org/weew/php-router.svg?branch=master)](https://travis-ci.org/weew/php-router)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/weew/php-router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/weew/php-router/?branch=master)
[![Coverage Status](https://coveralls.io/repos/weew/php-router/badge.svg?branch=master&service=github)](https://coveralls.io/github/weew/php-router?branch=master)
[![License](https://poser.pugx.org/weew/php-router/license)](https://packagist.org/packages/weew/php-router)

## Installation

`composer require weew/php-router`

## Related Projects

[URL](https://github.com/weew/php-url): used throughout the project.

[HTTP Layer](https://github.com/weew/php-http): offers response and request objects,
handles cookies, headers and much more.

## Introduction

What the router basically does is matching a URL to a list of registered
routes and returns you a route upon a successful match. If there was no match,
you'll get null. A route can contain any value you want, since it's up to you
to create a response based on the route after all. This gives you the flexibility
to use the router together with any other existing dependency injection containers
or any other components. The router doesn't do anything but matching a URL
to a route.

## Usage

#### Registering routes

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

#### Route parameters

Let's say you've defined some routes that expect a parameter
to be set in the url, here you'll see how you can access them.

```php
$router = new Router();
$router->get('home/{greeting?}', 'home');

$route = $router->match(HttpRequestMethod::GET, new Url('home/welcome'));
echo $route->getParameter('greeting');
// welcome
```

#### Custom patterns

In some situations you wont to be able to specify custom
patterns for route parameters. For example, lets say you want your ids
to consist of numerical characters only.

```php
$router = new Router();
    ->addPattern('id', '[0-9]+')
    ->get('users/{id}', '');
```

#### Restrictions

You might also want to specify additional routing restrictions
based on the current url. For example, limiting your routes to a
subdomain or protocol.

```php
$router = new Router();
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

#### Grouping routes

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

#### Matching routes to a URL

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

#### Complete example

A complete example of how you might use the router out of the box.

```php
$router = new Router();

$router->get('/', 'home');
$router->get('about', 'about');
$router->post('login', 'login');

$router->group(function(IRouter $router) {
    $router->setBasePath('api/v1');
    $router->restrictProtocol('https');
    $router->restrictSubdomain('api');

    $router->get('users/{id}/{alias?}', function(IRoute $route) {
        $response = new JsonResponse(HttpRequestMethod::GET, [
            'id' => $route->getParameter('id'),
            'alias' => $route->getParameter('alias', 'no alias')
        ]);
        $response->send();
    });
});

$router->group(function(IRouter $router) {
    $router->setBasePath('api/v2');
    $router->addPattern('id', '[a-zA-Z]+');

    $router->get('users/{id}/{alias?}', function(IRoute $route) {
        $response = new JsonResponse(HttpRequestMethod::GET, [
            'id' => $route->getParameter('id'),
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

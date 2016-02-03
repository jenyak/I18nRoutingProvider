I18n Routing Service Provider  [![Build Status](https://secure.travis-ci.org/jenyak/I18nRoutingServiceProvider.png)](http://travis-ci.org/jenyak/I18nRoutingServiceProvider)
=============================

Silex i18n routing service provider inspired by [JMSI18nRoutingBundle](https://github.com/schmittjoh/JMSI18nRoutingBundle)

Installation
------------

Recommended installation is [through composer](http://getcomposer.org). Just add
the following to your `composer.json` file:
### Silex 1.3
    {
        "require": {
            "jenyak/i18n-routing-service-provider": "~1.0"
        }
    }
### Silex 2
    {
        "require": {
            "jenyak/i18n-routing-service-provider": "dev-master"
        }
    }

# Registering

```php
$app->register(new Jenyak\I18nRouting\Provider\I18nRoutingServiceProvider());
```

# Parameters

* **i18n_routing.translation_domain**: Translation domain for routes. The default value is `routes`.
* **i18n_routing.locales**: Routing locales. The default value is `array(en)`.
* **locale**: Default routing locale. The default value is `en`.

# Example

```php
$app = new Application();
//...
$app->register(new Jenyak\I18nRouting\Provider\I18nRoutingServiceProvider());
$app['locale'] = 'en';
$app['i18n_routing.locales'] = array('en', 'hu', 'ua');

// You can translate patterns
$app['translator.domains'] = array('routes' => array(
    'hu' => array('test_route' => '/teszt'),
));

// There's no need to put {_locale} in route pattern
$app->get('/test', function () {
   //...
})->bind('test_route');
```
Matched URLs will be:

`/test` - url for default locale without prefix

`/hu/teszt` - url with prefix and translated

`/ua/test` - url with prefix

# Disable I18n for a route
```php
$app->get('/dont-translate', function() {
    //...
})->bind('my_route')->getRoute()->setOption('i18n', false);



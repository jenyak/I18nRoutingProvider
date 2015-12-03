<?php

namespace Jenyak\I18nRouting;

use Jenyak\I18nRouting\Provider\I18nRoutingServiceProvider;
use Silex\Application;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\HttpFoundation\Request;


class I18nRoutingServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    private function createApplication()
    {
        $app = new Application();
        $app['locale'] = 'en';

        $app->register(new TranslationServiceProvider());
        $app->register(new I18nRoutingServiceProvider());

        $app['i18n_routing.locales'] = array('en', 'ua');
        $app['translator.domains'] = array('routes' => array(
            'ua' => array('test' => '/тест'),
        ));
        $app['i18n_routing.allow_localized_default'] = false;

        return $app;
    }

    public function testDefaultLocaleI18nRoutes()
    {
        $app = $this->createApplication();

        $app->get('/test', function () {
            return 'ok';
        })->bind('test');

        $this->assertEquals(200, $app->handle(Request::create('/test'))->getStatusCode());
    }

    public function testDefaultLocaleWithPrefixI18nRoutes()
    {
        $app = $this->createApplication();

        $app->get('/', function () {
            return 'ok';
        });

        $this->assertEquals(404, $app->handle(Request::create('/en/'))->getStatusCode());
    }

    public function testNonDefaultLocaleI18nRoutes()
    {
        $app = $this->createApplication();

        $app->get('/test', function () {
            return 'ok';
        })->bind('test');

        $this->assertEquals(200, $app->handle(Request::create('/ua/тест'))->getStatusCode());
    }
}

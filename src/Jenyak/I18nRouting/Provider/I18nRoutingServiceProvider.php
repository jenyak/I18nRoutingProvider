<?php

namespace Jenyak\I18nRouting\Provider;

use Jenyak\I18nRouting\I18nControllerCollection;
use Jenyak\I18nRouting\I18nUrlGenerator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;

class I18nRoutingServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['controllers_factory'] = function () use ($app) {
            return new I18nControllerCollection(
                $app['route_factory'],
                $app['locale'],
                $app['i18n_routing.locales'],
                $app['translator'],
                $app['i18n_routing.translation_domain'],
                $app['i18n_routing.allow_localized_default']
            );
        };

        $app['url_generator'] = function ($app) {
            $app->flush();

            return new I18nUrlGenerator($app['routes'], $app['request_context']);
        };

        $app['i18n_routing.locales'] = array('en');
        $app['i18n_routing.translation_domain'] = 'routes';
        $app['i18n_routing.allow_localized_default'] = false;
    }

    public function boot(Application $app)
    {
    }
}

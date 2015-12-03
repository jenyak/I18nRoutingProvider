<?php

namespace Jenyak\I18nRouting;

use Silex\Controller;
use Silex\ControllerCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\TranslatorInterface;

class I18nControllerCollection extends ControllerCollection
{
    const ROUTING_PREFIX = '__RG__';

    private $defaultLocale;
    private $locales;
    private $translator;
    private $translationDomain;
    private $localizedDefault;

    public function __construct(Route $defaultRoute, $defaultLocale = 'en', array $locales, TranslatorInterface $translator, $translationDomain = 'routes', $localizedDefault = false)
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->localizedDefault = $localizedDefault;

        parent::__construct($defaultRoute);
    }

    public function generateI18nPatterns($routeName, Route $route)
    {
        $patterns = array();
        foreach ($route->getOption('i18n_locales') ?: $this->locales as $locale) {
            // if no translation exists, we use the current pattern
            if ($routeName === $i18nPattern = $this->translator->trans($routeName, array(), $this->translationDomain, $locale)) {
                $i18nPattern = $route->getPath();
            }

            if ($this->defaultLocale == $locale && $this->localizedDefault === true) {
                $patterns[$i18nPattern][] = $locale;
            }

            $patterns['/'.$locale.$i18nPattern][] = $locale;

        }

        return $patterns;
    }


    public function flush($prefix = '')
    {
        $routes = new RouteCollection();

        foreach ($this->controllers as $controller) {
            if ($controller instanceof Controller) {
                if (!$name = $controller->getRouteName()) {
                    $name = $controller->generateRouteName($prefix);
                    while ($routes->get($name)) {
                        $name .= '_';
                    }
                    $controller->bind($name);
                }
                $route = $controller->getRoute();


                $prefix = trim(trim($prefix), '/');
                $route->setPath('/'.$prefix.$route->getPath());

                if ($this->shouldExcludeRoute($name, $route)) {
                    $routes->add($name, $route);
                } else {
                    foreach ($this->generateI18nPatterns($name, $route) as $pattern => $locales) {
                        // If this pattern is used for more than one locale, we need to keep the original route.
                        // We still add individual routes for each locale afterwards for faster generation.
                        if (count($locales) > 1) {
                            $catchMultipleRoute = clone $route;
                            $catchMultipleRoute->setPath($pattern);
                            $catchMultipleRoute->setDefault('_locales', $locales);
                            $routes->add(implode('_', $locales).self::ROUTING_PREFIX.$name, $catchMultipleRoute);
                        }

                        foreach ($locales as $locale) {
                            $localeRoute = clone $route;
                            $localeRoute->setPath($pattern);
                            $localeRoute->setDefault('_locale', $locale);
                            $default = (0 === strpos($pattern, '/'.$locale)) ? '' : '_default';
                            $routes->add($locale.$default.self::ROUTING_PREFIX.$name, $localeRoute);
                        }
                    }
                }

                $controller->freeze();
            } else {
                $routes->addCollection($controller->flush($controller->prefix));
            }
        }

        $this->controllers = array();

        return $routes;
    }

    public function shouldExcludeRoute($routeName, Route $route)
    {
        if ('_' === $routeName[0]) {
            return true;
        }

        if (false === $route->getOption('i18n')) {
            return true;
        }

        return false;
    }
}

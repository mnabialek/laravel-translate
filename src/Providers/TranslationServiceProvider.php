<?php

namespace Mnabialek\Translate\Providers;

use Mnabialek\LaravelTranslate\Services\PoFileLoader;
use Mnabialek\LaravelTranslate\Services\PoFileReader;
use Mnabialek\LaravelTranslate\Services\Translator;

class TranslationServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLoader();

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];

            $trans = new Translator($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });

        // register files to be published
        $this->publishes([
            '../../config/translator.php' => config_path('translator.php'),
        ]);
    }

    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new PoFileLoader($app['files'], $app['path.lang'],
                new PoFileReader());
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['translator', 'translation.loader'];
    }
}

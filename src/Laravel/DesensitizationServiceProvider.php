<?php

namespace Leoboy\Desensitization\Laravel;

use Illuminate\Contracts\Console\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Leoboy\Desensitization\Desensitizer;

class DesensitizationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->bind(Desensitizer::class, function (Application $app) {
            return new Desensitizer($app['config']->get('desensitization'));
        });

        $this->app->alias(Desensitizer::class, 'desensitizer');

        $this->mergeConfigFrom(
            __DIR__.'/config/desensitization.php',
            'desensitization'
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/desensitization.php',
            $this->app->configPath('desensitization.php'),
        ]);
    }

    public function provides(): array
    {
        return [
            Desensitizer::class,
            'desensitizer',
        ];
    }
}

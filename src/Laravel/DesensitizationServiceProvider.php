<?php

/*
 * This file is part of the Leoboy\Desensitization package.
 *
 * (c) messikiller <messikiller@aliyun.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Leoboy\Desensitization
 * @author messikiller <messikiller@aliyun.com>
 */

namespace Leoboy\Desensitization\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Leoboy\Desensitization\Desensitizer;

/**
 * service provider for laravel integeration.
 */
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

    public function boot(): void
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

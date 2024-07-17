<?php

use Leoboy\Desensitization\Desensitizer;
use Leoboy\Desensitization\Laravel\Facades\Desensitization;
use Orchestra\Testbench\TestCase;

final class LaravelTest extends TestCase
{
    protected $enablesPackageDiscoveries = true;

    public function testConfig(): void
    {
        $this->assertSame('*', $this->app['config']->get('desensitization.wildcardChar'));
        $this->assertSame('.', $this->app['config']->get('desensitization.keyDot'));
        $this->assertSame(false, $this->app['config']->get('desensitization.skipTransformationException'));
    }

    public function testServiceProvider(): void
    {
        $this->assertInstanceOf(Desensitizer::class, $this->app->get(Desensitizer::class));
        $this->assertInstanceOf(Desensitizer::class, $this->app->get('desensitizer'));
    }

    public function testFacade(): void
    {
        $this->assertSame('Lio###ssi', Desensitization::invoke('LionelMessi', 'mask|use:#|repeat:3|padding:3'));
        $this->assertSame('$$$', Desensitization::via('replace|use:$$$')->invoke('LionelMessi'));

        Desensitization::global()->via('mask|use:*|repeat:2|padding:1');
        $this->assertSame('L**i', Desensitization::global()->invoke('LionelMessi'));

        $globalized = Desensitization::via('replace|use:@@@')->globalize();
        $this->assertSame($globalized, Desensitization::global());
        $this->assertSame('@@@', Desensitization::global()->invoke('LionelMessi'));
    }
}

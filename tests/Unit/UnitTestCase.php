<?php
namespace andytan07\LaravelSesTracker\Tests\Unit;

use andytan07\LaravelSesTracker\SesMail;
use andytan07\LaravelSesTracker\LaravelSesTrackerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class UnitTestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'testbench']);
    }
    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return lasselehtinen\MyPackage\MyPackageServiceProvider
     */
    protected function getPackageProviders($app)
    {
        return [LaravelSesTrackerServiceProvider::class];
    }
    /**
     * Load package alias
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'SesMail' => SesMail::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('app.url', 'https://laravel-ses.com');
    }
}

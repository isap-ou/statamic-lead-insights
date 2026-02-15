<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Tests;

use Isapp\LeadInsights\ServiceProvider;
use Statamic\Addons\Manifest;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = ServiceProvider::class;

    /**
     * Set the addon edition to "pro" so all existing tests exercise Pro features.
     *
     * AddonTestCase::getEnvironmentSetUp() builds the manifest without the editions key,
     * so we patch it here and configure the runtime edition.
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        // Patch the manifest to include editions (AddonTestCase omits them)
        $manifest = $app->make(Manifest::class);
        $manifest->manifest['isapp/statamic-lead-insights']['editions'] = ['free', 'pro'];

        // Set the runtime edition to Pro for all tests
        $app['config']->set('statamic.editions.addons.isapp/statamic-lead-insights', 'pro');
    }
}

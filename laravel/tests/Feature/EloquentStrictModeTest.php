<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Illuminate\Database\Eloquent\Model;

afterEach(function () {
    app()->detectEnvironment(fn () => 'testing');
    Model::shouldBeStrict(! app()->isProduction());
});

it('enables Model::shouldBeStrict() in the current (testing) environment', function () {
    expect(app()->isProduction())->toBeFalse()
        ->and(Model::preventsLazyLoading())->toBeTrue()
        ->and(Model::preventsSilentlyDiscardingAttributes())->toBeTrue()
        ->and(Model::preventsAccessingMissingAttributes())->toBeTrue();
});

it('enables Model::shouldBeStrict() in non-production environments', function (string $environment) {
    Model::shouldBeStrict(false);
    app()->detectEnvironment(fn () => $environment);

    (new AppServiceProvider(app()))->boot();

    expect(app()->isProduction())->toBeFalse()
        ->and(Model::preventsLazyLoading())->toBeTrue()
        ->and(Model::preventsSilentlyDiscardingAttributes())->toBeTrue()
        ->and(Model::preventsAccessingMissingAttributes())->toBeTrue();
})->with([
    'local' => 'local',
    'development' => 'development',
    'dev' => 'dev',
    'staging' => 'staging',
    'testing' => 'testing',
]);

it('disables Model::shouldBeStrict() in production', function () {
    Model::shouldBeStrict(true);
    app()->detectEnvironment(fn () => 'production');

    (new AppServiceProvider(app()))->boot();

    expect(app()->isProduction())->toBeTrue()
        ->and(Model::preventsLazyLoading())->toBeFalse()
        ->and(Model::preventsSilentlyDiscardingAttributes())->toBeFalse()
        ->and(Model::preventsAccessingMissingAttributes())->toBeFalse();
});

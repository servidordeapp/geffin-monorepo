<?php

declare(strict_types=1);

use App\Modules\Tenancy\Models\Domain;

it('uses scheme and port from app.url in development', function () {
    config()->set('app.url', 'http://localhost:8080');

    $domain = new Domain(['domain' => 'escola-um.localhost']);

    expect($domain->url())->toBe('http://escola-um.localhost:8080');
});

it('omits the port for a standard https app.url', function () {
    config()->set('app.url', 'https://app.geffin.com');

    $domain = new Domain(['domain' => 'escola-um.geffin.com']);

    expect($domain->url())->toBe('https://escola-um.geffin.com');
});

it('returns the domain untouched when it already includes a scheme', function () {
    config()->set('app.url', 'http://localhost:8080');

    $domain = new Domain(['domain' => 'https://escola-um.com']);

    expect($domain->url())->toBe('https://escola-um.com');
});

it('does not append the app.url port when the domain already carries one', function () {
    config()->set('app.url', 'http://localhost:8080');

    $domain = new Domain(['domain' => 'escola-um.localhost:9000']);

    expect($domain->url())->toBe('http://escola-um.localhost:9000');
});

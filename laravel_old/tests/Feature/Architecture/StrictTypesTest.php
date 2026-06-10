<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

it('enforces declare(strict_types=1) in every PHP file', function () {
    $basePath = base_path();

    $finder = (new Finder())
        ->files()
        ->name('*.php')
        ->in([
            base_path('app'),
            base_path('bootstrap'),
            base_path('config'),
            base_path('database'),
            base_path('public'),
            base_path('routes'),
            base_path('tests'),
        ])
        ->exclude(['cache']);

    $offenders = [];

    foreach ($finder as $file) {
        $contents = $file->getContents();

        if (! preg_match('/^<\?php\s*(\/\*.*?\*\/\s*)*declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/s', $contents)) {
            $offenders[] = str_replace($basePath.'/', '', $file->getPathname());
        }
    }

    expect($offenders)->toBe(
        [],
        sprintf("The following files are missing `declare(strict_types=1);`:\n - %s", implode("\n - ", $offenders))
    );
});

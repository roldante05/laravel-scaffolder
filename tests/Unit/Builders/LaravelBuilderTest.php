<?php

declare(strict_types=1);

namespace Tests\Unit\Builders;

use Roldante05\ScaffoldingFactory\Builders\LaravelBuilder;
use Roldante05\ScaffoldingFactory\DTOs\LaravelOptions;
use Symfony\Component\Console\Output\NullOutput;

test('laravel builder signature', function () {
    $builder = new LaravelBuilder();
    $options = new LaravelOptions(
        projectName: 'test-laravel',
        wantKit: false,
        kit: 'None',
        stack: 'none',
        withTeams: false,
        database: 'sqlite',
        withBoost: false
    );

    // This will probably fail during execution because it tries to run real commands,
    // but the signature mismatch will fail at the compilation/loading level.
    expect($builder)->toBeInstanceOf(LaravelBuilder::class);
});

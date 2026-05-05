<?php

declare(strict_types=1);

namespace Tests\Unit\Interactions;

use Roldante05\ScaffoldingFactory\Interactions\LaravelInteractionHandler;
use Roldante05\ScaffoldingFactory\Interactions\InteractionHandlerInterface;

test('laravel interaction handler implements interface', function () {
    $handler = new LaravelInteractionHandler();
    expect($handler)->toBeInstanceOf(InteractionHandlerInterface::class);
});

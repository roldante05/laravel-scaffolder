<?php

declare(strict_types=1);

namespace Tests\Unit\Interactions;

use Roldante05\ScaffoldingFactory\Interactions\PhpVanillaInteractionHandler;
use Roldante05\ScaffoldingFactory\Interactions\InteractionHandlerInterface;

test('php vanilla interaction handler implements interface', function () {
    $handler = new PhpVanillaInteractionHandler();
    expect($handler)->toBeInstanceOf(InteractionHandlerInterface::class);
});

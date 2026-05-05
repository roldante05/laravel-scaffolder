<?php

declare(strict_types=1);

namespace Tests\Unit\Interactions;

use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;
use Roldante05\ScaffoldingFactory\Interactions\InteractionHandlerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

test('interaction handler interface contract', function () {
    $handler = new class implements InteractionHandlerInterface {
        public function handle(InputInterface $input, OutputInterface $output): ProjectOptions {
            return new class('test', 'sqlite') extends ProjectOptions {};
        }
    };

    expect($handler)->toBeInstanceOf(InteractionHandlerInterface::class);
});

<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\Interactions;

use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface InteractionHandlerInterface
{
    public function handle(InputInterface $input, OutputInterface $output): ProjectOptions;
}

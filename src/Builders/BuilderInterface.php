<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\Builders;

use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;
use Symfony\Component\Console\Output\OutputInterface;

interface BuilderInterface
{
    /**
     * Construir el proyecto basado en las opciones.
     */
    public function build(string $projectName, ProjectOptions $options, OutputInterface $output): int;
}

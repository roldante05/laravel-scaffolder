<?php

namespace Roldante05\ScaffoldingFactory\Builders;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface BuilderInterface
{
    /**
     * Preguntar al usuario por las opciones de configuración.
     */
    public function askOptions(InputInterface $input, OutputInterface $output, $questionHelper): array;

    /**
     * Construir el proyecto basado en las opciones.
     */
    public function build(string $projectName, array $options, OutputInterface $output): int;
}

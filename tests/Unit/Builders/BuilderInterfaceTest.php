<?php

declare(strict_types=1);

namespace Tests\Unit\Builders;

use Roldante05\ScaffoldingFactory\Builders\BuilderInterface;
use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;
use Symfony\Component\Console\Output\OutputInterface;

test('builder interface new contract', function () {
    $builder = new class implements BuilderInterface {
        public function build(string $projectName, ProjectOptions $options, OutputInterface $output): int {
            return 0;
        }
    };

    expect($builder)->toBeInstanceOf(BuilderInterface::class);
});

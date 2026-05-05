<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\DTOs;

abstract readonly class ProjectOptions
{
    public function __construct(
        public string $projectName,
        public string $database,
    ) {
    }
}

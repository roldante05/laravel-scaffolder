<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\DTOs;

readonly class LaravelOptions extends ProjectOptions
{
    public function __construct(
        string $projectName,
        public bool $wantKit,
        public string $kit,
        public string $stack,
        public bool $withTeams,
        string $database,
        public bool $withBoost,
    ) {
        parent::__construct($projectName, $database);
    }
}

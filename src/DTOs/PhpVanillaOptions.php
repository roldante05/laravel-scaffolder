<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\DTOs;

readonly class PhpVanillaOptions extends ProjectOptions
{
    public function __construct(
        string $projectName,
        string $database,
        public bool $login,
        public string $css,
    ) {
        parent::__construct($projectName, $database);
    }
}

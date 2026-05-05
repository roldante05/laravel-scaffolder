<?php

declare(strict_types=1);

namespace Tests\Unit\Builders;

use Roldante05\ScaffoldingFactory\Builders\PhpVanillaBuilder;
use Roldante05\ScaffoldingFactory\DTOs\PhpVanillaOptions;
use Symfony\Component\Console\Output\NullOutput;

test('php vanilla builder builds project structure', function () {
    $builder = new PhpVanillaBuilder();
    $options = new PhpVanillaOptions(
        projectName: 'test-vanilla-build',
        database: 'sqlite',
        login: true,
        css: 'Tailwind CSS'
    );

    $tmpDir = sys_get_temp_dir() . '/scaffolder-test-' . uniqid();
    mkdir($tmpDir);
    $oldCwd = getcwd();
    chdir($tmpDir);

    try {
        $result = $builder->build('test-vanilla-build', $options, new NullOutput());
        expect($result)->toBe(0);
        expect(is_dir('test-vanilla-build/src'))->toBeTrue();
        expect(file_exists('test-vanilla-build/index.php'))->toBeTrue();
    } finally {
        chdir($oldCwd);
        // Clean up would be good but let's keep it simple for now
    }
});

<?php

declare(strict_types=1);

use Roldante05\ScaffoldingFactory\Builders\LaravelBuilder;
use Roldante05\ScaffoldingFactory\DTOs\LaravelOptions;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

function getBuilder() {
    return new LaravelBuilder();
}

function callPrivateMethod($object, $methodName, ...$parameters) {
    $reflection = new ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);
    return $method->invoke($object, ...$parameters);
}

dataset('noise_patterns', [
    // Visual noise
    ['--- 10% ---', true],
    ['......', true],
    ['====================', true],
    ['####################', true],
    ['[####################]', true],
    ['Progress: 45%', true],

    // Composer noise
    ['Loading composer repositories with package information', true],
    ['Updating dependencies', true],
    ['Installing dependencies (including require-dev)', true],
    ['Writing lock file', true],
    ['Generating autoload files', true],
    ['Generating optimized autoload files', true],
    ['Nothing to install, update or remove', true],
    ['Packages you are using are looking for funding', true],

    // Laravel Installer / Artisan noise
    ['Application ready in 0.5s', true],
    ['Running database migrations...', true],
    ['Configuration cached successfully', true],
    ['Copying .env file', true],
    ['publishing [config] assets', true],
    ['➜ cd my-app', true],
    ['➜  cd my-app', true],
    ['New to Laravel? Check out our documentation.', true],
    ['Build something amazing!', true],
    ['WARN  TTY mode requires /dev/tty to be read/writable.', true],
    ['Lock file operations: 2 installs, 0 updates, 0 removals', true],
    ['Package operations: 2 installs, 0 updates, 0 removals', true],
    [' - Locking livewire/livewire (v3.8.0)', true],
    [' 0/2 [>---------------------------]   0%', true],
    ['    0 [>---------------------------]    0 [->--------------------------]', true],
    ['> Illuminate\\Foundation\\ComposerScripts::postAutoloadDump', true],
    ['> @php artisan package:discover --ansi', true],
    ['   INFO  Discovering packages.  ', true],
    ['  laravel/fortify ....................................................... DONE', true],
    ['<fg=gray>» Nothing to modify in lock file</>', true],
    ['<fg name="green">✔ Laravel project created</>', false],
    ['   INFO  No publishable resources for tag [laravel-assets].  ', true],

    // Sail / Docker noise
    ['Pulling mysql...', true],
    ['Pulled mysql', true],
    ['Downloading package...', true],
    ['Extracting files...', true],
    ['build-kit output line', true],

    // Meaningful output (not noise)
    ['✔ Laravel project created', false],
    ['• Configuring Laravel Sail', false],
    ['❌ Error: could not connect to database', false],
    ['⚠️ Warning: PHP version mismatch', false],
    ['Project Name: my-app', false],
    ['PHP Unit tests passed', false],
]);

test('isNoise correctly identifies noise patterns', function (string $line, bool $expected) {
    $builder = getBuilder();
    $result = callPrivateMethod($builder, 'isNoise', $line);
    expect($result)->toBe($expected);
})->with('noise_patterns');

test('runProcess filters noise in quiet mode', function () {
    $output = test()->createMock(OutputInterface::class);
    $builder = getBuilder();

    // Expect writeln to be called for meaningful output but not for noise
    $output->expects(test()->exactly(2))
           ->method('writeln')
           ->with(test()->callback(fn($line) => 
               str_contains($line, 'Meaningful output') || 
               str_contains($line, 'Another meaningful line')
           ));

    callPrivateMethod(
        $builder, 
        'runProcess',
        ['php', '-r', 'echo "Loading composer repositories\n"; echo "✔ Meaningful output\n"; echo "Updating dependencies\n"; echo "Another meaningful line\n";'],
        getcwd(),
        $output,
        false, // hideOutput
        false, // allowFailure
        true,  // quietMode
        false  // verboseMode
    );
});

test('runProcess shows all output in verbose mode', function () {
    $output = test()->createMock(OutputInterface::class);
    $builder = getBuilder();

    // Expect write to be called at least once with the full output
    $output->expects(test()->atLeastOnce())
           ->method('write')
           ->with(test()->stringContains('Loading composer repositories'));

    callPrivateMethod(
        $builder, 
        'runProcess',
        ['php', '-r', 'echo "Loading composer repositories\n"; echo "✔ Meaningful output\n"; echo "Updating dependencies\n"; echo "Another meaningful line\n";'],
        getcwd(),
        $output,
        false, // hideOutput
        false, // allowFailure
        false, // quietMode
        true   // verboseMode
    );
});

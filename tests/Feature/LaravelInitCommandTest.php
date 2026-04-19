<?php

use Roldante05\LaravelScaffolder\Console\LaravelInitCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

// ─── Tests del comando ────────────────────────────────────────────────────────

it('falla si no se proporciona el nombre del proyecto', function () {
    $app = new Application();
    $app->add(new LaravelInitCommand());
    $command = $app->find('init');
    $tester = new CommandTester($command);

    expect(fn () => $tester->execute([]))->toThrow(\RuntimeException::class);
});

it('muestra el resumen de configuración antes de ejecutar', function () {
    $app = new Application();
    $app->add(new LaravelInitCommand());
    $command = $app->find('init');
    $tester = new CommandTester($command);

    // Simular respuestas interactivas: cancelar en la confirmación final
    $tester->setInputs(['no', 'no', '0', '0', 'no']);
    $tester->execute(['project-name' => 'test-project']);

    $output = $tester->getDisplay();

    expect($output)
        ->toContain('test-project')
        ->toContain('Resumen de configuración')
        ->toContain('Operación cancelada');
});

// ─── Tests del sistema de templates ──────────────────────────────────────────

it('el stub de install.sh existe', function () {
    $stubPath = __DIR__ . '/../../src/Templates/install.sh.stub';
    expect(file_exists($stubPath))->toBeTrue();
});

it('el stub contiene los placeholders esperados', function () {
    $stub = file_get_contents(__DIR__ . '/../../src/Templates/install.sh.stub');

    expect($stub)
        ->toContain('{{USE_SAIL}}')
        ->toContain('{{/USE_SAIL}}')
        ->toContain('{{!USE_SAIL}}')
        ->toContain('{{USE_LIVEWIRE}}')
        ->toContain('{{USE_PEST}}')
        ->toContain('{{USE_VITE}}');
});

it('genera un install.sh con Sail habilitado correctamente', function () {
    $command = new LaravelInitCommand();
    $stub = file_get_contents(__DIR__ . '/../../src/Templates/install.sh.stub');

    // Usar reflexión para acceder al método protegido
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('processConditionalBlock');
    $method->setAccessible(true);

    $result = $stub;
    $result = $method->invoke($command, $result, 'USE_SAIL', true);
    $result = $method->invoke($command, $result, 'USE_LIVEWIRE', false);
    $result = $method->invoke($command, $result, 'USE_PEST', false);
    $result = $method->invoke($command, $result, 'USE_VITE', true);

    expect($result)
        ->toContain('vendor/bin/sail')
        ->toContain('command -v docker')
        ->not->toContain('{{USE_SAIL}}')
        ->not->toContain('php artisan serve');   // solo aparece cuando NO hay Sail
});

it('genera un install.sh sin Sail con comandos php y npm directos', function () {
    $command = new LaravelInitCommand();
    $stub = file_get_contents(__DIR__ . '/../../src/Templates/install.sh.stub');

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('processConditionalBlock');
    $method->setAccessible(true);

    $result = $stub;
    $result = $method->invoke($command, $result, 'USE_SAIL', false);
    $result = $method->invoke($command, $result, 'USE_LIVEWIRE', false);
    $result = $method->invoke($command, $result, 'USE_PEST', false);
    $result = $method->invoke($command, $result, 'USE_VITE', true);

    expect($result)
        ->toContain('php artisan key:generate')
        ->toContain('npm install')
        ->not->toContain('vendor/bin/sail')
        ->not->toContain('command -v docker');   // Docker no se chequea si no usa Sail
});

it('generate install.sh escribe el archivo y lo hace ejecutable', function () {
    $command = new LaravelInitCommand();
    $tmpDir = sys_get_temp_dir() . '/scaffolder-test-' . uniqid();
    mkdir($tmpDir);

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('generateInstallScript');
    $method->setAccessible(true);

    // Crear un OutputInterface dummy
    $output = new \Symfony\Component\Console\Output\NullOutput();

    $method->invoke($command, $tmpDir, [
        'useSail'     => true,
        'useLivewire' => true,
        'frontend'    => 'vite',
        'testing'     => 'pest',
    ], $output);

    $installPath = $tmpDir . '/install.sh';

    expect(file_exists($installPath))->toBeTrue();
    expect(is_executable($installPath))->toBeTrue();

    // Limpiar
    unlink($installPath);
    rmdir($tmpDir);
});

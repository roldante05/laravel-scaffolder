<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\Builders;

use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;
use Roldante05\ScaffoldingFactory\DTOs\LaravelOptions;
use Roldante05\ScaffoldingFactory\Helpers\StubProcessor;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LaravelBuilder implements BuilderInterface
{
    /** @var array<string> */
    private array $allowedDatabases = ['mysql', 'mariadb', 'pgsql', 'sqlite', 'sqlsrv', 'none'];
    /** @var array<string> */
    private array $allowedKits = ['Breeze', 'Jetstream', 'Official Starter Kit (2026)', 'None'];
    /** @var array<string> */
    private array $allowedStacks = ['livewire', 'vue', 'react', 'inertia', 'blade', 'none'];
    /** @var array<string> */
    private array $logBuffer = [];
    private int $maxLogLines = 5;

    public function build(string $projectName, ProjectOptions $options, OutputInterface $output): int
    {
        /** @var LaravelOptions $options */
        // Validate and sanitize inputs FIRST
        $sanitizedOptions = $this->sanitizeLaravelOptions($options);
        $sanitizedProjectName = $this->sanitizeProjectName($projectName);

        $projectPath = getcwd() . DIRECTORY_SEPARATOR . $sanitizedProjectName;

        try {
            // 0️⃣ Preparar secciones de salida (solo una sección para los logs dinámicos)
            $logSection = $output instanceof ConsoleOutputInterface ? $output->section() : $output;
            $historySection = $output;
            $statusSection = $output;

            // 1️⃣ Scaffold del proyecto Laravel
            $this->runStep(
                'Scaffolding project',
                fn() => $this->scaffoldProject($sanitizedProjectName, $statusSection, $logSection, $sanitizedOptions),
                'Laravel project created',
                $historySection,
                $statusSection,
                $logSection
            );

            // 2️⃣ Bootstrap JS
            $this->ensureBootstrap($projectPath);

            // 3️⃣ Sail
            $this->runStep(
                'Configuring Laravel Sail',
                fn() => $this->configureSailStep($projectPath, $sanitizedOptions, $logSection),
                'Laravel Sail configured',
                $historySection,
                $statusSection,
                $logSection
            );

            // 4️⃣ Auth Kit
            if ($sanitizedOptions->kit !== 'None') {
                $kitName = $sanitizedOptions->kit === 'Breeze' ? 'Laravel Breeze' : ($sanitizedOptions->kit === 'Official Starter Kit (2026)' ? 'Official Starter Kit' : 'Laravel Jetstream');
                $this->runStep(
                    "Installing {$kitName}",
                    fn() => $this->installAuthKit($projectPath, $sanitizedOptions, $logSection),
                    "{$kitName} installed",
                    $historySection,
                    $statusSection,
                    $logSection
                );
            }

            // 5️⃣ Boost (opcional)
            if ($sanitizedOptions->withBoost) {
                $this->runStep(
                    'Installing Laravel Boost',
                    fn() => $this->installBoost($projectPath, $logSection, $sanitizedOptions),
                    'Laravel Boost installed',
                    $historySection,
                    $statusSection,
                    $logSection
                );
            }

            // 6️⃣ Base de datos
            $this->runStep(
                'Setting database connection',
                fn() => $this->setDatabaseConnection($projectPath, $sanitizedOptions->database, $logSection),
                'Database connection set to ' . $sanitizedOptions->database,
                $historySection,
                $statusSection,
                $logSection
            );

            // 7️⃣ Script de instalación
            $this->runStep(
                'Generating installation script',
                fn() => $this->generateInstallScript($projectPath, $sanitizedOptions, $logSection),
                'Installation script generated',
                $historySection,
                $statusSection,
                $logSection
            );

            // 8️⃣ Permisos de node_modules
            $this->fixNodeModulesPermissions($projectPath, $statusSection, $sanitizedOptions);

            // 9️⃣ Mensaje final
            $this->showFinalInfo($statusSection, $sanitizedProjectName);

            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    /**
     * Sanitiza y valida las opciones de Laravel.
     *
     * @param LaravelOptions $options Las opciones a sanitizar
     * @return LaravelOptions Las opciones sanitizadas
     * @throws \InvalidArgumentException Si las opciones son inválidas
     */
    private function sanitizeLaravelOptions(LaravelOptions $options): LaravelOptions
    {
        // Validar y sanitizar la base de datos
        $database = strtolower($options->database);
        if (!in_array($database, $this->allowedDatabases, true)) {
            throw new \InvalidArgumentException("Database '{$options->database}' is not allowed. Allowed: " . implode(', ', $this->allowedDatabases));
        }

        // Validar y sanitizar el kit
        $kit = $options->kit;
        if (!in_array($kit, $this->allowedKits, true)) {
            throw new \InvalidArgumentException("Kit '{$options->kit}' is not allowed. Allowed: " . implode(', ', $this->allowedKits));
        }

        // Validar y sanitizar el stack
        $stack = strtolower($options->stack);
        if (!in_array($stack, $this->allowedStacks, true)) {
            throw new \InvalidArgumentException("Stack '{$options->stack}' is not allowed. Allowed: " . implode(', ', $this->allowedStacks));
        }

        // Validar y sanitizar withBoost (debe ser booleano)
        $withBoost = filter_var($options->withBoost, FILTER_VALIDATE_BOOL);
        if ($withBoost === null && $options->withBoost !== false && $options->withBoost !== '') {
            throw new \InvalidArgumentException("withBoost must be a boolean value");
        }

        // Validar y sanitizar withTeams (debe ser booleano)
        $withTeams = filter_var($options->withTeams, FILTER_VALIDATE_BOOL);
        if ($withTeams === null && $options->withTeams !== false && $options->withTeams !== '') {
            throw new \InvalidArgumentException("withTeams must be a boolean value");
        }

        // Retornar un nuevo objeto con valores sanitizados
        return new LaravelOptions(
            projectName: $options->projectName, // Se sanitiza por separado
            wantKit: $options->wantKit,
            kit: $kit,
            stack: $stack,
            withTeams: $withTeams,
            database: $database,
            withBoost: $withBoost,
            quiet: $options->quiet,
            verbose: $options->verbose
        );
    }

    /**
     * Sanitiza el nombre del proyecto para prevenir path traversal.
     *
     * @param string $projectName El nombre del proyecto a sanitizar
     * @return string El nombre del proyecto sanitizado
     * @throws \InvalidArgumentException Si el nombre del proyecto es inválido
     */
    private function sanitizeProjectName(string $projectName): string
    {
        // Reject any path separators to prevent directory traversal
        if (strpos($projectName, '/') !== false || strpos($projectName, '\\') !== false) {
            throw new \InvalidArgumentException("Project name '{$projectName}' contains invalid path separators.");
        }

        // Also reject if the name is empty, '.', or '..'
        if (empty($projectName) || $projectName === '.' || $projectName === '..') {
            throw new \InvalidArgumentException("Project name '{$projectName}' is invalid.");
        }

        // Verificar que solo contenga caracteres seguros (letras, números, guiones, guiones bajos)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $projectName)) {
            throw new \InvalidArgumentException("Project name '{$projectName}' contains invalid characters. Only letters, numbers, hyphens, and underscores are allowed.");
        }

        // Limitar la longitud para prevenir DoS
        if (strlen($projectName) > 50) {
            throw new \InvalidArgumentException("Project name is too long (maximum 50 characters)");
        }

        return $projectName;
    }


    /**
     * Runs a step with a status message and handles logs with a sliding window.
     *
     * @param string $title The initial title (e.g. "Configuring Sail")
     * @param callable $task The task to execute
     * @param string $successTitle The success message (e.g. "Sail configured")
     * @param OutputInterface $historySection Section for permanent history
     * @param OutputInterface $statusSection Section for current step status
     * @param OutputInterface $logSection Section for scrolling logs
     */
    private function runStep(
        string $title,
        callable $task,
        string $successTitle,
        OutputInterface $historySection,
        OutputInterface $statusSection,
        OutputInterface $logSection
    ): void {
        $this->logBuffer = [];

        // Escribimos el bullet de progreso
        $statusSection->writeln("   • {$title} ...");

        $task();

        // Limpiamos los logs al terminar
        if ($logSection instanceof ConsoleSectionOutput) {
            $logSection->clear();
        }

        // Si la terminal lo soporta, subimos una línea y la borramos para reemplazar el bullet
        // \033[1A = subir una línea, \033[2K = borrar línea entera
        if ($statusSection->isDecorated()) {
            $statusSection->write("\033[1A\033[2K");
        }

        $historySection->writeln("   <info>✔</info> {$successTitle}");
    }

    /**
     * Ejecuta un proceso en el directorio especificado con manejo de salida.
     *
     * @param array $command El comando y sus argumentos como array
     * @param string $workingDirectory El directorio donde ejecutar el comando
     * @param OutputInterface $output La salida donde se mostrará el progreso
     * @param bool $hideOutput Si se debe ocultar la salida del comando
     * @param bool $allowFailure Si se debe permitir que el comando falle sin lanzar excepción
     * @param bool $quietMode Si se debe filtrar la salida no esencial (ruido)
     * @param bool $verboseMode Si se debe mostrar toda la salida ignorando el filtrado
     * @throws \RuntimeException Si el proceso falla y $allowFailure es false
     */
    protected function runProcess(
        array $command,
        string $workingDirectory,
        OutputInterface $output,
        bool $hideOutput = false,
        bool $allowFailure = false,
        bool $quietMode = false,
        bool $verboseMode = false,
        array $env = []
    ): void {
        $process = new Process($command, $workingDirectory, $env);
        $process->setTimeout(null);

        $process->run(function ($type, $line) use ($output, $hideOutput, $quietMode, $verboseMode) {
            if ($hideOutput) {
                return;
            }

            $this->handleProcessOutput($type, $line, $output, $quietMode, $verboseMode);
        });

        if (!$process->isSuccessful() && !$allowFailure) {
            throw new \RuntimeException(
                sprintf(
                    'Process failed with exit code %d.' . PHP_EOL .
                    'Output: %s' . PHP_EOL .
                    'Error: %s',
                    $process->getExitCode(),
                    $process->getOutput(),
                    $process->getErrorOutput()
                )
            );
        }
    }

    /**
     * Handles output from a process, applying noise filtering and formatting.
     *
     * @param string $type Tipo de salida (STDOUT o STDERR).
     * @param string $line Línea de salida.
     * @param OutputInterface $output Salida donde mostrar el progreso.
     * @param bool $isQuiet Si se debe aplicar filtrado de ruido.
     * @param bool $isVerbose Si se debe mostrar toda la salida sin filtrar.
     */
    private function handleProcessOutput(string $type, string $line, OutputInterface $output, bool $isQuiet, bool $isVerbose): void
    {
        if ($isVerbose) {
            $output->write($line);
            return;
        }

        if ($isQuiet) {
            // Split chunk into lines to apply filtering accurately
            $lines = explode("\n", $line);
            foreach ($lines as $singleLine) {
                if (trim($singleLine) === '') {
                    continue;
                }
                if (!$this->isNoise($singleLine)) {
                    // Limpiamos etiquetas HTML y TODO tipo de código ANSI (colores y movimientos de cursor)
                    // \x1b\[ = ESC [, [0-9;?]* = parámetros, [A-Za-z] = comando (m=color, A=arriba, K=borrar línea, etc.)
                    $cleanOutput = preg_replace(['/<[^>]*>/', '/\x1b\[[0-9;?]*[A-Za-z]/'], '', $singleLine);
                    $formattedOutput = '      <fg=gray>» ' . $cleanOutput . '</>';

                    if ($output instanceof ConsoleSectionOutput) {
                        $this->logBuffer[] = $formattedOutput;
                        if (count($this->logBuffer) > $this->maxLogLines) {
                            array_shift($this->logBuffer);
                        }
                        $output->overwrite(implode("\n", $this->logBuffer));
                    } else {
                        $output->writeln($formattedOutput);
                    }
                }
            }
            return;
        }

        // Default mode: write as is (only if not a section, or use standard write)
        if (!$output instanceof ConsoleSectionOutput) {
            $output->write($line);
        }
    }

    /**
     * Ejecuta la creación del proyecto Laravel usando el instalador oficial.
     *
     * @param string $projectName Nombre del proyecto a crear (ya sanitizado).
     * @param OutputInterface $output Salida para mostrar progreso.
     * @param LaravelOptions $options Opciones de Laravel (ya sanitizadas).
     */
    protected function scaffoldProject(string $projectName, OutputInterface $statusSection, OutputInterface $logSection, LaravelOptions $options): void
    {
        $this->createLaravelProjectWithInstaller($projectName, $statusSection, $logSection, $options);
    }

    /**
     * Asegura que exista el archivo resources/js/bootstrap.js y su contenido básico.
     *
     * @param string $projectPath Ruta raíz del proyecto.
     */
    private function ensureBootstrap(string $projectPath): void
    {
        $this->ensureBootstrapJs($projectPath);
    }

    /**
     * Configura Laravel Sail según las opciones.
     *
     * @param string $projectPath Ruta raíz del proyecto.
     * @param LaravelOptions $options Opciones de Laravel (ya sanitizadas).
     * @param OutputInterface $historySection Sección de historial para mensajes de éxito.
     * @param OutputInterface $activeSection Sección activa para logs en tiempo real.
     */
    protected function configureSailStep(string $projectPath, LaravelOptions $options, OutputInterface $logSection): void
    {
        $this->runProcess(['composer', 'require', 'laravel/sail', '--dev', '--no-interaction', '--quiet'], $projectPath, $logSection, false, true, $options->isQuiet(), $options->isVerbose());
        $this->installSail($projectPath, $options, $logSection, $logSection);
    }

    /**
     * Instala el kit de autenticación seleccionado (Breeze, Jetstream o el kit oficial).
     *
     * @param string $projectPath Raíz del proyecto.
     * @param LaravelOptions $options Opciones de Laravel (ya sanitizadas).
     * @param OutputInterface $historySection Sección de historial para mensajes de éxito.
     * @param OutputInterface $activeSection Sección activa para logs en tiempo real.
     */
    protected function installAuthKit(string $projectPath, LaravelOptions $options, OutputInterface $logSection): void
    {
        $kit = $options->kit;

        if ($kit === 'Breeze' || $kit === 'Official Starter Kit (2026)') {
            $this->installBreezeOrOfficialKit($projectPath, $options, $logSection);
        } elseif ($kit === 'Jetstream') {
            $this->installJetstreamKit($projectPath, $options, $logSection);
        }

        if ($kit === 'Breeze' || $kit === 'Official Starter Kit (2026)' || $kit === 'Jetstream') {
            $this->fixJsDependencies($projectPath, $options->stack, $logSection);
        }
    }

    /**
     * Instala el kit de autenticación Breeze o el Kit Oficial.
     *
     * @param string $projectPath Raíz del proyecto.
     * @param LaravelOptions $options Opciones de Laravel (ya sanitizadas).
     * @param OutputInterface $historySection Sección de historial para mensajes de éxito.
     * @param OutputInterface $activeSection Sección activa para logs en tiempo real.
     */
    protected function installBreezeOrOfficialKit(string $projectPath, LaravelOptions $options, OutputInterface $logSection): void
    {
        $breezePath = $projectPath . '/vendor/laravel/breeze';
        if (!is_dir($breezePath)) {
            $this->runProcess(['composer', 'require', 'laravel/breeze', '--dev', '--no-interaction', '--quiet'], $projectPath, $logSection, false, true, $options->isQuiet(), $options->isVerbose());
            $breezeArgs = [$options->stack];
            $this->runProcess(
                array_merge(['php', 'artisan', 'breeze:install'], $breezeArgs),
                $projectPath,
                $logSection,
                false,
                true,
                $options->isQuiet(),
                $options->isVerbose(),
                ['NPM_CONFIG_LEGACY_PEER_DEPS' => 'true']
            );
        }
    }

    /**
     * Instala el kit de autenticación Jetstream.
     *
     * @param string $projectPath Raíz del proyecto.
     * @param LaravelOptions $options Opciones de Laravel (ya sanitizadas).
     * @param OutputInterface $historySection Sección de historial para mensajes de éxito.
     * @param OutputInterface $activeSection Sección activa para logs en tiempo real.
     */
    protected function installJetstreamKit(string $projectPath, LaravelOptions $options, OutputInterface $logSection): void
    {
        $jetstreamPath = $projectPath . '/vendor/laravel/jetstream';
        if (!is_dir($jetstreamPath)) {
            $this->runProcess(['composer', 'require', 'laravel/jetstream', '--no-interaction', '--quiet'], $projectPath, $logSection, false, true, $options->isQuiet(), $options->isVerbose());

            $process = new Process(['php', 'artisan', 'jetstream:install', $options->stack, '--no-interaction'], $projectPath, ['NPM_CONFIG_LEGACY_PEER_DEPS' => 'true']);
            $process->setTimeout(null);

            $isQuiet = $options->isQuiet();
            $isVerbose = $options->isVerbose();

            $process->run(function ($type, $line) use ($logSection, $isQuiet, $isVerbose) {
                $this->handleProcessOutput($type, $line, $logSection, $isQuiet, $isVerbose);
            });
        }
    }




    /**
     * Corrige los permisos de la carpeta node_modules si existe.
     *
     * @param string $projectPath Ruta raíz del proyecto.
     * @param OutputInterface $activeSection Sección activa para logs en tiempo real (se usa para posibles mensajes, aunque actualmente no se muestra nada).
     */
    protected function fixNodeModulesPermissions(string $projectPath, OutputInterface $activeSection, LaravelOptions $options): void
    {
        $nodeModulesPath = $projectPath . DIRECTORY_SEPARATOR . 'node_modules';
        if (is_dir($nodeModulesPath)) {
            // Usar permisos más seguros: propietario puede leer/escribir/ejecutar, grupo y otros solo leer y ejecutar
            $this->runProcess(['chmod', '-R', '755', 'node_modules'], $projectPath, $activeSection, false, false, $options->isQuiet(), $options->isVerbose());
        }
    }

    /**
     * Muestra la información final después de la generación exitosa.
     *
     * @param OutputInterface $activeSection Sección activa para imprimir los mensajes finales.
     * @param string $projectName Nombre del proyecto generado (ya sanitizado).
     */
    protected function showFinalInfo(OutputInterface $activeSection, string $projectName): void
    {
        $activeSection->writeln('');
        $activeSection->writeln('<info>🎉 Project generated successfully!</info>');
        $activeSection->writeln('<info>📝 Next steps:</info>');
        $activeSection->writeln('   1. cd ' . $projectName);
        $activeSection->writeln('   2. scripts/install.sh');
    }

    /**
     * Creates a Laravel project using the official installer.
     * This method has been refactored to improve readability and testability.
     *
     * @param string $projectName Nombre del proyecto a crear (ya sanitizado).
     * @param OutputInterface $output Output para mostrar progreso.
     * @param LaravelOptions $options Opciones de Laravel (ya sanitizadas).
     */
    protected function createLaravelProjectWithInstaller(string $projectName, OutputInterface $statusSection, OutputInterface $logSection, LaravelOptions $options): void
    {
        $this->checkForLaravelInstallerUpdates($logSection, $options);
        $this->setComposerPathInEnvironment();
        $this->createLaravelProject($projectName, $logSection, $options);
    }

    protected function checkForLaravelInstallerUpdates(OutputInterface $logSection, LaravelOptions $options): void
    {
        $this->runProcess(['composer', 'global', 'require', 'laravel/installer', '--no-interaction', '--quiet'], getcwd(), $logSection, false, true, $options->isQuiet(), $options->isVerbose());
    }

    /**
     * Determines if a line of output is noise that should be filtered in quiet mode.
     * Consolidates patterns from Composer, Laravel installer, Artisan, and Docker/Sail.
     *
     * Patterns filtered:
     * - Visual noise (progress bars, separators)
     * - Routine Composer messages (loading repos, writing lock file)
     * - Laravel Artisan routine success messages (configuration cached, migrations running)
     * - Docker/Sail routine pulling/building logs
     *
     * @param string $line A line of output from various processes.
     * @return bool True if the line is noise, false otherwise.
     */
    protected function isNoise(string $line): bool
    {
        // Strip Symfony tags and ANSI escape sequences for better pattern matching
        $cleanLine = preg_replace(['/<[^>]*>/', '/\x1b\[[0-9;]*m/'], '', $line);

        $noisePatterns = [
            // Process/Visual noise
            '/^(?:\s*)?[-]{3,}(?:\s*)(?:[\d.]+%?)?(?:\s*)?[-]{3,}$/',
            '/^(?:\s*)?[.]{3,}(?:\s*)?[.]{3,}(?:\s*)?$/',
            '/^(?:\s*)?={3,}(?:\s*)?={3,}(?:\s*)?$/',
            '/^(?:\s*)?#{3,}(?:\s*)?#{3,}(?:\s*)?$/',
            '/^(?:\s*)?\[.{20,}\](?:\s*)?$/',
            '/^(?:\s*)?Progress: \d+(?:\.\d+)?%(?:\s*)?$/',

            // Composer noise
            'Loading composer repositories',
            'Updating dependencies',
            'Installing dependencies',
            'Writing lock file',
            'Generating autoload files',
            'Generating optimized autoload files',
            'Generated optimized autoload files',
            'Nothing to install, update or remove',
            'Packages you are using are looking for funding',
            'Use the `composer fund` command',
            'No security vulnerability advisories found',
            'Using version',
            './composer.json has been updated',
            'Running composer update',
            'Changed current directory',
            'Info from https://repo.packagist.org:',
            'Executing command',
            'Executing script',
            'Discovered Package:',
            'Package manifest generated successfully',
            'Lock file operations',
            'Package operations',
            'Nothing to modify in lock file',
            '/\[[>=-]*\]/i',

            // Laravel Installer / Artisan noise
            'Application ready in',
            'Running database migrations',
            'Configuration cached successfully',
            'Route cached successfully',
            'Filesystem linked successfully',
            'Copying .env',
            'publishing [config]',
            '/➜\s+cd/u',
            'New to Laravel?',
            'Check out our documentation',
            'Build something amazing!',
            'WARN  TTY mode requires /dev/tty',
            'INFO  Discovering packages',
            'INFO  No publishable resources',
            'INFO  Installing and building Node dependencies',
            '/^> /',
            '/^  [\w\/-]+(?:\s+\.+\s*|\s+)DONE/i',

            // Sail / Docker noise
            'Pulling',
            'Pulled',
            'Downloading',
            'Verifying archive integrity',
            'All good!',
            'build-kit',
            'context:',
            'dockerfile:',
            'COPY',
            'RUN',
            'ENTRYPOINT',

            // Webpack / Assets noise
            '[webpack] building modules',
            '[webpack] optimization stages',
            '[webpack] emit',
            '[webpack] done',
            'webpack compiled with',
            'webpack compiled successfully',
            'asset unchanged',
            'Entrypoint',
            'runtime modules',
            '[built]',
        ];

        foreach ($noisePatterns as $pattern) {
            // Check if pattern is a regex (starts with /)
            if (str_starts_with($pattern, '/')) {
                if (@preg_match($pattern, $cleanLine)) {
                    return true;
                }
            } else {
                if (stripos($cleanLine, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Sets the Composer path in the environment variables.
     */
    private function setComposerPathInEnvironment(): void
    {
        $home = getenv('HOME');
        putenv("PATH={$home}/.config/composer/vendor/bin:{$home}/.composer/vendor/bin:" . getenv('PATH'));
    }

    /**
     * Creates a new Laravel project using the installer.
     *
     * @param string $projectName The name of the project.
     * @param OutputInterface $output The output section for logs.
     * @param LaravelOptions $options The options for noise filtering.
     */
    private function createLaravelProject(string $projectName, OutputInterface $output, LaravelOptions $options): void
    {
        $this->runProcess(
            ['laravel', 'new', $projectName, '--no-interaction', '--quiet'],
            getcwd(),
            $output,
            false, // hideOutput
            false, // allowFailure
            $options->isQuiet(),
            $options->isVerbose()
        );
    }


    /**
     * Asegura que exista el archivo resources/js/bootstrap.js y su contenido básico.
     *
     * @param string $projectPath Ruta raíz del proyecto.
     */
    protected function ensureBootstrapJs(string $projectPath): void
    {
        $jsPath = $projectPath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'js';
        if (!is_dir($jsPath)) {
            // Usar permisos más seguros: 755 en lugar de 0755 (aunque 0755 es lo mismo, ser explícito)
            @mkdir($jsPath, 0755, true);
        }
        $bootstrapPath = $jsPath . DIRECTORY_SEPARATOR . 'bootstrap.js';
        if (!file_exists($bootstrapPath)) {
            $bootstrapContent = "import axios from 'axios';\nwindow.axios = axios;\nwindow.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';\n";
            // No suprimir el error, pero si falla, continuamos (no es crítico)
            $result = @file_put_contents($bootstrapPath, $bootstrapContent);
            if ($result === false) {
                // Log the error but don't fail - this is a minor issue
                // In a real application, we might want to use a proper logger
            }
        }
    }

    protected function installSail(string $projectPath, LaravelOptions $options, OutputInterface $activeSection, OutputInterface $detailSection): void
    {
        $database = $options->database; // Ya sanitizado
        $sailServices = $this->getSailServicesForDatabase($database);

        $sailCommand = $this->buildSailCommand($sailServices);

        $this->runProcess($sailCommand, $projectPath, $activeSection, false, true, $options->isQuiet(), $options->isVerbose());
        $this->customizeComposeFile($projectPath, $database, $activeSection);
    }

    /**
     * Gets the Sail services to install based on the database type.
     *
     * @param string $database El tipo de base de datos (ya sanitizado).
     * @return array Lista de services to include with Sail.
     */
    private function getSailServicesForDatabase(string $database): array
    {
        $nativeSailDatabases = ['mysql', 'mariadb', 'pgsql'];
        if (in_array($database, $nativeSailDatabases, true)) {
            return [$database];
        }

        return [];
    }

    /**
     * Builds the Sail install command with the appropriate services.
     *
     * @param array $sailServices Lista de services to include.
     * @return array El comando Sail con los argumentos de service.
     */
    private function buildSailCommand(array $sailServices): array
    {
        $sailCommand = ['php', 'artisan', 'sail:install', '--no-interaction'];
        if (!empty($sailServices)) {
            $sailCommand[] = '--with=' . implode(',', $sailServices);
        } else {
            $sailCommand[] = '--with=';
        }

        return $sailCommand;
    }

    protected function installBoost(string $projectPath, OutputInterface $logSection, LaravelOptions $options): void
    {
        try {
            $this->runProcess(['composer', 'require', 'laravel/boost', '--dev', '--no-interaction', '--quiet'], $projectPath, $logSection, false, true, $options->isQuiet(), $options->isVerbose());
            $this->runProcess(['php', 'artisan', 'boost:install'], $projectPath, $logSection, false, true, $options->isQuiet(), $options->isVerbose());
        } catch (\Exception $e) {
            // Silently fail or log to buffer
            $this->handleProcessOutput(Process::ERR, 'Laravel Boost installation failed. Continuing...', $logSection, $options->isQuiet(), $options->isVerbose());
        }
    }

    protected function generateInstallScript(string $projectPath, LaravelOptions $options, OutputInterface $output): void
    {
        $stubPath = __DIR__ . '/../Templates/laravel/install.sh.stub';
        if (!file_exists($stubPath)) {
            $output->writeln('<error>❌ Template not found</error>');
            return;
        }

        $database = $options->database; // Ya sanitizado
        $stub = file_get_contents($stubPath);
        $tags = [
            'USE_SAIL' => true,
            'USE_SQLSRV' => $database === 'sqlsrv',
            'USE_BREEZE' => $options->kit === 'Breeze',
            'USE_JETSTREAM' => $options->kit === 'Jetstream',
            'USE_STARTER_KIT' => $options->kit === 'Official Starter Kit (2026)',
        ];
        $variables = [
            'PROJECT_NAME' => basename($projectPath),
            'DB_SERVICE' => $database,
            'BREEZE_STACK' => $options->stack,
            'JETSTREAM_STACK' => $options->stack,
        ];

        $scriptsDir = $projectPath . '/scripts';
        if (!is_dir($scriptsDir)) {
            mkdir($scriptsDir, 0755, true);
        }

        $content = StubProcessor::process($stub, $variables, $tags);
        file_put_contents($scriptsDir . '/install.sh', $content);
        chmod($scriptsDir . '/install.sh', 0755);
    }

    protected function fixJsDependencies(string $projectPath, string $stack, OutputInterface $output): void
    {
        $packageJsonPath = $projectPath . '/package.json';
        if (!file_exists($packageJsonPath)) {
            return;
        }

        $packageJson = json_decode(file_get_contents($packageJsonPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        if (!isset($packageJson['dependencies'])) {
            $packageJson['dependencies'] = [];
        }
        if (!isset($packageJson['devDependencies'])) {
            $packageJson['devDependencies'] = [];
        }

        if (!isset($packageJson['dependencies']['axios']) || empty($packageJson['dependencies']['axios'])) {
            $packageJson['dependencies']['axios'] = '^1.6.0';
        }

        $stack = strtolower($stack); // Ya debería estar sanitizado, pero lo aseguramos
        if (in_array($stack, ['react', 'vue', 'inertia'], true)) {
            // Force Vite version to avoid ERESOLVE conflicts with older plugins
            $packageJson['devDependencies']['vite'] = '^7.0.0';

            // Add overrides to force the version across the entire tree
            if (!isset($packageJson['overrides'])) {
                $packageJson['overrides'] = [];
            }
            $packageJson['overrides']['vite'] = '$vite';

            $output->writeln('   <info>✔</info> Adjusted vite version and added overlays to resolve dependency conflicts');
        }

        file_put_contents($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function customizeComposeFile(string $projectPath, string $database, OutputInterface $output): void
    {
        $composeFile = $projectPath . '/compose.yaml';
        if (!file_exists($composeFile)) {
            return;
        }

        $composeContent = file_get_contents($composeFile);
        $servicesToRemove = match ($database) {
            'sqlite' => ['mysql', 'redis'],
            default => [],
        };

        foreach ($servicesToRemove as $service) {
            $pattern = '/^\s*' . preg_quote($service, '/') . ':\s*$\n(?:^\s{2,}.*\n)*/m';
            $composeContent = preg_replace($pattern, '', $composeContent);
        }

        if (!empty($servicesToRemove)) {
            $composeContent = preg_replace('/^(\s+)depends_on:\s*\n(?:\1\s+-[^\n]*\n)*/m', '', $composeContent);
        }

        $orphanVolumes = match ($database) {
            'sqlite' => ['sail-mysql', 'sail-redis'],
            default => [],
        };

        foreach ($orphanVolumes as $vol) {
            $composeContent = preg_replace('/^\s*' . preg_quote($vol, '/') . ':\s*\n(?:\s+driver:.*\n)?/m', '', $composeContent);
        }

        $composeContent = preg_replace('/^volumes:\s*\n(?:\s*\n)*(?=\S|$)/m', '', $composeContent);
        file_put_contents($composeFile, $composeContent);
    }

    protected function setDatabaseConnection(string $projectPath, string $database, OutputInterface $output): void
    {
        $envPath = $projectPath . '/.env';
        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);
        $dbVars = [
            'DB_CONNECTION' => $database,
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ];

        foreach ($dbVars as $var => $value) {
            $pattern = "/^{$var}=.*/m";
            $newLine = "{$var}={$value}";

            if (preg_match($pattern, $envContent)) {
                if ($database === 'sqlite' || $var === 'DB_CONNECTION') {
                    $envContent = preg_replace($pattern, $newLine, $envContent);
                }
            } else {
                $envContent .= "\n{$newLine}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
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
    public function build(string $projectName, ProjectOptions $options, OutputInterface $output): int
    {
        /** @var LaravelOptions $options */
        $projectPath = getcwd() . DIRECTORY_SEPARATOR . $projectName;

        try {
            // 1. Create Laravel project using official installer
            $output->writeln(' • Scaffolding project');
            $this->createLaravelProjectWithInstaller($projectName, $output);

            // Create sections for a persistent dashboard experience for the remaining steps
            // historySection: Keeps the list of completed steps (append-only)
            // activeSection: Shows the current task and technical logs (transient)
            $historySection = $output instanceof ConsoleOutputInterface ? $output->section() : $output;
            $activeSection = $output instanceof ConsoleOutputInterface ? $output->section() : $output;

            $historySection->writeln('   <fg=green>✔</> Laravel project created');

            // 2. Ensure resources/js/bootstrap.js exists
            $this->ensureBootstrapJs($projectPath);

            // 3. Install Sail
            $activeSection->writeln('   • Configuring Laravel Sail');
            $this->runProcess(['composer', 'require', 'laravel/sail', '--dev', '--no-interaction', '--quiet'], $projectPath, $activeSection, false, true);

            $this->installSail($projectPath, $options, $activeSection, $activeSection);
            $activeSection->clear();
            $historySection->writeln('   <fg=green>✔</> Laravel Sail configured');

            // 4. Install Auth Kit
            $this->installAuthKit($projectPath, $options, $historySection, $activeSection);

            // 5. Install Laravel Boost
            if ($options->withBoost) {
                $activeSection->writeln('   • Installing Laravel Boost');
                $this->installBoost($projectPath, $activeSection, $activeSection);
                $activeSection->clear();
                $historySection->writeln('   <fg=green>✔</> Laravel Boost installed');
            }

            // 6. Set database connection
            $activeSection->writeln('   • Setting database connection');
            $this->setDatabaseConnection($projectPath, $options->database, $activeSection);
            $activeSection->clear();
            $historySection->writeln('   <fg=green>✔</> Database connection set to ' . $options->database);

            // 7. Generate install.sh
            $activeSection->writeln('   • Generating installation script');
            $this->generateInstallScript($projectPath, $options, $activeSection);
            $activeSection->clear();
            $historySection->writeln('   <fg=green>✔</> Installation script generated');

            // Fix node_modules permissions
            $nodeModulesPath = $projectPath . DIRECTORY_SEPARATOR . 'node_modules';
            if (is_dir($nodeModulesPath)) {
                $this->runProcess(['chmod', '-R', 'a+rX', 'node_modules'], $projectPath, $activeSection);
            }

            $activeSection->clear();
            $activeSection->writeln('');
            $activeSection->writeln('<info>🎉 Project generated successfully!</info>');
            $activeSection->writeln('<info>📝 Next steps:</info>');
            $activeSection->writeln('   1. cd ' . $projectName);
            $activeSection->writeln('   2. ./install.sh');

            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    protected function ensureBootstrapJs(string $projectPath): void
    {
        $jsPath = $projectPath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'js';
        if (!is_dir($jsPath)) {
            @mkdir($jsPath, 0755, true);
        }
        $bootstrapPath = $jsPath . DIRECTORY_SEPARATOR . 'bootstrap.js';
        if (!file_exists($bootstrapPath)) {
            $bootstrapContent = "import axios from 'axios';\nwindow.axios = axios;\nwindow.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';\n";
            @file_put_contents($bootstrapPath, $bootstrapContent);
        }
    }

    protected function installSail(string $projectPath, LaravelOptions $options, OutputInterface $activeSection, OutputInterface $detailSection): void
    {
        $database = $options->database;
        $sailServices = [];

        $nativeSailDatabases = ['mysql', 'mariadb', 'pgsql'];
        if (in_array($database, $nativeSailDatabases, true)) {
            $sailServices[] = $database;
        }

        $sailCommand = ['php', 'artisan', 'sail:install', '--no-interaction'];
        if (!empty($sailServices)) {
            $sailCommand[] = '--with=' . implode(',', $sailServices);
        } else {
            $sailCommand[] = '--with=';
        }

        $this->runProcess($sailCommand, $projectPath, $activeSection, false, true);
        $this->customizeComposeFile($projectPath, $database, $activeSection);
    }

    protected function installAuthKit(string $projectPath, LaravelOptions $options, OutputInterface $historySection, OutputInterface $activeSection): void
    {
        $kit = $options->kit;
        if ($kit === 'Breeze' || $kit === 'Official Starter Kit (2026)') {
            $kitName = $kit === 'Breeze' ? 'Laravel Breeze' : 'Official Starter Kit (2026)';
            $activeSection->writeln("   • Ensuring {$kitName} is installed");

            $breezePath = $projectPath . '/vendor/laravel/breeze';
            if (!is_dir($breezePath)) {
                $this->runProcess(['composer', 'require', 'laravel/breeze', '--dev', '--no-interaction', '--quiet'], $projectPath, $activeSection, false, true);
                $breezeArgs = [$options->stack];

                // Set env to avoid NPM ERESOLVE issues during artisan's internal npm install
                $process = new Process(array_merge(['php', 'artisan', 'breeze:install'], $breezeArgs), $projectPath, ['NPM_CONFIG_LEGACY_PEER_DEPS' => 'true']);
                $process->setTimeout(null);
                $process->run(function ($type, $line) use ($activeSection) {
                    if (!$activeSection instanceof ConsoleSectionOutput) {
                        $activeSection->write($line);
                    }
                });

                if (!$process->isSuccessful()) {
                    $historySection->writeln('   <fg=yellow>⚠</> Breeze install finished with some warnings (likely NPM)');
                } else {
                    $activeSection->clear();
                    $historySection->writeln("   <fg=green>✔</> {$kitName} installed");
                }
            } else {
                $activeSection->clear();
                $historySection->writeln("   <fg=green>✔</> {$kitName} already installed");
            }

            $this->fixJsDependencies($projectPath, $options->stack, $historySection);
        } elseif ($kit === 'Jetstream') {
            $activeSection->writeln('   • Ensuring Laravel Jetstream is installed');

            $jetstreamPath = $projectPath . '/vendor/laravel/jetstream';
            if (!is_dir($jetstreamPath)) {
                $this->runProcess(['composer', 'require', 'laravel/jetstream', '--no-interaction', '--quiet'], $projectPath, $activeSection, false, true);

                // Set env to avoid NPM ERESOLVE issues during artisan's internal npm install
                $process = new Process(['php', 'artisan', 'jetstream:install', $options->stack, '--no-interaction'], $projectPath, ['NPM_CONFIG_LEGACY_PEER_DEPS' => 'true']);
                $process->setTimeout(null);
                $process->run(function ($type, $line) use ($activeSection) {
                    if (!$activeSection instanceof ConsoleSectionOutput) {
                        $activeSection->write($line);
                    }
                });

                if (!$process->isSuccessful()) {
                    $historySection->writeln('   <fg=yellow>⚠</> Jetstream install finished with some warnings (likely NPM)');
                } else {
                    $activeSection->clear();
                    $historySection->writeln('   <fg=green>✔</> Laravel Jetstream installed');
                }
            } else {
                $activeSection->clear();
                $historySection->writeln('   <fg=green>✔</> Laravel Jetstream already installed');
            }

            $this->fixJsDependencies($projectPath, $options->stack, $historySection);
        }
    }

    protected function installBoost(string $projectPath, OutputInterface $headerSection, OutputInterface $detailSection): void
    {
        $headerSection->writeln('   • Installing Laravel Boost for AI assisted coding');
        try {
            $this->runProcess(['composer', 'require', 'laravel/boost', '--dev', '--no-interaction', '--quiet'], $projectPath, $detailSection, false, true);
            $this->runProcess(['php', 'artisan', 'boost:install'], $projectPath, $detailSection, false, true);
        } catch (\Exception $e) {
            $headerSection->writeln('<warning>⚠️ Laravel Boost installation failed. Continuing without it...</warning>');
        }
    }

    protected function createLaravelProjectWithInstaller(string $projectName, OutputInterface $output): void
    {
        $output->writeln('   • Checking for Laravel installer updates');
        $process = new Process(['composer', 'global', 'require', 'laravel/installer']);
        $process->setTimeout(null);

        $process->run(function ($type, $line) use ($output) {
            $isNoise = (
                strpos($line, 'Loading composer repositories') !== false ||
                strpos($line, 'Updating dependencies') !== false ||
                strpos($line, 'Installing dependencies') !== false ||
                strpos($line, 'Writing lock file') !== false ||
                strpos($line, 'Generating autoload files') !== false ||
                strpos($line, 'Nothing to install, update or remove') !== false ||
                strpos($line, 'packages you are using are looking for funding') !== false ||
                strpos($line, 'Use the `composer fund` command') !== false ||
                strpos($line, 'No security vulnerability advisories found') !== false ||
                strpos($line, 'Using version') !== false ||
                strpos($line, './composer.json has been updated') !== false ||
                strpos($line, 'Running composer update') !== false ||
                strpos($line, 'Changed current directory') !== false
            );

            if (!$isNoise && trim($line) !== '') {
                $output->writeln('      <fg=gray>» ' . trim($line) . '</>');
            }
        });

        $home = getenv('HOME');
        putenv("PATH={$home}/.config/composer/vendor/bin:{$home}/.composer/vendor/bin:" . getenv('PATH'));

        $process = new Process(['laravel', 'new', $projectName, '--no-interaction']);
        $process->setTimeout(null);

        $skipBlock = false;
        $process->run(function ($type, $line) use ($output, &$skipBlock) {
            if (strpos($line, 'Running database migrations') !== false || strpos($line, 'Application ready in') !== false) {
                $skipBlock = true;
            }
            if (!$skipBlock) {
                $output->write($line);
            }
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("laravel new failed: " . $process->getErrorOutput());
        }
    }

    protected function runProcess(array $command, ?string $cwd, OutputInterface $output, bool $returnStatus = false, bool $silent = false): bool
    {
        $process = new Process($command);
        if ($cwd) {
            $process->setWorkingDirectory($cwd);
        }
        $process->setTimeout(null);

        $commandString = implode(' ', $command);
        $isMigrationCommand = (strpos($commandString, 'migrate') !== false && strpos($commandString, 'artisan') !== false);
        $isLaravelNew = (isset($command[0]) && $command[0] === 'laravel' && $command[1] === 'new');

        $process->run(function ($type, $line) use ($output, $isMigrationCommand, $isLaravelNew, $silent) {
            if ($silent) {
                return;
            }
            if ($isLaravelNew && (strpos($line, 'database migrations') !== false || strpos($line, 'Error output:') !== false || strpos($line, 'artisan migrate') !== false)) {
                return;
            }

            if ($isMigrationCommand && $type === Process::ERR) {
                return;
            }

            if ($output instanceof ConsoleSectionOutput) {
                $cleanLine = trim($line);
                if (!empty($cleanLine)) {
                    $output->overwrite('  <fg=gray>» ' . $cleanLine . '</>');
                }
                return;
            }

            $isNoise = !$isLaravelNew && (
                strpos($line, 'Loading composer repositories') !== false ||
                strpos($line, 'Updating dependencies') !== false ||
                strpos($line, 'Installing dependencies') !== false ||
                strpos($line, 'Writing lock file') !== false ||
                strpos($line, 'Generating optimized autoload files') !== false ||
                strpos($line, 'Package operations:') !== false ||
                strpos($line, '- Installing') !== false ||
                strpos($line, 'TTY mode requires /dev/tty') !== false ||
                (strpos($line, 'Image') !== false && (strpos($line, 'Pulling') !== false || strpos($line, 'Pulled') !== false))
            );

            if ($isNoise) {
                $output->write("\r  <info>Processing...</info>");
                return;
            }

            if (!$isLaravelNew) {
                $output->write("\r\033[K");
            }
            $output->write($line);
        });

        $success = $process->isSuccessful();
        if (!$success && !$returnStatus && !$isMigrationCommand) {
            throw new ProcessFailedException($process);
        }

        return $success;
    }

    protected function generateInstallScript(string $projectPath, LaravelOptions $options, OutputInterface $output): void
    {
        $stubPath = __DIR__ . '/../Templates/laravel/install.sh.stub';
        if (!file_exists($stubPath)) {
            $output->writeln('<error>❌ Template not found</error>');
            return;
        }

        $database = $options->database;
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

        $content = StubProcessor::process($stub, $variables, $tags);
        file_put_contents($projectPath . '/install.sh', $content);
        chmod($projectPath . '/install.sh', 0755);
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

        $stack = strtolower($stack);
        if (in_array($stack, ['react', 'vue', 'inertia'], true)) {
            // Force Vite version to avoid ERESOLVE conflicts with older plugins
            $packageJson['devDependencies']['vite'] = '^7.0.0';

            // Add overrides to force the version across the entire tree
            if (!isset($packageJson['overrides'])) {
                $packageJson['overrides'] = [];
            }
            $packageJson['overrides']['vite'] = '$vite';

            $output->writeln('   <fg=green>✔</> Adjusted vite version and added overrides to resolve dependency conflicts');
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
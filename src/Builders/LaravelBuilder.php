<?php

namespace Roldante05\ScaffoldingFactory\Builders;

use Roldante05\ScaffoldingFactory\Helpers\StubProcessor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LaravelBuilder implements BuilderInterface
{
    public function askOptions(InputInterface $input, OutputInterface $output, $helper): array
    {
        $options = [];
        $isTty = stream_isatty(STDIN);

        if ($isTty) {
            info('🔐 Authentication & Ecosystem');

            $wantKit = confirm(
                label: 'Do you want to install a starter kit?',
                default: true,
                hint: 'Starter kits provide a pre-built authentication system (Breeze or Jetstream).'
            );
            $options['wantKit'] = $wantKit;

            if (PHP_OS_FAMILY !== 'Windows') {
                $options['withBoost'] = confirm(
                    label: 'Install Laravel Boost for AI assisted coding?',
                    default: true,
                    hint: 'Provides documentation API and MCP servers for AI agents.'
                );
            } else {
                $options['withBoost'] = false;
            }

            if ($options['wantKit']) {
                $kit = select(
                    label: 'Which starter kit would you like to use?',
                    options: [
                        'Breeze' => 'Breeze (Minimal & Elegant)',
                        'Jetstream' => 'Jetstream (Advanced Features)',
                        'Official Starter Kit (2026)' => 'Official Starter Kit (2026)'
                    ],
                    default: 'Breeze'
                );
                $options['kit'] = $kit;

                if ($options['kit'] === 'Breeze') {
                    $breezeStack = select(
                        label: 'Which Breeze stack would you like to use?',
                        options: [
                            'Blade' => 'Blade (Classic)',
                            'Livewire' => 'Livewire (Full-stack PHP)',
                            'React (Inertia)' => 'React (Modern SPA)',
                            'Vue (Inertia)' => 'Vue (Modern SPA)'
                        ],
                        default: 'Blade'
                    );

                    $options['stack'] = match ($breezeStack) {
                        'Blade' => 'blade',
                        'Livewire' => 'livewire',
                        'React (Inertia)' => 'react',
                        'Vue (Inertia)' => 'vue',
                    };

                    $options['withTeams'] = confirm(
                        label: 'Would you like to include team support?',
                        default: false
                    );
                } elseif ($options['kit'] === 'Jetstream') {
                    $jetstreamStack = select(
                        label: 'Which Jetstream stack would you like to use?',
                        options: [
                            'Livewire' => 'Livewire',
                            'Inertia (Vue)' => 'Inertia (Vue)'
                        ],
                        default: 'Livewire'
                    );
                    $options['stack'] = $jetstreamStack === 'Livewire' ? 'livewire' : 'inertia';
                    $options['withTeams'] = false;
                } elseif ($options['kit'] === 'Official Starter Kit (2026)') {
                    $officialKit = select(
                        label: 'Which official starter kit would you like to use?',
                        options: [
                            'Livewire (Flux UI)' => 'Livewire (Flux UI)',
                            'React (shadcn)' => 'React (shadcn)',
                            'Vue (shadcn-vue)' => 'Vue (shadcn-vue)'
                        ],
                        default: 'Livewire (Flux UI)'
                    );

                    $options['stack'] = match (true) {
                        str_contains($officialKit, 'Livewire') => 'livewire',
                        str_contains($officialKit, 'React') => 'react',
                        default => 'vue'
                    };
                    $options['withTeams'] = false;
                }
            } else {
                $options['kit'] = 'None';
                $options['stack'] = 'none';
                $options['withTeams'] = false;
            }

            info('💾 Database Configuration');
            $options['database'] = select(
                label: 'Which database would you like to use?',
                options: [
                    'sqlite' => 'SQLite (Zero-config)',
                    'mysql' => 'MySQL',
                    'mariadb' => 'MariaDB',
                    'pgsql' => 'PostgreSQL'
                ],
                default: 'sqlite'
            );
        } else {
            // Default options for non-interactive mode
            $options = [
                'wantKit' => true,
                'withBoost' => PHP_OS_FAMILY !== 'Windows',
                'kit' => 'Breeze',
                'stack' => 'blade',
                'withTeams' => false,
                'database' => 'sqlite',
            ];
        }

        return $options;
    }

    public function build(string $projectName, array $options, OutputInterface $output): int
    {
        $projectPath = getcwd() . DIRECTORY_SEPARATOR . $projectName;

        // Show the Laravel logo (the official installer can't render it without a real TTY)
        $output->writeln('');
        $output->writeln('   <fg=red> ██╗       █████╗  ██████╗   █████╗  ██╗   ██╗ ███████╗ ██╗</>');
        $output->writeln('   <fg=red> ██║      ██╔══██╗ ██╔══██╗ ██╔══██╗ ██║   ██║ ██╔════╝ ██║</>');
        $output->writeln('   <fg=red> ██║      ███████║ ██████╔╝ ███████║ ██║   ██║ █████╗   ██║</>');
        $output->writeln('   <fg=red> ██║      ██╔══██║ ██╔══██╗ ██╔══██║ ╚██╗ ██╔╝ ██╔══╝   ██║</>');
        $output->writeln('   <fg=red> ███████╗ ██║  ██║ ██║  ██║ ██║  ██║  ╚████╔╝  ███████╗ ███████╗</>');
        $output->writeln('   <fg=red> ╚══════╝ ╚═╝  ╚═╝ ╚═╝  ╚═╝ ╚═╝  ╚═╝   ╚═══╝   ╚══════╝ ╚══════╝</>');
        $output->writeln('');

        try {
            // 1. Create Laravel project using official installer (interactive)
            $this->createLaravelProjectWithInstaller($projectName, $output);

            // 2. Ensure resources/js/bootstrap.js exists early (Fixes Vite [UNRESOLVED_IMPORT] './bootstrap')
            $jsPath = $projectPath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'js';
            if (!is_dir($jsPath)) {
                @mkdir($jsPath, 0755, true);
            }
            $bootstrapPath = $jsPath . DIRECTORY_SEPARATOR . 'bootstrap.js';
            if (!file_exists($bootstrapPath)) {
                $bootstrapContent = "import axios from 'axios';\nwindow.axios = axios;\nwindow.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';\n";
                @file_put_contents($bootstrapPath, $bootstrapContent);
            }

            // 3. Install Sail (our custom logic to ensure proper configuration)
            $output->writeln('<info>⛵ Configuring Laravel Sail...</info>');
            $this->runProcess(['composer', 'require', 'laravel/sail', '--dev', '--no-interaction', '--quiet'], $projectPath, $output);

            // Determine which Sail services to include based on database selection.
            // sqlite  → file-based, no DB container needed.
            // sqlsrv  → NOT a native Sail service; we inject MSSQL manually via customizeComposeFile.
            // others  → pass the driver name directly to sail:install.
            $database = $options['database'] ?? '';
            $sailServices = [];

            $nativeSailDatabases = ['mysql', 'mariadb', 'pgsql'];
            if (in_array($database, $nativeSailDatabases, true)) {
                $sailServices[] = $database;
            }

            $sailCommand = ['php', 'artisan', 'sail:install', '--no-interaction'];
            if (!empty($sailServices)) {
                $sailCommand[] = '--with=' . implode(',', $sailServices);
            } else {
                // sqlite / sqlsrv: only the app container, no extra DB service via Sail
                $sailCommand[] = '--with=';
            }

            $this->runProcess($sailCommand, $projectPath, $output);

            // Customize compose.yaml to remove unwanted services (mysql, redis) when not needed
            $this->customizeComposeFile($projectPath, $options['database'] ?? '', $output);

            // Set database connection in .env based on user selection
            $this->setDatabaseConnection($projectPath, $options['database'] ?? '', $output);

            // 4. Install Auth Kit (if not already handled by installer)
            // Note: The Laravel installer may have already installed the auth kit based on user selection
            // We'll check if the kit files exist before attempting to install
            $kit = $options['kit'] ?? 'None';
            if ($kit === 'Breeze' || $kit === 'Official Starter Kit (2026)') {
                $kitName = $kit === 'Breeze' ? 'Laravel Breeze' : 'Official Starter Kit (2026)';
                $output->writeln("<info>🍃 Ensuring {$kitName} is installed...</info>");

                // Check if Breeze is already installed
                $breezePath = $projectPath . '/vendor/laravel/breeze';
                if (!is_dir($breezePath)) {
                    $this->runProcess(['composer', 'require', 'laravel/breeze', '--dev', '--no-interaction', '--quiet'], $projectPath, $output);
                    $breezeArgs = [$options['stack']];

                    // Note: Laravel Breeze uses different flags for teams
                    // As of recent versions, teams are included by default or with different flags
                    // We'll skip the teams flag for now to avoid errors
                    // Note: No --no-teams flag exists; simply omit --teams for no team support

                    // Note: We don't use --no-migrations flag as it doesn't exist in breeze:install

                    // Note: We don't use --no-migrations flag as it doesn't exist in breeze:install
                    // Instead, we rely on the fact that migrations will be handled by the user later
                    // or by the Laravel installer if they selected that option

                    $this->runProcess(array_merge(['php', 'artisan', 'breeze:install'], $breezeArgs), $projectPath, $output);
                } else {
                    $output->writeln("<info>🍃 {$kitName} already installed, skipping...</info>");
                }

                // Fix Vite version compatibility for React/Vue stacks
                $stack = $options['stack'] ?? '';
                $this->fixJsDependencies($projectPath, $stack, $output);
            } elseif ($kit === 'Jetstream') {
                $output->writeln('<info>🚀 Ensuring Laravel Jetstream is installed...</info>');

                // Check if Jetstream is already installed
                $jetstreamPath = $projectPath . '/vendor/laravel/jetstream';
                if (!is_dir($jetstreamPath)) {
                    $this->runProcess(['composer', 'require', 'laravel/jetstream', '--no-interaction', '--quiet'], $projectPath, $output);
                    $this->runProcess(['php', 'artisan', 'jetstream:install', $options['stack'], '--no-interaction'], $projectPath, $output);
                } else {
                    $output->writeln('<info>🚀 Laravel Jetstream already installed, skipping...</info>');
                }

                // Fix Vite version compatibility for React/Vue stacks
                $this->fixJsDependencies($projectPath, $options['stack'], $output);
            }

            // 5. Install Laravel Boost if requested
            if ($options['withBoost'] ?? false) {
                $output->writeln('<info>🚀 Installing Laravel Boost for AI assisted coding...</info>');
                try {
                    // Install Laravel Boost package
                    $this->runProcess(['composer', 'require', 'laravel/boost', '--dev', '--no-interaction', '--quiet'], $projectPath, $output);

                    // Install MCP server and coding guidelines
                    $this->runProcess(['php', 'artisan', 'boost:install'], $projectPath, $output);
                } catch (\Exception $e) {
                    $output->writeln('<warning>⚠️ Laravel Boost installation failed. Continuing without it...</warning>');
                    $output->writeln('   You can manually install it later if needed:');
                    $output->writeln('   composer require laravel/boost --dev');
                    $output->writeln('   php artisan boost:install');
                }
            }

            // 6. Generate install.sh
            $this->generateInstallScript($projectPath, $options, $output);

            // Fix node_modules permissions to ensure container accessibility
            $nodeModulesPath = $projectPath . DIRECTORY_SEPARATOR . 'node_modules';
            if (is_dir($nodeModulesPath)) {
                $this->runProcess(['chmod', '-R', 'a+rX', 'node_modules'], $projectPath, $output);
            }

            $output->writeln('');
            $output->writeln('<info>🎉 Project generated successfully!</info>');
            $output->writeln('<info>📝 Next steps:</info>');
            $output->writeln('   1. cd ' . $projectName);
            $output->writeln('   2. ./install.sh');

            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    /**
     * Create Laravel project using the official installer (non-interactive for base app)
     */
    protected function createLaravelProjectWithInstaller(string $projectName, OutputInterface $output): void
    {
        // Check if Laravel installer is available (silently)
        $process = new Process(['laravel', '--version']);
        $process->run();
        $laravelInstalled = $process->isSuccessful();

        if (!$laravelInstalled) {
            $output->writeln('<info>📦 Installing Laravel installer globally...</info>');
            $this->runProcess(['composer', 'global', 'require', 'laravel/installer'], null, $output);
            putenv('PATH=' . getenv('HOME') . '/.composer/vendor/bin:' . getenv('PATH'));
        }

        // Create project using Laravel installer.
        // We use a callback to keep the creation steps visible but strip the
        // confusing "migration failed" and "Application ready" blocks that appear at the end.
        $process = new Process(['laravel', 'new', $projectName, '--no-interaction']);
        $process->setTimeout(null);

        $skipBlock = false;

        $process->run(function ($type, $line) use ($output, &$skipBlock) {
            // Once we hit the migration block or "Application ready", stop showing output.
            if (
                strpos($line, 'Running database migrations') !== false ||
                strpos($line, 'Application ready in') !== false
            ) {
                $skipBlock = true;
            }

            if ($skipBlock) {
                return;
            }

            $output->write($line);
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("laravel new failed: " . $process->getErrorOutput());
        }
    }

    /**
     * Run a process and return success status (without throwing exceptions)
     * @return bool True if successful, false otherwise
     */
    protected function runProcess(array $command, ?string $cwd, OutputInterface $output, bool $returnStatus = false): bool
    {
        $process = new Process($command);
        if ($cwd) {
            $process->setWorkingDirectory($cwd);
        }
        $process->setTimeout(null);

        $commandString = implode(' ', $command);
        $isMigrationCommand = (strpos($commandString, 'migrate') !== false && strpos($commandString, 'artisan') !== false);
        $isLaravelNew = (isset($command[0]) && $command[0] === 'laravel' && $command[1] === 'new');

        $process->run(function ($type, $line) use ($output, $isMigrationCommand, $isLaravelNew) {
            // For laravel new, suppress migration related output that often fails harmlessly
            if ($isLaravelNew) {
                if (
                    strpos($line, 'database migrations') !== false ||
                    strpos($line, 'Error output:') !== false ||
                    strpos($line, 'artisan migrate') !== false ||
                    strpos($line, 'Database migrated') !== false
                ) {
                    return;
                }
            }

            // For migration commands, only output normal output, suppress error output
            if ($isMigrationCommand && $type === Process::ERR) {
                // Suppress error output for migration commands
                return;
            }

            // Suppress common noisy lines from composer/npm/docker/sail (ONLY if it's NOT 'laravel new')
            $isNoise = !$isLaravelNew && (
                strpos($line, 'Loading composer repositories') !== false ||
                strpos($line, 'Updating dependencies') !== false ||
                strpos($line, 'Installing dependencies') !== false ||
                strpos($line, 'Writing lock file') !== false ||
                strpos($line, 'Generating optimized autoload files') !== false ||
                strpos($line, 'Package operations:') !== false ||
                strpos($line, '- Installing') !== false ||
                strpos($line, 'TTY mode requires /dev/tty') !== false ||
                (strpos($line, 'Image') !== false && (strpos($line, 'Pulling') !== false || strpos($line, 'Pulled') !== false || strpos($line, 'Building') !== false || strpos($line, 'Built') !== false)) ||
                preg_match('/^\s*#\d+/', $line) || // Docker build steps
                strpos($line, 'DONE') !== false ||
                strpos($line, 'exporting') !== false ||
                strpos($line, 'resolving provenance') !== false ||
                strpos($line, 'naming to') !== false ||
                strpos($line, 'unpacking to') !== false ||
                strpos($line, 'Application ready in') !== false ||
                strpos($line, '➜') !== false ||
                strpos($line, 'New to Laravel?') !== false ||
                strpos($line, 'Build something amazing!') !== false ||
                strpos($line, 'Sail scaffolding installed successfully') !== false ||
                strpos($line, 'A database service was installed') !== false
            );

            if ($isNoise) {
                // For noise, show a simple spinner/progress on the same line
                static $step = 0;
                $chars = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
                $char = $chars[$step % count($chars)];
                $output->write("\r  <info>{$char} Processing...</info>");
                $step++;
                return;
            }

            // Clear the progress line before writing real output (only if we were in noise mode)
            if (!$isLaravelNew) {
                $output->write("\r\033[K");
            }
            $output->write($line);
        });

        $success = $process->isSuccessful();

        if (!$success && !$returnStatus) {
            // Check if this is a migrate command failure that we can ignore
            if ($isMigrationCommand) {
                $output->writeln('<warning>⚠️ Migration command failed (this is expected if database is not yet ready):</warning>');
                $output->writeln('   (Output suppressed - this is normal if database is not configured yet)');
                // Don't throw exception, continue with process
                return false;
            }

            throw new ProcessFailedException($process);
        }

        return $success;
    }

    protected function generateInstallScript(string $projectPath, array $options, OutputInterface $output): void
    {
        $stubPath = __DIR__ . '/../Templates/laravel/install.sh.stub';
        if (!file_exists($stubPath)) {
            $output->writeln('<error>❌ Template not found</error>');
            return;
        }

        // All databases run via Docker (Sail container).
        // SQLite: only the app container, no DB service needed.
        // sqlsrv: app container + MSSQL container injected by customizeComposeFile.
        $database = $options['database'] ?? '';
        $useSail = true;
        $useSqlsrv = $database === 'sqlsrv';

        $stub = file_get_contents($stubPath);
        $tags = [
            'USE_SAIL' => $useSail,
            'USE_SQLSRV' => $useSqlsrv,
            'USE_BREEZE' => $options['kit'] === 'Breeze',
            'USE_JETSTREAM' => $options['kit'] === 'Jetstream',
            'USE_STARTER_KIT' => $options['kit'] === 'Official Starter Kit (2026)',
        ];
        $variables = [
            'PROJECT_NAME' => basename($projectPath),
            'DB_SERVICE' => $database,
            'BREEZE_STACK' => $options['stack'] ?? '',
            'JETSTREAM_STACK' => $options['stack'] ?? '',
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
            $output->writeln('<warning>⚠️ Could not parse package.json</warning>');
            return;
        }

        // Ensure both dependencies and devDependencies exist
        if (!isset($packageJson['dependencies'])) {
            $packageJson['dependencies'] = [];
        }
        if (!isset($packageJson['devDependencies'])) {
            $packageJson['devDependencies'] = [];
        }

        // Always ensure axios is in dependencies (needed by bootstrap.js)
        if (!isset($packageJson['dependencies']['axios']) || empty($packageJson['dependencies']['axios'])) {
            $packageJson['dependencies']['axios'] = '^1.6.0';
            $output->writeln('<info>🔧 Added axios to dependencies</info>');
        }

        // Only adjust vite version for stacks that use Vite plugins with version constraints
        $stack = strtolower($stack);
        if (in_array($stack, ['react', 'vue'], true)) {
            // Set vite to a version compatible with @vitejs/plugin-react and @vitejs/plugin-vue
            $packageJson['devDependencies']['vite'] = '^7.0.0';
            $output->writeln('<info>🔧 Adjusted vite version to ^7.0.0 for stack: ' . $stack . '</info>');
        }

        if (file_put_contents($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
            $output->writeln('<warning>⚠️ Failed to write package.json</warning>');
        }
    }

    /**
     * Customize compose.yaml:
     * - Remove orphan volumes (sail-mysql, sail-redis) when their services are absent.
     * - Remove stale depends_on entries that reference non-existent services.
     * - Inject the MSSQL service block for sqlsrv.
     */
    protected function customizeComposeFile(string $projectPath, string $database, OutputInterface $output): void
    {
        $composeFile = $projectPath . '/compose.yaml';

        if (!file_exists($composeFile)) {
            return;
        }

        $composeContent = file_get_contents($composeFile);

        // ------------------------------------------------------------------ //
        // 1. Determine which Sail-generated services to strip out             //
        // ------------------------------------------------------------------ //
        $servicesToRemove = match ($database) {
            'sqlite' => ['mysql', 'redis'],
            default => [],
        };

        foreach ($servicesToRemove as $service) {
            // Remove the service definition block
            $pattern = '/^\s*' . preg_quote($service, '/') . ':\s*$\n(?:^\s{2,}.*\n)*/m';
            $composeContent = preg_replace($pattern, '', $composeContent);
        }

        // ------------------------------------------------------------------ //
        // 2. Nuke the entire depends_on block from laravel.test               //
        // ------------------------------------------------------------------ //
        if (!empty($servicesToRemove)) {
            $composeContent = preg_replace('/^(\s+)depends_on:\s*\n(?:\1\s+-[^\n]*\n)*/m', '', $composeContent);
        }

        // ------------------------------------------------------------------ //
        // 3. Remove orphan volume declarations                                //
        // ------------------------------------------------------------------ //
        $orphanVolumes = match ($database) {
            'sqlite' => ['sail-mysql', 'sail-redis'],
            default => [],
        };

        foreach ($orphanVolumes as $vol) {
            $composeContent = preg_replace('/^\s*' . preg_quote($vol, '/') . ':\s*\n(?:\s+driver:.*\n)?/m', '', $composeContent);
        }

        // Remove the volumes: block entirely if it ends up empty
        $composeContent = preg_replace('/^volumes:\s*\n(?:\s*\n)*(?=\S|$)/m', '', $composeContent);

        file_put_contents($composeFile, $composeContent);

        if (!empty($servicesToRemove)) {
            $output->writeln('<info>🧹 Cleaned compose.yaml: removed ' . implode(', ', $servicesToRemove) . ' and orphan volumes</info>');
        }
    }

    /**
     * Set the database connection in .env file.
     * Also cleans up irrelevant DB_* variables so Laravel doesn't try to use them.
     */
    protected function setDatabaseConnection(string $projectPath, string $database, OutputInterface $output): void
    {
        $envPath = $projectPath . '/.env';
        if (!file_exists($envPath)) {
            $output->writeln('<warning>⚠️ .env file not found</warning>');
            return;
        }

        $envContent = file_get_contents($envPath);

        // Variables we want to ensure exist in the .env for a clean experience
        $dbVars = [
            'DB_CONNECTION' => $database,
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ];

        // If it's not SQLite, we don't force them empty (Sail might have set them)
        // but for SQLite we definitely want them clean and present.
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
        $output->writeln("<info>🔧 Database connection variables ensured in .env</info>");
    }
}
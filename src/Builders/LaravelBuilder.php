<?php

namespace Roldante05\ScaffoldingFactory\Builders;

use Roldante05\ScaffoldingFactory\Helpers\StubProcessor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LaravelBuilder implements BuilderInterface
{
    protected $helper;

    public function askOptions(InputInterface $input, OutputInterface $output, $helper): array
    {
        $options = [];

        // 1. Kit de autenticación
        $question = new ChoiceQuestion(
            'Which starter kit would you like to use?',
            ['Breeze', 'Jetstream', 'Official Starter Kit (2026)', 'None'],
            0
        );
        $options['kit'] = $helper->ask($input, $output, $question);

        // 2. Stack de frontend (depende del kit)
        if ($options['kit'] === 'Breeze') {
            $question = new ChoiceQuestion(
                'Which Breeze stack would you like to use?',
                ['Blade', 'Livewire', 'React (Inertia)', 'Vue (Inertia)'],
                0
            );
            $options['stack'] = strtolower($helper->ask($input, $output, $question));
        } elseif ($options['kit'] === 'Jetstream') {
            $question = new ChoiceQuestion(
                'Which Jetstream stack would you like to use?',
                ['Livewire', 'Inertia (Vue)'],
                0
            );
            $options['stack'] = str_contains($helper->ask($input, $output, $question), 'Livewire') ? 'livewire' : 'inertia';
        } elseif ($options['kit'] === 'Official Starter Kit (2026)') {
            $question = new ChoiceQuestion(
                'Which official starter kit would you like to use?',
                ['Livewire (Flux UI)', 'React (shadcn)', 'Vue (shadcn-vue)'],
                0
            );
            $ans = $helper->ask($input, $output, $question);
            if (str_contains($ans, 'Livewire')) $options['stack'] = 'livewire';
            elseif (str_contains($ans, 'React')) $options['stack'] = 'react';
            else $options['stack'] = 'vue';
        } else {
            $options['stack'] = 'none';
        }

        // 3. Base de datos
        $question = new ChoiceQuestion(
            'Which database would you like to use?',
            ['sqlite', 'mysql', 'mariadb', 'pgsql', 'sqlsrv'],
            0
        );
        $options['database'] = $helper->ask($input, $output, $question);

        return $options;
    }

    public function build(string $projectName, array $options, OutputInterface $output): int
    {
        $output->writeln('<info>📦 Creating Laravel project...</info>');
        $projectPath = getcwd() . DIRECTORY_SEPARATOR . $projectName;

        try {
            // 1. Create Laravel project
            $this->runProcess(['composer', 'create-project', 'laravel/laravel', $projectName, '--no-interaction'], null, $output);

            // 2. Install Sail
            $output->writeln('<info>⛵ Installing Laravel Sail...</info>');
            $this->runProcess(['composer', 'require', 'laravel/sail', '--dev', '--no-interaction'], $projectPath, $output);
            $this->runProcess(['php', 'artisan', 'sail:install', "--with={$options['database']}", '--no-interaction'], $projectPath, $output);

            // 3. Install Auth Kit
            if ($options['kit'] === 'Breeze') {
                $output->writeln('<info>🍃 Installing Laravel Breeze...</info>');
                $this->runProcess(['composer', 'require', 'laravel/breeze', '--dev', '--no-interaction'], $projectPath, $output);
                $this->runProcess(['php', 'artisan', 'breeze:install', $options['stack'], '--no-interaction'], $projectPath, $output);
            } elseif ($options['kit'] === 'Jetstream') {
                $output->writeln('<info>🚀 Installing Laravel Jetstream...</info>');
                $this->runProcess(['composer', 'require', 'laravel/jetstream', '--no-interaction'], $projectPath, $output);
                $this->runProcess(['php', 'artisan', 'jetstream:install', $options['stack'], '--no-interaction'], $projectPath, $output);
            }

            // 4. Generate install.sh
            $this->generateInstallScript($projectPath, $options, $output);

            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    protected function generateInstallScript(string $projectPath, array $options, OutputInterface $output): void
    {
        $stubPath = __DIR__ . '/../Templates/laravel/install.sh.stub';
        if (!file_exists($stubPath)) {
            $output->writeln('<error>❌ Template not found</error>');
            return;
        }

        $stub = file_get_contents($stubPath);
        $tags = [
            'USE_SAIL' => true,
            'USE_BREEZE' => $options['kit'] === 'Breeze',
            'USE_JETSTREAM' => $options['kit'] === 'Jetstream',
            'USE_STARTER_KIT' => $options['kit'] === 'Official Starter Kit (2026)',
        ];
        $variables = [
            'PROJECT_NAME' => basename($projectPath),
            'DB_SERVICE' => $options['database'],
            'BREEZE_STACK' => $options['stack'] ?? '',
            'JETSTREAM_STACK' => $options['stack'] ?? '',
        ];

        $content = StubProcessor::process($stub, $variables, $tags);
        file_put_contents($projectPath . '/install.sh', $content);
        chmod($projectPath . '/install.sh', 0755);
    }

    protected function runProcess(array $command, ?string $cwd, OutputInterface $output): void
    {
        $process = new Process($command);
        if ($cwd) $process->setWorkingDirectory($cwd);
        $process->setTimeout(null);
        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}

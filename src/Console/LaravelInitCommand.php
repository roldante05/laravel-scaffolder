<?php

namespace Roldante05\LaravelScaffolder\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\Option;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LaravelInitCommand extends Command
{
    /**
     * Configure the command options.
     */
    protected function configure(): void
    {
        $this
            ->setName('init')
            ->setDescription('Create a new Laravel project with customized options')
            ->addArgument('project-name', InputArgument::REQUIRED, 'The name of the project to create')
            ->setHelp('This command allows you to create a Laravel project with customizable options...');
    }

    /**
     * Execute the console command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectName = $input->getArgument('project-name');

        // Validar nombre del proyecto
        if (empty($projectName)) {
            $output->writeln('<error>❌ Nombre de proyecto requerido</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>🚀 Laravel Scaffolder - Creando proyecto Laravel personalizado</info>');
        $output->writeln('');

        // Preguntas interactivas
        $options = $this->askForOptions($input, $output);

        // Mostrar resumen
        $output->writeln('<info>📋 Resumen de configuración:</info>');
        $output->writeln('   Proyecto: <comment>' . $projectName . '</comment>');
        $output->writeln('   Docker/Sail: <comment>' . ($options['useSail'] ? 'Sí' : 'No') . '</comment>');
        $output->writeln('   Livewire: <comment>' . ($options['useLivewire'] ? 'Sí' : 'No') . '</comment>');
        $output->writeln('   Frontend: <comment>' . $options['frontend'] . '</comment>');
        $output->writeln('   Testing: <comment>' . $options['testing'] . '</comment>');
        $output->writeln('');

        // Confirmar antes de continuar
        if (!$this->confirm('¿Continuar con la creación del proyecto?', true, $input, $output)) {
            $output->writeln('<info>👋 Operación cancelada.</info>');
            return Command::SUCCESS;
        }

        // Crear el proyecto
        return $this->createProject($projectName, $options, $input, $output);
    }

    /**
     * Preguntar al usuario por las opciones de configuración.
     *
     * @return array
     */
    protected function askForOptions(InputInterface $input, OutputInterface $output): array
    {
        $options = [];

        // Preguntar si usar Laravel Sail con MySQL
        $question = new ConfirmationQuestion('¿Usar Laravel Sail con MySQL? [<comment>yes</comment>]: ', true);
        $options['useSail'] = $this->getHelper('question')->ask($input, $output, $question);

        // Preguntar si incluir Livewire
        $question = new ConfirmationQuestion('¿Incluir Livewire? [<comment>yes</comment>]: ', true);
        $options['useLivewire'] = $this->getHelper('question')->ask($input, $output, $question);

        // Preguntar qué usar para assets
        $question = new ChoiceQuestion(
            '¿Qué usar para assets?',
            ['Vite (recomendado)', 'Webpack (legacy)'],
            0
        );
        $answer = $this->getHelper('question')->ask($input, $output, $question);
        $options['frontend'] = str_contains($answer, 'Vite') ? 'vite' : 'webpack';

        // Preguntar qué framework de tests usar
        $question = new ChoiceQuestion(
            '¿Qué framework de tests usar?',
            ['PEST (recomendado)', 'PHPUnit'],
            0
        );
        $answer = $this->getHelper('question')->ask($input, $output, $question);
        $options['testing'] = str_contains($answer, 'PEST') ? 'pest' : 'phpunit';

        return $options;
    }

    /**
     * Crear el proyecto Laravel con las opciones especificadas.
     *
     * @param string $projectName
     * @param array $options
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function createProject(string $projectName, array $options, InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>📦 Creando proyecto Laravel...</info>');

        try {
            // Crear proyecto Laravel base
            $process = new Process([
                'composer', 'create-project', 'laravel/laravel:^12.0', $projectName, '--no-interaction'
            ]);
            $process->setTimeout(null);
            $process->run(function ($type, $line) use ($output) {
                $output->write($line);
            });

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output->writeln('<info>✅ Proyecto Laravel creado exitosamente</info>');
            $output->writeln('');

            // Cambiar al directorio del proyecto
            chdir($projectName);

            // Configurar según las opciones seleccionadas
            $this->configureProject($options, $input, $output);

            // Generar install.sh personalizado
            $this->generateInstallScript($options, $output);

            $output->writeln('<info>🎉 ¡Proyecto creado exitosamente!</info>');
            $output->writeln('');
            $output->writeln('<info>📋 Próximos pasos:</info>');
            $output->writeln('   1. cd ' . $projectName);
            $output->writeln('   2. ./install.sh');
            $output->writeln('');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error al crear el proyecto: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }

    /**
     * Preguntar si se confirma una acción.
     *
     * @param string $question
     * @param boolean $default
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return boolean
     */
    protected function confirm(string $question, bool $default = false, InputInterface $input, OutputInterface $output): bool
    {
        $confirmationQuestion = new ConfirmationQuestion($question . ' [<comment>yes</comment>]: ', $default);
        return $this->getHelper('question')->ask($input, $output, $confirmationQuestion);
    }

    /**
     * Configurar el proyecto según las opciones seleccionadas.
     *
     * @param array $options
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function configureProject(array $options, InputInterface $input, OutputInterface $output): void
    {
        // Instalar Laravel Sail si se seleccionó
        if ($options['useSail']) {
            $output->writeln('<info>⛵ Instalando Laravel Sail...</info>');
            $process = new Process([
                './vendor/bin/sail', 'install', '--with=mysql'
            ]);
            $process->setTimeout(null);
            $process->run(function ($type, $line) use ($output) {
                $output->write($line);
            });

            if (!$process->isSuccessful()) {
                $output->writeln('<warning>⚠️  Advertencia: Problema al instalar Sail, continuando...</warning>');
            }
        }

        // Instalar Livewire si se seleccionó
        if ($options['useLivewire']) {
            $output->writeln('<info>⚡ Instalando Livewire...</info>');
            $process = new Process([
                'composer', 'require', 'livewire/livewire:^4.0', '--no-interaction'
            ]);
            $process->setTimeout(null);
            $process->run(function ($type, $line) use ($output) {
                $output->write($line);
            });

            if (!$process->isSuccessful()) {
                $output->writeln('<warning>⚠️  Advertencia: Problema al instalar Livewire, continuando...</warning>');
            } else {
                // Instalar el starter kit de Livewire (Blaze)
                $output->writeln('<info>🎨 Instalando Livewire Blaze starter kit...</info>');
                $process = new Process([
                    'composer', 'require', 'livewire/blaze:^1.0', '--dev', '--no-interaction'
                ]);
                $process->setTimeout(null);
                $process->run(function ($type, $line) use ($output) {
                    $output->write($line);
                });

                if ($process->isSuccessful()) {
                    $process = new Process([
                        'php', 'artisan', 'livewire:blaze', '--no-interaction'
                    ]);
                    $process->setTimeout(null);
                    $process->run(function ($type, $line) use ($output) {
                        $output->write($line);
                    });
                }
            }
        }

        // Configurar testing framework
        if ($options['testing'] === 'pest') {
            $output->writeln('<info>🧪 Instalando PEST...</info>');
            $process = new Process([
                'composer', 'require', 'pestphp/pest:^4.0', '--dev', '--no-interaction'
            ]);
            $process->setTimeout(null);
            $process->run(function ($type, $line) use ($output) {
                $output->write($line);
            });

            if ($process->isSuccessful()) {
                $process = new Process([
                    'php', 'artisan', 'pest:install'
                ]);
                $process->setTimeout(null);
                $process->run(function ($type, $line) use ($output) {
                    $output->write($line);
                });
            }
        }
    }

    /**
     * Generar el script install.sh personalizado basado en las opciones.
     *
     * @param array $options
     * @param OutputInterface $output
     * @return void
     */
    protected function generateInstallScript(array $options, OutputInterface $output): void
    {
        $stubPath = __DIR__ . '/../Templates/install.sh.stub';

        if (!file_exists($stubPath)) {
            $output->writeln('<error>❌ No se encontró la plantilla de install.sh</error>');
            return;
        }

        $stub = file_get_contents($stubPath);

        // Reemplazar placeholders con valores reales usando sintaxis simple
        $replacements = [
            '{{USE_SAIL}}' => $options['useSail'] ? '' : '# ',
            '{{/USE_SAIL}}' => $options['useSail'] ? '' : '# ',
            '{{USE_LIVEWIRE}}' => $options['useLivewire'] ? '' : '# ',
            '{{/USE_LIVEWIRE}}' => $options['useLivewire'] ? '' : '# ',
            '{{USE_PEST}}' => $options['testing'] === 'pest' ? '' : '# ',
            '{{/USE_PEST}}' => $options['testing'] === 'pest' ? '' : '# ',
            '{{!USE_SAIL}}' => !$options['useSail'] ? '' : '# ',
            '{{/!USE_SAIL}}' => !$options['useSail'] ? '' : '# ',
        ];

        foreach ($replacements as $placeholder => $replacement) {
            $stub = str_replace($placeholder, $replacement, $stub);
        }

        // Limpiar comentarios innecesarios y líneas vacías
        $lines = explode("\n", $stub);
        $cleanedLines = [];
        foreach ($lines as $line) {
            // Si la línea empieza con # y solo tiene espacios después, la ignoramos
            if (trim($line) === '' || (str_starts_with(trim($line), '#') && strlen(trim($line)) === 1)) {
                continue;
            }
            $cleanedLines[] = $line;
        }

        $stub = implode("\n", $cleanedLines);

        // Escribir el archivo install.sh
        file_put_contents('install.sh', $stub);
        chmod('install.sh', 0755); // Hacer ejecutable

        $output->writeln('<info>📄 Script install.sh generado exitosamente</info>');
    }
}
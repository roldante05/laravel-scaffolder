<?php

namespace Roldante05\LaravelScaffolder\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->setDescription('Crea un nuevo proyecto Laravel con opciones personalizadas')
            ->addArgument('project-name', InputArgument::REQUIRED, 'El nombre del proyecto a crear')
            ->setHelp('Este comando te permite crear un proyecto Laravel con opciones configurables de forma interactiva.');
    }

    /**
     * Execute the console command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectName = $input->getArgument('project-name');

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
        if (!$this->confirm('¿Continuar con la creación del proyecto?', $input, $output, true)) {
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

        $question = new ConfirmationQuestion('¿Usar Laravel Sail con MySQL? [<comment>yes</comment>]: ', true);
        $options['useSail'] = $this->getHelper('question')->ask($input, $output, $question);

        $question = new ConfirmationQuestion('¿Incluir Livewire (con Volt)? [<comment>yes</comment>]: ', true);
        $options['useLivewire'] = $this->getHelper('question')->ask($input, $output, $question);

        $question = new ChoiceQuestion(
            '¿Qué usar para assets?',
            ['Vite (recomendado)', 'Webpack (legacy)'],
            0
        );
        $answer = $this->getHelper('question')->ask($input, $output, $question);
        $options['frontend'] = str_contains($answer, 'Vite') ? 'vite' : 'webpack';

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
     */
    protected function createProject(string $projectName, array $options, InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>📦 Creando proyecto Laravel...</info>');

        // Resolve la ruta absoluta donde se creará el proyecto
        $projectPath = getcwd() . DIRECTORY_SEPARATOR . $projectName;

        try {
            // 1. Crear proyecto Laravel base
            $process = new Process([
                'composer', 'create-project', 'laravel/laravel:^12.0', $projectName, '--no-interaction',
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

            // 2. Configurar paquetes (sin chdir — se pasa el cwd a cada proceso)
            $this->configureProject($projectPath, $options, $output);

            // 3. Generar install.sh personalizado
            $this->generateInstallScript($projectPath, $options, $output);

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
     */
    protected function confirm(string $question, InputInterface $input, OutputInterface $output, bool $default = false): bool
    {
        $confirmationQuestion = new ConfirmationQuestion($question . ' [<comment>yes</comment>]: ', $default);
        return $this->getHelper('question')->ask($input, $output, $confirmationQuestion);
    }

    /**
     * Configurar el proyecto según las opciones seleccionadas.
     * Usa setWorkingDirectory() en lugar de chdir() para mayor seguridad.
     */
    protected function configureProject(string $projectPath, array $options, OutputInterface $output): void
    {
        // Instalar Laravel Sail
        if ($options['useSail']) {
            $output->writeln('<info>⛵ Instalando Laravel Sail...</info>');

            // Paso 1: requerir el paquete
            $process = new Process(['composer', 'require', 'laravel/sail', '--dev', '--no-interaction']);
            $process->setWorkingDirectory($projectPath);
            $process->setTimeout(null);
            $process->run(function ($type, $line) use ($output) {
                $output->write($line);
            });

            if (!$process->isSuccessful()) {
                $output->writeln('<warning>⚠️  Advertencia: No se pudo instalar el paquete laravel/sail, continuando...</warning>');
            } else {
                // Paso 2: publicar la configuración de Sail con MySQL
                $process = new Process(['php', 'artisan', 'sail:install', '--with=mysql', '--no-interaction']);
                $process->setWorkingDirectory($projectPath);
                $process->setTimeout(null);
                $process->run(function ($type, $line) use ($output) {
                    $output->write($line);
                });

                if (!$process->isSuccessful()) {
                    $output->writeln('<warning>⚠️  Advertencia: Problema al configurar Sail, continuando...</warning>');
                }
            }
        }

        // Instalar Livewire + Volt
        if ($options['useLivewire']) {
            $output->writeln('<info>⚡ Instalando Livewire...</info>');

            $process = new Process(['composer', 'require', 'livewire/livewire', 'livewire/volt', '--no-interaction', '-W']);
            $process->setWorkingDirectory($projectPath);
            $process->setTimeout(null);
            $process->run(function ($type, $line) use ($output) {
                $output->write($line);
            });

            if (!$process->isSuccessful()) {
                $output->writeln('<warning>⚠️  Advertencia: Problema al instalar Livewire, continuando...</warning>');
            } else {
                $output->writeln('<info>🎨 Publicando Livewire Volt...</info>');
                $process = new Process(['php', 'artisan', 'volt:install']);
                $process->setWorkingDirectory($projectPath);
                $process->setTimeout(null);
                $process->run(function ($type, $line) use ($output) {
                    $output->write($line);
                });
            }
        }

        // Instalar PEST
        if ($options['testing'] === 'pest') {
            $output->writeln('<info>🧪 Instalando PEST...</info>');

            $process = new Process(['composer', 'require', 'pestphp/pest', 'pestphp/pest-plugin-laravel', '--dev', '--no-interaction', '-W']);
            $process->setWorkingDirectory($projectPath);
            $process->setTimeout(null);
            $process->run(function ($type, $line) use ($output) {
                $output->write($line);
            });

            if ($process->isSuccessful()) {
                $output->writeln('<info>🧪 Inicializando PEST...</info>');
                $process = new Process(['./vendor/bin/pest', '--init']);
                $process->setWorkingDirectory($projectPath);
                $process->setTimeout(null);
                $process->run(function ($type, $line) use ($output) {
                    $output->write($line);
                });
            } else {
                $output->writeln('<warning>⚠️  Advertencia: Problema al instalar PEST, continuando...</warning>');
            }
        }
    }

    /**
     * Generar el script install.sh personalizado basado en las opciones.
     */
    protected function generateInstallScript(string $projectPath, array $options, OutputInterface $output): void
    {
        $stubPath = __DIR__ . '/../Templates/install.sh.stub';

        if (!file_exists($stubPath)) {
            $output->writeln('<error>❌ No se encontró la plantilla de install.sh</error>');
            return;
        }

        $stub = file_get_contents($stubPath);

        // Procesar bloques condicionales {{TAG}} ... {{/TAG}}
        $stub = $this->processConditionalBlock($stub, 'USE_SAIL', $options['useSail']);
        $stub = $this->processConditionalBlock($stub, 'USE_LIVEWIRE', $options['useLivewire']);
        $stub = $this->processConditionalBlock($stub, 'USE_PEST', $options['testing'] === 'pest');
        $stub = $this->processConditionalBlock($stub, 'USE_VITE', $options['frontend'] === 'vite');

        // Limpiar líneas vacías consecutivas (máximo 1)
        $stub = preg_replace("/\n{3,}/", "\n\n", $stub);

        $installPath = $projectPath . DIRECTORY_SEPARATOR . 'install.sh';
        file_put_contents($installPath, $stub);
        chmod($installPath, 0755);

        $output->writeln('<info>📄 Script install.sh generado exitosamente</info>');
    }

    /**
     * Procesa un bloque condicional en el stub:
     *   {{TAG}} ... contenido ... {{/TAG}}  → se incluye si $condition es true
     *   {{!TAG}} ... contenido ... {{/!TAG}} → se incluye si $condition es false
     *
     * Si la condición no se cumple, el bloque (incluidas sus líneas) se elimina completamente.
     */
    protected function processConditionalBlock(string $content, string $tag, bool $condition): string
    {
        // Bloque positivo: {{TAG}} ... {{/TAG}}
        $content = preg_replace_callback(
            '/\{\{' . $tag . '\}\}(.*?)\{\{\/' . $tag . '\}\}/s',
            function (array $matches) use ($condition): string {
                return $condition ? trim($matches[1]) . "\n" : '';
            },
            $content
        );

        // Bloque negado: {{!TAG}} ... {{/!TAG}}
        $content = preg_replace_callback(
            '/\{\{!' . $tag . '\}\}(.*?)\{\{\/!' . $tag . '\}\}/s',
            function (array $matches) use ($condition): string {
                return !$condition ? trim($matches[1]) . "\n" : '';
            },
            $content
        );

        return $content;
    }
}
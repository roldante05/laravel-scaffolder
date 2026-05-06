<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\Builders;

use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;
use Roldante05\ScaffoldingFactory\DTOs\PhpVanillaOptions;
use Roldante05\ScaffoldingFactory\Helpers\StubProcessor;
use Symfony\Component\Console\Output\OutputInterface;

class PhpVanillaBuilder implements BuilderInterface
{
    public function build(string $projectName, ProjectOptions $options, OutputInterface $output): int
    {
        /** @var PhpVanillaOptions $options */
        $output->writeln('<info>📦 Creating PHP Vanilla project...</info>');
        $projectPath = getcwd() . DIRECTORY_SEPARATOR . $projectName;

        try {
            // 1. Create directory structure
            $this->createDirectories($projectPath, $options);

            // 2. Generate files from stubs
            $this->generateFiles($projectPath, $options);

            $output->writeln('<info>✅ Project structure created.</info>');
            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    protected function createDirectories(string $path, PhpVanillaOptions $options): void
    {
        @mkdir($path, 0755, true);
        @mkdir($path . '/src', 0755, true);
        @mkdir($path . '/src/Core', 0755, true);
        @mkdir($path . '/src/Controllers', 0755, true);
        @mkdir($path . '/src/Models', 0755, true);
        @mkdir($path . '/src/Views', 0755, true);
        @mkdir($path . '/src/Views/layout', 0755, true);
        @mkdir($path . '/src/resources', 0755, true);

        if ($options->login) {
            @mkdir($path . '/src/Views/form', 0755, true);
        }
    }

    protected function generateFiles(string $path, PhpVanillaOptions $options): void
    {
        $templatesDir = __DIR__ . '/../Templates/php-vanilla';

        $tags = [
            'USE_MYSQL' => $options->database === 'mysql',
            'USE_SQLITE' => $options->database === 'sqlite',
            'USE_LOGIN' => $options->login,
            'USE_TAILWIND' => $options->css === 'Tailwind CSS',
            'USE_BOOTSTRAP' => $options->css === 'Bootstrap',
        ];

        $variables = [
            'PROJECT_NAME' => basename($path),
            'DB_DATABASE' => basename($path),
        ];

        // Files to process
        $files = [
            'index.php.stub' => 'index.php',
            'Core/Router.php.stub' => 'src/Core/Router.php',
            'Core/Controller.php.stub' => 'src/Core/Controller.php',
            'Core/Model.php.stub' => 'src/Core/Model.php',
            'Core/Database.php.stub' => 'src/Core/Database.php',
            'Controllers/HomeController.php.stub' => 'src/Controllers/HomeController.php',
            'Views/welcome.php.stub' => 'src/Views/welcome.php',
            'Views/home.php.stub' => 'src/Views/home.php',
            'Views/nav.php.stub' => 'src/Views/nav.php',
            'Views/contact.php.stub' => 'src/Views/contact.php',
            'Views/about.php.stub' => 'src/Views/about.php',
            'Views/layout/sidebar.php.stub' => 'src/Views/layout/sidebar.php',
            'Views/layout/header.php.stub' => 'src/Views/layout/header.php',
            'htaccess.stub' => '.htaccess',
            'docker-compose.yml.stub' => 'docker-compose.yml',
            'Dockerfile.stub' => 'Dockerfile',
            'composer.json.stub' => 'composer.json',
            'install.sh.stub' => 'install.sh',
        ];

        if ($options->database !== 'none') {
            $files['env.stub'] = '.env';
        }

        if ($options->login) {
            $files['Controllers/AuthController.php.stub'] = 'src/Controllers/AuthController.php';
            $files['Models/User.php.stub'] = 'src/Models/User.php';
            $files['Views/form/login.php.stub'] = 'src/Views/form/login.php';
            $files['Views/form/register.php.stub'] = 'src/Views/form/register.php';
        }

        foreach ($files as $stub => $dest) {
            $stubFile = $templatesDir . '/' . $stub;
            if (file_exists($stubFile)) {
                $content = file_get_contents($stubFile);
                $processed = StubProcessor::process($content, $variables, $tags);
                file_put_contents($path . '/' . $dest, $processed);

                if (str_ends_with($dest, '.sh')) {
                    chmod($path . '/' . $dest, 0755);
                }
            }
        }
    }
}
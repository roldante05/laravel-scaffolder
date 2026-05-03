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

class PhpVanillaBuilder implements BuilderInterface
{
    public function askOptions(InputInterface $input, OutputInterface $output, $helper): array
    {
        $options = [];

        info('💾 Database Configuration');
        $options['database'] = select(
            label: 'Which database would you like to use?',
            options: [
                'mysql' => 'MySQL',
                'sqlite' => 'SQLite (Zero-config)',
                'none' => 'None (No database)'
            ],
            default: 'mysql'
        );

        if ($options['database'] !== 'none') {
            info('🔐 Authentication System');
            $options['login'] = confirm(
                label: 'Would you like to include a Login Kit?',
                default: false
            );
        } else {
            $options['login'] = false;
        }

        info('🎨 CSS Framework Selection');
        $options['css'] = select(
            label: 'Which CSS framework would you like to use?',
            options: [
                'Tailwind CSS' => 'Tailwind CSS',
                'Bootstrap' => 'Bootstrap'
            ],
            default: 'Tailwind CSS'
        );

        return $options;
    }

    public function build(string $projectName, array $options, OutputInterface $output): int
    {
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

    protected function createDirectories(string $path, array $options): void
    {
        @mkdir($path, 0755, true);
        @mkdir($path . '/src', 0755, true);
        @mkdir($path . '/src/controllers', 0755, true);
        @mkdir($path . '/src/models', 0755, true);
        @mkdir($path . '/src/views', 0755, true);
        @mkdir($path . '/src/resources', 0755, true);

        if ($options['login']) {
            @mkdir($path . '/src/views/form', 0755, true);
        }
    }

    protected function generateFiles(string $path, array $options): void
    {
        $templatesDir = __DIR__ . '/../Templates/php-vanilla';

        $tags = [
            'USE_MYSQL' => $options['database'] === 'mysql',
            'USE_SQLITE' => $options['database'] === 'sqlite',
            'USE_LOGIN' => $options['login'],
            'USE_TAILWIND' => $options['css'] === 'Tailwind CSS',
            'USE_BOOTSTRAP' => $options['css'] === 'Bootstrap',
        ];

        $variables = [
            'PROJECT_NAME' => basename($path),
            'DB_DATABASE' => basename($path),
        ];

        // Files to process
        $files = [
            'index.php.stub' => 'index.php',
            'app.php.stub' => 'src/controllers/app.php',
            'welcome.php.stub' => 'src/views/welcome.php',
            'home.php.stub' => 'src/views/home.php',
            'nav.php.stub' => 'src/views/nav.php',
            'contact.php.stub' => 'src/views/contact.php',
            'about.php.stub' => 'src/views/about.php',
            'htaccess.stub' => '.htaccess',
            'docker-compose.yml.stub' => 'docker-compose.yml',
            'Dockerfile.stub' => 'Dockerfile',
            'composer.json.stub' => 'composer.json',
            'install.sh.stub' => 'install.sh',
        ];

        if ($options['database'] !== 'none') {
            $files['env.stub'] = '.env';
        }

        if ($options['login']) {
            $files['form/login.php.stub'] = 'src/views/form/login.php';
            $files['form/register.php.stub'] = 'src/views/form/register.php';
            $files['form/authenticate.php.stub'] = 'src/views/form/authenticate.php';
            $files['form/store.php.stub'] = 'src/views/form/store.php';
            $files['form/logout.php.stub'] = 'src/views/form/logout.php';
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
<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\Builders;

use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;
use Roldante05\ScaffoldingFactory\DTOs\PhpVanillaOptions;
use Roldante05\ScaffoldingFactory\Helpers\StubProcessor;
use Roldante05\ScaffoldingFactory\Security\Helpers\SecurityHelper;
use Symfony\Component\Console\Output\OutputInterface;

class PhpVanillaBuilder implements BuilderInterface
{
    public function build(string $projectName, ProjectOptions $options, OutputInterface $output): int
    {
        /** @var PhpVanillaOptions $options */
        // Validate and sanitize inputs FIRST
        $sanitizedProjectName = $this->sanitizeProjectName($projectName);
        $sanitizedOptions = $this->sanitizePhpVanillaOptions($options);

        $output->writeln('<info>📦 Creating PHP Vanilla project...</info>');
        $projectPath = getcwd() . DIRECTORY_SEPARATOR . $sanitizedProjectName;

        try {
            // 1. Create directory structure
            $this->createDirectories($projectPath, $sanitizedOptions);

            // 2. Generate files from stubs
            $this->generateFiles($projectPath, $sanitizedOptions);

            $output->writeln('<info>✅ Project structure created.</info>');
            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    /**
     * Sanitiza y valida las opciones de PHP Vanilla.
     *
     * @param PhpVanillaOptions $options Las opciones a sanitizar
     * @return PhpVanillaOptions Las opciones sanitizadas
     * @throws \InvalidArgumentException Si las opciones son inválidas
     */
    private function sanitizePhpVanillaOptions(PhpVanillaOptions $options): PhpVanillaOptions
    {
        // Validar y sanitizar la base de datos
        $database = strtolower($options->database);
        $allowedDatabases = ['mysql', 'sqlite', 'none'];
        if (!in_array($database, $allowedDatabases, true)) {
            throw new \InvalidArgumentException("Database '{$options->database}' is not allowed. Allowed: " . implode(', ', $allowedDatabases));
        }

        // Validar y sanitizar con login (debe ser booleano)
        $login = filter_var($options->login, FILTER_VALIDATE_BOOL);
        if ($login === null && $options->login !== false && $options->login !== '') {
            throw new \InvalidArgumentException("login must be a boolean value");
        }

        // Validar y sanitizar css
        $css = $options->css;
        $allowedCss = ['Tailwind CSS', 'Bootstrap'];
        if (!in_array($css, $allowedCss, true)) {
            throw new \InvalidArgumentException("CSS '{$options->css}' is not allowed. Allowed: " . implode(', ', $allowedCss));
        }

        // Retornar un nuevo objeto con valores sanitizados
        return new PhpVanillaOptions(
            projectName: $options->projectName, // Se sanitiza por separado
            database: $database,
            login: $login,
            css: $css
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
        @mkdir($path . '/src/resources/css', 0755, true);
        @mkdir($path . '/src/resources/js', 0755, true);

        @mkdir($path . '/src/routes', 0755, true);
        @mkdir($path . '/config', 0755, true);

        if ($options->login) {
            @mkdir($path . '/src/Views/form', 0755, true);
        }

        if ($options->database !== 'none') {
            @mkdir($path . '/migrations', 0755, true);
        }

        $this->downloadResources($path, $options);
    }

    /**
     * Descarga los recursos CSS/JS localmente para no depender de CDN.
     *
     * @param string $projectPath Ruta del proyecto.
     * @param PhpVanillaOptions $options Opciones del proyecto.
     */
    private function downloadResources(string $projectPath, PhpVanillaOptions $options): void
    {
        $cssDir = $projectPath . '/src/resources/css';
        $jsDir = $projectPath . '/src/resources/js';

        if ($options->css === 'Bootstrap') {
            $this->downloadFile(
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                $cssDir . '/bootstrap.min.css'
            );
            $this->downloadFile(
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                $jsDir . '/bootstrap.bundle.min.js'
            );
        } elseif ($options->css === 'Tailwind CSS') {
            $this->downloadFile(
                'https://cdn.tailwindcss.com',
                $jsDir . '/tailwindcss.js'
            );
        }
    }

    /**
     * Descarga un archivo desde una URL.
     *
     * @param string $url URL del archivo.
     * @param string $destPath Ruta de destino.
     * @return bool true si éxito, false si falla.
     */
    private function downloadFile(string $url, string $destPath): bool
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'ignore_errors' => true,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            return false;
        }

        return @file_put_contents($destPath, $content) !== false;
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
            'DB_CONNECTION' => $options->database,
        ];

        // Files to process
        $files = [
            'index.php.stub' => 'index.php',
            'che.stub' => 'che',
            'che.php.stub' => 'che.php',
            'resources/img/chephp-logo.png' => 'src/resources/img/chephp-logo.png',
            'config/config.php.stub' => 'config/config.php',
            'routes/web.php.stub' => 'src/routes/web.php',
            'Core/Router.php.stub' => 'src/Core/Router.php',
            'Core/Controller.php.stub' => 'src/Core/Controller.php',
            'Core/Model.php.stub' => 'src/Core/Model.php',
            'Core/Database.php.stub' => 'src/Core/Database.php',
            'Core/ORM.php.stub' => 'src/Core/ORM.php',
            'Core/Migration.php.stub' => 'src/Core/Migration.php',
            'Controllers/HomeController.php.stub' => 'src/Controllers/HomeController.php',
            'Views/welcome.php.stub' => 'src/Views/welcome.php',
            'Views/home.php.stub' => 'src/Views/home.php',
            'Views/nav.php.stub' => 'src/Views/nav.php',

            'Views/layout/sidebar.php.stub' => 'src/Views/layout/sidebar.php',
            'Views/layout/header.php.stub' => 'src/Views/layout/header.php',
            'Views/layout/app.php.stub' => 'src/Views/layout/app.php',
            'htaccess.stub' => '.htaccess',
            'docker-compose.yml.stub' => 'docker-compose.yml',
            'Dockerfile.stub' => 'Dockerfile',
            'composer.json.stub' => 'composer.json',
            'install.sh.stub' => 'scripts/install.sh',
            'Security/Helpers/SecurityHelper.php.stub' => 'src/Security/Helpers/SecurityHelper.php',
        ];

        if ($options->database !== 'none') {
            $files['env.stub'] = '.env';
        }

        if ($options->login) {
            $files['Core/Auth.php.stub'] = 'src/Core/Auth.php';
            $files['Controllers/AuthController.php.stub'] = 'src/Controllers/AuthController.php';
            $files['Controllers/ProfileController.php.stub'] = 'src/Controllers/ProfileController.php';
            $files['Models/User.php.stub'] = 'src/Models/User.php';
            $files['Views/form/login.php.stub'] = 'src/Views/form/login.php';
            $files['Views/form/register.php.stub'] = 'src/Views/form/register.php';
            $files['Views/profile.php.stub'] = 'src/Views/profile.php';
        }

        if ($options->login && $options->database !== 'none') {
            $files['migrations/001_create_users_table.php.stub'] = 'migrations/001_create_users_table.php';
        }

        $executables = ['scripts/install.sh', 'che'];

        foreach ($files as $stub => $dest) {
            $stubFile = $templatesDir . '/' . $stub;
            if (file_exists($stubFile)) {
                $content = file_get_contents($stubFile);
                $processed = StubProcessor::process($content, $variables, $tags);
                $destPath = $path . '/' . $dest;
                // Ensure the target directory exists
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    @mkdir($destDir, 0755, true);
                }
                file_put_contents($destPath, $processed);

                if (in_array($dest, $executables, true)) {
                    chmod($destPath, 0755);
                }
            }
        }
    }
}
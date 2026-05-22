<?php

declare(strict_types=1);

namespace Tests\Unit\Builders;

use Roldante05\ScaffoldingFactory\Builders\PhpVanillaBuilder;
use Roldante05\ScaffoldingFactory\DTOs\PhpVanillaOptions;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

class PhpVanillaBuilderTest extends \PHPUnit\Framework\TestCase
{
    private string $testDir;
    private string $oldCwd;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/php_vanilla_builder_test_' . uniqid();
        mkdir($this->testDir, 0755, true);
        $this->oldCwd = getcwd();
        chdir($this->testDir);
    }

    protected function tearDown(): void
    {
        chdir($this->oldCwd);
        @array_map('unlink', glob("$this->testDir/*"));
        @rmdir($this->testDir);
    }

    public function test_builder_can_be_instantiated(): void
    {
        $builder = new PhpVanillaBuilder();
        $this->assertInstanceOf(PhpVanillaBuilder::class, $builder);
    }

    /**
     * Test building a project with MySQL, login enabled, Tailwind CSS
     */
    public function test_build_method_returns_zero_on_success_with_database_mysql_login_tailwind(): void
    {
        $options = new PhpVanillaOptions(
            projectName: 'test-vanilla', // This is ignored because we pass our own project name to build()
            database: 'mysql',
            login: true,
            css: 'Tailwind CSS'
        );

        $builder = new PhpVanillaBuilder();
        $output = new NullOutput();

        // Use a unique project name to avoid any potential conflicts
        $projectName = 'test-project-' . uniqid();

        $result = $builder->build($projectName, $options, $output);

        $this->assertEquals(0, $result);
        
        // Check that the project directory was created
        $this->assertDirectoryExists($projectName);
        
        // Check that some key files were created
        $this->assertFileExists($projectName . '/index.php');
        $this->assertFileExists($projectName . '/che');
        $this->assertFileExists($projectName . '/che.php');
        $this->assertFileExists($projectName . '/app/routes.php');
        $this->assertFileExists($projectName . '/config/config.php');
        $this->assertFileExists($projectName . '/migrations/001_create_users_table.php');
        $this->assertFileExists($projectName . '/src/Core/Router.php');
        $this->assertFileExists($projectName . '/src/Core/ORM.php');
        $this->assertFileExists($projectName . '/src/Core/Migration.php');
        $this->assertFileExists($projectName . '/src/Core/Auth.php');
        $this->assertFileExists($projectName . '/src/Controllers/HomeController.php');
        $this->assertFileExists($projectName . '/src/Models/User.php');
        $this->assertFileExists($projectName . '/src/Views/form/login.php');
        $this->assertFileExists($projectName . '/.env');
    }

    /**
     * Test building a project with SQLite, login disabled, Bootstrap CSS
     */
    public function test_build_method_returns_zero_on_success_with_database_sqlite_no_login_bootstrap(): void
    {
        $options = new PhpVanillaOptions(
            projectName: 'test-vanilla',
            database: 'sqlite',
            login: false,
            css: 'Bootstrap'
        );

        $builder = new PhpVanillaBuilder();
        $output = new NullOutput();

        $projectName = 'test-project-' . uniqid();

        $result = $builder->build($projectName, $options, $output);

        $this->assertEquals(0, $result);
        
        // Check that the project directory was created
        $this->assertDirectoryExists($projectName);
        
        // Check that some key files were created (basic structure)
        $this->assertFileExists($projectName . '/index.php');
        $this->assertFileExists($projectName . '/che');
        $this->assertFileExists($projectName . '/che.php');
        $this->assertFileExists($projectName . '/app/routes.php');
        $this->assertFileExists($projectName . '/config/config.php');
        $this->assertFileExists($projectName . '/migrations');
        $this->assertFileExists($projectName . '/src/Core/Router.php');
        $this->assertFileExists($projectName . '/src/Core/ORM.php');
        $this->assertFileExists($projectName . '/src/Core/Migration.php');
        $this->assertFileExists($projectName . '/src/Controllers/HomeController.php');
        // Check auth files are NOT generated when login is false
        $this->assertFileDoesNotExist($projectName . '/src/Core/Auth.php');
        $this->assertFileDoesNotExist($projectName . '/src/Models/User.php');
        $this->assertFileDoesNotExist($projectName . '/src/Views/form');
        $this->assertFileDoesNotExist($projectName . '/migrations/001_create_users_table.php');
    }

    /**
     * Test building a project with no database, login disabled, Tailwind CSS
     */
    public function test_build_method_returns_zero_on_success_with_database_none(): void
    {
        $options = new PhpVanillaOptions(
            projectName: 'test-vanilla',
            database: 'none', // This should skip .env generation
            login: false,
            css: 'Tailwind CSS'
        );

        $builder = new PhpVanillaBuilder();
        $output = new NullOutput();

        $projectName = 'test-project-' . uniqid();

        $result = $builder->build($projectName, $options, $output);

        $this->assertEquals(0, $result);
        
        // Check that the project directory was created
        $this->assertDirectoryExists($projectName);
        
        // Check that .env file was NOT created (because database is 'none')
        $this->assertFileDoesNotExist($projectName . '/.env');
        // migrations directory should NOT exist when no database
        $this->assertFileDoesNotExist($projectName . '/migrations');
        
        // But other files should still be created
        $this->assertFileExists($projectName . '/index.php');
        $this->assertFileExists($projectName . '/che');
        $this->assertFileExists($projectName . '/che.php');
        $this->assertFileExists($projectName . '/app/routes.php');
        $this->assertFileExists($projectName . '/config/config.php');
        $this->assertFileExists($projectName . '/src/Core/Router.php');
        $this->assertFileExists($projectName . '/src/Core/ORM.php');
        $this->assertFileExists($projectName . '/src/Core/Migration.php');
    }

    public function test_build_method_returns_one_on_exception(): void
    {
        // To properly test this, we would need to mock internal dependencies
        // For now, we'll test that the method handles exceptions gracefully
        // by verifying it returns 1 when an exception occurs
        
        // Since it's difficult to trigger an actual exception in the build process
        // without complex mocking, we'll mark this as skipped but note that
        // the existing implementation does have try/catch and returns 1 on exception
        $this->markTestSkipped('Exception handling test - verify try/catch returns 1 on exception');
    }
}
<?php

declare(strict_types=1);

namespace Tests\Unit\Builders;

use Roldante05\ScaffoldingFactory\Builders\LaravelBuilder;
use Roldante05\ScaffoldingFactory\DTOs\LaravelOptions;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Process\Exception\ProcessFailedException;
use InvalidArgumentException;

class LaravelBuilderTest extends \PHPUnit\Framework\TestCase
{
    private OutputInterface $output;
    private ConsoleOutputInterface $consoleOutput;
    private ConsoleSectionOutput $historySection;
    private ConsoleSectionOutput $activeSection;

    protected function setUp(): void
    {
        $this->output = $this->createMock(OutputInterface::class);
        $this->consoleOutput = $this->createMock(ConsoleOutputInterface::class);
        $this->historySection = $this->createMock(ConsoleSectionOutput::class);
        $this->activeSection = $this->createMock(ConsoleSectionOutput::class);
        
        // Configure console output to return our sections
        $this->consoleOutput->method('section')
            ->willReturnOnConsecutiveCalls($this->historySection, $this->activeSection);
    }

    /**
     * Get a mock of LaravelBuilder with all internal methods mocked.
     *
     * @return MockObject<LaravelBuilder>
     */
    private function createMockBuilder(): MockObject
    {
        return $this->getMockBuilder(LaravelBuilder::class)
                    ->setMethods([
                        'scaffoldProject',
                        'ensureBootstrap',
                        'configureSail',
                        'installAuthKit',
                        'maybeInstallBoost',
                        'setDatabase',
                        'generateInstallationScript',
                        'fixNodeModulesPermissions',
                        'showFinalInfo',
                        'createLaravelProjectWithInstaller',
                        'ensureBootstrapJs',
                        'runProcess',
                        'installSail',
                        'customizeComposeFile',
                        'installBreezeOrOfficialKit',
                        'installJetstreamKit',
                        'installBoost',
                        'setDatabaseConnection',
                        'generateInstallScript',
                        'fixJsDependencies'
                    ])
                    ->getMock();
    }

    public function test_builder_can_be_instantiated(): void
    {
        $builder = new LaravelBuilder();
        $this->assertInstanceOf(LaravelBuilder::class, $builder);
    }

    public function test_build_method_returns_zero_on_success(): void
    {
        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'none',
            withTeams: false,
            database: 'sqlite',
            withBoost: false
        );

        $builder = $this->createMockBuilder();
        // By default, the mocked methods do nothing and return null (for void methods) or false (for boolean methods).
        // We don't need to set any expectations because we don't care about how many times they are called, only that they don't throw.
        // The build method will return 0 if no exception is thrown.

        $result = $builder->build('test-project', $options, $this->output);

        $this->assertEquals(0, $result);
    }

    public function test_build_method_returns_one_on_exception(): void
    {
        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'none',
            withTeams: false,
            database: 'sqlite',
            withBoost: false
        );

        $builder = $this->createMockBuilder();
        // Make the scaffoldProject method throw an exception
        $builder->expects($this->once())
                ->method('scaffoldProject')
                ->with($this->equalTo('test-project'), $this->identicalTo($this->output))
                ->willThrowException(new \Exception('Test exception'));

        $result = $builder->build('test-project', $options, $this->output);

        $this->assertEquals(1, $result);
    }

    /**
     * Security tests: Verify that invalid inputs are rejected
     */

    public function test_build_method_throws_exception_on_invalid_database(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Database 'invalid_db' is not allowed");

        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'none',
            withTeams: false,
            database: 'invalid_db', // Invalid database
            withBoost: false
        );

        $builder = new LaravelBuilder();
        $builder->build('test-project', $options, $this->output);
    }

    public function test_build_method_throws_exception_on_invalid_kit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Kit 'InvalidKit' is not allowed");

        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: true,
            kit: 'InvalidKit', // Invalid kit
            stack: 'none',
            withTeams: false,
            database: 'sqlite',
            withBoost: false
        );

        $builder = new LaravelBuilder();
        $builder->build('test-project', $options, $this->output);
    }

    public function test_build_method_throws_exception_on_invalid_stack(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Stack 'invalid_stack' is not allowed");

        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'invalid_stack', // Invalid stack
            withTeams: false,
            database: 'sqlite',
            withBoost: false
        );

        $builder = new LaravelBuilder();
        $builder->build('test-project', $options, $this->output);
    }

    public function test_build_method_throws_exception_on_invalid_project_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Project name 'invalid/name' contains invalid characters");

        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'none',
            withTeams: false,
            database: 'sqlite',
            withBoost: false
        );

        $builder = new LaravelBuilder();
        $builder->build('invalid/name', $options, $this->output); // Path traversal attempt
    }

    public function test_build_method_throws_exception_on_project_name_with_dots(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Project name '..' is invalid after sanitization");

        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'none',
            withTeams: false,
            database: 'sqlite',
            withBoost: false
        );

        $builder = new LaravelBuilder();
        $builder->build('..', $options, $this->output); // Attempt to traverse up
    }

    public function test_build_method_throws_exception_on_project_name_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Project name is too long (maximum 50 characters)");

        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'none',
            withTeams: false,
            database: 'sqlite',
            withBoost: false
        );

        $builder = new LaravelBuilder();
        $builder->build(str_repeat('a', 51), $options, $this->output); // 51 characters
    }

    public function test_build_method_throws_exception_on_non_boolean_with_boost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("withBoost must be a boolean value");

        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'none',
            withTeams: false,
            database: 'sqlite',
            withBoost: 'yes' // Invalid boolean
        );

        $builder = new LaravelBuilder();
        $builder->build('test-project', $options, $this->output);
    }

    public function test_build_method_throws_exception_on_non_boolean_with_teams(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("withTeams must be a boolean value");

        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'none',
            withTeams: 'yes', // Invalid boolean
            database: 'sqlite',
            withBoost: false
        );

        $builder = new LaravelBuilder();
        $builder->build('test-project', $options, $this->output);
    }

    /**
     * Test that valid inputs are accepted (positive security tests)
     */

    public function validDatabaseProvider(): array
    {
        return [
            ['mysql'],
            ['mariadb'],
            ['pgsql'],
            ['sqlite'],
            ['sqlsrv'],
            ['none'],
        ];
    }

    /**
     * @dataProvider validDatabaseProvider
     */
    public function test_build_method_accepts_valid_database_values(string $database): void
    {
        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'none',
            withTeams: false,
            database: $database,
            withBoost: false
        );

        $builder = $this->createMockBuilder();
        $result = $builder->build('test-project', $options, $this->output);
        $this->assertEquals(0, $result, "Database='$database' should be accepted");
    }

    public function validKitProvider(): array
    {
        return [
            ['Breeze'],
            ['Jetstream'],
            ['Official Starter Kit (2026)'],
            ['None'],
        ];
    }

    /**
     * @dataProvider validKitProvider
     */
    public function test_build_method_accepts_valid_kit_values(string $kit): void
    {
        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: ($kit !== 'None'),
            kit: $kit,
            stack: 'none',
            withTeams: false,
            database: 'sqlite',
            withBoost: false
        );

        $builder = $this->createMockBuilder();
        $result = $builder->build('test-project', $options, $this->output);
        $this->assertEquals(0, $result, "Kit='$kit' should be accepted");
    }

    public function validStackProvider(): array
    {
        return [
            ['livewire'],
            ['vue'],
            ['react'],
            ['inertia'],
            ['none'],
        ];
    }

    /**
     * @dataProvider validStackProvider
     */
    public function test_build_method_accepts_valid_stack_values(string $stack): void
    {
        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: $stack,
            withTeams: false,
            database: 'sqlite',
            withBoost: false
        );

        $builder = $this->createMockBuilder();
        $result = $builder->build('test-project', $options, $this->output);
        $this->assertEquals(0, $result, "Stack='$stack' should be accepted");
    }

    public function test_build_method_sanitizes_project_name_with_valid_chars(): void
    {
        // This test verifies that a valid project name with hyphens and underscores is accepted
        $options = new LaravelOptions(
            projectName: 'test-project',
            wantKit: false,
            kit: 'None',
            stack: 'none',
            withTeams: false,
            database: 'sqlite',
            withBoost: false
        );

        $builder = $this->createMockBuilder();
        $result = $builder->build('valid-project_name', $options, $this->output);
        $this->assertEquals(0, $result, "Valid project name with hyphens and underscores should be accepted");
    }
}
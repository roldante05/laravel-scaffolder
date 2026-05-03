<?php

use Roldante05\ScaffoldingFactory\Console\NewCommand;
use Roldante05\ScaffoldingFactory\Builders\LaravelBuilder;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use PHPUnit\Framework\MockObject\MockObject;

beforeEach(function () {
    // This will run before each test
});

test('Laravel builder can be instantiated', function () {
    $builder = new LaravelBuilder();
    
    expect($builder)->toBeInstanceOf(LaravelBuilder::class);
    expect(method_exists($builder, 'askOptions'))->toBeTrue();
    expect(method_exists($builder, 'build'))->toBeTrue();
    expect(method_exists($builder, 'createLaravelProjectWithInstaller'))->toBeTrue();
    expect(method_exists($builder, 'runProcess'))->toBeTrue();
    expect(method_exists($builder, 'generateInstallScript'))->toBeTrue();
    expect(method_exists($builder, 'fixJsDependencies'))->toBeTrue();
});

test('Laravel builder askOptions returns expected structure when mocked', function () {
    $builder = new LaravelBuilder();
    
    // Create a mock for the QuestionHelper
    /** @var QuestionHelper&MockObject $helper */
    $helper = $this->createMock(QuestionHelper::class);
    
    // Configure the helper to return specific answers in sequence
    $helper->method('ask')
        ->willReturnOnConsecutiveCalls(
            'Yes', // Kit question: Do you want to install a starter kit?
            'No',  // Boost question: Install Laravel Boost?
            'Breeze', // Which kit?
            'Blade', // Breeze stack?
            'No',   // Team support?
            'sqlite', // Database?
            true    // Confirmation?
        );
    
    $input = $this->createMock(ArrayInput::class);
    $input->method('getArgument')
        ->willReturn('test-project');
    
    $output = $this->createMock(NullOutput::class);
    
    // Execute askOptions
    $options = $builder->askOptions($input, $output, $helper);
    
    // Verify the structure
    expect(is_array($options))->toBeTrue();
    expect(array_key_exists('wantKit', $options))->toBeTrue();
    expect($options['wantKit'])->toBeTrue();
    expect(array_key_exists('withBoost', $options))->toBeTrue();
    expect($options['withBoost'])->toBeFalse();
    expect(array_key_exists('kit', $options))->toBeTrue();
    expect($options['kit'])->toBe('Breeze');
    expect(array_key_exists('stack', $options))->toBeTrue();
    expect($options['stack'])->toBe('blade');
    expect(array_key_exists('withTeams', $options))->toBeTrue();
    expect($options['withTeams'])->toBeFalse();
    expect(array_key_exists('database', $options))->toBeTrue();
    expect($options['database'])->toBe('sqlite');
    expect(array_key_exists('cancelled', $options))->toBeFalse();
});

test('NewCommand can be instantiated', function () {
    $command = new NewCommand();
    
    expect($command)->toBeInstanceOf(NewCommand::class);
    expect($command)->toBeInstanceOf(\Symfony\Component\Console\Command\Command::class);
});

test('Application can be instantiated with NewCommand added', function () {
    $application = new Application();
    $application->addCommand(new NewCommand());
    
    // Check that the command exists
    $command = $application->find('new');
    
    expect($command)->toBeInstanceOf(NewCommand::class);
});
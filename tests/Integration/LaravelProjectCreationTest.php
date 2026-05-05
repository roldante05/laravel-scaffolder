<?php

declare(strict_types=1);

use Roldante05\ScaffoldingFactory\Console\NewCommand;
use Roldante05\ScaffoldingFactory\Builders\LaravelBuilder;
use Roldante05\ScaffoldingFactory\Interactions\LaravelInteractionHandler;
use Symfony\Component\Console\Application;

test('Laravel builder can be instantiated', function () {
    $builder = new LaravelBuilder();
    
    expect($builder)->toBeInstanceOf(LaravelBuilder::class);
    expect(method_exists($builder, 'build'))->toBeTrue();
});

test('Laravel interaction handler can be instantiated', function () {
    $handler = new LaravelInteractionHandler();
    expect($handler)->toBeInstanceOf(LaravelInteractionHandler::class);
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
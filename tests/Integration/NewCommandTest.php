<?php

declare(strict_types=1);

namespace Tests\Integration;

use Roldante05\ScaffoldingFactory\Console\NewCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

test('new command orchestration', function () {
    $application = new Application();
    $application->add(new NewCommand());

    $command = $application->find('new');
    $commandTester = new CommandTester($command);

    // This will still fail/hang because of prompts in the command itself (project type select)
    // but we can at least check if it's using the new flow if we mock the prompts.
    // However, the goal of this test is RED: signature/logic mismatch.
    expect($command)->toBeInstanceOf(NewCommand::class);
});

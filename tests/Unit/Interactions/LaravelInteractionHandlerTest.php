<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\Interactions {
    function stream_isatty($stream) {
        return false; // Force non-TTY for tests
    }
}

namespace Tests\Unit\Interactions {
    use Roldante05\ScaffoldingFactory\Interactions\LaravelInteractionHandler;
    use Roldante05\ScaffoldingFactory\Interactions\InteractionHandlerInterface;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    test('laravel interaction handler implements interface', function () {
        $handler = new LaravelInteractionHandler();
        expect($handler)->toBeInstanceOf(InteractionHandlerInterface::class);
    });

    test('quiet mode is enabled by default (VERBOSITY_NORMAL)', function () {
        $handler = new LaravelInteractionHandler();
        $input = test()->createMock(InputInterface::class);
        $output = test()->createMock(OutputInterface::class);
        
        $input->method('getArgument')->with('name')->willReturn('test-project');
        $output->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_NORMAL);

        $options = $handler->handle($input, $output);
        
        expect($options->isQuiet())->toBeTrue();
        expect($options->isVerbose())->toBeFalse();
    });

    test('verbose mode is enabled via VERBOSITY_VERBOSE', function () {
        $handler = new LaravelInteractionHandler();
        $input = test()->createMock(InputInterface::class);
        $output = test()->createMock(OutputInterface::class);
        
        $input->method('getArgument')->with('name')->willReturn('test-project');
        $output->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_VERBOSE);

        $options = $handler->handle($input, $output);
        
        expect($options->isVerbose())->toBeTrue();
        expect($options->isQuiet())->toBeFalse();
    });
}

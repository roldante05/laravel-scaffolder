<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\Console;

use Roldante05\ScaffoldingFactory\Builders\LaravelBuilder;
use Roldante05\ScaffoldingFactory\Builders\PhpVanillaBuilder;
use Roldante05\ScaffoldingFactory\Interactions\LaravelInteractionHandler;
use Roldante05\ScaffoldingFactory\Interactions\PhpVanillaInteractionHandler;
use Roldante05\ScaffoldingFactory\DTOs\LaravelOptions;
use Roldante05\ScaffoldingFactory\DTOs\PhpVanillaOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use function Laravel\Prompts\note;
use function Laravel\Prompts\error;

class NewCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('new')
            ->setDescription('Create a new project (Laravel or PHP Vanilla)')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the project');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->renderLogo($output);
        $projectName = $input->getArgument('name');

        Prompt::setOutput($output);
        intro('🚀 Welcome to Scaffolding Factory!');

        if (!$projectName) {
            $projectName = text(
                label: 'What is the name of your project?',
                placeholder: 'my-awesome-project',
                required: true,
                validate: fn(string $value) => trim($value) !== '' ? true : 'Project name cannot be empty.'
            );
            $input->setArgument('name', $projectName);
        }

        $projectType = select(
            label: 'What type of project would you like to create?',
            options: ['Laravel', 'PHP Vanilla'],
            default: 'Laravel'
        );

        $handler = $projectType === 'Laravel'
            ? new LaravelInteractionHandler()
            : new PhpVanillaInteractionHandler();

        $builder = $projectType === 'Laravel'
            ? new LaravelBuilder()
            : new PhpVanillaBuilder();

        $options = $handler->handle($input, $output);

        // Summary
        $summaryLines = [
            "Project Name: $projectName",
            "Type: $projectType",
        ];

        if ($options instanceof LaravelOptions) {
            $summaryLines[] = "Starter Kit: " . ($options->wantKit ? $options->kit : 'None');
            $summaryLines[] = "Stack: " . $options->stack;
            $summaryLines[] = "Database: " . $options->database;
            $summaryLines[] = "Boost: " . ($options->withBoost ? 'Yes' : 'No');
        } elseif ($options instanceof PhpVanillaOptions) {
            $summaryLines[] = "Database: " . $options->database;
            $summaryLines[] = "Login Kit: " . ($options->login ? 'Yes' : 'No');
            $summaryLines[] = "CSS: " . $options->css;
        }

        note(implode("\n", $summaryLines), 'Configuration Summary');

        if (!confirm('Does everything look correct?', true)) {
            error('❌ Operation cancelled.');
            return Command::FAILURE;
        }

        return $builder->build($projectName, $options, $output);
    }

    protected function renderLogo(OutputInterface $output): void
    {
        $output->writeln([
            '',
            ' <fg=white> ███████╗ ██████╗ █████╗ ███████╗███████╗ ██████╗ ██╗     ██████╗ ██╗███╗   ██╗ ██████╗ </>',
            ' <fg=white> ██╔════╝██╔════╝██╔══██╗██╔════╝██╔════╝██╔═══██╗██║     ██╔══██╗██║████╗  ██║██╔════╝ </>',
            ' <fg=white> ███████╗██║     ███████║█████╗  █████╗  ██║   ██║██║     ██║  ██║██║██╔██╗ ██║██║  ███╗</>',
            ' <fg=red> ╚════██║██║     ██╔══██║██╔══╝  ██╔══╝  ██║   ██║██║     ██║  ██║██║██║╚██╗██║██║   ██║</>',
            ' <fg=red> ███████║╚██████╗██║  ██║██║     ██║     ╚██████╔╝███████╗██████╔╝██║██║ ╚████║╚██████╔╝</>',
            ' <fg=red> ╚══════╝ ╚═════╝╚═╝  ╚═╝╚═╝     ╚═╝      ╚═════╝ ╚══════╝╚═════╝ ╚═╝╚═╝  ╚═══╝ ╚═════╝ </>',
            ' <fg=red>                                                                                    </>',
            ' <fg=red>              ███████╗ █████╗  ██████╗████████╗ ██████╗ ██████╗ ██╗   ██╗           </>',
            ' <fg=red>              ██╔════╝██╔══██╗██╔════╝╚══██╔══╝██╔═══██╗██╔══██╗╚██╗ ██╔╝           </>',
            ' <fg=red>              █████╗  ███████║██║        ██║   ██║   ██║██████╔╝ ╚████╔╝            </>',
            ' <fg=white>              ██╔══╝  ██╔══██║██║        ██║   ██║   ██║██╔══██╗  ╚██╔╝             </>',
            ' <fg=white>              ██║     ██║  ██║╚██████╗   ██║   ╚██████╔╝██║  ██║   ██║              </>',
            ' <fg=white>              ╚═╝     ╚═╝  ╚═╝ ╚═════╝   ╚═╝    ╚═════╝ ╚═╝  ╚═╝   ╚═╝              </>',
            '',
        ]);
    }
}
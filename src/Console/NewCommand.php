<?php

namespace Roldante05\ScaffoldingFactory\Console;

use Roldante05\ScaffoldingFactory\Builders\LaravelBuilder;
use Roldante05\ScaffoldingFactory\Builders\PhpVanillaBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use function Laravel\Prompts\note;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class NewCommand extends Command
{
    protected static $defaultName = 'new';

    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new project (Laravel or PHP Vanilla)')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the project');
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
        }

        $projectType = select(
            label: 'What type of project would you like to create?',
            options: ['Laravel', 'PHP Vanilla'],
            default: 'Laravel'
        );

        $builder = $projectType === 'Laravel'
            ? new LaravelBuilder()
            : new PhpVanillaBuilder();

        $options = $builder->askOptions($input, $output, null);

        if (isset($options['cancelled']) && $options['cancelled'] === true) {
            error('❌ Operation cancelled.');
            return Command::FAILURE;
        }

        // Summary
        $summaryLines = [
            "Project Name: $projectName",
            "Type: $projectType",
        ];
        foreach ($options as $key => $value) {
            if ($key === 'cancelled')
                continue;
            $displayValue = is_bool($value) ? ($value ? 'Yes' : 'No') : $value;
            $summaryLines[] = ucfirst($key) . ": $displayValue";
        }

        note(implode("\n", $summaryLines), 'Configuration Summary');

        if (!confirm('Does everything look correct?', true)) {
            error('❌ Operation cancelled.');
            return Command::FAILURE;
        }

        return $builder->build($projectName, $options, $output);
    }

    /**
     * Render the Scaffolding Factory logo.
     */
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
<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\Console;

use Roldante05\ScaffoldingFactory\Builders\LaravelBuilder;
use Roldante05\ScaffoldingFactory\Builders\PhpVanillaBuilder;
use Roldante05\ScaffoldingFactory\Interactions\LaravelInteractionHandler;
use Roldante05\ScaffoldingFactory\Interactions\PhpVanillaInteractionHandler;
use Roldante05\ScaffoldingFactory\DTOs\PhpVanillaOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

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

        if ($options instanceof PhpVanillaOptions) {
            $output->writeln('');
            $output->writeln("<info>Configuration:</info>");
            $output->writeln("   • Database: {$options->database}");
            $output->writeln("   • Login Kit: " . ($options->login ? 'Yes' : 'No'));
            $output->writeln("   • CSS: {$options->css}");
        }

        if ($projectType === 'PHP Vanilla') {
            $this->renderChePhpLogo($output);
        }

        $output->writeln(['', '', ' <fg=blue>🛠️  Starting installation process...</>', '']);

        return $builder->build($projectName, $options, $output);
    }

    protected function renderChePhpLogo(OutputInterface $output): void
    {
        $output->writeln([
            '',
            ' <fg=green>    ____ _          ____  _    _ ____    </>',
            ' <fg=green>   / ___| |__   ___|  _ \| |  | |  _ \  </>',
            ' <fg=green>  | |   | \'_ \ / _ \ |_) | |__| | |_)  </>',
            ' <fg=green>  | |___| | | |  __/  __/|  __  |  __/  </>',
            ' <fg=green>   \____|_| |_|\___|_|   |_|  |_|_|     </>',
            ' <fg=green>        ORM · Migrations · CLI           </>',
            '',
        ]);
    }

    protected function renderLogo(OutputInterface $output): void
    {
        $output->writeln([
            '',
            ' <fg=blue>    _____            ________      __    ___             </>',
            ' <fg=blue>   / ___/_________ _/ __/ __/___  / /___/ (_)___  ____ _ </>',
            ' <fg=cyan>   \__ \/ ___/ __ `/ /_/ /_/ __ \/ / __  / / __ \/ __ `/ </>',
            ' <fg=cyan>  ___/ / /__/ /_/ / __/ __/ /_/ / / /_/ / / / / / /_/ /  </>',
            ' <fg=blue> /____/\___/\__,_/_/ /_/  \____/_/\__,_/_/_/ /_/\__, /   </>',
            ' <fg=blue>                                                /____/    </>',
            '',
        ]);
    }
}
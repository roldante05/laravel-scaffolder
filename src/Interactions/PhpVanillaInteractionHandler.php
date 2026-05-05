<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\Interactions;

use Roldante05\ScaffoldingFactory\DTOs\PhpVanillaOptions;
use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;

class PhpVanillaInteractionHandler implements InteractionHandlerInterface
{
    public function handle(InputInterface $input, OutputInterface $output): ProjectOptions
    {
        $projectName = $input->getArgument('name');

        info('💾 Database Configuration');
        $database = select(
            label: 'Which database would you like to use?',
            options: [
                'mysql' => 'MySQL',
                'sqlite' => 'SQLite (Zero-config)',
                'none' => 'None (No database)'
            ],
            default: 'mysql'
        );

        $login = false;
        if ($database !== 'none') {
            info('🔐 Authentication System');
            $login = confirm(
                label: 'Would you like to include a Login Kit?',
                default: false
            );
        }

        info('🎨 CSS Framework Selection');
        $css = select(
            label: 'Which CSS framework would you like to use?',
            options: [
                'Tailwind CSS' => 'Tailwind CSS',
                'Bootstrap' => 'Bootstrap'
            ],
            default: 'Tailwind CSS'
        );

        return new PhpVanillaOptions(
            projectName: $projectName,
            database: $database,
            login: $login,
            css: $css
        );
    }
}

<?php

declare(strict_types=1);

namespace Roldante05\ScaffoldingFactory\Interactions;

use Roldante05\ScaffoldingFactory\DTOs\LaravelOptions;
use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;

class LaravelInteractionHandler implements InteractionHandlerInterface
{
    public function handle(InputInterface $input, OutputInterface $output): ProjectOptions
    {
        $projectName = $input->getArgument('name');
        $isTty = stream_isatty(STDIN);

        if (!$isTty) {
            return new LaravelOptions(
                projectName: $projectName,
                wantKit: true,
                kit: 'Breeze',
                stack: 'blade',
                withTeams: false,
                database: 'sqlite',
                withBoost: PHP_OS_FAMILY !== 'Windows'
            );
        }

        info('🔐 Authentication & Ecosystem');

        $wantKit = confirm(
            label: 'Do you want to install a starter kit?',
            default: true,
            hint: 'Starter kits provide a pre-built authentication system (Breeze or Jetstream).'
        );

        $withBoost = false;
        if (PHP_OS_FAMILY !== 'Windows') {
            $withBoost = confirm(
                label: 'Install Laravel Boost for AI assisted coding?',
                default: true,
                hint: 'Provides documentation API and MCP servers for AI agents.'
            );
        }

        $kit = 'None';
        $stack = 'none';
        $withTeams = false;

        if ($wantKit) {
            $kit = select(
                label: 'Which starter kit would you like to use?',
                options: [
                    'Breeze' => 'Breeze (Minimal & Elegant)',
                    'Jetstream' => 'Jetstream (Advanced Features)',
                    'Official Starter Kit (2026)' => 'Official Starter Kit (2026)'
                ],
                default: 'Breeze'
            );

            if ($kit === 'Breeze') {
                $breezeStack = select(
                    label: 'Which Breeze stack would you like to use?',
                    options: [
                        'Blade' => 'Blade (Classic)',
                        'Livewire' => 'Livewire (Full-stack PHP)',
                        'React (Inertia)' => 'React (Modern SPA)',
                        'Vue (Inertia)' => 'Vue (Modern SPA)'
                    ],
                    default: 'Blade'
                );

                $stack = match ($breezeStack) {
                    'Blade' => 'blade',
                    'Livewire' => 'livewire',
                    'React (Inertia)' => 'react',
                    'Vue (Inertia)' => 'vue',
                };

                $withTeams = confirm(
                    label: 'Would you like to include team support?',
                    default: false
                );
            } elseif ($kit === 'Jetstream') {
                $jetstreamStack = select(
                    label: 'Which Jetstream stack would you like to use?',
                    options: [
                        'Livewire' => 'Livewire',
                        'Inertia (Vue)' => 'Inertia (Vue)'
                    ],
                    default: 'Livewire'
                );
                $stack = $jetstreamStack === 'Livewire' ? 'livewire' : 'inertia';
                $withTeams = false;
            } elseif ($kit === 'Official Starter Kit (2026)') {
                $officialKit = select(
                    label: 'Which official starter kit would you like to use?',
                    options: [
                        'Livewire (Flux UI)' => 'Livewire (Flux UI)',
                        'React (shadcn)' => 'React (shadcn)',
                        'Vue (shadcn-vue)' => 'Vue (shadcn-vue)'
                    ],
                    default: 'Livewire (Flux UI)'
                );

                $stack = match (true) {
                    str_contains($officialKit, 'Livewire') => 'livewire',
                    str_contains($officialKit, 'React') => 'react',
                    default => 'vue'
                };
                $withTeams = false;
            }
        }

        info('💾 Database Configuration');
        $database = select(
            label: 'Which database would you like to use?',
            options: [
                'sqlite' => 'SQLite (Zero-config)',
                'mysql' => 'MySQL',
                'mariadb' => 'MariaDB',
                'pgsql' => 'PostgreSQL'
            ],
            default: 'sqlite'
        );

        return new LaravelOptions(
            projectName: $projectName,
            wantKit: $wantKit,
            kit: $kit,
            stack: $stack,
            withTeams: $withTeams,
            database: $database,
            withBoost: $withBoost
        );
    }
}

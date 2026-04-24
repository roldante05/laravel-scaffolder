<?php

namespace Roldante05\ScaffoldingFactory\Console;

use Roldante05\ScaffoldingFactory\Builders\LaravelBuilder;
use Roldante05\ScaffoldingFactory\Builders\PhpVanillaBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

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
        $projectName = $input->getArgument('name');
        $helper = $this->getHelper('question');

        $output->writeln([
            '🚀 <info>Welcome to Scaffolding Factory!</info>',
            '=====================================',
        ]);

        // 1. Elegir tipo de proyecto
        $question = new ChoiceQuestion(
            'What type of project would you like to create?',
            ['Laravel', 'PHP Vanilla'],
            0
        );
        $projectType = $helper->ask($input, $output, $question);

        // 2. Obtener Builder
        $builder = $projectType === 'Laravel' 
            ? new LaravelBuilder() 
            : new PhpVanillaBuilder();

        // 3. Preguntar opciones
        $options = $builder->askOptions($input, $output, $helper);

        // 4. Resumen
        $output->writeln("\n<comment>Configuration Summary:</comment>");
        $output->writeln("• Project Name: <info>$projectName</info>");
        $output->writeln("• Type: <info>$projectType</info>");
        foreach ($options as $key => $value) {
            $output->writeln("• " . ucfirst($key) . ": <info>" . (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) . "</info>");
        }

        $question = new ChoiceQuestion("\nDoes everything look correct?", ['Yes', 'No'], 0);
        if ($helper->ask($input, $output, $question) === 'No') {
            $output->writeln('<error>❌ Operation cancelled.</error>');
            return Command::FAILURE;
        }

        // 5. Ejecutar
        return $builder->build($projectName, $options, $output);
    }
}

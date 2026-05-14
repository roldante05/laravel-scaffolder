<?php

use Roldante05\ScaffoldingFactory\Builders\LaravelBuilder;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;

test('laravel builder can be instantiated', function () {
    $builder = new LaravelBuilder();
    expect($builder)->toBeInstanceOf(LaravelBuilder::class);
});
<?php

use Roldante05\ScaffoldingFactory\Helpers\StubProcessor;

test('it replaces variables', function () {
    $content = "Hello {{NAME}}!";
    $variables = ['NAME' => 'World'];
    $tags = [];
    
    $result = StubProcessor::process($content, $variables, $tags);
    
    expect($result)->toBe("Hello World!");
});

test('it processes conditional blocks', function () {
    $content = "{{USE_SAIL}}Sail is enabled{{/USE_SAIL}}{{!USE_SAIL}}Sail is disabled{{/!USE_SAIL}}";
    
    $resultTrue = StubProcessor::process($content, [], ['USE_SAIL' => true]);
    expect($resultTrue)->toContain("Sail is enabled");
    expect($resultTrue)->not->toContain("Sail is disabled");
    
    $resultFalse = StubProcessor::process($content, [], ['USE_SAIL' => false]);
    expect($resultFalse)->toContain("Sail is disabled");
    expect($resultFalse)->not->toContain("Sail is enabled");
});

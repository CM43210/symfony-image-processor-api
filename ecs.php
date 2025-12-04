<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;

return ECSConfig::configure()
    ->withPaths(paths: [__DIR__.'/src'])
    ->withEditorConfig()
    ->withSets([
        SetList::PSR_12,
    ])
    ->withRules([
        NoUnusedImportsFixer::class
    ])
    ->withConfiguredRule(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);

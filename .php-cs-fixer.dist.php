<?php

declare(strict_types=1);

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new PhpCsFixer\Finder())
    ->files()
    ->name('*.php')
    ->in(array_filter([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ], static function (string $path): bool {
        return file_exists($path) && is_dir($path) && is_readable($path);
    }))
    ->append([
        __FILE__,
    ])
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([

        '@PER-CS' => true,
        '@PER-CS:risky' => true,

        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'backtick_to_shell_exec' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => false,
        'cast_spaces' => ['space' => 'single'],
        'class_attributes_separation' => ['elements' => [
            'const' => 'none',
            'method' => 'one',
            'property' => 'none',
            'trait_import' => 'none',
            'case' => 'none',
        ]],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'function_declaration' => [
            'closure_function_spacing' => 'one',
            'closure_fn_spacing' => 'one',
        ],
        'heredoc_indentation' => ['indentation' => 'same_as_start'],
        'mb_str_functions' => false,
        'method_argument_space' => [
            'on_multiline' => 'ignore',
        ],
        'modernize_strpos' => true,
        'native_function_invocation' => ['include' => ['@all'], 'scope' => 'namespaced', 'strict' => true],
        'no_blank_lines_after_class_opening' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        // TODO: do not allow mixed on next major version (requires MSPV 8.0).
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true, 'allow_mixed' => true],
        'no_unused_imports' => true,
        'no_whitespace_in_blank_line' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const']],
        'phpdoc_line_span' => ['const' => 'single', 'method' => 'single', 'property' => 'single'],
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'protected_to_private' => true,
        'psr_autoloading' => true,
        'semicolon_after_instruction' => true,
        'single_quote' => true,
        'static_lambda' => true,
        'strict_param' => true,
        'ternary_operator_spaces' => true,
        // TODO: trailing commas in `arguments` too, on next major version (requires MSPV 7.3).
        // TODO: trailing commas in `parameters` too, on next major version (requires MSPV 8.0).
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'trim_array_spaces' => true,
        'void_return' => true,
        'yoda_style' => true,

    ]);

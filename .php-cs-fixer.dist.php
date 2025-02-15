<?php

// PHP-CS-Fixer v2.2

$rules = array(
//    '@PSR1'           => false, // [@PSR1]
//    '@PSR2'           => false, // [@PSR2]
//    '@Symfony'        => false, // [@Symfony]
//    '@Symfony:risky'  => false, // [@Symfony:risky]
//    '@PHP56Migration' => false, // [@PHP56Migration]
//    '@PHP70Migration' => false, // [@PHP70Migration]
//    '@PHP70Migration:risky' => false, // [@PHP70Migration:risky]
//    '@PHP71Migration' => false, // [@PHP71Migration]
//    '@PHP71Migration:risky' => false, // [@PHP71Migration:risky]
//    '@PHP73Migration' => false, // [@PHP73Migration]
//    '@PHP74Migration' => false, // [@PHP74Migration]
//    '@PHP74Migration:risky' => false, // [@PHP74Migration:risky]
//    '@PHP80Migration' => false, // [@PHP80Migration]
//    '@PHP80Migration:risky' => false, // [@PHP80Migration:risky]

    'array_syntax'                                => array('syntax' => 'long'), // [@] TODO: PHP >= 5.4 => array('syntax' => 'short')
//    'binary_operator_spaces'                    => false, // [@Symfony]
    'blank_line_after_namespace'                  => true, // [@PSR2, @Symfony]
    'blank_line_after_opening_tag'                => true,  // [@Symfony]
//    'blank_line_before_return'                  => false, // [@Symfony]
    'braces'                                      => array('position_after_control_structures' => 'same'), // [@PSR2, @Symfony]
    'cast_spaces'                                 => true,  // [@Symfony]
    'class_definition'                            => array('single_line' => true, 'single_item_single_line' => true, 'multi_line_extends_each_single_line' => true, 'space_before_parenthesis' => true), // [@PSR2, @Symfony]
    'class_keyword_remove'                        => false, // [@]
    'combine_consecutive_unsets'                  => true,  // [@]
//    'concat_space'                              => false, // [@Symfony]
    'declare_equal_normalize'                     => true,  // [@Symfony]
//    'declare_strict_types'                      => false, // [@PHP70Migration] TODO: PHP >= 7.0 => true
    'dir_constant'                                => true,  // [@]
//    'doctrine_annotation_braces'                => false, // [@]
//    'doctrine_annotation_indentation'           => false, // [@]
//    'doctrine_annotation_spaces'                => false, // [@]
    'elseif'                                      => true,  // [@PSR2, @Symfony]
    'encoding'                                    => true,  // [@PSR1, @PSR2, @Symfony]
    'ereg_to_preg'                                => true,  // [@]
    'full_opening_tag'                            => true,  // [@PSR1, @PSR2, @Symfony]
    'function_declaration'                        => true,  // [@PSR2, @Symfony]
    'function_to_constant'                        => true,  // [@Symfony]
    'function_typehint_space'                     => true,  // [@Symfony]
//    'general_phpdoc_annotation_remove'          => false, // [@]
    'single_line_comment_style'                   => true,  // [@Symfony]
//    'header_comment'                            => false, // [@]
    'heredoc_to_nowdoc'                           => true,  // [@Symfony]
//    'include'                                   => false, // [@Symfony]
    'indentation_type'                            => true,  // [@PSR2, @Symfony]
//    'is_null'                                   => false, // [@Symfony]
    'line_ending'                                 => true,  // [@PSR2, @Symfony]
    'linebreak_after_opening_tag'                 => true,  // [@]
    'lowercase_cast'                              => true,  // [@Symfony]
    'constant_case'                               => true,  // [@PSR2, @Symfony]
    'lowercase_keywords'                          => true,  // [@PSR2, @Symfony]
//    'mb_str_functions'                          => false, // [@]
    'method_argument_space'                       => array('on_multiline' => 'ensure_fully_multiline'), // [@PSR2, @Symfony]
//    'method_separation'                         => false, // [@Symfony]
    'modernize_types_casting'                     => true,  // [@]
    'native_function_casing'                      => true,  // [@Symfony]
//    'native_function_invocation'                => false, // [@]
    'new_with_braces'                             => true,  // [@Symfony]
    'no_alias_functions'                          => true,  // [@Symfony]
    'no_blank_lines_after_class_opening'          => true,  // [@Symfony]
    'no_blank_lines_after_phpdoc'                 => true,  // [@Symfony]
//    'no_blank_lines_before_namespace'           => false, // [@]
    'no_break_comment'                            => array('comment_text' => 'no break'), // [@PSR2]
    'no_closing_tag'                              => true,  // [@PSR2, @Symfony]
    'no_empty_comment'                            => true,  // [@Symfony]
    'no_empty_phpdoc'                             => true,  // [@Symfony]
    'no_empty_statement'                          => true,  // [@Symfony]
//    'no_extra_consecutive_blank_lines'          => false, // [@Symfony]
//    'no_leading_import_slash'                   => false, // [@Symfony]
//    'no_leading_namespace_whitespace'           => false, // [@Symfony]
    'no_mixed_echo_print'                         => array('use' => 'echo'), // [@Symfony]
    'no_multiline_whitespace_around_double_arrow' => true,  // [@Symfony]
    'multiline_whitespace_before_semicolons'      => false,  // [@]
    'no_php4_constructor'                         => true,  // [@]
    'no_short_bool_cast'                          => true,  // [@Symfony]
//    'no_short_echo_tag'                         => false, // [@]
    'no_singleline_whitespace_before_semicolons'  => true,  // [@Symfony]
    'no_space_around_double_colon'                => true,  // [@PSR2]
    'no_spaces_after_function_name'               => true,  // [@PSR2, @Symfony]
    'no_spaces_around_offset'                     => true,  // [@Symfony]
    'no_spaces_inside_parenthesis'                => true,  // [@PSR2, @Symfony]
    'no_trailing_comma_in_list_call'              => true,  // [@Symfony]
    'no_trailing_comma_in_singleline_array'       => true,  // [@Symfony]
    'no_trailing_whitespace'                      => true,  // [@PSR2, @Symfony]
    'no_trailing_whitespace_in_comment'           => true,  // [@PSR2, @Symfony]
    'no_unneeded_control_parentheses'             => true,  // [@Symfony]
//    'no_unreachable_default_argument_value'     => false, // [@Symfony]
//    'no_unused_imports'                         => false, // [@Symfony]
//    'no_useless_else'                           => false, // [@]
    'no_useless_return'                           => true,  // [@]
    'no_whitespace_before_comma_in_array'         => true,  // [@Symfony]
    'no_whitespace_in_blank_line'                 => true,  // [@Symfony]
    'non_printable_character'                     => true,  // [@Symfony]
    'normalize_index_brace'                       => true,  // [@Symfony]
//    'not_operator_with_space'                   => false, // [@]
//    'not_operator_with_successor_space'         => false, // [@]
    'object_operator_without_whitespace'          => true,  // [@Symfony]
//    'ordered_class_elements'                    => false, // [@]
//    'ordered_imports'                           => false, // [@]
//    'php_unit_construct'                        => false, // [@Symfony:risky]
//    'php_unit_dedicate_assert'                  => false, // [@Symfony:risky]
//    'php_unit_fqcn_annotation'                  => false, // [@Symfony]
//    'php_unit_strict'                           => false, // [@]
//    'phpdoc_add_missing_param_annotation'       => false, // [@]
//    'phpdoc_align'                              => false, // [@Symfony]
//    'phpdoc_annotation_without_dot'             => false, // [@Symfony]
    'phpdoc_indent'                               => true,  // [@Symfony]
    'general_phpdoc_tag_rename'                   => true,  // [@Symfony]
    'phpdoc_no_access'                            => true,  // [@Symfony]
    'phpdoc_no_alias_tag'                         => true,  // [@Symfony]
//    'phpdoc_no_empty_return'                    => false, // [@Symfony]
    'phpdoc_no_package'                           => true,  // [@Symfony]
    'phpdoc_no_useless_inheritdoc'                => true,  // [@Symfony]
    'phpdoc_order'                                => true,  // [@]
    'phpdoc_return_self_reference'                => true,  // [@Symfony]
    'phpdoc_scalar'                               => true,  // [@Symfony]
//    'phpdoc_separation'                         => false, // [@Symfony]
    'phpdoc_single_line_var_spacing'              => true,  // [@Symfony]
//    'phpdoc_summary'                            => false, // [@Symfony]
    'phpdoc_to_comment'                           => true,  // [@Symfony]
    'phpdoc_trim'                                 => true,  // [@Symfony]
    'phpdoc_types'                                => true,  // [@Symfony]
//    'phpdoc_var_without_name'                   => false, // [@Symfony]
    'pow_to_exponentiation'                       => true, // [@PHP56Migration, @PHP70Migration, @PHP71Migration]
//    'pre_increment'                             => false, // [@Symfony]
    'protected_to_private'                        => true,  // [@]
//    'psr0'                                      => false, // [@]
//    'psr4'                                      => false, // [@]
    'random_api_migration'                        => true,  // [@PHP70Migration, @PHP71Migration]
//    'return_type_declaration'                   => false, // [@Symfony]
    'self_accessor'                               => true,  // [@Symfony]
    'semicolon_after_instruction'                 => true,  // [@]
    'short_scalar_cast'                           => true,  // [@Symfony]
//    'silenced_deprecation_error'                => false, // [@Symfony:risky]
//    'simplified_null_return'                    => false, // [@]
    'single_blank_line_at_eof'                    => true,  // [@PSR2, @Symfony]
//    'single_blank_line_before_namespace'        => false, // [@Symfony]
    'single_class_element_per_statement'          => true,  // [@PSR2, @Symfony]
    'single_import_per_statement'                 => true, // [@PSR2, @Symfony]
    'single_line_after_imports'                   => true, // [@PSR2, @Symfony]
    'single_quote'                                => true,  // [@Symfony]
    'space_after_semicolon'                       => true,  // [@Symfony]
    'standardize_not_equals'                      => true,  // [@Symfony]
//    'strict_comparison'                         => false, // [@]
//    'strict_param'                              => false, // [@]
    'switch_case_semicolon_to_colon'              => true,  // [@PSR2, @Symfony]
    'switch_case_space'                           => true,  // [@PSR2, @Symfony]
    'ternary_operator_spaces'                     => true,  // [@Symfony]
//    'ternary_to_null_coalescing'                => false, // [@PHP70Migration, @PHP71Migration]
//    'trailing_comma_in_multiline_array'         => false, // [@Symfony]
    'trim_array_spaces'                           => true,  // [@Symfony]
//    'unary_operator_spaces'                     => false, // [@Symfony]
    'visibility_required'                         => true,  // [@PSR2, @Symfony, @PHP71Migration]
    'whitespace_after_comma_in_array'             => true   // [@Symfony]
);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('libs')
    ->exclude('node_modules')
;

$config = new PhpCsFixer\Config();
return $config->setRules($rules)
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ;

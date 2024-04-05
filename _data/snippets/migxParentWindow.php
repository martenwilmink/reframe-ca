id: 100064
name: migxParentWindow
description: 'Return the win_id value, which is the last bit of the parent window ID. Make sure you don''t use hyphenated IDs!'
category: E6_data
snippet: "/**\n * migxParentWindow\n *\n * Return the win_id value, which is the last bit of the parent window ID.\n * Make sure you don't use hyphenated IDs!\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$input = $modx->getOption('parent_window', $_REQUEST, 'undefined');\n$output = explode('-', $input);\nreturn array_pop($output);"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/migxparentwindow.snippet.php'

-----


/**
 * migxParentWindow
 *
 * Return the win_id value, which is the last bit of the parent window ID.
 * Make sure you don't use hyphenated IDs!
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$input = $modx->getOption('parent_window', $_REQUEST, 'undefined');
$output = explode('-', $input);
return array_pop($output);
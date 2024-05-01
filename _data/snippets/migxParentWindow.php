id: 100064
name: migxParentWindow
description: 'Return the win_id value, which is the last bit of the parent window ID. Make sure you don''t use hyphenated IDs!'
category: E6_data
properties: 'a:0:{}'

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
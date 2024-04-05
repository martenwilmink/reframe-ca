id: 100073
name: migxLoadExchange
description: 'This adds the class_key to the request, so it can be accessed with POST.'
category: E6_dat_load
snippet: "/**\n * migxLoadExchange\n *\n * When parsing options inside a selector with @CHUNK, it seems impossible to\n * read existing values from other fields. This hook fetches the existing class\n * key and adds it to the request, making it available for templating.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n// Forward class key, for use in TV templating\n$classKey = $scriptProperties['record']['class_key'] ?? '';\nif ($classKey) {\n    $_POST['object_class_key'] = $classKey;\n}\n\nreturn '';"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/e6_dat_load/migxloadexchange.snippet.php'

-----


/**
 * migxLoadExchange
 *
 * When parsing options inside a selector with @CHUNK, it seems impossible to
 * read existing values from other fields. This hook fetches the existing class
 * key and adds it to the request, making it available for templating.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

// Forward class key, for use in TV templating
$classKey = $scriptProperties['record']['class_key'] ?? '';
if ($classKey) {
    $_POST['object_class_key'] = $classKey;
}

return '';
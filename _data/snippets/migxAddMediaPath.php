id: 100069
name: migxAddMediaPath
category: E6_plumbing
snippet: "/**\n * migxAddMediaPath\n *\n * @var modX $modx\n * @var array $scriptProperties\n * @var string $input\n * @var string $options\n */\n\n$output = str_replace('./','',$input);\nif ($mediaSource = $modx->getObject('sources.modMediaSource',$options)){\n    $output = $mediaSource->prepareOutputUrl($output);\n}\nreturn '/' . $output;"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_plumbing/migxaddmediapath.snippet.php'

-----


/**
 * migxAddMediaPath
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

$output = str_replace('./','',$input);
if ($mediaSource = $modx->getObject('sources.modMediaSource',$options)){
    $output = $mediaSource->prepareOutputUrl($output);
}
return '/' . $output;
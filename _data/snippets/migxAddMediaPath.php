id: 100069
name: migxAddMediaPath
category: E6_plumbing
properties: 'a:0:{}'

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
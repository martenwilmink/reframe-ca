id: 100070
name: earthRenderImage
category: E6_presentation
snippet: "/**\n * earthRenderImage\n *\n * For use as Collections renderer.\n *\n * @todo: Turn into generic renderer.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n//$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n//$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));\n//$corePath = $modx->getOption('forestbrain.core_path', null, $modx->getOption('core_path') . 'components/forestbrain/');\n//$forestbrain = $modx->getService('forestbrain','ForestBrain',$corePath . 'model/forestbrain/', array('core_path' => $corePath));\n\n$input = $modx->getOption('input', $scriptProperties, '');\n$resource = $modx->getObject('modResource', array('uri' => $input));\n$resourceID = $resource->get('id');\n\n$image = $modx->runSnippet('migxLoopCollection', [\n    'packageName'=>'earthbrain',\n    'classname'=>'earthImage',\n    'where'=>'[{\"class_key\":\"forestImage\"},{\"parent_id\":\"' . $resourceID . '\"},{\"deleted:=\":0}]',\n    'tpl'=>'@CODE:[[+img]]',\n    'limit'=>'1',\n    'sortConfig'=>'[{\"sortby\":\"pos\",\"sortdir\":\"ASC\"}]',\n]);\n\nif ($image) {\n    $image = json_decode($image, true);\n\n    // Fix media source path\n    $image['sourceImg']['src'] = \"uploads/img/forest/$resourceID/\" . $image['sourceImg']['src'];\n    $image['sourceImg']['source'] = 1;\n\n    // Create thumbnail\n    return $modx->runSnippet('ImagePlus', array(\n        'value' => json_encode($image),\n        'options' => 'w=600&q=85&zc=1',\n    ));\n}\n\nreturn '';"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_presentation/earthrenderimage.snippet.php'

-----


/**
 * earthRenderImage
 *
 * For use as Collections renderer.
 *
 * @todo: Turn into generic renderer.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

//$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
//$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));
//$corePath = $modx->getOption('forestbrain.core_path', null, $modx->getOption('core_path') . 'components/forestbrain/');
//$forestbrain = $modx->getService('forestbrain','ForestBrain',$corePath . 'model/forestbrain/', array('core_path' => $corePath));

$input = $modx->getOption('input', $scriptProperties, '');
$resource = $modx->getObject('modResource', array('uri' => $input));
$resourceID = $resource->get('id');

$image = $modx->runSnippet('migxLoopCollection', [
    'packageName'=>'earthbrain',
    'classname'=>'earthImage',
    'where'=>'[{"class_key":"forestImage"},{"parent_id":"' . $resourceID . '"},{"deleted:=":0}]',
    'tpl'=>'@CODE:[[+img]]',
    'limit'=>'1',
    'sortConfig'=>'[{"sortby":"pos","sortdir":"ASC"}]',
]);

if ($image) {
    $image = json_decode($image, true);

    // Fix media source path
    $image['sourceImg']['src'] = "uploads/img/forest/$resourceID/" . $image['sourceImg']['src'];
    $image['sourceImg']['source'] = 1;

    // Create thumbnail
    return $modx->runSnippet('ImagePlus', array(
        'value' => json_encode($image),
        'options' => 'w=600&q=85&zc=1',
    ));
}

return '';
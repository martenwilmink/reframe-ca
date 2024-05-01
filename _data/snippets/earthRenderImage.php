id: 100070
name: earthRenderImage
category: E6_presentation
properties: 'a:0:{}'

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
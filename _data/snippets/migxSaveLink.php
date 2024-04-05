id: 100080
name: migxSaveLink
description: 'After save hook for links. Connects links to the correct parent object, and increments sort order of new items.'
category: E6_dat_save
snippet: "/**\n * migxSaveLink\n *\n * Hook snippet for links. Fire on aftersave event.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\n\n$object = $modx->getOption('object', $scriptProperties);\n$properties = $modx->getOption('scriptProperties', $scriptProperties, []);\n$configs = $modx->getOption('configs', $properties, '');\n$postValues = $modx->getOption('postvalues', $scriptProperties, []);\n$co_id = $modx->getOption('co_id', $properties);\n$result = [];\n\nif (!is_object($object)) return;\n\n$object->set('parent_id', $co_id);\n\n// If co_id is 0, then parent might be a resource\nif (!$co_id && $properties['resource_id']) {\n    $object->set('parent_id', $properties['resource_id']);\n}\n\n$earthbrain->incrementPosition($object, $properties);\n\n$object->save();\n\nreturn json_encode($result);"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/e6_dat_save/migxsavelink.snippet.php'

-----


/**
 * migxSaveLink
 *
 * Hook snippet for links. Fire on aftersave event.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);
$configs = $modx->getOption('configs', $properties, '');
$postValues = $modx->getOption('postvalues', $scriptProperties, []);
$co_id = $modx->getOption('co_id', $properties);
$result = [];

if (!is_object($object)) return;

$object->set('parent_id', $co_id);

// If co_id is 0, then parent might be a resource
if (!$co_id && $properties['resource_id']) {
    $object->set('parent_id', $properties['resource_id']);
}

$earthbrain->incrementPosition($object, $properties);

$object->save();

return json_encode($result);
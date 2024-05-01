id: 100061
name: migxPrepare
description: 'Some objects are extending a class, whereby data is stored in the parent table. This means that each item needs to receive a class_key in order to be recognized by MODX.'
category: E6_data
snippet: "/**\n * migxPrepare\n *\n * Hook snippet for images, notes and links. These classes are extended for\n * every table that uses them. This means that each object needs to receive a\n * class_key in order to be recognized in MODX. Failure to do so results in an\n * orphaned object!\n *\n * Needs to be attached to aftergetfields event.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\n\n$object = $modx->getOption('object', $scriptProperties);\n$properties = $modx->getOption('scriptProperties', $scriptProperties, []);\n$configs = $modx->getOption('configs', $properties);\n\n$co_id = $modx->getOption('co_id', $properties, 0);\n\n// Get parent config from stored request parameters\n$storeParams = $modx->getOption('storeParams', $_REQUEST);\n$storeParams = json_decode($storeParams, true);\n\n// Get current grid config type (because win_id is wishy-washy)\n$configType = '';\nswitch ($configs) {\n    case 'earthbrain_images:earthbrain':\n        $configType = 'image';\n        break;\n    case 'earthbrain_notes:earthbrain':\n        $configType = 'note';\n        break;\n    case 'earthbrain_links:earthbrain':\n        $configType = 'link';\n        break;\n}\n\n// Get array of class keys for this config\n$classKeys = $modx->runSnippet('migxGetClassKeys', ['config' => $storeParams['reqConfigs']]) ?? [];\n\n// Values here won't persist with object->set, but the record fields will\n$record = $object->get('record_fields');\n\nif (is_object($object)) {\n    $record['class_key'] = $classKeys[$configType] ?? '';\n    $object->set('record_fields', $record);\n}\n\n//$earthbrain->resetNull($object, $properties);\n//$earthbrain->saveLocation($object, $properties, $locationID);\n\nreturn '';"
properties: 'a:0:{}'

-----


/**
 * migxPrepare
 *
 * Hook snippet for images, notes and links. These classes are extended for
 * every table that uses them. This means that each object needs to receive a
 * class_key in order to be recognized in MODX. Failure to do so results in an
 * orphaned object!
 *
 * Needs to be attached to aftergetfields event.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);
$configs = $modx->getOption('configs', $properties);

$co_id = $modx->getOption('co_id', $properties, 0);

// Get parent config from stored request parameters
$storeParams = $modx->getOption('storeParams', $_REQUEST);
$storeParams = json_decode($storeParams, true);

// Get current grid config type (because win_id is wishy-washy)
$configType = '';
switch ($configs) {
    case 'earthbrain_images:earthbrain':
        $configType = 'image';
        break;
    case 'earthbrain_notes:earthbrain':
        $configType = 'note';
        break;
    case 'earthbrain_links:earthbrain':
        $configType = 'link';
        break;
}

// Get array of class keys for this config
$classKeys = $modx->runSnippet('migxGetClassKeys', ['config' => $storeParams['reqConfigs']]) ?? [];

// Values here won't persist with object->set, but the record fields will
$record = $object->get('record_fields');

if (is_object($object)) {
    $record['class_key'] = $classKeys[$configType] ?? '';
    $object->set('record_fields', $record);
}

//$earthbrain->resetNull($object, $properties);
//$earthbrain->saveLocation($object, $properties, $locationID);

return '';
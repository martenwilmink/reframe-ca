id: 100061
name: migxPrepare
description: 'Some objects are extending a class, whereby data is stored in the parent table. This means that each item needs to receive a class_key in order to be recognized by MODX.'
category: E6_data
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
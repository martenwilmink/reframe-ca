id: 100074
name: migxSavePlanting
category: E6_dat_save
snippet: "/**\n * migxSavePlant\n *\n * Aftersave snippet for plants.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\n\n$object = $modx->getOption('object', $scriptProperties);\n$properties = $modx->getOption('scriptProperties', $scriptProperties, []);\n$configs = $modx->getOption('configs', $properties, '');\n$postValues = $modx->getOption('postvalues', $scriptProperties, []);\n$co_id = $modx->getOption('co_id', $properties);\n\n//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties,1));\n\nif (!is_object($object)) return;\n\n// Set key and ID of parent object\nif ($co_id) {\n    $object->set('plant_id', $co_id);\n    $object->save();\n}\n\n// Get location\n$locationID = $object->get('location_id');\n\n// Get currently selected features\n$currentFeatures = [];\n$joinedFeatures = $object->getMany('Features');\nforeach ($joinedFeatures as $feature) {\n    $currentFeatures[] = $feature->get('option_id');\n}\n\n// Get updated features\n$savedFeatures = (array)$properties['features'] ?? [];\n\n// Differentiate which features to add, and which to remove\n$addFeatures = array_diff_assoc($savedFeatures, $currentFeatures);\n$delFeatures = array_diff_assoc($currentFeatures, $savedFeatures);\n\n// Add features, but watch out for empty features\nif ($addFeatures) {\n    $features = [];\n    foreach ($addFeatures as $feature) {\n        if (!$feature) continue;\n        $features[] = $modx->newObject('earthPlantingFeature', [\n            'planting_id' => $object->get('id'),\n            'option_id' => $feature,\n        ]);\n    }\n    $object->addMany($features);\n}\n\nif ($delFeatures) {\n    foreach ($delFeatures as $feature) {\n        $delete = $modx->removeObject('earthPlantingFeature', [\n            'planting_id' => $object->get('id'),\n            'option_id' => $feature,\n        ]);\n    }\n}\n\n$earthbrain->resetNull($object, $properties);\n$earthbrain->saveLocation($object, $properties);\n\nreturn '';"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/e6_dat_save/migxsaveplanting.snippet.php'

-----


/**
 * migxSavePlant
 *
 * Aftersave snippet for plants.
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

//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties,1));

if (!is_object($object)) return;

// Set key and ID of parent object
if ($co_id) {
    $object->set('plant_id', $co_id);
    $object->save();
}

// Get location
$locationID = $object->get('location_id');

// Get currently selected features
$currentFeatures = [];
$joinedFeatures = $object->getMany('Features');
foreach ($joinedFeatures as $feature) {
    $currentFeatures[] = $feature->get('option_id');
}

// Get updated features
$savedFeatures = (array)$properties['features'] ?? [];

// Differentiate which features to add, and which to remove
$addFeatures = array_diff_assoc($savedFeatures, $currentFeatures);
$delFeatures = array_diff_assoc($currentFeatures, $savedFeatures);

// Add features, but watch out for empty features
if ($addFeatures) {
    $features = [];
    foreach ($addFeatures as $feature) {
        if (!$feature) continue;
        $features[] = $modx->newObject('earthPlantingFeature', [
            'planting_id' => $object->get('id'),
            'option_id' => $feature,
        ]);
    }
    $object->addMany($features);
}

if ($delFeatures) {
    foreach ($delFeatures as $feature) {
        $delete = $modx->removeObject('earthPlantingFeature', [
            'planting_id' => $object->get('id'),
            'option_id' => $feature,
        ]);
    }
}

$earthbrain->resetNull($object, $properties);
$earthbrain->saveLocation($object, $properties);

return '';
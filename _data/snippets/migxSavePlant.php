id: 100085
name: migxSavePlant
category: E6_dat_save
properties: 'a:0:{}'

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
$earthbrain = $modx->getService('earthbrain','earthbrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);
$configs = $modx->getOption('configs', $properties, '');
$postValues = $modx->getOption('postvalues', $scriptProperties, []);

$co_id = $modx->getOption('co_id', $properties);
$locationID = '';

//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties,1));

if (is_object($object)) {
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
}

$earthbrain->resetNull($object, $properties);
$earthbrain->saveLocation($object, $properties, $locationID);

return '';
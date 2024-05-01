id: 100082
name: migxVerifyData
description: 'Hook that checks and corrects data before saving. For generic use.'
category: E6_dat_verify
properties: 'a:0:{}'

-----

/**
 * migxVerifyData
 *
 * Hook that checks and corrects data before saving.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, '');
$configs = $modx->getOption('configs', $properties, '');

//$modx->log(modX::LOG_LEVEL_ERROR, 'Props: ' . print_r($properties, true));
//$modx->log(modX::LOG_LEVEL_ERROR, 'Configs: ' . $configs);
//$modx->log(modX::LOG_LEVEL_ERROR, 'Field: ' . print_r($field, true));

$data = json_decode($properties['data'], true);
//$modx->log(modX::LOG_LEVEL_ERROR, 'Data: ' . print_r($data, true));

// Format decimal keys
$decimalKeys = array(
    'height',
    'radius',
    'size',
    'quantity',
    'price',
    'lat',
    'lng',
    'elevation',
);

foreach ($data as $key => $value)
{
    // Change comma separator to dot for decimals
    if (in_array($key,$decimalKeys) && stripos($value, ',')) {
        $value = str_replace(',','.',$value);
        $modx->log(modX::LOG_LEVEL_ERROR, 'Changed decimal separator for: ' . $key);
        $object->set($key, $value);
    }

    // Reset empty decimals to NULL
    if (in_array($key,$decimalKeys) && $value === '') {
        $modx->log(modX::LOG_LEVEL_WARN, 'NULL was reset for: ' . $key);
        $object->set($key, NULL);
    }
}

// If class_key exists, make sure it is set
if (array_key_exists('class_key', $properties) && !$properties['class_key'] ?? '')
{
    $configType = preg_match('/(.+)_(.+):(.+)/', $configs, $matches);
    $configType = rtrim($matches[2], 's');
    return json_encode(['error'=>"Please select $configType type."]);
}

// Save changes
$object->save();

// Validate JSON data
if (array_key_exists('Location_geojson', $properties))
{
    if ($properties['Location_geojson']) {
        $validateOutput = $earthbrain->validateJSON($properties['Location_geojson']);

        if ($validateOutput) {
            return json_encode($validateOutput);
        }
    }
}

return true;
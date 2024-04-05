id: 100082
name: migxVerifyData
description: 'Hook that checks and corrects data before saving. For generic use.'
category: E6_dat_verify
snippet: "/**\n * migxVerifyData\n *\n * Hook that checks and corrects data before saving.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\n\n$object = $modx->getOption('object', $scriptProperties);\n$properties = $modx->getOption('scriptProperties', $scriptProperties, '');\n$configs = $modx->getOption('configs', $properties, '');\n\n//$modx->log(modX::LOG_LEVEL_ERROR, 'Props: ' . print_r($properties, true));\n//$modx->log(modX::LOG_LEVEL_ERROR, 'Configs: ' . $configs);\n//$modx->log(modX::LOG_LEVEL_ERROR, 'Field: ' . print_r($field, true));\n\n$data = json_decode($properties['data'], true);\n//$modx->log(modX::LOG_LEVEL_ERROR, 'Data: ' . print_r($data, true));\n\n// Format decimal keys\n$decimalKeys = array(\n    'height',\n    'radius',\n    'size',\n    'quantity',\n    'price',\n    'lat',\n    'lng',\n    'elevation',\n);\n\nforeach ($data as $key => $value)\n{\n    // Change comma separator to dot for decimals\n    if (in_array($key,$decimalKeys) && stripos($value, ',')) {\n        $value = str_replace(',','.',$value);\n        $modx->log(modX::LOG_LEVEL_ERROR, 'Changed decimal separator for: ' . $key);\n        $object->set($key, $value);\n    }\n\n    // Reset empty decimals to NULL\n    if (in_array($key,$decimalKeys) && $value === '') {\n        $modx->log(modX::LOG_LEVEL_WARN, 'NULL was reset for: ' . $key);\n        $object->set($key, NULL);\n    }\n}\n\n// If class_key exists, make sure it is set\nif (array_key_exists('class_key', $properties) && !$properties['class_key'] ?? '')\n{\n    $configType = preg_match('/(.+)_(.+):(.+)/', $configs, $matches);\n    $configType = rtrim($matches[2], 's');\n    return json_encode(['error'=>\"Please select $configType type.\"]);\n}\n\n// Save changes\n$object->save();\n\n// Validate JSON data\nif (array_key_exists('Location_geojson', $properties))\n{\n    if ($properties['Location_geojson']) {\n        $validateOutput = $earthbrain->validateJSON($properties['Location_geojson']);\n\n        if ($validateOutput) {\n            return json_encode($validateOutput);\n        }\n    }\n}\n\nreturn true;"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/e6_dat_verify/migxverifydata.snippet.php'

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
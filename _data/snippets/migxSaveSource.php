id: 100076
name: migxSaveSource
category: E6_dat_save
snippet: "/**\n * migxSaveSource\n *\n * Aftersave snippet for sources.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n$earthlocation = $modx->getService('earthlocation','earthLocation',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n$earthimage = $modx->getService('earthimage','earthImage',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\nif (!($earthlocation instanceof earthLocation)) return;\nif (!($earthimage instanceof earthImage)) return;\n\n$object = $modx->getOption('object', $scriptProperties);\n$properties = $modx->getOption('scriptProperties', $scriptProperties, []);\n$configs = $modx->getOption('configs', $properties, '');\n$postValues = $modx->getOption('postvalues', $scriptProperties, []);\n$result = [];\n\nif (!is_object($object)) return;\n\n$earthbrain->resetNull($object, $properties);\n\n// Geocode addresses\nif ($properties['Location_geocode']) {\n    $location = $earthlocation->geocodeAddress($properties['Location_geocode']);\n\n    // Abort on error\n    if (!$location || $location['error']) {\n        return json_encode($location);\n    }\n\n    // Update coordinates and address, or just the coordinates\n    if ($properties['Location_update_address']) {\n        $properties = array_merge($properties, $location);\n    } else {\n        $properties['Location_lat'] = $location['lat'];\n        $properties['Location_lng'] = $location['lng'];\n    }\n}\nelseif ($properties['Location_geocode_reverse']) {\n    $location = $earthlocation->geocodeAddressReverse($properties['Location_geocode_reverse']);\n    $properties = array_merge($properties, $location);\n}\n\n// Attempt to extract location from image\nif ($properties['Location_from_image']) {\n    $path = $properties['Location_from_image'];\n    $source = $modx->getOption('earthbrain.img_source_meta');\n\n    if ($location = $earthimage->getExifData($path, $source)) {\n        $properties['Location_lat'] = $location['lat'];\n        $properties['Location_lng'] = $location['lng'];\n        $properties['Location_elevation'] = $location['elevation'];\n    }\n}\n\n$earthbrain->saveAddress($object, $properties);\n$earthbrain->saveLocation($object, $properties);\n\nreturn json_encode($result);"
properties: 'a:0:{}'

-----


/**
 * migxSaveSource
 *
 * Aftersave snippet for sources.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));
$earthlocation = $modx->getService('earthlocation','earthLocation',$corePath . 'model/earthbrain/',array('core_path' => $corePath));
$earthimage = $modx->getService('earthimage','earthImage',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;
if (!($earthlocation instanceof earthLocation)) return;
if (!($earthimage instanceof earthImage)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);
$configs = $modx->getOption('configs', $properties, '');
$postValues = $modx->getOption('postvalues', $scriptProperties, []);
$result = [];

if (!is_object($object)) return;

$earthbrain->resetNull($object, $properties);

// Geocode addresses
if ($properties['Location_geocode']) {
    $location = $earthlocation->geocodeAddress($properties['Location_geocode']);

    // Abort on error
    if (!$location || $location['error']) {
        return json_encode($location);
    }

    // Update coordinates and address, or just the coordinates
    if ($properties['Location_update_address']) {
        $properties = array_merge($properties, $location);
    } else {
        $properties['Location_lat'] = $location['lat'];
        $properties['Location_lng'] = $location['lng'];
    }
}
elseif ($properties['Location_geocode_reverse']) {
    $location = $earthlocation->geocodeAddressReverse($properties['Location_geocode_reverse']);
    $properties = array_merge($properties, $location);
}

// Attempt to extract location from image
if ($properties['Location_from_image']) {
    $path = $properties['Location_from_image'];
    $source = $modx->getOption('earthbrain.img_source_meta');

    if ($location = $earthimage->getExifData($path, $source)) {
        $properties['Location_lat'] = $location['lat'];
        $properties['Location_lng'] = $location['lng'];
        $properties['Location_elevation'] = $location['elevation'];
    }
}

$earthbrain->saveAddress($object, $properties);
$earthbrain->saveLocation($object, $properties);

return json_encode($result);
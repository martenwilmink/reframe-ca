id: 100066
name: importGeoJSON
description: 'Work in progress.'
category: E6_import
snippet: "/**\n * importGeoJSON snippet\n *\n * Turn GeoJSON objects into database objects.\n *\n * IMPORTANT: This is just a blueprint!\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\nif (!($earthbrain instanceof EarthBrain)) return;\n\n$json = $modx->getOption('json', $scriptProperties);\n\n$createdOn = time();\n$createdBy = 1;\n\nif ($json) {\n    $validate = $earthbrain->validateJSON($json);\n\n    if (!$validate) {\n        $modx->log(modX::LOG_LEVEL_ERROR, '[importGeoJSON] Validation failed!');\n        return false;\n    }\n\n    $geoArray = json_decode($json, 1);\n    $output = [];\n\n    foreach ($geoArray['features'] as $id => $feature) {\n        $lat = $feature['geometry']['coordinates'][1];\n        $lng = $feature['geometry']['coordinates'][0];\n\n        $locationData = [\n            'lat' => $lat,\n            'lng' => $lng,\n            'elevation' => null,\n            'radius' => '',\n            'geojson' => null,\n            'createdon' => $createdOn,\n            'createdby' => $createdBy,\n            'published' => 1,\n        ];\n\n        // Reverse geocode coordinates\n        $location = $modx->runSnippet('geocodeAddress', ['lat' => $lat, 'lng' => $lng]) ?? [];\n\n        $addressData = [\n            'line_1' => trim($location['properties']['streetNumber'] . ' ' . $location['properties']['streetName']),\n            'line_2' => '',\n            'line_3' => $location['properties']['subLocality'] ?? '',\n            'locality' => $location['properties']['locality'] ?? '',\n            'region' => $location['properties']['adminLevels'][1]['name'] ?? '',\n            'country' => $location['properties']['countryCode'] ?? '',\n            'postal_code' => $location['properties']['postalCode'] ?? '',\n            'comments' => '',\n            'createdon' => $createdOn,\n            'createdby' => $createdBy,\n            'published' => 1,\n        ];\n\n        $output[$id] = [\n            'network_id' => $modx->getOption('network', $scriptProperties),\n            'title' => $feature['properties']['name'],\n            'description' => $feature['properties']['description'],\n            'type' => '',\n            'images' => $feature['properties']['gx_media_links'],\n            'createdon' => $createdOn,\n            'createdby' => $createdBy,\n            'published' => 0,\n            'nodeAddress' => $addressData,\n            'nodeLocation' => $locationData,\n\n        ];\n\n        $modx->log(modX::LOG_LEVEL_ERROR, $id);\n        $modx->log(modX::LOG_LEVEL_ERROR, print_r($output[$id],1));\n    }\n}"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_import/importgeojson.snippet.php'

-----


/**
 * importGeoJSON snippet
 *
 * Turn GeoJSON objects into database objects.
 *
 * IMPORTANT: This is just a blueprint!
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));
if (!($earthbrain instanceof EarthBrain)) return;

$json = $modx->getOption('json', $scriptProperties);

$createdOn = time();
$createdBy = 1;

if ($json) {
    $validate = $earthbrain->validateJSON($json);

    if (!$validate) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[importGeoJSON] Validation failed!');
        return false;
    }

    $geoArray = json_decode($json, 1);
    $output = [];

    foreach ($geoArray['features'] as $id => $feature) {
        $lat = $feature['geometry']['coordinates'][1];
        $lng = $feature['geometry']['coordinates'][0];

        $locationData = [
            'lat' => $lat,
            'lng' => $lng,
            'elevation' => null,
            'radius' => '',
            'geojson' => null,
            'createdon' => $createdOn,
            'createdby' => $createdBy,
            'published' => 1,
        ];

        // Reverse geocode coordinates
        $location = $modx->runSnippet('geocodeAddress', ['lat' => $lat, 'lng' => $lng]) ?? [];

        $addressData = [
            'line_1' => trim($location['properties']['streetNumber'] . ' ' . $location['properties']['streetName']),
            'line_2' => '',
            'line_3' => $location['properties']['subLocality'] ?? '',
            'locality' => $location['properties']['locality'] ?? '',
            'region' => $location['properties']['adminLevels'][1]['name'] ?? '',
            'country' => $location['properties']['countryCode'] ?? '',
            'postal_code' => $location['properties']['postalCode'] ?? '',
            'comments' => '',
            'createdon' => $createdOn,
            'createdby' => $createdBy,
            'published' => 1,
        ];

        $output[$id] = [
            'network_id' => $modx->getOption('network', $scriptProperties),
            'title' => $feature['properties']['name'],
            'description' => $feature['properties']['description'],
            'type' => '',
            'images' => $feature['properties']['gx_media_links'],
            'createdon' => $createdOn,
            'createdby' => $createdBy,
            'published' => 0,
            'nodeAddress' => $addressData,
            'nodeLocation' => $locationData,

        ];

        $modx->log(modX::LOG_LEVEL_ERROR, $id);
        $modx->log(modX::LOG_LEVEL_ERROR, print_r($output[$id],1));
    }
}
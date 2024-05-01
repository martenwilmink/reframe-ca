id: 100066
name: importGeoJSON
description: 'Work in progress.'
category: E6_import
properties: 'a:0:{}'

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
$earthbrain = $modx->getService('earthbrain','earthbrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));
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
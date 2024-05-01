id: 100065
name: importKoboData
description: 'Work in progress. Will probably function as blueprint only.'
category: E6_import
properties: 'a:0:{}'

-----

/**
 * importKoboData snippet
 *
 * Get form submissions from Kobo Toolbox through the API.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','earthbrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

// Guzzle should be available as MODX extra
if (!class_exists(Client::class)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Guzzle client not found.');
    return 'Guzzle client not found.';
}
$client = new Client();

$apiURL = $modx->getOption('apiURL', $scriptProperties);
$apiUser = $modx->getOption('apiUser', $scriptProperties);
$apiKey = $modx->getOption('apiKey', $scriptProperties);
$cacheKey = $modx->getOption('cacheKey', $scriptProperties, 'earthbrain');

$cacheManager = $modx->getCacheManager();
$cacheLifetime = (int)$modx->getOption('cacheLifetime', $scriptProperties, 48 * 60 * 60, true);
$cacheOptions = [
    xPDO::OPT_CACHE_KEY => 'kobo',
];
$fromCache = true;
$data = $cacheManager->get($cacheKey, $cacheOptions);

// Connect to API and get the data
if (!is_array($data)) {
    $fromCache = false;
    $response = '';

    try {
        $response = $client->request('GET', $apiURL, [
            'headers' => [
                'Authorization' => 'Token ' . $apiKey,
                'Accept' => 'application/json',
            ],
        ]);
    }
    catch (GuzzleException $e) {
        if ($e->hasResponse()) {
            $response = $e->getResponse();
        }
    }

    if ($response->getStatusCode() != 200) {
        $error = '[importKoboData] Request failed with status code ' . $response->getStatusCode() . ': ' . $response->getBody();
        $modx->log(modX::LOG_LEVEL_ERROR, $error);
        return $error;
    }

    $data = json_decode($response->getBody(), true);
    $cacheManager->set($cacheKey, $data, $cacheLifetime, $cacheOptions);
}

//echo "<pre><code>";
//echo print_r($data['results'], 1);
//echo "</code></pre>";

if (!is_array($data)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[importKoboData] Could not find requested data');
    return '';
}

// Let's start with collecting the coordinates in a GeoJSON object
$features = [];

foreach ($data['results'] as $result) {
    $id = $result['_id'];
    $name = $result['profile/name_local'];
    $name = ucfirst($name);

    $coordinates = $result['location/coordinates'];
    $coordinates = explode(' ', $coordinates);
    $location = [
        'lat' => $coordinates[0],
        'lng' => $coordinates[1],
        'elevation' => $coordinates[2],
        'accuracy' => $coordinates[3],
        'privacy' => $result['location/privacy'],
        'radius' => 0,
    ];

    if (!$location['lat'] || !$location['lng']) {
        $location['lat'] = $result['_geolocation'][0];
        $location['lng'] = $result['_geolocation'][1];
    }
    if (!$location['lat'] || !$location['lng']) {
        continue;
    }
    if ($location['privacy'] == 'obfuscated') {
        $location['radius'] = 13;
    }

    $features[] = [
        'type' => 'Feature',
        'properties' => [
            'name' => $name,
            'amenity' => '',
            'popupContent' => $result['forest/forest_name'],
            'id' => $id,
        ],
        'geometry' => [
            'type' => 'Point',
            'coordinates' => [$location['lng'], $location['lat']]
        ]
    ];

    // From here, further import actions can be defined
}

//echo "<pre><code>";
//echo print_r($output, 1);
//echo "</code></pre>";

$output = [
    'type' => 'FeatureCollection',
    'features' => $features,
];

//return;

return json_encode($output, JSON_PRETTY_PRINT);
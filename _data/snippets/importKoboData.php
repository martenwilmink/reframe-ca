id: 100065
name: importKoboData
description: 'Work in progress. Will probably function as blueprint only.'
category: E6_import
snippet: "/**\n * importKoboData snippet\n *\n * Get form submissions from Kobo Toolbox through the API.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\nuse GuzzleHttp\\Client;\nuse GuzzleHttp\\Exception\\GuzzleException;\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\n\n// Guzzle should be available as MODX extra\nif (!class_exists(Client::class)) {\n    $modx->log(modX::LOG_LEVEL_ERROR, 'Guzzle client not found.');\n    return 'Guzzle client not found.';\n}\n$client = new Client();\n\n$apiURL = $modx->getOption('apiURL', $scriptProperties);\n$apiUser = $modx->getOption('apiUser', $scriptProperties);\n$apiKey = $modx->getOption('apiKey', $scriptProperties);\n$cacheKey = $modx->getOption('cacheKey', $scriptProperties, 'earthbrain');\n\n$cacheManager = $modx->getCacheManager();\n$cacheLifetime = (int)$modx->getOption('cacheLifetime', $scriptProperties, 48 * 60 * 60, true);\n$cacheOptions = [\n    xPDO::OPT_CACHE_KEY => 'kobo',\n];\n$fromCache = true;\n$data = $cacheManager->get($cacheKey, $cacheOptions);\n\n// Connect to API and get the data\nif (!is_array($data)) {\n    $fromCache = false;\n    $response = '';\n\n    try {\n        $response = $client->request('GET', $apiURL, [\n            'headers' => [\n                'Authorization' => 'Token ' . $apiKey,\n                'Accept' => 'application/json',\n            ],\n        ]);\n    }\n    catch (GuzzleException $e) {\n        if ($e->hasResponse()) {\n            $response = $e->getResponse();\n        }\n    }\n\n    if ($response->getStatusCode() != 200) {\n        $error = '[importKoboData] Request failed with status code ' . $response->getStatusCode() . ': ' . $response->getBody();\n        $modx->log(modX::LOG_LEVEL_ERROR, $error);\n        return $error;\n    }\n\n    $data = json_decode($response->getBody(), true);\n    $cacheManager->set($cacheKey, $data, $cacheLifetime, $cacheOptions);\n}\n\n//echo \"<pre><code>\";\n//echo print_r($data['results'], 1);\n//echo \"</code></pre>\";\n\nif (!is_array($data)) {\n    $modx->log(modX::LOG_LEVEL_ERROR, '[importKoboData] Could not find requested data');\n    return '';\n}\n\n// Let's start with collecting the coordinates in a GeoJSON object\n$features = [];\n\nforeach ($data['results'] as $result) {\n    $id = $result['_id'];\n    $name = $result['profile/name_local'];\n    $name = ucfirst($name);\n\n    $coordinates = $result['location/coordinates'];\n    $coordinates = explode(' ', $coordinates);\n    $location = [\n        'lat' => $coordinates[0],\n        'lng' => $coordinates[1],\n        'elevation' => $coordinates[2],\n        'accuracy' => $coordinates[3],\n        'privacy' => $result['location/privacy'],\n        'radius' => 0,\n    ];\n\n    if (!$location['lat'] || !$location['lng']) {\n        $location['lat'] = $result['_geolocation'][0];\n        $location['lng'] = $result['_geolocation'][1];\n    }\n    if (!$location['lat'] || !$location['lng']) {\n        continue;\n    }\n    if ($location['privacy'] == 'obfuscated') {\n        $location['radius'] = 13;\n    }\n\n    $features[] = [\n        'type' => 'Feature',\n        'properties' => [\n            'name' => $name,\n            'amenity' => '',\n            'popupContent' => $result['forest/forest_name'],\n            'id' => $id,\n        ],\n        'geometry' => [\n            'type' => 'Point',\n            'coordinates' => [$location['lng'], $location['lat']]\n        ]\n    ];\n\n    // From here, further import actions can be defined\n}\n\n//echo \"<pre><code>\";\n//echo print_r($output, 1);\n//echo \"</code></pre>\";\n\n$output = [\n    'type' => 'FeatureCollection',\n    'features' => $features,\n];\n\n//return;\n\nreturn json_encode($output, JSON_PRETTY_PRINT);"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_import/importkobodata.snippet.php'

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
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

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
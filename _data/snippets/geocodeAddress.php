id: 100067
name: geocodeAddress
description: 'Convert address sting into a set of geographical coordinates. Alternatively, you can provide a set of coordinates too, which will be converted to a human-readable address.'
category: E6_map
snippet: "/**\n * geocodeAddress snippet\n *\n * Convert address sting into a set of geographical coordinates.\n *\n * Alternatively, you can provide a set of coordinates too, which will be\n * converted to a human-readable address.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\nuse GuzzleHttp\\Client;\nuse GuzzleHttp\\Exception\\GuzzleException;\n//use Geocoder\\Provider\\Mapbox\\Mapbox;\nuse Geocoder\\Provider\\LocationIQ\\LocationIQ;\nuse Geocoder\\Query\\GeocodeQuery;\nuse Geocoder\\Query\\ReverseQuery;\nuse Geocoder\\Exception\\Exception;\nuse Geocoder\\Dumper\\GeoArray;\n\n$config = [\n    'timeout' => 5.0,\n    'verify' => true,\n];\n\n$httpClient = new Client($config);\n$geocoder = new LocationIQ($httpClient, 'pk.2cbec71a4e2a5c7b60d6c782cfac8f2c', 'eu1');\n//$geocoder = new Mapbox($httpClient, $modx->getOption('earthbrain.mapbox_access_token'));\n\n$address = $modx->getOption('address', $scriptProperties, $input ?? null);\n$address = str_replace(\"\\r\\n\", ', ', $address);\n\n// Input can be a set of coordinates too, for reverse geocoding\n$lat = $modx->getOption('lat', $scriptProperties, $lat ?? null);\n$lng = $modx->getOption('lng', $scriptProperties, $lng ?? null);\n\n// Cache results, to prevent unnecessary API requests\n$cacheManager = $modx->getCacheManager();\n$cacheKey = 'geocoder';\n$cacheElementKey = 'locations/' . md5(json_encode($address ?: $lat.$lng));\n$cacheLifetime = 86400*365;\n$cacheOptions = [\n    xPDO::OPT_CACHE_KEY => $cacheKey,\n    xPDO::OPT_CACHE_EXPIRES => $cacheLifetime,\n];\n\n// Check the cache first\n$locationCached = $cacheManager->get($cacheElementKey, $cacheOptions);\n\n// If a cached result was found, use that data\nif ($locationCached) {\n    $location = $locationCached;\n} else {\n    try {\n        $dumper = new GeoArray();\n\n        // Either geocode address, or reverse geocode coordinates\n        if ($address) {\n            $location = $geocoder->geocodeQuery(GeocodeQuery::create($address));\n        }\n        elseif ($lat && $lng) {\n            $location = $geocoder->reverseQuery(ReverseQuery::fromCoordinates($lat,$lng));\n        }\n        else {\n            $modx->log(modX::LOG_LEVEL_ERROR, '[geocodeAddress] No address or coordinates provided.');\n            return false;\n        }\n\n        $location = $dumper->dump($location->first());\n    }\n    catch (GuzzleException $e) {\n        $modx->log(modX::LOG_LEVEL_ERROR, '[geocodeAddress] ' . $e->getMessage());\n        return false;\n    }\n    catch (Exception $e) {\n        $modx->log(modX::LOG_LEVEL_ERROR, '[geocodeAddress] Error ' . $e->getCode() . ': ' . $e->getMessage());\n        return false;\n    }\n\n    // Cache result\n    $cacheManager->set($cacheElementKey, $location, $cacheLifetime, $cacheOptions);\n}\n\nreturn $location;"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_map/geocodeaddress.snippet.php'

-----


/**
 * geocodeAddress snippet
 *
 * Convert address sting into a set of geographical coordinates.
 *
 * Alternatively, you can provide a set of coordinates too, which will be
 * converted to a human-readable address.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
//use Geocoder\Provider\Mapbox\Mapbox;
use Geocoder\Provider\LocationIQ\LocationIQ;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Exception\Exception;
use Geocoder\Dumper\GeoArray;

$config = [
    'timeout' => 5.0,
    'verify' => true,
];

$httpClient = new Client($config);
$geocoder = new LocationIQ($httpClient, 'pk.2cbec71a4e2a5c7b60d6c782cfac8f2c', 'eu1');
//$geocoder = new Mapbox($httpClient, $modx->getOption('earthbrain.mapbox_access_token'));

$address = $modx->getOption('address', $scriptProperties, $input ?? null);
$address = str_replace("\r\n", ', ', $address);

// Input can be a set of coordinates too, for reverse geocoding
$lat = $modx->getOption('lat', $scriptProperties, $lat ?? null);
$lng = $modx->getOption('lng', $scriptProperties, $lng ?? null);

// Cache results, to prevent unnecessary API requests
$cacheManager = $modx->getCacheManager();
$cacheKey = 'geocoder';
$cacheElementKey = 'locations/' . md5(json_encode($address ?: $lat.$lng));
$cacheLifetime = 86400*365;
$cacheOptions = [
    xPDO::OPT_CACHE_KEY => $cacheKey,
    xPDO::OPT_CACHE_EXPIRES => $cacheLifetime,
];

// Check the cache first
$locationCached = $cacheManager->get($cacheElementKey, $cacheOptions);

// If a cached result was found, use that data
if ($locationCached) {
    $location = $locationCached;
} else {
    try {
        $dumper = new GeoArray();

        // Either geocode address, or reverse geocode coordinates
        if ($address) {
            $location = $geocoder->geocodeQuery(GeocodeQuery::create($address));
        }
        elseif ($lat && $lng) {
            $location = $geocoder->reverseQuery(ReverseQuery::fromCoordinates($lat,$lng));
        }
        else {
            $modx->log(modX::LOG_LEVEL_ERROR, '[geocodeAddress] No address or coordinates provided.');
            return false;
        }

        $location = $dumper->dump($location->first());
    }
    catch (GuzzleException $e) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[geocodeAddress] ' . $e->getMessage());
        return false;
    }
    catch (Exception $e) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[geocodeAddress] Error ' . $e->getCode() . ': ' . $e->getMessage());
        return false;
    }

    // Cache result
    $cacheManager->set($cacheElementKey, $location, $cacheLifetime, $cacheOptions);
}

return $location;
id: 100067
name: geocodeAddress
description: 'Convert address sting into a set of geographical coordinates. Alternatively, you can provide a set of coordinates too, which will be converted to a human-readable address.'
category: E6_map
properties: 'a:0:{}'

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
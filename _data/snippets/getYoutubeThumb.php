id: 148
name: getYoutubeThumb
description: 'Retrieve the largest existing thumbnail image available. You can choose between JPG and webP extension. Can be used as output modifier as well.'
category: f_presentation
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:39:"romanesco.getyoutubethumb.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:40:"romanesco.getyoutubethumb.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * getYoutubeThumb
 *
 * Retrieve the largest existing thumbnail image available. You can choose
 * between JPG and WebP extension.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$videoID = $modx->getOption('videoID', $scriptProperties, '');
$imgSize = $modx->getOption('imgSize', $scriptProperties, '720');
$imgType = $modx->getOption('imgType', $scriptProperties, 'webp');
$prefix = $modx->getOption('prefix', $scriptProperties, '');

$cacheKey = $videoID;
$cacheManager = $modx->getCacheManager();
$cacheLifetime = (int)$modx->getOption('cacheLifetime', $scriptProperties, 7 * 24 * 60 * 60, true);
$cacheOptions = [
    xPDO::OPT_CACHE_KEY => 'video/youtube',
];
$fromCache = true;
$data = $cacheManager->get($cacheKey, $cacheOptions);

// Use pThumb cache location if activated
if ($modx->getOption('pthumb.use_ptcache', null, true) ) {
    $cachePath = $modx->getOption('pthumb.ptcache_location', null, 'assets/cache/img', true);
} else {
    $cachePath = $modx->getOption('phpthumbof.cache_path', null, "assets/components/phpthumbof/cache", true);
}
$cachePath = rtrim($cachePath, '/') . '/video/youtube/';
$cachePathFull = MODX_BASE_PATH . $cachePath;

// Invalidate cache if ID changed
if (isset($data['properties']) && array_diff($data['properties'], $scriptProperties)) {
    $data = '';
}

// Invalidate cache if thumbnail can't be found
if (isset($data['thumbPath']) && !file_exists(MODX_BASE_PATH . $data['thumbPath'])) {
    $data = '';
}

// Make the request and fetch thumbnail
if (!is_array($data)) {
    $fromCache = false;
    if (!$imgType) $imgType = 'jpg';
    $vi = ($imgType == 'webp') ? 'vi_webp' : 'vi';
    $imgURL = "https://img.youtube.com/$vi/$videoID/0.$imgType";

    $resolutions = ['maxresdefault', 'hqdefault', 'mqdefault'];

    // Loop through resolutions until a match is found
    foreach($resolutions as $resolution) {
        $maxResURL = "https://img.youtube.com/$vi/$videoID/$resolution.$imgType";
        if(@getimagesize($maxResURL)) {
            $imgURL = $maxResURL;
            break;
        }
    }

    // Write image file to assets cache folder
    $thumbFile = file_get_contents($imgURL);
    $thumbFileName = $videoID . '.' . $imgType;
    $thumbPath = $cachePath . $thumbFileName;

    if (!@is_dir($cachePathFull)) {
        if (!@mkdir($cachePathFull, 0755, 1)) {
            $modx->log(xPDO::LOG_LEVEL_ERROR, '[getYoutubeThumb] Could not create cache path.', '', 'Romanesco');
        }
    }
    if (!@file_put_contents(MODX_BASE_PATH . $thumbPath, $thumbFile)) {
        $modx->log(xPDO::LOG_LEVEL_ERROR, '[getYoutubeThumb] Could not create thumbnail file @ ' . $thumbPath, '', 'Romanesco');
    }

    // Create array of data to be cached
    $data = [
        'properties' => $scriptProperties,
        'thumbURL' => $imgURL,
        'thumbPath' => $thumbPath,
    ];

    $cacheManager->set($cacheKey, $data, $cacheLifetime, $cacheOptions);
}

if (!is_array($data)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[getYoutubeThumb] Could not find requested data');
    return '';
}

// Load data from cache
$modx->toPlaceholder('youtubeThumb', $data['thumbPath'], $prefix);

//return '<p>From cache: ' . ($fromCache ? 'Yes' : 'No') . '</p>';

return;
id: 122
name: getImageDimensions
description: 'Retrieve width and height from physical image files. Auto detects SVGs.'
category: f_presentation
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:42:"romanesco.getimagedimensions.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:43:"romanesco.getimagedimensions.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * getImageDimensions
 *
 * Retrieve width and height from physical image files. Auto-detects SVGs.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$imgPath = $modx->getOption('image', $scriptProperties, '');
$imgFile = pathinfo($imgPath, PATHINFO_FILENAME);
$imgType = pathinfo($imgPath, PATHINFO_EXTENSION);

$phWidth = $modx->getOption('phWidth', $scriptProperties, 'img_width');
$phHeight = $modx->getOption('phHeight', $scriptProperties, 'img_height');
$prefix = $modx->getOption('prefix', $scriptProperties, '');

// Check if image is SVG
if (strtolower($imgType) == 'svg') {
    $modx->log(modX::LOG_LEVEL_INFO, '[getImageDimensions] Image is SVG');

    $xml = file_get_contents($imgPath);
    $attributes = simplexml_load_string($xml)->attributes();

    // Primarily relying on viewbox, since width and height values are not
    // required and also kind of meaningless given the scalable nature of SVG.
    $viewbox = (string) $attributes->viewBox;

    if ($viewbox) {
        $viewbox = explode(' ', $viewbox);

        $width = round($viewbox[2], 5);
        $height = round($viewbox[3], 5);
    }

    // Fall back on width and height attributes if viewbox is empty
    else {
        $width = (string) $attributes->width;
        $width = preg_replace('/[a-zA-Z]+/', '', $width);
        $width = round($width, 2);

        $height = (string) $attributes->height;
        $height = preg_replace('/[a-zA-Z]+/', '', $height);
        $height = round($height, 2);
    }
}

// Validate image file and get dimensions
else if (substr(mime_content_type($imgPath), 0, 5) === 'image') {
    $modx->log(modX::LOG_LEVEL_INFO, '[getImageDimensions] Logo is valid image file');

    $img = getimagesize($imgPath);

    $width = $img[0];
    $height = $img[1];
}

else {
    $modx->log(modX::LOG_LEVEL_ERROR, '[getImageDimensions] Image file could not be found');
    return '';
}

// Only output values if they exist
if ($width) $modx->toPlaceholder($phWidth, round($width), $prefix);
if ($height) $modx->toPlaceholder($phHeight, round($height), $prefix);

return '';
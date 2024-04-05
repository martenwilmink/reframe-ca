id: 100079
name: migxSaveImage
description: 'After save hook for images. Connects images to the correct parent object, and increments sort order of new items.'
category: E6_dat_save
snippet: "/**\n * migxSaveImage\n *\n * Hook snippet for images. Fire on aftersave event.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));\n$earthimage = $modx->getService('earthimage','earthImage',$corePath . 'model/earthbrain/', array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\nif (!($earthimage instanceof earthImage)) return;\n\n$object = $modx->getOption('object', $scriptProperties);\n$properties = $modx->getOption('scriptProperties', $scriptProperties, []);\n$configs = $modx->getOption('configs', $properties);\n$postValues = $modx->getOption('postvalues', $scriptProperties, []);\n$co_id = $modx->getOption('co_id', $properties, 0);\n$result = [];\n\n//$modx->log(modX::LOG_LEVEL_ERROR, print_r($_REQUEST, 1));\n//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties, 1));\n//$modx->log(modX::LOG_LEVEL_ERROR, print_r($postValues, 1));\n\nif (!is_object($object)) return;\n\n// Make sure null values are really null\n$earthbrain->resetNULL($object, $properties);\n\n// Attach object to parent\n$object->set('parent_id', $co_id);\n\n// If co_id is 0, then parent might be a resource\nif (!$co_id && $properties['resource_id']) {\n    $object->set('parent_id', $properties['resource_id']);\n}\n\n// Create image variants with different aspect ratios\n$earthimage->setImageVariants($object);\n\n// Extract Exif data from image\n$imageExif = $earthimage->getExifData($object->get('img'));\n\n// Use Exif data as fallback for manually entered values\nif ($imageExif) {\n    $properties['Location_lat'] = $properties['Location_lat'] ?: $imageExif['lat'];\n    $properties['Location_lng'] = $properties['Location_lng'] ?: $imageExif['lng'];\n\n    // Elevation can be 0, so avoid interpreting that as empty\n    if ($properties['Location_elevation'] != 0) {\n        $properties['Location_elevation'] = $properties['Location_elevation'] ?: $imageExif['elevation'];\n    }\n\n    // Attach actual date taken to image\n    if ($imageExif['date'] && !$properties['date']) {\n        $object->set('date', $imageExif['date']);\n    }\n}\n\n// Wrap up\n$earthbrain->saveLocation($object, $properties);\n$earthbrain->incrementPosition($object, $properties);\n\n$object->save();\n\nreturn json_encode($result);"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/e6_dat_save/migxsaveimage.snippet.php'

-----


/**
 * migxSaveImage
 *
 * Hook snippet for images. Fire on aftersave event.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));
$earthimage = $modx->getService('earthimage','earthImage',$corePath . 'model/earthbrain/', array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;
if (!($earthimage instanceof earthImage)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);
$configs = $modx->getOption('configs', $properties);
$postValues = $modx->getOption('postvalues', $scriptProperties, []);
$co_id = $modx->getOption('co_id', $properties, 0);
$result = [];

//$modx->log(modX::LOG_LEVEL_ERROR, print_r($_REQUEST, 1));
//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties, 1));
//$modx->log(modX::LOG_LEVEL_ERROR, print_r($postValues, 1));

if (!is_object($object)) return;

// Make sure null values are really null
$earthbrain->resetNULL($object, $properties);

// Attach object to parent
$object->set('parent_id', $co_id);

// If co_id is 0, then parent might be a resource
if (!$co_id && $properties['resource_id']) {
    $object->set('parent_id', $properties['resource_id']);
}

// Create image variants with different aspect ratios
$earthimage->setImageVariants($object);

// Extract Exif data from image
$imageExif = $earthimage->getExifData($object->get('img'));

// Use Exif data as fallback for manually entered values
if ($imageExif) {
    $properties['Location_lat'] = $properties['Location_lat'] ?: $imageExif['lat'];
    $properties['Location_lng'] = $properties['Location_lng'] ?: $imageExif['lng'];

    // Elevation can be 0, so avoid interpreting that as empty
    if ($properties['Location_elevation'] != 0) {
        $properties['Location_elevation'] = $properties['Location_elevation'] ?: $imageExif['elevation'];
    }

    // Attach actual date taken to image
    if ($imageExif['date'] && !$properties['date']) {
        $object->set('date', $imageExif['date']);
    }
}

// Wrap up
$earthbrain->saveLocation($object, $properties);
$earthbrain->incrementPosition($object, $properties);

$object->save();

return json_encode($result);
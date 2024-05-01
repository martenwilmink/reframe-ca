id: 100079
name: migxSaveImage
description: 'After save hook for images. Connects images to the correct parent object, and increments sort order of new items.'
category: E6_dat_save
properties: 'a:0:{}'

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
$earthimage = $modx->getService('earthimage','earthImage',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

if (!($earthimage instanceof earthImage)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);
$configs = $modx->getOption('configs', $properties);
$postValues = $modx->getOption('postvalues', $scriptProperties, []);

$co_id = $modx->getOption('co_id', $properties, 0);
$locationID = '';

//$modx->log(modX::LOG_LEVEL_ERROR, print_r($_REQUEST, 1));
//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties, 1));
//$modx->log(modX::LOG_LEVEL_ERROR, print_r($postValues, 1));

if (is_object($object))
{
    $object->set('parent_id', $co_id);

    // If co_id is 0, then parent might be a resource
    if (!$co_id && $properties['resource_id']) {
        $object->set('parent_id', $properties['resource_id']);
    }

    // Create image variants with different aspect ratios
    $earthimage->setImageVariants($object);

    $object->save();
}

// Increment sort order of new items
if ($properties['object_id'] === 'new') {

    // Ask for last position
    $q = $modx->newQuery($properties['class_key']);
    $q->select(array(
        "max(pos)",
    ));
    $lastPosition = $modx->getValue($q->prepare());

    // Set and Save
    $object->set('pos', ++$lastPosition);
    $object->save();
}

//$earthbrain->resetNull($object, $properties);
//$earthbrain->saveLocation($object, $properties, $locationID);

return '';
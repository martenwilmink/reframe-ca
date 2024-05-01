id: 100080
name: migxSaveLink
description: 'After save hook for links. Connects links to the correct parent object, and increments sort order of new items.'
category: E6_dat_save
properties: 'a:0:{}'

-----

/**
 * migxSaveLink
 *
 * Hook snippet for links. Fire on aftersave event.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','earthbrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);
$configs = $modx->getOption('configs', $properties, '');
$postValues = $modx->getOption('postvalues', $scriptProperties, []);
$co_id = $modx->getOption('co_id', $properties);

if (is_object($object))
{
    $object->set('parent_id', $co_id);

    // If co_id is 0, then parent might be a resource
    if (!$co_id && $properties['resource_id']) {
        $object->set('parent_id', $properties['resource_id']);
    }

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

return '';
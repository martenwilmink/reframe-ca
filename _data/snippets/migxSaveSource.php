id: 100076
name: migxSaveSource
category: E6_dat_save
properties: 'a:0:{}'

-----

/**
 * migxSaveSource
 *
 * Aftersave snippet for sources.
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

$result = [];
$locationID = '';
$addressID = null;

// Get IDs
if (is_object($object)) {
    $locationID = $object->get('location_id');
    $addressID = $object->get('address_id');
}

$earthbrain->resetNull($object, $properties);
$earthbrain->saveLocation($object, $properties, $locationID);
$earthbrain->saveAddress($object, $properties, $addressID);

return json_encode($result);
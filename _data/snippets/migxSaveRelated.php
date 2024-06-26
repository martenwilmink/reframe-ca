id: 132
name: migxSaveRelated
description: 'Aftersave hook for MIGXdb. Sets source and target IDs in opposite direction also, to establish a double cross-link. Yeah, better watch your back with those!'
category: f_data
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:39:"romanesco.migxsaverelated.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:40:"romanesco.migxsaverelated.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * migxSaveRelated
 *
 * Aftersave hook for MIGXdb. Sets source and target IDs in opposite direction
 * also, to establish a double cross-link. Yeah, watch your back with those!
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, array());
$configs = $modx->getOption('configs', $properties, '');

$objectID = $object->get('id');
$crosslinkID = $object->get('crosslink_id');
$source = $object->get('source');
$destination = $object->get('destination');
$title = $object->get('title');
$description = $object->get('description');
$createdon = $object->get('createdon');
$createdby = $object->get('createdby');
$weight = $object->get('weight');

// Set current resource as source (if no source was set)
if (!$source && isset($properties['resource_id'])) {
    $object->set('source', $properties['resource_id']);
    $object->save();

    // Update source variable
    $source = $object->get('source');
}

// Check if cross-link exists already
$existingSrc = $modx->getObject('rmCrosslinkRelated', array('source' => $source, 'destination' => $destination));
$existingDest = $modx->getObject('rmCrosslinkRelated', array('source' => $destination, 'destination' => $source));

// Create another cross-link in the opposite direction
if (is_object($existingSrc) && !is_object($existingDest)) {
    $newSrc = $modx->newObject('rmCrosslinkRelated', array(
        'crosslink_id' => $objectID,
        'source' => $destination,
        'destination' => $source,
        'title' => $title,
        'description' => $description,
        'createdon' => $createdon,
        'createdby' => $createdby,
        'weight' => $weight,
    ));
    $newSrc->save();

    // Set crosslink ID of source
    $object->set('crosslink_id', $newSrc->get('id'));
    $object->save();
}

return '';
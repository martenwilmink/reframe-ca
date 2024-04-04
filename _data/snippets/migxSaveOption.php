id: 128
name: migxSaveOption
description: 'Aftersave hook for MIGXdb. Gets and sets the group (parent) ID inside a nested configuration. Also generates an alias if none is present and increments the sort order.'
category: f_data
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:38:"romanesco.migxsaveoption.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:39:"romanesco.migxsaveoption.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * migxSaveOption
 *
 * Aftersave hook for MIGXdb. Gets and sets the group (parent) ID inside a
 * nested configuration. Also generates an alias if none is present and
 * increments the sort order.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, array());
$configs = $modx->getOption('configs', $properties, '');

$co_id = $modx->getOption('co_id', $properties, 0);
$parent = $modx->getObject('rmOptionGroup', array('id' => $co_id));

// Set key and ID of parent object
if (is_object($object)) {
    $object->set('key', $parent->get('key'));
    $object->set('group', $co_id);
    $object->save();
}

// Generate alias if empty
if (!$object->get('alias')) {
    $alias = $modx->runSnippet('stripAsAlias', (array('input' => $object->get('name'))));

    $object->set('alias', $alias);
    $object->save();
}

// Increment sort order of new items
if ($properties['object_id'] === 'new') {

    // Ask for last position
    $q = $modx->newQuery('rmOption');
    $q->select(array(
        "max(position)",
    ));
    $lastPosition = $modx->getValue($q->prepare());

    // Set and Save
    $object->set('position', ++$lastPosition);
    $object->save();
}

return '';
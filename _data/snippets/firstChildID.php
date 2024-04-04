id: 113
name: firstChildID
description: 'The name says it all: retrieve the first child ID of a given ID.'
category: f_basic
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:36:"romanesco.firstchildid.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:37:"romanesco.firstchildid.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * firstChildID
 * Finds the first child from the given id
 * Returns the first child id or the given id on failure
 *
 * @author Bert Oost <bert@oostdesign.nl> at OostDesign.nl
 *
 * Examples:
 *
 * As output filter:
 *   [[*id:firstChildID]]
 *
 * As snippet:
 *   [[firstChildID? &id=`[[*id]]`]]
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$id = (isset($input) && !empty($input)) ? $input : false;
if(empty($id)) { $id = $modx->getOption('id', $scriptProperties, $modx->resource->get('id')); }

// select the first child
$c = $modx->newQuery('modResource');
$c->select(array('id'));
$c->where(array(
    'parent' => $id,
    'published' => true,
));
$c->sortby('menuindex', 'ASC');
$c->limit(1);

$child = $modx->getObject('modResource', $c);
if(!empty($child) && $child instanceof modResource) {
    return $child->get('id');
}

return $id;
id: 70
name: resourceTVInputOptions
description: 'Generate input option values that can be used in TVs, for creating resource selectors.'
category: f_resource
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:46:"romanesco.resourcetvinputoptions.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:7:"unknown";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:47:"romanesco.resourcetvinputoptions.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * resourceTVInputOptions
 *
 * @todo: Is this snippet used anywhere?
 *
 * Grab list of resources from given parent. Parent ID can be provided directly
 * (with &parents) or via a context or system setting (&key).
 *
 * For use in ContentBlocks settings, add &tplMode=`cb`.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$key = $modx->getOption('key',$scriptProperties,null);
$context = $modx->getContext($modx->resource->get('context_key'));
$parents = $context->getOption($key) ?? $modx->getOption('parents',$scriptProperties,$key);

$tpl = $modx->getOption('tpl',$scriptProperties,'');
$tplMode = $modx->getOption('tplMode',$scriptProperties,'tv');
$separator = $modx->getOption('outputSeparator',$scriptProperties,null);

// Set appropriate template if no custom tpl is defined
if (!$tpl && $tplMode == 'cb') {
    $tpl = '@INLINE [[+pagetitle]]=[[+id]]';
}
if (!$tpl && $tplMode == 'tv') {
    $tpl = '@INLINE [[+pagetitle]]==[[+id]]';
    $separator = '||';
}

$output = $modx->runSnippet('getResources', (array(
    'parents' => $parents,
    'limit' => 99,
    'showHidden' => 1,
    'showUnpublished' => 1,
    'tpl' => $tpl,
    'sortby' => 'menuindex',
    'sortdir' => 'ASC',
    'outputSeparator' => $separator,
)));

return $output;
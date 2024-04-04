id: 57
name: fbGetForms
category: f_formblocks
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:34:"romanesco.fbgetforms.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:35:"romanesco.fbgetforms.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * fbGetForms snippet
 *
 * @var modX $modx
 * @var array $scriptProperties
 *
 * @package romanesco
 */

if (!($modx->resource instanceof modResource)) return;

$context = $modx->getContext($modx->resource->get('context_key'));
$contextKey = $context->get('key');
$contextName = $context->get('name');
$parentID = $context->getOption('formblocks.container_id') ?? $modx->getOption('formblocks.container_id');

$output = $modx->runSnippet('getResources', (array(
    'parents' => $parentID,
    'limit' => 0,
    'depth' => 2,
    'showHidden' => 0,
    'showUnpublished' => 1,
    'tpl' => '@INLINE ['.$contextName.'] [[+pagetitle]]=[[+id]]',
    'sortby' => 'menuindex',
    'sortdir' => 'ASC',
    'where' => '[{"template:IN":[10,19]},{"uri:LIKE":"%/'.$contextKey.'/%"}]',
)));
if ($output) {
    $output .= "\n";
}
$output .= $modx->runSnippet('getResources', (array(
    'parents' => $parentID,
    'limit' => 0,
    'depth' => 0,
    'showHidden' => 0,
    'showUnpublished' => 1,
    'tpl' => '@INLINE [[+pagetitle]]=[[+id]]',
    'sortby' => 'menuindex',
    'sortdir' => 'ASC',
    'where' => '{"template:IN":[10,19]}',
)));

return $output;
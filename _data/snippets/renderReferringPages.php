id: 167
name: renderReferringPages
description: 'Takes an ID as input and returns a list of pages in which this resource is used. Intended as snippet renderer for Collections, to show where Forms, CTAs and Backgrounds are being used.'
category: f_resource
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:44:"romanesco.renderreferringpages.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:12:"experimental";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:45:"romanesco.renderreferringpages.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * renderReferringPages
 *
 * Takes an ID as input and returns a list of pages in which this resource is
 * referenced. Intended as snippet renderer for Collections, to show where Forms,
 * CTAs and Backgrounds are being used.
 *
 * Scans content and TVs. Note that for TVs, inherited values are not evaluated.
 *
 * If you want to limit the list to only include pages from certain contexts,
 * you may do so by creating the system referring_pages_contexts in the
 * Romanesco namespace.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

$id = $modx->getOption('id', $scriptProperties, $scriptProperties['row']['id'] ?? '');
$contexts = $modx->getOption('contexts', $scriptProperties, $modx->getOption('romanesco.referring_pages_contexts') ?? '');
$column = $modx->getOption('column', $scriptProperties);

$where = '';

// Content
switch ($column) {
    case 'referring_pages_form':
        $where = '{ "properties:LIKE":"%\"form_id\":\"' . $id . '\"%" }';
        break;
    case 'referring_pages_cta':
        $where = '{ "properties:LIKE":"%\"cta_id\":\"' . $id . '\"%" }';
        break;
    case 'referring_pages_background':
        $where = '{ "properties:LIKE":"%background_____' . $id . '__,%" }';
        break;
}

if (!$where) return;

// TVs
$tvValues = [];
$tvValuesHead = $modx->getCollection('modTemplateVarResource', [
    'tmplvarid' => 3, // header_cta
    'value' => $id
]);
$tvValuesFooter = $modx->getCollection('modTemplateVarResource', [
    'tmplvarid' => 104, // footer_cta
    'value' => $id
]);
$tvValuesSidebar = $modx->getCollection('modTemplateVarResource', [
    'tmplvarid' => 148, // sidebar_cta
    'value' => $id
]);

foreach ($tvValuesHead as $value) {
    $tvValues[] = $value->get('contentid');
}
foreach ($tvValuesFooter as $value) {
    $tvValues[] = $value->get('contentid');
}
foreach ($tvValuesSidebar as $value) {
    $tvValues[] = $value->get('contentid');
}

if ($tvValues) {
    $where .= ',{ "OR:id:IN": [' . implode(',', $tvValues) . '] }';
}

$output = $modx->runSnippet('pdoMenu', (array(
    'parents' => '',
    'context' => $contexts,
    'limit' => 0,
    'depth' => 0,
    'showHidden' => 1,
    'showUnpublished' => 1,
    'tplOuter' => '@INLINE <ul class="referring-pages">[[+wrapper]]</ul>',
    'tpl' => '@INLINE <li><a href="[[~[[+id]]]]" target="_blank">[[+pagetitle]]</a> (<a title="Edit" href="[[++site_url]]manager/?a=resource/update&id=[[+id]]" target="_blank">[[+id]]</a>)</li>',
    'sortby' => 'menuindex',
    'sortdir' => 'ASC',
    'where' => "[$where]",
)));


return $output;
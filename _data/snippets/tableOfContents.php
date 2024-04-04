id: 136
name: tableOfContents
category: f_content
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:39:"romanesco.tableofcontents.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:40:"romanesco.tableofcontents.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * tableOfContents snippet
 *
 * Not to be confused with the ToC plugin. The plugin takes care of generating
 * the menu items, because this needs to be done during the rendering process.
 *
 * This snippet only sets a few placeholders for the plugin to pick up.
 *
 * The target attribute is required. Without it, no ToC menu is created.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @package romanesco
 */

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$target = $modx->getOption('target', $scriptProperties, '');

if ($target) {
    $modx->setPlaceholder('toc.target', $target);
} else {
    return '';
}

if ($tpl) $modx->setPlaceholder('toc.tpl', $tpl);
id: 30
name: LexiconWeb
description: 'Load default lexicon in web context.'
category: c_global
properties: 'a:1:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:34:"romanesco.lexiconweb.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * LexiconWeb
 *
 * Load default lexicon in web context.
 *
 * @var modX $modx
 * @package romanesco
 */

if ($modx->event->name == 'OnHandleRequest') {
    $modx->lexicon->load($modx->context->getOption('cultureKey') . ':romanescobackyard:default');
}
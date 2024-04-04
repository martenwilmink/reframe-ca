id: 44
name: AutomaticReferences
description: 'Turn references to an external link (the ones you can create under TVs > Links) into an actual link. Links are referenced by their number value and must be enclosed in square brackets: [12].'
category: c_content
properties: 'a:1:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:43:"romanesco.automaticreferences.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:6:"review";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * AutomaticReferences plugin
 *
 * Turn references to an external link (the ones you created under TVs > Links)
 * into an actual link. Links are referenced by their number value and must be
 * enclosed in square brackets: [12].
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @package romanesco
 */

if (!class_exists(\Wa72\HtmlPageDom\HtmlPageCrawler::class)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[HtmlPageDom] Class not found!');
    return;
}

use \Wa72\HtmlPageDom\HtmlPageCrawler;

$tpl = $modx->getOption('tpl', $scriptProperties, 'externalNavItemLabel');

switch ($modx->event->name) {
    case 'OnWebPagePrerender':

        // Get processed output of resource
        $content = &$modx->resource->_output;

        // Generate links if requested
        if ($modx->resource->getTVValue('auto_references')) {
            $corePath = $modx->getOption('romanescobackyard.core_path', null, $modx->getOption('core_path') . 'components/romanescobackyard/');
            $romanesco = $modx->getService('romanesco','Romanesco', $corePath . 'model/romanescobackyard/', array('core_path' => $corePath));
            if (!($romanesco instanceof Romanesco)) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[Romanesco] Class not found!');
                break;
            }

            // Get external links for this resource
            $linkObject = $modx->getIterator('rmExternalLink', [
                'resource_id' => $modx->resource->get('id'),
                'deleted' => 0
            ]);

            if (!is_object($linkObject)) break;

            // Prepare array with link tags
            $links = [];
            foreach ($linkObject as $link) {
                $links[$link->get('number')] = $modx->getChunk($tpl, $link->toArray());
            }

            // Feed output to HtmlPageDom
            $dom = new HtmlPageCrawler($content);

            // Search the content area for references
            $dom->filter('#content')
                ->filter('p,li')
                ->each(function (HtmlPageCrawler $node) use ($links) {
                    $text = $node->getInnerHtml();

                    // Only accept numeric values inside square brackets
                    preg_match_all('/\[[0-9]+\]/', $text, $matches);

                    // Filter duplicate matches to avoid glitches
                    $matches = array_unique($matches[0]);

                    if (!$matches) return true;

                    foreach ($matches as $match) {
                        $number = filter_var($match, FILTER_SANITIZE_NUMBER_INT);
                        $text = str_replace($match, $links[$number], $text);
                    }

                    $node->setInnerHtml($text);
                    return true;
                })
            ;

            $content = $dom->saveHTML();
        }

        break;
}

return true;
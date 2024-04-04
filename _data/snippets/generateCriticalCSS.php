id: 151
name: generateCriticalCSS
description: 'Utility snippet to determine which CSS styles are used above the fold and write them to a custom CSS file. This needs NPM and the critical package to be installed.'
category: f_performance
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:43:"romanesco.generatecriticalcss.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:44:"romanesco.generatecriticalcss.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * generateCriticalCSS
 *
 * Determine which CSS styles are used above the fold and write them to a custom
 * CSS file. This needs NPM and the critical package to be installed.
 *
 * https://github.com/addyosmani/critical
 *
 * It works by using runProcessor to save the given resource, which triggers
 * the GenerateCriticalCSS plugin, which in turn triggers the critical gulp task.
 * This detour is required, because the gulp task needs to know the exact path
 * of the critical CSS file, which is stored in a TV. Without the save action,
 * that TV might still be empty.
 *
 * Usage:
 *
 * - As a utility snippet. Place it in the content somewhere and visit that page
 *   in the browser to generate the file.
 * - As tpl inside a getResources / pdoTools call, to generate CSS for a batch
 *   of resources. Be careful though: will quickly lead to performance issues!
 * - As snippet source for a Scheduler task. This will bypass the processor
 *   part and execute the task directly.
 *
 * Example:
 *
 * [[!generateCriticalCSS? &id=`[[+id]]`]]
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var object $task
 *
 * @package romanesco
 */

$corePath = $modx->getOption('romanescobackyard.core_path', null, $modx->getOption('core_path') . 'components/romanescobackyard/');
$romanesco = $modx->getService('romanesco','Romanesco',$corePath . 'model/romanescobackyard/',array('core_path' => $corePath));

if (!($romanesco instanceof Romanesco)) return;

$resourceID = $modx->getOption('id', $scriptProperties, '');
$resourceURL = $modx->getOption('url', $scriptProperties, '');
$resourceURI = $modx->getOption('uri', $scriptProperties, '');
$resource = $modx->getObject('modResource', $resourceID);

if (!($resource instanceof modResource)) return;

// If snippet is run as scheduled task, generate CSS directly
if (is_object($task)) {
    $romanesco->generateCriticalCSS([
        'id' => $resourceID,
        'url' => $resourceURL,
        'uri' => $resourceURI ?? $resource->get('uri'),
        'cssPath' => $romanesco->getContextSetting('romanesco.custom_css_path', $resource->get('context_key')),
        'criticalPath' => $romanesco->getContextSetting('romanesco.critical_css_path', $resource->get('context_key')),
        'distPath' => $romanesco->getContextSetting('romanesco.semantic_dist_path', $resource->get('context_key')),
    ]);

    return "Critical CSS generated for: {$resource->get('uri')} ($resourceID)";
}

// Run update processor to generate the critical_css_uri TV value
// NB: processor won't run without pagetitle and context_key!
// NB: sometimes an old alias is retrieved when alias is not forwarded!!
$resourceFields = [
    'id' => $resourceID,
    'pagetitle' => $resource->get('pagetitle'),
    'alias' => $resource->get('alias'),
    'context_key' => $resource->get('context_key')
];

// The update processor will trigger the GenerateCriticalCSS plugin
$response = $modx->runProcessor('resource/update', $resourceFields);

if ($response->isError()) {
    $error = 'Failed to update resource: ' . $resource->get('pagetitle') . '. Errors: ' . implode(', ', $response->getAllErrors());
    $modx->log(modX::LOG_LEVEL_ERROR, $error, __METHOD__, __LINE__);
    return $error;
}

return "Critical CSS will be generated for: {$resource->get('uri')} ($resourceID)";
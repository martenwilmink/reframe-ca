id: 74
name: iconInputOptions
description: 'Generate input options with all Semantic UI icon classes.'
category: f_presentation
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:40:"romanesco.iconinputoptions.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:41:"romanesco.iconinputoptions.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * iconInputOptions
 *
 * Based on fontAwesomeInputOptions, but modified to be used with Semantic UI.
 *
 * @author YJ Tso @sepiariver
 * GPL, no warranties, etc.
 *
 * Usage: execute in TV input options, preferably with @CHUNK binding.
 * Alternatively install as Content Blocks input (link to repo coming soon).
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

// source file
$cssUrl = $modx->getOption('cssUrl', $scriptProperties, 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
// scan options
$regexPrefix = $modx->getOption('regexPrefix', $scriptProperties, 'fa-');
// label text output options
$mode = $modx->getOption('mode', $scriptProperties, 'tv'); // can be 'tv' or 'cb'
$titleCaseLabels = $modx->getOption('titleCaseLabels', $scriptProperties, 1);
$operator = $modx->getOption('operator', $scriptProperties, '');
if (empty($operator)) {
    $operator = ($mode === 'cb') ? '=' : '==';
}
// value text output options
$outputPrefix = $modx->getOption('classPrefix', $scriptProperties, 'fa-');
// list output options
$separator = $modx->getOption('separator', $scriptProperties, '');
if (empty($separator)) {
    $separator = ($mode === 'cb') ? "\n" : '||';
}
$excludeClasses = array_filter(array_map('trim', explode(',', $modx->getOption('excludeClasses', $scriptProperties, 'ul,li'))));
// check cache
$cacheKey = $modx->getOption('cacheKey', $scriptProperties, 'fontawesomecsssource');
$provider = $modx->cacheManager->getCacheProvider('default');
$css = $provider->get($cacheKey);
if (!$css) {
    // get source file
    $css = file_get_contents($cssUrl);
    if ($css) {
        $provider->set($cacheKey, $css, 0);
    } else {
        $modx->log(modX::LOG_LEVEL_ERROR, '[iconInputOptions] could not get css source!');
        return '';
    }
}
// output
$output = array();
$regex = "/" . $regexPrefix . "([\w.]*)/";
if (preg_match_all($regex, $css, $matches)) {

    $icons = array_diff($matches[1], $excludeClasses);
    foreach($icons as $icon) {

        $label = ($titleCaseLabels) ? ucwords(str_replace('.', ' ', $icon)) : $icon;
        $icon = str_replace('.', ' ', $icon);
        $output[] = $label . $operator . $icon . $outputPrefix;
    }
}
return implode($separator, $output);
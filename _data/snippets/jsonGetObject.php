id: 143
name: jsonGetObject
description: 'Search a JSON object for specific item and return the entire array. This is initially intended to turn CB repeater elements into CSS, without having to change the internal templating in CB.'
category: f_json
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:37:"romanesco.jsongetobject.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:38:"romanesco.jsongetobject.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * jsonGetObject
 *
 * Search a JSON object for specific item and return the entire array.
 *
 * This is initially intended to turn CB repeater elements into CSS, without
 * having to change the internal templating in ContentBlocks.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('romanescobackyard.core_path', null, $modx->getOption('core_path') . 'components/romanescobackyard/');
$romanesco = $modx->getService('romanesco','Romanesco',$corePath . 'model/romanescobackyard/',array('core_path' => $corePath));

if (!($romanesco instanceof Romanesco)) return;

$json = $modx->getOption('json', $scriptProperties, '');
$object = $modx->getOption('object', $scriptProperties, '');
$tpl = $modx->getOption('tpl', $scriptProperties, '');
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, '');

$jsonArray = json_decode($json, true);
$output = array();

//$modx->log(modX::LOG_LEVEL_ERROR, print_r($jsonArray,1));

// Return directly if JSON input is not present or valid
if (!$jsonArray) {
    $modx->log(modX::LOG_LEVEL_INFO, '[jsonGetObject] No valid JSON input provided.');
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $modx->log(modX::LOG_LEVEL_INFO, '[jsonGetObject] No errors');
            break;
        case JSON_ERROR_DEPTH:
            $modx->log(modX::LOG_LEVEL_INFO, '[jsonGetObject] Maximum stack depth exceeded');
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $modx->log(modX::LOG_LEVEL_INFO, '[jsonGetObject] Underflow or the modes mismatch');
            break;
        case JSON_ERROR_CTRL_CHAR:
            $modx->log(modX::LOG_LEVEL_INFO, '[jsonGetObject] Unexpected control character found');
            break;
        case JSON_ERROR_SYNTAX:
            $modx->log(modX::LOG_LEVEL_INFO, '[jsonGetObject] Syntax error, malformed JSON');
            break;
        case JSON_ERROR_UTF8:
            $modx->log(modX::LOG_LEVEL_INFO, '[jsonGetObject] Malformed UTF-8 characters, possibly incorrectly encoded');
            break;
        default:
            $modx->log(modX::LOG_LEVEL_INFO, '[jsonGetObject] Unknown error');
            break;
    }
    return '';
}

// Search array for given object
$result = $romanesco->recursiveArraySearch($jsonArray,$object);

// Flatten first level, since that's always the full JSON object itself
$result = $result[0];

// Return result if it's no longer an array
if (!is_array($result)) {
    return $result;
}

// Flat arrays can be forwarded directly to the tpl chunk
if (!$result[0]) {
    return $modx->getChunk($tpl, $result);
}

// Loop over multidimensional arrays
if ($result[0]) {
    $idx = 1;
    foreach ($result as $row) {
        $row['idx'] = $idx++;
        $output[] = $modx->getChunk($tpl, $row);
    }
    return implode($outputSeparator,$output);
}

return '';

// @todo: Investigate approach below, where recursiveArraySearch can find multiple instances using 'yield' instead of 'return'.
//foreach ($romanesco->recursiveArraySearch($jsonArray,$object) as $result) {
//    // Flatten first level, since that's always the full JSON object itself
//    $result = $result[0];
//
//    // Return result directly if it's no longer an array
//    if (!is_array($result)) {
//        $output[] = $result;
//    }
//
//    // Flat arrays can be forwarded directly to the tpl chunk
//    if (!$result[0]) {
//        $output[] = $modx->getChunk($tpl, $result);
//    }
//
//    // Loop over multidimensional arrays
//    if ($result[0]) {
//        $rows = array();
//        foreach ($result as $row) {
//            $rows[] = $modx->getChunk($tpl, $row);
//        }
//        $output[] = implode($outputSeparator,$rows);
//    }
//}
//
//return implode(',',$output);
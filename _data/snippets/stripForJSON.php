id: 150
name: stripForJSON
description: 'Prepare the input for being used in JSON. This means escaping backslashes and double quotes.'
category: f_json
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:36:"romanesco.stripforjson.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:37:"romanesco.stripforjson.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * stripForJSON
 *
 * Escape backslashes and double quotes.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 */

$input = $modx->getOption('input', $scriptProperties, $input);
if ($input == '') { return ''; }
return str_replace('\n','',json_encode($input));
id: 121
name: splitString
description: 'Divide string into multiple sections, based on a delimiter. Regular snippet call outputs sections to placeholders. If used as output modifier, specify the number of the part you want to get.'
category: f_modifier
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:35:"romanesco.splitstring.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:36:"romanesco.splitstring.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * splitString
 *
 * Divide string into multiple sections, based on a delimiter.
 *
 * If used as a regular snippet, each part is output to a separate placeholder.
 *
 * If used as output modifier, you need to specify the number of the part you
 * want to get. For example, if your string is:
 *
 * 'Ubuntu|300,700,300italic,700italic|latin'
 *
 * Then [[+placeholder:splitString=`1`]] will return 'Ubuntu'.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

$input = $modx->getOption('input', $scriptProperties, $input);
$options = $modx->getOption('options', $scriptProperties, $options);
$delimiter = $modx->getOption('delimiter', $scriptProperties, '|');
$prefix = $modx->getOption('prefix', $scriptProperties, 'snippet');

// Output filters are also processed when the input is empty, so check for that
if ($input == '') { return ''; }

// Break up the string
$output = explode($delimiter,$input);
$idx = 0;

// If snippet is used as output modifier, return matching section
if ($options) {
    return $output[$options - 1];
}

// Process each section individually
foreach ($output as $value) {
    $idx++;
    $modx->toPlaceholder($idx, trim($value), $prefix);

    // Additional first and last placeholders
    if ($idx == 1) {
        $modx->toPlaceholder('first', trim($value), $prefix);
    }
    $modx->toPlaceholder('last', trim($value), $prefix);
}

// Return placeholder with total idx
$modx->toPlaceholder('total', $idx, $prefix);

return '';
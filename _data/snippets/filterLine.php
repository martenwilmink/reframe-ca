id: 112
name: filterLine
description: 'Search the input for lines containing a specific string. And then return those lines.'
category: f_modifier
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:34:"romanesco.filterline.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:35:"romanesco.filterline.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * filterLine
 *
 * Search input for lines containing a specific string. And then return those
 * lines.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

$lines = $modx->getOption('input', $scriptProperties, $input);
$file = $modx->getOption('file', $scriptProperties, '');
$search = $modx->getOption('searchString', $scriptProperties, $options);
$limit = $modx->getOption('limit', $scriptProperties, 10);
$tpl = $modx->getOption('tpl', $scriptProperties, '');

// Check first if we're dealing with an external file
if ($file) {
    $lines = file_get_contents($file);
}

// Create an array of all lines inside the input
$lines = explode("\n", $lines);
$i = 0;
$output = [];

// Check if the line contains the string we're looking for, and print if it does
foreach ($lines as $line) {
    if(strpos($line, $search) !== false) {
        $output[] = $line;

        $i++;
        if($i >= $limit) {
            break;
        }

        if ($tpl) {
            $output[] = $modx->getChunk($tpl, array(
                'content' => $line,
            ));
        }
    }
}

if ($output) {
    return implode('<br>', $output);
}

return '';
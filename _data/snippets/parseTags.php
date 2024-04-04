id: 90
name: parseTags
description: 'Take in a comma separated string and turn each value into a separate tag. Sometimes you just need that :)'
category: f_modifier
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:33:"romanesco.parsetags.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:34:"romanesco.parsetags.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * parseTags
 *
 * Take in a comma separated string and turn each value into a separate tag.
 * Sometimes you just need that.
 *
 * Original by Mark Hamstra (http://www.markhamstra.nl).
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

$tpl = $modx->getOption('tpl', $scriptProperties, 'tagItemBasic');
$iconClass = $modx->getOption('iconClass', $scriptProperties, 'info');

if ($input == '') { return ''; } // Output filters are also processed when the input is empty, so check for that.
$tags = explode(',',$input); // Based on a delimiter of comma-space.
$output = array();

// Process them individually
foreach ($tags as $key => $value) {
    if (stripos($tpl,'flag') === false) {
        $value = ucfirst($value);
    }
    $output[] = $modx->getChunk($tpl,array(
        'tag' => $value,
        'icon_class' => $iconClass,
    ));
}

return implode('', $output);
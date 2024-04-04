id: 135
name: renderInputOption
description: 'Fetch option name from the romanesco_options table (when used as output modifier). You can also get other available fields with a regular snippet call and tpl chunk.'
category: f_data
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:41:"romanesco.renderinputoption.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:42:"romanesco.renderinputoption.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * renderInputOption
 *
 * Fetch option name from the romanesco_options table (when used as output
 * modifier). You can also get other available fields with a regular snippet
 * call and tpl chunk.
 *
 * Use as output modifier:
 *
 * [[+status_progress:renderInputOption]]
 *
 * Choose whether to match by something other than the default ID:
 *
 * [[+status_progress:renderInputOption=`alias`]]
 *
 * Use as regular snippet, with tpl and key to restrict search results by:
 *
 * [[renderInputOption?
 *     &value=`[[+status_progress]]`
 *     &match=`alias`
 *     &key=`status_progress`
 *     &tpl=`tagItemTooltip`
 * ]]
 *
 * Available fields in tpl:
 *
 * [[+id]]
 * [[+name]]
 * [[+alias]]
 * [[+description]]
 * [[+parent]]
 * [[+key]]
 * [[+position]]
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

$corePath = $modx->getOption('romanescobackyard.core_path', null, $modx->getOption('core_path') . 'components/romanescobackyard/');
$backyard = $modx->addPackage('romanescobackyard',$corePath . 'model/');

$value = $modx->getOption('value', $scriptProperties, $input);
$match = $modx->getOption('match', $scriptProperties, $options);
$key = $modx->getOption('key', $scriptProperties, '');
$select = $modx->getOption('select', $scriptProperties, 'name');
$tpl = $modx->getOption('tpl', $scriptProperties, '');
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, '');

if (!function_exists('getInputOption')) {
    function getInputOption($value,$match,$key,$select,$tpl){
        global $modx;

        $inputOption = $modx->getObject('rmOption', array(
            $match => $value,
            'key' => $key,
        ));
        $outputFields = array(
            'id' => $inputOption->get('id'),
            'name' => $inputOption->get('name'),
            'title' => $inputOption->get('name'),
            'tag' => $inputOption->get('name'),
            'alias' => $inputOption->get('alias'),
            'description' => $inputOption->get('description'),
            'parent' => $inputOption->get('parent'),
            'group' => $inputOption->get('parent'),
            'key' => $inputOption->get('key'),
            'position' => $inputOption->get('position'),
        );

        if ($tpl) {
            $output = $modx->getChunk($tpl, $outputFields);
        } else {
            $output = $inputOption->get($select);
        }

        return $output;
    }
}

if ($value == '') { return ''; }

$output = [];

// Find matching ID by default
if (!$match) { $match = 'id'; }

// Don't fetch entire object if it's being used as output modifier
if ($input) {
    $query = $modx->newQuery('rmOption');
    $query->where(array(
        $match => $value,
    ));
    $query->select($select);

    return $modx->getValue($query->prepare());
}

// Value can be an array as well
else if (strpos($value,',')) {
    $values = explode(',',$value);

    foreach ($values as $value) {
        $output[] = getInputOption($value,$match,$key,$select,$tpl);
    }
    return implode($outputSeparator,$output);
}

else {
    return getInputOption($value,$match,$key,$select,$tpl);
}
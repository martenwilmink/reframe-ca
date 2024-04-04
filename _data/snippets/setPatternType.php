id: 101
name: setPatternType
category: f_hub
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:38:"romanesco.setpatterntype.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:39:"romanesco.setpatterntype.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

$input = $modx->getOption('input', $scriptProperties, $input);
$length = $modx->getOption('length', $scriptProperties, $options);

switch($input) {
    case stripos($input,'electrons') !== false:
        $type = "Electron";
        $type_s = "E";
        break;
    case stripos($input,'atoms') !== false:
        $type = "Atom";
        $type_s = "A";
        break;
    case stripos($input,'molecules') !== false:
        $type = "Molecule";
        $type_s = "M";
        break;
    case stripos($input,'organisms') !== false:
        $type = "Organism";
        $type_s = "O";
        break;
    case stripos($input,'templates') !== false:
        $type = "Template";
        $type_s = "T";
        break;
    case stripos($input,'pages') !== false:
        $type = "Page";
        $type_s = "P";
        break;
    case stripos($input,'formulas') !== false:
        $type = "Formula";
        $type_s = "F";
        break;
    case stripos($input,'computation') !== false:
        $type = "Computation";
        $type_s = "C";
        break;
    case stripos($input,'boson') !== false:
        $type = "Boson";
        $type_s = "B";
        break;
    default:
        $type = "undefined";
        $type_s = "U";
        break;
}

if ($length == 'word') {
    return $type;
}

return $type_s;
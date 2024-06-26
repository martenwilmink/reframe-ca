id: 66
name: stripAsAlias
description: 'Turn input into lowercase-hyphen-separated-alias-format and strip unwanted special characters. Useful for creating anchor links based on headings, for example.'
category: f_modifier
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:36:"romanesco.stripasalias.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:37:"romanesco.stripasalias.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * stripAsAlias
 *
 * Turn input into lowercase-hyphen-separated-alias-format and strip unwanted
 * special characters. Useful for creating anchor links based on headings, for
 * example.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

$input = strip_tags($input); // strip HTML
$input = strtolower($input); // convert to lowercase
$input = preg_replace('/[^.A-Za-z0-9 _-]/', '', $input); // strip non-alphanumeric characters
$input = preg_replace('/\s+/', '-', $input); // convert white-space to dash
$input = preg_replace('/-+/', '-', $input);  // convert multiple dashes to one
$input = trim($input, '-'); // trim excess

return $input;
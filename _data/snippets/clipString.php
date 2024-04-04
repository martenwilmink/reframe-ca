id: 126
name: clipString
description: 'Trim the edges of a string. The given value represents the number of characters that will be clipped. If the value is negative, they will be clipped from the end of the string.'
category: f_modifier
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:34:"romanesco.clipstring.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:35:"romanesco.clipstring.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * clipString
 *
 * Trim a certain amount of characters from the edges of a string.
 *
 * If a negative value is used, this number of characters will be clipped from
 * the end. Otherwise, they are clipped from the start of the string.
 *
 * If no value is given, whitespace is trimmed from the edges.
 *
 * Usage examples:
 *
 * [[*your_tv:clipString=`-1`]]
 * (if the value of your_tv is 'https', this will return 'http')
 *
 * [[clipString?
 *     &input=`[[+some_string]]`
 *     &clip=`1`
 * ]]
 * (if your string is 'your website', this will return 'our website')
 *
 * You can also clip both edges:
 *
 * [[*your_tv:clipString=`8`:clipString=`-1`]]
 * (if your_tv is 'https://your_website/', this will return 'your_website')
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 */

$input = $modx->getOption('input', $scriptProperties, $input);
$clip = (int) $modx->getOption('clip', $scriptProperties, $options);

// Output filters are also processed when the input is empty, so check for that
if ($input == '') { return ''; }

// Only trim whitespace if clip is not defined
if (!$clip) {
    return trim($input);
}

// Decide whether to clip the start or end of the string
if ($clip < 0) {
    return mb_substr($input, 0, $clip);
} else {
    return mb_substr($input, $clip);
}
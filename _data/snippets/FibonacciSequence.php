id: 137
name: FibonacciSequence
description: 'Generate a sequence of Fibonacci numbers. In a Fibonacci sequence, every number after the first two is the sum of the two preceding ones.'
category: f_framework
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:41:"romanesco.fibonaccisequence.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:42:"romanesco.fibonaccisequence.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * FibonacciSequence
 *
 * Generate a sequence of Fibonacci numbers. In a Fibonacci sequence, every
 * number after the first two is the sum of the two preceding ones.
 *
 * You can indicate where to start and how many numbers to generate:
 *
 * [[FibonacciSequence?
 *    &limit=`9`
 *    &start=`65`
 * ]]
 *
 * If you want to retrieve a specific number from inside the sequence, you can
 * do so using the position parameter:
 *
 * [[FibonacciSequence?
 *    &start=`40`
 *    &position=`5`
 * ]]
 *
 * Without any parameters, the script will output a comma delimited sequence of
 * 8 numbers. The duplicate 1 at position 2 and 3 is automatically removed.
 *
 * [[FibonacciSequence]]
 * will output: 0,1,2,3,5,8,13,21
 *
 * @link http://www.hashbangcode.com/blog/get-fibonacci-numbers-using-php
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$limit = $modx->getOption('limit', $scriptProperties, 8);
$start = $modx->getOption('start', $scriptProperties, 0);
$position = $modx->getOption('position', $scriptProperties, '');
$delimiter = $modx->getOption('delimiter', $scriptProperties, ',');

if ($start > 0) {
    $second = $start * 2;
} else {
    $second = 1;
    $limit++; // The third 1 is removed later, so limit needs +1 to be accurate
}

$sequence = array();

if (!function_exists('fibonacciSequence')) {
    function fibonacciSequence($limit, $start, $second, $position){
        $sequence = array($start, $second);

        if ($position > $limit) {
            $limit = $position;
        }

        for ($i=2; $i<=$limit; ++$i) {
            if ($i >= $limit) {
                break;
            } else {
                $sequence[$i] = $sequence[$i-1] + $sequence[$i-2];
            }
        }

        if ($position) {
            return $sequence[$position - 1];
        } else {
            return $sequence;
        }
    }
}

$output = fibonacciSequence($limit, $start, $second, $position);
$output = array_unique($output); // Remove duplicate 1

if ($position) {
    return $output;
} else {
    return implode($delimiter, $output);
}
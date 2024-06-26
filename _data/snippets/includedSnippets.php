id: 98
name: includedSnippets
category: f_hub
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:40:"romanesco.includedsnippets.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:41:"romanesco.includedsnippets.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * includedSnippets
 *
 * This snippet is intended to list the snippets that are being called
 * inside a given chunk. It needs the raw content of the chunk as input.
 *
 * See includedChunks for more detailed instructions.
 *
 * @author Hugo Peek
 */

$string = $input;
$tpl = $modx->getOption('tpl', $scriptProperties, 'includedPatternsRow');

// Create a list with all available snippets
$snippetList = $modx->runSnippet('Rowboat', (array(
    'table' => 'modx_site_snippets',
    'tpl' => 'rawName',
    'limit' => '0',
    'columns' => '{ "name":"" }',
    'outputSeparator' => '|'
)
));

// Find included snippets by comparing them to the list
$regex = '"(' . $snippetList . ')"';

// Set idx start value to something high, to prevent overlap
$idx = 1000;

// Define output array
$output = array();

if (preg_match_all($regex, $string, $matches)) {
    foreach ($matches as $snippet) {
        $match = $snippet;
    }

    // Remove duplicates
    $result = array_unique($match);

    // Process matches individually
    foreach ($result as $name) {
        // Also fetch category, to help ensure the correct resource is being linked
        $query = $modx->newQuery('modSnippet', array(
            'name' => $name
        ));
        $query->select('category');
        $category = $modx->getValue($query->prepare());

        // Up idx value by 1, so a unique placeholder can be created
        $idx++;

        // Output to a chunk that contains the link generator
        $output[] = $modx->getChunk($tpl, array(
            'name' => $name,
            'category' => $category,
            'idx' => $idx
        ));
    }
}

sort($output);

return implode($output);
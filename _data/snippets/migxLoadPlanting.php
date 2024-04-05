id: 100072
name: migxLoadPlanting
description: 'This adds the class_key to the request, so it can be accessed with POST. In addition, the planting features TV is populated with values from the connected features table.'
category: E6_dat_load
snippet: "/**\n * migxLoadPlanting\n *\n * When parsing options inside a selector with @CHUNK, it seems impossible to\n * read existing values from other fields. This hook fetches the existing class\n * key and adds it to the request, making it available for templating.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n// Forward class key, for use in TV templating\n$classKey = $scriptProperties['record']['class_key'] ?? '';\n$_POST['object_class_key'] = $classKey;\n\n// Set planting features TV\n$featuresTV = [];\n$features = $modx->getCollection('earthPlantingFeature', [\n    'planting_id' => $scriptProperties['record']['id'],\n]);\nforeach ($features as $feature) {\n    $featuresTV[] = $feature->get('option_id');\n}\nif ($featuresTV) {\n    $scriptProperties['record']['features'] = implode('||', $featuresTV);\n}\n\nreturn '';"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/e6_dat_load/migxloadplanting.snippet.php'

-----


/**
 * migxLoadPlanting
 *
 * When parsing options inside a selector with @CHUNK, it seems impossible to
 * read existing values from other fields. This hook fetches the existing class
 * key and adds it to the request, making it available for templating.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

// Forward class key, for use in TV templating
$classKey = $scriptProperties['record']['class_key'] ?? '';
$_POST['object_class_key'] = $classKey;

// Set planting features TV
$featuresTV = [];
$features = $modx->getCollection('earthPlantingFeature', [
    'planting_id' => $scriptProperties['record']['id'],
]);
foreach ($features as $feature) {
    $featuresTV[] = $feature->get('option_id');
}
if ($featuresTV) {
    $scriptProperties['record']['features'] = implode('||', $featuresTV);
}

return '';
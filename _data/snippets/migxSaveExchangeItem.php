id: 100078
name: migxSaveExchangeItem
description: 'After save hook for exchange items.'
category: E6_dat_save
snippet: "/**\n * migxSaveExchange\n *\n * Aftersave snippet for exchanges.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\n\n$object = $modx->getOption('object', $scriptProperties);\n$properties = $modx->getOption('scriptProperties', $scriptProperties, []);\n$configs = $modx->getOption('configs', $properties, '');\n$postValues = $modx->getOption('postvalues', $scriptProperties, []);\n$co_id = $modx->getOption('co_id', $properties);\n\n//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties,1));\n//$modx->log(modX::LOG_LEVEL_ERROR, $object->get('id'));\n\n$result = [];\n\nif (!is_object($object)) return;\n\nif (str_contains($configs, 'exchange_items')) {\n    $object->set('exchange_id', $co_id);\n}\nelseif (str_contains($configs, 'exchange_smeti')) {\n    $q = $modx->newQuery('earthExchange',[\n        'id' => $co_id,\n    ]);\n    $q->select('exchange_id');\n    $oppositeID = $modx->getValue($q->prepare());\n\n    $object->set('exchange_id', $oppositeID);\n}\n\n$object->save();\n\nreturn json_encode($result);"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/e6_dat_save/migxsaveexchangeitem.snippet.php'

-----


/**
 * migxSaveExchange
 *
 * Aftersave snippet for exchanges.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);
$configs = $modx->getOption('configs', $properties, '');
$postValues = $modx->getOption('postvalues', $scriptProperties, []);
$co_id = $modx->getOption('co_id', $properties);

//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties,1));
//$modx->log(modX::LOG_LEVEL_ERROR, $object->get('id'));

$result = [];

if (!is_object($object)) return;

if (str_contains($configs, 'exchange_items')) {
    $object->set('exchange_id', $co_id);
}
elseif (str_contains($configs, 'exchange_smeti')) {
    $q = $modx->newQuery('earthExchange',[
        'id' => $co_id,
    ]);
    $q->select('exchange_id');
    $oppositeID = $modx->getValue($q->prepare());

    $object->set('exchange_id', $oppositeID);
}

$object->save();

return json_encode($result);
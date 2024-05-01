id: 100077
name: migxSaveExchange
description: 'After save hook for exchanges.'
category: E6_dat_save
snippet: "/**\n * migxSaveExchange\n *\n * Aftersave snippet for exchanges.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\n\n$object = $modx->getOption('object', $scriptProperties);\n$properties = $modx->getOption('scriptProperties', $scriptProperties, []);\n$configs = $modx->getOption('configs', $properties, '');\n$postValues = $modx->getOption('postvalues', $scriptProperties, []);\n//$co_id = $modx->getOption('co_id', $properties);\n\n//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties,1));\n//$modx->log(modX::LOG_LEVEL_ERROR, $object->get('id'));\n\n$result = [];\n\n// Don't process gift exchange\nif (is_object($object) && !$postValues['gift'])\n{\n    // @todo: move this to processor\n\n    // Define opposite class key\n    $oppositeClassKey = $object->get('class_key');\n    $oppositeClassKey = str_replace('earthExchange','', $oppositeClassKey);\n    $oppositeClassKey = preg_split('/(?=[A-Z])/', $oppositeClassKey, 2, PREG_SPLIT_NO_EMPTY);\n    $oppositeClassKey = array_reverse($oppositeClassKey);\n    $oppositeClassKey = 'earthExchange' . implode($oppositeClassKey);\n\n    // Create or retrieve opposite exchange\n    if (!$object->get('exchange_id')) {\n        $postValues['exchange_id'] = $object->get('id');\n        $postValues['class_key'] = $oppositeClassKey;\n        $oppositeExchange = $modx->newObject('earthExchange', $postValues);\n        $oppositeExchange->save();\n        $object->set('exchange_id', $oppositeExchange->get('id'));\n    } else {\n        $oppositeExchange = $modx->getObject('earthExchange', [\n            'exchange_id' => $object->get('id'),\n            'class_key' => $oppositeClassKey,\n        ]);\n    }\n\n    // Update opposite parent and target\n    $oppositeExchange->set('parent_id', $object->get('target_id'));\n    $oppositeExchange->set('target_id', $object->get('parent_id'));\n    $oppositeExchange->set('title', $object->get('title'));\n    $oppositeExchange->save();\n\n    $object->save();\n}\n\nreturn json_encode($result);"
properties: 'a:0:{}'

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
//$co_id = $modx->getOption('co_id', $properties);

//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties,1));
//$modx->log(modX::LOG_LEVEL_ERROR, $object->get('id'));

$result = [];

// Don't process gift exchange
if (is_object($object) && !$postValues['gift'])
{
    // @todo: move this to processor

    // Define opposite class key
    $oppositeClassKey = $object->get('class_key');
    $oppositeClassKey = str_replace('earthExchange','', $oppositeClassKey);
    $oppositeClassKey = preg_split('/(?=[A-Z])/', $oppositeClassKey, 2, PREG_SPLIT_NO_EMPTY);
    $oppositeClassKey = array_reverse($oppositeClassKey);
    $oppositeClassKey = 'earthExchange' . implode($oppositeClassKey);

    // Create or retrieve opposite exchange
    if (!$object->get('exchange_id')) {
        $postValues['exchange_id'] = $object->get('id');
        $postValues['class_key'] = $oppositeClassKey;
        $oppositeExchange = $modx->newObject('earthExchange', $postValues);
        $oppositeExchange->save();
        $object->set('exchange_id', $oppositeExchange->get('id'));
    } else {
        $oppositeExchange = $modx->getObject('earthExchange', [
            'exchange_id' => $object->get('id'),
            'class_key' => $oppositeClassKey,
        ]);
    }

    // Update opposite parent and target
    $oppositeExchange->set('parent_id', $object->get('target_id'));
    $oppositeExchange->set('target_id', $object->get('parent_id'));
    $oppositeExchange->set('title', $object->get('title'));
    $oppositeExchange->save();

    $object->save();
}

return json_encode($result);
id: 100077
name: migxSaveExchange
description: 'After save hook for exchanges.'
category: E6_dat_save
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
$earthbrain = $modx->getService('earthbrain','earthbrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);
$configs = $modx->getOption('configs', $properties, '');
$postValues = $modx->getOption('postvalues', $scriptProperties, []);
//$co_id = $modx->getOption('co_id', $properties);

//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties,1));
//$modx->log(modX::LOG_LEVEL_ERROR, $object->get('id'));

$result = [];

if (is_object($object))
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

return true;
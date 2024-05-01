id: 100078
name: migxSaveExchangeItem
description: 'After save hook for exchange items.'
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
$co_id = $modx->getOption('co_id', $properties);

//$modx->log(modX::LOG_LEVEL_ERROR, print_r($properties,1));
//$modx->log(modX::LOG_LEVEL_ERROR, $object->get('id'));

$result = [];

if (is_object($object))
{
    if (str_contains($configs, 'exchange_items')) {
        $object->set('exchange_id', $co_id);
        $object->save();
    }
    elseif (str_contains($configs, 'exchange_smeti')) {
        $q = $modx->newQuery('earthExchange',[
            'id' => $co_id,
        ]);
        $q->select('exchange_id');
        $oppositeID = $modx->getValue($q->prepare());

        $object->set('exchange_id', $oppositeID);
        $object->save();
    }
}

return true;
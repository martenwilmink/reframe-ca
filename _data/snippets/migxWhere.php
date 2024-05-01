id: 100062
name: migxWhere
description: 'Snippet to generate a where clause that only fetches items attached to the parent object.'
category: E6_data
properties: 'a:0:{}'

-----

/**
 * migxWhere
 *
 * Snippet to generate a where clause that only fetches items attached to the
 * parent object.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));

// Get parent config from stored request parameters
$objectID = $modx->getOption('object_id', $_REQUEST);
$parentConfig = $modx->getOption('reqConfigs', $_REQUEST);
$configs = $modx->getOption('configs', $scriptProperties, '');
$classType = $modx->getOption('type', $scriptProperties);

$classKeys = [];
$where = [];

// Exchange items in opposite direction
if ($classType === 'exchange_smeti') {
    $q = $modx->newQuery('earthExchange',[
        'id' => $objectID,
    ]);
    $q->select('exchange_id');
    $oppositeID = $modx->getValue($q->prepare());

    $where['exchange_id'] = $oppositeID;
}

// All combinations of class_key/parent_id
elseif ($objectID && $parentConfig) {
    $classKeys = $modx->runSnippet('migxGetClassKeys', ['config' => $parentConfig]) ?? [];

    $where['class_key'] = $classKeys[$classType] ?? '';
    $where['parent_id'] = $objectID;
}

$where = json_encode($where);
$validate = $earthbrain->validateJSON($where);

if (!$validate) {
    return '';
}

return $where;
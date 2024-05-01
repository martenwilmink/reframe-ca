id: 100062
name: migxWhere
description: 'Snippet to generate a where clause that only fetches items attached to the parent object.'
category: E6_data
snippet: "/**\n * migxWhere\n *\n * Snippet to generate a where clause that only fetches items attached to the\n * parent object.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));\n\n// Get parent config from stored request parameters\n$objectID = $modx->getOption('object_id', $_REQUEST);\n$parentConfig = $modx->getOption('reqConfigs', $_REQUEST);\n$configs = $modx->getOption('configs', $scriptProperties, '');\n$classType = $modx->getOption('type', $scriptProperties);\n\n$classKeys = [];\n$where = [];\n\n// Exchange items in opposite direction\nif ($classType === 'exchange_smeti') {\n    $q = $modx->newQuery('earthExchange',[\n        'id' => $objectID,\n    ]);\n    $q->select('exchange_id');\n    $oppositeID = $modx->getValue($q->prepare());\n\n    $where['exchange_id'] = $oppositeID;\n}\n\n// All combinations of class_key/parent_id\nelseif ($objectID && $parentConfig) {\n    $classKeys = $modx->runSnippet('migxGetClassKeys', ['config' => $parentConfig]) ?? [];\n\n    $where['class_key'] = $classKeys[$classType] ?? '';\n    $where['parent_id'] = $objectID;\n}\n\n$where = json_encode($where);\n$validate = $earthbrain->validateJSON($where);\n\nif (!$validate) {\n    return '';\n}\n\nreturn $where;"
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
id: 100083
name: migxVerifyImage
description: 'Hook that checks if an image already exists (and if it''s edited) before saving.'
category: E6_dat_verify
snippet: "/**\n * migxVerifyImage\n *\n * Hook that checks if an image already exists (and if it's edited) before\n * saving.\n *\n * Needs to be attached to beforesave event.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\n\n$object = $modx->getOption('object', $scriptProperties);\n$properties = $modx->getOption('scriptProperties', $scriptProperties, '');\n$configs = $modx->getOption('configs', $properties, '');\n\n//$modx->log(modX::LOG_LEVEL_ERROR, 'POST: ' . print_r($_REQUEST, true));\n//$modx->log(modX::LOG_LEVEL_ERROR, 'Props: ' . print_r($properties, true));\n//$modx->log(modX::LOG_LEVEL_ERROR, 'Configs: ' . $configs);\n\nif (is_object($object))\n{\n    $img = json_decode($object->get('img'), true);\n    $imgExisting = null;\n\n    // Check if image already exists\n    if ($properties['object_id'] !== 'new')\n    {\n        $q = $modx->newQuery($properties['class_key'], [\n            'id' => $object->get('id')\n        ]);\n        $q->select('img');\n        $imgExisting = json_decode($modx->getValue($q->prepare()), true);\n    }\n\n    // Check if it's being created, updated or removed\n    if ($img && $imgExisting)\n    {\n        $diff1 = array_merge($img['sourceImg'], $img['crop']);\n        $diff2 = array_merge($imgExisting['sourceImg'], $imgExisting['crop']);\n        $imgDiff = array_diff_assoc($diff1, $diff2);\n\n        //$modx->log(modX::LOG_LEVEL_ERROR, 'Diff: ' . print_r($imgDiff, true));\n\n        if ($imgDiff && !$imgDiff['src']) {\n            $object->set('img_action', 'update');\n            //$modx->log(modX::LOG_LEVEL_ERROR, \"Image exists and is being updated\");\n        }\n        elseif ($imgDiff && $imgDiff['src']) {\n            $object->set('img_action', 'create'); // treat replaced image as new\n            //$modx->log(modX::LOG_LEVEL_ERROR, \"Image exists and is being replaced\");\n        }\n        else {\n            $object->set('img_action', '');\n            //$modx->log(modX::LOG_LEVEL_ERROR, \"Image exists and is NOT being updated\");\n        }\n\n    }\n    elseif (!$img && $imgExisting)\n    {\n        $object->set('img_action', 'remove');\n        //$modx->log(modX::LOG_LEVEL_ERROR, \"Image exists and is being removed\");\n    }\n    elseif ($img && !$imgExisting)\n    {\n        $object->set('img_action', 'create');\n        //$modx->log(modX::LOG_LEVEL_ERROR, \"Image is new\");\n    }\n}\n\n\n\n\n\n\n\n\nreturn '';"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/e6_dat_verify/migxverifyimage.snippet.php'

-----


/**
 * migxVerifyImage
 *
 * Hook that checks if an image already exists (and if it's edited) before
 * saving.
 *
 * Needs to be attached to beforesave event.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, '');
$configs = $modx->getOption('configs', $properties, '');

//$modx->log(modX::LOG_LEVEL_ERROR, 'POST: ' . print_r($_REQUEST, true));
//$modx->log(modX::LOG_LEVEL_ERROR, 'Props: ' . print_r($properties, true));
//$modx->log(modX::LOG_LEVEL_ERROR, 'Configs: ' . $configs);

if (is_object($object))
{
    $img = json_decode($object->get('img'), true);
    $imgExisting = null;

    // Check if image already exists
    if ($properties['object_id'] !== 'new')
    {
        $q = $modx->newQuery($properties['class_key'], [
            'id' => $object->get('id')
        ]);
        $q->select('img');
        $imgExisting = json_decode($modx->getValue($q->prepare()), true);
    }

    // Check if it's being created, updated or removed
    if ($img && $imgExisting)
    {
        $diff1 = array_merge($img['sourceImg'], $img['crop']);
        $diff2 = array_merge($imgExisting['sourceImg'], $imgExisting['crop']);
        $imgDiff = array_diff_assoc($diff1, $diff2);

        //$modx->log(modX::LOG_LEVEL_ERROR, 'Diff: ' . print_r($imgDiff, true));

        if ($imgDiff && !$imgDiff['src']) {
            $object->set('img_action', 'update');
            //$modx->log(modX::LOG_LEVEL_ERROR, "Image exists and is being updated");
        }
        elseif ($imgDiff && $imgDiff['src']) {
            $object->set('img_action', 'create'); // treat replaced image as new
            //$modx->log(modX::LOG_LEVEL_ERROR, "Image exists and is being replaced");
        }
        else {
            $object->set('img_action', '');
            //$modx->log(modX::LOG_LEVEL_ERROR, "Image exists and is NOT being updated");
        }

    }
    elseif (!$img && $imgExisting)
    {
        $object->set('img_action', 'remove');
        //$modx->log(modX::LOG_LEVEL_ERROR, "Image exists and is being removed");
    }
    elseif ($img && !$imgExisting)
    {
        $object->set('img_action', 'create');
        //$modx->log(modX::LOG_LEVEL_ERROR, "Image is new");
    }
}








return '';